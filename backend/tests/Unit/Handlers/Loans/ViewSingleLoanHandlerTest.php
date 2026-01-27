<?php
require_once __DIR__ . '/../../../../src/Application/Mediator/Mediator.php';
require_once __DIR__ . '/../../../../src/Application/Commands/Loans/ViewSingleLoanCommand.php';
require_once __DIR__ . '/../../../../src/Application/Handlers/Loans/ViewSingleLoanHandler.php';
require_once __DIR__ . '/../../../../src/Domain/Entities/Loan.php';

$mediator = new Mediator();
$mediator->register('ViewSingleLoanCommand', 'ViewSingleLoanHandler');

echo "========== TEST VISUALIZZAZIONE PRESTITO COMPLETO ==========\n\n";

try {
    $targetId = 1; 
    echo "Test 1: Recupero prestito ID {$targetId}... ";
    
    $command = new ViewSingleLoanCommand($targetId);
    $loan = $mediator->send($command);
    
    if ($loan instanceof Loan) {
        echo "✅ SUCCESSO\n\n";
        
        // --- SEZIONE 1: DATI BASE PRESTITO ---
        echo "=== DETTAGLI PRESTITO ===\n";
        echo "ID:            " . $loan->getId() . "\n";
        echo "Stato:         " . ($loan->getState() ? "🟢 ATTIVO" : "🔴 CHIUSO") . "\n";
        echo "Inizio:        " . $loan->getStartDate()->format('d/m/Y') . "\n";
        echo "Fine Prevista: " . $loan->getEndDate()->format('d/m/Y') . "\n";
        $returnDate = $loan->getReturnDate() ? $loan->getReturnDate()->format('d/m/Y') : "Non restituito";
        echo "Restituzione:  " . $returnDate . "\n";
        echo "Note:          " . ($loan->getNotes() ?? "N/D") . "\n\n";

        // --- SEZIONE 2: DATI CLIENTE (Proprietà aggiunta $loan->client) ---
        echo "=== CLIENTE ASSOCIATO ===\n";
        $client = $loan->client ?? null;
        if ($client) {
            // Usiamo i getter della classe Client
            $cName = method_exists($client, 'getFullName') ? $client->getFullName() : 'N/D';
            $cEmail = method_exists($client, 'getEmail') ? $client->getEmail() : 'N/D';
            echo "Nome:  $cName\n";
            echo "Email: $cEmail\n\n";
        } else {
            echo "⚠️ Dati cliente non caricati.\n\n";
        }

        // --- SEZIONE 3: OGGETTI PRESTATI (Proprietà aggiunta $loan->items) ---
        echo "=== OGGETTI NEL PRESTITO ===\n";
        $items = $loan->loanedObjects ?? []; // Supponiamo sia un array di esemplari fisici
        if (is_array($items) && count($items) > 0) {
            foreach ($items as $item) {
                // Estraiamo codice tipo e posizione dall'esemplare
                $code = method_exists($item, 'getObjectTypeCode') ? $item->getObjectTypeCode() : 'N/D';
                $id = method_exists($item, 'getObjectId') ? $item->getObjectId() : 'N/D';
                $damaged  = method_exists($item, 'isDamaged') ? $item->isDamaged() : 'N/D';
                
                echo "  > [Esemplare] Id: $id | Codice: $code | Danneggiato: $damaged\n";
            }
        } else {
            echo "⚠️ Nessun oggetto fisico collegato a questo prestito.\n";
        }

    } else {
        throw new Exception("L'handler non ha restituito un'istanza di Loan.");
    }

} catch (Exception $e) {
    echo "❌ ERRORE: " . $e->getMessage() . "\n";
}

echo "\n================ FINE TEST ===================\n";