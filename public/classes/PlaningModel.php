<?php

class PlaningModel
{

    private $link;

    function __construct()
    {
        $this->link = mysqli_connect("planingdb", "root", "password", "planingdb");

        mysqli_set_charset($this->link, "utf8");
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
     * Search entry in the database
     *
     * @param string $table
     *            Name of the table.
     * @param string $select
     *            Variables that should be stored in the return array.
     * @param string $var
     *            Id of the column to search.
     * @param string $value
     *            Value of the variable.
     */
    public function select(string $table, string $select, string $var, string $value)
    {
        $query = "SELECT $select FROM $table WHERE $var=$value";
        $result = $this->link->query($query);
        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        } else {
            return null;
        }
    }
}