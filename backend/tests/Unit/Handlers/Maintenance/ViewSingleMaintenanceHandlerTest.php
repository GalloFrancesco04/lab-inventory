<?php
require_once __DIR__ . '/../../../../src/Application/Mediator/Mediator.php';
require_once __DIR__ . '/../../../../src/Application/Commands/Maintenance/ViewSingleMaintenanceCommand.php';
require_once __DIR__ . '/../../../../src/Application/Handlers/Maintenance/ViewSingleMaintenanceHandler.php';

$mediator = new Mediator();
$mediator->register('ViewSingleMaintenanceCommand', 'ViewSingleMaintenanceHandler');

echo "========== TEST: DETTAGLIO MANUTENZIONE E OPERAZIONI ==========\n\n";

try {
    $targetId = 1; // Assicurati che questo ID esista nel DB
    echo "Recupero manutenzione ID {$targetId}... ";
    
    $command = new ViewSingleMaintenanceCommand($targetId);
    $m = $mediator->send($command);
    
    if (is_object($m)) {
        echo "✅ TROVATA\n\n";

        // --- 1. DATI TESTATA MANUTENZIONE ---
        echo "=== INFO GENERALI ===\n";
        echo "ID Manutenzione: " . (method_exists($m, 'getId') ? $m->getId() : 'N/D') . "\n";
        echo "Oggetto:         " . ($m->objectName ?? 'N/D') . "\n";
        echo "Tecnico:         " . ($m->technician ?? 'N/D') . "\n";
        echo "Tipo:            " . (method_exists($m, 'getType') ? $m->getType() : 'N/D') . "\n";
        echo "--------------------------------------------------\n\n";

        // --- 2. ELENCO OPERAZIONI (Proprietà dinamica 'operations') ---
        $operations = $m->operations ?? [];

        echo "=== OPERAZIONI EFFETTUATE ===\n";
        if (is_array($operations) && count($operations) > 0) {
            
            echo sprintf("%-4s | %-12s | %-12s | %-10s | %-20s\n", "ID", "INIZIO", "FINE", "COSTO", "DESCRIZIONE");
            echo str_repeat("-", 70) . "\n";

            $totalCost = 0;

            foreach ($operations as $op) {
                // Usiamo i Getter della classe Operation
                $id    = $op->getId() ?? 'N/D';
                $start = $op->getStartDate()->format('d/m/Y');
                $end   = $op->getEndDate() ? $op->getEndDate()->format('d/m/Y') : 'In corso';
                $cost  = number_format($op->getCost(), 2, ',', '.') . " €";
                $desc  = $op->getDescription();

                $totalCost += $op->getCost();

                echo sprintf("%-4s | %-12s | %-12s | %-10s | %-20s\n", 
                    $id, 
                    $start, 
                    $end, 
                    $cost, 
                    substr($desc, 0, 20)
                );
            }
            
            echo str_repeat("-", 70) . "\n";
            echo "TOTALE COSTI MANUTENZIONE: " . number_format($totalCost, 2, ',', '.') . " €\n";

        } else {
            echo "⚠️ Nessuna operazione specifica registrata per questa manutenzione.\n";
        }

    } else {
        throw new Exception("Manutenzione non trovata.");
    }

} catch (Exception $e) {
    echo "❌ ERRORE: " . $e->getMessage() . "\n";
}

echo "\n================ FINE TEST ==========================\n";