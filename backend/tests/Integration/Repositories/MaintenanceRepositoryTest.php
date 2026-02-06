<?php

require_once __DIR__ . '/../../../src/Infrastructure/Persistence/Repositories/MaintenanceRepository.php';
require_once __DIR__ . '/../../../src/Domain/Entities/Maintenance.php';
require_once __DIR__ . '/../../../src/Domain/Entities/InventoryItem.php';

echo "===== Inizio Test Integrazione: MaintenanceRepository (Tutti i test 1-8) =====\n";

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

    // 2. Avvio Transazione (Bolla di protezione)
    $pdo->beginTransaction();
    echo "🔌 Connessione DB riuscita. Transazione avviata.\n";

    $repo = new MaintenanceRepository($pdo);

    // --- SETUP AMBIENTE (Dati preliminari) ---
    // Creiamo: 1 Tipo, 2 Oggetti (A e B), 1 Tecnico
    
    $pdo->exec("INSERT INTO objecttype (code, name, quantity) VALUES ('FULL-TEST-MAINT', 'Test Device', 10)");
    
    $pdo->exec("INSERT INTO object_ (object_type_code, state, location) VALUES ('FULL-TEST-MAINT', 3, 'Lab Riparazioni')");
    $objIdA = $pdo->lastInsertId();

    $pdo->exec("INSERT INTO object_ (object_type_code, state, location) VALUES ('FULL-TEST-MAINT', 1, 'Magazzino')");
    $objIdB = $pdo->lastInsertId();

    $pdo->exec("INSERT INTO user (email, pass, role) VALUES ('tech.full@test.it', 'pass', 2)");
    $techId = $pdo->lastInsertId();

    echo "🛠  Setup completato: ObjA=$objIdA, ObjB=$objIdB, Tech=$techId\n";
    echo "------------------------------------------------------------\n";


    // --- TEST 1: Creazione (Create) ---
    echo "Test 1: Creazione Manutenzione... ";
    
    $startDate = new DateTime('now');
    $newMaint = new Maintenance(
        null, $objIdA, "Problema Alimentazione", $startDate, null, 
        "Hardware", $techId, "333-0000", 100.00
    );

    $repo->create($newMaint);
    $maintId = $newMaint->getId();

    if ($maintId > 0) echo "✅ PASS (ID: $maintId)\n";
    else throw new Exception("ID non generato.");


    // --- TEST 2: Lettura (FindById) ---
    echo "Test 2: Lettura per ID... ";
    
    $found = $repo->findById($maintId);
    
    if ($found !== null && $found->getDescription() === "Problema Alimentazione") echo "✅ PASS\n";
    else echo "❌ FAIL\n";


    // --- TEST 3: Aggiornamento Costo (UpdateCost) ---
    echo "Test 3: Aggiornamento Costo... ";
    
    $repo->updateCost($maintId, 150.00);
    $checkCost = $repo->findById($maintId);
    
    if ($checkCost->getCost() == 150.00) echo "✅ PASS\n";
    else echo "❌ FAIL (Costo: " . $checkCost->getCost() . ")\n";


    // --- TEST 4: Manutenzioni Attive (GetActiveMaintenances) ---
    echo "Test 4: Get Active Maintenances... ";
    
    $actives = $repo->getActiveMaintenances();
    $isActiveFound = false;
    foreach ($actives as $m) {
        if ($m->getId() == $maintId) $isActiveFound = true;
    }

    if ($isActiveFound) echo "✅ PASS\n";
    else echo "❌ FAIL\n";


    // --- TEST 5: Chiusura (CloseMaintenance) ---
    echo "Test 5: Chiusura Manutenzione... ";
    
    $endDate = new DateTime('now');
    $repo->CloseMaintenance($maintId, $endDate);
    $closedMaint = $repo->findById($maintId);
    
    if ($closedMaint->getEndDate() !== null) echo "✅ PASS\n";
    else echo "❌ FAIL\n";


    // --- TEST 6: Costo Totale Oggetto (GetCostObjectMaintenance) ---
    echo "Test 6: Calcolo Costo Totale Oggetto... ";
    
    // L'oggetto A ha una sola manutenzione da 150.00 (aggiornata nel test 3)
    $total = $repo->getCostObjectMaintenance($objIdA);
    
    if ($total == 150.00) echo "✅ PASS\n";
    else echo "❌ FAIL (Totale: $total)\n";


    // --- TEST 7: Manutenzioni del Tecnico (GetAllTechnicianMaintenances) ---
    echo "Test 7: Storico Manutenzioni Tecnico... ";
    
    $techHistory = $repo->getAllTechnicianMaintenances($techId);
    $inHistory = false;
    foreach ($techHistory as $m) {
        if ($m->getId() == $maintId) $inHistory = true;
    }

    if ($inHistory) echo "✅ PASS\n";
    else echo "❌ FAIL\n";


    // --- TEST 8: Oggetti Assegnati (GetAllTechnicianAssignedObjects) ---
    echo "Test 8: Oggetti Attualmente Assegnati (Oggetti Fisici)... ";
    
    // PREPARAZIONE:
    // La manutenzione sull'Oggetto A è CHIUSA (Test 5), quindi non dovrebbe apparire.
    // Creiamo una NUOVA manutenzione ATTIVA sull'Oggetto B per vederla comparire.
    $maintB = new Maintenance(null, $objIdB, "Nuova Attiva", new DateTime(), null, "Software", $techId, "N/A", 0);
    $repo->create($maintB);

    // ESECUZIONE QUERY
    $assignedObjects = $repo->getAllTechnicianAssignedObjects($techId);

    // VERIFICA
    $foundA = false; // Non deve esserci (Chiusa)
    $foundB = false; // Deve esserci (Attiva)
    $isCorrectType = true;

    foreach ($assignedObjects as $item) {
        if (!($item instanceof InventoryItem)) {
            $isCorrectType = false;
        }
        if ($item->getId() == $objIdA) $foundA = true;
        if ($item->getId() == $objIdB) $foundB = true;
    }

    if (!$isCorrectType) {
        echo "❌ FAIL (Il repository non restituisce istanze di InventoryItem)\n";
    } elseif ($foundB && !$foundA) {
        echo "✅ PASS (Trovato B attivo, escluso A chiuso)\n";
    } else {
        echo "❌ FAIL\n";
        echo "   - Trovato Oggetto A (Chiuso - Errore se presente): " . ($foundA ? "SI" : "NO") . "\n";
        echo "   - Trovato Oggetto B (Attivo - Errore se assente): " . ($foundB ? "SI" : "NO") . "\n";
    }


} catch (PDOException $e) {
    echo "\n⚠️ ERRORE SQL: " . $e->getMessage() . "\n";
} catch (ArgumentCountError $e) {
    echo "\n⚠️ ERRORE ARGOMENTI: " . $e->getMessage() . "\n";
    echo "   (Controlla nel Repository: 'new InventoryItem(...)' ha i parametri nell'ordine giusto?)\n";
} catch (TypeError $e) {
    echo "\n⚠️ ERRORE DI TIPO: " . $e->getMessage() . "\n";
    echo "   (Probabilmente stai passando una stringa dove serve un int nel costruttore di InventoryItem)\n";
} catch (Exception $e) {
    echo "\n⚠️ ERRORE GENERICO: " . $e->getMessage() . "\n";
} finally {
    // 3. ROLLBACK FINALE OBBLIGATORIO
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
        echo "\n🧹 Rollback eseguito. Nessuna traccia lasciata nel DB.\n";
    }
}

echo "===== Fine Test =====\n";