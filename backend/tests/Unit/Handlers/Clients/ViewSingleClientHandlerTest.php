<?php
require_once __DIR__ . '/../../../../src/Application/Mediator/Mediator.php';
require_once __DIR__ . '/../../../../src/Application/Commands/Clients/ViewSingleClientCommand.php';
require_once __DIR__ . '/../../../../src/Application/Handlers/Clients/ViewSingleClientHandler.php';
require_once __DIR__ . '/../../../../src/Domain/Entities/Client.php';

$mediator = new Mediator();

// Registrazione
$mediator->register('ViewSingleClientCommand', 'ViewSingleClientHandler');

echo "========== TEST VISUALIZZAZIONE SINGOLO CLIENTE E PRESTITI ==========\n\n";

/**
 * CASE 1: Recupero cliente esistente con i suoi prestiti
 */
try {
    $targetId = 1; // Assicurati che questo ID esista nel tuo DB
    echo "Test 1: Recupero cliente con ID {$targetId}... ";
    
    $command = new ViewSingleClientCommand($targetId);
    $client = $mediator->send($command);
    
    if ($client instanceof Client) {
        echo "✅ SUCCESSO\n\n";
        echo "--- SCHEDA CLIENTE ---\n";
        echo "Nome Completo: " . $client->getFullName() . "\n";
        echo "Email:         " . $client->getEmail() . "\n";
        echo "Stato Cliente: " . $client->getState() . "\n";
        echo "----------------------\n";

        // Gestione Proprietà 'loans' aggiunta dinamicamente
        $loans = $client->loans ?? [];

        if (is_array($loans) && count($loans) > 0) {
            echo "📦 PRESTITI ASSOCIATI:\n";
            
            foreach ($loans as $loan) {
                // Essendo proprietà private in Loan, usiamo i Getter
                $id    = method_exists($loan, 'getId') ? $loan->getId() : 'N/D';
                $qty   = method_exists($loan, 'getQuantity') ? $loan->getQuantity() : '1';
                $state = method_exists($loan, 'getState') 
                         ? ($loan->getState() ? 'ATTIVO' : 'CHIUSO') 
                         : 'N/D';

                // Gestione specifica per le date (oggetti DateTime)
                $start = 'N/D';
                if (method_exists($loan, 'getStartDate')) {
                    $dateObj = $loan->getStartDate();
                    $start = ($dateObj instanceof DateTime) ? $dateObj->format('d/m/Y') : $dateObj;
                }

                echo "  > [Loan ID: $id] Inizio: $start | Qta: $qty | Stato: $state\n";

                // Se sono presenti note
                if (method_exists($loan, 'getNotes') && $loan->getNotes()) {
                    echo "    Note: {$loan->getNotes()}\n";
                }
            }
        } else {
            echo "ℹ️ Nessun prestito trovato per questo cliente.\n";
        }

    } else {
        throw new Exception("Il risultato non è un'istanza di Client.");
    }

} catch (Exception $e) {
    echo "❌ ERRORE: " . $e->getMessage() . "\n";
}

echo "\n----------------------------------------------------------\n";

/**
 * CASE 2: Recupero ID inesistente
 */
try {
    $invalidId = 999999;
    echo "Test 2: Recupero cliente con ID inesistente ({$invalidId})... ";
    
    $command = new ViewSingleClientCommand($invalidId);
    $mediator->send($command);
    
    echo "⚠️ NOTA: Il sistema non ha segnalato l'assenza del cliente.\n";
} catch (Exception $e) {
    echo "✅ CATTURATO: " . $e->getMessage() . "\n";
}

echo "\n================ FINE TEST ===================\n";