<?php
// Handler class to process CreateClientCommand
require_once __DIR__ . '/../../../../config/database.php';
require_once __DIR__ . '/../../Commands/Clients/CreateClientCommand.php';
// Import Database connection and Command class
class CreateClientHandler {
    private $conn;

    // Initialize with Database connection
    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    // Handle the CreateClientCommand
    public function handle(CreateClientCommand $command) {
        if (empty($command->name) || empty($command->email)) {
             throw new Exception("Nome ed Email sono obbligatori");
        }

        // Insert new client into the database
        $sql = "INSERT INTO clients (name, email, phone, address) VALUES (:name, :email, :phone, :address)";
        $stmt = $this->conn->prepare($sql);
        
        // Bind parameters and execute
        $stmt->execute([
            ':name' => $command->name,
            ':email' => $command->email,
            ':phone' => $command->phone,
            ':address' => $command->address
        ]);

        // Return the ID of the newly created client
        return $this->conn->lastInsertId();
    }
}