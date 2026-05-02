<?php
// Suppress errors to ensure JSON output
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

try {
    require_once __DIR__ . '/../config/path.php';
    require_once BASE_PATH . '/includes/functions.php';
    require_once BASE_PATH . '/includes/accounting_helper.php';
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Configuration error: ' . $e->getMessage()]);
    exit();
}

// Auth check
try {
    requireLogin();
} catch (Exception $e) {
    http_response_code(401);
    echo json_encode(['error' => 'Authentication failed: ' . $e->getMessage()]);
    exit();
}

try {
    $user = getCurrentUser();
} catch (Exception $e) {
    http_response_code(401);
    echo json_encode(['error' => 'Failed to get user: ' . $e->getMessage()]);
    exit();
}

if (!$user) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

// Get current kantor from query parameter or use single office (id = 1)
$kantor_id = $_GET['kantor_id'] ?? 1;

// Get kantor info
$kantor = query("SELECT * FROM cabang WHERE id = ?", [$kantor_id]);
if (!$kantor) {
    http_response_code(404);
    echo json_encode(['error' => 'Kantor tidak ditemukan']);
    exit();
}

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        // Get pinjaman list
        $search = $_GET['search'] ?? '';
        $status = $_GET['status'] ?? '';
        
        $where = [];
        $params = [];
        
        if ($search) {
            $where[] = "(p.kode_pinjaman LIKE ? OR n.nama LIKE ? OR n.kode_nasabah LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
        
        if ($status) {
            $where[] = "p.status = ?";
            $params[] = $status;
        }
        
        $where_clause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";
        
        $pinjaman = query("
            SELECT p.*, n.nama, n.kode_nasabah 
            FROM pinjaman p 
            JOIN nasabah n ON p.nasabah_id = n.id 
            $where_clause 
            ORDER BY p.created_at DESC
        ", $params);
        
        if (!is_array($pinjaman)) $pinjaman = [];
        
        echo json_encode([
            'success' => true,
            'data' => $pinjaman,
            'total' => count($pinjaman)
        ]);
        break;
        
    case 'POST':
        // Create new pinjaman
        if (!hasPermission('manage_pinjaman')) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Forbidden - No permission to manage pinjaman']);
            break;
        }
        $input = json_decode(file_get_contents('php://input'), true);
        
        $nasabah_id = $input['nasabah_id'] ?? '';
        $plafon = $input['plafon'] ?? '';
        $tenor = $input['tenor'] ?? '';
        $frekuensi = $input['frekuensi'] ?? 'bulanan';
        $bunga_per_bulan = $input['bunga_per_bulan'] ?? '';
        $tanggal_akad = $input['tanggal_akad'] ?? '';
        $tujuan_pinjaman = $input['tujuan_pinjaman'] ?? '';
        $jaminan = $input['jaminan'] ?? '';
        
        // Validate frekuensi
        if (!in_array($frekuensi, ['harian', 'mingguan', 'bulanan'])) {
            $frekuensi = 'bulanan';
        }
        
        // Validation
        if (!$nasabah_id || !$plafon || !$tenor || !$bunga_per_bulan || !$tanggal_akad) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Semua field wajib diisi']);
            break;
        }
        
        if (!is_numeric($plafon) || $plafon <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Plafon harus berupa angka positif']);
            break;
        }
        
        $max_tenor = ['harian' => 365, 'mingguan' => 52, 'bulanan' => 24];
        $max = $max_tenor[$frekuensi] ?? 24;
        if (!is_numeric($tenor) || $tenor <= 0 || $tenor > $max) {
            http_response_code(400);
            $label = ['harian' => 'hari', 'mingguan' => 'minggu', 'bulanan' => 'bulan'];
            echo json_encode(['success' => false, 'error' => "Tenor harus antara 1-$max " . ($label[$frekuensi] ?? 'bulan')]);
            break;
        }
        
        // Check if nasabah has active loan
        $active_loan = query("SELECT id FROM pinjaman WHERE nasabah_id = ? AND status IN ('disetujui', 'aktif')", [$nasabah_id]);
        if ($active_loan) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Nasabah masih memiliki pinjaman aktif']);
            break;
        }
        
        // Calculate loan
        $calc = calculateLoan($plafon, $tenor, $bunga_per_bulan, $frekuensi);
        
        // Generate kode pinjaman
        $kode_pinjaman = generateKode('PNJ', 'pinjaman', 'kode_pinjaman');
        
        // Calculate due date based on frequency
        switch ($frekuensi) {
            case 'harian':
                $tanggal_jatuh_tempo = date('Y-m-d', strtotime("+$tenor day", strtotime($tanggal_akad)));
                break;
            case 'mingguan':
                $tanggal_jatuh_tempo = date('Y-m-d', strtotime("+$tenor week", strtotime($tanggal_akad)));
                break;
            default:
                $tanggal_jatuh_tempo = date('Y-m-d', strtotime("+$tenor month", strtotime($tanggal_akad)));
                break;
        }
        
        // Insert pinjaman
        $result = query("INSERT INTO pinjaman (
            kode_pinjaman, nasabah_id, plafon, tenor, frekuensi, bunga_per_bulan, 
            total_bunga, total_pembayaran, angsuran_pokok, angsuran_bunga, angsuran_total,
            tanggal_akad, tanggal_jatuh_tempo, tujuan_pinjaman, jaminan, status, petugas_id
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pengajuan', ?)", [
            $kode_pinjaman, $nasabah_id, $plafon, $tenor, $frekuensi, $bunga_per_bulan, 
            $calc['total_bunga'], $calc['total_pembayaran'], $calc['angsuran_pokok'], 
            $calc['angsuran_bunga'], $calc['angsuran_total'],
            $tanggal_akad, $tanggal_jatuh_tempo, $tujuan_pinjaman, $jaminan, $user['id']
        ]);
        
        if ($result) {
            $new_pinjaman_result = query("SELECT * FROM pinjaman WHERE id = LAST_INSERT_ID()");
            $new_pinjaman = is_array($new_pinjaman_result) && isset($new_pinjaman_result[0]) ? $new_pinjaman_result[0] : null;
            echo json_encode([
                'success' => true,
                'message' => 'Pengajuan pinjaman berhasil dibuat',
                'data' => $new_pinjaman
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Gagal membuat pengajuan pinjaman']);
        }
        break;
        
    case 'PUT':
        // Update pinjaman (approve/reject/update jaminan status)
        $input = json_decode(file_get_contents('php://input'), true);
        $pinjaman_id = $_GET['id'] ?? $input['id'] ?? '';
        $action = $_GET['action'] ?? '';
        
        // Handle jaminan_status update directly from body
        if (isset($input['jaminan_status'])) {
            $jaminan_status = $input['jaminan_status'];
            $catatan_status = $input['catatan_status'] ?? '';
            
            if (!$pinjaman_id) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'ID required']);
                break;
            }
            
            $valid_statuses = ['aktif', 'dilepas', 'terjual', 'hilang'];
            if (!in_array($jaminan_status, $valid_statuses)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Invalid jaminan_status']);
                break;
            }
            
            $result = query("UPDATE pinjaman SET jaminan_status = ? WHERE id = ?", 
                [$jaminan_status, $pinjaman_id]);
            
            if ($result) {
                // Log the update
                logAudit('update', 'pinjaman', $pinjaman_id, null, ['jaminan_status' => $jaminan_status, 'catatan' => $catatan_status]);
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Status jaminan berhasil diupdate'
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'Gagal mengupdate status jaminan']);
            }
            break;
        }
        
        if (!$pinjaman_id || !$action) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'ID and action required']);
            break;
        }
        
        // Get pinjaman data
        $pinjaman = query("SELECT * FROM pinjaman WHERE id = ?", [$pinjaman_id]);
        if (!$pinjaman) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Pinjaman tidak ditemukan']);
            break;
        }
        
        $pinjaman = $pinjaman[0];
        
        switch ($action) {
            case 'approve':
                if ($pinjaman['status'] !== 'pengajuan') {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'error' => 'Hanya dapat menyetujui pinjaman dengan status pengajuan']);
                    break;
                }
                
                // Update status to disetujui
                $result = query("UPDATE pinjaman SET status = 'disetujui' WHERE id = ?", [$pinjaman_id]);
                
                if ($result) {
                    // Create loan schedule
                    $frek = $pinjaman['frekuensi'] ?? 'bulanan';
                    createLoanSchedule($pinjaman_id, $pinjaman['plafon'], $pinjaman['tenor'], $pinjaman['bunga_per_bulan'], $pinjaman['tanggal_akad'], $frek);
                    
                    // Update to aktif
                    query("UPDATE pinjaman SET status = 'aktif' WHERE id = ?", [$pinjaman_id]);
                    
                    // Post accounting journal entry
                    postJurnalPinjaman($pinjaman_id, $kantor_id);
                    
                    echo json_encode([
                        'success' => true,
                        'message' => 'Pinjaman berhasil disetujui dan diaktifkan'
                    ]);
                } else {
                    http_response_code(500);
                    echo json_encode(['success' => false, 'error' => 'Gagal menyetujui pinjaman']);
                }
                break;
                
            case 'reject':
                if ($pinjaman['status'] !== 'pengajuan') {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'error' => 'Hanya dapat menolak pinjaman dengan status pengajuan']);
                    break;
                }
                
                $result = query("UPDATE pinjaman SET status = 'ditolak' WHERE id = ?", [$pinjaman_id]);
                
                echo json_encode(['success' => true, 'message' => 'Pinjaman ditolak']);
                break;
                
            default:
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Invalid action']);
                break;
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        break;
}
?>
