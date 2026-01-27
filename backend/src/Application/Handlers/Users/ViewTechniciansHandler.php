<?php

require_once __DIR__ . '/../../../Infrastructure/Persistence/Repositories/UserRepository.php';
require_once __DIR__ . '/../../../../config/Database.php';

require_once __DIR__ . '/../../Commands/Users/ViewTechniciansCommand.php';


class ViewTechniciansHandler {
    private $userRepository;

    public function __construct() {
        $db = new Database();

        $this->userRepository = new UserRepository($db->getConnection());
    }

    public function handle(ViewTechniciansCommand $command) {

        try {

            $technicians = $this->userRepository->getAllTechnicians();
            
            if(empty($technicians)) {
                return [];
            }

            // I don't want to return the password do the client
            foreach($technicians as $tech) {
                $tech->setPass('');
            }

            return $technicians;

        }catch(Exception $ex) {
             throw new Exception("Si e' verificato un errore durante il recupero dei Tecnici: " . $ex->getMessage(), 0, $ex);
        }
    }
}