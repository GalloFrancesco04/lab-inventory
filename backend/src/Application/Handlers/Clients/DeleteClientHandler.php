<?php

require_once __DIR__ . '/../../../../config/Database.php';
require_once __DIR__ . '/../../Commands/Clients/DeleteClientCommand.php';
require_once __DIR__ . '/../../../Infrastructure/Persistence/Repositories/ClientRepository.php';
require_once __DIR__ . '/../../../Infrastructure/Persistence/Repositories/LoanRepository.php';


class DeleteClientHandler {

    private $clientRepository;
    private $loanRepository;

    public function __construct() {
        $db = new Database();
        $connection = $db->getConnection();

        $this->clientRepository = new ClientRepository($connection);
        $this->loanRepository = new LoanRepository($connection);
    }

    public function handle(DeleteClientCommand $command) {

        try {
            if(empty($command->id)) {
                throw new Exception("L'id non puo' essere vuoto");
            }

            $client = $this->clientRepository->findById($command->id);

            if(empty($client)) {
                throw new Exception("Non esiste un Client con l'id specificato");
            }

            // loans keep the client's history, so a client with loans cannot be deleted
            if(!empty($this->loanRepository->getAllLoansOfClient($command->id))) {
                throw new Exception("Non e' possibile eliminare un Cliente che ha dei Prestiti");
            }

            $this->clientRepository->delete($command->id);

        }catch(Exception $ex) {
            throw new Exception("Si e' verificato un errore durante l'eliminazione del Cliente: " . $ex->getMessage(), 0, $ex);
        }
    }
}
