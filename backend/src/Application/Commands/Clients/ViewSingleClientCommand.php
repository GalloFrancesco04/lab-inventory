<?php

class ViewSingleClientCommand {

    public $client_id;

    public function __construct($client_id) {
        $this->client_id = $client_id;
    }
}