<?php

//***REQUIRED IMPORTS */
require_once __DIR__ . '/../Response.php'; 
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../Application/Mediator/Mediator.php';

// Import Create Components
require_once __DIR__ . '/../../Application/Commands/ObjectTypes/CreateObjectTypeCommand.php';
require_once __DIR__ . '/../../Application/Handlers/ObjectTypes/CreateObjectTypeHandler.php';

// Import Update Components
require_once __DIR__ . '/../../Application/Commands/ObjectTypes/UpdateObjectTypeCommand.php';
require_once __DIR__ . '/../../Application/Handlers/ObjectTypes/UpdateObjectTypeHandler.php';

require_once __DIR__ . '/../../Application/Commands/ObjectTypes/DeleteObjectTypeCommand.php'; 
require_once __DIR__ . '/../../Application/Handlers/ObjectTypes/DeleteObjectTypeHandler.php';

//***CONTROLLER DEFINITION */
class ObjectTypesController {

    private $mediator;
    private $conn;


    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
        $this->mediator = new Mediator();

        // register commands and their handlers
        $this->mediator->register('CreateObjectTypeCommand', 'CreateObjectTypeHandler');
        $this->mediator->register('UpdateObjectTypeCommand', 'UpdateObjectTypeHandler'); 
        $this->mediator->register('DeleteObjectTypeCommand', 'DeleteObjectTypeHandler');
    }

    /**
     * GET /api/objecttypes
     * Retrieves all object types.
     */
    public function getAll() {
        // Direct query to fetch all object types
        try {
            $stmt = $this->conn->prepare("SELECT * FROM objecttype");
            $stmt->execute();  
            $types = $stmt->fetchAll(PDO::FETCH_ASSOC);
            Response::success($types);
        } catch (Exception $e) {
            Response::error("Error retrieving object types: " . $e->getMessage(), "DB_ERROR", 500);
        }
    }

    /**
     * POST /api/objecttypes
     * Creates a new object type.
     */
    public function create() {
        $data = json_decode(file_get_contents("php://input"), true);

        try {
            // Create the command with data from request
            $command = new CreateObjectTypeCommand(
                $data['code'] ?? null,
                $data['name'] ?? null,
                $data['description'] ?? null,
                $data['model'] ?? null,
                $data['brand'] ?? null,
                $data['quantity'] ?? 0
            );

            // Send the command via the mediator
            $newCode = $this->mediator->send($command);
            Response::success(["message" => "Object Type created", "code" => $newCode], 201);

        } catch (Exception $e) {
            Response::error($e->getMessage(), "CREATE_ERROR", 400);
        }
    }

    /**
     * PUT /api/objecttypes/{code}
     * Updates an existing object type.
     */
    public function update($code) {
        $data = json_decode(file_get_contents("php://input"), true);

        try {
            // Il $code arriva dall'URL, il resto dal body JSON
            $command = new UpdateObjectTypeCommand(
                $code,
                $data['name'] ?? null,
                $data['description'] ?? null,
                $data['model'] ?? null,
                $data['brand'] ?? null,
                $data['quantity'] ?? 0
            );

            $this->mediator->send($command);

            Response::success(["message" => "Object Type updated successfully"]);

        } catch (Exception $e) {
            Response::error($e->getMessage(), "UPDATE_ERROR", 400);
        }
    }
    /**
     * DELETE /api/objecttypes/{code}
     * Deletes an existing object type.
     */
    public function delete($code) {
    try {
        $command = new DeleteObjectTypeCommand($code);
        $this->mediator->send($command);
        Response::success(["message" => "Object Type deleted successfully"]);
    } catch (Exception $e) {
        Response::error($e->getMessage(), "DELETE_ERROR", 400);
    }
}

}