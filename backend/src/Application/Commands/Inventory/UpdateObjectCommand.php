<?php
// Command class to encapsulate data for updating an existing inventory object
class UpdateObjectCommand {
    public int $id;
    public ?string $location;

    public function __construct(int $id, ?string $location) {
        $this->id = $id;
        $this->location = $location;
    }
}