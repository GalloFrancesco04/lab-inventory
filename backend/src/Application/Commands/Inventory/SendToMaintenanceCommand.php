<?php


class SendToMaintenanceCommand {
    public int $id;             
    public string $note;        
    public int $technician_id;  

    // Constructor to initialize all properties
    public function __construct(array $data) {
        $this->id = (int)($data['id'] ?? 0);
        $this->note = $data['note'] ?? '';
        
        //if technician_id is not provided, set to 0 (as default)
        $this->technician_id = (int)($data['technician_id'] ?? 0);
    }
}