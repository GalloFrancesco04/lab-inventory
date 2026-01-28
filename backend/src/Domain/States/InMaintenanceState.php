<?php

require_once __DIR__.'/ItemState.php';
require_once __DIR__.'/OutOfServiceState.php';
require_once __DIR__.'/AvailableState.php';

class InMaintenanceState implements ItemState {
    public function getStateCode(): int {
        return 3; // InMaintenance is 3
    }

    public function handleLoan(InventoryItem $item): void {
        return;
    }

    public function handleAvailable(InventoryItem $item): void {
        // from maintenance to available
        $item->setState(new AvailableState());
    }


    public function handleOutOfService(InventoryItem $item): void {
        // from maintenance to out of service
        $item->setState(new OutOfServiceState());
    }


    public function handleDamaged(InventoryItem $item): void {
        return;
    }

    public function handleMaintenance(InventoryItem $item): void {
        throw new Exception("Oggetto gia' in Manutenzione");
        return;
    }


}