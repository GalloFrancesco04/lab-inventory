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
  
        if(empty($command->id)) {
            throw new Exception("L'id non puo' essere vuoto");
        }

        try{

            $this->clientRepository->unblockClient($command->id);
        }catch(Exception $ex) {
            throw new Exception("C'e' stato un problema durante lo sblocco del Cliente");
        }

    }
}