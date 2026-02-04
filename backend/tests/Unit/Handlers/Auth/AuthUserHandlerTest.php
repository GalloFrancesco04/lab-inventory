<?php
require_once __DIR__ . '/../../../../src/Application/Mediator/Mediator.php';
require_once __DIR__ . '/../../../../src/Application/Commands/Auth/AuthUserCommand.php';
require_once __DIR__ . '/../../../../src/Application/Handlers/Auth/AuthUserHandler.php';

$mediator = new Mediator();
$mediator->register('AuthUserCommand', 'AuthUserHandler');

echo "========== TEST: VALIDAZIONE SESSIONE (AUTH USER) ==========\n\n";

// --- CASO 1: UTENTE NON AUTENTICATO (Cookie mancante o sbagliato) ---
echo "Test 1: Verifica senza sessione attiva... ";

// Simuliamo una sessione vuota o mai iniziata
$_SESSION = []; 

try {
    $command = new AuthUserCommand();
    $mediator->send($command);
    
    // Se l'handler non lancia eccezione, il test è fallito (dovrebbe proteggere la rotta)
    echo "❌ ERRORE: L'handler ha permesso l'accesso senza sessione!\n";
} catch (Exception $e) {
    echo "✅ OK (Accesso negato correttamente: " . $e->getMessage() . ")\n";
}

echo "------------------------------------------------------------\n";

// --- CASO 2: UTENTE AUTENTICATO (Cookie/Sessione valida) ---
echo "Test 2: Verifica con sessione valida... ";

// Simuliamo manualmente ciò che il SessionManager scriverebbe dopo un login riuscito
$_SESSION['user_id'] = 1;
$_SESSION['user_name'] = 'Admin Test';
$_SESSION['user_role'] = 1;

try {
    $command = new AuthUserCommand();
    $result = $mediator->send($command);
    
    // Se l'handler termina senza errori (o restituisce i dati utente), il test è superato
    echo "✅ SUCCESSO: Utente riconosciuto.\n";
    
    if (is_object($result)) {
        echo "Dati restituiti dall'Handler:\n";
        echo " - ID: " . ($result->id ?? 'N/D') . "\n";
        echo " - Nome: " . ($result->name ?? 'N/D') . "\n";
    }
} catch (Exception $e) {
    echo "❌ ERRORE: L'handler ha rifiutato una sessione valida: " . $e->getMessage() . "\n";
}

echo "\n================ FINE TEST ==========================\n";