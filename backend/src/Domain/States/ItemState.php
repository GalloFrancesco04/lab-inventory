<?php

interface ItemState {
    public function getStateCode(): int;
    public function handleLoan(InventoryItem $item): void;
    public function handleAvailable(InventoryItem $item): void;
    public function handleOutOfService(InventoryItem $item): void;
    public function handleDamaged(InventoryItem $item): void;
    public function handleMaintenance(InventoryItem $item): void;
}