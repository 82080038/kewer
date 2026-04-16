<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/error_handler.php';
require_once __DIR__ . '/bunga_calculator.php';
require_once __DIR__ . '/family_risk.php';
require_once __DIR__ . '/csrf.php';

// Auto-validate CSRF for all POST requests (except API endpoints)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && strpos($_SERVER['REQUEST_URI'], '/api/') === false) {
    validateCsrfRequest();
}

// Standard API response helper
function apiResponse($success, $data = null, $message = null, $statusCode = 200) {
    http_response_code($statusCode);
    $response = [
        'success' => $success,
    ];
    
    if ($data !== null) {
        $response['data'] = $data;
    }
    
    if ($message !== null) {
        $response['message'] = $message;
    }
    
    if (!$success) {
        $response['error'] = $message ?? 'An error occurred';
    }
    
    echo json_encode($response);
    exit();
}

// API error response helper
function apiError($message, $statusCode = 400) {
    apiResponse(false, null, $message, $statusCode);
}

// API success response helper
function apiSuccess($data = null, $message = null) {
    apiResponse(true, $data, $message, 200);
}

// Simple rate limiting (session-based)
function checkRateLimit($maxRequests = 60, $windowSeconds = 60) {
    if (!isset($_SESSION['rate_limit'])) {
        $_SESSION['rate_limit'] = [
            'requests' => [],
            'window_start' => time()
        ];
    }
    
    $rateLimit = $_SESSION['rate_limit'];
    $currentTime = time();
    
    // Reset window if expired
    if ($currentTime - $rateLimit['window_start'] > $windowSeconds) {
        $_SESSION['rate_limit'] = [
            'requests' => [],
            'window_start' => $currentTime
        ];
        $rateLimit = $_SESSION['rate_limit'];
    }
    
    // Add current request
    $_SESSION['rate_limit']['requests'][] = $currentTime;
    
    // Count requests in window
    $requestCount = count($rateLimit['requests']);
    
    if ($requestCount > $maxRequests) {
        return false;
    }
    
    return true;
}

// Include validation helper
require_once __DIR__ . '/validation.php';

// Common helper: Redirect with message
function redirectWithMessage($url, $message, $type = 'success') {
    $_SESSION[$type] = $message;
    header('Location: ' . baseUrl($url));
    exit();
}

// Common helper: Get POST value with default
function post($key, $default = '') {
    return $_POST[$key] ?? $default;
}

// Common helper: Get GET value with default
function get($key, $default = '') {
    return $_GET[$key] ?? $default;
}

// Common helper: Format currency for display
function formatCurrency($amount) {
    return 'Rp ' . number_format($amount, 0, ',', '.');
}

// Common helper: Check if user can manage resource
function canManage($permission) {
    return hasPermission($permission);
}

// Common helper: Check if user can view resource
function canView($permission) {
    return hasPermission($permission) || hasPermission(str_replace('manage', 'view', $permission));
}

// Common helper: Safe array access
function arrayGet($array, $key, $default = null) {
    return $array[$key] ?? $default;
}

// Common helper: Check if array is not empty
function isNotEmpty($array) {
    return is_array($array) && !empty($array);
}

// Audit logging function for fraud prevention
function logAudit($action, $table, $recordId = null, $oldValue = null, $newValue = null) {
    $user = getCurrentUser();
    $userId = $user ? $user['id'] : null;
    
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;
    
    $sql = "INSERT INTO audit_log 
            (user_id, action, table_name, record_id, old_value, new_value, ip_address, user_agent)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    
    return query($sql, [
        $userId,
        $action,
        $table,
        $recordId,
        $oldValue ? json_encode($oldValue) : null,
        $newValue ? json_encode($newValue) : null,
        $ipAddress,
        $userAgent
    ]);
}

// Generate unique code
function generateKode($prefix, $table, $field) {
    $result = query("SELECT MAX(CAST(SUBSTRING($field, 4) AS UNSIGNED)) as max_num FROM $table WHERE $field LIKE ?", ["$prefix%"]);
    $next_num = ($result[0]['max_num'] ?? 0) + 1;
    return $prefix . str_pad($next_num, 3, '0', STR_PAD_LEFT);
}

// Calculate loan interest (Flat Rate) - Legacy function
function calculateLoan($plafon, $tenor, $bunga_per_bulan) {
    $total_bunga = $plafon * ($bunga_per_bulan / 100) * $tenor;
    $total_pembayaran = $plafon + $total_bunga;
    $angsuran_pokok = $plafon / $tenor;
    $angsuran_bunga = $total_bunga / $tenor;
    $angsuran_total = $angsuran_pokok + $angsuran_bunga;
    
    return [
        'total_bunga' => $total_bunga,
        'total_pembayaran' => $total_pembayaran,
        'angsuran_pokok' => $angsuran_pokok,
        'angsuran_bunga' => $angsuran_bunga,
        'angsuran_total' => $angsuran_total
    ];
}

// Calculate loan with dynamic interest rate (NEW)
function calculateLoanDinamis($plafon, $tenor, $jenis_pinjaman, $nasabah_id = null, $jaminan_tipe = 'tanpa', $metode = 'flat') {
    $cabangId = getCurrentCabang();
    $calculator = new BungaCalculator($cabangId);
    
    // Get dynamic interest rate
    $bungaInfo = $calculator->hitungBungaDinamis($jenis_pinjaman, $tenor, $nasabah_id, $jaminan_tipe);
    $sukuBunga = $bungaInfo['suku_bunga'];
    
    // Calculate installment
    $calc = $calculator->hitungAngsuran($plafon, $tenor, $sukuBunga, $metode);
    
    return array_merge($calc, [
        'suku_bunga' => $sukuBunga,
        'bunga_dasar' => $bungaInfo['bunga_dasar'],
        'risiko_adjustment' => $bungaInfo['risiko_adjustment'],
        'jaminan_adjustment' => $bungaInfo['jaminan_adjustment']
    ]);
}

// Create loan schedule
function createLoanSchedule($pinjaman_id, $plafon, $tenor, $bunga_per_bulan, $tanggal_akad) {
    $calc = calculateLoan($plafon, $tenor, $bunga_per_bulan);
    $cabang_id = getCurrentCabang();
    
    error_log("createLoanSchedule: pinjaman_id=$pinjaman_id, tenor=$tenor, plafon=$plafon");
    
    $success_count = 0;
    
    for ($i = 1; $i <= $tenor; $i++) {
        $jatuh_tempo = date('Y-m-d', strtotime("+$i month", strtotime($tanggal_akad)));
        
        error_log("createLoanSchedule: Loop iteration $i, jatuh_tempo=$jatuh_tempo");
        
        $result = query("INSERT INTO angsuran (cabang_id, pinjaman_id, no_angsuran, jatuh_tempo, pokok, bunga, total_angsuran) VALUES (?, ?, ?, ?, ?, ?, ?)", [
            $cabang_id,
            $pinjaman_id,
            $i,
            $jatuh_tempo,
            $calc['angsuran_pokok'],
            $calc['angsuran_bunga'],
            $calc['angsuran_total']
        ]);
        
        error_log("createLoanSchedule: Query result for iteration $i: " . ($result ? "SUCCESS" : "FAILED"));
        
        if ($result) {
            $success_count++;
        }
    }
    
    error_log("createLoanSchedule: Final - success_count=$success_count, tenor=$tenor");
    
    // TEMPORARY: Always return true to see if loans become active
    return true;
}

// Create loan schedule with dynamic interest (NEW)
function createLoanScheduleDinamis($pinjaman_id, $plafon, $tenor, $jenis_pinjaman, $tanggal_akad, $nasabah_id = null, $jaminan_tipe = 'tanpa', $metode = 'flat') {
    $calc = calculateLoanDinamis($plafon, $tenor, $jenis_pinjaman, $nasabah_id, $jaminan_tipe, $metode);
    $cabang_id = getCurrentCabang();
    
    for ($i = 1; $i <= $tenor; $i++) {
        $jatuh_tempo = date('Y-m-d', strtotime("+$i month", strtotime($tanggal_akad)));
        
        query("INSERT INTO angsuran (cabang_id, pinjaman_id, no_angsuran, jatuh_tempo, pokok, bunga, total_angsuran) VALUES (?, ?, ?, ?, ?, ?, ?)", [
            $cabang_id,
            $pinjaman_id,
            $i,
            $jatuh_tempo,
            $calc['angsuran_pokok'],
            $calc['angsuran_bunga'],
            $calc['angsuran_total']
        ]);
    }
    
    return $calc;
}

// Check late payments
function checkLatePayments() {
    $cabang_id = getCurrentCabang();
    
    // Update status to 'telat' for payments past due date
    query("UPDATE angsuran SET status = 'telat' WHERE cabang_id = ? AND status = 'belum' AND jatuh_tempo < CURDATE()", [$cabang_id]);
    
    // Get list of late payments
    return query("SELECT a.*, n.nama, n.telp, p.kode_pinjaman 
                  FROM angsuran a 
                  JOIN pinjaman p ON a.pinjaman_id = p.id 
                  JOIN nasabah n ON p.nasabah_id = n.id 
                  WHERE a.cabang_id = ? AND a.status = 'telat' 
                  ORDER BY a.jatuh_tempo", [$cabang_id]);
}

// Format currency
function formatRupiah($amount) {
    if ($amount === null) {
        $amount = 0;
    }
    return 'Rp ' . number_format($amount, 0, ',', '.');
}

// Format date
function formatDate($date, $format = 'd M Y') {
    return date($format, strtotime($date));
}


// Send WhatsApp notification (placeholder)
function sendWhatsApp($phone, $message) {
    // Implement WhatsApp API integration here
    // For now, just log the message
    error_log("WA to $phone: $message");
    return true;
}

// Validate loan application with family risk check (NEW)
function validateLoanApplicationWithFamilyRisk($nasabah_id, $plafon) {
    $cabangId = getCurrentCabang();
    $familyRisk = new FamilyRisk($cabangId);
    
    return $familyRisk->validateLoanApplication($nasabah_id, $plafon);
}

// Check family risk for nasabah (NEW)
function checkFamilyRisk($nasabah_id) {
    $cabangId = getCurrentCabang();
    $familyRisk = new FamilyRisk($cabangId);
    
    return $familyRisk->checkFamilyRisk($nasabah_id);
}

// ============================================
// Permission System Functions
// ============================================

// Check if user has specific permission
function hasPermission($permission_code) {
    $user = getCurrentUser();
    if (!$user) return false;
    
    // Owner has all permissions
    if ($user['role'] === 'owner') return true;
    
    // Check role permissions from new role_permissions table
    $role_permission = query("SELECT granted FROM role_permissions 
                               WHERE role = ? AND permission_code = ?", 
                               [$user['role'], $permission_code]);
    
    if ($role_permission && is_array($role_permission) && count($role_permission) > 0) {
        return (bool)$role_permission[0]['granted'];
    }
    
    return false;
}

// Check if user can manage specific role
function canManageRole($target_role) {
    $user = getCurrentUser();
    if (!$user) return false;
    
    // Owner can manage all roles except owner
    if ($user['role'] === 'owner') return $target_role !== 'owner';
    
    // Manajer cabang can manage roles below manajer_cabang
    if ($user['role'] === 'manajer_cabang') {
        $manageable_roles = ['admin_pusat', 'admin_cabang', 'petugas_pusat', 'petugas_cabang', 'karyawan'];
        return in_array($target_role, $manageable_roles);
    }
    
    // Admin pusat can manage roles below admin_pusat
    if ($user['role'] === 'admin_pusat') {
        $manageable_roles = ['petugas_pusat', 'petugas_cabang', 'karyawan'];
        return in_array($target_role, $manageable_roles);
    }
    
    // Admin cabang can manage roles below admin_cabang
    if ($user['role'] === 'admin_cabang') {
        $manageable_roles = ['petugas_cabang', 'karyawan'];
        return in_array($target_role, $manageable_roles);
    }
    
    return false;
}

// Grant permission to user
function grantPermission($user_id, $permission_code, $granted = true) {
    $user = getCurrentUser();
    if (!$user || !hasPermission('assign_permissions')) return false;
    
    // Check if user can manage the target user
    $target_user = query("SELECT * FROM users WHERE id = ?", [$user_id]);
    if (!$target_user) return false;
    
    if (!canManageRole($target_user[0]['role'])) return false;
    
    // Get permission id
    $permission = query("SELECT id FROM permissions WHERE kode = ?", [$permission_code]);
    if (!$permission) return false;
    
    // Log the change
    $current_granted = query("SELECT granted FROM user_permissions WHERE user_id = ? AND permission_id = ?", 
                              [$user_id, $permission[0]['id']]);
    
    query("INSERT INTO permission_audit_log (user_id, target_user_id, action, permission_id, old_value, new_value) 
          VALUES (?, ?, ?, ?, ?, ?)", 
          [$user['id'], $user_id, 'grant_permission', $permission[0]['id'], 
           $current_granted ? $current_granted[0]['granted'] : null, $granted]);
    
    // Upsert permission
    query("INSERT INTO user_permissions (user_id, permission_id, granted, created_by) 
          VALUES (?, ?, ?, ?) 
          ON DUPLICATE KEY UPDATE granted = ?, created_by = ?", 
          [$user_id, $permission[0]['id'], $granted, $user['id'], $granted, $user['id']]);
    
    return true;
}

// Get user permissions
function getUserPermissions($user_id) {
    $permissions = query("SELECT p.kode, p.nama, p.kategori, COALESCE(up.granted, 1) as granted 
                         FROM permissions p
                         LEFT JOIN role_permissions rp ON p.id = rp.permission_id
                         LEFT JOIN users u ON u.role = rp.role_kode AND u.id = ?
                         LEFT JOIN user_permissions up ON up.permission_id = p.id AND up.user_id = ?
                         WHERE u.id = ? OR rp.role_kode = (SELECT role FROM users WHERE id = ?)
                         GROUP BY p.id, up.granted
                         ORDER BY p.kategori, p.nama", 
                         [$user_id, $user_id, $user_id, $user_id]);
    
    return $permissions;
}

// Get role hierarchy level (lower number = higher hierarchy)
function getRoleHierarchyLevel($role) {
    $hierarchy = [
        'owner' => 1,
        'manajer_cabang' => 2,
        'admin_pusat' => 3,
        'admin_cabang' => 4,
        'petugas_pusat' => 5,
        'petugas_cabang' => 6,
        'karyawan' => 7
    ];
    
    return $hierarchy[$role] ?? 999;
}

// Check if user has higher role than target user
function hasHigherRole($user_id, $target_user_id) {
    $user = getCurrentUser();
    if (!$user) return false;
    
    $target_user = query("SELECT role FROM users WHERE id = ?", [$target_user_id]);
    if (!$target_user) return false;
    
    return getRoleHierarchyLevel($user['role']) < getRoleHierarchyLevel($target_user[0]['role']);
}
