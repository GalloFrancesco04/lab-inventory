<?php

require_once __DIR__ . '/../../../Infrastructure/Persistence/Repositories/ItemRepository.php';
require_once __DIR__ . '/../../../Infrastructure/Persistence/Repositories/ObjectTypeRepository.php';
require_once __DIR__ . '/../../../../config/Database.php';
require_once __DIR__ . '/../../../Infrastructure/Persistence/Connection/TransactionManager.php';


require_once __DIR__ . '/../../Commands/Inventory/ToggleOutOfServiceCommand.php';

class ToggleOutOfServiceHandler {
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

    public function handle(ToggleOutOfServiceCommand $command) {

    try{

        if(empty($command->id)) {
            throw new Exception("L'id dell'Oggetto non puo' essere vuoto");
        }
            
        $obj = $this->itemRepository->findById($command->id);
            
        if (empty($obj)){
            throw new Exception("Non e' stato trovato nessun Oggetto corrispondente a quell'Id");
        }

        $this->transaction->begin();
        // i set The new state
        $obj->outOfService();
        $this->itemRepository->updateState($obj->getId(), $obj->getStateCode());


        $this->transaction->commit();

        }catch(Exception $ex) {
            $this->transaction->rollback();
            throw new Exception("Si e' verificato un errore durante il recupero dell'Oggetto: " . $ex->getMessage(), 0, $ex);
        }
    }
}