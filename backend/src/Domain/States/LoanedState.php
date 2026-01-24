<?php

require_once __DIR__.'/ItemState.php';
require_once __DIR__.'/AvailableState.php';
require_once __DIR__.'/DamagedState.php';

class LoanedState implements ItemState {
    public function getStateCode(): int {
        return 2; // Loaned is 2
    }

    public function handleLoan(InventoryItem $item): void {
        return;
    }

    public function handleAvailable(InventoryItem $item): void {
        // from Loaned to Available
        $item->setState(new AvailableState());
    }


    public function handleOutOfService(InventoryItem $item): void {
        return;
    }


    public function handleDamaged(InventoryItem $item): void {
        // from Loaned to damaged
        $item->setState(new DamagedState());
    }

    public function handleMaintenance(InventoryItem $item): void {
        return;
    }


}