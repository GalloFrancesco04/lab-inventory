<?php
require_once __DIR__ . '/../../../../src/Application/Mediator/Mediator.php';
require_once __DIR__ . '/../../../../src/Application/Handlers/Loans/UpdateLoanHandler.php';
require_once __DIR__ . '/../../../../src/Application/Commands/Loans/UpdateLoanCommand.php';
require_once __DIR__ . '/../../../../src/Domain/Entities/InventoryItem.php';

$mediator = new Mediator();
$mediator->register('UpdateLoanCommand', 'UpdateLoanHandler');

echo "=== AVVIO TEST AGGIORNAMENTO PRESTITO (STRUTTURA SNELLA) ===\n\n";

/**
 * TEST 1: Aggiornamento Note (Happy Path)
 */
try {
    echo "Test 1: Aggiornamento delle sole note... ";
    $items = [];
    $updateNotesCmd = new UpdateLoanCommand(1, "Note aggiornate il 2026-01-26", ...$items);
    
    $mediator->send($updateNotesCmd);
    echo "✅ OK!\n";
} catch (Exception $e) {
    echo "❌ FALLITO: " . $e->getMessage() . "\n";
}

/**
 * TEST 2: Aggiornamento con Oggetti Esistenti
 */
try {
    echo "Test 2: Aggiornamento oggetti inclusi nel prestito... ";
    $item1 = new InventoryItem(1, 'LAPTOP', 3, 'Ufficio A');
    $item2 = new InventoryItem(2, 'SERVER', 3, 'Ufficio A');
    
    $items = [$item1, $item2];

    $updateItemsCmd = new UpdateLoanCommand(1, "Aggiunti oggetti", ...$items);
    
    $mediator->send($updateItemsCmd);
    echo "✅ OK!\n";
} catch (Exception $e) {
    echo "❌ FALLITO: " . $e->getMessage() . "\n";
}

/**
 * TEST 3: Oggetto Inesistente nel DB
 */
try {
    echo "Test 3: Tentativo inserimento oggetto non a catalogo (ID 9999)... ";
    $ghostItem = new InventoryItem(9999, 'FAKE', 1, 'Nowhere');
    
    $ghostCmd = new UpdateLoanCommand(1, "Test inesistenza", $ghostItem);
    
    $mediator->send($ghostCmd);
    echo "❌ FALLITO: L'handler non ha validato l'esistenza dell'oggetto nel DB\n";
} catch (Exception $e) {
    echo "✅ OK! Bloccato: " . $e->getMessage() . "\n";
}

/**
 * TEST 4: Stato DAMAGED (Transizione Ammessa)
 */
try {
    echo "Test 4: Segnalazione danno su un oggetto esistente... ";
    // Supponiamo che l'ID 1 sia già nel prestito, ora lo passiamo come Damaged (Stato 4)
    $damagedItem = new InventoryItem(1, 'LAPTOP', 4, 'Service Desk');
    
    $damageCmd = new UpdateLoanCommand(1, "Rilevato guasto durante l'uso", $damagedItem);
    
    $mediator->send($damageCmd);
    echo "✅ OK! Stato danneggiato accettato.\n";
} catch (Exception $e) {
    echo "❌ FALLITO: " . $e->getMessage() . "\n";
}

/**
 * TEST 5: Downgrade dello Stato (Vietato: da Damaged a Available)
 */
try {
    echo "Test 5: Tentativo di 'riparazione' abusiva (da Damaged a Available)... ";
    // Proviamo a riportare a 1 un oggetto che l'handler sa essere già a 4 nel DB
    $itemToRestore = new InventoryItem(1, 'LAPTOP', 1, 'Ufficio');
    
    $restoreCmd = new UpdateLoanCommand(1, "Tentativo ripristino", $itemToRestore);
    
    $mediator->send($restoreCmd);
    echo "❌ FALLITO: L'handler ha permesso di resettare il danno senza processo di manutenzione\n";
} catch (Exception $e) {
    echo "✅ OK! Bloccato correttamente: " . $e->getMessage() . "\n";
}

/**
 * TEST 6: Atomicità (Rollback su errore oggetti)
 */
try {
    echo "Test 6: Verifica transazione su lista mista... ";
    $valid = new InventoryItem(2, 'MONITOR', 1, 'Sede');
    $invalid = new InventoryItem(-1, 'ERROR', 1, 'X');
    
    $atomicCmd = new UpdateLoanCommand(1, "Test atomicità", $valid, $invalid);
    
    $mediator->send($atomicCmd);
    echo "❌ FALLITO: Transazione non gestita correttamente\n";
} catch (Exception $e) {
    echo "✅ OK! Rollback eseguito correttamente: " . $e->getMessage() . "\n";
}

echo "\n=== FINE TEST AGGIORNAMENTO ===\n";