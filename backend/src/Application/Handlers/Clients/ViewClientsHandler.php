<?php

require_once __DIR__ . '/../../../Infrastructure/Persistence/Repositories/ClientRepository.php';
require_once __DIR__ . '/../../../../config/Database.php';

require_once __DIR__ . '/../../Commands/Clients/ViewClientsCommand.php';


class ViewClientsHandler {
    private $repository;

    public function __construct() {
        $db = new Database();

        $this->repository = new ClientRepository($db->getConnection());
    }

    public function handle(ViewClientsCommand $command) {

        try{

            $clients = $this->repository->getAll();    
                
            return $clients;

        }catch(Exception $ex) {
             throw new Exception("Si e' verificato un errore durante il recupero dei Clienti: " . $ex->getMessage(), 0, $ex);
        }
    }
}