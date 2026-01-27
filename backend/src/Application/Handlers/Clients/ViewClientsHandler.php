<?php

require_once __DIR__ . '/../../../Infrastructure/Persistence/Repositories/ClientRepository.php';
require_once __DIR__ . '/../../../../config/Database.php';

require_once __DIR__ . '/../../Commands/Clients/ViewClientsCommand.php';


class ViewClientsHandler {
    private $clientRepository;

    public function __construct() {
        $db = new Database();

        $this->clientRepository = new ClientRepository($db->getConnection());
    }

    public function handle(ViewClientsCommand $command) {

        try{

            $clients = $this->clientRepository->getAll();    
                
            if(empty($clients)) {
                return [];
            }

            return $clients;

        }catch(Exception $ex) {
             throw new Exception("Si e' verificato un errore durante il recupero dei Clienti: " . $ex->getMessage(), 0, $ex);
        }
    }
}