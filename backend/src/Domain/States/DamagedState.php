<?php

require_once __DIR__.'/ItemState.php';
require_once __DIR__.'/InMaintenanceState.php';

class DamagedState implements ItemState {
    public function getStateCode(): int {
        return 4; // Damaged is 4
    }

    public function handleLoan(InventoryItem $item): void {
        return;
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
        // from damaged to maintenance
        $item->setState(new InMaintenanceState());
        return;
    }


}