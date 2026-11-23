<?php

require_once __DIR__ . '/../../../../config/Database.php';

require_once __DIR__ . '/../../../Domain/Entities/Operation.php';

require_once __DIR__ . '/../../Commands/Operations/AddOperationCommand.php';
require_once __DIR__ . '/../../../Infrastructure/Persistence/Repositories/MaintenanceRepository.php';
require_once __DIR__ . '/../../../Infrastructure/Persistence/Repositories/OperationsRepository.php';

class AddOperationHandler {
    private $maintenanceRepository;
    private $operationsRepository;

    public function __construct() {
        $db = new Database();
        $connection = $db->getConnection();

        $this->maintenanceRepository = new MaintenanceRepository($connection);
        $this->operationsRepository = new OperationsRepository($connection);
    }

    public function handle(AddOperationCommand $command) {
        try {

            if (empty($command->maintenance_id) || empty($command->start_date) || empty($command->description)) {
                throw new Exception("Id Manutenzione, Data di inizio e Descrizione sono campi obbligatori");
            }

            $maintenance = $this->maintenanceRepository->findById($command->maintenance_id);

            if(empty($maintenance)) {
                throw new Exception("Non e' stata trovata alcuna manutenzione con quell'Id");
            }

            $operation = new Operation(
                null,
                $maintenance->getId(),
                $command->start_date,
                null,
                $command->description,
                $command->cost
            );
            $this->operationsRepository->create($operation);

            return $operation->getId();

        }catch(Exception $ex) {
            throw new Exception("Si e' verificato un errore durante l'aggiunta dell'Operazione " . $ex->getMessage(), 0, $ex);
        }
    }
}