<?php
// Command class to encapsulate data for creating a new client
class CreateClientCommand {
    public $name;
    public $email;
    public $phone;
    public $address;

    public function __construct($name, $email, $phone = null, $address = null) {
        $this->name = $name;
        $this->email = $email;
        $this->phone = $phone;
        $this->address = $address;
    }
}