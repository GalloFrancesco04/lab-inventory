<?php

class CreateObjectCommand {
    public string $object_type_code;
    public ?string $location;

    public function __construct($object_type_code, $location = null) {
        $this->object_type_code = $object_type_code;
        $this->location = $location;
    }
}