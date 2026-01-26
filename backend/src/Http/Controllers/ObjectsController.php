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
require_once __DIR__ . '/../../Application/Commands/Inventory/DeleteObjectCommand.php';
require_once __DIR__ . '/../../Application/Handlers/Inventory/DeleteObjectHandler.php';

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
        $this->mediator->register(DeleteObjectCommand::class, DeleteObjectHandler::class);
        $this->mediator->register(SendToMaintenanceCommand::class, SendToMaintenanceHandler::class);
    }

    // POST /api/objects
    public function create(Request $request) {
        $data = $request->getBody();
        try {
            $command = new CreateObjectCommand($data);
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
            $command = new UpdateObjectCommand((int)$id, $data);
            $this->mediator->send($command);
            Response::success(["message" => "Oggetto aggiornato correttamente"]);
        } catch (Exception $e) {
            Response::error($e->getMessage(), "UPDATE_ERROR", 400);
        }
    }

    // DELETE /api/objects/{id}
    public function delete(Request $request, $id) {
        try {
            $command = new DeleteObjectCommand((int)$id);
            $this->mediator->send($command);
            Response::success(["message" => "Oggetto eliminato correttamente"]);
        } catch (Exception $e) {
            Response::error($e->getMessage(), "DELETE_ERROR", 400);
        }
    }

    // POST /api/objects/maintenance
    public function sendMaintenance(Request $request) {
        $data = $request->getBody();
        try {
            $command = new SendToMaintenanceCommand($data);
            $this->mediator->send($command);
            Response::success(["message" => "Oggetto inviato in manutenzione"]);
        } catch (Exception $e) {
            Response::error($e->getMessage(), "MAINTENANCE_ERROR", 400);
        }
    }

    // GET /api/objects
    public function getAll(Request $request) {
        try {
            
            $sql = "SELECT o.*, t.name as model_name, t.brand 
                    FROM object_ o 
                    JOIN objecttype t ON o.object_type_code = t.code";
            
            $stmt = $this->conn->query($sql);
            Response::success($stmt->fetchAll(PDO::FETCH_ASSOC));
        } catch (Exception $e) {
            Response::error($e->getMessage(), "DB_ERROR", 500);
        }
    }


    // GET /api/objects/{id}
    public function getById(Request $request, $id) {
        try {
            $sql = "SELECT o.*, t.name as model_name, t.brand 
                    FROM object_ o 
                    JOIN objecttype t ON o.object_type_code = t.code
                    WHERE o.id = :id";

            $stmt = $this->conn->prepare($sql);
            $stmt->execute([':id' => $id]);
            $obj = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$obj) {
                Response::error("Oggetto non trovato", "NOT_FOUND", 404);
                return;
            }
            Response::success($obj);
        } catch (Exception $e) {
            Response::error($e->getMessage(), "DB_ERROR", 500);
        }
    }
}