<?php
require_once __DIR__ . '/../../../../src/Application/Mediator/Mediator.php';
require_once __DIR__ . '/../../../../src/Application/Commands/Maintenance/CloseMaintenanceCommand.php';
require_once __DIR__ . '/../../../../src/Application/Handlers/Maintenance/CloseMaintenanceHandler.php';

$mediator = new Mediator();
$mediator->register('CloseMaintenanceCommand', 'CloseMaintenanceHandler');

echo "========== TEST: CHIUSURA MANUTENZIONE (VOID RETURN) ==========\n\n";

try {
    $maintenanceId = 1; 
    $finalNotes = "Intervento concluso. Apparato testato e funzionante.";
    $closureDate = new DateTime();

    echo "Tentativo di chiusura manutenzione ID: $maintenanceId...\n";

    // Creazione del comando
    $command = new CloseMaintenanceCommand($maintenanceId, $finalNotes, $closureDate);

    // Esecuzione del comando
    // Non assegniamo il risultato a una variabile perché l'handler non restituisce nulla
    $mediator->send($command);

    // Se arriviamo qui senza eccezioni, l'operazione è andata a buon fine
    echo "✅ SUCCESSO: La manutenzione è stata chiusa.\n";
    echo "L'oggetto associato dovrebbe ora essere nello stato 'Disponibile' (1).\n";

} catch (Exception $e) {
    // Se qualcosa va storto (es. ID inesistente o errore DB), l'handler lancerà un'eccezione
    echo "❌ ERRORE: " . $e->getMessage() . "\n";
}

echo "\n================ FINE TEST ==========================\n";