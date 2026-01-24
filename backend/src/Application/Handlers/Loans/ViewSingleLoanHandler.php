<?php

require_once __DIR__ . '/../../../Infrastructure/Persistence/LoanRepository.php';
require_once __DIR__ . '/../../../../config/Database.php';

require_once __DIR__ . "../../Commands/ViewSingleLoanCommand.php"


class ViewSingleLoanHandler {
    private $repository;

    public function __construct() {
        $db = new Database();

        $this->repository = new LoanRepository($db->conn);
    }

    public function handle(ViewSingleLoanCommand $command) {

        if(empty($command->id)) {
            throw new Exception("L'id del Prestito non puo' essere vuoto");
        }

        $loan = $this->repository->getLoanById($command->id);

        if (empty($loan)){
         throw new Exception("Si e' verificato un problema durante il recupero del Prestito");
        }

        return $loan;
    }
}