<?php
require_once __DIR__ . '/../../../../src/Application/Mediator/Mediator.php';
require_once __DIR__ . '/../../../../src/Application/Commands/Inventory/ViewSingleObjectCommand.php';
require_once __DIR__ . '/../../../../src/Application/Handlers/Inventory/ViewSingleObjectHandler.php';
require_once __DIR__ . '/../../../../src/Domain/Entities/InventoryItem.php';

$mediator = new Mediator();
$mediator->register('ViewSingleObjectCommand', 'ViewSingleObjectHandler');

echo "========== TEST: VIEW SINGLE OBJECT & MAINTENANCE HISTORY ==========\n\n";

try {
    $targetId = 3; // ID dell'esemplare fisico da testare
    echo "Recupero esemplare ID {$targetId}... ";
    
    $command = new ViewSingleObjectCommand($targetId);
    $object = $mediator->send($command);
    
    if ($object instanceof InventoryItem) {
        echo "✅ TROVATO\n\n";

        // --- 1. DETTAGLI ESEMPLARE (Usando i tuoi getter) ---
        $stateStr = 'N/D';
        $stateObj = method_exists($object, 'getState') ? $object->getState() : ($object->state ?? null);
        if (is_object($stateObj) && method_exists($stateObj, 'getStateCode')) {
            $stateStr = $stateObj->getStateCode();
        }

        echo "=== DETTAGLI ESEMPLARE ===\n";
        echo "ID:        " . $object->getId() . "\n";
        echo "Codice:    " . $object->getObjectTypeCode() . "\n";
        echo "Posizione: " . $object->getLocation() . "\n";
        echo "Stato:     " . $stateStr . "\n";
        echo "--------------------------\n\n";

        // --- 2. CRONOLOGIA MANUTENZIONI (Proprietà dinamica) ---
        $maintenances = $object->maintenances ?? [];

        echo "=== STORICO MANUTENZIONI ===\n";
        if (is_array($maintenances) && count($maintenances) > 0) {
            
            echo sprintf("%-5s | %-12s | %-15s | %-20s\n", "ID", "STARTDATE", "TIPO", "TECNICO");
            echo str_repeat("-", 60) . "\n";

            foreach ($maintenances as $m) {
                // Gestione Date (presumendo DateTime)
                $date = 'N/D';
                if (method_exists($m, 'getStartDate')) {
                    $date = $m->getStartDate()->format('d/m/Y');
                }

                // Estrazione dati tramite getter della classe Maintenance
                $mId   = method_exists($m, 'getId') ? $m->getId() : 'N/D';
                $mType = method_exists($m, 'getType') ? $m->getType() : 'N/D';
                
                // Se hai il nome del tecnico caricato dinamicamente o l'ID
                $tech  = method_exists($m, 'getTechnicianId') ? "ID: " . $m->getTechnicianId() : 'N/D';
                if (isset($m->technicianName)) $tech = $m->technicianName;

                echo sprintf("%-5s | %-12s | %-15s | %-20s\n", 
                    $mId, 
                    $date, 
                    substr($mType, 0, 15), 
                    substr($tech, 0, 20)
                );

                // Mostra la descrizione se presente
                $desc = method_exists($m, 'getDescription') ? $m->getDescription() : null;
                if ($desc) {
                    echo "      > Nota: $desc\n";
                }
            }
        } else {
            echo "ℹ️ Nessuna manutenzione registrata per questo esemplare.\n";
        }

    } else {
        throw new Exception("L'handler non ha restituito un'istanza di InventoryItem.");
    }

} catch (Exception $e) {
    echo "❌ ERRORE: " . $e->getMessage() . "\n";
}

echo "\n================ FINE TEST ==========================\n";