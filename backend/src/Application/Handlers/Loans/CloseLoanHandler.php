<?php

require_once __DIR__ . '/../../../Infrastructure/Persistence/LoanRepository.php';
require_once __DIR__ . '/../../../Infrastructure/Persistence/ItemRepository.php';
// require_once __DIR__ . '/../../../Infrastructure/Persistence/LoanedItemRepository.php';

require_once __DIR__ . '/../../../config/Database.php';
require_once __DIR__ . '/../../../Infrastructure/Persistence/Connection/TransactionManager.php';

require_once __DIR__ . '/../../../Domain/States/AvailableState()';
require_once __DIR__ . '/../../../Domain/States/DamagedState()';

require_once __DIR__ . '../../Commands/CloseLoanCommand.php';

class CloseLoanHandler {
    private $loanRepository;
    // private $loanedItemRepository;
    private $itemRepository
    private $transaction;

    public function __construct() {
        $db = new Database();

        $loanRepository = new LoanRepository($db->conn);
        $itemRepository = new ItemRepository($db->conn);
        // $loanedItemRepository = new LoanedItemRepository();

        $transaction = new TransactionManager($db->conn);
    }

    public function handle(CloseLoanCommand $command) {
       
        if(empty($command->id)) {
            throw new Exception("L'Id non puo' essere vuoto");
        }

        // i close the loan and put the return date as today
        // after that I mark the objects as available or damaged if the loanedObject is damaged
        
        $loanToClose = $this->loanRepository->getLoanById($command->id);
        
        if(empty($loanToClose)){
            throw new Exception("Il Prestito non e' stato trovato");
        }
        
         $returnDate = new DateTime(now);
          
        $transaction->begin();
        try {

            $result = $this->loanRepository->closeLoan($loanToClose->id, $returnDate);
                
            // I want to mark the object state as 'available' or 'damaged' so first I get all the loanedObjects
            //$loanedObjects = $this->loanedItemRepository->getLoanedObjectsFromLoanId($loanToClose->id);
              
            $loanedObjects = null;

            foreach($loanedObjects as $loanedObject) {
            
            if ($loanedObject->is_damaged) 
            {
                $loanedObject->state = 1;

               // $this->loanedItemRepository->update($loanedObject);

                $this->itemRepository->updateState($loanedObject->object_id, new DamagedState()->getCode());
                continue;

            }

            // I release the non damaged objects
            $this->itemRepository->updateState($loanedObject->object_id, new AvailableState()->getCode());
                    
            }

            $transaction->commit();   

            }catch(Exception ex) {

                $transaction->rollback();
                throw new Exception("Si e' verificato qualche problema nella chiusura del Prestito");
            }

        return $result;
    }
}