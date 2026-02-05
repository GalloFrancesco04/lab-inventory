<?php

require_once __DIR__ . '/../../../src/Infrastructure/Persistence/Repositories/OperationsRepository.php';
require_once __DIR__ . '/../../../src/Domain/Entities/Operation.php';

echo "===== Inizio Test Integrazione: OperationsRepository (Operazioni di Manutenzione) =====\n";

// --- CONFIGURAZIONE DATABASE ---
$host = '127.0.0.1';
$dbname = 'laboratorio_inventario';
$user = 'root';
$pass = 'root';
$port = '3306';

try {
    // 1. Connessione
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    // 2. AVVIO TRANSAZIONE
    $pdo->beginTransaction();
    echo "🔌 Connessione DB riuscita. Transazione avviata.\n";

    $repo = new OperationsRepository($pdo);

    // --- SETUP AMBIENTE (Gerarchia Completa) ---
    // A. Tipo Oggetto
    $pdo->exec("INSERT INTO objecttype (code, name, quantity) VALUES ('OP-TEST-TYPE', 'Complex Device', 5)");
    
    // B. Oggetto Fisico
    $pdo->exec("INSERT INTO object_ (object_type_code, state, location) VALUES ('OP-TEST-TYPE', 3, 'Banco 1')");
    $objectId = $pdo->lastInsertId();

    // C. Tecnico
    $pdo->exec("INSERT INTO user (email, pass, role) VALUES ('op.tech@test.it', 'pass', 2)");
    $techId = $pdo->lastInsertId();

    // D. Manutenzione (Genitore dell'Operazione)
    $pdo->exec("INSERT INTO maintenance (object_id, description, start_date, type, technician_id, phone_number, cost) 
                VALUES ($objectId, 'Riparazione Generale', NOW(), 'Hardware', $techId, '123', 0)");
    $maintId = (int)$pdo->lastInsertId();

    echo "🛠  Setup: Obj=$objectId, Tech=$techId, Maint=$maintId\n";

    // --- TEST 1: Creazione Operazione (Create) ---
    echo "Test 1: Creazione Operazione... ";
    
    $startDate = new DateTime('now');
    
    // Costruttore Operation: id, maintenance_id, start_date, end_date, description, cost
    $newOp = new Operation(
        null, 
        $maintId, 
        $startDate, 
        null, // End date null
        "Sostituzione Condensatori", 
        45.50
    );

    $repo->create($newOp);
    $opId = $newOp->getId();

    if ($opId > 0) {
        echo "✅ PASS (ID creato: $opId)\n";
    } else {
        throw new Exception("ID Operazione non generato.");
    }

    // --- TEST 2: Lettura per ID (GetById) ---
    echo "Test 2: Lettura per ID... ";
    
    $foundOp = $repo->getById($opId);
    
    if ($foundOp !== null &&
        $foundOp->getDescription() === "Sostituzione Condensatori" &&
        $foundOp->getMaintenanceId() === $maintId) {
        echo "✅ PASS\n";
    } else {
        echo "❌ FAIL (Dati non corrispondenti)\n";
    }

    echo "Operazione trovata: ID=$opId, Descrizione={$foundOp->getDescription()},Maintenance_Id={$foundOp->getMaintenanceId()}, Costo={$foundOp->getCost()}\n";

    // --- TEST 3: Chiusura Operazione (CloseOperation) ---
    echo "Test 3: Chiusura Operazione... ";
    
    $endDate = new DateTime('+2 hours');
    $repo->closeOperation($opId, $endDate);
    
    $closedCheck = $repo->getById($opId);
    
    if ($closedCheck->getEndDate() !== null) {
        echo "✅ PASS (Data fine impostata)\n";
    } else {
        echo "❌ FAIL (End Date ancora null)\n";
    }

    // --- TEST 4: Aggiornamento Dati (Update) ---
    echo "Test 4: Aggiornamento Descrizione e Costo... ";
    

    $closedCheck->setDescription("Sostituzione Condensatori e Pulizia");
    $closedCheck->setCost(60.00);
    
    $repo->update($closedCheck);
    
    $updatedOp = $repo->getById($opId);
    
    if ($updatedOp->getDescription() === "Sostituzione Condensatori e Pulizia" && $updatedOp->getCost() == 60.00) {
        echo "✅ PASS\n";
    } else {
        echo "❌ FAIL (Valori aggiornati non corretti)\n";
    }

    // --- TEST 5: Lista Operazioni per Manutenzione (GetAllMaintenanceOperations) ---
    echo "Test 5: Operazioni della Manutenzione... ";
    
    // Creiamo una seconda operazione per la stessa manutenzione
    $op2 = new Operation(null, $maintId, new DateTime(), null, "Test Finale", 20.00);
    $repo->create($op2);
    
    $listMaint = $repo->getAllMaintenanceOperations($maintId);
    
    if (count($listMaint) === 2) {
        echo "✅ PASS (Trovate 2 operazioni)\n";
    } else {
        echo "❌ FAIL (Conteggio errato: " . count($listMaint) . ")\n";
    }

    // --- TEST 6: Tutte le Operazioni di un Oggetto (GetAllObjectOperations) ---
    echo "Test 6: Operazioni totali per Oggetto (JOIN)... ";
    
    $listObjectOps = $repo->getAllObjectOperations($objectId);
    
    // Dovrebbe trovarne sempre 2 (quelle create sopra), perché appartengono alla manutenzione $maintId che appartiene a $objectId
    if (count($listObjectOps) === 2) {
        echo "✅ PASS\n";
    } else {
        echo "❌ FAIL (Operazioni non trovate tramite l'oggetto)\n";
    }

} catch (PDOException $e) {
    echo "\n⚠️ ERRORE SQL: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "\n⚠️ ERRORE LOGICO: " . $e->getMessage() . "\n";
} finally {
    // 3. ROLLBACK FINALE
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
        echo "\n🧹 Rollback eseguito.\n";
    }
}

echo "===== Fine Test =====\n";