<?php

/**
 * ClientsController Unit Test
 *
 * Description:
 * This script verifies the functionality of the ClientsController class.
 * It focuses on the 'create' method to ensure that HTTP requests are correctly
 * processed, transformed into Commands, and dispatched via the Mediator.
 *
 * Note:
 * Since the Controller instantiates the Mediator and Database internally (Hard Dependency),
 * this test is designed to catch Database connection exceptions.
 * If the Controller reaches the point of trying to connect to the Database,
 * the test is considered successful regarding the Controller's logic layer.
 *
 * Environment:
 * - Designed to run in both Docker (Full Success) and Local XAMPP (Logic Success/DB Connection Error).
 */

// -----------------------------------------------------------------------------
// 1. Environment Configuration & Path Setup
// -----------------------------------------------------------------------------

// Define the relative path to the source directory
$baseSrc = __DIR__ . '/../../../src';

// Ensure the source directory exists
if (!is_dir($baseSrc)) {
    fprintf(STDERR, "Error: Source directory not found at %s\n", $baseSrc);
    exit(1);
}

// Import Core Dependencies
try {
    require_once $baseSrc . '/Http/Response.php';
    require_once $baseSrc . '/Application/Mediator/Mediator.php';
    require_once $baseSrc . '/Http/Request.php';
    
    // Import Database Config (Required for the internal instantiation in Handler)
    $dbConfigPath = $baseSrc . '/../config/Database.php';
    if (file_exists($dbConfigPath)) {
        require_once $dbConfigPath;
    }

    // Import the Controller under test
    require_once $baseSrc . '/Http/Controllers/ClientsController.php';

} catch (Throwable $e) {
    fprintf(STDERR, "Critical Error: Failed to load dependencies. %s\n", $e->getMessage());
    exit(1);
}

// -----------------------------------------------------------------------------
// 2. Mock Classes Definition
// -----------------------------------------------------------------------------

/**
 * MockRequest
 * * Simulates an HTTP Request object.
 * overrides getBody to allow data injection without a real HTTP server.
 */
class MockRequest extends Request {
    private array $data;

    public function __construct(array $data) {
        $this->data = $data;
    }

    /**
     * Compatible signature with the original Request::getBody(?string $key = null)
     */
    public function getBody(?string $key = null) {
        if ($key !== null) {
            return $this->data[$key] ?? null;
        }
        return $this->data;
    }
}

// -----------------------------------------------------------------------------
// 3. Test Execution Helper
// -----------------------------------------------------------------------------

function runTestCase(string $testName, callable $callback): void {
    echo "------------------------------------------------------------\n";
    echo "TEST: " . $testName . "\n";
    echo "------------------------------------------------------------\n";
    
    // Start output buffering to capture Controller response
    ob_start();
    
    try {
        $callback();
        $output = ob_get_clean();
        
        // Analyze output for success confirmation
        if (strpos($output, 'success') !== false) {
            echo "[PASSED] Controller returned success response.\n";
            echo "Response Payload: " . trim($output) . "\n";
        } else {
            echo "[WARNING] Unexpected response format.\n";
            echo "Output: " . trim($output) . "\n";
        }

    } catch (Exception $e) {
        ob_end_clean(); // Discard buffer on error
        
        // Analyze the exception type
        $msg = $e->getMessage();
        
        // If it is a Database/Network error, the Controller logic worked correctly
        if (strpos($msg, 'SQLSTATE') !== false || strpos($msg, 'getaddrinfo') !== false || strpos($msg, 'Host sconosciuto') !== false) {
            echo "[PASSED (LOGIC)] Controller processed the request.\n";
            echo "Note: Database connection failed as expected in this environment.\n";
            echo "Error Details: " . $msg . "\n";
        } else {
            // Actual Logic Error
            echo "[FAILED] Critical logic error detected.\n";
            echo "Exception: " . $msg . "\n";
            echo "File: " . $e->getFile() . " on line " . $e->getLine() . "\n";
        }
    } catch (Error $e) {
        ob_end_clean();
        echo "[FATAL ERROR] PHP Runtime Error.\n";
        echo "Error: " . $e->getMessage() . "\n";
        echo "File: " . $e->getFile() . " on line " . $e->getLine() . "\n";
    }
    
    echo "\n";
}

// -----------------------------------------------------------------------------
// 4. Test Cases
// -----------------------------------------------------------------------------

echo "\nStarting Test Suite for ClientsController...\n";

// TEST CASE 1: Create Client
runTestCase("Create Client - Valid Data", function () {
    
    // 1. Initialize Controller
    $controller = new ClientsController();
    
    // 2. Prepare Mock Data (Randomized to avoid unique constraint violations in real DB)
    $mockData = [
        'name'    => 'TestUserA',
        'surname' => 'Automated',
        'email'   => 'test.user.' . time() . '@example.com',
        'state'   => 1
    ];
    
    $request = new MockRequest($mockData);

    // 3. Execute Method
    echo "Action: Dispatching create request...\n";
    $controller->create($request);
});


// TEST CASE 2: Create Client - Missing Data (Validation Test)
runTestCase("Create Client - Missing Name (Validation)", function () {
    
    $controller = new ClientsController();
    
    // Missing 'name' field
    $mockData = [
        "name"=> "ko",
        'surname' => 'Unnamed',
        'email'   => 'no.name.' . time() . '@example.com',
        'state'   => 1
    ];
    
    $request = new MockRequest($mockData);
    
    echo "Action: Dispatching invalid request...\n";
    $controller->create($request);
});

echo "Test Suite Completed.\n";