<?php

/**
 * OPERATIONS CONTROLLER
 * Handles high-level system operations and administrative tasks.
 */

// IMPORTS
require_once __DIR__ . '/../Response.php';
require_once __DIR__ . '/../../Application/Mediator/Mediator.php';
require_once __DIR__ . '/../../../config/Database.php';
require_once __DIR__ . '/../../Http/Request.php';

// COMMANDS & HANDLERS
// System Status / Health
require_once __DIR__ . '/../../Application/Commands/Operations/SystemStatusCommand.php';
require_once __DIR__ . '/../../Application/Handlers/Operations/SystemStatusHandler.php';
// Logs / Activity
require_once __DIR__ . '/../../Application/Commands/Operations/ViewLogsCommand.php';
require_once __DIR__ . '/../../Application/Handlers/Operations/ViewLogsHandler.php';
// Statistics (Inventory/Loans summary)
require_once __DIR__ . '/../../Application/Commands/Operations/GetStatisticsCommand.php';
require_once __DIR__ . '/../../Application/Handlers/Operations/GetStatisticsHandler.php';

class OperationsController {
    private $mediator;
    private $conn;

    public function __construct() {
        $this->mediator = new Mediator();
        $db = new Database();
        $this->conn = $db->getConnection();

        // Registration of Operations Commands
        $this->mediator->register(SystemStatusCommand::class, SystemStatusHandler::class);
        $this->mediator->register(ViewLogsCommand::class, ViewLogsHandler::class);
        $this->mediator->register(GetStatisticsCommand::class, GetStatisticsHandler::class);
    }

    /**
     * GET /api/operations/status
     * Returns the current health status of the system.
     */
    public function getSystemStatus(Request $request) {
        try {
            $command = new SystemStatusCommand();
            $status = $this->mediator->send($command);
            Response::success($status);
        } catch (Exception $e) {
            Response::error($e->getMessage(), "STATUS_ERROR", 500);
        }
    }

    /**
     * GET /api/operations/logs
     * Retrieves system activity logs.
     */
    public function getLogs(Request $request) {
        try {
            $command = new ViewLogsCommand();
            $logs = $this->mediator->send($command);
            Response::success($logs);
        } catch (Exception $e) {
            Response::error($e->getMessage(), "LOGS_ERROR", 500);
        }
    }

    /**
     * GET /api/operations/statistics
     * Generates a summary of inventory, maintenance, and loan data.
     */
    public function getStats(Request $request) {
        try {
            $command = new GetStatisticsCommand();
            $stats = $this->mediator->send($command);
            Response::success($stats);
        } catch (Exception $e) {
            Response::error($e->getMessage(), "STATS_ERROR", 500);
        }
    }
}