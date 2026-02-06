<?php


require_once __DIR__ . '/../../../../src/Infrastructure/Security/PasswordHasher.php';

echo "===== Inizio Test Manuali: PasswordHasher =====\n" ;

try {

    $hasher = new PasswordHasher();
    $password = "PasswordSegreta123!";

    // --- TEST 1: Hashing ---
    echo "Test 1: Generazione Hash... ";
    $hash = $hasher->hashPassword($password);

    if (!empty($hash) && $hash !== $password) {
        echo "✅ PASS (Hash generato: " .$hash . ") \n";
    } else {
        echo "❌ FAIL (L'hash è vuoto o uguale alla password) \n";
    }

    // --- TEST 2: Verifica Corretta ---
    echo "Test 2: Verifica password corretta... ";
    if ($hasher->verifyPassword($password, $hash) === true) {
        echo "✅ PASS \n";
    } else {
        echo "❌ FAIL (La password corretta non è stata riconosciuta) \n";
    }

    // --- TEST 3: Verifica Errata ---
    echo "Test 3: Verifica password errata... ";
    if ($hasher->verifyPassword("Sbagliata!", $hash) === false) {
        echo "✅ PASS \n";
    } else {
        echo "❌ FAIL (Una password sbagliata è stata accettata!) \n";
    }

    // --- TEST 4: Sicurezza (Salting) ---
    echo "Test 4: Controllo univocità (Salting)... ";
    $hash2 = $hasher->hashPassword($password); // Stessa password di prima

    if ($hash !== $hash2) {
        echo "✅ PASS (Due hash della stessa password sono diversi) \n" ;
    } else {
        echo "❌ FAIL (Hash identici: rischio sicurezza!) \n" ;
    }

} catch (Exception $e) {
    echo "⚠️ ERRORE CRITICO: " . $e->getMessage() . "\n";
}

echo "===== Fine Test ===== \n" ;