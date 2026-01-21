<?php

// require_once __DIR__ . '/../../../Infrastructure/LoanRepository.php';
require_once __DIR__ . '/../../Commands/Loans/CreateLoanCommand.php';
class CreateLoanHandler {
    private $repository;


    public function __construct() {
        // $repository = new LoanRepository();
    }

    // Handle the CreateLoanCommand
    public function handle(CreateLoanCommand $command) {
       
        if (empty($command->start_date) || empty($command->end_date) || empty($command->client_id)) {
             throw new Exception("Data di inizio e Id Cliente sono obbligatori");
        }

        $start = new DateTime($command->start_date);
        $end = new DateTime($command->end_date);
        
        if($start > $end) {
            throw new Exception("La Data di inizio non puo' essere successiva a quella di fine");
        }

        $interval = $start->diff($end);

        // i check if end_date - start_date <= 30d
        if ($interval->days > 30) {
            throw new Exception("La Data di fine non puo' essere successiva alla Data di inizio di piu' di 30 giorni")
        }

        // $createdLoan = $this->repository.CreateLoan($command);
      
        // if(empty($createdLoan))  {
        //     return null;
        // }

        // return $createdLoan->Id;
    }
}