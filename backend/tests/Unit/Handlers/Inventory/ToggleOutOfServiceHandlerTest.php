<?php
require_once __DIR__ . '/../../../../src/Application/Mediator/Mediator.php';
require_once __DIR__ . '/../../../../src/Application/Commands/Inventory/ToggleOutOfServiceCommand.php';
require_once __DIR__ . '/../../../../src/Application/Handlers/Inventory/ToggleOutOfServiceHandler.php';

$mediator = new Mediator();
$mediator->register('ToggleOutOfServiceCommand', 'ToggleOutOfServiceHandler');

echo "========== TEST: TOGGLE OUT OF SERVICE (CAMBIO STATO DISPONIBILITÀ) ==========\n\n";

try {
    $objectId = 1; // ID dell'esemplare fisico da testare
    echo "Esecuzione Toggle OutOfService per l'oggetto ID: $objectId...\n";

    // 1. Creazione del comando (passando solo l'ID)
    $command = new ToggleOutOfServiceCommand($objectId);

    // 2. Invio del comando
    // Se l'handler restituisce il nuovo stato (es. l'oggetto ItemState o il codice), lo visualizziamo
    $result = $mediator->send($command);

    echo "✅ SUCCESSO: Stato invertito correttamente.\n";

    // 3. Verifica feedback (se l'handler restituisce qualcosa)
    if ($result !== null) {
        $stateCode = is_object($result) && method_exists($result, 'getStateCode') 
            ? $result->getStateCode() 
            : (string)$result;
        
        echo "Nuovo stato dell'oggetto: **$stateCode**\n";
    } else {
        echo "L'operazione è stata completata (nessun dato restituito).\n";
    }

} catch (Exception $e) {
    echo "❌ ERRORE: " . $e->getMessage() . "\n";
    echo "Possibili cause: l'oggetto è attualmente in prestito o in manutenzione.\n";
}

echo "\n================ FINE TEST ==========================\n";