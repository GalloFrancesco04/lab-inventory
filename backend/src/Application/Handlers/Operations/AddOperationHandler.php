<?php

require_once __DIR__ . '/../../../../config/Database.php';

require_once __DIR__ . '/../../../Domain/Entities/Operation.php';

require_once __DIR__ . '/../../Commands/Operations/AddOperationCommand.php';
require_once __DIR__ . '/../../../Infrastructure/Persistence/Repositories/MaintenanceRepository.php';
require_once __DIR__ . '/../../../Infrastructure/Persistence/Repositories/OperationsRepository.php';
require_once __DIR__ . '/../../../Infrastructure/Security/SessionManager.php';

class AddOperationHandler {
    private $maintenanceRepository;
    private $operationsRepository;
    private $session;

    public function __construct() {
        $db = new Database();
        $connection = $db->getConnection();

        $this->maintenanceRepository = new MaintenanceRepository($connection);
        $this->operationsRepository = new OperationsRepository($connection);
        $this->session = new SessionManager();
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

            // a Technician can only work on the Maintenances assigned to him
            if($this->session->getUserRole() == 2 && $maintenance->getTechnicianId() != $this->session->getUserId()) {
                throw new Exception("La Manutenzione non e' assegnata a questo Tecnico");
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