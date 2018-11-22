<?php

class User extends Entity
{

    public static $USER = "User";

    public static $GIVENNAME = "givenName";

    public static $SURNAME = "surname";

    public static $EMAIL = "email";

    public static $PASSWORD = "password";

    public static $CREATED_AT = "createdAt";

    protected function initializeEntityType(): string
    {
        return User::$USER;
    }

    protected function initializeAttributes(): array
    {
        return array(
            User::$GIVENNAME,
            User::$SURNAME,
            User::$EMAIL,
            User::$PASSWORD,
            User::$CREATED_AT
        );
    }

    protected function checkConstraints()
    {
        $message = "";
        // not nullable:
        foreach (array(
            User::$GIVENNAME,
            User::$SURNAME,
            User::$EMAIL,
            User::$PASSWORD
        ) as $attribute) {
            if (empty($this->getValue($attribute))) {
                $message .= "Attribute '$attribute' is not nullable. ";
            }
        }
        // email must be unique:
        $email = $this->getValue(User::$EMAIL);
        $result = $this->model->select(User::$USER, "*", User::$EMAIL, "'$email'");
        if ($result != null) {
            $message .= "Email must be unique. ";
        }
        $createdAt = $this->getValue(User::$CREATED_AT);
        if (! empty($createdAt)) {
            $message .= "Attribute 'createdAt' should not be set for storing.!";
        }
        if (! empty($message)) {
            throw new InvalidArgumentException($message);
        }
    }

    /**
     * Login the user to the session.
     *
     * @return string Error message.
     */
    public function login(): string
    {
        $email = $this->getValue(User::$EMAIL);
        $password = $this->getValue(User::$PASSWORD);
        if (empty($email) || empty($password)) {
            return "Please enter your credentials.";
        }
        $result = $this->model->select(User::$USER, "*", User::$EMAIL, "'$email'");
        if ($result != null && $result["password"] == $password) {
            // Cookie lifespan: 30 minutes
            setcookie(User::$USER, "$email", time() + 1800, "/");
            header("Location: /");
            return "";
        }
        return "Invalid credentials.";
    }

    /**
     * Logout the user from the session.
     */
    public static function logout()
    {
        $cookie = $_COOKIE[User::$USER];
        if (isset($cookie)) {
            setcookie(User::$USER, '', time() - 1000);
        }
    }

    /**
     * Returns the user found with the email in the session cookie.
     *
     * @param PlaningModel $model
     *            Model needed for the database connection.
     * @return NULL|User Logged in user or null when user is not logged in or was deleted.
     *        
     */
    public static function getUserFromSession(PlaningModel $model)
    {
        $user = new User($model);
        // get email from cookie:
        $email = PlaningController::getCookieValue(User::$USER);
        if (empty($email)) {
            return null;
        }
        $result = $model->select(User::$USER, "*", User::$EMAIL, "'$email'");
        if ($result != null) {
            $user->setValue(User::$EMAIL, $email);
            $user->setValue(User::$GIVENNAME, $result[User::$GIVENNAME]);
            $user->setValue(User::$SURNAME, $result[User::$SURNAME]);
            $user->setValue(User::$CREATED_AT, $result[User::$CREATED_AT]);
            return $user;
        }
        // User not found.
        return null;
    }
}