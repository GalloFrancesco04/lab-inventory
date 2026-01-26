<?php


class SendToMaintenanceCommand {
    public $object_id;             
    public $description;
    public $technician_id;  
    public $start_date;
    public $end_date;
    public $type;
    public $phone_number;
    public $cost;

    public function __construct($object_id, $description, $technician_id, $start_date, $end_date, $type, $phone_number, $cost) {
        $this->id = $object_id;
        $this->description = $description;
        $this->technician_id = $technician_id;
        $this->start_date = $start_date;
        $this->end_date = $end_date;
        $this->type = $type;
        $this->phone_number = $phone_number;
        $this->cost = $cost;
    }
}