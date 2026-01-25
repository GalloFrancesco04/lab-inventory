<?php

require_once __DIR__ . '/../../../../config/Database.php';

require_once __DIR__ . '/../../../Domain/Entities/Client.php';

require_once __DIR__ . '/../../Commands/Clients/CreateClientCommand.php';
require_once __DIR__ . '/../../../Infrastructure/Persistence/Repositories/ClientRepository.php';

class CreateClientHandler {
    private $clientRepository;

    public function __construct() {
        $db = new Database();
        $this->clientRepository = new ClientRepository($db->getConnection());
    }

    public function handle(CreateClientCommand $command) {
        try {

        if (empty($command->name) || empty($command->email)|| empty($command->surname)) {
             throw new Exception("Nome, Email e Cognome sono obbligatori");
        }

        if($command->state < 0 || $command->state > 1) {
            throw new Exception("Stato Cliente inserito non ammesso");
        }

        $foundClient = $this->clientRepository->findByEmail($command->email);

        $clientToCreate = new Client(null, $command->email, $command->name, $command->surname, 1);

        $createdClientId = $this->clientRepository->create($clientToCreate);

        return $createdClientId;

        }catch(Exception $ex) {
            throw new Exception("Si e' verificato un errore durante la creazione del Cliente: " . $ex->getMessage(), 0, $ex);
        }
    }
}