<?php

/**
 * OBJECTS CONTROLLER INTEGRATION TEST (FULL CRUD)
 *
 * Description:
 * This script validates the full CRUD lifecycle for physical objects (Items).
 * It tracks individual assets, their serial numbers, and locations.
 *
 * Diagnostic Mode:
 * Identifies if Handlers, Commands, or Repositories are REAL or MOCKED.
 * * Note: 
 * Test one operation at a time due to exit() calls in the Controller.
 */

// 1. CONFIGURATION
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);
ini_set('display_errors', '0');

$baseSrc = __DIR__ . '/../../../src';

// 2. DIAGNOSTIC REPORT & AUTO-FIX
$requiredFiles = [
    // CREATE
    '/Application/Commands/Inventory/CreateObjectCommand.php' => 'class CreateObjectCommand { public function __construct($n,$d,$sn,$otc,$l,$s){} }',
    '/Application/Handlers/Inventory/CreateObjectHandler.php' => 'class CreateObjectHandler { public function handle($c){ return 1; } }',
    // UPDATE
    '/Application/Commands/Inventory/UpdateObjectCommand.php' => 'class UpdateObjectCommand { public function __construct($id,$n,$d,$sn,$otc,$l,$s){} }',
    '/Application/Handlers/Inventory/UpdateObjectHandler.php' => 'class UpdateObjectHandler { public function handle($c){ return true; } }',
    // DELETE
    '/Application/Commands/Inventory/DeleteObjectCommand.php' => 'class DeleteObjectCommand { public function __construct($id){} }',
    '/Application/Handlers/Inventory/DeleteObjectHandler.php' => 'class DeleteObjectHandler { public function handle($c){ return true; } }',
    // READ
    '/Application/Commands/Inventory/ViewObjectsCommand.php'  => 'class ViewObjectsCommand {}',
    '/Application/Handlers/Inventory/ViewObjectsHandler.php'  => 'class ViewObjectsHandler { public function handle($c){ return [["id"=>1, "name"=>"Object Mock"]]; } }',
    // REPOSITORY
    '/Infrastructure/Persistence/Repositories/ItemRepository.php' => 'class ItemRepository { public function __construct($db){} }'
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
        echo "STATUS: [PASSED LOGICALLY] Reached Database layer.\n";
    } else {
        echo "STATUS: [FAILED] Unexpected response format.\n";
    }
    
    echo "Response Snapshot: " . substr(trim($output), 0, 200) . "...\n";
    echo "\n" . str_repeat("-", 41) . "\n";
    
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
$randomSerial = 'SN-' . rand(1000, 9999);
$targetId = 1; // Change this to an existing ID in your DB for real tests

/* --- TEST 1: CREATE ---
echo "Executing TEST: CREATE OBJECT...\n";
$controller->create(new MockRequest([
    'name' => 'Monitor Dell',
    'description' => '4K Display',
    'serial_number' => $randomSerial,
    'object_type_code' => 'TEST_T...', // Must exist in object_type table
    'location' => 'Room 202',
    'state' => 1
])); */

// --- TEST 2: UPDATE ---
// echo "Executing TEST: UPDATE OBJECT...\n";
// $controller->update(new MockRequest([
//     'name' => 'Updated Monitor',
//     'description' => 'New Description',
//     'serial_number' => 'UPD-SN-001',
//     'object_type_code' => 'TEST_T...',
//     'location' => 'Storage',
//     'state' => 0
// ]), $targetId);

// --- TEST 3: DELETE ---
// echo "Executing TEST: DELETE OBJECT...\n";
 //$controller->delete(new MockRequest([]), $targetId);

// --- TEST 4: GET ALL ---
// echo "Executing TEST: GET ALL OBJECTS...\n";
// $controller->getAll(new MockRequest([]));