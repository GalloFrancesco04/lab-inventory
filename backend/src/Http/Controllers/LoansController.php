<?php

// IMPORTS
require_once __DIR__ . '/../Response.php';
require_once __DIR__ . '/../../Application/Mediator/Mediator.php';
require_once __DIR__ . '/../../../config/Database.php';
require_once __DIR__ . '/../../Http/Request.php';
require_once __DIR__ . '/../../Domain/Entities/InventoryItem.php';

// COMMANDS & HANDLERS
require_once __DIR__ . '/../../Application/Commands/Loans/CreateLoanCommand.php';
require_once __DIR__ . '/../../Application/Handlers/Loans/CreateLoanHandler.php';
require_once __DIR__ . '/../../Application/Commands/Loans/UpdateLoanCommand.php';
require_once __DIR__ . '/../../Application/Handlers/Loans/UpdateLoanHandler.php';
require_once __DIR__ . '/../../Application/Commands/Loans/CloseLoanCommand.php';
require_once __DIR__ . '/../../Application/Handlers/Loans/CloseLoanHandler.php';
require_once __DIR__ . '/../../Application/Commands/Loans/ViewLoansCommand.php';
require_once __DIR__ . '/../../Application/Handlers/Loans/ViewLoansHandler.php';
require_once __DIR__ . '/../../Application/Commands/Loans/ViewSingleLoanCommand.php';
require_once __DIR__ . '/../../Application/Handlers/Loans/ViewSingleLoanHandler.php';

class LoansController {
    private $mediator;
    private $conn;

    public function __construct() {
        $this->mediator = new Mediator();
        // Database connection for legacy methods
        $db = new Database();
        $this->conn = $db->getConnection();

        
        $this->mediator->register(CreateLoanCommand::class, CreateLoanHandler::class);
        $this->mediator->register(UpdateLoanCommand::class, UpdateLoanHandler::class);
        $this->mediator->register(CloseLoanCommand::class, CloseLoanHandler::class); // Mapped Close instead of Delete
        $this->mediator->register(ViewLoansCommand::class, ViewLoansHandler::class);
        $this->mediator->register(ViewSingleLoanCommand::class, ViewSingleLoanHandler::class);
    }


    // Utility to map loan entity to array
    private function mapToArray($loan) {
        if (is_array($loan)) return $loan;

        return [
            'id' => method_exists($loan, 'getId') ? $loan->getId() : null,
            'client_id' => method_exists($loan, 'getClientId') ? $loan->getClientId() : null,
            'start_date' => $loan->getStartDate()->format('Y-m-d'),
            'end_date' => $loan->getEndDate()->format('Y-m-d'),
            'return_date' => $loan->getReturnDate()?->format('Y-m-d'),
            'state' => $loan->getState(),
            'note' => $loan->getNotes(),
            'quantity' => $loan->getQuantity(),
            'loaned_objects' => array_map(fn($lo) => [
                'object_id' => $lo->getObjectId(),
                'object_type_code' => $lo->getObjectTypeCode(),
                'damaged' => $lo->isDamaged(),
            ], $loan->loanedObjects ?? [])
        ];
    }

    /**
     * GET /api/loans
     
     */
    public function getAll(Request $request) {
       try {
            $command = new ViewLoansCommand();
            $loansObjects = $this->mediator->send($command);
            
            // Mapping manuale
            $loansArray = [];
            if (is_array($loansObjects)) {
                foreach ($loansObjects as $loan) {
                    $loansArray[] = $this->mapToArray($loan);
                }
            }
            
            Response::success($loansArray);
        } catch (Exception $e) {
            Response::error($e->getMessage(), "DB_ERROR", 500);
        }
    }

    /**
     * GET /api/loans/{id}
     
     */
    public function getById(Request $request, $id) {
       try {
            $command = new ViewSingleLoanCommand((int)$id);
            $loanObject = $this->mediator->send($command);

            // Mapping manuale
            $loanArray = $this->mapToArray($loanObject);

            Response::success($loanArray);
        } catch (Exception $e) {
            Response::error($e->getMessage(), "NOT_FOUND", 404);
        }
    }

    /**
     * POST /api/loans
     
     */
    public function create(Request $request) {
       
         $data = $request->getBody();
        try {
            $client_id = $data['client_id'] ?? null;
            $start_date = $data['start_date'] ?? date('Y-m-d');
            $end_date = $data['end_date'] ?? null;
            $note = $data['note'] ?? null;
            
            // Prepare InventoryItem instances
            $rawItems = $data['inventory_items'] ?? [];
            $objectInstances = [];

            foreach ($rawItems as $itemData) {
                // retrieve id, quantity, state
                $itemId = is_array($itemData) ? ($itemData['id'] ?? null) : $itemData;
                $quantity = is_array($itemData) ? ($itemData['quantity'] ?? 1) : 1;
                $state = is_array($itemData) ? ($itemData['state'] ?? '1') : '1';
                $state=(int)$state;
                // Create InventoryItem instance
                // Not using null coalescing here to ensure id is set
                $item = new InventoryItem($itemId, $quantity, $state);
                
                $objectInstances[] = $item;
            }

            // Create and send the CreateLoanCommand
            $command = new CreateLoanCommand(
                $start_date, 
                $end_date, 
                $client_id, 
                $note, 
                ...$objectInstances
            );

            $id = $this->mediator->send($command);
            Response::success(["message" => "Loan created successfully", "id" => $id], 201);
        } catch (Exception $e) {
            Response::error($e->getMessage(), "CREATE_ERROR", 400);
    }
}

    /**
     * PUT /api/loans/{id}
     
     */
    public function update(Request $request, $id) {
      $data = $request->getBody();
        try {
            $note = $data['note'] ?? '';
            
            // Prepare InventoryItem instances
            $rawItems = $data['inventory_items'] ?? [];
            $objectInstances = [];
            // Create InventoryItem instances
            foreach ($rawItems as $itemData) {
                $itemId = is_array($itemData) ? ($itemData['id'] ?? null) : $itemData;
                $quantity = is_array($itemData) ? ($itemData['quantity'] ?? 1) : 1;
                $state = is_array($itemData) ? ($itemData['state'] ?? '1') : '1';
                $state = (int)$state;

                // Create InventoryItem instance
                $item = new InventoryItem($itemId, $quantity, $state);
                
                $objectInstances[] = $item;
            }

            // Create and send the UpdateLoanCommand
            $command = new UpdateLoanCommand(
                (int)$id, 
                $note, 
                ...$objectInstances
            );

            $this->mediator->send($command);
            Response::success(["message" => "Loan updated successfully"]);
        } catch (Exception $e) {
            Response::error($e->getMessage(), "UPDATE_ERROR", 400);
        }
    }

    /**
     * DELETE /api/loans/{id}
     
     */
    public function close(Request $request, $id) {
        try {
            
            $command = new CloseLoanCommand((int)$id);
            $this->mediator->send($command);
            Response::success(["message" => "Loan closed successfully"]);
        } catch (Exception $e) {
            Response::error($e->getMessage(), "DELETE_ERROR", 400);
        }
    }
}