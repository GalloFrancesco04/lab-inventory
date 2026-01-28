<?php

require_once __DIR__ . '/../../../Infrastructure/Persistence/Repositories/ItemRepository.php';
require_once __DIR__ . '/../../../Infrastructure/Persistence/Repositories/ObjectTypeRepository.php';
require_once __DIR__ . '/../../../Infrastructure/Persistence/Repositories/MaintenanceRepository.php';
require_once __DIR__ . '/../../../Infrastructure/Persistence/Repositories/UserRepository.php';
require_once __DIR__ . '/../../../Infrastructure/Persistence/Repositories/OperationsRepository.php';
require_once __DIR__ . '/../../../../config/Database.php';

require_once __DIR__ . '/../../Commands/Maintenance/ViewSingleMaintenanceCommand.php';


class ViewSingleMaintenanceHandler {
    private $itemRepository;
    private $objectTypeRepository;
    private $maintenanceRepository;
    private $userRepository;
    private $operationsRepository;

    public function __construct() {
        $db = new Database();
        $connection = $db->getConnection();

        $this->itemRepository = new ItemRepository($connection);
        $this->objectTypeRepository = new ObjectTypeRepository($connection);
        $this->maintenanceRepository = new MaintenanceRepository($connection);
        $this->userRepository = new UserRepository($connection);
        $this->operationsRepository = new OperationsRepository($connection);
    }

    public function handle(ViewSingleMaintenanceCommand $command) {

        try {

            if(empty($command->id)) {
                throw new Exception("L'id e' un campo obbligatorio");
            }

            $maintenance = $this->maintenanceRepository->findById($command->id);
            
            if(empty($maintenance)) {
                return null;
            }


            // techincian name -> we don't have name, only email
            $technician = $this->userRepository->findUserById($maintenance->getTechnicianId());
            $maintenance->technician = $technician->getEmail();

            // object name
            $object = $this->itemRepository->findById($maintenance->getObjectId());
            $objectType = $this->objectTypeRepository->findByCode($object->getObjectTypeCode());
            $maintenance->objectName = $objectType->getName();

            // i return the operations associated
            $operations = $this->operationsRepository->getAllMaintenanceOperations($maintenance->getId());
            $maintenance->operations = $operations;

            return $maintenance;

        }catch(Exception $ex) {
             throw new Exception("Si e' verificato un errore durante il recupero della Manutenzione: " . $ex->getMessage(), 0, $ex);
        }
    }
}