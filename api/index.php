<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../helpers/response.php';

header('Content-Type: application/json');

// API endpoint structure
$requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';

jsonResponse([
    'status' => 'success',
    'message' => 'API is running',
    'version' => env('APP_VERSION', '1.0.0')
]);
