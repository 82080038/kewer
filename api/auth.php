<?php
/**
 * API: Authentication
 * 
 * Endpoints for user authentication
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../config/path.php';
require_once BASE_PATH . '/includes/functions.php';
require_once BASE_PATH . '/includes/database_class.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'POST':
        $action = $_GET['action'] ?? 'login';
        
        switch ($action) {
            case 'login':
                // Login user
                $input = json_decode(file_get_contents('php://input'), true);
                
                $username = $input['username'] ?? '';
                $password = $input['password'] ?? '';
                
                if (empty($username) || empty($password)) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Username dan password diperlukan']);
                    exit();
                }
                
                // Development mode quick login
                $dev_credentials = [
                    'admin' => 'password',
                    'owner' => 'password',
                    'manager1' => 'password',
                    'petugas1' => 'password',
                    'petugas2' => 'password',
                    'karyawan1' => 'password',
                    'karyawan2' => 'password',
                ];
                
                if (isset($dev_credentials[$username]) && $password === $dev_credentials[$username]) {
                    // Get user from database for session
                    require_once BASE_PATH . '/config/database.php';
                    $user = query("SELECT * FROM users WHERE username = ? AND status = 'aktif'", [$username]);
                    
                    if ($user) {
                        // Start session if not already active
                        if (session_status() === PHP_SESSION_NONE) {
                            session_start();
                        }
                        $_SESSION['user_id'] = $user[0]['id'];
                        $_SESSION['username'] = $user[0]['username'];
                        $_SESSION['role'] = $user[0]['role'];
                        $_SESSION['cabang_id'] = $user[0]['cabang_id'];
                        
                        echo json_encode([
                            'success' => true,
                            'message' => 'Login berhasil (development mode)',
                            'user' => [
                                'id' => $user[0]['id'],
                                'username' => $user[0]['username'],
                                'nama' => $user[0]['nama'],
                                'role' => $user[0]['role'],
                                'cabang_id' => $user[0]['cabang_id']
                            ]
                        ]);
                        exit();
                    }
                }
                
                $db = db();
                $sql = "SELECT * FROM users WHERE username = ? AND status = 'aktif'";
                $user = $db->selectOne($sql, [$username]);
                
                if ($user && password_verify($password, $user['password'])) {
                    // Start session if not already active
                    if (session_status() === PHP_SESSION_NONE) {
                        session_start();
                    }
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['cabang_id'] = $user['cabang_id'];
                    
                    // Update last login
                    $db->update("UPDATE users SET updated_at = NOW() WHERE id = ?", [$user['id']]);
                    
                    echo json_encode([
                        'success' => true,
                        'message' => 'Login berhasil',
                        'user' => [
                            'id' => $user['id'],
                            'username' => $user['username'],
                            'nama' => $user['nama'],
                            'role' => $user['role'],
                            'cabang_id' => $user['cabang_id']
                        ]
                    ]);
                } else {
                    http_response_code(401);
                    echo json_encode(['error' => 'Username atau password salah']);
                }
                break;
                
            case 'logout':
                // Logout user
                if (session_status() === PHP_SESSION_NONE) {
                    session_start();
                }
                session_destroy();
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Logout berhasil'
                ]);
                break;
                
            case 'check':
                // Check if user is logged in
                if (session_status() === PHP_SESSION_NONE) {
                    session_start();
                }
                
                if (isset($_SESSION['user_id'])) {
                    echo json_encode([
                        'success' => true,
                        'logged_in' => true,
                        'user' => [
                            'id' => $_SESSION['user_id'],
                            'username' => $_SESSION['username'],
                            'role' => $_SESSION['role'],
                            'cabang_id' => $_SESSION['cabang_id']
                        ]
                    ]);
                } else {
                    echo json_encode([
                        'success' => true,
                        'logged_in' => false
                    ]);
                }
                break;
                
            default:
                http_response_code(400);
                echo json_encode(['error' => 'Invalid action. Use: login, logout, check']);
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
}
?>
