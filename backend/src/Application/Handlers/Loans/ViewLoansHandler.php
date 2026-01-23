<?php

// require_once __DIR__ . '/../../../Infrastructure/LoanRepository.php';
require_once __DIR__ . "../../Commands/ViewLoansCommand.php"


class ViewLoansHandler {

    private $repository;

    public function __construct() {
        $this->repository = new LoanRepository();
    }

    public function handle(ViewLoansCommand $command) {

        // $loans = $this->repository->GetAllLoansOfUser($command->user_id);

        // if (empty($loans)){
        //  throw new Exception("Si e' verificato un problema durante il recupero dei prestiti");
        //}

        // return $loans;
    }
}