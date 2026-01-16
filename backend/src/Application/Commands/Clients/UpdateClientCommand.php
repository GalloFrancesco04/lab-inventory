<?php
// Command class to encapsulate data for updating an existing client
class UpdateClientCommand {
    public $id;
    public $email;
    public $name;
    public $surname;
    
// Constructor to initialize all properties
    public function __construct($id, $email, $name, $surname ) {
        $this->id = $id;
        $this->name = $name;
        $this->email = $email;
        $this->surname = $surname;
        
    }
}