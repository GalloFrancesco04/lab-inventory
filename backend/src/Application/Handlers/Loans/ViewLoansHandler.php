<?php

require_once __DIR__ . '/../../../Infrastructure/Persistence/Repositories/LoanRepository.php';
require_once __DIR__ . '/../../../../config/Database.php';

require_once __DIR__ . '/../../Commands/Loans/ViewLoansCommand.php';


class ViewLoansHandler {
    private $loanRepository;

    public function __construct() {
        $db = new Database();

        $this->loanRepository = new LoanRepository($db->getConnection());
    }

    public function handle(ViewLoansCommand $command) {

        try {

            $loans = $this->loanRepository->getAll();
            
            if(empty($loans)) {
                return [];
            }

            return $loans;

        }catch(Exception $ex) {
             throw new Exception("Si e' verificato un errore durante il recupero dei Prestiti: " . $ex->getMessage(), 0, $ex);
        }
    }
}