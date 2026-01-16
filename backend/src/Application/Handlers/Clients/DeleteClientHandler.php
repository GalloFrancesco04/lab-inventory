<?php
// Handler class to process DeleteClientCommand
require_once __DIR__ . '/../../../../config/database.php';
require_once __DIR__ . '/../../Commands/Clients/DeleteClientCommand.php';
// Import Database connection and Command class
class DeleteClientHandler {
    private $conn;
    // Initialize with Database connection
    public function __construct() {
        // Initialize Database connection
        $db = new Database();
        $this->conn = $db->getConnection();
    }
    // Handle the DeleteClientCommand
    public function handle(DeleteClientCommand $command) {
        // Validate that ID is provided
        if (empty($command->id)) {
            throw new Exception("ID required for deletion");
        }

        // Query DELETE
        $sql = "DELETE FROM client WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        
        $stmt->execute([':id' => $command->id]);

        // Check if any row was affected
        if ($stmt->rowCount() === 0) {
            throw new Exception("Client not found or already deleted");
        }

        return true;
    }
}