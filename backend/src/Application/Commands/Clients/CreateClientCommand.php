<?php
// Command class to encapsulate data for creating a new client
class CreateClientCommand {
    public $name;
    public $email;
    public $surname;
    public $state;

    public function __construct($name, $email, $surname, $state=1) {
        $this->name = $name;
        $this->email = $email;
        $this->surname = $surname;
        $this->state = $state;
    }
}