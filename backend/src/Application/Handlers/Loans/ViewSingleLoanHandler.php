<?php

require_once __DIR__ . '/../../../Infrastructure/Persistence/Repositories/LoanRepository.php';
require_once __DIR__ . '/../../../../config/Database.php';

require_once __DIR__ . '/../../Commands/Loans/ViewSingleLoanCommand.php';

class ViewSingleLoanHandler {
    private $loanRepository;

    public function __construct() {
        $db = new Database();

        $this->loanRepository = new LoanRepository($db->getConnection());
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
                
        return $loan;

        }catch(Exception $ex) {
            throw new Exception("Si e' verificato un errore durante il recupero del Prestito: " . $ex->getMessage(), 0, $ex);
        }
    }
}