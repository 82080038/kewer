<?php
require_once __DIR__ . '/../config/path.php';
require_once BASE_PATH . '/includes/functions.php';

// Get current cabang from query parameter (for API)
$cabang_id = $_GET['cabang_id'] ?? null;
if (!$cabang_id) {
    // For API, require cabang_id parameter
    http_response_code(400);
    echo json_encode(['error' => 'cabang_id parameter required']);
    exit();
}

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        // Get nasabah list
        $search = $_GET['search'] ?? '';
        $status = $_GET['status'] ?? '';
        
        $where = ["n.cabang_id = ?"];
        $params = [$cabang_id];
        
        if ($search) {
            $where[] = "(n.nama LIKE ? OR n.kode_nasabah LIKE ? OR n.ktp LIKE ? OR n.telp LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
        
        if ($status) {
            $where[] = "n.status = ?";
            $params[] = $status;
        }
        
        $where_clause = "WHERE " . implode(" AND ", $where);
        
        $nasabah = query("
            SELECT n.*, c.nama_cabang 
            FROM nasabah n 
            LEFT JOIN cabang c ON n.cabang_id = c.id 
            $where_clause 
            ORDER BY n.created_at DESC
        ", $params);
        
        echo json_encode([
            'success' => true,
            'data' => $nasabah,
            'total' => count($nasabah)
        ]);
        break;
        
    case 'POST':
        // Create new nasabah
        $input = json_decode(file_get_contents('php://input'), true);
        
        $nama = $input['nama'] ?? '';
        $alamat = $input['alamat'] ?? '';
        $province_id = $input['province_id'] ?? '';
        $regency_id = $input['regency_id'] ?? '';
        $district_id = $input['district_id'] ?? '';
        $village_id = $input['village_id'] ?? '';
        $ktp = $input['ktp'] ?? '';
        $telp = $input['telp'] ?? '';
        $jenis_usaha = $input['jenis_usaha'] ?? '';
        $lokasi_pasar = $input['lokasi_pasar'] ?? '';
        
        // Validation
        if (!validateKTP($ktp)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Format KTP tidak valid']);
            break;
        }
        
        if (!validatePhone($telp)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Format telepon tidak valid']);
            break;
        }
        
        // Check duplicate KTP
        $check = query("SELECT id FROM nasabah WHERE ktp = ?", [$ktp]);
        if ($check) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'KTP sudah terdaftar']);
            break;
        }
        
        // Generate kode nasabah
        $kode_nasabah = generateKode('NSB', 'nasabah', 'kode_nasabah');
        
        // Insert nasabah
        $result = query("INSERT INTO nasabah (cabang_id, kode_nasabah, nama, alamat, province_id, regency_id, district_id, village_id, ktp, telp, jenis_usaha, lokasi_pasar) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)", [
            $cabang_id, $kode_nasabah, $nama, $alamat, $province_id ?: null, $regency_id ?: null, $district_id ?: null, $village_id ?: null, $ktp, $telp, $jenis_usaha, $lokasi_pasar
        ]);
        
        if ($result) {
            $new_nasabah = query("SELECT * FROM nasabah WHERE id = LAST_INSERT_ID()")[0];
            echo json_encode([
                'success' => true,
                'message' => 'Nasabah berhasil ditambahkan',
                'data' => $new_nasabah
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Gagal menambahkan nasabah']);
        }
        break;
        
    case 'PUT':
        // Update nasabah
        $nasabah_id = $_GET['id'] ?? '';
        if (!$nasabah_id) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'ID required']);
            break;
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        // Check if nasabah exists
        $nasabah = query("SELECT * FROM nasabah WHERE id = ? AND cabang_id = ?", [$nasabah_id, $cabang_id]);
        if (!$nasabah) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Nasabah tidak ditemukan']);
            break;
        }
        
        // Update fields
        $fields = [];
        $params = [];
        
        $updatable_fields = ['nama', 'alamat', 'province_id', 'regency_id', 'district_id', 'village_id', 'telp', 'jenis_usaha', 'lokasi_pasar', 'status'];
        
        foreach ($updatable_fields as $field) {
            if (isset($input[$field])) {
                $fields[] = "$field = ?";
                $params[] = $input[$field];
            }
        }
        
        if (empty($fields)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'No fields to update']);
            break;
        }
        
        $params[] = $nasabah_id;
        
        $result = query("UPDATE nasabah SET " . implode(', ', $fields) . " WHERE id = ?", $params);
        
        if ($result) {
            $updated_nasabah_result = query("SELECT * FROM nasabah WHERE id = ?", [$nasabah_id]);
            $updated_nasabah = is_array($updated_nasabah_result) && isset($updated_nasabah_result[0]) ? $updated_nasabah_result[0] : null;
            echo json_encode([
                'success' => true,
                'message' => 'Data nasabah berhasil diperbarui',
                'data' => $updated_nasabah
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Gagal memperbarui data nasabah']);
        }
        break;
        
    case 'DELETE':
        // Delete nasabah
        $nasabah_id = $_GET['id'] ?? '';
        if (!$nasabah_id) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'ID required']);
            break;
        }
        
        // Check if nasabah exists and has no active loans
        $nasabah = query("SELECT * FROM nasabah WHERE id = ? AND cabang_id = ?", [$nasabah_id, $cabang_id]);
        if (!$nasabah) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Nasabah tidak ditemukan']);
            break;
        }
        
        $active_loans_result = query("SELECT COUNT(*) as count FROM pinjaman WHERE nasabah_id = ? AND status IN ('pengajuan', 'disetujui', 'aktif')", [$nasabah_id]);
        $active_loans = is_array($active_loans_result) && isset($active_loans_result[0]) ? $active_loans_result[0]['count'] : 0;
        
        if ($active_loans > 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Tidak dapat menghapus nasabah yang masih memiliki pinjaman aktif']);
            break;
        }
        
        $result = query("DELETE FROM nasabah WHERE id = ?", [$nasabah_id]);
        
        if ($result) {
            echo json_encode([
                'success' => true,
                'message' => 'Nasabah berhasil dihapus'
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Gagal menghapus nasabah']);
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        break;
}
?>
?>
