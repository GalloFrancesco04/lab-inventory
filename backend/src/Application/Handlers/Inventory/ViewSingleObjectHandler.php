<?php

require_once __DIR__ . '/../../../Infrastructure/Persistence/Repositories/ItemRepository.php';
require_once __DIR__ . '/../../../Infrastructure/Persistence/Repositories/MaintenanceRepository.php';
require_once __DIR__ . '/../../../../config/Database.php';

require_once __DIR__ . '/../../Commands/Inventory/ViewSingleObjectCommand.php';

class ViewSingleObjectHandler {
    private $itemRepository;
    private $maintenanceRepository;

    public function __construct() {
        $db = new Database();
        $connection = $db->getConnection();

        $this->itemRepository = new ItemRepository($connection);
        $this->maintenanceRepository = new MaintenanceRepository($connection);
    }

    public function handle(ViewSingleObjectCommand $command) {

    try{

        if(empty($command->id)) {
            throw new Exception("L'id dell'Oggetto non puo' essere vuoto");
        }
            
        $obj = $this->itemRepository->findById($command->id);
            
        if (empty($obj)){
            return null;
        }

        // i want to get all the maintenances associated to the object
        $maintenances = $this->maintenanceRepository->getObjectMaintenances($command->id);
        $obj->maintenances = $maintenances;
                
        return $obj;

        }catch(Exception $ex) {
            throw new Exception("Si e' verificato un errore durante il recupero dell'Oggetto: " . $ex->getMessage(), 0, $ex);
        }
    }
}