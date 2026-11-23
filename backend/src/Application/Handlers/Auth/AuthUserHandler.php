<?php

require_once __DIR__ . '/../../../Infrastructure/Security/SessionManager.php';
require_once __DIR__ . '/../../Commands/Auth/AuthUserCommand.php';

class AuthUserHandler {

     private $session;

    public function __construct() {

        $this->session = new SessionManager();
    }

    public function handle(AuthUserCommand $command) {

        try { 
        
            if (!$this->session->isLoggedIn()) {
                // Il browser non ha inviato un cookie valido o la sessione è scaduta
               throw new Exception("Il cookie e' sbagliato o la sessione e' scaduta, riautenticarsi");
            }

            return [
                'id' => $this->session->getUserId(),
                'email' => $this->session->getUserName(),
                'role' => $this->session->getUserRole(),
            ];


        }catch(Exception $ex) {
            throw new Exception("Non e' stato possibile autenticare l'Utente: " . $ex->getMessage(), 0, $ex);
        }
    }
}