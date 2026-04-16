<?php
/**
 * API: Angsuran (Installments)
 * 
 * Endpoints for managing loan installments
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

require_once __DIR__ . '/../config/path.php';
require_once BASE_PATH . '/includes/functions.php';

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

switch ($method) {
    case 'GET':
        // Get installments with filters
        $pinjaman_id = $_GET['pinjaman_id'] ?? null;
        $status = $_GET['status'] ?? null;
        $tanggal_mulai = $_GET['tanggal_mulai'] ?? null;
        $tanggal_selesai = $_GET['tanggal_selesai'] ?? null;
        
        $where = ["a.cabang_id = ?"];
        $params = [$cabangId];
        
        if ($pinjaman_id) {
            $where[] = "a.pinjaman_id = ?";
            $params[] = $pinjaman_id;
        }
        
        if ($status) {
            $where[] = "a.status = ?";
            $params[] = $status;
        }
        
        if ($tanggal_mulai) {
            $where[] = "a.jatuh_tempo >= ?";
            $params[] = $tanggal_mulai;
        }
        
        if ($tanggal_selesai) {
            $where[] = "a.jatuh_tempo <= ?";
            $params[] = $tanggal_selesai;
        }
        
        $where_clause = "WHERE " . implode(" AND ", $where);
        
        $sql = "SELECT a.*, n.nama as nama_nasabah, p.kode_pinjaman, u.nama as nama_petugas
                FROM angsuran a
                JOIN pinjaman p ON a.pinjaman_id = p.id
                JOIN nasabah n ON p.nasabah_id = n.id
                LEFT JOIN users u ON a.petugas_id = u.id
                $where_clause
                ORDER BY a.jatuh_tempo ASC, a.no_angsuran ASC";
        
        $angsuran = query($sql, $params);
        
        echo json_encode([
            'success' => true,
            'data' => $angsuran
        ]);
        break;
        
    case 'POST':
        // Create new installment record (usually auto-generated)
        $input = json_decode(file_get_contents('php://input'), true);
        
        $sql = "INSERT INTO angsuran (cabang_id, pinjaman_id, no_angsuran, jatuh_tempo, pokok, bunga, total_angsuran)
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $result = query($sql, [
            $input['cabang_id'] ?? $cabangId,
            $input['pinjaman_id'],
            $input['no_angsuran'],
            $input['jatuh_tempo'],
            $input['pokok'],
            $input['bunga'],
            $input['total_angsuran']
        ]);
        
        if ($result) {
            echo json_encode([
                'success' => true,
                'message' => 'Angsuran berhasil ditambahkan',
                'id' => $result
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Gagal menambahkan angsuran']);
        }
        break;
        
    case 'PUT':
        // Update installment (mark as paid)
        $input = json_decode(file_get_contents('php://input'), true);
        $id = $_GET['id'] ?? null;
        
        if (!$id) {
            http_response_code(400);
            echo json_encode(['error' => 'ID angsuran diperlukan']);
            exit();
        }
        
        $sql = "UPDATE angsuran 
                SET status = ?, tanggal_bayar = ?, total_bayar = ?, petugas_id = ?, updated_at = NOW()
                WHERE id = ?";
        
        $result = query($sql, [
            $input['status'],
            $input['tanggal_bayar'],
            $input['total_bayar'],
            $input['petugas_id'],
            $id
        ]);
        
        if ($result) {
            echo json_encode([
                'success' => true,
                'message' => 'Angsuran berhasil diupdate'
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Gagal mengupdate angsuran']);
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
}
?>
