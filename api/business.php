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

        default:
            http_response_code(400);
            echo json_encode(['error' => "Action '{$action}' tidak dikenali"]);
    }
    exit();
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);
