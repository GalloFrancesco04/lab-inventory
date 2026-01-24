<?php

require_once __DIR__ . '/../../../Infrastructure/Persistence/Repositories/UserRepository.php';
require_once __DIR__ . '/../../Commands/Auth/AuthUserCommand.php';

require_once __DIR__ . '/../../../../config/Database.php';

class AuthUserHandler {

     private $userRepository;

    public function __construct() {

        $db = new Database();

        $userRepository = new UserRepository($db->getConnection());
    }

    public function handle(AuthUserCommand $command) {
        if (empty($command->email) || empty($command->pass)) {
            throw new Exception("Id, Email e Password sono obbligatori");
        }

         $user = $this->repository.findUserByEmail($command->email);

        if (empty($user)) {
            throw new Exception("L'Utente non esiste");
        }



        if ($user->pass != $command->pass){
            throw new Exception("La Password e' sbagliata");
        }

        // here the password is correct so I create a new session and return it

        // try {
        
            // $this->userRepository->createSession($user);

            // return;
        // }catch(Exception $ex) {
            throw new Exception("Non e' stato possibile autenticare l'utente");
        // }
    }
}