<?php

require_once __DIR__ . '/../Response.php';
require_once __DIR__ . '/../../Application/Mediator/Mediator.php';
require_once __DIR__ . '/../../Http/Request.php';

// --- COMMANDS & HANDLERS IMPORTS ---

// 1. ADD OPERATION
require_once __DIR__ . '/../../Application/Commands/Operations/AddOperationCommand.php';
require_once __DIR__ . '/../../Application/Handlers/Operations/AddOperationHandler.php';

// 2. UPDATE OPERATION
require_once __DIR__ . '/../../Application/Commands/Operations/UpdateOperationCommand.php';
require_once __DIR__ . '/../../Application/Handlers/Operations/UpdateOperationHandler.php';

// 3. VIEW OPERATIONS (Assumo esistano questi comandi standard per la lettura)
// Se non li hai ancora creati, puoi commentare queste righe per ora.
require_once __DIR__ . '/../../Application/Commands/Operations/ViewOperationsCommand.php';
require_once __DIR__ . '/../../Application/Handlers/Operations/ViewOperationsHandler.php';
require_once __DIR__ . '/../../Application/Commands/Operations/ViewSingleOperationCommand.php';
require_once __DIR__ . '/../../Application/Handlers/Operations/ViewSingleOperationHandler.php';

class OperationsController {
    private $mediator;

    public function __construct() {
        $this->mediator = new Mediator();

        // Registrazione Add
        $this->mediator->register(AddOperationCommand::class, AddOperationHandler::class);
        
        // Registrazione Update
        $this->mediator->register(UpdateOperationCommand::class, UpdateOperationHandler::class);

        // Registrazione View (Standard)
        $this->mediator->register(ViewOperationsCommand::class, ViewOperationsHandler::class);
        $this->mediator->register(ViewSingleOperationCommand::class, ViewSingleOperationHandler::class);
    }

    /**
     * Helper: Trasforma l'oggetto Operation in Array visibile per il JSON.
     */
    private function mapToArray($operation) {
        if (is_array($operation)) return $operation;

        return [
            'id' => method_exists($operation, 'getId') ? $operation->getId() : null,
            'maintenance_id' => method_exists($operation, 'getMaintenanceId') ? $operation->getMaintenanceId() : null,
            'description' => method_exists($operation, 'getDescription') ? $operation->getDescription() : null,
            'start_date' => method_exists($operation, 'getStartDate') ? $operation->getStartDate() : null,
            'end_date' => method_exists($operation, 'getEndDate') ? $operation->getEndDate() : null,
            'cost' => method_exists($operation, 'getCost') ? $operation->getCost() : 0
        ];
    }

    /**
     * GET /api/operations
     * Recupera tutte le operazioni.
     */
    public function getAll(Request $request) {
        try {
            $command = new ViewOperationsCommand();
            $opsObjects = $this->mediator->send($command);

            $opsArray = [];
            if (is_array($opsObjects)) {
                foreach ($opsObjects as $op) {
                    $opsArray[] = $this->mapToArray($op);
                }
            }
            Response::success($opsArray);
        } catch (Exception $e) {
            Response::error($e->getMessage(), "DB_ERROR", 500);
        }
    }

    /**
     * GET /api/operations/{id}
     * Recupera una singola operazione.
     */
    public function getById(Request $request, $id) {
        try {
            $command = new ViewSingleOperationCommand((int)$id);
            $opObject = $this->mediator->send($command);
            
            $opArray = $this->mapToArray($opObject);
            Response::success($opArray);
        } catch (Exception $e) {
            Response::error($e->getMessage(), "NOT_FOUND", 404);
        }
    }

    /**
     * POST /api/operations
     * Aggiunge una nuova operazione a una manutenzione.
     */
    public function create(Request $request) {
        $data = $request->getBody();

        try {
            // Validazione minima
            if (empty($data['maintenance_id']) || empty($data['description'])) {
                throw new Exception("Maintenance ID e Description sono obbligatori.");
            }

            $maintenance_id = $data['maintenance_id'];
            $description = $data['description'];
            // Se start_date non c'è, usiamo la data odierna
            $start_date = $data['start_date'] ?? date('Y-m-d H:i:s');
            $cost = $data['cost'] ?? 0;

            // Istanzia il comando specifico che mi hai dato
            $command = new AddOperationCommand($maintenance_id, $description, $start_date, $cost);
            
            // Invia al mediator
            $newId = $this->mediator->send($command);

            Response::success(["message" => "Operation added successfully", "id" => $newId], 201);
        } catch (Exception $e) {
            Response::error($e->getMessage(), "CREATE_ERROR", 400);
        }
    }

    /**
     * PUT /api/operations/{id}
     * Aggiorna un'operazione esistente.
     */
    public function update(Request $request, $id) {
        $data = $request->getBody();

        try {
            // Prepariamo i dati opzionali (null se non presenti)
            $description = $data['description'] ?? null;
            $end_date = $data['end_date'] ?? null;
            $cost = $data['cost'] ?? null;

            // Istanzia il comando di update
            $command = new UpdateOperationCommand((int)$id, $description, $end_date, $cost);
            
            $this->mediator->send($command);

            Response::success(["message" => "Operation updated successfully"]);
        } catch (Exception $e) {
            Response::error($e->getMessage(), "UPDATE_ERROR", 400);
        }
    }
}