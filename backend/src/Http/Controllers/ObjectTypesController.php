<?php

//***REQUIRED IMPORTS */
require_once __DIR__ . '/../Response.php'; 
require_once __DIR__ . '/../../../config/Database.php';
require_once __DIR__ . '/../../Application/Mediator/Mediator.php';
require_once __DIR__ . '/../../Http/Request.php';

// Import Create Components
require_once __DIR__ . '/../../Application/Commands/ObjectTypes/CreateObjectTypeCommand.php';
require_once __DIR__ . '/../../Application/Handlers/ObjectTypes/CreateObjectTypeHandler.php';

// Import Update Components
require_once __DIR__ . '/../../Application/Commands/ObjectTypes/UpdateObjectTypeCommand.php';
require_once __DIR__ . '/../../Application/Handlers/ObjectTypes/UpdateObjectTypeHandler.php';

require_once __DIR__ . '/../../Application/Commands/ObjectTypes/DeleteObjectTypeCommand.php'; 
require_once __DIR__ . '/../../Application/Handlers/ObjectTypes/DeleteObjectTypeHandler.php';

// Import View Components
require_once __DIR__ . '/../../Application/Commands/ObjectTypes/ViewObjectTypesCommand.php'; 
require_once __DIR__ . '/../../Application/Handlers/ObjectTypes/ViewObjectTypesHandler.php';
require_once __DIR__ . '/../../Application/Commands/ObjectTypes/ViewSingleObjectTypeCommand.php'; 
require_once __DIR__ . '/../../Application/Handlers/ObjectTypes/ViewSingleObjectTypeHandler.php';

//***CONTROLLER DEFINITION */
class ObjectTypesController {

    private $mediator;
    private $conn;


    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
        $this->mediator = new Mediator();

        // register commands and their handlers
        $this->mediator->register(CreateObjectTypeCommand::class, CreateObjectTypeHandler::class);
        $this->mediator->register(UpdateObjectTypeCommand::class, UpdateObjectTypeHandler::class); 
        $this->mediator->register(DeleteObjectTypeCommand::class, DeleteObjectTypeHandler::class);
        $this->mediator->register(ViewObjectTypesCommand::class, ViewObjectTypesHandler::class);
        $this->mediator->register(ViewSingleObjectTypeCommand::class, ViewSingleObjectTypeHandler::class);
    }

    /**
     * GET /api/objecttypes
     * Retrieves all object types.
     */
    public function getAll(Request $request) {
        // Direct query to fetch all object types
        try {
            $command = new ViewObjectTypesCommand();
            $types = $this->mediator->send($command);
            Response::success($types);
        } catch (Exception $e) {
            Response::error("Error retrieving object types: " . $e->getMessage(), "DB_ERROR", 500);
        }
    }

    /**
     * GET /api/objecttypes/{code}
     * Retrieves a specific object type by its code.
     */
    public function getByCode(Request $request, $code) {
        try {
            $command = new ViewSingleObjectTypeCommand($code);
            $type = $this->mediator->send($command);
            Response::success($type);
        } catch (Exception $e) {
            Response::error($e->getMessage(), "DB_ERROR", 500);
        }
    }

    /**
     * POST /api/objecttypes
     * Creates a new object type.
     */
    public function create(Request $request) {
        $data = $request->getBody();

        try {
            // Create the command with data from request
            $command = new CreateObjectTypeCommand($data["code"], $data["name"], $data["description"], $data["model"], $data["brand"]);

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
    public function update(Request $request, $code) {
        $data = $request->getBody();

        try {
            // Il $code arriva dall'URL, il resto dal body JSON
            $command = new UpdateObjectTypeCommand(
                $code,
                $data["name"],
                $data["description"],
                $data["model"],
                $data["brand"]
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
    public function delete(Request $request, $code) {
    try {
        $command = new DeleteObjectTypeCommand($code);
        $this->mediator->send($command);
        Response::success(["message" => "Object Type deleted successfully"]);
    } catch (Exception $e) {
        Response::error($e->getMessage(), "DELETE_ERROR", 400);
    }
}

}