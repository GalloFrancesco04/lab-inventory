<?php

require_once __DIR__ . '/../../../../config/Database.php';

require_once __DIR__ . '/../../../Domain/Entities/Operation.php';

require_once __DIR__ . '/../../Commands/Operations/UpdateOperationCommand.php';
require_once __DIR__ . '/../../../Infrastructure/Persistence/Repositories/MaintenanceRepository.php';
require_once __DIR__ . '/../../../Infrastructure/Persistence/Repositories/OperationsRepository.php';
require_once __DIR__ . '/../../../Infrastructure/Security/SessionManager.php';

class UpdateOperationHandler {
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

    public function handle(UpdateOperationCommand $command) {
        try {

            if (empty($command->id)) {
                throw new Exception("Id e' un campo obbligatorio");
            }

            $oldOperation = $this->operationsRepository->getById($command->id);

            if(empty($oldOperation)) {
                throw new Exception("Non e' stata trovata alcuna operazione con quell'Id");
            }

            $maintenance = $this->maintenanceRepository->findById($oldOperation->getMaintenanceId());

            // a Technician can only work on the Maintenances assigned to him
            if($this->session->getUserRole() == 2 && $maintenance->getTechnicianId() != $this->session->getUserId()) {
                throw new Exception("La Manutenzione non e' assegnata a questo Tecnico");
            }


            if(!empty($command->end_date) && $command->end_date < $oldOperation->getStartDate()) {
                throw new Exception("Non e' possibile inserire una data di fine anteriore a quella di inizio");
            }

            $operation = new Operation(
                $oldOperation->getId(),
                $oldOperation->getMaintenanceId(),
                $oldOperation->getStartDate(),
                $command->end_date,
                $command->description,
                $command->cost
            );
            
            $this->operationsRepository->update($operation);
       

        }catch(Exception $ex) {
            throw new Exception("Si e' verificato un errore durante la modifica dell'Operazione " . $ex->getMessage(), 0, $ex);
        }
    }
}