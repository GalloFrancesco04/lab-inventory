<?php

require_once __DIR__ . '/../../../Infrastructure/Persistence/Repositories/LoanRepository.php';
require_once __DIR__ . '/../../../Infrastructure/Persistence/Repositories/ItemRepository.php';
require_once __DIR__ . '/../../../Infrastructure/Persistence/Repositories/LoanedObjectRepository.php';
require_once __DIR__ . '/../../../Infrastructure/Persistence/Repositories/ObjectTypeRepository.php';

require_once __DIR__ . '/../../../../config/Database.php';
require_once __DIR__ . '/../../../Infrastructure/Persistence/Connection/TransactionManager.php';

require_once __DIR__ . '/../../../Domain/States/AvailableState.php';
require_once __DIR__ . '/../../../Domain/States/DamagedState.php';

require_once __DIR__ . '/../../Commands/Loans/CloseLoanCommand.php';

class CloseLoanHandler {
    private $loanRepository;
    private $itemRepository;
    private $loanedObjectRepository;
    private $objectTypeRepository;
    private $transaction;

    public function __construct() {
        $db = new Database();

        $connection = $db->getConnection();
        $this->loanRepository = new LoanRepository($connection);
        $this->itemRepository = new ItemRepository($connection);
        $this->loanedObjectRepository = new LoanedObjectRepository($connection);
        $this->objectTypeRepository = new ObjectTypeRepository($connection);

        $this->transaction = new TransactionManager($connection);
    }

    public function handle(CloseLoanCommand $command) {
       
        try {
                
            if(empty($command->id)) {
                throw new Exception("L'Id non puo' essere vuoto");
            }

            // i close the loan and put the return date as today
            // after that I mark the objects as available or damaged if the loanedObject is damaged
            
            $loanToClose = $this->loanRepository->getLoanById($command->id);
            
            if(empty($loanToClose)){
                throw new Exception("Il Prestito non e' stato trovato");
            }
            
            $returnDate = new DateTime('now');
            
            $this->transaction->begin();

            $this->loanRepository->closeLoan($loanToClose->getId(), $returnDate);
                    
            // I want to mark the object state as 'available' or 'damaged' so first I get all the loanedObjects
            $loanedObjects = $this->loanedObjectRepository->getLoanedObjectsByLoanId($loanToClose->getId());

            
            foreach($loanedObjects as $loanedObject) {
                
                if ($loanedObject->isDamaged()) 
                {
                    $this->itemRepository->updateState($loanedObject->getObjectId(), (new DamagedState())->getStateCode());
                    continue;

                }

                // I release the non damaged objects and increase the quantity the quantity
                $this->itemRepository->updateState($loanedObject->getObjectId(), (new AvailableState())->getStateCode());
                $objType = $this->objectTypeRepository->findByCode($loanedObject->getObjectTypeCode());
                $objType->addQuantity(1);
                $this->objectTypeRepository->updateQuantity($objType->getCode(), $objType->getQuantity());
            }


            $this->transaction->commit();   

            }catch(Exception $ex) {

                $this->transaction->rollback();
                 throw new Exception("Si e' verificato un errore durante la chiusura del Prestito: " . $ex->getMessage(), 0, $ex);
            }

        
    }
}