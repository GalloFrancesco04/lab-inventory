<?php
// 1. Inclusione delle dipendenze necessarie
require_once __DIR__ . '/../../../../src/Application/Mediator/Mediator.php';

// Comandi
require_once __DIR__ . '/../../../../src/Application/Commands/Clients/BlockClientCommand.php';
require_once __DIR__ . '/../../../../src/Application/Commands/Clients/UnblockClientCommand.php';

// Handler
require_once __DIR__ . '/../../../../src/Application/Handlers/Clients/BlockClientHandler.php';
require_once __DIR__ . '/../../../../src/Application/Handlers/Clients/UnblockClientHandler.php';

// 2. Inizializzazione Ambiente
$mediator = new Mediator();

// Registrazione degli Handler specifici per i Clienti
$mediator->register('BlockClientCommand', 'BlockClientHandler');
$mediator->register('UnblockClientCommand', 'UnblockClientHandler');

echo "========== UNIT TEST: CLIENT STATUS ==========\n";

// ID di un cliente presente nel tuo database per il test
$targetClientId = 1; 

/**
 * TEST 1: Blocco del Cliente
 */
try {
    echo "Test 1: Esecuzione BlockClientCommand per ID {$targetClientId}... ";
    
    $blockCmd = new BlockClientCommand($targetClientId);
    $mediator->send($blockCmd);
    
    echo "✅ SUCCESSO\n";
} catch (Exception $e) {
    echo "❌ ERRORE: " . $e->getMessage() . "\n";
}

echo "----------------------------------------------\n";

/**
 * TEST 2: Sblocco del Cliente
 */
try {
    echo "Test 2: Esecuzione UnblockClientCommand per ID {$targetClientId}... ";
    
    $unblockCmd = new UnblockClientCommand($targetClientId);
    $mediator->send($unblockCmd);
    
    echo "✅ SUCCESSO\n";
} catch (Exception $e) {
    echo "❌ ERRORE: " . $e->getMessage() . "\n";
}

/**
 * TEST 3: Caso di Errore (ID inesistente)
 * Utile per verificare come il tuo Handler gestisce i dati mancanti
 */
try {
    echo "Test 3: Tentativo di blocco su ID inesistente (99999)... ";
    
    $invalidCmd = new BlockClientCommand(99999);
    $mediator->send($invalidCmd);
    
    echo "⚠️ NOTA: Il comando è passato (verifica se l'handler dovrebbe lanciare errore se l'ID non esiste).\n";
} catch (Exception $e) {
    echo "✅ ERRORE CATTURATO (Corretto): " . $e->getMessage() . "\n";
}

echo "================ FINE TEST ===================\n";