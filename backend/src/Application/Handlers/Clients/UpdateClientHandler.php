<?php
// Handler class to process UpdateClientCommand
require_once __DIR__ . '/../../../../config/database.php';
require_once __DIR__ . '/../../Commands/Clients/UpdateClientCommand.php';

class UpdateClientHandler {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    // Handle the UpdateClientCommand 
    public function handle(UpdateClientCommand $command) {
        
        // Validate that ID is provided
        if (empty($command->id)) {
            throw new Exception("ID cliente mancante per l'aggiornamento");
        }

        // Update client in the database
        $sql = "UPDATE clients 
                SET name = :name, email = :email, phone = :phone, address = :address 
                WHERE id = :id";
        
        $stmt = $this->conn->prepare($sql);
        
        // Bind parameters and execute
        $stmt->execute([
            ':id' => $command->id,
            ':name' => $command->name,
            ':email' => $command->email,
            ':phone' => $command->phone,
            ':address' => $command->address
        ]);

       
        if ($stmt->rowCount() === 0) {
           
        }

        return true;
    }
}
    
