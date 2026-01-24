<?php

require_once __DIR__ . '/../../../Infrastructure/Persistence/Repositories/LoanRepository.php';
require_once __DIR__ . '/../../../Infrastructure/Persistence/Repositories/ItemRepository.php';
// require_once __DIR__ . '/../../../Infrastructure/Persistence/Repositories/LoanedItemRepository.php';

require_once __DIR__ . '/../../../../config/Database.php';
require_once __DIR__ . '/../../../Infrastructure/Persistence/Connection/TransactionManager.php';

require_once __DIR__ . '/../../../Domain/States/AvailableState.php';
require_once __DIR__ . '/../../../Domain/States/DamagedState.php';

require_once __DIR__ . '/../../Commands/Loans/CloseLoanCommand.php';

class CloseLoanHandler {
    private $loanRepository;
    // private $loanedItemRepository;
    private $itemRepository;
    private $transaction;

    public function __construct() {
        $db = new Database();

        $this->loanRepository = new LoanRepository($db->getConnection());
        $this->itemRepository = new ItemRepository($db->getConnection());
        // $loanedItemRepository = new LoanedItemRepository($db->getConnection());

        $this->transaction = new TransactionManager($db->getConnection());
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
          
        $this->transaction->begin();
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

                $this->itemRepository->updateState($loanedObject->object_id, (new DamagedState())->getCode());
                continue;

            }

            // I release the non damaged objects
            $this->itemRepository->updateState($loanedObject->object_id, (new AvailableState())->getCode());
                    
            }

            $this->transaction->commit();   

            }catch(Exception $ex) {

                $this->transaction->rollback();
                throw new Exception("Si e' verificato qualche problema nella chiusura del Prestito");
            }

        return $result;
    }
}