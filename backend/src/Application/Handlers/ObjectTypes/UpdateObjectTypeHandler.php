<?php

// Handler class to process UpdateObjectTypeCommand
require_once __DIR__ . '/../../../../config/database.php';
require_once __DIR__ . '/../../Commands/ObjectTypes/UpdateObjectTypeCommand.php';

// Import Database connection and Command class
class UpdateObjectTypeHandler {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    // Handle the UpdateObjectTypeCommand
    public function handle(UpdateObjectTypeCommand $command) {
        // Validation of required fields
        if (empty($command->code)) {
            throw new Exception("Codice oggetto mancante");
        }

        // Update query
        $sql = "UPDATE objecttype 
                SET name = :name, 
                    description = :description, 
                    model = :model, 
                    brand = :brand, 
                    quantity = :quantity 
                WHERE code = :code";
        
        $stmt = $this->conn->prepare($sql);
        
        // Bind parameters and execute
        $stmt->execute([
            ':code' => $command->code,
            ':name' => $command->name,
            ':description' => $command->description,
            ':model' => $command->model,
            ':brand' => $command->brand,
            ':quantity' => $command->quantity
        ]);

        
        return true;
    }
}