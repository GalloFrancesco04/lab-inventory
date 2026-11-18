<?php
require_once __DIR__ . '/../../../../src/Application/Mediator/Mediator.php';
require_once __DIR__ . '/../../../../src/Application/Commands/Inventory/CreateObjectCommand.php';
require_once __DIR__ . '/../../../../src/Application/Handlers/Inventory/CreateObjectHandler.php';

$mediator = new Mediator();
$mediator->register('CreateObjectCommand', 'CreateObjectHandler');

echo "========== UNIT TEST: CREATE PHYSICAL OBJECT ==========\n\n";

/**
 * TEST 1: Creazione Oggetto Fisico (Caso Successo)
 * Assicurati che 'LAPTOP-PRO' (o un altro codice) esista nella tabella object_types
 */
try {
    $typeCode = "NB-DELL-XPS15"; // Sostituisci con un codice esistente nel tuo DB
    $location = "Ufficio Milano - Piano 1";

    echo "Test 1: Creazione esemplare per tipo '$typeCode'... ";
    
    $cmd = new CreateObjectCommand($typeCode, $location);
    $newObjectId = $mediator->send($cmd);
    
    echo "✅ SUCCESSO! Creato Oggetto ID: $newObjectId\n";
    echo "Posizione assegnata: $location\n";

} catch (Exception $e) {
    echo "❌ FALLITO: " . $e->getMessage() . "\n";
}

echo "------------------------------------------------------\n";

/**
 * TEST 2: Creazione con Tipo Oggetto Inesistente
 * Verifica se l'Handler controlla che il codice del tipo esista davvero
 */
try {
    echo "Test 2: Tentativo con codice tipo inesistente ('FAKE-TYPE')... ";
    
    $invalidCmd = new CreateObjectCommand("FAKE-TYPE", "Magazzino");
    $mediator->send($invalidCmd);
    
    echo "❌ FALLITO: Il sistema ha creato un oggetto per un tipo che non esiste!\n";
} catch (Exception $e) {
    echo "✅ OK! Errore catturato correttamente: " . $e->getMessage() . "\n";
}

/**
 * TEST 3: Creazione senza Location (Opzionale)
 */
try {
    echo "Test 3: Creazione senza specificare la location... ";
    
    $noLocationCmd = new CreateObjectCommand("NB-DELL-XPS15", null);
    $id = $mediator->send($noLocationCmd);
    
    echo "✅ SUCCESSO! ID: $id (Location lasciata null/default).\n";
} catch (Exception $e) {
    echo "❌ FALLITO: " . $e->getMessage() . "\n";
}

echo "\n================ FINE TEST ==========================\n";