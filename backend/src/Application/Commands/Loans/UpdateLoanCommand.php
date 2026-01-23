<?php

class UpdateLoanCommand {
    public $notes;
    public $objects;

    public function __construct($notes, $objects) {
        $this->notes = $notes;
        $this->objects = $objects;
    }

}