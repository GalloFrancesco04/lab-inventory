<?php

// Controller for handling HTTP requests related to physical inventory objects
require_once __DIR__ . '/../Response.php';
require_once __DIR__ . '/../../Application/Mediator/Mediator.php';

require_once __DIR__ . '/../../Application/Commands/Inventory/CreateObjectCommand.php';
require_once __DIR__ . '/../../Application/Handlers/Inventory/CreateObjectHandler.php';

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
    }

    // POST /api/objects
    public function create() {
        $data = json_decode(file_get_contents("php://input"), true);
        try {
            $command = new CreateObjectCommand($data);
            $id = $this->mediator->send($command);
            Response::success(["message" => "Oggetto fisico creato", "id" => $id], 201);
        } catch (Exception $e) {
            Response::error($e->getMessage(), "CREATE_ERROR", 400);
        }
    }

    // GET /api/objects
    public function getAll() {
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
}