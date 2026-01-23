<?php

// require_once __DIR__ . '/../../../Infrastructure/LoanRepository.php';
// require_once __DIR__ . '/../../../Infrastructure/LoanedObjectRepository.php';
// import Transaction
require_once __DIR__ . "../../Commands/UpdateLoanCommand.php"

class UpdateLoanHandler {
    // private $loanRepository;
    // private $loanedObjectRepository;

    public function __construct() {
    //    $this->loanRepository = new LoanRepository();
    //    $this->loanedObjectRepository = new LoanedObjectRepository();
    }

    public function handle(UpdateLoanCommand $command) {
       
        if(empty($command->id)) {
            throw new Exception("L'Id non puo' essere vuoto");
        }

        
        // $loanToUpdate = $this->loanRepository->GetLoan($command->id);
        
        // if(empty($loanToUpdate)){
        //     throw new Exception("Il Prestito non e' stato trovato");
        //}

        // if($loanToUpdate->state == 0) {
        //     throw new Exception("Non e' possibile aggiornare un prestito chiuso");
        // }
        
          
        // $transaction = new Transaction();
        // $transaction->start();
        try {
        
            // foreach($command->objects as $updatedObject) {
            
            // $loanedObject = $this->loanedObjectRepository->GetLoanedObject($updatedObject->object_id, $loanToUpdate->id);
            
            // the object is a new one so I have to add it
            // if(empty($loanedObject)) {
            
            //  if(count($loanToUpdate->quantity) < 5) {
             
            // $createdLoanedObject = $this->loanedObjectRepository->CreateLoanedObject($loanToUpdate->id, $updatedObject);

            // if(empty($createdLoanedObject)){
            // continue;
            //}

            //   $loanToUpdate->quantity++;
            
            //   continue;
            
            //  }else {
            //
            //   reached the maximum quantity of the loan
            //   continue;
            //
            //  }
            //}
                    
            //}

            // here the object exists and only the status has changed
            // if ($loanedObject->damaged != $updatedObject->damaged){
            //     $this->loanedObjectRepository->UpdateLoanedObject($updatedObject);
            // }

            //}   
            // $transaction->commit();   

            }catch(Exception ex) {

            // $transaction->rollback();
            // throw new Exception("Si e' verificato qualche problema nella chiusura del Prestito");
            }

        // return $loanToUpdate;
    }
}