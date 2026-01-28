<?php
// 1. Inclusione delle dipendenze (Assicurati che i percorsi siano corretti)
require_once __DIR__ . '/../../../../src/Application/Mediator/Mediator.php';
require_once __DIR__ . '/../../../../src/Application/Commands/ObjectTypes/CreateObjectTypeCommand.php';
require_once __DIR__ . '/../../../../src/Application/Handlers/ObjectTypes/CreateObjectTypeHandler.php';

// 2. Inizializzazione Ambiente
$mediator = new Mediator();

// Registrazione dell'Handler
$mediator->register('CreateObjectTypeCommand', 'CreateObjectTypeHandler');

echo "========== UNIT TEST: CREATE OBJECT TYPE ==========\n\n";

// Generiamo un codice univoco per il test per evitare conflitti con test precedenti
$uniqueCode = "TEST-" . time(); 

/**
 * TEST 1: Creazione Nuovo Object Type (Successo)
 */
try {
    echo "Test 1: Creazione nuovo tipo oggetto (Codice: $uniqueCode)... ";
    
    $cmd = new CreateObjectTypeCommand(
        $uniqueCode, 
        "Laptop Pro X", 
        "Descrizione del laptop di test", 
        "X-2026", 
        "TechBrand", 
        5
    );

    $result = $mediator->send($cmd);
    echo "✅ SUCCESSO! ID Creato: $result\n";
} catch (Exception $e) {
    echo "❌ FALLITO: " . $e->getMessage() . "\n";
}

echo "---------------------------------------------------\n";

/**
 * TEST 2: Tentativo di Duplicazione (Caso Errore)
 * Proviamo a reinserire lo stesso codice univoco usato sopra.
 */
try {
    echo "Test 2: Tentativo di inserimento codice duplicato ($uniqueCode)... ";
    
    // Ripetiamo lo stesso comando
    $duplicateCmd = new CreateObjectTypeCommand(
        $uniqueCode, 
        "Altro Nome", 
        "Non dovrebbe funzionare"
    );

    $mediator->send($duplicateCmd);
    
    echo "❌ FALLITO: Il sistema ha accettato un codice duplicato!\n";
} catch (Exception $e) {
    // Se l'Handler lancia un'eccezione (es: "Object Type code already exists"), il test passa.
    echo "✅ OK! Errore catturato (Corretto): " . $e->getMessage() . "\n";
}

echo "---------------------------------------------------\n";

/**
 * TEST 3: Dati Opzionali
 * Verifica se il costruttore gestisce correttamente i parametri null/default
 */
try {
    echo "Test 3: Creazione con parametri opzionali omessi... ";
    
    $minimalCode = "MIN-" . time();
    $minimalCmd = new CreateObjectTypeCommand($minimalCode, "Oggetto Minimo");

    $mediator->send($minimalCmd);
    echo "✅ SUCCESSO! Gestione parametri opzionali corretta.\n";
} catch (Exception $e) {
    echo "❌ FALLITO: " . $e->getMessage() . "\n";
}

echo "\n================ FINE TEST ========================\n";