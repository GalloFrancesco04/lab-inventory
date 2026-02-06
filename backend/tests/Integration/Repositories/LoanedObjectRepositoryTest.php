<?php

require_once __DIR__ . '/../../../src/Infrastructure/Persistence/Repositories/LoanedObjectRepository.php';
require_once __DIR__ . '/../../../src/Domain/Entities/LoanedObject.php';

echo "===== Inizio Test Integrazione: LoanedObjectRepository (Associazione Oggetti-Prestiti) =====\n";

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

    $repo = new LoanedObjectRepository($pdo);

    // --- SETUP AMBIENTE COMPLESSO ---
    // Per testare la tabella ponte, dobbiamo creare tutta la gerarchia:
    // Tipo -> Oggetto -> Cliente -> Prestito
    
    // A. Creiamo un Tipo
    $typeCode = "TEST-LINK-TYPE";
    $pdo->exec("INSERT INTO objecttype (code, name, quantity) VALUES ('$typeCode', 'Link Test Item', 10)");
    
    // B. Creiamo 2 Oggetti Fisici (ID dinamici)
    $pdo->exec("INSERT INTO object_ (object_type_code, state, location) VALUES ('$typeCode', 1, 'Scaffale A')");
    $objId1 = $pdo->lastInsertId();
    
    $pdo->exec("INSERT INTO object_ (object_type_code, state, location) VALUES ('$typeCode', 1, 'Scaffale B')");
    $objId2 = $pdo->lastInsertId();

    // C. Creiamo un Cliente
    $pdo->exec("INSERT INTO client (email, name, surname, state) VALUES ('link.test@user.com', 'Link', 'User', 1)");
    $clientId = $pdo->lastInsertId();

    // D. Creiamo un Prestito
    $pdo->exec("INSERT INTO loans (start_date, end_date, client_id, quantity, state) VALUES (NOW(), NOW(), $clientId, 2, 1)");
    $loanId = $pdo->lastInsertId();

    echo "🛠  Setup completato: Loan ID $loanId, Objects ID $objId1 & $objId2\n";


    // --- TEST 1: Associazione Oggetto al Prestito (Create) ---
    echo "Test 1: Associazione Oggetto (Create)... ";
    
    // Creiamo l'entità LoanedObject
    $link1 = new LoanedObject($typeCode, $objId1, $loanId, false);
    
    $repo->create($loanId, $link1);

    // Verifica immediata
    $check = $repo->getLoanedObject($loanId, $objId1);

    if ($check !== null && 
        $check->getObjectId() == $objId1 && 
        $check->getLoanId() == $loanId) {
        echo "✅ PASS\n";
    } else {
        echo "❌ FAIL (Associazione non trovata)\n";
    }

    // --- TEST 2: Recupero Lista Oggetti per Prestito (GetLoanedObjectsByLoanId) ---
    echo "Test 2: Lista oggetti nel prestito... ";
    
    // Aggiungiamo il secondo oggetto allo stesso prestito
    $link2 = new LoanedObject($typeCode, $objId2, $loanId, false);
    $repo->create($loanId, $link2);
    
    // Recuperiamo la lista
    $list = $repo->getLoanedObjectsByLoanId($loanId);
    
    if (count($list) === 2) {
        echo "✅ PASS (Trovati 2 oggetti associati)\n";
    } else {
        echo "❌ FAIL (Conteggio errato: " . count($list) . ")\n";
    }

    // --- TEST 3: Segnalazione Danno (MarkAsDamaged & IsDamaged) ---
    echo "Test 3: Segnalazione Danno... ";
    
    // Segniamo il primo oggetto come danneggiato
    $repo->markAsDamaged($loanId, $objId1);
    
    // Verifichiamo con isDamaged
    $isDamagedBool = $repo->isDamaged($loanId, $objId1);
    
    // Verifichiamo anche recuperando l'oggetto intero
    $damagedObj = $repo->getLoanedObject($loanId, $objId1);
    
    if ($isDamagedBool === true && $damagedObj->isDamaged() === true) {
        echo "✅ PASS\n";
    } else {
        echo "❌ FAIL (Status danneggiato non aggiornato)\n";
    }

    // --- TEST 4: Rimozione Associazione (Delete) ---
    echo "Test 4: Rimozione Oggetto dal Prestito... ";
    
    // Rimuoviamo il secondo oggetto ($objId2)
    $repo->delete($loanId, $objId2);
    
    // Cerchiamo di recuperarlo (dovrebbe essere null)
    $deletedObj = $repo->getLoanedObject($loanId, $objId2);
    
    // Controlliamo che la lista sia scesa a 1
    $listAfterDelete = $repo->getLoanedObjectsByLoanId($loanId);
    
    if ($deletedObj === null && count($listAfterDelete) === 1) {
        echo "✅ PASS\n";
    } else {
        echo "❌ FAIL (Oggetto ancora presente o lista non aggiornata)\n";
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