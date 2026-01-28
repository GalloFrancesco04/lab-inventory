<?php

require_once __DIR__ . '/../../../Infrastructure/Persistence/Repositories/UserRepository.php';
require_once __DIR__ . '/../../../Infrastructure/Security/SessionManager.php';
require_once __DIR__ . '/../../../Infrastructure/Security/PasswordHasher.php';
require_once __DIR__ . '/../../Commands/Auth/AuthUserCommand.php';

require_once __DIR__ . '/../../../../config/Database.php';

class LoginUserHandler {

     private $userRepository;
     private $session;
     private $passwordHasher;

    public function __construct() {

        $db = new Database();

        $this->userRepository = new UserRepository($db->getConnection());
        $this->session = new SessionManager();
        $this->passwordHasher = new PasswordHasher();
    }

    public function handle(AuthUserCommand $command) {
        try {

        if (empty($command->email) || empty($command->pass)) {
            throw new Exception("Email e Password sono obbligatori");
        }

         $user = $this->userRepository->findUserByEmail($command->email);


        // I don't return error messages with infos for security reasons
        if (empty($user)) {
            throw new Exception();
        }


        if (!$this->passwordHasher->verifyPassword($command->pass, $user->getPass())){
            throw new Exception("Credenziali non valide");
        }

        $this->session->setUser($user->getId(), $user->getEmail(), $user->getRole());

        }catch(Exception $ex) {
            throw new Exception("Non e' stato possibile fare il Login per l'Utente: " . $ex->getMessage(), 0, $ex);

        }
    }
}