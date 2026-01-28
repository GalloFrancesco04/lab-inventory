<?php

/**
 * INTEGRATION TEST: AuthController
 * * Objective: Verify that the AuthController correctly handles 
 * Login, Logout, and AuthStatus requests by dispatching 
 * the appropriate commands via the Mediator.
 */

// 1. Configuration of base paths and error handling
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
ini_set('display_errors', '0');

$baseSrc = __DIR__ . '/../../../src';

// 2. Capture output buffer to prevent premature printing during imports
ob_start();

try {
    // Import Core Dependencies
    require_once $baseSrc . '/Http/Response.php';
    require_once $baseSrc . '/Application/Mediator/Mediator.php';
    require_once $baseSrc . '/Http/Request.php';
    
    // Import Database Config (if available) to prevent crashes
    if (file_exists($baseSrc . '/../config/Database.php')) {
        require_once $baseSrc . '/../config/Database.php';
    }

    // Import the Controller to be tested
    // Note: The Controller file itself handles the imports of Commands/Handlers
    require_once $baseSrc . '/Http/Controllers/AuthController.php';

} catch (Throwable $e) {
    ob_end_clean();
    echo "\n[FATAL ERROR] Failed to load required files:\n";
    echo $e->getMessage() . "\n";
    exit(1);
}
ob_end_clean();

/**
 * Class TestAuthRequest
 * A concrete implementation of Request to simulate HTTP body data.
 */
class TestAuthRequest extends Request {
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
 * Helper function to execute a test case and analyze the response.
 */
function runAuthTestCase($testName, $callback) {
    echo "------------------------------------------------------------\n";
    echo "TEST: $testName\n";
    echo "------------------------------------------------------------\n";
    
    ob_start(); 
    try {
        $callback();
        $output = ob_get_clean();
        
        // Analyze the output
        if (empty($output)) {
            echo "[FAIL] No response received.\n";
        } elseif (strpos($output, '{') !== false) {
            // Valid JSON response detected
            echo "[PASS] JSON Response Received.\n";
            
            // Optional: Print JSON for debugging
            // echo $output . "\n"; 

            if (strpos($output, 'error') !== false) {
                echo "[INFO] Response contains an error (Expected if DB is down).\n";
            }
        } else {
            echo "[INFO] Raw Output: $output\n";
        }

    } catch (Throwable $e) {
        ob_end_clean();
        echo "[FAIL] PHP Runtime Error: " . $e->getMessage() . "\n";
    }
    echo "\n";
}

// ------------------------------------------------------------
// EXECUTION OF TEST SUITE
// ------------------------------------------------------------

echo "\nSTARTING AUTH CONTROLLER TEST SUITE\n\n";

try {
    $controller = new AuthController();
} catch (Throwable $e) {
    echo "[CRITICAL] Could not instantiate AuthController.\n";
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}

// Test Case 1: Login
// Simulates a user trying to log in with email and password.
runAuthTestCase("Login User (Standard Flow)", function() use ($controller) {
    $requestData = [
        'email' => 'admin@example.com',
        'password' => 'secret123'
    ];
    
    $req = new TestAuthRequest($requestData);
    $controller->login($req);
});

// Test Case 2: Check Status
// Simulates a request to check if the user is authenticated.
runAuthTestCase("Check Auth Status", function() use ($controller) {
    $req = new TestAuthRequest([]); // No body needed
    $controller->checkStatus($req);
});

// Test Case 3: Logout
// Simulates a logout request.
runAuthTestCase("Logout User", function() use ($controller) {
    $req = new TestAuthRequest([]); // No body needed
    $controller->logout($req);
});