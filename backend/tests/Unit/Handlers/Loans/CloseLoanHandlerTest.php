<?php
// 1. Caricamento dipendenze minime
require_once __DIR__ . '/../../../../src/Application/Mediator/Mediator.php';
require_once __DIR__ . '/../../../../src/Application/Commands/Loans/CloseLoanCommand.php';
require_once __DIR__ . '/../../../../src/Application/Handlers/Loans/CloseLoanHandler.php';

// 2. Setup Mediator
$mediator = new Mediator();
$mediator->register('CloseLoanCommand', 'CloseLoanHandler');

echo "=== AVVIO TEST DI SISTEMA ===\n\n";

/**
 * NOTA: Assicurati che l'ID qui sotto esista nel tuo DB 
 * e sia associato a un prestito attualmente APERTO.
 */
$idPrestitoEsistente = 1; 

try {
    echo "Tentativo di chiusura prestito ID: $idPrestitoEsistente...\n";

    // Creiamo il comando con l'ID "fisso" del tuo database
    $closeCmd = new CloseLoanCommand($idPrestitoEsistente);

    // Invio al Mediator
    $mediator->send($closeCmd);

    echo "✅ OK! L'Handler ha processato la chiusura.\n";
    echo "👉 Verifica ora nel DB: \n";
    echo "   - Tabella LOANS: la colonna 'return_date' deve essere " . (new DateTime())->format('Y-m-d') . "\n";
    echo "   - Tabella INVENTORY: gli oggetti legati al prestito $idPrestitoEsistente devono essere disponibili.\n";

} catch (Exception $e) {
    echo "❌ ERRORE DURANTE LA CHIUSURA: " . $e->getMessage() . "\n";
    echo "   Controlla che l'ID $idPrestitoEsistente non sia già stato chiuso o che esista.\n";
}

/**
 * TEST: Chiusura con ID non numerico o nullo
 */
try {
    echo "\nTest Validazione: Invio ID nullo... ";
    $badCmd = new CloseLoanCommand(null);
    $mediator->send($badCmd);
    echo "❌ FALLITO: Il sistema ha accettato un ID null\n";
} catch (Error $e) { 
    // Usiamo Error perché se il costruttore ha il type hint 'int', PHP lancia un TypeError
    echo "✅ OK! Errore di tipo bloccato: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "✅ OK! Validazione catturata: " . $e->getMessage() . "\n";
}

echo "\n=== FINE TEST ===\n";