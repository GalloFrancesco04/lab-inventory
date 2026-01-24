<?php

class InventoryItem {
    private ?int $id;
    private string $objectTypeCode;
    private ItemState $state;
    private ?string $location;

    public function __construct(?int $id, string $objectTypeCode, ItemState $state, ?string $location = null) {
        $this->id = $id;
        $this->objectTypeCode = $objectTypeCode;
        $this->state = $state;
        $this->location = $location;
    }


    public function setState(ItemState $state): void {
        $this->state = $state;
    }

    public function getState(): ItemState {
        return $this->state;
    }

    public function loan(): void {
        $this->state->handleLoan($this);
    }

    public function available(): void {
        $this->state->handleAvailable($this);
    }

    public function outOfService(): void {
        $this->state->handleOutOfService($this);
    }

    public function damaged() : void {
        $this->state->handleDamaged($this);
    }

    public function maintenance() : void {
        $this->state->handleMaintenance($this);
    }

    public function getId(): ?int { return $this->id; }
    public function getStateCode() : int { return $this->state->getStateCode();}
    public function getObjectTypeCode(): string { return $this->objectTypeCode; }
    public function getLocation(): ?string { return $this->location; }
}