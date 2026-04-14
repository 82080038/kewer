<?php
require_once '../includes/functions.php';

// Get current cabang from query parameter
$cabang_id = $_GET['cabang_id'] ?? null;
if (!$cabang_id) {
    http_response_code(400);
    echo json_encode(['error' => 'cabang_id parameter required']);
    exit();
}

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        // Get pinjaman list
        $search = $_GET['search'] ?? '';
        $status = $_GET['status'] ?? '';
        
        $where = ["p.cabang_id = ?"];
        $params = [$cabang_id];
        
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
        
        $where_clause = "WHERE " . implode(" AND ", $where);
        
        $pinjaman = query("
            SELECT p.*, n.nama, n.kode_nasabah 
            FROM pinjaman p 
            JOIN nasabah n ON p.nasabah_id = n.id 
            $where_clause 
            ORDER BY p.created_at DESC
        ", $params);
        
        echo json_encode([
            'success' => true,
            'data' => $pinjaman,
            'total' => count($pinjaman)
        ]);
        break;
        
    case 'POST':
        // Create new pinjaman
        $input = json_decode(file_get_contents('php://input'), true);
        
        $nasabah_id = $input['nasabah_id'] ?? '';
        $plafon = $input['plafon'] ?? '';
        $tenor = $input['tenor'] ?? '';
        $bunga_per_bulan = $input['bunga_per_bulan'] ?? '';
        $tanggal_akad = $input['tanggal_akad'] ?? '';
        $tujuan_pinjaman = $input['tujuan_pinjaman'] ?? '';
        $jaminan = $input['jaminan'] ?? '';
        
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
        
        if (!is_numeric($tenor) || $tenor <= 0 || $tenor > 12) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Tenor harus antara 1-12 bulan']);
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
        $calc = calculateLoan($plafon, $tenor, $bunga_per_bulan);
        
        // Generate kode pinjaman
        $kode_pinjaman = generateKode('PNJ', 'pinjaman', 'kode_pinjaman');
        
        // Calculate due date
        $tanggal_jatuh_tempo = date('Y-m-d', strtotime("+$tenor month", strtotime($tanggal_akad)));
        
        // Insert pinjaman
        $result = query("INSERT INTO pinjaman (
            cabang_id, kode_pinjaman, nasabah_id, plafon, tenor, bunga_per_bulan, 
            total_bunga, total_pembayaran, angsuran_pokok, angsuran_bunga, angsuran_total,
            tanggal_akad, tanggal_jatuh_tempo, tujuan_pinjaman, jaminan, status, petugas_id
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pengajuan', ?)", [
            $cabang_id, $kode_pinjaman, $nasabah_id, $plafon, $tenor, $bunga_per_bulan,
            $calc['total_bunga'], $calc['total_pembayaran'], $calc['angsuran_pokok'], 
            $calc['angsuran_bunga'], $calc['angsuran_total'], $tanggal_akad, 
            $tanggal_jatuh_tempo, $tujuan_pinjaman, $jaminan, 1 // Default petugas ID for API
        ]);
        
        if ($result) {
            $new_pinjaman = query("SELECT * FROM pinjaman WHERE id = LAST_INSERT_ID()")[0];
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
        // Update pinjaman (approve/reject)
        $pinjaman_id = $_GET['id'] ?? '';
        $action = $_GET['action'] ?? '';
        
        if (!$pinjaman_id || !$action) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'ID and action required']);
            break;
        }
        
        // Get pinjaman data
        $pinjaman = query("SELECT * FROM pinjaman WHERE id = ? AND cabang_id = ?", [$pinjaman_id, $cabang_id]);
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
                    createLoanSchedule($pinjaman_id, $pinjaman['plafon'], $pinjaman['tenor'], $pinjaman['bunga_per_bulan'], $pinjaman['tanggal_akad']);
                    
                    // Update to aktif
                    query("UPDATE pinjaman SET status = 'aktif' WHERE id = ?", [$pinjaman_id]);
                    
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
                
                if ($result) {
                    echo json_encode([
                        'success' => true,
                        'message' => 'Pinjaman berhasil ditolak'
                    ]);
                } else {
                    http_response_code(500);
                    echo json_encode(['success' => false, 'error' => 'Gagal menolak pinjaman']);
                }
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
