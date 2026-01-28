<?php
require_once __DIR__ . '/../../../../src/Application/Mediator/Mediator.php';
require_once __DIR__ . '/../../../../src/Application/Commands/Maintenance/CloseMaintenanceCommand.php';
require_once __DIR__ . '/../../../../src/Application/Handlers/Maintenance/CloseMaintenanceHandler.php';

$mediator = new Mediator();
$mediator->register('CloseMaintenanceCommand', 'CloseMaintenanceHandler');

echo "========== TEST: CHIUSURA MANUTENZIONE CON VARIANTI STATO ==========\n\n";

/**
 * Funzione helper per eseguire il test di chiusura
 */
$runCloseTest = function($mId, $notes, $isDamaged, $mediator) {
    $statusText = $isDamaged ? "DANNEGGIATO (1)" : "RIPRISTINATO (0)";
    echo "--- Test Chiusura ID $mId | Flag Damaged: $statusText ---\n";
    
    try {
        $closureDate = new DateTime();
        
        $command = new CloseMaintenanceCommand($mId, $isDamaged);
        
        $mediator->send($command);
        
        echo "✅ SUCCESSO: Manutenzione $mId chiusa.\n";
        echo "Esito previsto: L'oggetto dovrebbe essere " . ($isDamaged ? "nello stato DANNEGGIATO." : "nello stato DISPONIBILE (1).") . "\n\n";
        
    } catch (Exception $e) {
        echo "❌ FALLITO: " . $e->getMessage() . "\n\n";
    }
};

// --- CASO 1: CHIUSURA STANDARD (Oggetto riparato correttamente) ---
// Utilizziamo il flag 0 (o lo omettiamo se il tuo costruttore ha il default)
$runCloseTest(1, "Riparazione perfetta, test superato.", 0, $mediator);

// --- CASO 2: CHIUSURA CON OGGETTO DANNEGGIATO (Irreparabile o scartato) ---
// Passiamo 1 per indicare che l'oggetto non è più utilizzabile
$runCloseTest(1, "Componenti interni fusi, oggetto non riparabile.", 1, $mediator);

echo "================ FINE TEST ==========================\n";