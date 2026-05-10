<?php
/**
 * API: Business Logic Endpoint
 * Handles: restrukturisasi, write-off, pelunasan dipercepat,
 *          nasabah meninggal, pengganti petugas, verifikasi kas,
 *          kelebihan bayar, notifikasi, offline queue
 *
 * POST /api/business.php
 * Body: { "action": "...", ...params }
 */

error_reporting(0);
ini_set('display_errors', 0);
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit(); }

try {
    require_once __DIR__ . '/../config/path.php';
    require_once BASE_PATH . '/includes/functions.php';
    require_once BASE_PATH . '/includes/business_logic.php';
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Configuration error: ' . $e->getMessage()]);
    exit();
}

requireLogin();
$user   = getCurrentUser();
$method = $_SERVER['REQUEST_METHOD'];

// ── GET: ambil data ───────────────────────────────────────────────
if ($method === 'GET') {
    $action = $_GET['action'] ?? '';

    switch ($action) {
        // Hitung simulasi pelunasan dipercepat
        case 'hitung_lunas_dipercepat':
            $pinjaman_id = (int)($_GET['pinjaman_id'] ?? 0);
            if (!$pinjaman_id) { http_response_code(400); echo json_encode(['error' => 'pinjaman_id wajib']); exit(); }
            echo json_encode(hitungPelunasanDipercepat($pinjaman_id, $_GET['tanggal'] ?? null));
            break;

        // Cek kelayakan nasabah untuk pinjaman baru
        case 'cek_kelayakan_nasabah':
            $nasabah_id  = (int)($_GET['nasabah_id'] ?? 0);
            $owner_bos_id = getOwnerBosId();
            echo json_encode(cekKelayakanNasabah($nasabah_id, $owner_bos_id));
            break;

        // Notifikasi user yang login
        case 'notifikasi':
            $bos_cabang = getBosOwnedCabangIds();
            $cabang_id  = $user['cabang_id'] ?? ($bos_cabang[0] ?? null);
            $limit      = min((int)($_GET['limit'] ?? 20), 50);
            echo json_encode([
                'success' => true,
                'data'    => getNotifikasiUser($user['id'], $user['role'], $cabang_id, $limit),
            ]);
            break;

        // Antrian offline petugas
        case 'offline_queue':
            if (!hasPermission('manage_pembayaran')) { http_response_code(403); echo json_encode(['error' => 'Forbidden']); exit(); }
            $petugas_id = (int)($_GET['petugas_id'] ?? $user['id']);
            $status     = $_GET['status'] ?? 'pending';
            $rows = query("SELECT oq.*, n.nama as nama_nasabah, p.kode_pinjaman FROM pembayaran_offline_queue oq
                JOIN pinjaman p ON oq.pinjaman_id = p.id JOIN nasabah n ON p.nasabah_id = n.id
                WHERE oq.petugas_id = ? AND oq.status = ? ORDER BY oq.tanggal_kutip DESC",
                [$petugas_id, $status]) ?: [];
            echo json_encode(['success' => true, 'data' => $rows]);
            break;

        // Kelebihan bayar
        case 'kelebihan_bayar':
            if (!hasPermission('manage_pembayaran') && !hasPermission('view_laporan')) { http_response_code(403); echo json_encode(['error' => 'Forbidden']); exit(); }
            $cabang_filter = buildCabangFilter('n.cabang_id');
            $where = $cabang_filter ? "WHERE " . ltrim($cabang_filter['clause'], 'AND ') : "";
            $params = $cabang_filter ? $cabang_filter['params'] : [];
            $rows = query("SELECT kb.*, n.nama as nama_nasabah, n.kode_nasabah FROM kelebihan_bayar kb
                JOIN nasabah n ON kb.nasabah_id = n.id $where ORDER BY kb.created_at DESC", $params) ?: [];
            echo json_encode(['success' => true, 'data' => $rows]);
            break;

        // Riwayat skor kredit nasabah
        case 'skor_kredit':
            $nasabah_id = (int)($_GET['nasabah_id'] ?? 0);
            if (!$nasabah_id) { http_response_code(400); echo json_encode(['error' => 'nasabah_id wajib']); exit(); }
            $rows = query("SELECT * FROM riwayat_skor_kredit WHERE nasabah_id = ? ORDER BY created_at DESC LIMIT 30", [$nasabah_id]) ?: [];
            echo json_encode(['success' => true, 'data' => $rows]);
            break;

        // Billing plans for settings page
        case 'billing_plans':
            if ($user['role'] !== 'appOwner') { http_response_code(403); echo json_encode(['error' => 'Forbidden']); exit(); }
            $plans = query("SELECT * FROM billing_plans ORDER BY harga_bulanan") ?: [];
            echo json_encode(['success' => true, 'data' => $plans]);
            break;

        // Bank accounts for settings page
        case 'bank_accounts':
            if ($user['role'] !== 'appOwner') { http_response_code(403); echo json_encode(['error' => 'Forbidden']); exit(); }
            $accounts = query("SELECT * FROM platform_bank_accounts ORDER BY is_primary DESC, created_at DESC") ?: [];
            echo json_encode(['success' => true, 'data' => $accounts]);
            break;

        // Platform stats for settings page
        case 'platform_stats':
            if ($user['role'] !== 'appOwner') { http_response_code(403); echo json_encode(['error' => 'Forbidden']); exit(); }
            $total_koperasi = query("SELECT COUNT(*) as c FROM bos_registrations WHERE status = 'approved'");
            $total_koperasi = (is_array($total_koperasi) && isset($total_koperasi[0])) ? (int)$total_koperasi[0]['c'] : 0;
            $total_invoices = query("SELECT COUNT(*) as c FROM koperasi_invoices");
            $total_invoices = (is_array($total_invoices) && isset($total_invoices[0])) ? (int)$total_invoices[0]['c'] : 0;
            $total_advice = query("SELECT COUNT(*) as c FROM ai_advice");
            $total_advice = (is_array($total_advice) && isset($total_advice[0])) ? (int)$total_advice[0]['c'] : 0;
            echo json_encode(['success' => true, 'data' => [
                'total_koperasi' => $total_koperasi,
                'total_invoices' => $total_invoices,
                'total_advice' => $total_advice
            ]]);
            break;

        // App owner dashboard stats
        case 'app_owner_dashboard':
            if ($user['role'] !== 'appOwner') { http_response_code(403); echo json_encode(['error' => 'Forbidden']); exit(); }
            
            $total_pending = query("SELECT COUNT(*) as c FROM bos_registrations WHERE status = 'pending'");
            $total_pending = (is_array($total_pending) && isset($total_pending[0])) ? (int)$total_pending[0]['c'] : 0;
            
            $total_approved = query("SELECT COUNT(*) as c FROM bos_registrations WHERE status = 'approved'");
            $total_approved = (is_array($total_approved) && isset($total_approved[0])) ? (int)$total_approved[0]['c'] : 0;
            
            $total_bos = query("SELECT COUNT(*) as c FROM users WHERE role = 'bos' AND status = 'aktif'");
            $total_bos = (is_array($total_bos) && isset($total_bos[0])) ? (int)$total_bos[0]['c'] : 0;
            
            $total_users = query("SELECT COUNT(*) as c FROM users WHERE role != 'appOwner' AND status = 'aktif'");
            $total_users = (is_array($total_users) && isset($total_users[0])) ? (int)$total_users[0]['c'] : 0;
            
            $total_revenue = query("SELECT COALESCE(SUM(total),0) as c FROM koperasi_invoices WHERE status = 'dibayar'");
            $total_revenue = (is_array($total_revenue) && isset($total_revenue[0])) ? (float)$total_revenue[0]['c'] : 0;
            
            $overdue_invoices = query("SELECT COUNT(*) as c FROM koperasi_invoices WHERE status = 'overdue'");
            $overdue_invoices = (is_array($overdue_invoices) && isset($overdue_invoices[0])) ? (int)$overdue_invoices[0]['c'] : 0;
            
            $today_usage = query("SELECT COALESCE(SUM(total_api_calls),0) as api, COALESCE(SUM(total_renders),0) as renders FROM usage_daily_summary WHERE tanggal = CURDATE()");
            $today_api = (is_array($today_usage) && isset($today_usage[0])) ? (int)$today_usage[0]['api'] : 0;
            $today_renders = (is_array($today_usage) && isset($today_usage[0])) ? (int)$today_usage[0]['renders'] : 0;
            
            $recent = query("SELECT id, username, nama, nama_usaha, status, created_at FROM bos_registrations ORDER BY created_at DESC LIMIT 10");
            if (!is_array($recent)) $recent = [];
            
            $recent_advice = query("SELECT id, judul, kategori, prioritas, created_at FROM ai_advice ORDER BY created_at DESC LIMIT 5");
            if (!is_array($recent_advice)) $recent_advice = [];
            
            echo json_encode(['success' => true, 'data' => [
                'stats' => [
                    'total_pending' => $total_pending,
                    'total_approved' => $total_approved,
                    'total_bos' => $total_bos,
                    'total_users' => $total_users,
                    'total_revenue' => $total_revenue,
                    'overdue_invoices' => $overdue_invoices,
                    'today_api' => $today_api,
                    'today_renders' => $today_renders
                ],
                'recent_registrations' => $recent,
                'recent_advice' => $recent_advice
            ]]);
            break;

        // Get bos registrations for approvals page
        case 'bos_registrations':
            if ($user['role'] !== 'appOwner') { http_response_code(403); echo json_encode(['error' => 'Forbidden']); exit(); }
            
            $filter = $_GET['status'] ?? 'pending';
            $valid_filters = ['pending', 'approved', 'rejected', 'all'];
            if (!in_array($filter, $valid_filters)) $filter = 'pending';
            
            if ($filter === 'all') {
                $registrations = query("SELECT * FROM bos_registrations ORDER BY created_at DESC");
            } else {
                $registrations = query("SELECT * FROM bos_registrations WHERE status = ? ORDER BY created_at DESC", [$filter]);
            }
            if (!is_array($registrations)) $registrations = [];
            
            $cnt_pending = query("SELECT COUNT(*) as c FROM bos_registrations WHERE status = 'pending'");
            $cnt_pending = (is_array($cnt_pending) && isset($cnt_pending[0])) ? (int)$cnt_pending[0]['c'] : 0;
            $cnt_approved = query("SELECT COUNT(*) as c FROM bos_registrations WHERE status = 'approved'");
            $cnt_approved = (is_array($cnt_approved) && isset($cnt_approved[0])) ? (int)$cnt_approved[0]['c'] : 0;
            $cnt_rejected = query("SELECT COUNT(*) as c FROM bos_registrations WHERE status = 'rejected'");
            $cnt_rejected = (is_array($cnt_rejected) && isset($cnt_rejected[0])) ? (int)$cnt_rejected[0]['c'] : 0;
            
            echo json_encode(['success' => true, 'data' => [
                'registrations' => $registrations,
                'stats' => [
                    'pending' => $cnt_pending,
                    'approved' => $cnt_approved,
                    'rejected' => $cnt_rejected
                ]
            ]]);
            break;

        // Get billing data for billing page
        case 'billing_data':
            if ($user['role'] !== 'appOwner') { http_response_code(403); echo json_encode(['error' => 'Forbidden']); exit(); }
            
            $filter_status = $_GET['status'] ?? 'all';
            $filter_month = (int)($_GET['bulan'] ?? date('n'));
            $filter_year = (int)($_GET['tahun'] ?? date('Y'));
            
            $where = "WHERE ki.periode_bulan = $filter_month AND ki.periode_tahun = $filter_year";
            if ($filter_status !== 'all') {
                $where .= " AND ki.status = '" . addslashes($filter_status) . "'";
            }
            
            $invoices = query("
                SELECT ki.*, br.nama_usaha, bp.nama as plan_nama, bp.tipe as plan_tipe, u.nama as bos_nama
                FROM koperasi_invoices ki
                JOIN koperasi_billing kb ON kb.id = ki.koperasi_billing_id
                JOIN billing_plans bp ON bp.id = kb.billing_plan_id
                JOIN users u ON u.id = ki.bos_user_id
                LEFT JOIN bos_registrations br ON br.username = u.username
                $where
                ORDER BY ki.created_at DESC
            ");
            if (!is_array($invoices)) $invoices = [];
            
            // Summary
            $sum_total = 0; $sum_paid = 0;
            foreach ($invoices as $inv) {
                $sum_total += (float)$inv['total'];
                if ($inv['status'] === 'dibayar') $sum_paid += (float)$inv['total'];
            }
            
            // Billing plans summary
            $plans = query("SELECT bp.*, COUNT(kb.id) as subscribers FROM billing_plans bp LEFT JOIN koperasi_billing kb ON kb.billing_plan_id = bp.id AND kb.status = 'aktif' WHERE bp.is_active = 1 GROUP BY bp.id ORDER BY bp.harga_bulanan");
            if (!is_array($plans)) $plans = [];
            
            // Get primary bank account
            $primary_bank = query("SELECT * FROM platform_bank_accounts WHERE is_primary = 1 AND is_active = 1 LIMIT 1");
            $primary_bank = (is_array($primary_bank) && !empty($primary_bank)) ? $primary_bank[0] : null;
            
            echo json_encode(['success' => true, 'data' => [
                'invoices' => $invoices,
                'plans' => $plans,
                'primary_bank' => $primary_bank,
                'summary' => [
                    'total' => $sum_total,
                    'paid' => $sum_paid
                ],
                'filter' => [
                    'month' => $filter_month,
                    'year' => $filter_year,
                    'status' => $filter_status
                ]
            ]]);
            break;

        default:
            http_response_code(400);
            echo json_encode(['error' => 'Action tidak dikenali']);
    }
    exit();
}

// ── POST: aksi bisnis ─────────────────────────────────────────────
if ($method === 'POST') {
    $input  = json_decode(file_get_contents('php://input'), true) ?? [];
    $action = trim($input['action'] ?? '');

    switch ($action) {

        // ─── Nasabah meninggal ────────────────────────────────────
        case 'nasabah_meninggal':
            if (!hasPermission('manage_nasabah')) { http_response_code(403); echo json_encode(['error' => 'Forbidden']); exit(); }
            $nasabah_id = (int)($input['nasabah_id'] ?? 0);
            $tanggal    = $input['tanggal_meninggal'] ?? date('Y-m-d');
            $catatan    = $input['catatan'] ?? '';
            if (!$nasabah_id) { http_response_code(400); echo json_encode(['error' => 'nasabah_id wajib']); exit(); }
            echo json_encode(prosesNasabahMeninggal($nasabah_id, $tanggal, $catatan, $user['id']));
            break;

        // ─── Ahli waris ───────────────────────────────────────────
        case 'tambah_ahli_waris':
            if (!hasPermission('manage_nasabah')) { http_response_code(403); echo json_encode(['error' => 'Forbidden']); exit(); }
            $req = ['nasabah_id', 'nama', 'hubungan'];
            foreach ($req as $f) { if (empty($input[$f])) { http_response_code(400); echo json_encode(['error' => "$f wajib"]); exit(); } }
            $result = query(
                "INSERT INTO ahli_waris (nasabah_id, nama, hubungan, ktp, telp, alamat, adalah_penjamin, catatan) VALUES (?,?,?,?,?,?,?,?)",
                [$input['nasabah_id'], $input['nama'], $input['hubungan'], $input['ktp'] ?? null,
                 $input['telp'] ?? null, $input['alamat'] ?? null, (int)($input['adalah_penjamin'] ?? 0), $input['catatan'] ?? null]
            );
            echo json_encode(['success' => (bool)$result]);
            break;

        // ─── Restrukturisasi ──────────────────────────────────────
        case 'restrukturisasi':
            if (!hasPermission('pinjaman.approve')) { http_response_code(403); echo json_encode(['error' => 'Forbidden — butuh pinjaman.approve']); exit(); }
            $pinjaman_id = (int)($input['pinjaman_id'] ?? 0);
            $tipe        = $input['tipe'] ?? '';
            if (!$pinjaman_id || !$tipe) { http_response_code(400); echo json_encode(['error' => 'pinjaman_id dan tipe wajib']); exit(); }
            echo json_encode(buatRestrukturisasi($pinjaman_id, $tipe, $input, $user['id']));
            break;

        // ─── Hitung & proses pelunasan dipercepat ─────────────────
        case 'lunas_dipercepat':
            if (!hasPermission('manage_pembayaran')) { http_response_code(403); echo json_encode(['error' => 'Forbidden']); exit(); }
            $pinjaman_id = (int)($input['pinjaman_id'] ?? 0);
            if (!$pinjaman_id) { http_response_code(400); echo json_encode(['error' => 'pinjaman_id wajib']); exit(); }
            $hitungan = hitungPelunasanDipercepat($pinjaman_id, $input['tanggal'] ?? null);
            if (isset($hitungan['error'])) { http_response_code(400); echo json_encode($hitungan); exit(); }
            // Jika confirm=true, eksekusi pelunasan
            if (!empty($input['confirm'])) {
                $kode = 'LNS-' . date('Ymd') . '-' . $pinjaman_id;
                $r = query(
                    "INSERT INTO pembayaran (cabang_id, pinjaman_id, angsuran_id, kode_pembayaran, jumlah_bayar, denda, total_bayar, tanggal_bayar, cara_bayar, petugas_id, keterangan)
                     SELECT p.cabang_id, p.id, a.id, ?, ?, ?, ?, ?, ?, ?, 'Pelunasan dipercepat'
                     FROM pinjaman p JOIN angsuran a ON a.pinjaman_id = p.id WHERE p.id = ? AND a.status != 'lunas' ORDER BY a.no_angsuran ASC LIMIT 1",
                    [$kode, $hitungan['total_harus_dibayar'], $hitungan['denda_terhutang'],
                     $hitungan['total_harus_dibayar'], $input['tanggal'] ?? date('Y-m-d'),
                     $input['cara_bayar'] ?? 'tunai', $user['id'], $pinjaman_id]
                );
                query("UPDATE angsuran SET status='lunas' WHERE pinjaman_id = ? AND status != 'lunas'", [$pinjaman_id]);
                query("UPDATE pinjaman SET status='lunas', tanggal_lunas=?, sisa_pokok_berjalan=0, updated_at=NOW() WHERE id=?", [$input['tanggal'] ?? date('Y-m-d'), $pinjaman_id]);
                $result_pinjaman = query("SELECT nasabah_id FROM pinjaman WHERE id=?", [$pinjaman_id]);
                $nasabah_id = is_array($result_pinjaman) && isset($result_pinjaman[0]) ? (int)$result_pinjaman[0]['nasabah_id'] : null;
                if ($nasabah_id) {
                    updateSkorKredit($nasabah_id, +5, 'bayar_tepat_waktu', $pinjaman_id);
                }
                $hitungan['lunas'] = true;
            }
            echo json_encode(array_merge(['success' => true], $hitungan));
            break;

        // ─── Write-off pinjaman macet ─────────────────────────────
        case 'write_off':
            if ($user['role'] !== 'bos') { http_response_code(403); echo json_encode(['error' => 'Hanya bos yang bisa melakukan write-off']); exit(); }
            $pinjaman_id = (int)($input['pinjaman_id'] ?? 0);
            if (!$pinjaman_id) { http_response_code(400); echo json_encode(['error' => 'pinjaman_id wajib']); exit(); }
            echo json_encode(prosesWriteOff($pinjaman_id, $input, $user['id']));
            break;

        // ─── Pengganti petugas (legacy alias → assign_pengganti) ──
        case 'pengganti_petugas':
            if (!hasPermission('manage_users') && !hasPermission('manage_kas')) { http_response_code(403); echo json_encode(['error' => 'Forbidden']); exit(); }
            $pt_id  = (int)($input['petugas_asli_id'] ?? $input['petugas_id'] ?? 0);
            $pg_id  = (int)($input['petugas_pengganti_id'] ?? $input['pengganti_id'] ?? 0);
            $tgl_m  = $input['tanggal'] ?? $input['tanggal_mulai'] ?? date('Y-m-d');
            $alasan = $input['alasan'] ?? $input['alasan_ketidakhadiran'] ?? 'lainnya';
            if (!$pt_id || !$pg_id) { http_response_code(400); echo json_encode(['error' => 'petugas_id dan pengganti_id wajib']); exit(); }
            echo json_encode(buatPenggantiPetugas($user['cabang_id'] ?? 1, $pt_id, $pg_id, $tgl_m, $tgl_m, $alasan, $input['catatan'] ?? null, $user['id']));
            break;

        // ─── Verifikasi kas petugas ───────────────────────────────
        case 'verifikasi_kas':
            if (!hasPermission('manage_kas') && !hasPermission('manage_laporan') && !in_array($user['role'], ['bos','manager_pusat','manager_cabang','admin_pusat'])) {
                http_response_code(403); echo json_encode(['error' => 'Forbidden']); exit();
            }
            $kas_id = (int)($input['kas_id'] ?? 0);
            if (!$kas_id) { http_response_code(400); echo json_encode(['error' => 'kas_id wajib']); exit(); }
            $ket_selisih = $input['keterangan_selisih'] ?? null;
            // Simpan keterangan selisih dulu jika ada
            if ($ket_selisih) {
                query("UPDATE kas_petugas SET selisih_keterangan = ? WHERE id = ?", [$ket_selisih, $kas_id]);
            }
            echo json_encode(verifikasiKasPetugas($kas_id, $user['id']));
            break;

        // ─── Proses kelebihan bayar ───────────────────────────────
        case 'proses_kelebihan_bayar':
            if (!hasPermission('manage_pembayaran')) { http_response_code(403); echo json_encode(['error' => 'Forbidden']); exit(); }
            $kb_id   = (int)($input['kelebihan_bayar_id'] ?? 0);
            $aksi    = $input['aksi'] ?? ''; // 'dikembalikan' atau 'dikompensasi'
            if (!$kb_id || !in_array($aksi, ['dikembalikan', 'dikompensasi'])) {
                http_response_code(400); echo json_encode(['error' => 'kelebihan_bayar_id dan aksi (dikembalikan/dikompensasi) wajib']); exit();
            }
            $r = query("UPDATE kelebihan_bayar SET status = ?, diproses_oleh = ?, tanggal_proses = CURDATE(), keterangan = ?, updated_at = NOW() WHERE id = ? AND status = 'pending'",
                [$aksi, $user['id'], $input['keterangan'] ?? null, $kb_id]);
            echo json_encode(['success' => (bool)$r]);
            break;

        // ─── Baca notifikasi ──────────────────────────────────────
        case 'baca_notifikasi':
            $notif_id = (int)($input['notifikasi_id'] ?? 0);
            if (!$notif_id) { http_response_code(400); echo json_encode(['error' => 'notifikasi_id wajib']); exit(); }
            echo json_encode(['success' => (bool)bacaNotifikasi($notif_id, $user['id'])]);
            break;

        // ─── Tambah antrian offline ───────────────────────────────
        case 'tambah_offline_queue':
            if (!hasPermission('manage_pembayaran')) { http_response_code(403); echo json_encode(['error' => 'Forbidden']); exit(); }
            $req = ['nasabah_id', 'pinjaman_id', 'angsuran_id', 'jumlah_bayar', 'tanggal_kutip'];
            foreach ($req as $f) { if (empty($input[$f])) { http_response_code(400); echo json_encode(['error' => "$f wajib"]); exit(); } }
            $r = query(
                "INSERT INTO pembayaran_offline_queue (cabang_id, petugas_id, nasabah_id, pinjaman_id, angsuran_id, jumlah_bayar, tanggal_kutip, cara_bayar, keterangan, device_id)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
                [$user['cabang_id'], $user['id'], $input['nasabah_id'], $input['pinjaman_id'], $input['angsuran_id'],
                 $input['jumlah_bayar'], $input['tanggal_kutip'], $input['cara_bayar'] ?? 'tunai',
                 $input['keterangan'] ?? null, $input['device_id'] ?? null]
            );
            echo json_encode(['success' => (bool)$r, 'id' => $r ? query("SELECT LAST_INSERT_ID() as id")[0]['id'] : null]);
            break;

        // ─── Proses antrian offline ───────────────────────────────
        case 'proses_offline_queue':
            if (!hasPermission('manage_pembayaran') && $user['role'] !== 'admin_pusat') { http_response_code(403); echo json_encode(['error' => 'Forbidden']); exit(); }
            $queue_id = (int)($input['queue_id'] ?? 0);
            if (!$queue_id) { http_response_code(400); echo json_encode(['error' => 'queue_id wajib']); exit(); }
            echo json_encode(prosesOfflineQueue($queue_id, $user['id']));
            break;

        // ─── Blacklist platform (hanya appOwner) ──────────────────
        case 'platform_blacklist':
            if ($user['role'] !== 'appOwner') { http_response_code(403); echo json_encode(['error' => 'Hanya appOwner yang bisa blacklist platform-wide']); exit(); }
            $nasabah_id = (int)($input['nasabah_id'] ?? 0);
            $aktifkan   = (bool)($input['aktifkan'] ?? true);
            if (!$nasabah_id) { http_response_code(400); echo json_encode(['error' => 'nasabah_id wajib']); exit(); }
            query("UPDATE nasabah SET platform_blacklist = ? WHERE id = ?", [(int)$aktifkan, $nasabah_id]);
            logAudit('platform_blacklist', 'nasabah', $nasabah_id, [], ['aktifkan' => $aktifkan, 'alasan' => $input['alasan'] ?? '']);
            echo json_encode(['success' => true]);
            break;

        // ─── Auto tandai macet (admin/manager) ───────────────────
        case 'auto_tandai_macet':
            if (!hasPermission('manage_laporan') && $user['role'] !== 'bos') { http_response_code(403); echo json_encode(['error' => 'Forbidden']); exit(); }
            $cabang_id = (int)($input['cabang_id'] ?? 0) ?: null;
            $jumlah    = autoTandaiMacet($cabang_id);
            echo json_encode(['success' => true, 'ditandai' => $jumlah]);
            break;

        // ─── Kunci kas petugas ────────────────────────────────────
        case 'kunci_kas':
            if (!hasPermission('manage_kas') && !in_array($user['role'], ['bos', 'manager_pusat', 'manager_cabang'])) {
                http_response_code(403); echo json_encode(['error' => 'Forbidden']); exit();
            }
            $kas_id = (int)($input['kas_id'] ?? 0);
            if (!$kas_id) { http_response_code(400); echo json_encode(['error' => 'kas_id wajib']); exit(); }
            $kas = query("SELECT * FROM kas_petugas WHERE id = ?", [$kas_id]);
            if (!$kas) { http_response_code(404); echo json_encode(['error' => 'Kas tidak ditemukan']); exit(); }
            if ($kas[0]['is_locked']) { echo json_encode(['success' => false, 'error' => 'Kas sudah terkunci']); exit(); }
            query("UPDATE kas_petugas SET is_locked = 1, status = 'locked', updated_at = NOW() WHERE id = ?", [$kas_id]);
            logAudit('kunci_kas', 'kas_petugas', $kas_id, ['is_locked' => 0], ['is_locked' => 1]);
            echo json_encode(['success' => true, 'message' => 'Kas berhasil dikunci']);
            break;

        // ─── Assign pengganti petugas ─────────────────────────────
        case 'assign_pengganti':
            if (!hasPermission('manage_kas') && !hasPermission('manage_users')) {
                http_response_code(403); echo json_encode(['error' => 'Forbidden']); exit();
            }
            $petugas_id    = (int)($input['petugas_id'] ?? 0);
            $pengganti_id  = (int)($input['pengganti_id'] ?? 0);
            $tgl_mulai     = $input['tanggal_mulai'] ?? date('Y-m-d');
            $tgl_selesai   = $input['tanggal_selesai'] ?? date('Y-m-d');
            $alasan        = $input['alasan_ketidakhadiran'] ?? 'sakit';
            $catatan       = $input['catatan'] ?? null;
            $cab_id        = (int)($input['cabang_id'] ?? $user['cabang_id'] ?? 1);
            if (!$petugas_id || !$pengganti_id) { http_response_code(400); echo json_encode(['error' => 'petugas_id dan pengganti_id wajib']); exit(); }
            if ($petugas_id === $pengganti_id) { http_response_code(400); echo json_encode(['error' => 'Petugas dan pengganti tidak boleh sama']); exit(); }
            $r = buatPenggantiPetugas($cab_id, $petugas_id, $pengganti_id, $tgl_mulai, $tgl_selesai, $alasan, $catatan, $user['id']);
            echo json_encode($r);
            break;

        // ─── Tambah ahli waris ────────────────────────────────────
        case 'tambah_ahli_waris':
            if (!hasPermission('manage_nasabah')) { http_response_code(403); echo json_encode(['error' => 'Forbidden']); exit(); }
            $nasabah_id      = (int)($input['nasabah_id'] ?? 0);
            $nama_aw         = trim($input['nama'] ?? '');
            $hubungan        = $input['hubungan'] ?? 'lainnya';
            if (!$nasabah_id || !$nama_aw) { http_response_code(400); echo json_encode(['error' => 'nasabah_id dan nama wajib']); exit(); }
            // Validasi nasabah milik koperasi yang login
            $ns = query("SELECT owner_bos_id FROM nasabah WHERE id = ?", [$nasabah_id]);
            if (!$ns) { http_response_code(404); echo json_encode(['error' => 'Nasabah tidak ditemukan']); exit(); }
            if ($ns[0]['owner_bos_id'] && $ns[0]['owner_bos_id'] != getOwnerBosId() && $user['role'] !== 'appOwner') {
                http_response_code(403); echo json_encode(['error' => 'Nasabah bukan milik koperasi Anda']); exit();
            }
            $r = query(
                "INSERT INTO ahli_waris (nasabah_id, nama, hubungan, ktp, telp, adalah_penjamin, created_by)
                 VALUES (?, ?, ?, ?, ?, ?, ?)",
                [$nasabah_id, $nama_aw, $hubungan,
                 $input['ktp'] ?? null, $input['telp'] ?? null,
                 (int)($input['adalah_penjamin'] ?? 0), $user['id']]
            );
            echo json_encode(['success' => (bool)$r]);
            break;

        // ─── Proses lunas dipercepat (konfirmasi) ─────────────────
        case 'lunas_dipercepat':
            if (!hasPermission('manage_pembayaran')) { http_response_code(403); echo json_encode(['error' => 'Forbidden']); exit(); }
            $pinjaman_id = (int)($input['pinjaman_id'] ?? 0);
            if (!$pinjaman_id || empty($input['confirm'])) { http_response_code(400); echo json_encode(['error' => 'pinjaman_id dan confirm wajib']); exit(); }
            $cara_bayar = $input['cara_bayar'] ?? 'tunai';
            $r = prosesLunasDipercepat($pinjaman_id, $user['id'], $cara_bayar);
            echo json_encode($r);
            break;

        // ─── Approve bos registration (appOwner only) ───────────────
        case 'approve_bos_registration':
            if ($user['role'] !== 'appOwner') { http_response_code(403); echo json_encode(['error' => 'Forbidden']); exit(); }
            $registration_id = (int)($input['registration_id'] ?? 0);
            if (!$registration_id) { http_response_code(400); echo json_encode(['error' => 'registration_id wajib']); exit(); }
            
            $registration = query("SELECT * FROM bos_registrations WHERE id = ?", [$registration_id]);
            if (!is_array($registration) || empty($registration)) {
                http_response_code(404); echo json_encode(['error' => 'Pendaftaran tidak ditemukan']); exit();
            }
            
            $reg = $registration[0];
            if ($reg['status'] !== 'pending') {
                http_response_code(400); echo json_encode(['error' => 'Pendaftaran sudah diproses']); exit();
            }
            
            // Get default billing plan (STARTER)
            $default_plan = query("SELECT id FROM billing_plans WHERE kode = 'STARTER' AND is_active = 1 LIMIT 1");
            $default_plan_id = (is_array($default_plan) && !empty($default_plan)) ? (int)$default_plan[0]['id'] : null;
            
            // Create user account
            $user_result = query(
                "INSERT INTO users (username, password, nama, email, telp, role, status) VALUES (?, ?, ?, ?, ?, 'bos', 'aktif')",
                [$reg['username'], $reg['password'], $reg['nama'], $reg['email'] ?? '', $reg['telp'] ?? '']
            );
            
            if ($user_result) {
                $bos_user_id = query("SELECT LAST_INSERT_ID() as id")[0]['id'];
                
                // Auto-assign default billing plan
                if ($default_plan_id) {
                    query("INSERT INTO koperasi_billing (bos_user_id, billing_plan_id, status, tanggal_mulai, created_by) VALUES (?, ?, 'aktif', CURDATE(), ?)",
                        [$bos_user_id, $default_plan_id, $user['id']]);
                }
                
                query("UPDATE bos_registrations SET status = 'approved', approved_at = NOW(), approved_by = ? WHERE id = ?",
                    [$user['id'], $registration_id]);
                
                $plan_info = $default_plan_id ? " dengan plan STARTER" : " (belum ada billing plan)";
                echo json_encode(['success' => true, 'message' => "Bos '{$reg['nama']}' ({$reg['nama_usaha']}) berhasil disetujui. User aktif dengan username: {$reg['username']}{$plan_info}"]);
            } else {
                http_response_code(500); echo json_encode(['error' => 'Gagal membuat akun user Bos']); exit();
            }
            break;

        // ─── Reject bos registration (appOwner only) ────────────────
        case 'reject_bos_registration':
            if ($user['role'] !== 'appOwner') { http_response_code(403); echo json_encode(['error' => 'Forbidden']); exit(); }
            $registration_id = (int)($input['registration_id'] ?? 0);
            $rejection_reason = trim($input['rejection_reason'] ?? '');
            if (!$registration_id) { http_response_code(400); echo json_encode(['error' => 'registration_id wajib']); exit(); }
            
            $registration = query("SELECT * FROM bos_registrations WHERE id = ?", [$registration_id]);
            if (!is_array($registration) || empty($registration)) {
                http_response_code(404); echo json_encode(['error' => 'Pendaftaran tidak ditemukan']); exit();
            }
            
            $reg = $registration[0];
            if ($reg['status'] !== 'pending') {
                http_response_code(400); echo json_encode(['error' => 'Pendaftaran sudah diproses']); exit();
            }
            
            query("UPDATE bos_registrations SET status = 'rejected', rejected_reason = ?, approved_by = ?, approved_at = NOW() WHERE id = ?",
                [$rejection_reason, $user['id'], $registration_id]);
            
            echo json_encode(['success' => true, 'message' => "Pendaftaran '{$reg['nama']}' ditolak."]);
            break;

        // ─── Generate invoices (appOwner only) ─────────────────────────
        case 'generate_invoices':
            if ($user['role'] !== 'appOwner') { http_response_code(403); echo json_encode(['error' => 'Forbidden']); exit(); }
            $bulan = (int)($input['bulan'] ?? date('n'));
            $tahun = (int)($input['tahun'] ?? date('Y'));
            $generated = 0;

            $active_billings = query("SELECT kb.*, bp.* FROM koperasi_billing kb JOIN billing_plans bp ON bp.id = kb.billing_plan_id WHERE kb.status = 'aktif'");
            if (is_array($active_billings)) {
                foreach ($active_billings as $kb) {
                    // Check if invoice already exists
                    $exists = query("SELECT id FROM koperasi_invoices WHERE koperasi_billing_id = ? AND periode_bulan = ? AND periode_tahun = ?",
                        [$kb['id'], $bulan, $tahun]);
                    if (is_array($exists) && !empty($exists)) continue;

                    $biaya_fixed = 0; $biaya_persen = 0; $biaya_usage = 0;
                    $keuntungan = 0; $total_api = 0; $total_renders = 0;

                    if ($kb['tipe'] === 'fixed') {
                        $biaya_fixed = (float)$kb['harga_bulanan'];
                    } elseif ($kb['tipe'] === 'percentage') {
                        // Get koperasi profit from pembayaran this month
                        $profit = query("SELECT COALESCE(SUM(total_bayar),0) as p FROM pembayaran WHERE petugas_id IN (SELECT id FROM users WHERE (owner_bos_id = ? OR id = ?)) AND MONTH(tanggal_bayar) = ? AND YEAR(tanggal_bayar) = ?",
                            [$kb['bos_user_id'], $kb['bos_user_id'], $bulan, $tahun]);
                        $keuntungan = (is_array($profit) && isset($profit[0])) ? (float)$profit[0]['p'] : 0;
                        $biaya_persen = $keuntungan * ((float)$kb['persentase_keuntungan'] / 100);
                    } elseif ($kb['tipe'] === 'usage') {
                        $usage = query("SELECT COALESCE(SUM(total_api_calls),0) as api, COALESCE(SUM(total_renders),0) as renders FROM usage_daily_summary WHERE bos_user_id = ? AND MONTH(tanggal) = ? AND YEAR(tanggal) = ?",
                            [$kb['bos_user_id'], $bulan, $tahun]);
                        if (is_array($usage) && isset($usage[0])) {
                            $total_api = (int)$usage[0]['api'];
                            $total_renders = (int)$usage[0]['renders'];
                        }
                        $billable_api = max(0, $total_api - (int)$kb['api_call_gratis']);
                        $billable_renders = max(0, $total_renders - (int)$kb['render_gratis']);
                        $biaya_usage = ($billable_api * (float)$kb['harga_per_api_call']) + ($billable_renders * (float)$kb['harga_per_render']);
                    }

                    $subtotal = $biaya_fixed + $biaya_persen + $biaya_usage;
                    $kode = 'INV-' . $tahun . str_pad($bulan, 2, '0', STR_PAD_LEFT) . '-' . str_pad($kb['bos_user_id'], 4, '0', STR_PAD_LEFT);

                    query("INSERT INTO koperasi_invoices (koperasi_billing_id, bos_user_id, kode_invoice, periode_bulan, periode_tahun, biaya_fixed, biaya_persentase, keuntungan_koperasi, biaya_usage, total_api_calls, total_renders, subtotal, total, status, tanggal_terbit, tanggal_jatuh_tempo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'terbit', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 14 DAY))",
                        [$kb['id'], $kb['bos_user_id'], $kode, $bulan, $tahun, $biaya_fixed, $biaya_persen, $keuntungan, $biaya_usage, $total_api, $total_renders, $subtotal, $subtotal]);
                    $generated++;
                }
            }
            echo json_encode(['success' => true, 'message' => "$generated invoice berhasil di-generate untuk periode $bulan/$tahun."]);
            break;

        // ─── Mark invoice as paid (appOwner only) ─────────────────────
        case 'mark_invoice_paid':
            if ($user['role'] !== 'appOwner') { http_response_code(403); echo json_encode(['error' => 'Forbidden']); exit(); }
            $invoice_id = (int)($input['invoice_id'] ?? 0);
            $metode = $input['metode_bayar'] ?? 'transfer';
            if ($invoice_id) {
                query("UPDATE koperasi_invoices SET status = 'dibayar', tanggal_bayar = CURDATE(), metode_bayar = ? WHERE id = ?", [$metode, $invoice_id]);
                echo json_encode(['success' => true, 'message' => 'Invoice ditandai sebagai dibayar.']);
            } else {
                http_response_code(400); echo json_encode(['error' => 'invoice_id wajib']); exit();
            }
            break;

        default:
            http_response_code(400);
            echo json_encode(['error' => "Action '{$action}' tidak dikenali"]);
    }
    exit();
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);
