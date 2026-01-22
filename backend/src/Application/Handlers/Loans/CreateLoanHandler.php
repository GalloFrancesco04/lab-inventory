<?php

// require_once __DIR__ . '/../../../Infrastructure/LoanRepository.php';
// require_once __DIR__ . '/../../../Infrastructure/ObjectRepository.php';
// import Transaction
require_once __DIR__ . '/../../Commands/Loans/CreateLoanCommand.php';
class CreateLoanHandler {
    // private $repository;


    public function __construct() {
        // $repository = new LoanRepository();
    }

    // Handle the CreateLoanCommand
    public function handle(CreateLoanCommand $command) {
       
        if (empty($command->start_date) || empty($command->end_date) empty($command->client_id)) {
             throw new Exception("Data di inizio, Data di fine e Id Cliente sono obbligatori");
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

        // loan fields are valid here

        //$transaction = new Transaction();
        // $transaction->start();
        try{
            // $client = $this->repository->GetClient($command->client_id);
            // if(empty($client)) {
            //     throw new Exception("Il Cliente specificato non esiste");
            // }
                
            // $createdLoan = $this->repository->CreateLoan($command);
                
            // if(empty($createdLoan))  {
            //     throw new Exception("Si e' verificato qualche problema nella creazione del Prestito");
            // }
                    
            // for each object of the loan I have to insert a new loanedObject
            // for($command->objects as $object){
                // $createdLoanedObject = $this->repository->CreateLoanedObject($createdLoan->id, $object);

                // if (empty($createdLoanedObject)){
                //     // print loaned object not created without launchig an error
                //     continue;
                //}

                // mark object as loaned
                // $this->repository->MarkObjectAsLoaned($createdLoanedObject->object_id);
            //}

           // $transaction->commit();

        }catch(Exception ex) {
            // $transaction->rollback();
            throw new Exception("Si e' verificato qualche problema nella creazione del Prestito");
        }

        // return $createdLoan->Id;
    }
}