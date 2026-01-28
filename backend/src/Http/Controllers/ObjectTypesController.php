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


// Import View Components
require_once __DIR__ . '/../../Application/Commands/ObjectTypes/ViewObjectTypesCommand.php'; 
require_once __DIR__ . '/../../Application/Handlers/ObjectTypes/ViewObjectTypesHandler.php';
require_once __DIR__ . '/../../Application/Commands/ObjectTypes/ViewSingleObjectTypeCommand.php'; 
require_once __DIR__ . '/../../Application/Handlers/ObjectTypes/ViewSingleObjectTypeHandler.php';

//***CONTROLLER DEFINITION */
class ObjectTypesController {

    private $mediator;
   


    public function __construct() {
      
        $this->mediator = new Mediator();

        // register commands and their handlers
        $this->mediator->register(CreateObjectTypeCommand::class, CreateObjectTypeHandler::class);
        $this->mediator->register(UpdateObjectTypeCommand::class, UpdateObjectTypeHandler::class); 
        $this->mediator->register(ViewObjectTypesCommand::class, ViewObjectTypesHandler::class);
        $this->mediator->register(ViewSingleObjectTypeCommand::class, ViewSingleObjectTypeHandler::class);
    }

        // Utility to map object type entity to array
    private function mapToArray($type) {
        if (is_array($type)) return $type;

        return [
            // Getter basati sui campi che usi nel create/update
            'code' => method_exists($type, 'getCode') ? $type->getCode() : null,
            'name' => method_exists($type, 'getName') ? $type->getName() : null,
            'description' => method_exists($type, 'getDescription') ? $type->getDescription() : null,
            'model' => method_exists($type, 'getModel') ? $type->getModel() : null,
            'brand' => method_exists($type, 'getBrand') ? $type->getBrand() : null,
        ];
    }

    /**
     * GET /api/objecttypes
     * Retrieves all object types.
     */
    public function getAll(Request $request) {
        // Direct query to fetch all object types
       try {
            $command = new ViewObjectTypesCommand();
            $typesObjects = $this->mediator->send($command);
            
            // Mapping 
            $typesArray = [];
            if (is_array($typesObjects)) {
                foreach ($typesObjects as $type) {
                    $typesArray[] = $this->mapToArray($type);
                }
            }

            Response::success($typesArray);
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
            $typeObject = $this->mediator->send($command);
            
            // Mapping manuale
            $typeArray = $this->mapToArray($typeObject);

            Response::success($typeArray);
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
   
}