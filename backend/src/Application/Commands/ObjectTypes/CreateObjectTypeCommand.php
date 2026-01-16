<?php
// Command class to encapsulate data for creating a new object type
class CreateObjectTypeCommand {
    public $code;
    public $name;
    public $description;
    public $model;
    public $brand;
    public $quantity;

    //  Constructor to initialize all properties
    public function __construct($code, $name, $description = null, $model = null, $brand = null, $quantity = 0) {
        $this->code = $code;
        $this->name = $name;
        $this->description = $description;
        $this->model = $model;
        $this->brand = $brand;
        $this->quantity = $quantity;
    }
}