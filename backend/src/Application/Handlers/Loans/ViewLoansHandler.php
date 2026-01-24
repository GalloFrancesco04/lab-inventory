<?php

require_once __DIR__ . '/../../../Infrastructure/Persistence/LoanRepository.php';
require_once __DIR__ . '/../../../../config/Database.php';

require_once __DIR__ . "../../Commands/ViewLoansCommand.php"


class ViewLoansHandler {
    private $repository;

    public function __construct() {
        $db = new Database();

        $this->repository = new LoanRepository($db->conn);
    }

    public function handle(ViewLoansCommand $command) {

         $loans = $this->repository->getAllLoansOfUser($command->user_id);

        if (empty($loans)){
         throw new Exception("Si e' verificato un problema durante il recupero dei Prestiti");
        }

        return $loans;
    }
}