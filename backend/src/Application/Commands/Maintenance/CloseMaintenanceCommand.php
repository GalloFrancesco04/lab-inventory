<?php

class CloseMaintenanceCommand {

    public $id;
    public $damaged;

    public function __construct($id, $damaged = 0) {
        $this->id = $id;
        $this->damaged = $damaged;
    }
}