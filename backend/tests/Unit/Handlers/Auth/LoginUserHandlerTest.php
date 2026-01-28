<?php
require_once __DIR__ . '/../../../../src/Application/Mediator/Mediator.php';
require_once __DIR__ . '/../../../../src/Application/Commands/Auth/LoginUserCommand.php';
require_once __DIR__ . '/../../../../src/Application/Handlers/Auth/LoginUserHandler.php';
require_once __DIR__ . '/../../../../src/Infrastructure/Security/SessionManager.php';

$mediator = new Mediator();
$mediator->register('LoginUserCommand', 'LoginUserHandler');
$session = new SessionManager();

echo "========== TEST: PROCESSO DI AUTENTICAZIONE UTENTE ==========\n\n";

try {
    // 1. CASO: Credenziali errate
    echo "Test 1: Tentativo con password errata... ";
    try {
        $badCommand = new LoginUserCommand("admin@example.com", "wrong_password");
        $mediator->send($badCommand);
        echo "❌ ERRORE: Il sistema ha permesso il login con password errata!\n";
    } catch (Exception $e) {
        echo "✅ OK (Bloccato correttamente: " . $e->getMessage() . ")\n";
    }

    echo "\n";

    // 2. CASO: Credenziali corrette
    // Sostituisci con una email e password reali presenti nel tuo DB (hashata)
    $email = "admin@example.com";
    $pass = "password123"; 

    echo "Test 2: Tentativo con credenziali corrette ($email)... ";
    
    $loginCommand = new LoginUserCommand($email, $pass);
    $mediator->send($loginCommand);
    
    echo "✅ SUCCESSO: Login eseguito.\n";

    // 3. VERIFICA DEI DATI IN SESSIONE
    echo "\nVerifica dati salvati nel SessionManager:\n";
    if ($session->isLoggedIn()) {
        echo " - ID Utente:   " . $session->getUserId() . "\n";
        echo " - Email/Nome:  " . $session->getUserName() . "\n";
        echo " - Ruolo:       " . $session->getUserRole() . "\n";
        echo "✅ Sessione validata correttamente.\n";
    } else {
        throw new Exception("La sessione risulta vuota nonostante il login riuscito.");
    }

} catch (Exception $e) {
    echo "❌ ERRORE CRITICO: " . $e->getMessage() . "\n";
}

echo "\n================ FINE TEST ==========================\n";