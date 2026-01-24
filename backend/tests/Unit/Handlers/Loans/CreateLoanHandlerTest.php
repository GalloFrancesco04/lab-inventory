<?php
// 1. Carica le classi (o usa il tuo autoloader se ne hai uno)
require_once __DIR__ . '/../../../../src/Application/Mediator/Mediator.php';
require_once __DIR__ . '/../../../../src/Application/Handlers/Loans/CreateLoanHandler.php';
require_once __DIR__ . '/../../../../src/Application/Commands/Loans/CreateLoanCommand.php';

require_once __DIR__ . '/../../../../src/Application/Commands/Loans/UpdateLoanCommand.php';
require_once __DIR__ . '/../../../../src/Application/Commands/Loans/CloseLoanCommand.php';
require_once __DIR__ . '/../../../../src/Application/Handlers/Loans/UpdateLoanHandler.php';
require_once __DIR__ . '/../../../../src/Application/Handlers/Loans/CloseLoanHandler.php';


// 2. Inizializza il Mediator 
// (Se il tuo Mediator crea internamente DB e Repo, basta un 'new')
$mediator = new Mediator();

$mediator->register('CreateLoanCommand', 'CreateLoanHandler');
$mediator->register('UpdateLoanCommand', 'UpdateLoanHandler');
$mediator->register('CloseLoanCommand', 'CloseLoanHandler');

echo "=== AVVIO TEST DI SISTEMA ===\n\n";

/**
 * TEST 1: Creazione Prestito (Caso Successo)
 */
try {
    echo "Esecuzione Test Loan... ";
    $loanCmd = new CreateLoanCommand("2026-01-24", "2026-02-10", 1, "test automatico");

    $result = $mediator->send($loanCmd);
    echo "✅ OK! Creato record ID: $result\n";
} catch (Exception $e) {
    echo "❌ FALLITO: " . $e->getMessage() . "\n";
}

/**
 * TEST 2: Validazione Date (Caso Errore)
 */
try {
    echo "Esecuzione Test Validazione Date... ";
    $failCmd = new CreateLoanCommand("2026-01-24", "2026-03-15", 1);

    $mediator->send($failCmd);
    echo "❌ FALLITO: Il sistema doveva bloccare l'intervallo superiore a 30gg\n";
} catch (Exception $e) {
    echo "✅ OK! Bloccato correttamente: " . $e->getMessage() . "\n";
}

/**
 * TEST 3: Aggiornamento Prestito (Update)
 */
try {
    if (!$currentId) throw new Exception("Salto test Update: nessun ID disponibile");
    
    echo "Esecuzione Test UpdateLoan... ";
    // Passiamo le nuove note e l'array di oggetti (come definito nel tuo Command)
    $updateCmd = new UpdateLoanCommand("Note aggiornate tramite test", [101, 102]);
    // Nota: se il tuo handler ha bisogno dell'ID nel command, dovresti aggiungerlo alla classe UpdateLoanCommand
    
    $mediator->send($updateCmd);
    echo "✅ OK! Prestito aggiornato correttamente.\n";
} catch (Exception $e) {
    echo "❌ FALLITO: " . $e->getMessage() . "\n";
}

/**
 * TEST 4: Chiusura Prestito (Close)
 */
try {
    if (!$currentId) throw new Exception("Salto test Close: nessun ID disponibile");

    echo "Esecuzione Test CloseLoan... ";
    $closeCmd = new CloseLoanCommand($currentId);
    
    $mediator->send($closeCmd);
    echo "✅ OK! Prestito ID $currentId chiuso correttamente.\n";
} catch (Exception $e) {
    echo "❌ FALLITO: " . $e->getMessage() . "\n";
}

echo "\n=== FINE TEST ===\n";