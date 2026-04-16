<?php
require_once __DIR__ . '/../config/path.php';
require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/config/session.php';
require_once BASE_PATH . '/includes/auto_confirm.php';

header('Content-Type: application/json');

// Get request method
$method = $_SERVER['REQUEST_METHOD'];

// Get current user
$user = getCurrentUser();
if (!$user) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

// Permission check
if (!hasPermission('pinjaman.auto_confirm')) {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden - No permission']);
    exit();
}

switch ($method) {
    case 'GET':
        $cabang_id = $_GET['cabang_id'] ?? null;
        
        if ($cabang_id === 'global' || $cabang_id === 'null') {
            $cabang_id = null;
        }
        
        $settings = getAutoConfirmSettings($cabang_id);
        
        // Get all branch settings if no specific cabang requested
        if ($cabang_id === null && isset($_GET['all'])) {
            $allSettings = query("SELECT acs.*, c.nama_cabang 
                                   FROM auto_confirm_settings acs 
                                   LEFT JOIN cabang c ON acs.cabang_id = c.id 
                                   ORDER BY acs.cabang_id IS NULL, c.nama_cabang");
            echo json_encode(['success' => true, 'data' => $allSettings]);
        } else {
            echo json_encode(['success' => true, 'data' => $settings]);
        }
        break;
        
    case 'POST':
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid input']);
            exit();
        }
        
        $cabang_id = $input['cabang_id'] ?? null;
        $enabled = $input['enabled'] ?? false;
        $plafon_threshold = $input['plafon_threshold'] ?? 0;
        $tenor_limit = $input['tenor_limit'] ?? 0;
        $max_risk_score = $input['max_risk_score'] ?? 10;
        $require_nasabah_history = $input['require_nasabah_history'] ?? true;
        $min_nasabah_history_months = $input['min_nasabah_history_months'] ?? 3;
        
        // Validation
        if ($cabang_id !== null && !is_numeric($cabang_id)) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid cabang_id']);
            exit();
        }
        
        // Check if setting exists
        $existing = query("SELECT id FROM auto_confirm_settings WHERE cabang_id " . 
                         ($cabang_id === null ? "IS NULL" : "= ?"), 
                         $cabang_id === null ? [] : [$cabang_id]);
        
        if ($existing && count($existing) > 0) {
            // Update existing
            $result = query("UPDATE auto_confirm_settings 
                           SET enabled = ?, plafon_threshold = ?, tenor_limit = ?, 
                               max_risk_score = ?, require_nasabah_history = ?, 
                               min_nasabah_history_months = ?, updated_by = ? 
                           WHERE cabang_id " . ($cabang_id === null ? "IS NULL" : "= ?"),
                           array_filter([$enabled, $plafon_threshold, $tenor_limit, 
                                       $max_risk_score, $require_nasabah_history, 
                                       $min_nasabah_history_months, $user['id'], $cabang_id], 
                                      function($v) { return $v !== null; }));
            
            if ($result !== false) {
                echo json_encode(['success' => true, 'message' => 'Auto-confirm settings updated']);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Failed to update settings']);
            }
        } else {
            // Create new
            $result = query("INSERT INTO auto_confirm_settings 
                           (cabang_id, enabled, plafon_threshold, tenor_limit, 
                            max_risk_score, require_nasabah_history, min_nasabah_history_months, 
                            created_by) 
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
                           [$cabang_id, $enabled, $plafon_threshold, $tenor_limit, 
                            $max_risk_score, $require_nasabah_history, 
                            $min_nasabah_history_months, $user['id']]);
            
            if ($result !== false) {
                echo json_encode(['success' => true, 'message' => 'Auto-confirm settings created']);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Failed to create settings']);
            }
        }
        break;
        
    case 'DELETE':
        $cabang_id = $_GET['cabang_id'] ?? null;
        
        if ($cabang_id === 'global' || $cabang_id === 'null') {
            $cabang_id = null;
        }
        
        // Cannot delete global setting, only disable it
        if ($cabang_id === null) {
            $result = query("UPDATE auto_confirm_settings SET enabled = FALSE WHERE cabang_id IS NULL");
        } else {
            $result = query("DELETE FROM auto_confirm_settings WHERE cabang_id = ?", [$cabang_id]);
        }
        
        if ($result !== false) {
            echo json_encode(['success' => true, 'message' => 'Auto-confirm settings deleted']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to delete settings']);
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        break;
}
?>
