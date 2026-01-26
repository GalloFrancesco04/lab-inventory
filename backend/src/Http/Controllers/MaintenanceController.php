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
require_once __DIR__ . '/../../Application/Commands/Maintenance/ViewMaintenanceCommand.php';
require_once __DIR__ . '/../../Application/Handlers/Maintenance/ViewMaintenanceHandler.php';

class MaintenanceController {
    private $mediator;

    public function __construct() {
        $this->mediator = new Mediator();
        
        $this->mediator->register(SendToMaintenanceCommand::class, SendToMaintenanceHandler::class);
        $this->mediator->register(CloseMaintenanceCommand::class, CloseMaintenanceHandler::class);
        $this->mediator->register(ViewMaintenanceCommand::class, ViewMaintenanceHandler::class);
    }

    /**
     * GET /api/maintenance
     * view maintenance log
     */
    public function getAll(Request $request) {
        try {
            $command = new ViewMaintenanceCommand();
            $log = $this->mediator->send($command);
            Response::success($log);
        } catch (Exception $e) {
            Response::error($e->getMessage(), "DB_ERROR", 500);
        }
    }

    /**
     * POST /api/maintenance
     * send to maintenance
     */
    public function start(Request $request) {
        $data = $request->getBody();
        try {
            $command = new SendToMaintenanceCommand($data);
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
            $command = new CloseMaintenanceCommand((int)$id, $data);
            $this->mediator->send($command);
            Response::success(["message" => "Maintenance closed"]);
        } catch (Exception $e) {
            Response::error($e->getMessage(), "MAINTENANCE_CLOSE_ERROR", 400);
        }
    }
}