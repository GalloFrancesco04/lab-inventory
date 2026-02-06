<?php

require_once __DIR__ . '/../../../src/Infrastructure/Persistence/Repositories/UserRepository.php';
require_once __DIR__ . '/../../../src/Domain/Entities/User.php';

echo "===== Inizio Test Integrazione: UserRepository =====\n";

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

    $repo = new UserRepository($pdo);
    
    // --- TEST 1: Creazione Admin (Create) ---
    echo "Test 1: Creazione Utente Admin... ";
    
    // Ruolo 1 = Admin, Ruolo 2 = Tecnico
    $adminEmail = "admin.test@lab.it";
    $adminUser = new User(null, $adminEmail, "password_segreta_hash", 1);
    
    $repo->create($adminUser);
    $adminId = $adminUser->getId();

    if ($adminId > 0) {
        echo "✅ PASS (Admin ID creato: $adminId)\n";
    } else {
        throw new Exception("ID Admin non generato.");
    }

    // --- TEST 2: Ricerca Generica (FindUserById & Email) ---
    echo "Test 2: Ricerca Generica (ById e ByEmail)... ";
    
    $foundById = $repo->findUserById($adminId);
    $foundByEmail = $repo->findUserByEmail($adminEmail);
    
    if ($foundById !== null && 
        $foundByEmail !== null && 
        $foundById->getEmail() === $adminEmail) {
        echo "✅ PASS\n";
    } else {
        echo "❌ FAIL (Utente non trovato correttamente)\n";
    }

    // --- TEST 3: Verifica Ruoli (Admin vs Tecnico) ---
    echo "Test 3: Verifica metodi specifici per ruolo... ";
    
    // Cerchiamo l'admin con findAdminById -> Deve trovarlo
    $isAdmin = $repo->findAdminById($adminId);
    
    // Cerchiamo l'admin con findTechnicianById -> NON deve trovarlo (è un admin, non un tecnico!)
    $isTechnician = $repo->findTechnicianById($adminId);
    
    if ($isAdmin !== null && $isTechnician === null) {
        echo "✅ PASS (Distinzione ruoli corretta)\n";
    } else {
        echo "❌ FAIL (Admin trovato come tecnico o non trovato come admin)\n";
    }

    // --- TEST 4: Creazione e Lista Tecnici (GetAllTechnicians) ---
    echo "Test 4: Lista Tecnici... ";
    
    // Creiamo un tecnico 
    $techUser = new User(null, "tecnico.test@lab.it", "pass123", 2);
    $repo->create($techUser);
    
    // Recuperiamo tutti i tecnici
    $allTechs = $repo->getAllTechnicians();
    
    // Verifichiamo che il nostro tecnico sia nella lista
    $foundTech = false;
    foreach ($allTechs as $tech) {
        if ($tech->getEmail() === "tecnico.test@lab.it") {
            $foundTech = true;
            break;
        }
    }
    
    // Verifichiamo anche che l'Admin NON sia nella lista dei tecnici
    $adminInList = false;
    foreach ($allTechs as $tech) {
        if ($tech->getId() === $adminId) {
            $adminInList = true;
            break;
        }
    }

    if ($foundTech && !$adminInList) {
        echo "✅ PASS (Tecnico trovato nella lista e Admin escluso)\n";
    } else {
        echo "❌ FAIL (Lista tecnici non corretta)\n";
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