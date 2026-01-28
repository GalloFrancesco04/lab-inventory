<?php

class UpdateOperationCommand {
    public $id;
    public $description;
    public $end_date;
    public $cost;

    public function __construct($id, $description = null, $end_date = null, $cost = null) {
        $this->id = $id;
        $this->description = $description;
        $this->end_date = $end_date;
        $this->cost = $cost;
    }
}