<?php
require_once __DIR__ . '/../../config/path.php';
require_once BASE_PATH . '/includes/functions.php';
requireLogin();
$user = getCurrentUser();
$page_title = 'Koperasi';

$success = $_SESSION['success'] ?? '';
unset($_SESSION['success']);
$error = '';

// Handle POST actions (suspend/activate/assign billing/generate invoice)
if ($_POST) {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid CSRF token';
    } else {
        $action = $_POST['action'] ?? '';
        $bos_user_id = (int)($_POST['bos_user_id'] ?? 0);

        if ($action === 'suspend' && $bos_user_id) {
            query("UPDATE users SET status = 'nonaktif' WHERE id = ? AND role = 'bos'", [$bos_user_id]);
            query("UPDATE koperasi_billing SET status = 'suspended' WHERE bos_user_id = ? AND status = 'aktif'", [$bos_user_id]);
            $_SESSION['success'] = 'Koperasi berhasil di-suspend.';
            header('Location: ' . baseUrl('pages/app_owner/koperasi.php'));
            exit();
        } elseif ($action === 'activate' && $bos_user_id) {
            query("UPDATE users SET status = 'aktif' WHERE id = ? AND role = 'bos'", [$bos_user_id]);
            $_SESSION['success'] = 'Koperasi berhasil diaktifkan kembali.';
            header('Location: ' . baseUrl('pages/app_owner/koperasi.php'));
            exit();
        } elseif ($action === 'assign_plan' && $bos_user_id) {
            $plan_id = (int)($_POST['billing_plan_id'] ?? 0);
            if ($plan_id) {
                // Deactivate old billing
                query("UPDATE koperasi_billing SET status = 'cancelled' WHERE bos_user_id = ? AND status = 'aktif'", [$bos_user_id]);
                // Create new billing
                query("INSERT INTO koperasi_billing (bos_user_id, billing_plan_id, status, tanggal_mulai, created_by) VALUES (?, ?, 'aktif', CURDATE(), ?)",
                    [$bos_user_id, $plan_id, $user['id']]);
                $_SESSION['success'] = 'Billing plan berhasil di-assign.';
                header('Location: ' . baseUrl('pages/app_owner/koperasi.php'));
                exit();
            }
        } elseif ($action === 'generate_invoice' && $bos_user_id) {
            $bulan = (int)($_POST['bulan'] ?? date('n'));
            $tahun = (int)($_POST['tahun'] ?? date('Y'));

            // Get active billing for this koperasi
            $billing = query("SELECT kb.*, bp.* FROM koperasi_billing kb JOIN billing_plans bp ON bp.id = kb.billing_plan_id WHERE kb.bos_user_id = ? AND kb.status = 'aktif' LIMIT 1", [$bos_user_id]);
            if (!is_array($billing) || empty($billing)) {
                $error = 'Koperasi ini tidak memiliki billing plan aktif.';
            } else {
                $kb = $billing[0];

                // Check if invoice already exists
                $exists = query("SELECT id FROM koperasi_invoices WHERE koperasi_billing_id = ? AND periode_bulan = ? AND periode_tahun = ?",
                    [$kb['id'], $bulan, $tahun]);
                if (is_array($exists) && !empty($exists)) {
                    $error = 'Invoice untuk periode ini sudah ada.';
                } else {
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

                    $_SESSION['success'] = "Invoice {$kode} berhasil di-generate untuk periode {$bulan}/{$tahun}.";
                    header('Location: ' . baseUrl('pages/app_owner/koperasi.php'));
                    exit();
                }
            }
        }
    }
}

// Get billing plans for assignment
$plans = query("SELECT id, kode, nama, tipe, harga_bulanan, persentase_keuntungan FROM billing_plans WHERE is_active = 1 ORDER BY harga_bulanan");
if (!is_array($plans)) $plans = [];

// Get koperasi list
$koperasi_list = query("
    SELECT 
        br.id as reg_id, br.nama_usaha, br.alamat_usaha, br.username, br.nama, br.email, br.telp,
        br.approved_at,
        u.id as user_id, u.status as user_status,
        bp.nama as plan_nama, bp.tipe as plan_tipe, bp.harga_bulanan,
        kb.status as billing_status
    FROM bos_registrations br
    LEFT JOIN users u ON u.username = br.username AND u.role = 'bos'
    LEFT JOIN koperasi_billing kb ON kb.bos_user_id = u.id AND kb.status = 'aktif'
    LEFT JOIN billing_plans bp ON bp.id = kb.billing_plan_id
    WHERE br.status = 'approved'
    ORDER BY br.approved_at DESC
");
if (!is_array($koperasi_list)) $koperasi_list = [];
?>
<?php include __DIR__ . '/_header.php'; ?>

        <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show"><?php echo htmlspecialchars($success); ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>
        <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show"><?php echo htmlspecialchars($error); ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-building"></i> Koperasi Terdaftar (<?php echo count($koperasi_list); ?>)</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Koperasi</th>
                                <th>Bos</th>
                                <th>Billing Plan</th>
                                <th>Terdaftar</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($koperasi_list)): ?>
                            <tr><td colspan="7" class="text-center text-muted py-4">Belum ada koperasi terdaftar</td></tr>
                            <?php else: foreach ($koperasi_list as $i => $kop): ?>
                            <tr>
                                <td><?php echo $i + 1; ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($kop['nama_usaha'] ?? '-'); ?></strong>
                                    <?php if (!empty($kop['alamat_usaha'])): ?>
                                    <br><small class="text-muted"><?php echo htmlspecialchars($kop['alamat_usaha']); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($kop['nama']); ?>
                                    <br><small class="text-muted"><code><?php echo htmlspecialchars($kop['username']); ?></code></small>
                                    <?php if (!empty($kop['email'])): ?><br><small><?php echo htmlspecialchars($kop['email']); ?></small><?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($kop['plan_nama']): ?>
                                    <span class="badge bg-primary"><?php echo htmlspecialchars($kop['plan_nama']); ?></span>
                                    <br><small class="text-muted"><?php echo $kop['plan_tipe'] === 'fixed' ? 'Rp ' . number_format($kop['harga_bulanan'], 0, ',', '.') . '/bln' : ucfirst($kop['plan_tipe']); ?></small>
                                    <?php else: ?>
                                    <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td><small><?php echo $kop['approved_at'] ? date('d M Y', strtotime($kop['approved_at'])) : '-'; ?></small></td>
                                <td>
                                    <?php if ($kop['user_status'] === 'aktif'): ?>
                                        <?php if (!$kop['plan_nama']): ?>
                                        <span class="badge bg-warning text-dark">Aktif (Belum ada billing)</span>
                                        <?php else: ?>
                                        <span class="badge bg-success">Aktif</span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                    <span class="badge bg-secondary">Suspended</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($kop['user_id']): ?>
                                    <div class="btn-group btn-group-sm">
                                        <!-- Assign Billing -->
                                        <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#planModal<?php echo $kop['user_id']; ?>" title="Assign Plan">
                                            <i class="bi bi-receipt"></i>
                                        </button>
                                        <!-- Generate Invoice (only if has billing plan) -->
                                        <?php if ($kop['plan_nama']): ?>
                                        <button class="btn btn-outline-success btn-sm" data-bs-toggle="modal" data-bs-target="#invoiceModal<?php echo $kop['user_id']; ?>" title="Generate Invoice">
                                            <i class="bi bi-file-earmark-plus"></i>
                                        </button>
                                        <?php endif; ?>
                                        <!-- Suspend/Activate -->
                                        <?php if ($kop['user_status'] === 'aktif'): ?>
                                        <form method="POST" class="d-inline" onsubmit="return confirm('Suspend koperasi ini?')">
                                            <?= csrfField() ?>
                                            <input type="hidden" name="action" value="suspend">
                                            <input type="hidden" name="bos_user_id" value="<?php echo $kop['user_id']; ?>">
                                            <button class="btn btn-outline-danger btn-sm" title="Suspend"><i class="bi bi-pause-circle"></i></button>
                                        </form>
                                        <?php else: ?>
                                        <form method="POST" class="d-inline" onsubmit="return confirm('Aktifkan kembali koperasi ini?')">
                                            <?= csrfField() ?>
                                            <input type="hidden" name="action" value="activate">
                                            <input type="hidden" name="bos_user_id" value="<?php echo $kop['user_id']; ?>">
                                            <button class="btn btn-outline-success btn-sm" title="Activate"><i class="bi bi-play-circle"></i></button>
                                        </form>
                                        <?php endif; ?>
                                    </div>
                                    <!-- Plan Modal -->
                                    <div class="modal fade" id="planModal<?php echo $kop['user_id']; ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <form method="POST">
                                                    <?= csrfField() ?>
                                                    <input type="hidden" name="action" value="assign_plan">
                                                    <input type="hidden" name="bos_user_id" value="<?php echo $kop['user_id']; ?>">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Assign Billing Plan</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p>Koperasi: <strong><?php echo htmlspecialchars($kop['nama_usaha'] ?? $kop['nama']); ?></strong></p>
                                                        <?php if ($kop['plan_nama']): ?>
                                                        <div class="alert alert-info small">Plan saat ini: <strong><?php echo htmlspecialchars($kop['plan_nama']); ?></strong></div>
                                                        <?php endif; ?>
                                                        <div class="mb-3">
                                                            <label class="form-label">Pilih Plan</label>
                                                            <select name="billing_plan_id" class="form-select" required>
                                                                <option value="">-- Pilih Plan --</option>
                                                                <?php foreach ($plans as $p): ?>
                                                                <option value="<?php echo $p['id']; ?>">
                                                                    <?php echo htmlspecialchars($p['nama']); ?> (<?php echo $p['tipe'] === 'fixed' ? 'Rp ' . number_format($p['harga_bulanan'], 0, ',', '.') . '/bln' : ($p['tipe'] === 'percentage' ? $p['persentase_keuntungan'] . '% revenue' : 'Usage-based'); ?>)
                                                                </option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                        <button type="submit" class="btn btn-primary">Assign Plan</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Invoice Modal -->
                                    <?php if ($kop['plan_nama']): ?>
                                    <div class="modal fade" id="invoiceModal<?php echo $kop['user_id']; ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <form method="POST">
                                                    <?= csrfField() ?>
                                                    <input type="hidden" name="action" value="generate_invoice">
                                                    <input type="hidden" name="bos_user_id" value="<?php echo $kop['user_id']; ?>">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Generate Invoice</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p>Koperasi: <strong><?php echo htmlspecialchars($kop['nama_usaha'] ?? $kop['nama']); ?></strong></p>
                                                        <div class="alert alert-info small">Plan saat ini: <strong><?php echo htmlspecialchars($kop['plan_nama']); ?></strong> (<?php echo $kop['plan_tipe'] === 'fixed' ? 'Rp ' . number_format($kop['harga_bulanan'], 0, ',', '.') . '/bln' : ucfirst($kop['plan_tipe']); ?>)</div>
                                                        <div class="row">
                                                            <div class="col-6">
                                                                <div class="mb-3">
                                                                    <label class="form-label">Bulan</label>
                                                                    <select name="bulan" class="form-select" required>
                                                                        <?php for ($m = 1; $m <= 12; $m++): ?>
                                                                        <option value="<?php echo $m; ?>" <?php echo $m == date('n') ? 'selected' : ''; ?>><?php echo date('F', mktime(0,0,0,$m)); ?></option>
                                                                        <?php endfor; ?>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div class="col-6">
                                                                <div class="mb-3">
                                                                    <label class="form-label">Tahun</label>
                                                                    <input type="number" name="tahun" class="form-control" value="<?php echo date('Y'); ?>" min="2024" max="2030" required>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                        <button type="submit" class="btn btn-success">Generate Invoice</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

<?php include __DIR__ . '/_footer.php'; ?>
