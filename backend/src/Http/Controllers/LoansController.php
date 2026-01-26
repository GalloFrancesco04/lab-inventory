<?php

// IMPORTS
require_once __DIR__ . '/../Response.php';
require_once __DIR__ . '/../../Application/Mediator/Mediator.php';
require_once __DIR__ . '/../../../config/Database.php';
require_once __DIR__ . '/../../Http/Request.php';

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

    /**
     * GET /api/loans
     
     */
    public function getAll(Request $request) {
        try {
            // Use ViewLoansCommand to get all loans
            $command = new ViewLoansCommand();
            $loans = $this->mediator->send($command);
            
            Response::success($loans);
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
            $loan = $this->mediator->send($command);

            // if loan not found, the handler throws an exception
            // but if we reach here, the loan was found
            Response::success($loan);
        } catch (Exception $e) {
            // if loan not found, the handler throws an exception
            Response::error($e->getMessage(), "NOT_FOUND", 404);
        }
    }

    /**
     * POST /api/loans
     
     */
    public function create(Request $request) {
        $data = $request->getBody();
        try {
            // Assumiamo che CreateLoanCommand accetti l'array $data
            $command = new CreateLoanCommand($data);
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
            $command = new UpdateLoanCommand((int)$id, $data);
            $this->mediator->send($command);
            Response::success(["message" => "Loan updated successfully"]);
        } catch (Exception $e) {
            Response::error($e->getMessage(), "UPDATE_ERROR", 400);
        }
    }

    /**
     * DELETE /api/loans/{id}
     
     */
    public function delete(Request $request, $id) {
        try {
            
            $command = new CloseLoanCommand((int)$id);
            $this->mediator->send($command);
            Response::success(["message" => "Loan closed successfully"]);
        } catch (Exception $e) {
            Response::error($e->getMessage(), "DELETE_ERROR", 400);
        }
    }
}