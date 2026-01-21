<?php
// Handler class to process UnblockClientCommand
require_once __DIR__ . '/../../../../config/Database.php';
require_once __DIR__ . '/../../Commands/Clients/UnblockClientCommand.php';
// Import Database connection and Command class
class UnblockClientHandler {
    private $conn;
    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }
    // Handle the UnblockClientCommand
    public function handle(UnblockClientCommand $command) {
        $sql = "UPDATE client SET state = 1 WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id' => $command->id]);
        return true;
    }
}