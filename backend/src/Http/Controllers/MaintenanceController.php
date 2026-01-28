<?php

require_once __DIR__ . '/../Response.php';
require_once __DIR__ . '/../../Application/Mediator/Mediator.php';
require_once __DIR__ . '/../../Http/Request.php';

// Import Maintenance Commands 
// Start Maintenance
require_once __DIR__ . '/../../Application/Commands/Inventory/SendToMaintenanceCommand.php';
require_once __DIR__ . '/../../Application/Handlers/Inventory/SendToMaintenanceHandler.php';
// End Maintenance 
require_once __DIR__ . '/../../Application/Commands/Maintenance/CloseMaintenanceCommand.php';
require_once __DIR__ . '/../../Application/Handlers/Maintenance/CloseMaintenanceHandler.php';
// View Maintenance 
require_once __DIR__ . '/../../Application/Commands/Maintenance/ViewMaintenancesCommand.php';
require_once __DIR__ . '/../../Application/Handlers/Maintenance/ViewMaintenancesHandler.php';

require_once __DIR__ . '/../../Application/Commands/Maintenance/ViewSingleMaintenanceCommand.php';
require_once __DIR__ . '/../../Application/Handlers/Maintenance/ViewSingleMaintenanceHandler.php';

class MaintenanceController {
    private $mediator;

    public function __construct() {
        $this->mediator = new Mediator();
        
        $this->mediator->register(SendToMaintenanceCommand::class, SendToMaintenanceHandler::class);
        $this->mediator->register(CloseMaintenanceCommand::class, CloseMaintenanceHandler::class);
        $this->mediator->register(ViewSingleMaintenanceCommand::class, ViewSingleMaintenanceHandler::class);
        $this->mediator->register(ViewMaintenancesCommand::class, ViewMaintenancesHandler::class);
    }

    // utility to map maintenance object to array
    private function mapToArray($maintenance) {
        if (is_array($maintenance)) return $maintenance;

        return [
            'id' => method_exists($maintenance, 'getId') ? $maintenance->getId() : null,
            'object_id' => method_exists($maintenance, 'getObjectId') ? $maintenance->getObjectId() : null,
            'description' => method_exists($maintenance, 'getDescription') ? $maintenance->getDescription() : null,
            'start_date' => method_exists($maintenance, 'getStartDate') ? $maintenance->getStartDate() : null,
            'end_date' => method_exists($maintenance, 'getEndDate') ? $maintenance->getEndDate() : null,
            'technician_id' => method_exists($maintenance, 'getTechnicianId') ? $maintenance->getTechnicianId() : null,
            'damaged' => method_exists($maintenance, 'isDamaged') ? $maintenance->isDamaged() : (method_exists($maintenance, 'getDamaged') ? $maintenance->getDamaged() : 0)
        ];
    }


    /**
     * GET /api/maintenance
     * view maintenance log
     */
    public function getAll(Request $request) {
        try {
            // UPDATED: use ViewMaintenancesCommand
            $command = new ViewMaintenancesCommand();
            $logsObjects = $this->mediator->send($command);
            
            // Map each maintenance object to array
            $logsArray = [];
            if (is_array($logsObjects)) {
                foreach ($logsObjects as $log) {
                    $logsArray[] = $this->mapToArray($log);
                }
            }
            
            Response::success($logsArray);
        } catch (Exception $e) {
            Response::error($e->getMessage(), "DB_ERROR", 500);
        }
    }

        // GET /api/maintenance/{id}
    public function getById(Request $request, $id) {
        try {
            $command = new ViewSingleMaintenanceCommand((int)$id);
            $logObject = $this->mediator->send($command);
            
            $logArray = $this->mapToArray($logObject);
            Response::success($logArray);
        } catch (Exception $e) {
            Response::error($e->getMessage(), "NOT_FOUND", 404);
        }
    }

    /**
     * POST /api/maintenance
     * send to maintenance
     */
    public function start(Request $request) {
        $data = $request->getBody();
        try {
            $command = new SendToMaintenanceCommand($data["object_id"], $data["description"], $data["technician_id"], $data["type"]);
            $this->mediator->send($command);
            Response::success(["message" => "Maintenance started"], 201);
        } catch (Exception $e) {
            Response::error($e->getMessage(), "MAINTENANCE_START_ERROR", 400);
        }
    }

    /**
     * PUT /api/maintenance/{id}
     * 
     */
    public function close(Request $request, $id) {
        $data = $request->getBody(); 
        try {
            // Create and send the close maintenance command
            $command = new CloseMaintenanceCommand((int)$id, $data["damaged"] ?? 0);
            $this->mediator->send($command);
            Response::success(["message" => "Maintenance closed"]);
        } catch (Exception $e) {
            Response::error($e->getMessage(), "MAINTENANCE_CLOSE_ERROR", 400);
        }
    }
}