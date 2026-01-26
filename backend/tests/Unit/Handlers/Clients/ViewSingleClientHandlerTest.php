<?php
require_once __DIR__ . '/../../../../src/Application/Mediator/Mediator.php';
require_once __DIR__ . '/../../../../src/Application/Commands/Clients/ViewSingleClientCommand.php';
require_once __DIR__ . '/../../../../src/Application/Handlers/Clients/ViewSingleClientHandler.php';
require_once __DIR__ . '/../../../../src/Domain/Entities/Client.php';

$mediator = new Mediator();

// Registrazione
$mediator->register('ViewSingleClientCommand', 'ViewSingleClientHandler');

echo "========== TEST VISUALIZZAZIONE SINGOLO CLIENTE ==========\n\n";

/**
 * CASE 1: Recupero cliente esistente (Happy Path)
 */
try {
    $targetId = 1; // Assicurati che questo ID esista nel tuo DB
    echo "Test 1: Recupero cliente con ID {$targetId}... ";
    
    $command = new ViewSingleClientCommand($targetId);
    $client = $mediator->send($command);
    
    if ($client instanceof Client) {
        echo "✅ SUCCESSO\n";
        echo "Dettagli Trovati:\n";
        echo "- Nome Completo: " . $client->getFullName() . "\n";
        echo "- Email: " . $client->getEmail() . "\n";
        echo "- Stato: " . $client->getState() . "\n";
    } else {
        throw new Exception("Il risultato non è un'istanza di Client.");
    }

} catch (Exception $e) {
    echo "❌ ERRORE: " . $e->getMessage() . "\n";
}

echo "----------------------------------------------------------\n";

/**
 * CASE 2: Recupero ID inesistente (Edge Case)
 */
try {
    $invalidId = 999999;
    echo "Test 2: Recupero cliente con ID inesistente ({$invalidId})... ";
    
    $command = new ViewSingleClientCommand($invalidId);
    $mediator->send($command);
    
    echo "⚠️ NOTA: Il sistema non ha segnalato l'assenza del cliente.\n";
} catch (Exception $e) {
    // Qui verifichiamo che il messaggio sia quello che abbiamo lanciato nell'handler
    echo "✅ CATTURATO (Corretto): " . $e->getMessage() . "\n";
}

echo "\n================ FINE TEST ===================\n";