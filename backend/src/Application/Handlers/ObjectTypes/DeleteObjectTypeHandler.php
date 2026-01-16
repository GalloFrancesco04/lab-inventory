<?php
// Include necessary files and namespaces
require_once __DIR__ . '/../../../../config/database.php';
require_once __DIR__ . '/../../Commands/ObjectTypes/DeleteObjectTypeCommand.php';

// Handler class to process DeleteObjectTypeCommand
class DeleteObjectTypeHandler {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

        // Handle the DeleteObjectTypeCommand
    public function handle(DeleteObjectTypeCommand $command) {
        // Query sulla tabella objecttype (come da tuo DB)
        $sql = "DELETE FROM objecttype WHERE code = :code";
        
        //  Prepare and execute the statement
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':code' => $command->code]);

        
        if ($stmt->rowCount() === 0) {
            throw new Exception("Tipo oggetto non trovato o già eliminato.");
        }

        return true;
    }
}