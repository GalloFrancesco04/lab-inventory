<?php

class AddOperationCommand {
    public $maintenance_id;
    public $description;
    public $start_date;
    public $cost;

    public function __construct($maintenance_id, $description, $start_date, $cost = 0) {
        $this->maintenance_id = $maintenance_id;
        $this->description = $description;
        $this->start_date = $start_date;
        $this->cost = $cost;
    }
}