<?php

require_once __DIR__."./ItemState.php/";
require_once __DIR__."./InMaintenanceState.php/";

class BrokenState implements ItemState {
    public function getCode(): int {
        return 4; // Broken is 4
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


    public function handleBroken(InventoryItem $item): void {
        return;
    }

    public function handleMaintenance(InventoryItem $item): void {
        // from broken to maintenance
        $item->setState(new InMaintenanceState());
        return;
    }


}