<?php

require_once __DIR__."./ItemState.php/";
require_once __DIR__."./AvailableState.php/";
require_once __DIR__."./MaintenanceState.php/";
require_once __DIR__."./BrokenState.php/";

class LoanedState implements ItemState {
    public function getCode(): int {
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


    public function handleBroken(InventoryItem $item): void {
        // from Loaned to broken
        $item->setState(new BrokenState());
    }

    public function handleMaintenance(InventoryItem $item): void {
        // Change the state from Available to Maintenance -> the case in which the object is consumed
        $item->setState(new MaintenanceState());
    }


}