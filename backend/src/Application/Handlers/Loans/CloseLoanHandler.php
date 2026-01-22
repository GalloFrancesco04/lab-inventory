<?php

// require_once __DIR__ . '/../../../Infrastructure/LoanRepository.php';
require_once __DIR__ . "../../Commands/CloseLoanCommand.php"

class CloseLoanHandler {
    // private $repository;

    public function __construct() {
    //    $this->repository = new LoanRepository();
    }

    public function handle(CloseLoanCommand $command) {
       
        if(empty($command->id) || empty($command->return_date)) {
            throw new Exception("L'Id e la Data di restituzione non possono essere vuoti");
        }

        // devo chiudere il loan mettendo come return_date la data attuale, lo stato del loan viene aggiornato dal repo

        // $result = $this->repository.CloseLoan($command->id, $command->return_date);

        // if(empty($result)) {
        //     throw new Exception("Si e' verificato qualche problema durante la chiusura del prestito");
        // }

        // return $result;
    }
}