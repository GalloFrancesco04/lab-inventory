<?php
// Handler class to process the SendToMaintenanceCommand
require_once __DIR__ . '/../../../../config/database.php';
require_once __DIR__ . '/../../Commands/Inventory/SendToMaintenanceCommand.php';

// Handler class to process the SendToMaintenanceCommand
class SendToMaintenanceHandler {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }
    //  Method to handle the command and send the object to maintenance
    public function handle(SendToMaintenanceCommand $command) {
        
        if ($command->id <= 0 || $command->technician_id <= 0) {
            throw new Exception("ID Oggetto o ID Tecnico non validi.");
        }

        try {
            
            $this->conn->beginTransaction();

            //  Update object state to 'in maintenance' (assuming state code 3)
            $sqlObj = "UPDATE object_ SET state = 3 WHERE id = :id";
            $stmtObj = $this->conn->prepare($sqlObj);
            $stmtObj->execute([':id' => $command->id]);

            //  Insert maintenance record
            $sqlMaint = "INSERT INTO maintenance (object_id, description, start_date, technician_id) 
                         VALUES (:obj_id, :desc, CURDATE(), :tech_id)";
            
            $stmtMaint = $this->conn->prepare($sqlMaint);
            $stmtMaint->execute([
                ':obj_id'  => $command->id,
                ':desc'    => $command->note,
                ':tech_id' => $command->technician_id 
            ]);

            $this->conn->commit();
            return true;

        } catch (Exception $e) {
            $this->conn->rollBack();
           
            throw $e; 
        }
    }
}