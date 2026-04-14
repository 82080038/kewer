<?php
/**
 * API: Kas Petugas (Cash Tracking)
 * 
 * Endpoints for tracking cash held by field officers
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

require_once '../includes/functions.php';
require_once '../includes/kas_petugas.php';

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
$kasPetugas = new KasPetugas($cabangId);

switch ($method) {
    case 'GET':
        // Get all records or specific date
        $tanggal = $_GET['tanggal'] ?? null;
        $records = $kasPetugas->getBranchRecords($tanggal);
        
        echo json_encode([
            'success' => true,
            'data' => $records
        ]);
        break;
        
    case 'POST':
        // Create new daily record
        $input = json_decode(file_get_contents('php://input'), true);
        
        $result = $kasPetugas->createDailyRecord([
            'cabang_id' => $input['cabang_id'] ?? $cabangId,
            'petugas_id' => $input['petugas_id'],
            'tanggal' => $input['tanggal'],
            'saldo_awal' => $input['saldo_awal'] ?? 0,
            'total_terima' => $input['total_terima'] ?? 0,
            'total_disetor' => $input['total_disetor'] ?? 0,
            'catatan' => $input['catatan'] ?? null
        ]);
        
        if ($result) {
            echo json_encode([
                'success' => true,
                'message' => 'Record kas petugas berhasil dibuat',
                'id' => $result
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Gagal membuat record kas petugas']);
        }
        break;
        
    case 'PUT':
        // Update record
        $input = json_decode(file_get_contents('php://input'), true);
        $id = $_GET['id'] ?? null;
        
        if (!$id) {
            http_response_code(400);
            echo json_encode(['error' => 'ID record diperlukan']);
            exit();
        }
        
        $result = $kasPetugas->updateRecord($id, [
            'total_terima' => $input['total_terima'],
            'total_disetor' => $input['total_disetor'],
            'catatan' => $input['catatan'] ?? null
        ]);
        
        if ($result) {
            echo json_encode([
                'success' => true,
                'message' => 'Record kas petugas berhasil diupdate'
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Gagal mengupdate record kas petugas']);
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
}
?>
