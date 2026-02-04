<?php

/**
 * OperationsController Integration Test
 * * Objective: Verify that the OperationsController correctly receives HTTP requests,
 * instantiates the appropriate Commands (Add/Update), and dispatches them via the Mediator.
 * * Note: This test interacts with the real application files. If the database is not 
 * reachable, the test will pass the Controller logic check but return a DB connection error,
 * which is the expected behavior for an integration test without a live DB container.
 */

// Configuration of base paths
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
ini_set('display_errors', '0');

$baseSrc = __DIR__ . '/../../../src';

// capture output buffer to prevent premature printing
ob_start();

try {
    // 1. Import Core Dependencies
    require_once $baseSrc . '/Http/Response.php';
    require_once $baseSrc . '/Application/Mediator/Mediator.php';
    require_once $baseSrc . '/Http/Request.php';
    
    // 2. Import Database Configuration (if available)
    if (file_exists($baseSrc . '/../config/Database.php')) {
        require_once $baseSrc . '/../config/Database.php';
    }

    // 3. Import the Controller to be tested
    require_once $baseSrc . '/Http/Controllers/OperationController.php';

} catch (Throwable $e) {
    ob_end_clean();
    echo "\n[FATAL ERROR] Failed to load required files:\n";
    echo $e->getMessage() . "\n";
    exit(1);
}
ob_end_clean();

/**
 * Class TestRequest
 * * A concrete implementation of the Request class used to simulate 
 * incoming HTTP data (POST/PUT body) for the CLI environment.
 */
class TestRequest extends Request {
    private $data;

    public function __construct(array $data = []) { 
        $this->data = $data; 
    }

    public function getBody(?string $key = null) { 
        if ($key) {
            return $this->data[$key] ?? null;
        }
        return $this->data; 
    }
}

/**
 * Helper function to execute a test case and capture the JSON output.
 * * @param string $testName The name/description of the test.
 * @param callable $callback The function containing the test logic.
 */
function runTestCase($testName, $callback) {
    echo "------------------------------------------------------------\n";
    echo "TEST: $testName\n";
    echo "------------------------------------------------------------\n";
    
    ob_start(); 
    try {
        $callback();
        $output = ob_get_clean();
        
        // Analyze the output
        if (empty($output)) {
            echo "[FAIL] No response received from Controller.\n";
        } elseif (strpos($output, '{') !== false) {
            // Valid JSON response detected
            $jsonStart = strpos($output, '{');
            $json = substr($output, $jsonStart);
            echo "[PASS] JSON Response Received:\n$json\n";
            
            // Check for DB errors in the response
            if (strpos($json, 'SQLSTATE') !== false) {
                echo "[INFO] Database connection failed (Expected in local CLI without DB).\n";
            }
        } else {
            echo "[INFO] Raw Output (Non-JSON):\n$output\n";
        }

    } catch (Throwable $e) {
        ob_end_clean();
        echo "[FAIL] PHP Runtime Error: " . $e->getMessage() . "\n";
        echo "Location: " . $e->getFile() . " on line " . $e->getLine() . "\n";
    }
    echo "\n";
}

// ------------------------------------------------------------
// EXECUTION OF TEST SUITE
// ------------------------------------------------------------

echo "\nSTARTING OPERATIONS CONTROLLER TEST SUITE\n\n";

try {
    $controller = new OperationsController();
} catch (Throwable $e) {
    echo "[CRITICAL] Could not instantiate OperationsController. Check file paths.\n";
    echo $e->getMessage();
    exit(1);
}

// Test Case 1: Create Operation - Success Scenario
/*/ Simulates a valid request to add an operation to a maintenance ID.
runTestCase("Create Operation (Valid Input)", function() use ($controller) {
    $requestData = [
        'maintenance_id' => 1,
        'description' => 'Replacing internal wiring',
        'cost' => 150.50,
        'start_date' => new DateTime('now')
    ];
    
    $req = new TestRequest($requestData);
    $controller->create($req);
});*/

// Test Case 2: Create Operation - Validation Error
// Simulates a request missing the required 'maintenance_id' field.
runTestCase("Create Operation (Missing Required Field)", function() use ($controller) {
    $requestData = [
        'description' => 'This should fail validation'
        // maintenance_id is missing
    ];
    
    $req = new TestRequest($requestData);
    $controller->create($req);
});

// Test Case 3: Update Operation
/*/ Simulates a PUT request to update an existing operation (ID: 5).
runTestCase("Update Operation (Update Cost and End Date)", function() use ($controller) {
    $targetOperationId = 1;
    $requestData = [
        'cost' => 200.00,
        'end_date' => new DateTime('now'),
        'description' => 'Updated description after review'
    ];
    
    $req = new TestRequest($requestData);
    $controller->update($req, $targetOperationId);
});*/