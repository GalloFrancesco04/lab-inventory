<?php

// Command class to encapsulate data for creating a new inventory object
class CreateObjectCommand {
    public string $object_type_code;
    public int $state;
    public ?string $location;

    // Constructor to initialize all properties
    public function __construct(array $data) {
        $this->object_type_code = $data['object_type_code'] ?? '';
        $this->state = (int)($data['state'] ?? 1); 
        $this->location = $data['location'] ?? 'Magazzino';
    }
}