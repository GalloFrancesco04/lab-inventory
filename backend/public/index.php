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
require_once __DIR__ . '/../src/Http/Response.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/Http/Router.php';
require_once __DIR__ . '/../src/Http/Controllers/ClientsController.php';
require_once __DIR__ . '/../src/Application/Mediator/Mediator.php';

// Initialize Components
$router = new Router();
$mediator = new Mediator();

// --- ROUTES REGISTRATION ---
$router->add('GET', '/api/clients', ['ClientsController', 'getAll']);


// Dispatch Request
$method = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

// Test without mod_rewrite
//$uri = str_replace('/public', '', $uri);
//$uri = str_replace('/index.php', '', $uri);

$router->dispatch($method, $uri);