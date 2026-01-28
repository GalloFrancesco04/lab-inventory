<?php

require_once __DIR__ . '/../Response.php';
require_once __DIR__ . '/../../Application/Mediator/Mediator.php';
require_once __DIR__ . '/../../Http/Request.php';

// --- IMPORTS COMMANDS & HANDLERS ---

// 1. ADD OPERATION (Il tuo comando custom)
require_once __DIR__ . '/../../Application/Commands/Operations/AddOperationCommand.php';
require_once __DIR__ . '/../../Application/Handlers/Operations/AddOperationHandler.php';

// 2. UPDATE OPERATION (Il tuo comando custom)
require_once __DIR__ . '/../../Application/Commands/Operations/UpdateOperationCommand.php';
require_once __DIR__ . '/../../Application/Handlers/Operations/UpdateOperationHandler.php';

// 3. VIEW OPERATIONS (Standard: assicurati di avere questi file o commenta le righe)
require_once __DIR__ . '/../../Application/Commands/Operations/ViewOperationsCommand.php';
require_once __DIR__ . '/../../Application/Handlers/Operations/ViewOperationsHandler.php';
require_once __DIR__ . '/../../Application/Commands/Operations/ViewSingleOperationCommand.php';
require_once __DIR__ . '/../../Application/Handlers/Operations/ViewSingleOperationHandler.php';

class OperationsController {
    private $mediator;

    public function __construct() {
        $this->mediator = new Mediator();

        // Registration Add & Update
        $this->mediator->register(AddOperationCommand::class, AddOperationHandler::class);
        $this->mediator->register(UpdateOperationCommand::class, UpdateOperationHandler::class);


    }

   //helpers
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
     * POST /api/operations
     * Aggiunge una operazione (usa AddOperationCommand)
     */
    public function create(Request $request) {
        $data = $request->getBody();

        try {
            // Validazione minima
            if (empty($data['maintenance_id']) || empty($data['description'])) {
                throw new Exception("Maintenance ID e Description sono obbligatori.");
            }

            // Preparazione dati per il costruttore del comando
            $maintenance_id = $data['maintenance_id'];
            $description = $data['description'];
            // Se start_date non c'è, mette la data di oggi
            $start_date = $data['start_date'] ?? date('Y-m-d H:i:s');
            $cost = $data['cost'] ?? 0;

            // Istanzia il comando con i 4 argomenti richiesti
            $command = new AddOperationCommand($maintenance_id, $description, $start_date, $cost);
            
            $newId = $this->mediator->send($command);

            Response::success(["message" => "Operation added successfully", "id" => $newId], 201);
        } catch (Exception $e) {
            Response::error($e->getMessage(), "CREATE_ERROR", 400);
        }
    }

    /**
     * PUT /api/operations/{id}
     * Aggiorna una operazione (usa UpdateOperationCommand)
     */
    public function update(Request $request, $id) {
        $data = $request->getBody();

        try {
            // Estrae i campi opzionali (null se non presenti)
            $description = $data['description'] ?? null;
            $end_date = $data['end_date'] ?? null;
            $cost = $data['cost'] ?? null;

            // Istanzia il comando con gli argomenti corretti
            $command = new UpdateOperationCommand((int)$id, $description, $end_date, $cost);
            
            $this->mediator->send($command);

            Response::success(["message" => "Operation updated successfully"]);
        } catch (Exception $e) {
            Response::error($e->getMessage(), "UPDATE_ERROR", 400);
        }
    }
}