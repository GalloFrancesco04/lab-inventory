<?php
require_once __DIR__ . '/../../../../src/Application/Mediator/Mediator.php';
require_once __DIR__ . '/../../../../src/Application/Commands/Inventory/SendToMaintenanceCommand.php';
require_once __DIR__ . '/../../../../src/Application/Handlers/Inventory/SendToMaintenanceHandler.php';

// Includiamo le entità per verificare i risultati se necessario
require_once __DIR__ . '/../../../../src/Domain/Entities/InventoryItem.php';

$mediator = new Mediator();
$mediator->register('SendToMaintenanceCommand', 'SendToMaintenanceHandler');

echo "========== TEST: INVIO IN MANUTENZIONE ========== \n\n";

try {
    // 1. Prepariamo i dati del comando
    $objectId = 1;              // ID dell'esemplare da riparare
    $description = "Sostituzione batteria e pulizia sensori";
    $technicianId = 2;          // ID del tecnico assegnato
    $type = "Riparazione";       // Tipo di intervento

    echo "Invio oggetto ID $objectId in manutenzione...\n";
    
    $command = new SendToMaintenanceCommand(
        $objectId, 
        $description, 
        $technicianId, 
        $type
    );

    // 2. Invio del comando tramite il Mediator
    $result = $mediator->send($command);

    // 3. Verifica del risultato
    if ($result) {
        echo "✅ SUCCESSO: Oggetto inviato correttamente.\n";
        
        // Se l'handler restituisce l'oggetto aggiornato o la manutenzione creata
        if (is_object($result)) {
            echo "Dettagli operazione:\n";
            
            // Verifichiamo lo stato dell'oggetto se presente nel risultato
            if (method_exists($result, 'getState')) {
                $state = $result->getState();
                $stateCode = is_object($state) ? $state->getStateCode() : $state;
                echo "- Nuovo Stato Oggetto: $stateCode (ID: 3)\n";
            }
            
            // Verifichiamo se ci restituisce l'ID della manutenzione creata
            $maintenanceId = $result->maintenance_id ?? 'N/D';
            echo "- ID Record Manutenzione: $maintenanceId\n";
        }
    } else {
        throw new Exception("L'handler ha restituito un esito negativo.");
    }

} catch (Exception $e) {
    echo "❌ FALLITO: " . $e->getMessage() . "\n";
    echo "Controlla che:\n";
    echo " - L'oggetto ID $objectId non sia già in manutenzione o prestato.\n";
    echo " - Il tecnico ID $technicianId esista nel database.\n";
}

echo "\n================ FINE TEST ==========================\n";