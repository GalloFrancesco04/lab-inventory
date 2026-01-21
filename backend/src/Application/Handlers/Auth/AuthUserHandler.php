<?php

require_once __DIR__ . '/../../Commands/Auth/AuthUserCommand.php';
class AuthUserHandler {

    // private $repository;

    public function __construct() {
        // $repository = new UserRepository();
    }

    public function handle(AuthUserCommand $command) {
        if (empty($command->email) || empty($command->pass)) {
            throw new Exception("Id, Email e Password sono obbligatori");
        }

        // $user = $repository.getUserFromEmail($command->email);

        if (empty($user)) {
            return null;
        }

        if ($user->pass == $command->pass){
            // i should return the session token
        }
    }
}