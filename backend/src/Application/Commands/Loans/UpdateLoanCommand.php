<?php

require_once __DIR__."/../../../Domain/Entities/InventoryItem.php";

class UpdateLoanCommand {
    public int $id;
    public string $notes;
    public ?array $objects;

    public function __construct(int $id, string $notes, ?InventoryItem ...$objects) {
        $this->id = $id;
        $this->notes = $notes;
        $this->objects = $objects;
    }

}