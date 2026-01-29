<?php

require_once __DIR__ . '/../../../../config/Database.php';

require_once __DIR__ . '/../../../Domain/Entities/Client.php';

require_once __DIR__ . '/../../Commands/Clients/UpdateClientCommand.php';

require_once __DIR__ . '/../../../Infrastructure/Persistence/Repositories/ClientRepository.php';


class UpdateClientHandler {
    private $clientRepository;

    public function __construct() {
        $db = new Database();
        $this->clientRepository = new ClientRepository($db->getConnection());
    }

    // Handle the UpdateClientCommand 
    public function handle(UpdateClientCommand $command) {
        
        try {

            if (empty($command->id) || empty($command->name) || empty($command->surname)) {
                throw new Exception("ID cliente, nome e cognome sono campi obbligatori.");
            }

            $client = $this->clientRepository->findById($command->id);

            if(empty($client)) {
                throw new Exception("Non esiste un Client con l'id specificato");
            }

            $clientToUpdate = new Client($command->id, $client->getEmail(), $command->name, $command->surname, 1);
            
            $this->clientRepository->update($clientToUpdate);

        }catch(Exception $ex) {
            throw new Exception("Si e' verificato un errore durante la modifica del Cliente: " . $ex->getMessage(), 0, $ex);
        }
      
    }
}
    
