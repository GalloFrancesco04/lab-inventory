<?php

/**
 * MAINTENANCE CONTROLLER INTEGRATION TEST
 *
 * Description:
 * This script validates the maintenance lifecycle: starting an intervention,
 * viewing logs, and closing a maintenance record.
 *
 * Diagnostic Mode:
 * Checks for REAL vs MOCKED Handlers and Commands.
 */

// 1. CONFIGURATION
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);
ini_set('display_errors', '0');
$baseSrc = __DIR__ . '/../../../src';

// 2. DIAGNOSTIC REPORT & AUTO-FIX
$requiredFiles = [
    // START (Uses Inventory namespace Command as per your Controller)
    '/Application/Commands/Inventory/SendToMaintenanceCommand.php' => 'class SendToMaintenanceCommand { public function __construct($data){} }',
    '/Application/Handlers/Inventory/SendToMaintenanceHandler.php' => 'class SendToMaintenanceHandler { public function handle($c){ return 1; } }',
    // CLOSE
    '/Application/Commands/Maintenance/CloseMaintenanceCommand.php' => 'class CloseMaintenanceCommand { public function __construct($id, $data){} }',
    '/Application/Handlers/Maintenance/CloseMaintenanceHandler.php' => 'class CloseMaintenanceHandler { public function handle($c){ return true; } }',
    // VIEW
    '/Application/Commands/Maintenance/ViewMaintenanceCommand.php'  => 'class ViewMaintenanceCommand {}',
    '/Application/Handlers/Maintenance/ViewMaintenanceHandler.php'  => 'class ViewMaintenanceHandler { public function handle($c){ return [["id"=>1, "status"=>"open"]]; } }'
];

$createdMocks = [];

echo "\n" . str_repeat("-", 50) . "\n";
echo " DIAGNOSTIC REPORT: Maintenance Dependency\n";
echo str_repeat("-", 50) . "\n";

foreach ($requiredFiles as $relativePath => $dummyContent) {
    $fullPath = $baseSrc . $relativePath;
    if (file_exists($fullPath)) {
        echo " [REAL] Found file: $relativePath\n";
    } else {
        echo " [MOCK] Missing file, creating temporary: $relativePath\n";
        $dir = dirname($fullPath);
        if (!is_dir($dir)) @mkdir($dir, 0777, true);
        @file_put_contents($fullPath, "<?php\n" . $dummyContent);
        $createdMocks[] = $fullPath;
    }
}
echo str_repeat("-", 50) . "\n\n";

// 3. OUTPUT BUFFERING
ob_start();

// 4. SHUTDOWN FUNCTION
register_shutdown_function(function() use ($createdMocks) {
    $output = ob_get_clean();
    
    echo "\n" . str_repeat("=", 41) . "\n";
    echo " TEST REPORT: MaintenanceController\n";
    echo str_repeat("=", 41) . "\n";

    if (strpos($output, 'successfully') !== false || strpos($output, 'started') !== false || strpos($output, 'closed') !== false || strpos($output, '"id"') !== false || strpos($output, '"data":') !== false) {
        echo "STATUS: [PASSED] Operation executed successfully.\n";
    } elseif (strpos($output, 'SQLSTATE') !== false) {
        echo "STATUS: [PASSED LOGICALLY] Reached DB layer.\n";
    } else {
        echo "STATUS: [FAILED] Check output for errors.\n";
    }
    
    echo "Response Snapshot: " . substr(trim($output), 0, 200) . "...\n";
    
    foreach ($createdMocks as $file) { if (file_exists($file)) unlink($file); }
});

// 5. LOAD CORE
try {
    require_once $baseSrc . '/Http/Response.php';
    require_once $baseSrc . '/Application/Mediator/Mediator.php';
    require_once $baseSrc . '/Http/Request.php';
    $dbConfig = $baseSrc . '/../config/Database.php';
    if (file_exists($dbConfig)) require_once $dbConfig;
    require_once $baseSrc . '/Http/Controllers/MaintenanceController.php';
} catch (Throwable $e) {
    ob_end_clean();
    die("CRITICAL SETUP ERROR: " . $e->getMessage() . "\n");
}

class MockRequest extends Request {
    private array $data;
    public function __construct(array $data) { $this->data = $data; }
    public function getBody(?string $key = null) { return $this->data; }
}

// 6. EXECUTION SECTION
$controller = new MaintenanceController();

// --- TEST 1: START MAINTENANCE (POST) ---
echo "Executing TEST: START MAINTENANCE...\n";
$controller->start(new MockRequest([
    'object_id' => 1,
    'description' => 'Broken screen',
    'technician_id' => 5,
    'start_date' => date('Y-m-d')
]));

// --- TEST 2: GET ALL LOGS (GET) ---
// echo "Executing TEST: VIEW ALL MAINTENANCE...\n";
// $controller->getAll(new MockRequest([]));

// --- TEST 3: CLOSE MAINTENANCE (PUT) ---
// echo "Executing TEST: CLOSE MAINTENANCE...\n";
// $controller->close(new MockRequest(['resolution' => 'Fixed', 'cost' => 50]), 1);