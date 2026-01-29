<?php
require_once __DIR__ . '/../../../../src/Application/Mediator/Mediator.php';
require_once __DIR__ . '/../../../../src/Application/Commands/Clients/CreateClientCommand.php';
require_once __DIR__ . '/../../../../src/Application/Handlers/Clients/CreateClientHandler.php';

$mediator = new Mediator();

// Registrazione pulita: l'handler si gestisce il repo da solo
$mediator->register('CreateClientCommand', 'CreateClientHandler');

echo "========== TEST CREAZIONE CLIENTE ==========\n";

try {
    $command = new CreateClientCommand("Marco", "marco@test.it", "Neri", 1);
    $id = $mediator->send($command);
    
    echo "✅ Cliente creato con successo! ID: " . ($id ?? 'N/A') . "\n";
} catch (Exception $e) {
    echo "❌ Errore durante il test: " . $e->getMessage() . "\n";
}

/**
 * CASE 2: Validazione Email (Vincolo DB o Logica)
 */
try {
    echo "Test 2: Tentativo email duplicata... ";
    // Usiamo la stessa email del Test 1 (se non hai usato il timestamp sopra)
    $cmd = new CreateClientCommand("Duplicato", "marco@test.it", "Rossi", 1);
    $mediator->send($cmd);
    echo "⚠️ NOTA: Il sistema ha accettato un'email duplicata.\n";
} catch (Exception $e) {
    echo "✅ CATTURATO (Email duplicata non permessa): " . $e->getMessage() . "\n";
}


/**
 * CASE 3: Caratteri Speciali e SQL Injection base
 */
try {
    echo "Test 3: Inserimento nomi con caratteri speciali... ";
    $cmd = new CreateClientCommand("O'Brian", "obrian@test.it", "L'Altro", 1);
    $mediator->send($cmd);
    echo "✅ SUCCESSO (Quote gestite correttamente)\n";
} catch (Exception $e) {
    echo "❌ ERRORE: " . $e->getMessage() . "\n";
}

/**
 * CASE 4: Stringhe troppo lunghe (Vincolo VARCHAR)
 */
try {
    echo "Test 4: Nome oltre i limiti (300 caratteri)... ";
    $longName = str_repeat("A", 300);
    $cmd = new CreateClientCommand($longName, "long@test.it", "Rossi", 1);
    $mediator->send($cmd);
    echo "⚠️ NOTA: Stringa troppo lunga accettata.\n";
} catch (Exception $e) {
    echo "✅ CATTURATO (Limite lunghezza): " . $e->getMessage() . "\n";
}

/**
 * CASE 5: Stato non valido (Fuori range)
 */
try {
    echo "Test 5: Creazione con stato numerico assurdo (999)... ";
    $cmd = new CreateClientCommand("Test", "test.state@test.it", "User", 999);
    $mediator->send($cmd);
    echo "⚠️ NOTA: Stato 999 accettato (Dovrebbe essere validato).\n";
} catch (Exception $e) {
    echo "✅ CATTURATO (Stato non valido): " . $e->getMessage() . "\n";
}


echo "\n================ FINE TEST ===================\n";