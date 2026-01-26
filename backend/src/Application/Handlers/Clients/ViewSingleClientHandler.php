<?php

require_once __DIR__ . '/../../../Infrastructure/Persistence/Repositories/ClientRepository.php';
require_once __DIR__ . '/../../../../config/Database.php';

require_once __DIR__ . '/../../Commands/Clients/ViewSingleClientCommand.php';


class ViewSingleClientHandler {
    private $clientRepository;

    public function __construct() {
        $db = new Database();

        $this->clientRepository = new ClientRepository($db->getConnection());
    }

    public function handle(ViewSingleClientCommand $command) {

        try{

            if(empty($command->client_id)) {
                throw new Exception("L'Id del cliente e' un campo obbligatorio");
            }

            $client = $this->clientRepository->findById($command->client_id);    
                
            if(empty($client)) {
                return null;
            }

            return $client;

        }catch(Exception $ex) {
             throw new Exception("Si e' verificato un errore durante il recupero del Cliente: " . $ex->getMessage(), 0, $ex);
        }
    }
}