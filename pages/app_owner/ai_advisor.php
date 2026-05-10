<?php
require_once __DIR__ . '/../../config/path.php';
require_once BASE_PATH . '/includes/functions.php';
requireLogin();
$user = getCurrentUser();
$page_title = 'AI Advisor';

$success = $_SESSION['success'] ?? '';
unset($_SESSION['success']);

// Handle POST — generate AI advice
if ($_POST && validateCsrfToken($_POST['csrf_token'] ?? '')) {
    $action = $_POST['action'] ?? '';

    if ($action === 'generate_advice') {
        $target_bos = $_POST['bos_user_id'] ?? '';
        $generated = 0;

        // Get koperasi list to analyze
        if ($target_bos === 'all' || empty($target_bos)) {
            $bos_list = query("SELECT u.id, u.nama, br.nama_usaha FROM users u LEFT JOIN bos_registrations br ON br.username = u.username WHERE u.role = 'bos' AND u.status = 'aktif'");
        } else {
            $bos_list = query("SELECT u.id, u.nama, br.nama_usaha FROM users u LEFT JOIN bos_registrations br ON br.username = u.username WHERE u.id = ?", [$target_bos]);
        }
        if (!is_array($bos_list)) $bos_list = [];

        foreach ($bos_list as $bos) {
            $advice_items = generateAdviceForKoperasi($bos['id'], $bos['nama'], $bos['nama_usaha'] ?? '');
            foreach ($advice_items as $adv) {
                query("INSERT INTO ai_advice (bos_user_id, kategori, judul, isi, prioritas, data_pendukung) VALUES (?, ?, ?, ?, ?, ?)",
                    [$bos['id'], $adv['kategori'], $adv['judul'], $adv['isi'], $adv['prioritas'], json_encode($adv['data'] ?? [])]);
                $generated++;
            }
        }
        $_SESSION['success'] = "$generated saran AI berhasil di-generate.";
        header('Location: ' . baseUrl('pages/app_owner/ai_advisor.php'));
        exit();

    } elseif ($action === 'mark_read') {
        $advice_id = (int)($_POST['advice_id'] ?? 0);
        if ($advice_id) {
            query("UPDATE ai_advice SET status = 'dibaca', dibaca_at = NOW() WHERE id = ?", [$advice_id]);
        }
        header('Location: ' . baseUrl('pages/app_owner/ai_advisor.php'));
        exit();
    }
}

// Get advice list
$filter_kat = $_GET['kategori'] ?? 'all';
$filter_bos = $_GET['bos'] ?? '';

$where = "WHERE 1=1";
if ($filter_kat !== 'all') {
    $where .= " AND a.kategori = '" . addslashes($filter_kat) . "'";
}
if ($filter_bos) {
    $where .= " AND a.bos_user_id = " . (int)$filter_bos;
}

$advice_list = query("
    SELECT a.*, u.nama as bos_nama, br.nama_usaha
    FROM ai_advice a
    LEFT JOIN users u ON u.id = a.bos_user_id
    LEFT JOIN bos_registrations br ON br.username = u.username
    $where
    ORDER BY a.created_at DESC
    LIMIT 50
");
if (!is_array($advice_list)) $advice_list = [];

// Stats
$stat_baru = query("SELECT COUNT(*) as c FROM ai_advice WHERE status = 'baru'");
$stat_baru = (is_array($stat_baru) && isset($stat_baru[0])) ? (int)$stat_baru[0]['c'] : 0;

// Bos list for filter
$bos_for_filter = query("SELECT u.id, u.nama, br.nama_usaha FROM users u LEFT JOIN bos_registrations br ON br.username = u.username WHERE u.role = 'bos' AND u.status = 'aktif' ORDER BY u.nama");
if (!is_array($bos_for_filter)) $bos_for_filter = [];

/**
 * Generate AI advice based on koperasi data analysis
 */
function generateAdviceForKoperasi($bos_id, $bos_nama, $nama_usaha) {
    $advice = [];

    // 1. Analyze nasabah growth
    $nasabah_count = query("SELECT COUNT(*) as c FROM nasabah WHERE cabang_id IN (SELECT cabang_id FROM users WHERE id = ? OR owner_bos_id = ?)", [$bos_id, $bos_id]);
    $nasabah_total = (is_array($nasabah_count) && isset($nasabah_count[0])) ? (int)$nasabah_count[0]['c'] : 0;

    $nasabah_bulan_lalu = query("SELECT COUNT(*) as c FROM nasabah WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) AND cabang_id IN (SELECT cabang_id FROM users WHERE id = ? OR owner_bos_id = ?)", [$bos_id, $bos_id]);
    $nasabah_new = (is_array($nasabah_bulan_lalu) && isset($nasabah_bulan_lalu[0])) ? (int)$nasabah_bulan_lalu[0]['c'] : 0;

    if ($nasabah_total == 0) {
        $advice[] = [
            'kategori' => 'pertumbuhan',
            'judul' => "[$nama_usaha] Belum memiliki nasabah — mulai akuisisi segera",
            'isi' => "Koperasi $nama_usaha belum memiliki nasabah terdaftar. Disarankan untuk segera melakukan:\n\n1. Sosialisasi ke masyarakat sekitar\n2. Kerjasama dengan kelurahan/desa setempat\n3. Tawarkan program pinjaman ringan sebagai daya tarik awal\n4. Gunakan fitur petugas lapangan untuk kunjungan door-to-door",
            'prioritas' => 'kritis',
            'data' => ['nasabah_total' => 0]
        ];
    } elseif ($nasabah_new == 0 && $nasabah_total > 0) {
        $advice[] = [
            'kategori' => 'pertumbuhan',
            'judul' => "[$nama_usaha] Stagnasi — tidak ada nasabah baru dalam 30 hari",
            'isi' => "Koperasi $nama_usaha memiliki $nasabah_total nasabah tapi tidak ada penambahan baru dalam 30 hari terakhir. Saran:\n\n1. Evaluasi produk pinjaman apakah masih kompetitif\n2. Tingkatkan promosi di media sosial\n3. Adakan program referral untuk nasabah existing\n4. Pertimbangkan buka cabang baru di lokasi strategis",
            'prioritas' => 'tinggi',
            'data' => ['nasabah_total' => $nasabah_total, 'nasabah_baru_30d' => 0]
        ];
    }

    // 2. Analyze loan performance
    $pinjaman_aktif = query("SELECT COUNT(*) as c, COALESCE(SUM(plafon),0) as total FROM pinjaman WHERE status = 'aktif' AND cabang_id IN (SELECT cabang_id FROM users WHERE id = ? OR owner_bos_id = ?)", [$bos_id, $bos_id]);
    $pinjaman_cnt = (is_array($pinjaman_aktif) && isset($pinjaman_aktif[0])) ? (int)$pinjaman_aktif[0]['c'] : 0;
    $pinjaman_total = (is_array($pinjaman_aktif) && isset($pinjaman_aktif[0])) ? (float)$pinjaman_aktif[0]['total'] : 0;

    // Check late payments
    $late = query("SELECT COUNT(*) as c FROM angsuran WHERE status = 'belum_bayar' AND jatuh_tempo < CURDATE() AND pinjaman_id IN (SELECT id FROM pinjaman WHERE cabang_id IN (SELECT cabang_id FROM users WHERE id = ? OR owner_bos_id = ?))", [$bos_id, $bos_id]);
    $late_cnt = (is_array($late) && isset($late[0])) ? (int)$late[0]['c'] : 0;

    if ($late_cnt > 0 && $pinjaman_cnt > 0) {
        $late_ratio = round(($late_cnt / max($pinjaman_cnt, 1)) * 100);
        $advice[] = [
            'kategori' => 'risiko',
            'judul' => "[$nama_usaha] $late_cnt angsuran menunggak — tingkat keterlambatan {$late_ratio}%",
            'isi' => "Terdapat $late_cnt angsuran yang sudah melewati jatuh tempo. Rasio keterlambatan terhadap total pinjaman aktif: {$late_ratio}%.\n\nRekomendasi:\n1. Intensifkan penagihan oleh petugas lapangan\n2. Terapkan denda keterlambatan untuk efek jera\n3. Evaluasi proses verifikasi nasabah sebelum pencairan\n4. Pertimbangkan restrukturisasi untuk nasabah yang kesulitan",
            'prioritas' => $late_ratio > 30 ? 'kritis' : ($late_ratio > 15 ? 'tinggi' : 'sedang'),
            'data' => ['late_angsuran' => $late_cnt, 'pinjaman_aktif' => $pinjaman_cnt, 'late_ratio' => $late_ratio]
        ];
    }

    // 3. Branch utilization
    $cabang_count = query("SELECT COUNT(*) as c FROM cabang WHERE id IN (SELECT DISTINCT cabang_id FROM users WHERE id = ? OR owner_bos_id = ?)", [$bos_id, $bos_id]);
    $cabang_cnt = (is_array($cabang_count) && isset($cabang_count[0])) ? (int)$cabang_count[0]['c'] : 0;

    if ($cabang_cnt <= 1 && $nasabah_total > 50) {
        $advice[] = [
            'kategori' => 'pertumbuhan',
            'judul' => "[$nama_usaha] Potensi ekspansi cabang — $nasabah_total nasabah di 1 cabang",
            'isi' => "Dengan $nasabah_total nasabah yang dilayani hanya dari $cabang_cnt cabang, ada potensi untuk ekspansi. Pertimbangkan:\n\n1. Buka cabang di kecamatan/kota terdekat\n2. Analisis sebaran nasabah untuk lokasi optimal\n3. Rekrut manager cabang dari karyawan berprestasi\n4. Mulai dengan kantor kecil untuk minimalisir risiko",
            'prioritas' => 'sedang',
            'data' => ['nasabah' => $nasabah_total, 'cabang' => $cabang_cnt]
        ];
    }

    // 4. Staff efficiency
    $staff_count = query("SELECT COUNT(*) as c FROM users WHERE (owner_bos_id = ? OR id = ?) AND status = 'aktif' AND role != 'bos'", [$bos_id, $bos_id]);
    $staff_cnt = (is_array($staff_count) && isset($staff_count[0])) ? (int)$staff_count[0]['c'] : 0;

    if ($staff_cnt == 0 && $nasabah_total > 0) {
        $advice[] = [
            'kategori' => 'efisiensi',
            'judul' => "[$nama_usaha] Bos bekerja tanpa staf — risiko operasional tinggi",
            'isi' => "Koperasi $nama_usaha memiliki $nasabah_total nasabah tapi dikelola seorang diri oleh Bos. Ini berisiko dan tidak efisien.\n\nRekomendasi:\n1. Rekrut minimal 1 admin untuk administrasi\n2. Rekrut petugas lapangan untuk penagihan\n3. Delegasikan tugas operasional agar Bos fokus strategi",
            'prioritas' => 'tinggi',
            'data' => ['staf' => 0, 'nasabah' => $nasabah_total]
        ];
    }

    // 5. Product diversification
    if ($pinjaman_cnt > 10) {
        $avg_plafon = $pinjaman_total / max($pinjaman_cnt, 1);
        $advice[] = [
            'kategori' => 'produk',
            'judul' => "[$nama_usaha] Analisis produk — rata-rata plafon Rp " . number_format($avg_plafon, 0, ',', '.'),
            'isi' => "Dari $pinjaman_cnt pinjaman aktif, rata-rata plafon adalah Rp " . number_format($avg_plafon, 0, ',', '.') . ". Total outstanding: Rp " . number_format($pinjaman_total, 0, ',', '.') . ".\n\nSaran pengembangan produk:\n1. " . ($avg_plafon < 5000000 ? "Pertimbangkan produk pinjaman menengah (5-20jt) untuk nasabah berpengalaman" : "Produk mikro (< 2jt) bisa menarik nasabah baru yang belum berani besar") . "\n2. Variasi tenor: tawarkan tenor pendek (mingguan) dan panjang (6-12 bulan)\n3. Buat program pinjaman khusus: pendidikan, pertanian, UMKM",
            'prioritas' => 'sedang',
            'data' => ['pinjaman_aktif' => $pinjaman_cnt, 'avg_plafon' => $avg_plafon, 'total_outstanding' => $pinjaman_total]
        ];
    }

    // If no specific advice, give general encouragement
    if (empty($advice)) {
        $advice[] = [
            'kategori' => 'umum',
            'judul' => "[$nama_usaha] Kondisi operasional baik",
            'isi' => "Koperasi $nama_usaha berjalan dengan baik. Tidak ada masalah kritis yang terdeteksi saat ini. Terus pantau:\n\n1. Pertumbuhan nasabah\n2. Tingkat keterlambatan pembayaran\n3. Efisiensi operasional staf\n4. Diversifikasi produk pinjaman",
            'prioritas' => 'rendah',
            'data' => ['nasabah' => $nasabah_total, 'pinjaman' => $pinjaman_cnt, 'staf' => $staff_cnt ?? 0]
        ];
    }

    return $advice;
}
?>
<?php include __DIR__ . '/_header.php'; ?>

        <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show"><?php echo htmlspecialchars($success); ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>

        <!-- Controls -->
        <div class="row mb-4">
            <div class="col-md-6">
                <form method="POST" class="d-flex gap-2 align-items-end">
                    <?= csrfField() ?>
                    <input type="hidden" name="action" value="generate_advice">
                    <div>
                        <label class="form-label small mb-0">Target Koperasi</label>
                        <select name="bos_user_id" class="form-select form-select-sm">
                            <option value="all">Semua Koperasi</option>
                            <?php foreach ($bos_for_filter as $b): ?>
                            <option value="<?php echo $b['id']; ?>"><?php echo htmlspecialchars($b['nama_usaha'] ?? $b['nama']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm" onclick="return confirm('Generate saran AI untuk koperasi terpilih?')">
                        <i class="bi bi-robot"></i> Generate Saran
                    </button>
                </form>
            </div>
            <div class="col-md-6 text-end">
                <span class="badge bg-warning text-dark"><i class="bi bi-bell"></i> <?php echo $stat_baru; ?> saran baru</span>
            </div>
        </div>

        <!-- Filter -->
        <div class="mb-3">
            <div class="btn-group btn-group-sm">
                <?php
                $kats = ['all'=>'Semua','pertumbuhan'=>'Pertumbuhan','risiko'=>'Risiko','efisiensi'=>'Efisiensi','produk'=>'Produk','umum'=>'Umum'];
                foreach ($kats as $k => $label):
                ?>
                <a href="?kategori=<?php echo $k; ?><?php echo $filter_bos ? '&bos=' . $filter_bos : ''; ?>" class="btn btn-<?php echo $filter_kat === $k ? 'dark' : 'outline-dark'; ?>"><?php echo $label; ?></a>
                <?php endforeach; ?>
            </div>
            <?php if ($filter_bos): ?>
            <a href="?kategori=<?php echo $filter_kat; ?>" class="btn btn-sm btn-outline-secondary ms-2">× Hapus filter koperasi</a>
            <?php endif; ?>
        </div>

        <!-- Advice list -->
        <?php if (empty($advice_list)): ?>
        <div class="text-center text-muted py-5">
            <i class="bi bi-robot" style="font-size:3rem"></i>
            <p class="mt-2">Belum ada saran AI. Klik "Generate Saran" untuk menganalisis koperasi.</p>
        </div>
        <?php else: ?>
        <?php foreach ($advice_list as $adv): ?>
        <?php
            $pcolor = ['kritis'=>'danger','tinggi'=>'warning','sedang'=>'info','rendah'=>'secondary'];
            $kicon = ['pertumbuhan'=>'graph-up-arrow','risiko'=>'exclamation-triangle','efisiensi'=>'speedometer2','produk'=>'box-seam','umum'=>'info-circle'];
        ?>
        <div class="card border-0 shadow-sm mb-3 <?php echo $adv['status'] === 'baru' ? 'border-start border-4 border-' . ($pcolor[$adv['prioritas']] ?? 'secondary') : ''; ?>">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="flex-grow-1">
                        <div class="d-flex gap-2 mb-2">
                            <span class="badge bg-<?php echo $pcolor[$adv['prioritas']] ?? 'secondary'; ?>"><?php echo ucfirst($adv['prioritas']); ?></span>
                            <span class="badge bg-light text-dark"><i class="bi bi-<?php echo $kicon[$adv['kategori']] ?? 'info-circle'; ?>"></i> <?php echo ucfirst($adv['kategori']); ?></span>
                            <?php if ($adv['nama_usaha']): ?>
                            <a href="?bos=<?php echo $adv['bos_user_id']; ?>&kategori=<?php echo $filter_kat; ?>" class="badge bg-primary text-decoration-none"><?php echo htmlspecialchars($adv['nama_usaha']); ?></a>
                            <?php endif; ?>
                            <?php if ($adv['status'] === 'baru'): ?>
                            <span class="badge bg-warning text-dark">Baru</span>
                            <?php endif; ?>
                        </div>
                        <h6 class="mb-2"><?php echo htmlspecialchars($adv['judul']); ?></h6>
                        <div class="text-muted small" style="white-space: pre-line;"><?php echo htmlspecialchars($adv['isi']); ?></div>
                        <?php if ($adv['data_pendukung']): ?>
                        <?php $data = json_decode($adv['data_pendukung'], true); ?>
                        <?php if ($data): ?>
                        <div class="mt-2">
                            <?php foreach ($data as $key => $val): ?>
                            <span class="badge bg-light text-dark me-1"><?php echo str_replace('_', ' ', $key); ?>: <strong><?php echo is_numeric($val) ? number_format($val) : $val; ?></strong></span>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; endif; ?>
                    </div>
                    <div class="text-end ms-3">
                        <small class="text-muted d-block"><?php echo date('d M Y', strtotime($adv['created_at'])); ?></small>
                        <?php if ($adv['status'] === 'baru'): ?>
                        <form method="POST" class="mt-1">
                            <?= csrfField() ?>
                            <input type="hidden" name="action" value="mark_read">
                            <input type="hidden" name="advice_id" value="<?php echo $adv['id']; ?>">
                            <button class="btn btn-outline-primary btn-sm"><i class="bi bi-check2"></i> Dibaca</button>
                        </form>
                        <?php else: ?>
                        <small class="text-success"><i class="bi bi-check-circle"></i></small>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>

<?php include __DIR__ . '/_footer.php'; ?>
