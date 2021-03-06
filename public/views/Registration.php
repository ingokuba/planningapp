<?php

/**
 * Registration form to register a new user and store it in the database.
 */
class Registration extends Page
{

    /**
     * Error message that should be displayed when something failed
     * or invalid input was submitted.
     *
     * @var string
     */
    private $error = "";

    protected function getBody(): string
    {
        if ($this->user != null) {
            $this->user->logout();
        }
        $this->handlePost();
        return "<form method='post'
		style='max-width: 330px;' class='jumbotron vertical-center mx-auto'>
        <a href='/' data-toggle='tooltip' title='Home'><i class='fas fa-3x fa-home'></i></a>
		<h3 class='mb-3 font-weight-normal form-text'>Registration</h3>
			<div class='form-group row'>
				<input id='givenName' class='form-control col-sm-6' placeholder='First name' required='required' name='givenName'>
				<input id='surname' class='form-control col-sm-6' placeholder='Last name' required='required' name='surname'>
			</div>
			<div class='form-group row'>
				<input id='email' class='form-control col-sm'
					placeholder='Email' type='email' required='required' name='email'>
			</div>
			<div class='form-group row'>
				<input id='password' class='form-control col-sm'
					placeholder='Password' type='password' required='required' name='password'>
			</div>
			<button id='registerButton' class='btn btn-lg btn-primary btn-block'
				type='submit'>Submit</button>
            <div class='text-primary text-center mt-2'><a href='Login'>Already have an account?</a></div>
            <div class='row text-danger mt-2'>$this->error</div>
	     </form>";
    }

    /**
     * Register the new user with the entered credentials and log it in to the session.
     */
    private function handlePost()
    {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $user = new User($this->database);
            // get user credentials from post request:
            foreach (array(
                User::$GIVENNAME,
                User::$SURNAME,
                User::$EMAIL,
                User::$PASSWORD
            ) as $attribute) {
                $user->setValue($attribute, Util::getPostData($attribute));
            }
            // store user:
            try {
                $this->error = $user->store();
            } catch (Exception $e) {
                $this->error = $e->getMessage();
            }
            if (empty($this->error)) {
                // login user to the session
                $this->error = $user->login(false);
            }
        }
    }
}