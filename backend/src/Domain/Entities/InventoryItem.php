<?php

require_once __DIR__ . '/../States/AvailableState.php';
require_once __DIR__ . '/../States/LoanedState.php';
require_once __DIR__ . '/../States/InMaintenanceState.php';
require_once __DIR__ . '/../States/DamagedState.php';
require_once __DIR__ . '/../States/OutOfServiceState.php';

class InventoryItem {
    private ?int $id;
    private string $objectTypeCode;
    private ItemState $state;
    private ?string $location;

    public function __construct(?int $id, string $objectTypeCode, int $state, ?string $location = null) {
        $this->id = $id;
        $this->objectTypeCode = $objectTypeCode;
        
        // I map the states
        switch($state) {
            case 1:
                $this->state = new AvailableState();
                break;
            case 2:
                $this->state = new LoanedState();
                break;
            case 3:
                $this->state = new InMaintenanceState();
                break;
            case 4:
                $this->state = new DamagedState();
                break;
            case 5:
                $this->state = new OutOfServiceState();
                break;
            default:
                $this->state = null;
                break;
        }

        $this->location = $location;
    }

    public function setId(int $id) : void {
        $this->id = $id;
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