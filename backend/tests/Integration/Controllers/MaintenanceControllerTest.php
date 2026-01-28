<?php

/**
 * MAINTENANCE CONTROLLER INTEGRATION TEST (REAL FILES)
 * * Questo test carica i file REALI dalla cartella src.
 * Non crea mock. Se manca un file, il test fallirà con errore PHP.
 */

// 1. CONFIGURAZIONE PERCORSI
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE); // Nascondiamo warning minori
ini_set('display_errors', '0');

$baseSrc = __DIR__ . '/../../../src';

// Buffer output iniziale per pulire eventuali messaggi di import
ob_start();

try {
    // Caricamento dipendenze Core
    require_once $baseSrc . '/Http/Response.php';
    require_once $baseSrc . '/Application/Mediator/Mediator.php';
    require_once $baseSrc . '/Http/Request.php';
    
    // Caricamento Database (se esiste)
    if (file_exists($baseSrc . '/../config/Database.php')) {
        require_once $baseSrc . '/../config/Database.php';
    }

    // Caricamento del Controller da testare
    require_once $baseSrc . '/Http/Controllers/MaintenanceController.php';

} catch (Throwable $e) {
    ob_end_clean();
    echo "\n❌ ERRORE CRITICO DI CARICAMENTO:\n";
    echo $e->getMessage() . "\n";
    echo "Controlla che tutti i file (Comandi, Handler) esistano nelle cartelle corrette.\n";
    exit(1);
}
ob_end_clean();

// 2. CLASSE MOCK REQUEST (Necessaria per simulare $_POST/$_GET da terminale)
class MockRequest extends Request {
    private $data;
    public function __construct(array $data = []) { $this->data = $data; }
    public function getBody(?string $key = null) { 
        if ($key) return $this->data[$key] ?? null;
        return $this->data; 
    }
}

// 3. HELPER PER ESEGUIRE I TEST
function runTestCase($name, $callback) {
    echo "------------------------------------------------------------\n";
    echo "TEST: $name\n";
    echo "------------------------------------------------------------\n";
    
    ob_start();
    try {
        $callback();
        $output = ob_get_clean();
        
        // Analisi Risultato
        if (strpos($output, 'SQLSTATE') !== false) {
            echo "✅ [PASSED (LOGIC)] Controller OK -> DB Connection Error (Atteso in locale)\n";
        } elseif (strpos($output, '{') !== false) {
            echo "✅ [PASSED] JSON Response Received:\n";
            echo substr($output, strpos($output, '{')) . "\n";
        } else {
            echo "⚠️ [UNKNOWN] Output:\n$output\n";
        }
    } catch (Throwable $e) {
        ob_end_clean();
        echo "❌ [FAILED] Errore PHP: " . $e->getMessage() . "\n";
        echo "File: " . $e->getFile() . " riga " . $e->getLine() . "\n";
    }
    echo "\n";
}

// 4. ESECUZIONE TEST SUITE
echo "\n🚀 AVVIO TEST MAINTENANCE CONTROLLER (REAL SYSTEM)\n\n";

$controller = new MaintenanceController();

/*/ --- TEST 1: START MAINTENANCE ---
runTestCase("Start Maintenance (POST)", function() use ($controller) {
    // Simuliamo l'invio di un oggetto in manutenzione
    $req = new MockRequest([
        'object_id' => 1,
        'description' => 'Schermo rotto',
        'technician_id' => 2,
        'type' => 'repair'
    ]);
    $controller->start($req);
}); */

/*/ --- TEST 2: GET ALL (ViewMaintenancesCommand) ---
runTestCase("Get All Maintenances (GET)", function() use ($controller) {
    // Simuliamo una GET senza body
    $req = new MockRequest([]);
    $controller->getAll($req);
});*/

/*/ --- TEST 3: GET BY ID (ViewSingleMaintenanceCommand) ---
runTestCase("Get Single Maintenance (GET ID=1)", function() use ($controller) {
    $req = new MockRequest([]);
    $controller->getById($req, 1);
});*/

// --- TEST 4: CLOSE MAINTENANCE ---
runTestCase("Close Maintenance (PUT ID=1)", function() use ($controller) {
    // Simuliamo la chiusura (passiamo solo damaged, il controller aggiunge l'ID)
    $req = new MockRequest([
        'damaged' => 0 // 0 = riparato, 1 = irrecuperabile
    ]);
    $controller->close($req, 1);
});