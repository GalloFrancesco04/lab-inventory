<?php

require_once __DIR__ . '/../../../Infrastructure/Persistence/Repositories/LoanRepository.php';
require_once __DIR__ . '/../../../Infrastructure/Persistence/Repositories/ClientRepository.php';
require_once __DIR__ . '/../../../../config/Database.php';

require_once __DIR__ . '/../../Commands/Loans/ViewLoansCommand.php';


class ViewLoansHandler {
    private $loanRepository;
    private $clientRepository;

    public function __construct() {
        $db = new Database();
        $connection = $db->getConnection();

        $this->loanRepository = new LoanRepository($connection);
        $this->clientRepository = new ClientRepository($connection);
    }

    public function handle(ViewLoansCommand $command) {

        try {

            $loans = $this->loanRepository->getAll();
            
            if(empty($loans)) {
                return [];
            }

            foreach($loans as $loan) {
                $client = $this->clientRepository->findById($loan->getClientId());
                $loan->client = $client;
            }

            return $loans;

        }catch(Exception $ex) {
             throw new Exception("Si e' verificato un errore durante il recupero dei Prestiti: " . $ex->getMessage(), 0, $ex);
        }
    }
}