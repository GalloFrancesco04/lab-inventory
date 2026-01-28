<?php

// Controller for handling HTTP requests related to physical inventory objects
require_once __DIR__ . '/../Response.php';
require_once __DIR__ . '/../../Application/Mediator/Mediator.php';
require_once __DIR__ . '/../../../config/Database.php';
require_once __DIR__ . '/../../Http/Request.php';

require_once __DIR__ . '/../../Application/Commands/Inventory/CreateObjectCommand.php';
require_once __DIR__ . '/../../Application/Handlers/Inventory/CreateObjectHandler.php';
require_once __DIR__ . '/../../Application/Commands/Inventory/SendToMaintenanceCommand.php';
require_once __DIR__ . '/../../Application/Handlers/Inventory/SendToMaintenanceHandler.php';
require_once __DIR__ . '/../../Application/Commands/Inventory/UpdateObjectCommand.php';
require_once __DIR__ . '/../../Application/Handlers/Inventory/UpdateObjectHandler.php';


require_once __DIR__ . '/../../Application/Commands/Inventory/ViewObjectsCommand.php';
require_once __DIR__ . '/../../Application/Handlers/Inventory/ViewObjectsHandler.php';
require_once __DIR__ . '/../../Application/Commands/Inventory/ViewSingleObjectCommand.php';
require_once __DIR__ . '/../../Application/Handlers/Inventory/ViewSingleObjectHandler.php';

require_once __DIR__ . '/../../Application/Commands/Inventory/ToggleOutOfServiceCommand.php';
require_once __DIR__ . '/../../Application/Handlers/Inventory/ToggleOutOfServiceHandler.php';

require_once __DIR__ . '/../../Application/Commands/Inventory/ViewAvailableObjectsCommand.php';
require_once __DIR__ . '/../../Application/Handlers/Inventory/ViewAvailableObjectsHandler.php';

// Database connection
class ObjectsController {
    private $mediator;
    private $conn;

    // Constructor to set up mediator and database connection
    public function __construct() {
        $this->mediator = new Mediator();
        $db = new Database();
        $this->conn = $db->getConnection();

        
        $this->mediator->register(CreateObjectCommand::class, CreateObjectHandler::class);
        $this->mediator->register(UpdateObjectCommand::class, UpdateObjectHandler::class);
        
        $this->mediator->register(SendToMaintenanceCommand::class, SendToMaintenanceHandler::class);
        $this->mediator->register(ViewObjectsCommand::class, ViewObjectsHandler::class);
        $this->mediator->register(ViewSingleObjectCommand::class, ViewSingleObjectHandler::class);
        $this->mediator->register(ToggleOutOfServiceCommand::class, ToggleOutOfServiceHandler::class);
        $this->mediator->register(ViewAvailableObjectsCommand::class, ViewAvailableObjectsHandler::class);
    }


    // Utility to map object entity to array
    private function mapToArray($obj) {
        if (is_array($obj)) return $obj;

        return [
            // Adatta questi getter ai nomi reali nella tua Entity
            'id' => method_exists($obj, 'getId') ? $obj->getId() : null,
            'object_type_code' => method_exists($obj, 'getTypeCode') ? $obj->getTypeCode() : (method_exists($obj, 'getObjectTypeCode') ? $obj->getObjectTypeCode() : null),
            'location' => method_exists($obj, 'getLocation') ? $obj->getLocation() : null,
            'status' => method_exists($obj, 'getStatus') ? $obj->getStatus() : null,
            // Aggiungi altri campi se necessario
        ];
    }

    // POST /api/objects
    public function create(Request $request) {
        $data = $request->getBody();
        try {
            $command = new CreateObjectCommand($data["object_type_code"], $data["location"] ?? null);
            $id = $this->mediator->send($command);
            Response::success(["message" => "Oggetto fisico creato", "id" => $id], 201);
        } catch (Exception $e) {
            Response::error($e->getMessage(), "CREATE_ERROR", 400);
        }
    }

    public function update(Request $request, $id) {
        $data = $request->getBody();
        try {
            // Create and send the update command
            $command = new UpdateObjectCommand((int)$id, $data["location"]);
            $this->mediator->send($command);
            Response::success(["message" => "Oggetto aggiornato correttamente"]);
        } catch (Exception $e) {
            Response::error($e->getMessage(), "UPDATE_ERROR", 400);
        }
    }

   

    // POST /api/objects/maintenance
    public function sendMaintenance(Request $request) {
        $data = $request->getBody();
        try {
            $command = new SendToMaintenanceCommand($data["object_id"], $data["description"], $data["technician_id"], $data["start_date"], );
            $this->mediator->send($command);
            Response::success(["message" => "Oggetto inviato in manutenzione"]);
        } catch (Exception $e) {
            Response::error($e->getMessage(), "MAINTENANCE_ERROR", 400);
        }
    }

    // GET /api/objects
    public function getAll(Request $request) {
        try {
            $command = new ViewObjectsCommand();
            $objects = $this->mediator->send($command);

            
            $mappedObjects = [];
            if (is_array($objects)) {
                foreach ($objects as $obj) {
                    $mappedObjects[] = $this->mapToArray($obj);
                }
            }

            Response::success($mappedObjects);
        } catch (Exception $e) {
            Response::error($e->getMessage(), "DB_ERROR", 500);
        }
    }

    // GET /api/objects/{id}
    public function getById(Request $request, $id) {
        try {
            $command = new ViewSingleObjectCommand((int)$id);
            $obj = $this->mediator->send($command);
            
            // Mapping manuale
            $mappedObj = $this->mapToArray($obj);
            
            Response::success($mappedObj);
        } catch (Exception $e) {
            Response::error($e->getMessage(), "DB_ERROR", 500);
        }
    }

    // GET /api/objects/available
    public function getAvailable(Request $request) {
        try {
            $command = new ViewAvailableObjectsCommand();
            $objects = $this->mediator->send($command);

            
            $mappedObjects = [];
            if (is_array($objects)) {
                foreach ($objects as $obj) {
                    $mappedObjects[] = $this->mapToArray($obj);
                }
            }

            Response::success($mappedObjects);
        } catch (Exception $e) {
            Response::error($e->getMessage(), "DB_ERROR", 500);
        }
    }

    /**
     * PUT /api/objects/{id}/toggle-service
        */
    public function toggleStatus(Request $request, $id) {
        try {
            $command = new ToggleOutOfServiceCommand((int)$id);
            $this->mediator->send($command);
            
            Response::success(["message" => "Stato oggetto aggiornato (Toggle Out Of Service)"]);
        } catch (Exception $e) {
            Response::error($e->getMessage(), "TOGGLE_ERROR", 400);
        }
    }
}