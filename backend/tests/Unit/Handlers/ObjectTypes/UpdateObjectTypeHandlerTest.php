<?php
// 1. Inclusione delle dipendenze
require_once __DIR__ . '/../../../../src/Application/Mediator/Mediator.php';
require_once __DIR__ . '/../../../../src/Application/Commands/ObjectTypes/UpdateObjectTypeCommand.php';
require_once __DIR__ . '/../../../../src/Application/Handlers/ObjectTypes/UpdateObjectTypeHandler.php';

// 2. Inizializzazione Ambiente
$mediator = new Mediator();

// Registrazione dell'Handler
$mediator->register('UpdateObjectTypeCommand', 'UpdateObjectTypeHandler');

echo "========== UNIT TEST: UPDATE OBJECT TYPE (SOLO ANAGRAFICA) ==========\n\n";

// ID di un tipo di oggetto esistente nel tuo DB
$existingTypeId = 'MIN-1769453675'; 

/**
 * TEST 1: Aggiornamento Anagrafica (Successo)
 * Nota: La quantità NON viene passata perché non è modificabile
 */
try {
    echo "Test 1: Aggiornamento dati per ID {$existingTypeId}... ";
    
    $updateCmd = new UpdateObjectTypeCommand(
        $existingTypeId,
        "Laptop Workstation Pro", // Nome
        "Descrizione aggiornata",  // Descrizione
        "V2-2026",                // Modello
        "TechBrand"               // Brand
    );

    $mediator->send($updateCmd);
    
    echo "✅ COMANDO INVIATO\n";
    echo "Dati inviati: Nome -> Laptop Workstation Pro, Modello -> V2-2026\n";
} catch (Exception $e) {
    echo "❌ ERRORE: " . $e->getMessage() . "\n";
}

echo "---------------------------------------------------\n";

/**
 * TEST 2: Aggiornamento Parziale
 * Verifica il comportamento passando solo i dati minimi (Nome)
 */
try {
    echo "Test 2: Aggiornamento con campi opzionali null... ";
    
    $partialCmd = new UpdateObjectTypeCommand(
        $existingTypeId, 
        "Nuovo Nome Solo"
    );

    $mediator->send($partialCmd);
    
    echo "✅ COMANDO INVIATO\n";
} catch (Exception $e) {
    echo "❌ ERRORE: " . $e->getMessage() . "\n";
}

echo "\n================ FINE TEST ========================\n";