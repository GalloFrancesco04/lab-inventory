<?php
require_once __DIR__ . '/../../../../src/Application/Mediator/Mediator.php';
require_once __DIR__ . '/../../../../src/Application/Commands/Inventory/ViewObjectsCommand.php';
require_once __DIR__ . '/../../../../src/Application/Handlers/Inventory/ViewObjectsHandler.php';

$mediator = new Mediator();
$mediator->register('ViewObjectsCommand', 'ViewObjectsHandler');

echo "========== UNIT TEST: VIEW DETAILED PHYSICAL OBJECTS ==========\n\n";

try {
    $result = $mediator->send(new ViewObjectsCommand());

    if (is_array($result)) {
        echo "✅ Lista esemplari completa recuperata.\n\n";

        // Helper per lo stato (ItemState -> getStateCode)
        $extractState = function($obj) {
            $stateObj = method_exists($obj, 'getState') ? $obj->getState() : ($obj->state ?? null);
            if (is_object($stateObj) && method_exists($stateObj, 'getStateCode')) {
                return $stateObj->getStateCode();
            }
            return 'N/D';
        };

        // Header Tabella (Allargato per ospitare tutti i campi)
        echo sprintf(
            "%-4s | %-10s | %-15s | %-15s | %-15s | %-10s | %-10s | %-8s | %-5s\n", 
            "ID", "CODE", "NAME", "BRAND", "MODEL", "STATE", "LOC", "QTY_TOT", "DESC"
        );
        echo str_repeat("-", 120) . "\n";

        foreach ($result as $obj) {
            // Proprietà dinamica ObjectType
            $type = $obj->objectType ?? null;

            // --- Estrazione Campi ObjectType tramite Getter ---
            $tCode  = (is_object($type) && method_exists($type, 'getCode')) ? $type->getCode() : 'N/D';
            $tName  = (is_object($type) && method_exists($type, 'getName')) ? $type->getName() : 'N/D';
            $tBrand = (is_object($type) && method_exists($type, 'getBrand')) ? ($type->getBrand() ?? '-') : '-';
            $tModel = (is_object($type) && method_exists($type, 'getModel')) ? ($type->getModel() ?? '-') : '-';
            $tQty   = (is_object($type) && method_exists($type, 'getQuantity')) ? $type->getQuantity() : '0';
            $tDesc  = (is_object($type) && method_exists($type, 'getDescription')) ? ($type->getDescription() ?? '') : '';

            // --- Estrazione Campi PhysicalObject ---
            $id       = method_exists($obj, 'getId') ? $obj->getId() : 'N/D';
            $location = method_exists($obj, 'getLocation') ? $obj->getLocation() : 'N/D';
            $state    = $extractState($obj);

            echo sprintf(
                "%-4s | %-10s | %-15s | %-15s | %-15s | %-10s | %-10s | %-8s | %-5s\n",
                $id,
                $tCode,
                $tName,
                $tBrand,
                $tModel,
                $state,
                $location,
                $tQty,
                $tDesc// Indichiamo solo se la descrizione esiste per brevità
            );
        }
    }
} catch (Exception $e) {
    echo "❌ ERRORE: " . $e->getMessage() . "\n";
}

echo "\n================ FINE TEST ==========================\n";