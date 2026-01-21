<?php
// Handler class to process BlockClientCommand
require_once __DIR__ . '/../../../../config/Database.php';
require_once __DIR__ . '/../../Commands/Clients/BlockClientCommand.php';
// Import Database connection and Command class
class BlockClientHandler {
    private $conn;
    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }
    // Handle the BlockClientCommand
    public function handle(BlockClientCommand $command) {
        $sql = "UPDATE client SET state = 0 WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id' => $command->id]);
        return true;
    }
}