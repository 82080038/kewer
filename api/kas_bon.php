<?php
/**
 * API: Kas Bon (Employee Cash Advance)
 * 
 * Endpoints for managing employee cash advances
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

require_once __DIR__ . '/../config/path.php';
require_once BASE_PATH . '/includes/functions.php';
require_once BASE_PATH . '/includes/kas_bon.php';

// Authentication check
$headers = getallheaders();
$token = $headers['Authorization'] ?? '';
if ($token !== 'Bearer kewer-api-token-2024') {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$method = $_SERVER['REQUEST_METHOD'];
$cabangId = getCurrentCabang();
$kasBon = new KasBon($cabangId);

switch ($method) {
    case 'GET':
        $action = $_GET['action'] ?? 'list';
        
        switch ($action) {
            case 'list':
                // Get all kas bon records
                $filters = [
                    'karyawan_id' => $_GET['karyawan_id'] ?? null,
                    'status' => $_GET['status'] ?? null,
                    'tanggal_mulai' => $_GET['tanggal_mulai'] ?? null,
                    'tanggal_selesai' => $_GET['tanggal_selesai'] ?? null
                ];
                
                $records = $kasBon->getAll($filters);
                
                echo json_encode([
                    'success' => true,
                    'data' => $records
                ]);
                break;
                
            case 'detail':
                // Get kas bon by ID
                $id = $_GET['id'] ?? null;
                if (!$id) {
                    http_response_code(400);
                    echo json_encode(['error' => 'ID kas bon diperlukan']);
                    exit();
                }
                
                $detail = $kasBon->getById($id);
                
                if ($detail) {
                    $detail['potongan_history'] = $kasBon->getPotonganHistory($id);
                    echo json_encode([
                        'success' => true,
                        'data' => $detail
                    ]);
                } else {
                    http_response_code(404);
                    echo json_encode(['error' => 'Kas bon tidak ditemukan']);
                }
                break;
                
            case 'pending':
                // Get pending requests
                $pending = $kasBon->getPendingRequests();
                
                echo json_encode([
                    'success' => true,
                    'data' => $pending
                ]);
                break;
                
            case 'balance':
                // Get employee balance
                $karyawanId = $_GET['karyawan_id'] ?? null;
                if (!$karyawanId) {
                    http_response_code(400);
                    echo json_encode(['error' => 'karyawan_id diperlukan']);
                    exit();
                }
                
                $balance = $kasBon->getEmployeeBalance($karyawanId);
                
                echo json_encode([
                    'success' => true,
                    'data' => $balance
                ]);
                break;
                
            case 'check':
                // Check if employee can request kas bon
                $karyawanId = $_GET['karyawan_id'] ?? null;
                $jumlah = $_GET['jumlah'] ?? null;
                
                if (!$karyawanId || !$jumlah) {
                    http_response_code(400);
                    echo json_encode(['error' => 'karyawan_id dan jumlah diperlukan']);
                    exit();
                }
                
                $check = $kasBon->canRequestKasBon($karyawanId, $jumlah);
                
                echo json_encode([
                    'success' => true,
                    'data' => $check
                ]);
                break;
                
            case 'statistics':
                // Get kas bon statistics
                $stats = $kasBon->getStatistics();
                
                echo json_encode([
                    'success' => true,
                    'data' => $stats
                ]);
                break;
                
            case 'monthly_report':
                // Get monthly deduction report
                $bulan = $_GET['bulan'] ?? date('n');
                $tahun = $_GET['tahun'] ?? date('Y');
                
                $report = $kasBon->getMonthlyReport($bulan, $tahun);
                
                echo json_encode([
                    'success' => true,
                    'data' => $report
                ]);
                break;
                
            default:
                http_response_code(400);
                echo json_encode(['error' => 'Invalid action. Use: list, detail, pending, balance, check, statistics, monthly_report']);
        }
        break;
        
    case 'POST':
        $action = $_GET['action'] ?? 'create';
        
        switch ($action) {
            case 'create':
                // Create new kas bon request
                $input = json_decode(file_get_contents('php://input'), true);
                
                // Check if employee can request
                $check = $kasBon->canRequestKasBon($input['karyawan_id'], $input['jumlah']);
                if (!$check['can_request']) {
                    echo json_encode([
                        'success' => false,
                        'error' => 'Melebihi limit kasbon',
                        'limit' => $check['limit'],
                        'sisa_limit' => $check['sisa_limit']
                    ]);
                    exit();
                }
                
                // Generate kode kasbon
                $kodeKasbon = generateKode('KSB', 'kas_bon', 'kode_kasbon');
                
                $result = $kasBon->createKasBon([
                    'karyawan_id' => $input['karyawan_id'],
                    'kode_kasbon' => $kodeKasbon,
                    'jumlah' => $input['jumlah'],
                    'tenor_bulan' => $input['tenor_bulan'] ?? 1,
                    'tujuan' => $input['tujuan'],
                    'catatan' => $input['catatan'] ?? null
                ]);
                
                if ($result) {
                    echo json_encode([
                        'success' => true,
                        'message' => 'Kas bon berhasil diajukan',
                        'id' => $result,
                        'kode_kasbon' => $kodeKasbon
                    ]);
                } else {
                    http_response_code(500);
                    echo json_encode(['error' => 'Gagal mengajukan kas bon']);
                }
                break;
                
            case 'potong':
                // Record monthly deduction
                $input = json_decode(file_get_contents('php://input'), true);
                
                $result = $kasBon->recordPotongan(
                    $input['kas_bon_id'],
                    $input['bulan_potong'],
                    $input['jumlah_potong'],
                    $input['potong_oleh'],
                    $input['catatan'] ?? null
                );
                
                if ($result) {
                    // Update kas bon status
                    $kasBon->updateAfterPotongan($input['kas_bon_id']);
                    
                    echo json_encode([
                        'success' => true,
                        'message' => 'Potongan berhasil dicatat'
                    ]);
                } else {
                    http_response_code(500);
                    echo json_encode(['error' => 'Gagal mencatat potongan']);
                }
                break;
                
            default:
                http_response_code(400);
                echo json_encode(['error' => 'Invalid action. Use: create, potong']);
        }
        break;
        
    case 'PUT':
        $action = $_GET['action'] ?? null;
        $id = $_GET['id'] ?? null;
        
        if (!$id) {
            http_response_code(400);
            echo json_encode(['error' => 'ID kas bon diperlukan']);
            exit();
        }
        
        switch ($action) {
            case 'approve':
                // Approve kas bon request
                $userId = getCurrentUser();
                $result = $kasBon->approveKasBon($id, $userId['id']);
                
                if ($result) {
                    echo json_encode([
                        'success' => true,
                        'message' => 'Kas bon berhasil disetujui'
                    ]);
                } else {
                    http_response_code(500);
                    echo json_encode(['error' => 'Gagal menyetujui kas bon']);
                }
                break;
                
            case 'reject':
                // Reject kas bon request
                $input = json_decode(file_get_contents('php://input'), true);
                $userId = getCurrentUser();
                
                $result = $kasBon->rejectKasBon($id, $userId['id'], $input['alasan'] ?? null);
                
                if ($result) {
                    echo json_encode([
                        'success' => true,
                        'message' => 'Kas bon berhasil ditolak'
                    ]);
                } else {
                    http_response_code(500);
                    echo json_encode(['error' => 'Gagal menolak kas bon']);
                }
                break;
                
            case 'give':
                // Give kas bon to employee
                $result = $kasBon->giveKasBon($id);
                
                if ($result) {
                    echo json_encode([
                        'success' => true,
                        'message' => 'Kas bon berhasil diberikan'
                    ]);
                } else {
                    http_response_code(500);
                    echo json_encode(['error' => 'Gagal memberikan kas bon']);
                }
                break;
                
            default:
                http_response_code(400);
                echo json_encode(['error' => 'Invalid action. Use: approve, reject, give']);
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
}
?>
