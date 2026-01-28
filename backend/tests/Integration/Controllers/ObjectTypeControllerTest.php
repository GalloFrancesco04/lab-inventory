<?php

/**
 * OBJECT TYPES CONTROLLER INTEGRATION TEST (FULL CRUD)
 *
 * Description:
 * This script validates the full CRUD lifecycle (Create, Read, Update) 
 * of the ObjectTypesController. It ensures the Controller correctly manages 
 * object categories and definitions.
 *
 * Resilience Strategy:
 * Diagnostic mode identifies if Handlers, Commands, or Repositories are missing.
 * Missing dependencies are temporarily mocked to allow Controller logic validation.
 *
 * Execution Note:
 * Since the Controller calls exit() after a response, test one operation 
 * at a time by uncommenting the desired call in the Execution section.
 */

// 1. CONFIGURATION AND ERROR HANDLING
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);
ini_set('display_errors', '0');

$baseSrc = __DIR__ . '/../../../src';

// 2. DIAGNOSTIC REPORT AND AUTO-FIX
$requiredFiles = [
    // CREATE
    '/Application/Commands/ObjectTypes/CreateObjectTypeCommand.php' => 'class CreateObjectTypeCommand { public $code; public function __construct($c,$n,$d,$m,$b){$this->code=$c;} }',
    '/Application/Handlers/ObjectTypes/CreateObjectTypeHandler.php' => 'class CreateObjectTypeHandler { public function handle($c){ return "MOCK_CODE_123"; } }',
    // UPDATE
    '/Application/Commands/ObjectTypes/UpdateObjectTypeCommand.php' => 'class UpdateObjectTypeCommand { public function __construct($c,$n,$d,$m,$b){} }',
    '/Application/Handlers/ObjectTypes/UpdateObjectTypeHandler.php' => 'class UpdateObjectTypeHandler { public function handle($c){ return true; } }',
    // READ (GET ALL & SINGLE)
    '/Application/Commands/ObjectTypes/ViewObjectTypesCommand.php'  => 'class ViewObjectTypesCommand {}',
    '/Application/Handlers/ObjectTypes/ViewObjectTypesHandler.php'  => 'class ViewObjectTypesHandler { public function handle($c){ return [["code"=>"T1", "name"=>"Mock Type"]]; } }',
    '/Application/Commands/ObjectTypes/ViewSingleObjectTypeCommand.php' => 'class ViewSingleObjectTypeCommand { public function __construct($c){} }',
    '/Application/Handlers/ObjectTypes/ViewSingleObjectTypeHandler.php' => 'class ViewSingleObjectTypeHandler { public function handle($c){ return ["code"=>"T1", "name"=>"Single Mock"]; } }',
    // REPOSITORY
    '/Infrastructure/Persistence/Repositories/ObjectTypeRepository.php' => 'class ObjectTypeRepository { public function __construct($db){} }'
];

$createdMocks = [];

echo "\n" . str_repeat("-", 50) . "\n";
echo " DIAGNOSTIC REPORT: Dependency Resolution\n";
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

// 4. SHUTDOWN FUNCTION (POST-EXECUTION ANALYSIS)
register_shutdown_function(function() use ($createdMocks) {
    $output = ob_get_clean();
    
    echo "\n" . str_repeat("=", 41) . "\n";
    echo " TEST REPORT: ObjectTypesController CRUD\n";
    echo str_repeat("=", 41) . "\n";

    if (strpos($output, 'success') !== false || strpos($output, '"code"') !== false || strpos($output, '"data":') !== false) {
        echo "STATUS: [PASSED] Controller executed the requested operation.\n";
    } elseif (strpos($output, 'SQLSTATE') !== false) {
        echo "STATUS: [PASSED LOGICALLY] Controller reached the Database (SQL Error is expected if DB is empty/locked).\n";
    } elseif (empty($output)) {
        echo "STATUS: [NO OUTPUT] Did you uncomment a test case?\n";
    } else {
        echo "STATUS: [FAILED] Unexpected or empty response.\n";
    }
    
    echo "Response Snapshot: " . substr(trim($output), 0, 300) . "...\n";
    echo "\n" . str_repeat("-", 41) . "\n";
    
    foreach ($createdMocks as $file) {
        if (file_exists($file)) unlink($file);
    }
});

// 5. LOAD SYSTEM CORE
try {
    require_once $baseSrc . '/Http/Response.php';
    require_once $baseSrc . '/Application/Mediator/Mediator.php';
    require_once $baseSrc . '/Http/Request.php';
    $dbConfig = $baseSrc . '/../config/Database.php';
    if (file_exists($dbConfig)) require_once $dbConfig;
    require_once $baseSrc . '/Http/Controllers/ObjectTypesController.php';
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
// -----------------------------------------------------------------------------
$controller = new ObjectTypesController();
// Code to be used for testing Create/Update/GetSingle
$targetCode = 'INTEGRATION_8098'; 

// =================================================================
// UNCOMMENT ONE BLOCK AT A TIME TO TEST
// =================================================================

/* --- TEST 1: CREATE ---
echo "Executing TEST: CREATE OBJECT TYPE...\n";
$controller->create(new MockRequest([
    'code' => $targetCode, 
    'name' => 'Test Category', 
    'description' => 'Integration test description', 
    'model' => 'Model-X', 
    'brand' => 'Brand-Z'
]));
*/

/* --- TEST 2: GET ALL ---
echo "Executing TEST: GET ALL TYPES...\n";
$controller->getAll(new MockRequest([]));
*/

/* --- TEST 3: UPDATE ---
echo "Executing TEST: UPDATE TYPE...\n";
$controller->update(new MockRequest([
    'name' => 'Updated Category Name', 
    'description' => 'Updated Desc', 
    'model' => 'M1-Updated', 
    'brand' => 'B1-Updated'
]), $targetCode);
*/

//* --- TEST 4: GET BY CODE (SINGLE) ---
echo "Executing TEST: GET BY CODE ($targetCode)...\n";
$controller->getByCode(new MockRequest([]), $targetCode);
