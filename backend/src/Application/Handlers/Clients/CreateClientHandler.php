<?php
// Handler class to process CreateClientCommand
require_once __DIR__ . '/../../../../config/Database.php';
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
        if (empty($command->name) || empty($command->email)|| empty($command->surname)) {
             throw new Exception("Nome, Email e Cognome sono obbligatori");
        }

        // Insert new client into the database
        $sql = "INSERT INTO client (email, name, surname, state) VALUES (:email, :name, :surname, :state)";
        $stmt = $this->conn->prepare($sql);
        
        // Bind parameters and execute
        $stmt->execute([
            ':name' => $command->name,
            ':email' => $command->email,
            ':surname' => $command->surname,
            ':state' => $command->state
        ]);

        // Return the ID of the newly created client
        return $this->conn->lastInsertId();
    }
}