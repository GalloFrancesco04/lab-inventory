<?php

require_once __DIR__ . '/../../../../src/Application/Mediator/Mediator.php';
require_once __DIR__ . '/../../../../src/Application/Commands/Clients/UpdateClientCommand.php';
require_once __DIR__ . '/../../../../src/Application/Handlers/Clients/UpdateClientHandler.php';

$mediator = new Mediator();

$mediator->register('UpdateClientCommand', 'UpdateClientHandler');

echo "\n========== TEST AGGIORNAMENTO CLIENTE ==========\n";

$idToUpdate = 1; 

try {
    echo "Test 1: Aggiornamento nome e cognome per ID $idToUpdate... ";
    $updateCmd = new UpdateClientCommand($idToUpdate, "Marco Updated", "Neri Rossi");
    $mediator->send($updateCmd);
    echo "✅ SUCCESSO\n";
} catch (Exception $e) {
    echo "❌ ERRORE: " . $e->getMessage() . "\n";
}

/**
 * CASE 2: Tentativo di svuotare i campi
 */
try {
    echo "Test 2: Tentativo di aggiornamento con campi vuoti... ";
    $updateCmd = new UpdateClientCommand($idToUpdate, "", "");
    $mediator->send($updateCmd);
    echo "⚠️ NOTA: Il sistema ha accettato nomi vuoti.\n";
} catch (Exception $e) {
    echo "✅ CATTURATO (Validazione fallita correttamente): " . $e->getMessage() . "\n";
}

/**
 * CASE 3: ID inesistente
 */
try {
    echo "Test 3: Tentativo di aggiornamento su ID inesistente (9999)... ";
    $updateCmd = new UpdateClientCommand(9999, "Invisibile", "User");
    $mediator->send($updateCmd);
    echo "⚠️ NOTA: Il sistema non ha rilevato l'ID mancante.\n";
} catch (Exception $e) {
    echo "✅ CATTURATO (Cliente non trovato): " . $e->getMessage() . "\n";
}

echo "\n================ FINE TEST ===================\n";
