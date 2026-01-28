<?php

require_once __DIR__ . '/../../../Infrastructure/Persistence/Repositories/ItemRepository.php';
require_once __DIR__ . '/../../../Infrastructure/Persistence/Repositories/ObjectTypeRepository.php';
require_once __DIR__ . '/../../../Infrastructure/Persistence/Repositories/MaintenanceRepository.php';
require_once __DIR__ . '/../../../Infrastructure/Persistence/Repositories/OperationsRepository.php';
require_once __DIR__ . '/../../../Infrastructure/Persistence/Connection/TransactionManager.php';

require_once __DIR__ . '/../../../../config/Database.php';

require_once __DIR__ . '/../../Commands/Maintenance/CloseMaintenanceCommand.php';


class CloseMaintenanceHandler {
    private $itemRepository;
    private $objectTypeRepository;
    private $maintenanceRepository;
    private $operationsRepository;
    private $transaction;


    public function __construct() {
        $db = new Database();
        $connection = $db->getConnection();

        $this->itemRepository = new ItemRepository($connection);
        $this->objectTypeRepository = new ObjectTypeRepository($connection);
        $this->maintenanceRepository = new MaintenanceRepository($connection);
        $this->operationsRepository = new OperationsRepository($connection);
        $this->transaction = new TransactionManager($connection);

    }

    public function handle(CloseMaintenanceCommand $command) {

        try {

            if(empty($command->id)) {
                throw new Exception("L'id della Manutenzione non puo' essere vuoto");
            }

            $maintenance = $this->maintenanceRepository->findById($command->id);

            if(empty($maintenance)) {
                throw new Exception("L'id inviato non corrisponde a nessuna manutenzione");
            }


            $this->transaction->begin();

            // i close all the operations that are left open
            $operations = $this->operationsRepository->getAllMaintenanceOperations($maintenance->getId());
            foreach($operations as $operation) {

                if(empty($operation->getEndDate())) {
                    $this->operationsRepository->closeOperation($operation->getId(), new DateTime('now'));
                }

            }

            // I want to get the object and make its status
            $object = $this->itemRepository->findById($maintenance->getObjectId());
            $object->available();
            $this->itemRepository->updateState($object->getId(), $object->getStateCode());

            // I update the quantity of available objects
            $objectType = $this->objectTypeRepository->findByCode($object->getObjectTypeCode());
            $objectType->addQuantity(1);
            $this->objectTypeRepository->updateQuantity($objectType->getCode(), $objectType->getQuantity());


            $this->maintenanceRepository->CloseMaintenance($maintenance->getId(), new DateTime('now'));
          
            $this->transaction->commit();

        }catch(Exception $ex) {
            $this->transaction->rollback();
             throw new Exception("Si e' verificato un errore durante la chiusura della Manutenzione: " . $ex->getMessage(), 0, $ex);
        }
    }
}