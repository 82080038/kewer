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

require_once '../includes/functions.php';
require_once '../includes/database_class.php';

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
                
                $db = db();
                $sql = "SELECT * FROM users WHERE username = ? AND status = 'aktif'";
                $user = $db->selectOne($sql, [$username]);
                
                if ($user && password_verify($password, $user['password'])) {
                    // Start session
                    session_start();
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
                session_start();
                session_destroy();
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Logout berhasil'
                ]);
                break;
                
            case 'check':
                // Check if user is logged in
                session_start();
                
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
