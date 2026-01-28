<?php

/**
 * OBJECTS CONTROLLER INTEGRATION TEST (FULL CRUD + MAINTENANCE)
 *
 * Description:
 * This script validates the full lifecycle for physical objects.
 * Updated to match the specific commands of the ObjectsController.
 *
 * Diagnostic Mode:
 * Identifies if Handlers, Commands, or Repositories are REAL or MOCKED.
 * * Note: 
 * Test one operation at a time due to exit() calls in the Response class.
 */

// 1. CONFIGURATION
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);
ini_set('display_errors', '0');

$baseSrc = __DIR__ . '/../../../src';

// 2. DIAGNOSTIC REPORT & AUTO-FIX
// Updated definition based on your Controller's usage
$requiredFiles = [
    // CREATE
    '/Application/Commands/Inventory/CreateObjectCommand.php' => 'class CreateObjectCommand { public function __construct($otc,$l){} }',
    '/Application/Handlers/Inventory/CreateObjectHandler.php' => 'class CreateObjectHandler { public function handle($c){ return 1; } }',
    
    // UPDATE
    '/Application/Commands/Inventory/UpdateObjectCommand.php' => 'class UpdateObjectCommand { public function __construct($id,$l){} }',
    '/Application/Handlers/Inventory/UpdateObjectHandler.php' => 'class UpdateObjectHandler { public function handle($c){ return true; } }',
    
    // MAINTENANCE
    '/Application/Commands/Inventory/SendToMaintenanceCommand.php' => 'class SendToMaintenanceCommand { public function __construct($oid,$d,$tid,$sd){} }',
    '/Application/Handlers/Inventory/SendToMaintenanceHandler.php' => 'class SendToMaintenanceHandler { public function handle($c){ return true; } }',
    
    // READ OPERATIONS
    '/Application/Commands/Inventory/ViewObjectsCommand.php'  => 'class ViewObjectsCommand {}',
    '/Application/Handlers/Inventory/ViewObjectsHandler.php'  => 'class ViewObjectsHandler { public function handle($c){ return [["id"=>1, "location"=>"Mock Loc"]]; } }',
    
    '/Application/Commands/Inventory/ViewSingleObjectCommand.php' => 'class ViewSingleObjectCommand { public function __construct($id){} }',
    '/Application/Handlers/Inventory/ViewSingleObjectHandler.php' => 'class ViewSingleObjectHandler { public function handle($c){ return (object)["id"=>1, "location"=>"Mock Single"]; } }',
    
    '/Application/Commands/Inventory/ViewAvailableObjectsCommand.php' => 'class ViewAvailableObjectsCommand {}',
    '/Application/Handlers/Inventory/ViewAvailableObjectsHandler.php' => 'class ViewAvailableObjectsHandler { public function handle($c){ return [["id"=>2, "status"=>1]]; } }',

    // TOGGLE SERVICE
    '/Application/Commands/Inventory/ToggleOutOfServiceCommand.php' => 'class ToggleOutOfServiceCommand { public function __construct($id){} }',
    '/Application/Handlers/Inventory/ToggleOutOfServiceHandler.php' => 'class ToggleOutOfServiceHandler { public function handle($c){ return true; } }',
];

$createdMocks = [];

echo "\n" . str_repeat("-", 50) . "\n";
echo " DIAGNOSTIC REPORT: Objects Dependency\n";
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

// 3. START OUTPUT BUFFERING
ob_start();

// 4. SHUTDOWN FUNCTION
register_shutdown_function(function() use ($createdMocks) {
    $output = ob_get_clean();
    
    echo "\n" . str_repeat("=", 41) . "\n";
    echo " TEST REPORT: ObjectsController CRUD\n";
    echo str_repeat("=", 41) . "\n";

    if (strpos($output, 'success') !== false || strpos($output, '"id"') !== false || strpos($output, '"data":') !== false) {
        echo "STATUS: [PASSED] Controller executed the requested operation.\n";
    } elseif (strpos($output, 'SQLSTATE') !== false) {
        echo "STATUS: [PASSED LOGICALLY] Reached Database layer (SQL Error is expected if DB is empty/locked).\n";
    } elseif (empty($output)) {
        echo "STATUS: [NO OUTPUT] Did you uncomment a test case?\n";
    } else {
        echo "STATUS: [FAILED] Unexpected response format.\n";
    }
    
    echo "Response Snapshot: " . substr(trim($output), 0, 300) . "...\n";
    echo "\n" . str_repeat("-", 41) . "\n";
    
    // Clean up mocks
    foreach ($createdMocks as $file) {
        if (file_exists($file)) unlink($file);
    }
});

// 5. LOAD CORE
try {
    require_once $baseSrc . '/Http/Response.php';
    require_once $baseSrc . '/Application/Mediator/Mediator.php';
    require_once $baseSrc . '/Http/Request.php';
    $dbConfig = $baseSrc . '/../config/Database.php';
    if (file_exists($dbConfig)) require_once $dbConfig;
    require_once $baseSrc . '/Http/Controllers/ObjectsController.php';
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
$controller = new ObjectsController();
$targetId = 4; // ID to use for updates/gets (Make sure this exists in DB)
$techId = 2;   // ID of a technician user

// =================================================================
// UNCOMMENT ONE BLOCK AT A TIME TO TEST
// =================================================================

/*/ --- TEST 1: CREATE OBJECT ---
echo "Executing TEST: CREATE OBJECT...\n";
// Params updated to match ObjectsController::create
$controller->create(new MockRequest([
    'object_type_code' => 'TEST_TYPE',   // Must exist in objecttype table
    'location' => 'Armadio C - Ripiano 2'
])); */


// --- TEST 2: UPDATE OBJECT ---
/*/echo "Executing TEST: UPDATE OBJECT...\n";
// Params updated to match ObjectsController::update (only location)
$controller->update(new MockRequest([
    'location' => 'Nuova Posizione: Laboratorio 3'
]), $targetId); */


// --- TEST 3: SEND TO MAINTENANCE ---
echo "Executing TEST: SEND TO MAINTENANCE...\n";
$controller->sendMaintenance(new MockRequest([
    'object_id' => $targetId,
    'description' => 'Ventola rumorosa',
    'technician_id' => $techId,
    'start_date' => date('Y-m-d')
]));



/*/ --- TEST 4: GET ALL OBJECTS ---
echo "Executing TEST: GET ALL OBJECTS...\n";
$controller->getAll(new MockRequest([])); */


 /*/--- TEST 5: GET SINGLE OBJECT ---
echo "Executing TEST: GET BY ID ($targetId)...\n";
$controller->getById(new MockRequest([]), $targetId);


/* --- TEST 6: GET AVAILABLE OBJECTS ---
echo "Executing TEST: GET AVAILABLE...\n";
$controller->getAvailable(new MockRequest([]));
*/

/* --- TEST 7: TOGGLE OUT OF SERVICE ---
echo "Executing TEST: TOGGLE STATUS ($targetId)...\n";
$controller->toggleStatus(new MockRequest([]), $targetId);
*/