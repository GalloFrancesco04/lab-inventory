<?php

require_once __DIR__ . '/../../../src/Infrastructure/Persistence/Repositories/ClientRepository.php';
require_once __DIR__ . '/../../../src/Domain/Entities/Client.php';


echo "===== Inizio Test Integrazione: ClientRepository =====\n";

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

    // 2. AVVIO TRANSAZIONE (per non sporcare il DB)
    $pdo->beginTransaction();
    echo "🔌 Connessione DB riuscita. Transazione avviata (i dati verranno rimossi alla fine).\n";

    $repo = new ClientRepository($pdo);
    
    // Dati di prova univoci
    $email = "MR@test.it";
    $nome = "Mario";
    $cognome = "Rossi";

    // --- TEST 1: Creazione (Create) ---
    echo "Test 1: Creazione Client... ";
    
    // Istanziamo l'entità
    $client = new Client(null,$email, $nome, $cognome);
    
    $repo->create($client);
    $newId = $client->getId();

    if ($newId > 0) {
        echo "✅ PASS (ID creato: $newId)\n";
    } else {
        throw new Exception("Errore: L'ID non è stato generato dopo la create().");
    }

    // echo "client ID = " . $client->getId() . ", email = " . $client->getEmail() . ", name = " . $client->getName() . ", surname = " . $client->getSurname() . ", state = " . $client->getState() . "\n";

    // --- TEST 2: Lettura (FindById) ---
    echo "Test 2: Ricerca per ID... ";
    $foundClient = $repo->findById($newId);
    
    // Verifichiamo che l'abbia trovato e che l'email corrisponda
    if ($foundClient !== null && $foundClient->getEmail() === $email) {
        echo "✅ PASS\n";
    } else {
        echo "❌ FAIL (Client non trovato o dati non corrispondenti)\n";
    }

    // echo "client ID = " . $foundClient->getId() . ", email = " . $foundClient->getEmail() . ", name = " . $foundClient->getName() . ", surname = " . $foundClient->getSurname() . ", state = " . $foundClient->getState() . "\n";
    
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

    // echo "client ID = " . $updatedClient->getId() . ", email = " . $updatedClient->getEmail() . ", name = " . $updatedClient->getName() . ", surname = " . $updatedClient->getSurname() . ", state = " . $updatedClient->getState() . "\n";

    // --- TEST 4: Blocco Utente (Block) ---
    echo "Test 4: Blocco utente (state = 0)... ";
    $repo->blockClient($newId);
    
    $blockedClient = $repo->findById($newId);
    

    if ((int)$blockedClient->getState() === 0) { 
        echo "✅ PASS\n";
    } else {
        echo "❌ FAIL (Stato attuale: " . $blockedClient->getState() . ")\n";
    }

    // echo "client ID = " . $blockedClient->getId() . ", email = " . $blockedClient->getEmail() . ", name = " . $blockedClient->getName() . ", surname = " . $blockedClient->getSurname() . ", state = " . $blockedClient->getState() . "\n";

} catch (PDOException $e) {
    echo "\n⚠️ ERRORE CONNESSIONE DB: " . $e->getMessage() . "\n";
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