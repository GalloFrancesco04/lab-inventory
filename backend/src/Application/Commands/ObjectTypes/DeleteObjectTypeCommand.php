<?php
// Command class to encapsulate data for deleting an object type
class DeleteObjectTypeCommand {
    public $code;

    public function __construct($code) {
        $this->code = $code;
    }
}