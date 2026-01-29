<?php
require_once __DIR__ . '/../../../../src/Application/Mediator/Mediator.php';
require_once __DIR__ . '/../../../../src/Application/Commands/Operations/UpdateOperationCommand.php';
require_once __DIR__ . '/../../../../src/Application/Handlers/Operations/UpdateOperationHandler.php';

$mediator = new Mediator();
$mediator->register('UpdateOperationCommand', 'UpdateOperationHandler');

echo "========== TEST: AGGIORNAMENTO OPERAZIONE DI MANUTENZIONE ==========\n\n";

try {
    // 1. Dati per l'aggiornamento
    $operationId = 3;                         // ID dell'operazione esistente da modificare
    $newDescription = "Sostituzione completata e collaudo finale effettuato";
    $newEndDate = new DateTime();             // Impostiamo la data di fine a "ora"
    $newCost = 55.00;                         // Aggiorniamo il costo (es. inclusi materiali extra)

    echo "Aggiornamento operazione ID $operationId in corso...\n";

    // 2. Creazione del comando
    // Il comando riceve solo i campi necessari per l'update
    $command = new UpdateOperationCommand(
        $operationId,
        $newDescription,
        $newEndDate,
        $newCost
    );

    // 3. Invio tramite il Mediator
    $mediator->send($command);

    echo "✅ SUCCESSO: Operazione aggiornata correttamente.\n";
    echo "Campi modificati:\n";
    echo " - Descrizione: $newDescription\n";
    echo " - Data Fine:   " . $newEndDate->format('d/m/Y H:i') . "\n";
    echo " - Costo:       " . number_format($newCost, 2, ',', '.') . " €\n";

} catch (Exception $e) {
    echo "❌ ERRORE: " . $e->getMessage() . "\n";
    echo "Nota: Assicurati che l'operazione ID $operationId esista nel database.\n";
}

echo "\n================ FINE TEST ==========================\n";