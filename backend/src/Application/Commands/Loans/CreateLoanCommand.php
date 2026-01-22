<?php

class CreateLoanCommand {
    private $start_date;
    private $end_date;
    private $client_id;
    private $notes;
    private $quantity;
    private $state;
    private $objects;

    public function __construct($start_date, $end_date, $client_id, $notes, $quantity, $state=1, $objects) {
        $this->start_date = $start_date;
        $this->end_date = $end_date;
        $this->client_id = $client_id;
        $this->notes = $notes;
        $this->quantity = $quantity;
        $this->state = $state;
        $this->objects = $objects;
    }

}