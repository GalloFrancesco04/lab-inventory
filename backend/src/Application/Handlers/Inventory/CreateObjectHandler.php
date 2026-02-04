<?php

require_once __DIR__ . '/../../../../config/Database.php';
require_once __DIR__ . '/../../../Infrastructure/Persistence/Repositories/ItemRepository.php';
require_once __DIR__ . '/../../../Infrastructure/Persistence/Repositories/ObjectTypeRepository.php';
require_once __DIR__ . '/../../../Infrastructure/Persistence/Connection/TransactionManager.php';
require_once __DIR__ . '/../../Commands/Inventory/CreateObjectCommand.php';

class CreateObjectHandler {
    private $itemRepository;
    private $objectTypeRepository;
    private $transaction;

    public function __construct() {
        $db = new Database();
        $connection = $db->getConnection();

        $this->itemRepository = new ItemRepository($connection);
        $this->objectTypeRepository = new ObjectTypeRepository($connection);
        $this->transaction = new TransactionManager($connection);
    }

    // Method to handle the command and create the object in the database
    public function handle(CreateObjectCommand $command) {

        try{

            if (empty($command->object_type_code)) {
                throw new Exception("L'ObjectTypeCode e' un campo obbligatorio");
            }
                
            $objectType = $this->objectTypeRepository->findByCode($command->object_type_code);

            if(empty($objectType)){
                throw new Exception("L'ObjectType specificato non esiste");
            }

            $this->transaction->begin();

            $objectToCreate = new InventoryItem(null, $command->object_type_code, 1, $command->location);
               
            $createdId = $this->itemRepository->create($objectToCreate);

            // now I update the quantity on objectType
            $objectType->addQuantity(1);
            $this->objectTypeRepository->updateQuantity($objectType->getCode(), $objectType->getQuantity());

            $this->transaction->commit();

            return $createdId;

        }catch(Exception $ex) {
            $this->transaction->rollback();
            throw new Exception("Si e' verificato un errore durante la creazione dell'Oggetto: " . $ex->getMessage(), 0, $ex);

        }
    }
}