<?php
require_once __DIR__ . '/../../../../src/Application/Mediator/Mediator.php';
require_once __DIR__ . '/../../../../src/Application/Commands/Loans/ViewLoansCommand.php';
require_once __DIR__ . '/../../../../src/Application/Handlers/Loans/ViewLoansHandler.php';
require_once __DIR__ . '/../../../../src/Domain/Entities/Loan.php';

$mediator = new Mediator();

// Registrazione dell'Handler per i prestiti
$mediator->register('ViewLoansCommand', 'ViewLoansHandler');

echo "========== TEST VISUALIZZAZIONE PRESTITI ==========\n\n";

/**
 * CASE 1: Recupero lista completa dei prestiti
 */
try {
    echo "Test 1: Recupero di tutti i prestiti dal database... ";
    
    $command = new ViewLoansCommand();
    $loans = $mediator->send($command);
    
    if (!is_array($loans)) {
        throw new Exception("L'handler non ha restituito un array.");
    }

    echo "✅ SUCCESSO\n";
    echo "Numero di prestiti trovati: " . count($loans) . "\n";
    echo "----------------------------------------------\n";

    // Anteprima dati per verifica visiva
    foreach ($loans as $loan) {
        if ($loan instanceof Loan) {
            $status = $loan->getState() ? "Attivo" : "Chiuso";
            echo "ID: {$loan->getId()} | Cliente ID: {$loan->getClientId()} | Inizio: {$loan->getStartDate()->format('Y-m-d')} | Stato: {$status}\n";
        }
    }

} catch (Exception $e) {
    echo "❌ ERRORE: " . $e->getMessage() . "\n";
}

/**
 * CASE 2: Verifica integrità dati
 */
try {
    echo "\nTest 2: Verifica che gli oggetti restituiti siano istanze di Loan... ";
    
    if (count($loans) > 0 && !($loans[0] instanceof Loan)) {
        throw new Exception("L'array contiene dati sporchi, non sono oggetti Loan.");
    }
    
    echo "✅ SUCCESSO\n";
} catch (Exception $e) {
    echo "❌ ERRORE: " . $e->getMessage() . "\n";
}

echo "\n================ FINE TEST ===================\n";