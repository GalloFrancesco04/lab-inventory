<?php
require_once __DIR__ . '/../../../../config/Database.php';
require_once __DIR__ . '/../../../Infrastructure/Persistence/ItemRepository.php';
require_once __DIR__ . '/../../../Infrastructure/Persistence/UserRepository.php';
require_once __DIR__ . '/../../../Infrastructure/Persistence/MaintenanceRepository.php';

require_once __DIR__ . '/../../Commands/Inventory/SendToMaintenanceCommand.php';

class SendToMaintenanceHandler {
    private $itemRepository;
    private $userRepository;
    private $maintenanceRepository;
    private $transaction;

    public function __construct() {
        $db = new Database();
        
        $this->itemRepository = new ItemRepository($db->conn);
        $this->userRepository = new UserRepository($db->conn);
        $this->maintenanceRepository = new MaintenanceRepository($db->conn);

        $transaction = new TransactionManager($db->conn);
    }


    //  Method to handle the command and send the object to maintenance
    public function handle(SendToMaintenanceCommand $command) {
        if(empty($command->object_id) || empty($command->technician_id))   {
            throw new Exception("L'id dell'Oggetto e del Tecnico non possono essere vuoti");
        }

        if(empty($command->start_date)) {
            $command->start_date = new DateTime('now');
        }

        $command->end_date = null;

        $technician = $this->userRepository->findTechnicianById($command->technician_id);

        if(empty($technician)) {
            throw new Exception("Non e' stato trovato alcun Tecnico con l'id specificato");
        }

        $object = $this->itemRepository->findById($command->object_id);

        if(empty($object)) {
            throw new Exception("Non e' stato trovato alcun Oggetto con l'id specificato");
        }

        $this->transaction->begin();

        try {
            
            $object->maintenance();

            $this->itemRepository->update($object, $object->getStateCode());

            // i pass the entire maintenance object, for creation
            $this->maintenanceRepository->create($command);

            $this->transaction->commit();

        } catch (Exception $e) {
            
            $this->transaction->rollback();
            throw new Exception("Si e' verificato qualche problema nell'invio dell'Oggetto in Manutenzione");

        }
    }
}