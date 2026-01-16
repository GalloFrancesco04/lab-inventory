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
require_once __DIR__ . '/utils/JsonResponse.php';
require_once __DIR__ . '/database/Database.php';
require_once __DIR__ . '/Router.php';
require_once __DIR__ . '/Mediator.php';
require_once __DIR__ . '/controllers/ClientController.php';

// Initialize Components
$router = new Router();
$mediator = new Mediator();

// --- ROUTES REGISTRATION ---
$router->add('GET', '/api/clients', ['ClientController', 'getAll']);


// Dispatch Request
$method = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

$router->dispatch($method, $uri);