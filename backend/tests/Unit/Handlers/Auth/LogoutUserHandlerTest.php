<?php
require_once __DIR__ . '/../../../../src/Application/Mediator/Mediator.php';
require_once __DIR__ . '/../../../../src/Application/Commands/Auth/AuthUserCommand.php';
require_once __DIR__ . '/../../../../src/Application/Handlers/Auth/AuthUserHandler.php';
require_once __DIR__ . '/../../../../src/Infrastructure/Security/SessionManager.php';

$mediator = new Mediator();
$mediator->register('AuthUserCommand', 'AuthUserHandler');

echo "========== TEST: TIMEOUT DELLA SESSIONE (SICUREZZA) ==========\n\n";

// 1. PREPARAZIONE: Creiamo una sessione valida
$_SESSION['user_id'] = 1;
$_SESSION['user_name'] = 'User Test';
$_SESSION['user_role'] = 1;

// --- CASO 1: SESSIONE ATTIVA (Ultima attività 5 minuti fa) ---
echo "Test 1: Verifica con attività recente (5 min)... ";
$_SESSION['last_activity'] = time() - (5 * 60); // 5 minuti fa

try {
    $mediator->send(new AuthUserCommand());
    echo "✅ OK (Accesso consentito)\n";
} catch (Exception $e) {
    echo "❌ ERRORE: Sessione invalidata troppo presto: " . $e->getMessage() . "\n";
}


echo "\n================ FINE TEST ==========================\n";