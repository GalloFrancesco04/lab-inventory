<?php
require_once __DIR__ . '/../../../../config/database.php';
require_once __DIR__ . '/../../Commands/Inventory/UpdateObjectCommand.php';

class UpdateObjectHandler {
    private $conn;

    // Constructor to set up database connection
    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    // Method to handle the command and update the object in the database
    public function handle(UpdateObjectCommand $command) {
        $sql = "UPDATE object_ SET state = :state, location = :location WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        // Prepare and execute the SQL statement
        $stmt->execute([
            ':state' => $command->state,
            ':location' => $command->location,
            ':id' => $command->id
        ]);
        return true;
    }
}