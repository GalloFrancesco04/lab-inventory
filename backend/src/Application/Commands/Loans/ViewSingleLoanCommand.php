<?php

class ViewSingleLoanCommand {
    public $id;

    public function __construct($id) {
        $this->id = $id;
    }
}