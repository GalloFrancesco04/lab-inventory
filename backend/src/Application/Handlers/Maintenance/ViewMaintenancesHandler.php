<?php

require_once __DIR__ . '/../../../Infrastructure/Persistence/Repositories/ItemRepository.php';
require_once __DIR__ . '/../../../Infrastructure/Persistence/Repositories/ObjectTypeRepository.php';
require_once __DIR__ . '/../../../Infrastructure/Persistence/Repositories/MaintenanceRepository.php';
require_once __DIR__ . '/../../../Infrastructure/Persistence/Repositories/UserRepository.php';
require_once __DIR__ . '/../../../../config/Database.php';

require_once __DIR__ . '/../../Commands/Maintenance/ViewMaintenancesCommand.php';


class ViewMaintenancesHandler {
    private $itemRepository;
    private $objectTypeRepository;
    private $maintenanceRepository;
    private $userRepository;

    public function __construct() {
        $db = new Database();
        $connection = $db->getConnection();

        $this->itemRepository = new ItemRepository($connection);
        $this->objectTypeRepository = new ObjectTypeRepository($connection);
        $this->maintenanceRepository = new MaintenanceRepository($connection);
        $this->userRepository = new UserRepository($connection);
    }

    public function handle(ViewMaintenancesCommand $command) {

        try {

            $maintenances = $this->maintenanceRepository->getAll();
            
            if(empty($maintenances)) {
                return [];
            }

            foreach($maintenances as $maintenance) {

                // techincian name -> we don't have name, only email
                $technician = $this->userRepository->findUserById($maintenance->getTechnicianId());
                $maintenance->technician = $technician->getEmail();

                // object name
                $object = $this->itemRepository->findById($maintenance->getObjectId());
                $objectType = $this->objectTypeRepository->findByCode($object->getObjectTypeCode());
                $maintenance->objectName = $objectType->getName();
            }

            return $maintenances;

        }catch(Exception $ex) {
             throw new Exception("Si e' verificato un errore durante il recupero delle Manutenzioni: " . $ex->getMessage(), 0, $ex);
        }
    }
}