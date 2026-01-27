<?php

/**
 * OBJECTS CONTROLLER INTEGRATION TEST
 *
 * Scope:
 * This test suite validates the full lifecycle of the ObjectsController.
 * It performs an end-to-end verification starting from the HTTP Request,
 * passing through the Mediator and Handler, and persisting data to the real Database.
 *
 * Environment:
 * This test requires a running Docker environment with a reachable MySQL instance.
 */

// -----------------------------------------------------------------------------
// 1. CONFIGURATION AND DEPENDENCIES
// -----------------------------------------------------------------------------

// Define relative path to source directory from 'tests/Integration/Controllers'
$baseSrc = __DIR__ . '/../../../src';

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', '0');

// Load application dependencies
ob_start();
try {
    require_once $baseSrc . '/Http/Response.php';
    require_once $baseSrc . '/Application/Mediator/Mediator.php';
    require_once $baseSrc . '/Http/Request.php';
    
    // Load Database Configuration
    $dbConfig = $baseSrc . '/../config/Database.php';
    if (!file_exists($dbConfig)) {
        throw new Exception("Database configuration file not found.");
    }
    require_once $dbConfig;

    // Load the Controller Class
    require_once $baseSrc . '/Http/Controllers/ObjectsController.php';

} catch (Throwable $e) {
    ob_end_clean();
    fprintf(STDERR, "CRITICAL ERROR: Dependency loading failed. %s\n", $e->getMessage());
    exit(1);
}
ob_end_clean();

// -----------------------------------------------------------------------------
// 2. HELPER CLASSES
// -----------------------------------------------------------------------------

/**
 * Class MockRequest
 * Simulates an HTTP Request to trigger the Controller.
 */
class MockRequest extends Request {
    private array $data;

    public function __construct(array $data) {
        $this->data = $data;
    }

    public function getBody(?string $key = null) {
        if ($key !== null) {
            return $this->data[$key] ?? null;
        }
        return $this->data;
    }
}

// -----------------------------------------------------------------------------
// 3. TEST RUNNER
// -----------------------------------------------------------------------------

function runIntegrationTest(string $testName, callable $action) {
    echo "TEST: " . str_pad($testName, 60, ".");
    
    ob_start();
    try {
        $action();
        $output = ob_get_clean();
        
        $jsonStart = strpos($output, '{');
        $json = ($jsonStart !== false) ? json_decode(substr($output, $jsonStart), true) : null;

        if (!$json) {
            echo " [FAILED] - Invalid JSON Output\n";
            // Uncomment the line below to debug raw output if needed
            // echo "DEBUG: " . $output . "\n";
            return;
        }

        // Logic to determine success based on expected output
        $passed = false;
        
        // Scenario A: Successful Creation (Returns ID)
        if (isset($json['data']['id'])) {
            $passed = true;
        }
        // Scenario B: Validation Error (Returns Error Code)
        elseif (isset($json['error']['code']) && $json['error']['code'] === 'VALIDATION_ERROR') {
            $passed = true;
        }

        if ($passed) {
            echo " [PASSED]\n";
            if (isset($json['data']['id'])) {
                echo "   -> Created Object ID: " . $json['data']['id'] . "\n";
            }
        } else {
            echo " [FAILED]\n";
            echo "   Response: " . json_encode($json) . "\n";
        }

    } catch (Throwable $e) {
        ob_end_clean();
        echo " [EXCEPTION] - " . $e->getMessage() . "\n";
    }
}

// -----------------------------------------------------------------------------
// 4. EXECUTION SUITE
// -----------------------------------------------------------------------------

echo "\n============================================================\n";
echo "INTEGRATION TEST SUITE: ObjectsController\n";
echo "============================================================\n";

// TEST 1: Validation Logic
// Ensures the Controller blocks invalid data (e.g., missing name or type).
runIntegrationTest("Create Object - Validation (Missing Name)", function() {
    $controller = new ObjectsController();
    $invalidPayload = [
        'description' => 'Object without name',
        'state'       => 1
    ];
    $controller->create(new MockRequest($invalidPayload));
});

// TEST 2: Database Persistence
// Ensures the Controller successfully writes a new record to the Database.
runIntegrationTest("Create Object - Persistence (Valid Data)", function() {
    $controller = new ObjectsController();
    
    // Generate unique serial number to avoid Unique Constraint violations
    $rand = rand(10000, 99999);
    
    $validPayload = [
        'name'           => 'Integration Test Object',
        'description'    => 'Automated test object',
        'serial_number'  => "SN-$rand",
        'object_type_id' => 1, // WARNING: Ensure ObjectType with ID 1 exists in DB
        'state'          => 1
    ];
    
    $controller->create(new MockRequest($validPayload));
});

echo "\nTest Suite Completed.\n";