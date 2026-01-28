<?php

require_once __DIR__ . '/../../../Infrastructure/Security/SessionManager.php';
require_once __DIR__ . '/../../Commands/Auth/LogoutUserCommand.php';

class LogoutUserHandler {

     private $session;

    public function __construct() {

        $this->session = new SessionManager();
    }

    public function handle(LogoutUserCommand $command) {
        try {

            $this->session->logout();

        }catch(Exception $ex) {
            throw new Exception("Non e' stato possibile fare il Logout per l'Utente: " . $ex->getMessage(), 0, $ex);

        }
    }
}