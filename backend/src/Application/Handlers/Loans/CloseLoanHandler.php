<?php

// require_once __DIR__ . '/../../../Infrastructure/LoanRepository.php';
// require_once __DIR__ . '/../../../Infrastructure/LoanedItemRepository.php';
// require_once __DIR__ . '/../../../Infrastructure/ItemRepository.php';
// require_once __DIR__ L "/../../../Domain/States/AvailableState();
// require_once __DIR__ L "/../../../Domain/States/DamagedState();
// require Transaction

require_once __DIR__ . "../../Commands/CloseLoanCommand.php"

class CloseLoanHandler {
    // private $repository;
    // private $loanedItemRepository;
    // private $itemRepository

    public function __construct() {
    //    $this->loanRepository = new LoanRepository();
    //    $this->loanedItemRepository = new LoanedItemRepository();
    //    $this->itemRepository = new ItemRepository();
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
                
            // I want to mark the object state as 'available' or 'damaged' so first I get all the loanedObjects
            // $loanedObjects = $this->loanedItemRepository->GetLoanedObjectsFromLoanId($loanToClose->id);
                
            // foreach($loanedObjects as $loanedObject) {
            
            // if ($loanedObject->is_damaged) 
            // {
                // $loanedObject->state = 0;

                // $this->loanedItemRepository->update($loanedObject);
                // $this->itemRepository->update($loanedObject->object_id, new DamagedState()->getCode());
                // continue;

            //}

            // I release the non damaged objects
            // $this->itemRepository->update($loanedObject->object_id, new AvailableState()->getCode());
                    
            //}
            // $transaction->commit();   

            }catch(Exception ex) {

            // $transaction->rollback();
            // throw new Exception("Si e' verificato qualche problema nella chiusura del Prestito");
            }

        // return $result;
    }
}