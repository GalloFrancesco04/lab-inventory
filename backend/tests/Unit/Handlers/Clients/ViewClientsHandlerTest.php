<?php
require_once __DIR__ . '/../../../../src/Application/Mediator/Mediator.php';
require_once __DIR__ . '/../../../../src/Application/Commands/Clients/ViewClientsCommand.php';
require_once __DIR__ . '/../../../../src/Application/Handlers/Clients/ViewClientsHandler.php';
require_once __DIR__ . '/../../../../src/Domain/Entities/Client.php';

$mediator = new Mediator();
$mediator->register('ViewClientsCommand', 'ViewClientsHandler');

echo "========== TEST VISUALIZZAZIONE CLIENTI E PRESTITI ==========\n\n";

try {
    $clients = $mediator->send(new ViewClientsCommand());

    foreach ($clients as $client) {
        if ($client instanceof Client) {
            echo "ID: {$client->getId()} | Cliente: {$client->getFullName()}\n";
            
            // Accesso alla proprietà 'loans' aggiunta
            $loans = $client->loans ?? [];

            if (is_array($loans) && count($loans) > 0) {
                echo "   📦 Prestiti in carico:\n";
                
                foreach ($loans as $loan) {
                    // Estrazione sicura tramite Getter (essendo private)
                    $id = method_exists($loan, 'getId') ? $loan->getId() : 'N/D';
                    
                    // Gestione speciale per gli oggetti DateTime
                    $start = method_exists($loan, 'getStartDate') 
                             ? $loan->getStartDate()->format('d/m/Y') 
                             : 'N/D';
                             
                    $qty = method_exists($loan, 'getQuantity') ? $loan->getQuantity() : '1';
                    
                    // Stato (booleano)
                    $status = method_exists($loan, 'getState') 
                              ? ($loan->getState() ? 'ATTIVO' : 'CHIUSO') 
                              : 'N/D';

                    echo "     > [ID: $id] Inizio: $start | Qta: $qty | Stato: $status\n";
                    
                    // Se ci sono note, le stampiamo
                    if (method_exists($loan, 'getNotes') && $loan->getNotes()) {
                        echo "       Note: {$loan->getNotes()}\n";
                    }
                }
            } else {
                echo "   ℹ️ Nessun prestito attivo.\n";
            }
            echo str_repeat("-", 50) . "\n";
        }
    }

} catch (Exception $e) {
    echo "❌ ERRORE: " . $e->getMessage() . "\n";
}