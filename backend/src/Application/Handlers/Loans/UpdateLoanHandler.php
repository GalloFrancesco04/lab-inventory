<?php

require_once __DIR__ . '/../../../Infrastructure/Persistence/Repositories/LoanRepository.php';
// require_once __DIR__ . '/../../../Infrastructure/Persistence/LoanedItemRepository.php';


require_once __DIR__ . '/../../../../config/Database.php';
require_once __DIR__ . '/../../../Infrastructure/Persistence/Connection/TransactionManager.php';

require_once __DIR__ . "/../../Commands/Loans/UpdateLoanCommand.php";

class UpdateLoanHandler {
    private $loanRepository;
    // private $loanedObjectRepository;
    private $transaction;

    public function __construct() {
        $db = new Database();

        $this->loanRepository = new LoanRepository($db->conn);
        // $this->loanedObjectRepository = new LoanedObjectRepository($db->conn);

        $this->transaction = new TransactionManager($db->conn);
    }

    public function handle(UpdateLoanCommand $command) {
       
        if(empty($command->id)) {
            throw new Exception("L'Id non puo' essere vuoto");
        }

        
        $loanToUpdate = $this->loanRepository->getLoanById($command->id);
        
        if(empty($loanToUpdate)){
            throw new Exception("Il Prestito non e' stato trovato");
        }

        $loanToUpdate->setNotes($command->notes);

        if($loanToUpdate->state == 0) {
            throw new Exception("Non e' possibile aggiornare un prestito chiuso");
        }
        
        
        $this->transaction->begin();

        try {
        
            foreach($command->objects as $updatedObject) {
            
                // $loanedObject = $this->loanedObjectRepository->getLoanedObject($updatedObject->object_id, $loanToUpdate->id);
                
                $loanedObject = null;

                //the object is a new one so I have to add it
                if(empty($loanedObject)) {
                
                    if(count($loanToUpdate->quantity) < 5) {
                    
                        // $createdLoanedObject = $this->loanedObjectRepository->CreateLoanedObject($loanToUpdate->id, $updatedObject);

                        $createdLoanedObject = null;

                        // now that the object is added I increase the quantity of objects in loan
                        $loanToUpdate->quantity++;
                        continue;
                    
                    }else {
                    
                        //reached the maximum quantity of the loan
                        continue;
                    }
                    
                    //here the object exists and only the status has changed
                    // if ($loanedObject->damaged != $updatedObject->damaged)
                    //     $this->loanedObjectRepository->UpdateLoanedObject($updatedObject);
                }

            }   

            $this->loanRepository->update($loanToUpdate);

            $this->transaction->commit();   

        }catch(Exception $ex) {
            $this->transaction->rollback();
            throw new Exception("Si e' verificato qualche problema nella chiusura del Prestito");
        }

        return $loanToUpdate;
    }
}