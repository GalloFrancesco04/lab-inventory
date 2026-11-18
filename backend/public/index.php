<?php

// CORS Configuration
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Load Core Classes
require_once __DIR__ . '/../src/Http/Request.php';
require_once __DIR__ . '/../src/Http/Response.php';
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../src/Http/Router.php';
require_once __DIR__ . '/../src/Application/Mediator/Mediator.php';


 require_once __DIR__ . '/../src/Http/Controllers/LoansController.php';
 require_once __DIR__ . '/../src/Http/Controllers/AuthController.php';
 


require_once __DIR__ . '/../src/Http/Controllers/ObjectTypesController.php';
require_once __DIR__ . '/../src/Http/Controllers/ObjectsController.php';
require_once __DIR__ . '/../src/Http/Controllers/ClientsController.php';
require_once __DIR__ . '/../src/Http/Controllers/MaintenanceController.php';
require_once __DIR__ . '/../src/Http/Controllers/OperationController.php';
require_once __DIR__ . '/../src/Http/Controllers/UsersController.php';






// Initialize Components
$router = new Router();

$request = new Request();


// --- ROUTES REGISTRATION 
// Client Routes
$router->add('GET',    '/api/clients',              ['ClientsController', 'getAll']);
$router->add('GET', '/api/clients/{id}',            ['ClientsController', 'getById']);
$router->add('POST',   '/api/clients',              ['ClientsController', 'create']);
$router->add('PUT',    '/api/clients/{id}',         ['ClientsController', 'update']);
$router->add('DELETE', '/api/clients/{id}',         ['ClientsController', 'delete']);
$router->add('POST',   '/api/clients/toggle-block', ['ClientsController', 'toggleBlock']);

// Object Types Routes
$router->add('GET',    '/api/objecttypes',          ['ObjectTypesController', 'getAll']);
$router->add('GET', '/api/objecttypes/{code}',      ['ObjectTypesController', 'getByCode']);
$router->add('POST',   '/api/objecttypes',          ['ObjectTypesController', 'create']);
$router->add('PUT',    '/api/objecttypes/{code}',   ['ObjectTypesController', 'update']);


// Physical Objects Routes
$router->add('GET',  '/api/objects',                ['ObjectsController', 'getAll']);
$router->add('GET',    '/api/objects/available',    ['ObjectsController', 'getAvailable']);
$router->add('POST', '/api/objects',                ['ObjectsController', 'create']);
$router->add('GET',    '/api/objects/{id}',         ['ObjectsController', 'getById']);
$router->add('POST',   '/api/objects/maintenance',  ['ObjectsController', 'sendMaintenance']);
$router->add('PUT',    '/api/objects/{id}',         ['ObjectsController', 'update']);
$router->add('PUT',    '/api/objects/{id}/toggle-service',['ObjectsController', 'toggleStatus']);


// Loans Routes
$router->add('GET',    '/api/loans',                ['LoansController', 'getAll']);
$router->add('GET',    '/api/loans/{id}',           ['LoansController', 'getById']);
$router->add('POST',   '/api/loans',                ['LoansController', 'create']);
$router->add('PUT',    '/api/loans/{id}',           ['LoansController', 'update']);
$router->add('POST', '/api/loans/{id}/close',           ['LoansController', 'close']);

// Authentication Routes
$router->add('POST',   '/api/login',                ['AuthController', 'login']);
$router->add('POST',   '/api/logout',               ['AuthController', 'logout']);
$router->add('GET',    '/api/auth/status',          ['AuthController', 'checkStatus']);

// Users Routes
$router->add('GET',    '/api/technicians',          ['UsersController', 'getTechnicians']);

// Maintenance Routes
// Maintenance
$router->add('GET',    '/api/maintenance',          ['MaintenanceController', 'getAll']);
$router->add('POST',   '/api/maintenance',          ['MaintenanceController', 'start']);
$router->add('PUT',    '/api/maintenance/{id}',     ['MaintenanceController', 'close']);
$router->add('GET',    '/api/maintenance/{id}',     ['MaintenanceController', 'getById']);

// Operations
//outer->add('GET',    '/api/operations',           ['OperationsController', 'getAll']);
$router->add('POST', '/api/operations', ['OperationsController', 'create']);
$router->add('PUT', '/api/operations/{id}', ['OperationsController', 'update']);

// Create Request Object
$method = $request->getMethod();
$uri = $request->getPath();

// Every route except authentication requires a logged-in user
if (!in_array($uri, ['/api/login', '/api/logout', '/api/auth/status'])) {
    require_once __DIR__ . '/../src/Infrastructure/Security/SessionManager.php';
    if (!(new SessionManager())->isLoggedIn()) {
        Response::error("Login required", "UNAUTHORIZED", 401);
    }
}

// Dispatch Request with Request Object
$router->dispatch($method, $uri, $request);
