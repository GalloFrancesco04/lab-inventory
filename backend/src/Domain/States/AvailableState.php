<?php

require_once __DIR__."./ItemState.php/";
require_once __DIR__."./LoanedState.php/";
require_once __DIR__."./InMaintenanceState.php/";

class AvailableState implements ItemState {
    public function getCode(): int {
        return 1; // Available is 1
    }

    public function handleLoan(InventoryItem $item): void {
        // Change the state from Available (1) to Loaned (2)
        $item->setState(new LoanedState());
    }

    public function handleAvailable(InventoryItem $item): void {
        return;
    }


    public function handleOutOfService(InventoryItem $item): void {
        return;
    }


    public function handleDamaged(InventoryItem $item): void {
        return;
    }

    public function handleMaintenance(InventoryItem $item): void {
        // Change the state from Available to Maintenance -> the case in which the object is consumed
        $item->setState(new InMaintenanceState());
    }


}