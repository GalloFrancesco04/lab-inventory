<?php

require_once __DIR__ . '/../../../src/Infrastructure/Persistence/Repositories/ObjectTypeRepository.php';
require_once __DIR__ . '/../../../src/Domain/Entities/ObjectType.php';

echo "===== Inizio Test Integrazione: ObjectTypeRepository =====\n";

// --- CONFIGURAZIONE DATABASE ---
$host = '127.0.0.1';
$dbname = 'laboratorio_inventario';
$user = 'root';
$pass = 'root';
$port = '3306';

try {
    // 1. Connessione al Database
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    // 2. AVVIO TRANSAZIONE 
    $pdo->beginTransaction();
    echo "🔌 Connessione DB riuscita. Transazione avviata.\n";

    $repo = new ObjectTypeRepository($pdo);
    
    // Dati di prova
    $testCode = "TEST-LAPTOP-001";
    
    // --- TEST 1: Creazione (Create) ---
    echo "Test 1: Creazione ObjectType... ";
    
    $newType = new ObjectType(
        $testCode, 
        "Laptop Test", 
        5, 
        "Descrizione prova", 
        "Dell", 
        "XPS 13"
    );

    $repo->create($newType);

    // Verifichiamo se esiste provando a rileggerlo subito
    $check = $repo->findByCode($testCode);
    
    if ($check !== null && $check->getName() === "Laptop Test") {
        echo "✅ PASS (Creato: " . $check->getCode() . ")\n";
    } else {
        throw new Exception("Errore: ObjectType non trovato dopo la creazione.");
    }


    // --- TEST 2: Aggiornamento Info (Update) ---
    echo "Test 2: Aggiornamento Dati... ";
    
    $check->setName("Laptop Test Modificato");
    $check->setBrand("HP");
    
    $repo->update($check);
    
    $updated = $repo->findByCode($testCode);
    
    if ($updated->getName() === "Laptop Test Modificato" && $updated->getBrand() === "HP") {
        echo "✅ PASS\n";
    } else {
        echo "❌ FAIL (Nome letto: " . $updated->getName() . ")\n";
    }

    // --- TEST 3: Aggiornamento Quantità (UpdateQuantity) ---
    echo "Test 3: Aggiornamento Quantità... ";
    

    $repo->updateQuantity($testCode, 50);
    
    $qtyCheck = $repo->findByCode($testCode);
    
    if ($qtyCheck->getQuantity() === 50) {
        echo "✅ PASS\n";
    } else {
        echo "❌ FAIL (Quantità letta: " . $qtyCheck->getQuantity() . ")\n";
    }

    // --- TEST 4: Ricerca per Brand (FindByBrand) ---
    echo "Test 4: Ricerca per Brand... ";

    $brandCheck = $repo->findByBrand("HP");
    
    if ($brandCheck !== null && $brandCheck->getCode() === $testCode) {
        echo "✅ PASS\n";
    } else {
        echo "❌ FAIL (Non trovato o brand errato)\n";
    }

    // --- TEST 5: GetAll ---
    echo "Test 5: Recupero di tutti gli elementi... ";
    $all = $repo->getAll();
    
    $found = false;
    foreach ($all as $item) {
        if ($item->getCode() === $testCode) {
            $found = true;
            break;
        }
    }

    if (count($all) > 0 && $found) {
        echo "✅ PASS (Trovati " . count($all) . " elementi, incluso il test)\n";
    } else {
        echo "❌ FAIL (Elemento di test non presente nella lista)\n";
    }

} catch (PDOException $e) {
    echo "\n⚠️ ERRORE SQL: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "\n⚠️ ERRORE LOGICO: " . $e->getMessage() . "\n";
} finally {
    // 3. ROLLBACK FINALE
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
        echo "\n🧹 Rollback eseguito. Nessuna modifica salvata.\n";
    }
}

echo "===== Fine Test =====\n";