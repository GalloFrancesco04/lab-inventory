<?php


class SendToMaintenanceCommand {
    public $object_id;             
    public $description;
    public $technician_id;  
    public $type;

    public function __construct($object_id, $description, $technician_id, $type) {
        $this->object_id = $object_id;
        $this->description = $description;
        $this->technician_id = $technician_id;
        $this->type = $type;

    }
}