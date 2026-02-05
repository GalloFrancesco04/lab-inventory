<?php

// PERCORSO CORRETTO (Senza 'src')
// Risaliamo di 3 livelli: Repositories -> Integration -> tests -> ROOT
require_once __DIR__ . '/../../../Infrastructure/Persistence/Repositories/ClientRepository.php';

echo "===== Inizio Test Integrazione: ClientRepository =====\n";

// --- CONFIGURAZIONE DATABASE ---
// ATTENZIONE: Inserisci qui i dati del tuo container Docker
$host = '127.0.0.1'; // Usa '127.0.0.1' se lanci il test dal Mac, oppure 'db' se lo lanci da dentro Docker
$dbname = 'inventory'; 
$user = 'root';    
$pass = 'root'; // <--- Controlla se la password è corretta (a volte è vuota o diversa)
$port = '3306';

try {
    // 1. Connessione al Database
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    // 2. AVVIO TRANSAZIONE (Il trucco per non sporcare il DB)
    $pdo->beginTransaction();
    echo "🔌 Connessione DB riuscita. Transazione avviata (i dati verranno rimossi alla fine).\n";

    $repo = new ClientRepository($pdo);
    
    // Dati di prova univoci
    $timestamp = time();
    $email = "test.manuale.$timestamp@example.com"; 
    $nome = 'MarioTest';
    $cognome = 'RossiTest';

    // --- TEST 1: Creazione (Create) ---
    echo "Test 1: Creazione Client... ";
    
    // Istanziamo l'entità (Assicurati che i parametri costruttore siano in quest'ordine: email, nome, cognome, stato)
    $client = new \Application\Domain\Entities\Client($email, $nome, $cognome, 1);
    
    $repo->create($client);
    $newId = $client->getId();

    if ($newId > 0) {
        echo "✅ PASS (ID creato: $newId)\n";
    } else {
        throw new Exception("Errore: L'ID non è stato generato dopo la create().");
    }

    // --- TEST 2: Lettura (FindById) ---
    echo "Test 2: Ricerca per ID... ";
    $foundClient = $repo->findById($newId);
    
    // Verifichiamo che l'abbia trovato e che l'email corrisponda
    if ($foundClient !== null && $foundClient->getEmail() === $email) {
        echo "✅ PASS\n";
    } else {
        echo "❌ FAIL (Client non trovato o dati non corrispondenti)\n";
    }

    // --- TEST 3: Aggiornamento (Update) ---
    echo "Test 3: Aggiornamento dati... ";
    $client->setName("LuigiUpdated");
    $repo->update($client);
    
    // Rileggiamo dal DB per essere sicuri che la modifica sia stata salvata
    $updatedClient = $repo->findById($newId);
    
    if ($updatedClient->getName() === "LuigiUpdated") {
        echo "✅ PASS\n";
    } else {
        echo "❌ FAIL (Il nome nel DB è ancora: " . $updatedClient->getName() . ")\n";
    }

    // --- TEST 4: Blocco Utente (Block) ---
    echo "Test 4: Blocco utente (state = 0)... ";
    $repo->blockClient($newId);
    
    $blockedClient = $repo->findById($newId);
    
    // Nota: A volte PDO restituisce gli interi come stringhe o boolean come int, quindi facciamo un cast a (int)
    if ((int)$blockedClient->getState() === 0) { 
        echo "✅ PASS\n";
    } else {
        echo "❌ FAIL (Stato attuale: " . $blockedClient->getState() . ")\n";
    }

} catch (PDOException $e) {
    echo "\n⚠️ ERRORE CONNESSIONE DB: " . $e->getMessage() . "\n";
    echo "Suggerimento: Verifica user/password in cima al file e che il container DB sia acceso.\n";
} catch (Exception $e) {
    echo "\n⚠️ ERRORE LOGICO: " . $e->getMessage() . "\n";
} finally {
    // 3. ROLLBACK FINALE (Annulla tutto)
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
        echo "\n🧹 Rollback eseguito con successo: Nessuna traccia lasciata nel DB.\n";
    }
}

echo "===== Fine Test =====\n";