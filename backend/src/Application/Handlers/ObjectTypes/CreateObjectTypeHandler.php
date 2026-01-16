<?php
// Include necessary files and namespaces
require_once __DIR__ . '/../../../../config/database.php';
require_once __DIR__ . '/../../Commands/ObjectTypes/CreateObjectTypeCommand.php';

// Handler class to process CreateObjectTypeCommand
class CreateObjectTypeHandler {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }
    // Handle the CreateObjectTypeCommand 
    public function handle(CreateObjectTypeCommand $command) {
        // Query SQL basata esattamente sul tuo DB
        $sql = "INSERT INTO objecttype (code, name, description, model, brand, quantity) 
                VALUES (:code, :name, :description, :model, :brand, :quantity)";
        
        $stmt = $this->conn->prepare($sql);
        
        // Bind parameters and execute
        $stmt->execute([
            ':code'        => $command->code,
            ':name'        => $command->name,
            ':description' => $command->description,
            ':model'       => $command->model,
            ':brand'       => $command->brand,
            ':quantity'    => $command->quantity
        ]);

        return $command->code;
    }
}