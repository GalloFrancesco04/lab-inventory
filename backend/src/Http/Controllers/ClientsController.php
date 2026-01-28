<?php

//******** REQUIRED IMPORTS */
// Import utility and config files
require_once __DIR__ . '/../Response.php'; 
require_once __DIR__ . '/../../../config/Database.php';

// Import Request class
require_once __DIR__ . '/../../Http/Request.php';

// Import Mediator components
require_once __DIR__ . '/../../Application/Mediator/Mediator.php';

// Import specific Command and Handler for Client Creation
require_once __DIR__ . '/../../Application/Commands/Clients/CreateClientCommand.php';
require_once __DIR__ . '/../../Application/Handlers/Clients/CreateClientHandler.php';

// Import specific Command and Handler for Client Update
require_once __DIR__ . '/../../Application/Commands/Clients/UpdateClientCommand.php';
require_once __DIR__ . '/../../Application/Handlers/Clients/UpdateClientHandler.php';



// Import specific Commands and Handlers for Blocking/Unblocking Clients
require_once __DIR__ . '/../../Application/Commands/Clients/BlockClientCommand.php';
require_once __DIR__ . '/../../Application/Handlers/Clients/BlockClientHandler.php';
require_once __DIR__ . '/../../Application/Commands/Clients/UnblockClientCommand.php';
require_once __DIR__ . '/../../Application/Handlers/Clients/UnblockClientHandler.php';

// Import Commands and Handlers for Viewing Clients
require_once __DIR__ . '/../../Application/Commands/Clients/ViewClientsCommand.php';
require_once __DIR__ . '/../../Application/Handlers/Clients/ViewClientsHandler.php';
require_once __DIR__ . '/../../Application/Commands/Clients/ViewSingleClientCommand.php';
require_once __DIR__ . '/../../Application/Handlers/Clients/ViewSingleClientHandler.php';
//******** CONTROLLER DEFINITION */
class ClientsController {

    private $mediator;
   

    public function __construct() {
       
       

        // Initialize Mediator
        $this->mediator = new Mediator();

        // REGISTRATION: Map the Command to its Handler
        // When 'CreateClientCommand' is dispatched, 'CreateClientHandler' handles it.
        $this->mediator->register(CreateClientCommand::class, CreateClientHandler::class);
        $this->mediator->register(UpdateClientCommand::class, UpdateClientHandler::class);
      //$this->mediator->register(DeleteClientCommand::class, DeleteClientHandler::class);
        $this->mediator->register(BlockClientCommand::class, BlockClientHandler::class);
        $this->mediator->register(UnblockClientCommand::class, UnblockClientHandler::class);
        $this->mediator->register(ViewClientsCommand::class, ViewClientsHandler::class);
        $this->mediator->register(ViewSingleClientCommand::class, ViewSingleClientHandler::class);
    }
    /**
     * GET /api/clients
     * Retrieves the list of all clients.
     */
    public function getAll(Request $request) {
        try {
            // Use ViewClientsCommand to get all clients
            $command = new ViewClientsCommand();
            $clients = $this->mediator->send($command);
            // Return successful JSON response
            Response::success($clients);

        } catch (Exception $e) {
            // Handle errors using standardized Response
            Response::error("Error retrieving clients: " . $e->getMessage(), "DB_ERROR", 500);
        }
    }

 // GET /api/clients/{id }
    public function getById(Request $request, $id) {
        try {
            // Use ViewSingleClientCommand to get client by ID
            $command = new ViewSingleClientCommand((int)$id);
            $client = $this->mediator->send($command);
            Response::success($client);
        } catch (Exception $e) {
            Response::error($e->getMessage(), "NOT_FOUND", 404);
        }
    }

        public function create(Request $request) {
            // Decode the JSON body from the request
            $data = $request->getBody();
    
            try {
                //  Instantiate the Command with raw data
                
                // Retrieve user ID from request data or set to null if not present
                //$userId = $data['user_id'];
                $name = $data['name'];
                $email = $data['email'];
                $surname = $data['surname'];
                $state = $data['state'];
                $command = new CreateClientCommand($name, $email, $surname, $state);

            //  Dispatch the command to the Mediator
            // The Mediator will find the correct Handler and execute the logic
            $newId = $this->mediator->send($command);

            //  Return success response with the new ID
            Response::success(["message" => "Client created successfully", "id" => $newId], 201);

        } catch (Exception $e) {
        // Catch validation or database errors from the Handler
                Response::error($e->getMessage(), "CREATE_ERROR", 400);
        }
    }

    /**
    * PUT /api/clients/{id}
    * Updates an existing client using the CQRS pattern.
    */
    public function update(Request $request, $id) {
        $data = $request->getBody();

        try {
            // Create the UpdateClientCommand with ID and updated data
            $name = $data['name'] ?? null;
            $surname = $data['surname'] ?? null;
            $command = new UpdateClientCommand(
                $id, $name, $surname
            );

            // send the command via the Mediator
            $this->mediator->send($command);

            Response::success(["message" => "Client updated successfully"]);

        } catch (Exception $e) {
            Response::error($e->getMessage(), "UPDATE_ERROR", 400);
        }
    }

   

    /**
    * POST /api/clients/block
    * Blocks or Unblocks a client.
    */
    public function toggleBlock(Request $request) {
        $data = $request->getBody();

        $id = $data['id'] ?? null;
        $action = $data['action'] ?? null; // 'block' o 'unblock'
        // validation
        try {
            if (!$id || !$action) {
                throw new Exception("ID e action (block/unblock) sono richiesti");
            }

            // Decide which command to use based on action
            if ($action === 'block') {
                $command = new BlockClientCommand($id);
                $this->mediator->send($command);
                Response::success(["message" => "Client blocked successfully"]);
            } elseif ($action === 'unblock') {
                $command = new UnblockClientCommand($id);
                $this->mediator->send($command);
                Response::success(["message" => "Client unblocked successfully"]);
            } else {
                throw new Exception("Azione non valida");
            }

        

        } catch (Exception $e) {
            Response::error($e->getMessage(), "BLOCK_ERROR", 400);
        }
    }
}