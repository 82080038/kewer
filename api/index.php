<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once __DIR__ . '/../config/path.php';
require_once BASE_PATH . '/includes/functions.php';

// API Authentication (simple token-based)
function authenticateAPI() {
    $headers = getallheaders();
    $token = $headers['Authorization'] ?? '';
    
    // Simple token validation - in production, use JWT or similar
    if ($token !== 'Bearer kewer-api-token-2024') {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        exit();
    }
}

// Get request method and endpoint
$method = $_SERVER['REQUEST_METHOD'];
$request_uri = $_SERVER['REQUEST_URI'];
$path = parse_url($request_uri, PHP_URL_PATH);
$path_parts = explode('/', trim($path, '/'));

// Remove 'api' from path parts
if ($path_parts[0] === 'api') {
    array_shift($path_parts);
}

$endpoint = $path_parts[0] ?? '';
$id = $path_parts[1] ?? '';

// Authenticate all API requests
authenticateAPI();

try {
    switch ($endpoint) {
        case 'nasabah':
            require_once 'nasabah.php';
            break;
            
        case 'pinjaman':
            require_once 'pinjaman.php';
            break;
            
        case 'angsuran':
            require_once 'angsuran.php';
            break;
            
        case 'dashboard':
            require_once 'dashboard.php';
            break;
            
        case 'auth':
            require_once 'auth.php';
            break;
            
        case 'setting':
        case 'setting_bunga':
            require_once 'setting_bunga.php';
            break;
            
        case 'kas_petugas':
            require_once 'kas_petugas.php';
            break;
            
        case 'pengeluaran':
            require_once 'pengeluaran.php';
            break;
            
        case 'ocr':
            require_once 'ocr.php';
            break;
            
        case 'family_risk':
            require_once 'family_risk.php';
            break;
            
        case 'kas_bon':
            require_once 'kas_bon.php';
            break;
            
        default:
            http_response_code(404);
            echo json_encode(['error' => 'Endpoint not found']);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
