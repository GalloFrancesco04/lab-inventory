<?php


// Import utility and config files
require_once __DIR__ . '/../Response.php'; 
require_once __DIR__ . '/../../../config/database.php';

// Import Mediator components
require_once __DIR__ . '/../../Application/Mediator/Mediator.php';

// Import specific Command and Handler for Client Creation
require_once __DIR__ . '/../../Application/Commands/Clients/CreateClientCommand.php';
require_once __DIR__ . '/../../Application/Handlers/Clients/CreateClientHandler.php';

// Import specific Command and Handler for Client Update
require_once __DIR__ . '/../../Application/Commands/Clients/UpdateClientCommand.php';
require_once __DIR__ . '/../../Application/Handlers/Clients/UpdateClientHandler.php';

class ClientsController {

    private $mediator;
    private $conn; // Required for getAll (until refactored to Query pattern)

    public function __construct() {
        // Initialize Database connection (Legacy for getAll)
        $db = new Database();
        $this->conn = $db->getConnection();

        // Initialize Mediator
        $this->mediator = new Mediator();

        // REGISTRATION: Map the Command to its Handler
        // When 'CreateClientCommand' is dispatched, 'CreateClientHandler' handles it.
        $this->mediator->register('CreateClientCommand', 'CreateClientHandler');
        $this->mediator->register('UpdateClientCommand', 'UpdateClientHandler');
    }

    /**
     * GET /api/clients
     * Retrieves the list of all clients.
     */
    public function getAll() {
        try {
            // Execute direct query (To be refactored to separate Query class later)
            $stmt = $this->conn->prepare("SELECT id, name, email, phone, address FROM clients");
            $stmt->execute();  
            $clients = $stmt->fetchAll(PDO::FETCH_ASSOC); // Fetch as associative array
            
            // Return successful JSON response
            Response::success($clients);

        } catch (Exception $e) {
            // Handle errors using standardized Response
            Response::error("Error retrieving clients: " . $e->getMessage(), "DB_ERROR", 500);
        }
    }

    /**
     * POST /api/clients
     * Creates a new client using the CQRS pattern.
     */
    public function create() {
        //  Decode the JSON body from the request
        $data = json_decode(file_get_contents("php://input"), true);

        try {
            //  Instantiate the Command with raw data
            // (Using null coalescing '??' for optional fields)
            $command = new CreateClientCommand(
                $data['name'] ?? null,
                $data['email'] ?? null,
                $data['phone'] ?? null,
                $data['address'] ?? null
            );

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
    public function update($id) {
        $data = json_decode(file_get_contents("php://input"), true);

        try {
            // Creiamo il comando di aggiornamento
            $command = new UpdateClientCommand(
                $id, // L'ID arriva dall'URL (tramite il Router)
                $data['name'] ?? null,
                $data['email'] ?? null,
                $data['phone'] ?? null,
                $data['address'] ?? null
            );

            // Inviamo al Mediator
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
    public function toggleBlock() {
      
    }
}