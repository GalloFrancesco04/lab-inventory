<?php

require_once __DIR__ . '/../../../Infrastructure/Persistence/Repositories/ClientRepository.php';
require_once __DIR__ . '/../../../Infrastructure/Persistence/Repositories/LoanRepository.php';
require_once __DIR__ . '/../../../../config/Database.php';

require_once __DIR__ . '/../../Commands/Clients/ViewClientsCommand.php';


class ViewClientsHandler {
    private $clientRepository;
    private $loanRepository;

    public function __construct() {
        $db = new Database();
        $connection = $db->getConnection();

        $this->clientRepository = new ClientRepository($connection);
        $this->loanRepository = new LoanRepository($connection);
    }

    public function handle(ViewClientsCommand $command) {

        try{

            $clients = $this->clientRepository->getAll();    
                
            if(empty($clients)) {
                return [];
            }

            // i also want to return all the loans of client
            foreach($clients as $client) {
                $clientLoans = $this->loanRepository->getAllLoansOfClient($client->getId());
                $client->loans = $clientLoans;
            }


            return $clients;

        }catch(Exception $ex) {
             throw new Exception("Si e' verificato un errore durante il recupero dei Clienti: " . $ex->getMessage(), 0, $ex);
        }
    }
}