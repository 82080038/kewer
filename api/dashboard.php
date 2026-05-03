<?php
// Suppress errors to ensure JSON output
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

try {
    require_once __DIR__ . '/../config/path.php';
    require_once BASE_PATH . '/includes/functions.php';
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
        // Get dashboard statistics
        $nasabah_result = query("SELECT COUNT(*) as total FROM nasabah WHERE status = 'aktif'");
        $total_nasabah = is_array($nasabah_result) && isset($nasabah_result[0]) ? $nasabah_result[0]['total'] : 0;

        $pinjaman_result = query("SELECT COUNT(*) as total FROM pinjaman WHERE status = 'aktif'");
        $total_pinjaman = is_array($pinjaman_result) && isset($pinjaman_result[0]) ? $pinjaman_result[0]['total'] : 0;

        $outstanding_result = query("SELECT SUM(plafon) as total FROM pinjaman WHERE status = 'aktif'");
        $outstanding = is_array($outstanding_result) && isset($outstanding_result[0]) ? $outstanding_result[0]['total'] : 0;

        $late_payments = count(checkLatePayments());
        
        // Get recent activities
        $recent_activities = [];
        try {
            // Get recent pinjaman
            $recent_pinjaman = query("SELECT CONCAT('Pinjaman ', kode_pinjaman, ' untuk ', n.nama) as activity, p.created_at FROM pinjaman p LEFT JOIN nasabah n ON p.nasabah_id = n.id ORDER BY p.created_at DESC LIMIT 3");
            
            // Get recent pembayaran
            $recent_pembayaran = query("SELECT CONCAT('Pembayaran ', jumlah_bayar, ' dari ', n.nama) as activity, pemb.created_at FROM pembayaran pemb LEFT JOIN angsuran a ON pemb.angsuran_id = a.id LEFT JOIN pinjaman p ON a.pinjaman_id = p.id LEFT JOIN nasabah n ON p.nasabah_id = n.id ORDER BY pemb.created_at DESC LIMIT 3");
            
            // Merge and sort by date
            $recent_activities = array_merge($recent_pinjaman, $recent_pembayaran);
            usort($recent_activities, function($a, $b) {
                return strtotime($b['created_at']) - strtotime($a['created_at']);
            });
            $recent_activities = array_slice($recent_activities, 0, 5);
        } catch (Exception $e) {
            $recent_activities = [];
        }
        
        // Get loan statistics
        $loan_stats_result = query("
            SELECT 
                COUNT(*) as total_pinjaman,
                SUM(CASE WHEN status = 'pengajuan' THEN 1 ELSE 0 END) as pengajuan,
                SUM(CASE WHEN status = 'disetujui' THEN 1 ELSE 0 END) as disetujui,
                SUM(CASE WHEN status = 'aktif' THEN 1 ELSE 0 END) as aktif,
                SUM(CASE WHEN status = 'lunas' THEN 1 ELSE 0 END) as lunas,
                SUM(plafon) as total_plafon
            FROM pinjaman 
            WHERE cabang_id = ?
        ", [$cabang_id]);
        $loan_stats = is_array($loan_stats_result) && isset($loan_stats_result[0]) ? $loan_stats_result[0] : [
            'total_pinjaman' => 0, 'pengajuan' => 0, 'disetujui' => 0, 
            'aktif' => 0, 'lunas' => 0, 'total_plafon' => 0
        ];
        
        // Get installment statistics
        $installment_stats_result = query("
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
        ", [$cabang_id]);
        $installment_stats = is_array($installment_stats_result) && isset($installment_stats_result[0]) ? $installment_stats_result[0] : [
            'total_angsuran' => 0, 'belum' => 0, 'lunas' => 0, 
            'telat' => 0, 'total_tagihan' => 0, 'total_dibayar' => 0, 'total_denda' => 0
        ];
        
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
