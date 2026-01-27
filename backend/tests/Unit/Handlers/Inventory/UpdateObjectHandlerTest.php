<?php
require_once __DIR__ . '/../../../../src/Application/Mediator/Mediator.php';
require_once __DIR__ . '/../../../../src/Application/Commands/Inventory/UpdateObjectCommand.php';
require_once __DIR__ . '/../../../../src/Application/Handlers/Inventory/UpdateObjectHandler.php';

$mediator = new Mediator();
$mediator->register('UpdateObjectCommand', 'UpdateObjectHandler');

echo "========== UNIT TEST: UPDATE OBJECT LOCATION ==========\n\n";

// ID di un oggetto fisico esistente (es. un record nella tabella inventory/items)
$targetObjectId = 1; 
$newLocation = "Ufficio Tecnico - Scaffale " . rand(1, 50);

/**
 * TEST 1: Aggiornamento Location (Successo)
 */
try {
    echo "Test 1: Aggiornamento posizione per l'oggetto ID $targetObjectId... ";
    
    // Il comando prende l'ID dell'esemplare e la nuova location
    $cmd = new UpdateObjectCommand($targetObjectId, $newLocation);
    $mediator->send($cmd);
    
    echo "✅ SUCCESSO!\n";
    echo "Nuova posizione inviata: $newLocation\n";
    echo "👉 Verifica nel DB che la colonna 'location' sia cambiata per l'ID $targetObjectId.\n";

} catch (Exception $e) {
    echo "❌ FALLITO: " . $e->getMessage() . "\n";
}

echo "------------------------------------------------------\n";

/**
 * TEST 2: ID Oggetto Inesistente
 */
try {
    echo "Test 2: Tentativo di aggiornamento su ID inesistente (99999)... ";
    
    $invalidCmd = new UpdateObjectCommand(99999, "Magazzino Centrale");
    $mediator->send($invalidCmd);
    
    echo "⚠️ NOTA: Il comando è stato inviato senza errori (Verifica se l'Handler deve controllare l'esistenza).\n";
} catch (Exception $e) {
    echo "✅ OK! Errore catturato correttamente: " . $e->getMessage() . "\n";
}

/**
 * TEST 3: Svuotamento Location
 * Verifica se è permesso impostare la location a null o stringa vuota
 */
try {
    echo "Test 3: Reset della posizione (impostazione a null)... ";
    
    $resetCmd = new UpdateObjectCommand($targetObjectId, null);
    $mediator->send($resetCmd);
    
    echo "✅ SUCCESSO! La posizione è stata resettata.\n";
} catch (Exception $e) {
    echo "❌ FALLITO: " . $e->getMessage() . "\n";
}

echo "\n================ FINE TEST ==========================\n";