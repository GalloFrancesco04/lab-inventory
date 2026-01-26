<?php
require_once __DIR__ . '/../../../../src/Application/Mediator/Mediator.php';
require_once __DIR__ . '/../../../../src/Application/Commands/Loans/ViewSingleLoanCommand.php';
require_once __DIR__ . '/../../../../src/Application/Handlers/Loans/ViewSingleLoanHandler.php';
require_once __DIR__ . '/../../../../src/Domain/Entities/Loan.php';

$mediator = new Mediator();

// Registrazione dell'handler
$mediator->register('ViewSingleLoanCommand', 'ViewSingleLoanHandler');

echo "========== TEST VISUALIZZAZIONE SINGOLO PRESTITO ==========\n\n";

/**
 * CASE 1: Recupero prestito esistente (Happy Path)
 */
try {
    $targetId = 1; // Assicurati che questo ID esista nella tabella loans
    echo "Test 1: Recupero prestito con ID {$targetId}... ";
    
    $command = new ViewSingleLoanCommand($targetId);
    $loan = $mediator->send($command);
    
    if ($loan instanceof Loan) {
        echo "✅ SUCCESSO\n";
        echo "Dettagli Prestito:\n";
        echo "- ID: " . $loan->getId() . "\n";
        echo "- Cliente ID: " . $loan->getClientId() . "\n";
        echo "- Data Inizio: " . $loan->getStartDate()->format('d/m/Y') . "\n";
        echo "- Data Fine Prevista: " . $loan->getEndDate()->format('d/m/Y') . "\n";
        
        $returnDate = $loan->getReturnDate() ? $loan->getReturnDate()->format('d/m/Y') : "Non ancora restituito";
        echo "- Data Restituzione: " . $returnDate . "\n";
        
        echo "- Note: " . ($loan->getNotes() ?? "Nessuna nota") . "\n";
        echo "- Stato: " . ($loan->getState() ? "Attivo" : "Chiuso/Restituito") . "\n";
    } else {
        throw new Exception("Il risultato non è un'istanza di Loan.");
    }

} catch (Exception $e) {
    echo "❌ ERRORE: " . $e->getMessage() . "\n";
}

echo "----------------------------------------------------------\n";

/**
 * CASE 2: Recupero ID inesistente (Edge Case)
 */
try {
    $invalidId = 0; // O un ID altissimo
    echo "Test 2: Recupero prestito con ID inesistente ({$invalidId})... ";
    
    $command = new ViewSingleLoanCommand($invalidId);
    $mediator->send($command);
    
    echo "⚠️ NOTA: Il sistema non ha generato eccezioni per un ID mancante.\n";
} catch (Exception $e) {
    // Verifica che l'handler lanci un'eccezione se il repo restituisce null
    echo "✅ CATTURATO (Corretto): " . $e->getMessage() . "\n";
}

echo "\n================ FINE TEST ===================\n";