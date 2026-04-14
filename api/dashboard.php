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
        // Get dashboard statistics
        $total_nasabah = query("SELECT COUNT(*) as total FROM nasabah WHERE cabang_id = ? AND status = 'aktif'", [$cabang_id])[0]['total'];
        $total_pinjaman = query("SELECT COUNT(*) as total FROM pinjaman WHERE cabang_id = ? AND status = 'aktif'", [$cabang_id])[0]['total'];
        $outstanding = query("SELECT SUM(plafon) as total FROM pinjaman WHERE cabang_id = ? AND status = 'aktif'", [$cabang_id])[0]['total'];
        $late_payments = count(checkLatePayments());
        
        // Get recent activities
        $recent_activities = query("
            SELECT 
                CASE 
                    WHEN p.id IS NOT NULL THEN CONCAT('Pinjaman ', p.kode_pinjaman, ' untuk ', n.nama)
                    WHEN pemb.id IS NOT NULL THEN CONCAT('Pembayaran ', pemb.jumlah, ' dari ', n.nama)
                    ELSE 'Aktivitas lain'
                END as activity,
                created_at
            FROM (
                SELECT id, kode_pinjaman, nasabah_id, created_at FROM pinjaman WHERE cabang_id = ?
                UNION ALL
                SELECT id, NULL as kode_pinjaman, angsuran_id as nasabah_id, created_at FROM pembayaran WHERE cabang_id = ?
            ) recent
            LEFT JOIN pinjaman p ON recent.id = p.id AND recent.kode_pinjaman IS NOT NULL
            LEFT JOIN pembayaran pemb ON recent.id = pemb.id AND pemb.kode_pinjaman IS NULL
            LEFT JOIN nasabah n ON 
                (p.nasabah_id = n.id OR pemb.angsuran_id IN (SELECT id FROM angsuran WHERE pinjaman_id = p.id))
            ORDER BY created_at DESC
            LIMIT 5
        ", [$cabang_id, $cabang_id]);
        
        // Get loan statistics
        $loan_stats = query("
            SELECT 
                COUNT(*) as total_pinjaman,
                SUM(CASE WHEN status = 'pengajuan' THEN 1 ELSE 0 END) as pengajuan,
                SUM(CASE WHEN status = 'disetujui' THEN 1 ELSE 0 END) as disetujui,
                SUM(CASE WHEN status = 'aktif' THEN 1 ELSE 0 END) as aktif,
                SUM(CASE WHEN status = 'lunas' THEN 1 ELSE 0 END) as lunas,
                SUM(plafon) as total_plafon
            FROM pinjaman 
            WHERE cabang_id = ?
        ", [$cabang_id])[0];
        
        // Get installment statistics
        $installment_stats = query("
            SELECT 
                COUNT(*) as total_angsuran,
                SUM(CASE WHEN status = 'belum' THEN 1 ELSE 0 END) as belum,
                SUM(CASE WHEN status = 'lunas' THEN 1 ELSE 0 END) as lunas,
                SUM(CASE WHEN status = 'telat' THEN 1 ELSE 0 END) as telat,
                SUM(total_angsuran) as total_tagihan,
                SUM(total_bayar) as total_dibayar,
                SUM(denda) as total_denda
            FROM angsuran 
            WHERE cabang_id = ?
        ", [$cabang_id])[0];
        
        echo json_encode([
            'success' => true,
            'data' => [
                'summary' => [
                    'total_nasabah' => (int)$total_nasabah,
                    'total_pinjaman' => (int)$total_pinjaman,
                    'outstanding' => (float)$outstanding,
                    'late_payments' => (int)$late_payments
                ],
                'recent_activities' => array_map(function($item) {
                    return [
                        'activity' => $item['activity'],
                        'created_at' => $item['created_at'],
                        'formatted_date' => formatDate($item['created_at'], 'd M Y H:i')
                    ];
                }, $recent_activities),
                'loan_statistics' => [
                    'total_pinjaman' => (int)$loan_stats['total_pinjaman'],
                    'pengajuan' => (int)$loan_stats['pengajuan'],
                    'disetujui' => (int)$loan_stats['disetujui'],
                    'aktif' => (int)$loan_stats['aktif'],
                    'lunas' => (int)$loan_stats['lunas'],
                    'total_plafon' => (float)$loan_stats['total_plafon']
                ],
                'installment_statistics' => [
                    'total_angsuran' => (int)$installment_stats['total_angsuran'],
                    'belum' => (int)$installment_stats['belum'],
                    'lunas' => (int)$installment_stats['lunas'],
                    'telat' => (int)$installment_stats['telat'],
                    'total_tagihan' => (float)$installment_stats['total_tagihan'],
                    'total_dibayar' => (float)$installment_stats['total_dibayar'],
                    'total_denda' => (float)$installment_stats['total_denda']
                ]
            ]
        ]);
        break;
        
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        break;
}
?>
