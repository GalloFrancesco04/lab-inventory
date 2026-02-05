<?php

// Percorsi di inclusione corretti
require_once __DIR__ . '/../../../src/Infrastructure/Persistence/Repositories/ItemRepository.php';
require_once __DIR__ . '/../../../src/Domain/Entities/InventoryItem.php';

echo "===== Inizio Test Integrazione: ItemRepository (Oggetti Fisici) =====\n";

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

    $repo = new ItemRepository($pdo);
    
    // --- SETUP PRELIMINARE (IMPORTANTE!) ---
    // Poiché 'object_' ha una chiave esterna verso 'objecttype',
    // dobbiamo creare un TIPO DI PROVA prima di creare l'oggetto fisico.
    $typeCode = "TEST-HARDWARE-001";
    $pdo->exec("INSERT INTO objecttype (code, name, quantity) VALUES ('$typeCode', 'Hardware di Test', 10)");
    echo "🛠  Setup: Creato ObjectType temporaneo ($typeCode)\n";

    
    // --- TEST 1: Creazione Oggetto (Create) ---
    echo "Test 1: Creazione Oggetto Fisico... ";
    
    // Parametri: ID (null), CodiceTipo, Stato (1=Disponibile), Posizione
    $newItem = new InventoryItem(null, $typeCode, 1, "Scaffale A1");
    
    $repo->create($newItem);
    $newId = $newItem->getId();

    if ($newId > 0) {
        echo "✅ PASS (ID creato: $newId)\n";
    } else {
        throw new Exception("L'ID non è stato generato.");
    }

    // --- TEST 2: Lettura (FindById) ---
    echo "Test 2: Ricerca per ID... ";
    
    $foundItem = $repo->findById($newId);
    
    // Verifichiamo ID, Codice Tipo e Posizione
    if ($foundItem !== null && 
        $foundItem->getObjectTypeCode() === $typeCode && 
        $foundItem->getLocation() === "Scaffale A1") {
        echo "✅ PASS\n";
    } else {
        echo "❌ FAIL (Dati non corrispondenti)\n";
    }

    // --- TEST 3: Aggiornamento Stato (MarkObjectAsLoaned) ---
    echo "Test 3: Cambio Stato (Prestito)... ";
    
    // Usiamo il metodo specifico del repository per segnarlo come "In Prestito" (Stato 2)
    $repo->MarkObjectAsLoaned($newId);
    
    // Rileggiamo dal DB per conferma
    $loanedItem = $repo->findById($newId);
    
    // Nota: getStateCode() restituisce l'intero associato allo stato
    if ($loanedItem->getStateCode() === 2) {
        echo "✅ PASS (Stato aggiornato a 2)\n";
    } else {
        echo "❌ FAIL (Stato attuale: " . $loanedItem->getStateCode() . ")\n";
    }

    // --- TEST 4: Aggiornamento Posizione ---
    echo "Test 4: Aggiornamento Posizione... ";
    
    $repo->updateLocation($newId, "Laboratorio B");
    $movedItem = $repo->findById($newId);
    
    if ($movedItem->getLocation() === "Laboratorio B") {
        echo "✅ PASS\n";
    } else {
        echo "❌ FAIL (Posizione letta: " . $movedItem->getLocation() . ")\n";
    }

    // --- TEST 5: Ricerca Avanzata (Search) ---
    echo "Test 5: Ricerca con Filtri... ";
    
    // Cerchiamo oggetti di quel tipo specifico che si trovano nel "Laboratorio B"
    $filters = [
        'object_type_code' => $typeCode,
        'location' => '%Laboratorio%' // Testiamo anche il LIKE parziale
    ];
    
    $results = $repo->searchByStateTypeLocation($filters);
    
    // Dobbiamo trovare almeno il nostro oggetto
    $foundInSearch = false;
    foreach ($results as $res) {
        if ($res['id'] == $newId) {
            $foundInSearch = true;
            break;
        }
    }

    if ($foundInSearch) {
        echo "✅ PASS (Oggetto trovato tramite filtri)\n";
    } else {
        echo "❌ FAIL (Array risultati vuoto o oggetto mancante)\n";
        print_r($results); // Stampa per debug
    }

} catch (PDOException $e) {
    echo "\n⚠️ ERRORE SQL: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "\n⚠️ ERRORE LOGICO: " . $e->getMessage() . "\n";
} finally {
    // 3. ROLLBACK FINALE
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
        echo "\n🧹 Rollback eseguito. Nessuna traccia lasciata nel DB.\n";
    }
}

echo "===== Fine Test =====\n";