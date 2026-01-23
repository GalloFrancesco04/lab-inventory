<?php

// require_once __DIR__ . '/../../../Infrastructure/LoanRepository.php';
// require_once __DIR__ . '/../../../Infrastructure/LoanedObjectRepository.php';
// require_once __DIR__ . '/../../../Infrastructure/ObjectRepository.php';
// require Transaction
require_once __DIR__ . "../../Commands/CloseLoanCommand.php"

class CloseLoanHandler {
    // private $repository;
    // private $loanedObjectRepository;
    // private $objectRepository

    public function __construct() {
    //    $this->loanRepository = new LoanRepository();
    }

    public function handle(CloseLoanCommand $command) {
       
        if(empty($command->id)) {
            throw new Exception("L'Id non puo' essere vuoto");
        }

        // i close the loan and put the return date as today
        // after that I mark the objects as available

        
        // $loanToClose = $this->loanRepository->GetLoan($command->id);
        
        // if(empty($loanToClose)){
        //     throw new Exception("Il Prestito non e' stato trovato");
        //}
        
        // $returnDate = new DateTime(now);
          
        // $transaction = new Transaction();
        // $transaction->start();
        try {

            // $result = $this->loanRepository->CloseLoan($loanToClose->id, $returnDate);
            
            // if(empty($result)) {
            //     throw new Exception("Si e' verificato qualche problema durante la chiusura del Prestito");
            // }
                
            // I want to mark the object state as 'available'
            // $loanedObjects = $this->loanedObjectRepository->GetLoanedObjectsFromLoanId($loanToClose->id);
                
            // foreach($loanedObjects as $loanedObject) {
            
            // if ($loanedObject->is_damaged) 
            // {
                // $damagedResult = $this->objectRepository->MarkObjectAsDamaged($loanedObject->object_id);

                // if(empty($damagedResult)) {
                // the object is not marked as damaged
                // continue;
                //}

                // continue;

            //}

            // $availableResult = $this->objectRepository->MarkObjectAsAvailable($loanedObject->object_id);
                
            // if(empty($availableResult)){
            // the object is not makrked as available
            // continue;
            //}
                    
            //}
            // $transaction->commit();   

            }catch(Exception ex) {

            // $transaction->rollback();
            // throw new Exception("Si e' verificato qualche problema nella chiusura del Prestito");
            }

        // return $result;
    }
}