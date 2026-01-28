<?php
require_once __DIR__ . '/../../../../src/Application/Mediator/Mediator.php';
require_once __DIR__ . '/../../../../src/Application/Commands/Maintenance/ViewMaintenancesCommand.php';
require_once __DIR__ . '/../../../../src/Application/Handlers/Maintenance/ViewMaintenancesHandler.php';

$mediator = new Mediator();
$mediator->register('ViewMaintenancesCommand', 'ViewMaintenancesHandler');

echo "========== TEST: VISUALIZZAZIONE STORICO MANUTENZIONI COMPLETO ==========\n\n";

try {
    echo "Recupero elenco manutenzioni in corso... ";
    
    $command = new ViewMaintenancesCommand();
    $maintenances = $mediator->send($command);
    
    if (is_array($maintenances)) {
        echo "✅ SUCCESSO\n";
        echo "Totale record trovati: " . count($maintenances) . "\n";
        echo str_repeat("=", 90) . "\n";

        // Header della tabella
        echo sprintf(
            "%-4s | %-12s | %-15s | %-25s | %-25s\n", 
            "ID", "STARTDATE", "TIPO", "OGGETTO (Nome)", "TECNICO (Email)"
        );
        echo str_repeat("-", 90) . "\n";

        foreach ($maintenances as $m) {
            // 1. Estrazione dati base Manutenzione
            $mId   = method_exists($m, 'getId') ? $m->getId() : 'N/D';
            $mType = method_exists($m, 'getType') ? $m->getType() : 'N/D';
            
            // Gestione Data (DateTime)
            $date = 'N/D';
            if (method_exists($m, 'getStartDate')) {
                $dateObj = $m->getStartDate();
                $date = ($dateObj instanceof DateTime) ? $dateObj->format('d/m/Y') : $dateObj;
            }

            // 2. Estrazione dati dalle proprietà dinamiche richieste
            // Email del tecnico
            $techEmail = $m->technician ?? 'N/D'; 
            
            // Nome dell'oggetto
            $objName = $m->objectName ?? 'N/D';

            // 3. Stampa della riga
            echo sprintf(
                "%-4s | %-12s | %-15s | %-25s | %-25s\n",
                $mId,
                $date,
                substr($mType, 0, 15),
                substr($objName, 0, 25),
                substr($techEmail, 0, 25)
            );

            // Se c'è una descrizione, la stampiamo sotto in riga dedicata
            $desc = method_exists($m, 'getDescription') ? $m->getDescription() : null;
            if ($desc) {
                echo "     > Nota: $desc\n";
            }
            echo str_repeat("-", 90) . "\n";
        }
    } else {
        throw new Exception("L'handler non ha restituito un array di manutenzioni.");
    }

} catch (Exception $e) {
    echo "❌ ERRORE: " . $e->getMessage() . "\n";
}

echo "\n================ FINE TEST ==========================\n";