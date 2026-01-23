<?php

class CreateLoanCommand {
    public $start_date;
    public $end_date;
    public $client_id;
    public $notes;
    public $state;
    public $objects;

    public function __construct($start_date, $end_date, $client_id, $notes, $objects) {
        $this->start_date = $start_date;
        $this->end_date = $end_date;
        $this->client_id = $client_id;
        $this->notes = $notes;
        $this->state = 1; // this is the loan creation so the state has to be 1
        $this->objects = $objects;
    }

}