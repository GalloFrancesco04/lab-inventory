<?php

require_once __DIR__.'/ItemState.php';
class OutOfServiceState implements ItemState {
    public function getStateCode(): int {
        return 5; // OutOfService is 5
    }

    public function handleLoan(InventoryItem $item): void {
        return;
    }

    public function handleAvailable(InventoryItem $item): void {
        return;
    }


    public function handleOutOfService(InventoryItem $item): void {
        $item->setState(new OutOfServiceState());
        return;
    }


    public function handleDamaged(InventoryItem $item): void {
        return;
    }

    public function handleMaintenance(InventoryItem $item): void {
        return;
    }


}