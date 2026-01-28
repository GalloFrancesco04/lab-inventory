<?php
require_once __DIR__ . '/../../../../config/Database.php';
require_once __DIR__ . '/../../../Infrastructure/Persistence/Repositories/ItemRepository.php';
require_once __DIR__ . '/../../../Infrastructure/Persistence/Repositories/UserRepository.php';
require_once __DIR__ . '/../../../Infrastructure/Persistence/Repositories/MaintenanceRepository.php';
require_once __DIR__ . '/../../../Infrastructure/Persistence/Connection/TransactionManager.php';

require_once __DIR__ . '/../../Commands/Inventory/SendToMaintenanceCommand.php';
require_once __DIR__ . '/../../../Domain/Entities/Maintenance.php';



class SendToMaintenanceHandler {
    private $itemRepository;
    private $userRepository;
    private $maintenanceRepository;
    private $transaction;

    public function __construct() {
        $db = new Database();
        $connection = $db->getConnection();
        
        $this->itemRepository = new ItemRepository($connection);
        $this->userRepository = new UserRepository($connection);
        $this->maintenanceRepository = new MaintenanceRepository($connection);

        $this->transaction = new TransactionManager($connection);
    }


    //  Method to handle the command and send the object to maintenance
    public function handle(SendToMaintenanceCommand $command) {
        
        try {
    
            if(empty($command->object_id) || empty($command->technician_id))   {
                throw new Exception("L'id dell'Oggetto e del Tecnico non possono essere vuoti");
            }

            $start_date = new DateTime('now');

            $end_date = null;

            $technician = $this->userRepository->findTechnicianById($command->technician_id);

            if(empty($technician)) {
                throw new Exception("Non e' stato trovato alcun Tecnico con l'id specificato");
            }

            $object = $this->itemRepository->findById($command->object_id);

            if(empty($object)) {
                throw new Exception("Non e' stato trovato alcun Oggetto con l'id specificato");
            }

            $this->transaction->begin();

            
            $object->maintenance();

            $this->itemRepository->updateState($object->getId(), $object->getStateCode());

            // i pass the entire maintenance object, for creation
    
            $maintenanceToCreate = new Maintenance(
                null, 
                $object->getId(),
                $command->description,
                $start_date,
                $end_date,
                $command->type,
                $command->technician_id,
                null,
                0
             );

            $createdMaintenanceId = $this->maintenanceRepository->create($maintenanceToCreate);

            $this->transaction->commit();

            return $createdMaintenanceId;

        } catch (Exception $ex) {
            
            $this->transaction->rollback();
            throw new Exception("Si e' verificato un errore nell'invio dell'Oggetto in Manutenzione: " . $ex->getMessage(), 0, $ex);

        }
    }
}