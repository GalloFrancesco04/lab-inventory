<?php
// Command class to encapsulate data for updating an object type
class UpdateObjectTypeCommand {
    public $code; // La chiave primaria per identificare cosa modificare
    public $name;
    public $description;
    public $model;
    public $brand;

    // Constructor to initialize all properties
    public function __construct($code, $name, $description = null, $model = null, $brand = null) {
        $this->code = $code;
        $this->name = $name;
        $this->description = $description;
        $this->model = $model;
        $this->brand = $brand;
    }
}