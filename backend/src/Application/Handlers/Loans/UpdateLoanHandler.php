<?php

require_once __DIR__ . '/../../../Infrastructure/Persistence/Repositories/LoanRepository.php';
require_once __DIR__ . '/../../../Infrastructure/Persistence/Repositories/LoanedObjectRepository.php';
require_once __DIR__ . '/../../../Infrastructure/Persistence/Repositories/ObjectTypeRepository.php';
require_once __DIR__ . '/../../../Infrastructure/Persistence/Repositories/ItemRepository.php';


require_once __DIR__ . '/../../../../config/Database.php';
require_once __DIR__ . '/../../../Infrastructure/Persistence/Connection/TransactionManager.php';

require_once __DIR__ . '/../../Commands/Loans/UpdateLoanCommand.php';

class UpdateLoanHandler {
    private $loanRepository;
    private $loanedObjectRepository;
    private $objectTypeRepository;
    private $itemRepository;
    private $transaction;

    public function __construct() {
        $db = new Database();
        $connection = $db->getConnection();

        $this->loanRepository = new LoanRepository($connection);
        $this->loanedObjectRepository = new LoanedObjectRepository($connection);
        $this->objectTypeRepository = new ObjectTypeRepository($connection);
        $this->itemRepository = new ItemRepository($connection);

        $this->transaction = new TransactionManager($connection);
    }

    public function handle(UpdateLoanCommand $command) {
       

        try {
            if(empty($command->id)) {
                throw new Exception("L'Id non puo' essere vuoto");
            }

            if(count($command->objects) > 5) {
                throw new Exception("Non e' possibile aggiungere piu' di 5 Oggetti per Prestito");
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

            $currentLoanedObjectsOfLoan = $this->loanedObjectRepository->getLoanedObjectsByLoanId($loanToUpdate->getId());
            $found = false;
            if(count($command->objects) < count($currentLoanedObjectsOfLoan)) {
                foreach($currentLoanedObjectsOfLoan as $currentLoanedObject) {
                    $found = false;

                    foreach($command->objects as $updatedObject) {
                        if($updatedObject->getId() == $currentLoanedObject->getObjectId() && $updatedObject->getObjectTypeCode() == $currentLoanedObject->getObjectTypeCode()) {

                            // i update the state of the loanedobject
                            if($updatedObject->getStateCode() == 4) {
                                $this->loanedObjectRepository->markAsDamaged($loanToUpdate->getId(), $updatedObject->getId());
                            }

                            $found = true;
                            break;
                        }
                    }

                    // if I don't found it i remove it from loaned objects, i update the quantity in loan and also the quantity of objecttype available
                    if(!$found) {
                        $this->loanedObjectRepository->delete($currentLoanedObject->getLoanId(), $currentLoanedObject->getObjectId());

                        $objectType = $this->objectTypeRepository->findByCode($currentLoanedObject->getObjectTypeCode());

                        $objectFromDb = $this->itemRepository->findById($currentLoanedObject->getObjectId());

                        if(empty($objectFromDb)) {
                            throw new Exception("Non esiste un oggetto con l'id specificato");
                        }

                        $objectFromDb->available();
                        $this->itemRepository->updateState($objectFromDb->getId(), $objectFromDb->getStateCode());

                        // decreasing the quantity of objects of that type
                        $objectType->addQuantity(1);
                        $this->objectTypeRepository->updateQuantity($objectType->getCode(), $objectType->getQuantity());

                        $loanToUpdate->setQuantity($loanToUpdate->getQuantity() - 1);
                        
                    }
                }
            }else {
                

            // for each object received i check if it corresponds to 
            foreach($command->objects as $updatedObject) {

                $found = false;
                foreach($currentLoanedObjectsOfLoan as $currentLoanedObject) {
                    if($updatedObject->getId() == $currentLoanedObject->getObjectId() && $updatedObject->getObjectTypeCode() == $currentLoanedObject->getObjectTypeCode()) {

                            // i update the state of the loanedobject
                            if($updatedObject->getStateCode() == 4) {
                                $this->loanedObjectRepository->markAsDamaged($loanToUpdate->getId(), $updatedObject->getId());
                            }

                            $found = true;
                            break;
                        }
                    }
                
                // the object is not present in the loaned objects so i make all the checks and add it
                if(!$found && $loanToUpdate->getQuantity() < 5) {
                    // I check if the quantity of the object_type is > 0 and if it is I loan it if not I return an error
                    $objectType = $this->objectTypeRepository->findByCode($updatedObject->getObjectTypeCode());

                    if(empty($objectType)) {
                        throw new Exception("Non e' stato trovato un object type");
                    }

                    if($objectType->getQuantity() == 0) {
                        throw new Exception("Non e' possibile prendere in prestito l'oggetto con type code: " . $objectType->getCode());
                    }

                    // I check if the user passes an object_id with no correspondence to the objecttype
                    $objectFromDb = $this->itemRepository->findById($updatedObject->getId());

                    if(empty($objectFromDb)) {
                        throw new Exception("Non esiste un oggetto con l'id specificato");
                    }

                    if($objectFromDb->getObjectTypeCode() != $objectType->getCode()) {
                        throw new Exception("L'id dell'oggetto e il codice dell'object type non corrispondono a nessun oggetto");
                    }

                    // i create the new loaned object
                    $tmp = new LoanedObject($objectType->getCode(), $objectFromDb->getId(), $loanToUpdate->getId(), 0);
                    $createdLoanedObject = $this->loanedObjectRepository->create($loanToUpdate->getId(), $tmp);

                    $objectFromDb->loan();
                    $this->itemRepository->updateState($objectFromDb->getId(), $objectFromDb->getStateCode());

                    // decreasing the quantity of objects of that type
                    $objectType->removeQuantity(1);
                    $this->objectTypeRepository->updateQuantity($objectType->getCode(), $objectType->getQuantity());

                    // now that the object is added I increase the quantity of objects in loan
                    $loanToUpdate->setQuantity($loanToUpdate->getQuantity() + 1);
                    continue;
                }else {
                    // quantity > 5
                    continue;
                    }

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