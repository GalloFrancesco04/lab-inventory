<?php
require_once __DIR__ . '/../../../../src/Application/Mediator/Mediator.php';
require_once __DIR__ . '/../../../../src/Application/Commands/Loans/ViewLoansCommand.php';
require_once __DIR__ . '/../../../../src/Application/Handlers/Loans/ViewLoansHandler.php';
require_once __DIR__ . '/../../../../src/Domain/Entities/Loan.php';

$mediator = new Mediator();
$mediator->register('ViewLoansCommand', 'ViewLoansHandler');

echo "========== TEST VISUALIZZAZIONE LISTA PRESTITI COMPLETA ==========\n\n";

try {
    echo "Recupero di tutti i prestiti in corso... ";
    
    $command = new ViewLoansCommand();
    $loans = $mediator->send($command);
    
    if (!is_array($loans)) {
        throw new Exception("L'handler non ha restituito un array.");
    }

    echo "✅ SUCCESSO\n";
    echo "Numero di prestiti trovati: " . count($loans) . "\n";
    echo str_repeat("=", 60) . "\n\n";

    foreach ($loans as $loan) {
        if ($loan instanceof Loan) {
            // --- 1. TESTATA PRESTITO ---
            $status = $loan->getState() ? "🟢 ATTIVO" : "🔴 CHIUSO";
            echo "PRESTITO ID: {$loan->getId()} | Stato: $status | Quantita': {$loan->getQuantity()}\n ";
            echo "Periodo:     " . $loan->getStartDate()->format('d/m/Y') . " -> " . $loan->getEndDate()->format('d/m/Y') . "\n";
            
            // --- 2. DETTAGLI CLIENTE (Proprietà aggiunta) ---
            $client = $loan->client ?? null;
            if ($client) {
                $cName = method_exists($client, 'getFullName') ? $client->getFullName() : 'N/D';
                echo "Cliente:     $cName\n";
            } else {
                echo "Cliente:     ID {$loan->getClientId()} (Dettagli non caricati)\n";
            }

            if ($loan->getNotes()) {
                echo "Note:        {$loan->getNotes()}\n";
            }
            
            echo str_repeat("-", 60) . "\n";
        }
    }

} catch (Exception $e) {
    echo "❌ ERRORE: " . $e->getMessage() . "\n";
}

echo "\n================ FINE TEST ===================\n";