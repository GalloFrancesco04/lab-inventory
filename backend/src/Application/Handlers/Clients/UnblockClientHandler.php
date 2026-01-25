<?php

require_once __DIR__ . '/../../../../config/Database.php';
require_once __DIR__ . '/../../Commands/Clients/UnblockClientCommand.php';
require_once __DIR__ . '/../../../Infrastructure/Persistence/Repositories/ClientRepository.php';


class UnblockClientHandler {
    
    private $clientRepository;

    public function __construct() {
        $db = new Database();

        $this->clientRepository = new ClientRepository($db->getConnection());

    }
    

    public function handle(UnblockClientCommand $command) {
  
        try{
            
        if(empty($command->id)) {
            throw new Exception("L'id non puo' essere vuoto");
        }

        $client = $this->clientRepository->findById($command->id);
        
        if(empty($client)) {
            throw new Exception("Non esiste un Client con l'id specificato");
        }


            $this->clientRepository->unblockClient($command->id);
        }catch(Exception $ex) {
            throw new Exception("Si e' verificato un errore durante lo sblocco del Cliente: " . $ex->getMessage(), 0, $ex);

        }

    }
}