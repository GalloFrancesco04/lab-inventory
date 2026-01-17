<?php

require_once __DIR__ . '/../../../../config/database.php';
require_once __DIR__ . '/../../Commands/Inventory/CreateObjectCommand.php';

// Handler class to process the CreateObjectCommand
class CreateObjectHandler {
    private $conn;

    // Constructor to set up database connection
    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    // Method to handle the command and create the object in the database
    public function handle(CreateObjectCommand $command) {
        if (empty($command->object_type_code)) {
            throw new Exception("Codice tipo oggetto (object_type_code) mancante.");
        }

        
        $sql = "INSERT INTO object_ (object_type_code, state, location) 
                VALUES (:type_code, :state, :location)";
        
        // Prepare and execute the SQL statement
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ':type_code' => $command->object_type_code,
            ':state'     => $command->state,
            ':location'  => $command->location
        ]);

        return $this->conn->lastInsertId();
    }
}