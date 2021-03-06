<?php

/**
 * 
 * Component to access the applications database.
 */
class Database
{

    /**
     * mysqli database link
     *
     * @var mysqli
     */
    private $link;

    /**
     * Id of the last insert.
     *
     * @var integer
     */
    public $insert_id;

    function __construct()
    {
        $dbconfig = Configuration::getNode("database");
        $this->connect($dbconfig);
        // ping the database to find out whether the connection is stable.
        $start = microtime(true);
        while (! $this->link || ! $this->link->ping()) {
            sleep(1);
            $current = microtime(true);
            $exec_time = $current - $start;
            if ($exec_time > 30) {
                throw new mysqli_sql_exception("Cannot establish database connection.");
            }
            $this->connect($dbconfig);
        }
    }

    /**
     * Connect to the database.
     *
     * @param array $dbconfig
     *            Associative array of config parameters.
     */
    public function connect(array $dbconfig): void
    {
        $this->link = mysqli_connect($dbconfig["host"], $dbconfig["user"], $dbconfig["password"], $dbconfig["name"]);
    }

    /**
     * Inserts a new entry in the database.
     *
     * @param string $table
     *            Name of the table.
     * @param string $ids
     *            Comma separated ids of the columns.
     * @param string $values
     *            Comma separated values for the ids.
     * @return string Error message.
     */
    public function insert(string $table, string $ids, string $values): string
    {
        $query = "INSERT INTO $table ($ids) VALUES ($values)";
        if ($this->link->query($query)) {
            $this->insert_id = mysqli_insert_id($this->link);
            return "";
        }
        return $this->link->error;
    }

    /**
     * Updates an existing entry in the database.
     *
     * @param string $table
     *            Name of the table.
     * @param string $where
     *            Select statement (e.g. id).
     * @param string $values
     *            Comma separated ids and values to set.
     *            (e.g.: example = 2, example2 = 'test')
     * @return bool Whether store was successful.
     */
    public function update(string $table, string $where, string $values): bool
    {
        $query = "UPDATE $table SET $values WHERE $where";
        return $this->link->query($query);
    }

    /**
     * Search unique entry in the database.
     *
     * @param string $table
     *            Name of the table.
     * @param string $select
     *            Variables that should be stored in the return array.
     * @param string $query
     *            The query that should be executed. (e.g. "id=1")
     * @return mysqli_result Only the first entry is returned.
     * @throws BadFunctionCallException if result is not unique.
     */
    public function select(string $table, string $select, string $query)
    {
        $result = $this->multiSelect($table, $select, $query);
        if ($result->num_rows == 1) {
            return $result->fetch_assoc();
        } else {
            if ($result->num_rows == 0) {
                return null;
            }
            throw new BadFunctionCallException("Result not unique.");
        }
    }

    /**
     * Search entries in the database.
     *
     * @return mysqli_result Result of the query.
     */
    public function multiSelect(string $table, string $select, string $query)
    {
        $query = "SELECT $select FROM $table WHERE $query";
        return $this->query($query);
    }

    /**
     * Count entries in the database.
     *
     * @return int Amount of entities found.
     */
    public function count(string $table, string $select, string $query): int
    {
        $query = "SELECT $select FROM $table WHERE $query";
        $result = $this->query($query);
        $i = 0;
        if ($result->num_rows > 0) {
            while ($result->fetch_assoc()) {
                $i ++;
            }
        }
        return $i;
    }

    /**
     * Execute a sql query on the database.
     *
     * @param string $query
     *            Query to execute.
     */
    public function query(string $query)
    {
        return $this->link->query($query);
    }
}