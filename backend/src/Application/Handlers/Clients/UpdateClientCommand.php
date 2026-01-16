<?php
// Command class to encapsulate data for updating an existing client
class UpdateClientCommand {
    public $id;
    public $name;
    public $email;
    public $phone;
    public $address;
// Constructor to initialize all properties
    public function __construct($id, $name, $email, $phone = null, $address = null) {
        $this->id = $id;
        $this->name = $name;
        $this->email = $email;
        $this->phone = $phone;
        $this->address = $address;
    }
}