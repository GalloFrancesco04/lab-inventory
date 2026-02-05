<?php

require_once __DIR__ . '/../../../src/Infrastructure/Persistence/Repositories/LoanRepository.php';
require_once __DIR__ . '/../../../src/Domain/Entities/Loan.php';

echo "===== Inizio Test Integrazione: LoanRepository (Prestiti) =====\n";

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

    $repo = new LoanRepository($pdo);

    // --- SETUP DIPENDENZE ---
    // Un prestito deve appartenere a un CLIENTE esistente.
    // Creiamo un cliente al volo usando SQL grezzo (più veloce per il setup)
    $clientEmail = "test.loan.user@uni.it";
    $pdo->exec("INSERT INTO client (email, name, surname, state) VALUES ('$clientEmail', 'Test', 'User', 1)");
    $clientId = $pdo->lastInsertId();
    echo "🛠  Setup: Creato Client ID $clientId\n";

    // --- TEST 1: Creazione Prestito (Create) ---
    echo "Test 1: Creazione Prestito... ";
    
    $startDate = new DateTime('now');
    $endDate = new DateTime('+7 days'); 
    
    // Costruttore: id, startDate, endDate, returnDate, clientId, notes, quantity, state
    $newLoan = new Loan(
        null, 
        $startDate, 
        $endDate, 
        null, // returnDate è null all'inizio
        $clientId, 
        "Note di prova prestito", 
        1, 
        true // state = true (Attivo)
    );

    $repo->create($newLoan);
    $loanId = $newLoan->getId();

    if ($loanId > 0) {
        echo "✅ PASS (Loan ID creato: $loanId)\n";
    } else {
        throw new Exception("Errore: ID prestito non generato.");
    }

    // --- TEST 2: Lettura per ID (GetLoanById) ---
    echo "Test 2: Lettura per ID... ";
    
    $foundLoan = $repo->getLoanById($loanId);
    
    echo "Trovato Loan ID: " . $foundLoan->getId() . ", Client ID: " . $foundLoan->getClientId() . "\n";

    if ($foundLoan !== null && 
        $foundLoan->getClientId() == $clientId) {
        echo "✅ PASS\n";
    } else {
        echo "❌ FAIL (Dati non corrispondenti)\n";
    }

    // --- TEST 3: Aggiornamento Note (UpdateNotes) ---
    echo "Test 3: Aggiornamento Note... ";
    
    $repo->updateNotes($loanId, "Note Aggiornate");
    $updatedLoan = $repo->getLoanById($loanId);
    
    if ($updatedLoan->getNotes() === "Note Aggiornate") {
        echo "✅ PASS\n";
    } else {
        echo "❌ FAIL (Note lette: " . $updatedLoan->getNotes() . ")\n";
    }

    // --- TEST 4: Chiusura Prestito (CloseLoan) ---
    echo "Test 4: Chiusura Prestito (Restituzione)... ";
    
    $returnDate = new DateTime('now');
    $repo->closeLoan($loanId, $returnDate);
    
    $closedLoan = $repo->getLoanById($loanId);
    
    // Verifiche: Stato deve essere false (0) e ReturnDate deve esistere
    if ($closedLoan->getState() === false && $closedLoan->getReturnDate() !== null) {
        echo "✅ PASS (Stato chiuso e data restituzione settata)\n";
    } else {
        echo "❌ FAIL (Stato: " . ($closedLoan->getState() ? 'Attivo' : 'Chiuso') . ")\n";
    }

    // --- TEST 5: Storico Prestiti Oggetto (GetLoansHistoryOfObject) ---
    echo "Test 5: Storico prestiti per oggetto... ";
    
    // Per testare questo, dobbiamo simulare che questo prestito contenga un oggetto.
    // 1. Creiamo un Tipo Oggetto
    $pdo->exec("INSERT INTO objecttype (code, name, quantity) VALUES ('LOAN-TEST-01', 'Test Item', 5)");
    // 2. Creiamo un Oggetto Fisico
    $pdo->exec("INSERT INTO object_ (object_type_code, state, location) VALUES ('LOAN-TEST-01', 1, 'Lab')");
    $objectId = $pdo->lastInsertId();
    // 3. Colleghiamo manualmente il Prestito all'Oggetto (Tabella loanedobjects)
    // Nota: Il repository LoanRepository non ha un metodo 'addLoanedObject' (forse è gestito altrove),
    // quindi lo inseriamo manualmente per testare la funzione di LETTURA 'getLoansHistoryOfObject'.
    $pdo->exec("INSERT INTO loanedobjects (object_type_code, object_id, loan_id, damaged) VALUES ('LOAN-TEST-01', $objectId, $loanId, 0)");
    
    // Ora testiamo il metodo del repository
    $history = $repo->getLoansHistoryOfObject($objectId);
    
    $foundInHistory = false;
    foreach ($history as $l) {
        if ($l->getId() === $loanId) {
            $foundInHistory = true;
            break;
        }
    }

    if ($foundInHistory) {
        echo "✅ PASS (Prestito trovato nello storico dell'oggetto)\n";
    } else {
        echo "❌ FAIL (Storico vuoto o prestito mancante)\n";
    }

    // --- TEST 6: Prestiti Attivi (GetActiveLoans) ---
    echo "Test 6: Filtro Prestiti Attivi... ";
    
    // Il prestito precedente ($loanId) è stato chiuso nel Test 4.
    // Creiamone uno nuovo che rimanga attivo.
    $activeLoan = new Loan(null, new DateTime(), new DateTime('+1 day'), null, $clientId, "Active", 1, true);
    $repo->create($activeLoan);
    
    $activeList = $repo->getActiveLoans();
    
    $foundActive = false;
    foreach ($activeList as $l) {
        if ($l->getId() === $activeLoan->getId()) {
            $foundActive = true;
            break;
        }
    }
    
    // Verifichiamo anche che quello chiuso NON ci sia
    $foundClosed = false;
    foreach ($activeList as $l) {
        if ($l->getId() === $loanId) {
            $foundClosed = true;
            break;
        }
    }

    if ($foundActive && !$foundClosed) {
        echo "✅ PASS (Trovato solo il prestito attivo)\n";
    } else {
        echo "❌ FAIL (Lista attivi errata)\n";
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