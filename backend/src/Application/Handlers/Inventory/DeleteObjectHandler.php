<?php
require_once __DIR__ . '/../../../../config/Database.php';
require_once __DIR__ . '/../../Commands/Inventory/DeleteObjectCommand.php';

// Handler class to process the DeleteObjectCommand
class DeleteObjectHandler {
    private $conn;
    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    // Method to handle the command and delete the object from the database
    public function handle(DeleteObjectCommand $command) {
        $sql = "DELETE FROM object_ WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id' => $command->id]);
        return true;
    }
}