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
require_once __DIR__ . '/../src/Http/Controllers/ClientsController.php';
require_once __DIR__ . '/../src/Application/Mediator/Mediator.php';


 require_once __DIR__ . '/../src/Http/Controllers/LoansController.php';
 require_once __DIR__ . '/../src/Http/Controllers/AuthController.php';
 

// Load Controllers
require_once __DIR__ . '/../src/Http/Controllers/ObjectTypesController.php';
require_once __DIR__ . '/../src/Http/Controllers/ObjectsController.php';



// Initialize Components
$router = new Router();
$mediator = new Mediator();
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
$router->add('DELETE', '/api/objecttypes/{code}',   ['ObjectTypesController', 'delete']);

// Physical Objects Routes
$router->add('GET',  '/api/objects',                ['ObjectsController', 'getAll']);
$router->add('POST', '/api/objects',                ['ObjectsController', 'create']);
$router->add('GET',    '/api/objects/{id}',         ['ObjectsController', 'getById']);
$router->add('POST',   '/api/objects/maintenance',  ['ObjectsController', 'sendMaintenance']);
$router->add('PUT',    '/api/objects/{id}',         ['ObjectsController', 'update']);
$router->add('DELETE', '/api/objects/{id}',         ['ObjectsController', 'delete']);

// Loans Routes
$router->add('GET',    '/api/loans',                ['LoansController', 'getAll']);
$router->add('GET',    '/api/loans/{id}',           ['LoansController', 'getById']);
$router->add('POST',   '/api/loans',                ['LoansController', 'create']);
$router->add('PUT',    '/api/loans/{id}',           ['LoansController', 'update']);
$router->add('DELETE', '/api/loans/{id}',           ['LoansController', 'delete']);

// Authentication Routes
$router->add('POST',   '/api/login',                ['AuthController', 'login']);
$router->add('POST',   '/api/logout',               ['AuthController', 'logout']);



// Create Request Object
$method = $request->getMethod();
$uri = $request->getPath();

// Dispatch Request with Request Object
$router->dispatch($method, $uri, $request);
