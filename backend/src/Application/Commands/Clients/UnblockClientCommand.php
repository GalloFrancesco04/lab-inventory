<?php
// Command class to encapsulate data for blocking a client
class UnblockClientCommand {
    public $id;
    public function __construct($id) {
        $this->id = $id;
    }
}