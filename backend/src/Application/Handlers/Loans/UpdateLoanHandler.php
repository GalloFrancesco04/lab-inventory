<?php

require_once __DIR__ . '/../../../Infrastructure/Persistence/Repositories/LoanRepository.php';
require_once __DIR__ . '/../../../Infrastructure/Persistence/Repositories/LoanedObjectRepository.php';
require_once __DIR__ . '/../../../Infrastructure/Persistence/Repositories/ObjectTypeRepository.php';


require_once __DIR__ . '/../../../../config/Database.php';
require_once __DIR__ . '/../../../Infrastructure/Persistence/Connection/TransactionManager.php';

require_once __DIR__ . '/../../Commands/Loans/UpdateLoanCommand.php';

class UpdateLoanHandler {
    private $loanRepository;
    private $loanedObjectRepository;
    private $objectTypeRepository;
    private $transaction;

    public function __construct() {
        $db = new Database();
        $connection = $db->getConnection();

        $this->loanRepository = new LoanRepository($connection);
        $this->loanedObjectRepository = new LoanedObjectRepository($connection);
        $this->objectTypeRepository = new ObjectTypeRepository($connection);

        $this->transaction = new TransactionManager($connection);
    }

    public function handle(UpdateLoanCommand $command) {
       

        try {
            if(empty($command->id)) {
                throw new Exception("L'Id non puo' essere vuoto");
            }

            $loanToUpdate = $this->loanRepository->getLoanById($command->id);
            
            if(empty($loanToUpdate)){
                throw new Exception("Il Prestito non e' stato trovato");
            }

            if($loanToUpdate->getState() == 0) {
                throw new Exception("Non e' possibile aggiornare un prestito chiuso");
            }
                
            $loanToUpdate->setNotes($command->notes);
            
            // here I have laidated the fileds and updated the notes (locally)
            
            $this->transaction->begin();

            foreach($command->objects as $updatedObject) {
            
                $loanedObject = $this->loanedObjectRepository->getLoanedObject($loanToUpdate->getId(), $updatedObject->getId());
                
                //the object is a new one so I have to add it
                if(empty($loanedObject)) {
                
                    if($loanToUpdate->getQuantity() < 5) {
                    
                         // I check if the quantity of the object_type is > 0 and if it is I loan it if not I return an error
                        $objectType = $this->objectTypeRepository->findByCode($updatedObject->getObjectTypeCode());

                        if(empty($objectType)) {
                            throw new Exception("Non e' stato trovato un object type");
                        }

                        if($objectType->getQuantity() == 0) {
                            throw new Exception("Non e' possibile prendere in prestito l'oggetto con type code: " . $objectType->getCode());
                        }

                        // I check if the user passes an object_id with no correspondence to the objecttype
                        $objectFromDb = $this->itemRepository->findById($object->getId());

                        if(empty($objectFromDb)) {
                            throw new Exception("Non esiste un oggetto con l'id specificato");
                        }

                        if($objectFromDb->getObjectTypeCode() != $objectType->getCode()) {
                            throw new Exception("L'id dell'oggetto e il codice dell'object type non corrispondono a nessun oggetto");
                        }

                        $tmp = new LoanedObject($objectType->getCode(), $objectFromDb->getId(), $loanToUpdate->getId(), 0);

                        $createdLoanedObject = $this->loanedObjectRepository->create($loanToUpdate->getId(), $updatedObject);

                        // the object is correct and the quantity is >= 0 so I decrement it on db
                        $objectType->removeQuantity(1);
                        $this->objectTypeRepository->updateQuantity($objectType->getCode(), $objectType->getQuantity());

                        // now that the object is added I increase the quantity of objects in loan
                        $loanToUpdate->setQuantity($loanToUpdate->getQuantity() + 1);
                        continue;
                    
                    }else {
                    
                        //reached the maximum quantity of the loan
                        continue;
                    }
                    
                    //here the object exists and only the status has changed
                    // NOTE: this only allows to put it as damaged, not to have a damaged object ant make it loaned
                    if ($loanedObject->isDamaged() != $updatedObject->damaged)
                        $this->loanedObjectRepository->markAsDamaged($loanToUpdate->getId(), $updatedObject->id);
                }

            }   

            $this->loanRepository->update($loanToUpdate);

            $this->transaction->commit();   
            
            return $loanToUpdate;
        }catch(Exception $ex) {
            $this->transaction->rollback();
            throw new Exception("Si e' verificato un errore durante l'aggiornamento del Prestito: " . $ex->getMessage(), 0, $ex);
        }

    }
}