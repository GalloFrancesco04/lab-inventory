<?php
// Command class to encapsulate data for updating an existing inventory object
class UpdateObjectCommand {
    public int $id;
    public int $state;
    public string $location;
    // Constructor to initialize all properties
    public function __construct(int $id, array $data) {
        $this->id = $id;
        $this->state = (int)($data['state'] ?? 1);
        $this->location = $data['location'] ?? '';
    }
}