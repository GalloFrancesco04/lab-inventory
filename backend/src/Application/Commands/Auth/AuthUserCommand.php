<?php
// Command class to encapsulate data to authenticate the user
class AuthUserCommand {
    public $email;
    public $pass;

    public function __construct($email, $pass) {
        $this->email = $email;
        $this->pass = $pass;
    }
}