<?php

require_once __DIR__ . '/../../../Infrastructure/Persistence/Repositories/LoanRepository.php';
require_once __DIR__ . '/../../../Infrastructure/Persistence/Repositories/LoanedObjectRepository.php';
require_once __DIR__ . '/../../../Infrastructure/Persistence/Repositories/ClientRepository.php';
require_once __DIR__ . '/../../../../config/Database.php';

require_once __DIR__ . '/../../Commands/Loans/ViewSingleLoanCommand.php';

class ViewSingleLoanHandler {
    private $loanRepository;
    private $loanedObjectRepository;
    private $clientRepository;

    public function __construct() {
        $db = new Database();
        $connection = $db->getConnection();

        $this->loanRepository = new LoanRepository($connection);
        $this->loanedObjectRepository = new LoanedObjectRepository($connection);
        $this->clientRepository = new ClientRepository($connection);
    }

    public function handle(ViewSingleLoanCommand $command) {

    try{

        if(empty($command->id)) {
            throw new Exception("L'id del Prestito non puo' essere vuoto");
        }
            
        $loan = $this->loanRepository->getLoanById($command->id);
            
        if (empty($loan)){
            return null;
        }
           
        // I also want the client information
        $client = $this->clientRepository->findById($loan->getClientId());

        $loan->client = $client;
        
        // I also want the loanedObjects of the loan
        $loanedObjects = $this->loanedObjectRepository->getLoanedObjectsByLoanId($loan->getId());

        $loan->loanedObjects = $loanedObjects;

        return $loan;

        }catch(Exception $ex) {
            throw new Exception("Si e' verificato un errore durante il recupero del Prestito: " . $ex->getMessage(), 0, $ex);
        }
    }
}