<?php

class CreateLoanCommand {
    private $start_date;
    private $end_date;
    private $return_date;
    private $client_id;
    private $notes;
    private $quantity;
    private $state;

    public function __construct($start_date, $end_date, $return_date, $client_id, $notes, $quantity, $state=1) {
        $this->start_date = $start_date;
        $this->end_date = $end_date;
        $this->return_date = $end_date;
        $this->client_id = $client_id;
        $this->notes = $notes;
        $this->quantity = $quantity;
        $this->state = $state;
    }

}