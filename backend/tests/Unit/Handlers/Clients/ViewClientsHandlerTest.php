<?php
require_once __DIR__ . '/../../../../src/Application/Mediator/Mediator.php';
require_once __DIR__ . '/../../../../src/Application/Commands/Clients/ViewClientsCommand.php';
require_once __DIR__ . '/../../../../src/Application/Handlers/Clients/ViewClientsHandler.php';
require_once __DIR__ . '/../../../../src/Domain/Entities/Client.php';

$mediator = new Mediator();

// Registrazione
$mediator->register('ViewClientsCommand', 'ViewClientsHandler');

echo "========== TEST VISUALIZZAZIONE CLIENTI ==========\n\n";

/**
 * CASE 1: Recupero lista completa
 */
try {
    echo "Test 1: Recupero di tutti i clienti dal database... ";
    
    $command = new ViewClientsCommand();
    $clients = $mediator->send($command);
    
    if (!is_array($clients)) {
        throw new Exception("L'handler non ha restituito un array.");
    }

    echo "✅ SUCCESSO\n";
    echo "Numero di clienti trovati: " . count($clients) . "\n";
    echo "----------------------------------------------\n";

    // Mostriamo un'anteprima dei dati per verifica visiva
    foreach ($clients as $client) {
        if ($client instanceof Client) {
            echo "ID: {$client->getId()} | Nome: {$client->getFullName()} | Email: {$client->getEmail()}\n";
        }
    }

} catch (Exception $e) {
    echo "❌ ERRORE: " . $e->getMessage() . "\n";
}

/**
 * CASE 2: Verifica integrità dati (opzionale)
 */
try {
    echo "\nTest 2: Verifica che gli oggetti restituiti siano istanze di Client... ";
    
    if (count($clients) > 0 && !($clients[0] instanceof Client)) {
        throw new Exception("L'array contiene dati sporchi, non sono oggetti Client.");
    }
    
    echo "✅ SUCCESSO\n";
} catch (Exception $e) {
    echo "❌ ERRORE: " . $e->getMessage() . "\n";
}

echo "\n================ FINE TEST ===================\n";