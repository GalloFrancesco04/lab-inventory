<?php
// Command class to encapsulate data for deleting a client
class DeleteClientCommand {
    public $id;
    public function __construct($id) {
        $this->id = $id;
    }
}
