<?php
require_once __DIR__ . '/../../config/path.php';
require_once BASE_PATH . '/includes/functions.php';
requireLogin();
$user = getCurrentUser();
$page_title = 'Billing';

$success = $_SESSION['success'] ?? '';
unset($_SESSION['success']);
$error = '';

// Handle POST — generate invoice, mark paid
if ($_POST) {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid CSRF token';
    } else {
        $action = $_POST['action'] ?? '';

        if ($action === 'generate_invoices') {
            $bulan = (int)($_POST['bulan'] ?? date('n'));
            $tahun = (int)($_POST['tahun'] ?? date('Y'));
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
            $_SESSION['success'] = "$generated invoice berhasil di-generate untuk periode $bulan/$tahun.";
            header('Location: ' . baseUrl('pages/app_owner/billing.php'));
            exit();

        } elseif ($action === 'mark_paid') {
            $invoice_id = (int)($_POST['invoice_id'] ?? 0);
            $metode = $_POST['metode_bayar'] ?? 'transfer';
            if ($invoice_id) {
                query("UPDATE koperasi_invoices SET status = 'dibayar', tanggal_bayar = CURDATE(), metode_bayar = ? WHERE id = ?", [$metode, $invoice_id]);
                $_SESSION['success'] = 'Invoice ditandai sebagai dibayar.';
            }
            header('Location: ' . baseUrl('pages/app_owner/billing.php'));
            exit();
        }
    }
}

// Filter
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
?>
<?php include __DIR__ . '/_header.php'; ?>

        <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show"><?php echo htmlspecialchars($success); ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>

        <!-- Billing Plans -->
        <div class="row g-3 mb-4">
            <?php foreach ($plans as $p): ?>
            <div class="col-md-4 col-lg">
                <div class="card stat-card h-100">
                    <div class="card-body text-center py-3">
                        <h6 class="mb-1"><?php echo htmlspecialchars($p['nama']); ?></h6>
                        <div class="text-muted small mb-1"><?php echo ucfirst($p['tipe']); ?></div>
                        <?php if ($p['tipe'] === 'fixed'): ?>
                        <h5 class="text-primary mb-0">Rp <?php echo number_format($p['harga_bulanan'], 0, ',', '.'); ?><small class="text-muted">/bln</small></h5>
                        <?php elseif ($p['tipe'] === 'percentage'): ?>
                        <h5 class="text-success mb-0"><?php echo $p['persentase_keuntungan']; ?>%<small class="text-muted"> revenue</small></h5>
                        <?php else: ?>
                        <h5 class="text-warning mb-0">Usage<small class="text-muted">-based</small></h5>
                        <?php endif; ?>
                        <div class="mt-1"><span class="badge bg-secondary"><?php echo (int)$p['subscribers']; ?> subscribers</span></div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Generate & Filter -->
        <div class="row mb-3">
            <div class="col-md-6">
                <form method="POST" class="d-flex gap-2 align-items-end">
                    <?= csrfField() ?>
                    <input type="hidden" name="action" value="generate_invoices">
                    <div>
                        <label class="form-label small mb-0">Bulan</label>
                        <select name="bulan" class="form-select form-select-sm">
                            <?php for ($m = 1; $m <= 12; $m++): ?>
                            <option value="<?php echo $m; ?>" <?php echo $m == date('n') ? 'selected' : ''; ?>><?php echo date('F', mktime(0,0,0,$m)); ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div>
                        <label class="form-label small mb-0">Tahun</label>
                        <input type="number" name="tahun" class="form-control form-control-sm" value="<?php echo date('Y'); ?>" min="2024" max="2030">
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm" onclick="return confirm('Generate invoice untuk semua koperasi?')">
                        <i class="bi bi-plus-circle"></i> Generate Invoice
                    </button>
                </form>
            </div>
            <div class="col-md-6 text-end">
                <div class="d-inline-flex gap-2 align-items-center">
                    <span class="small text-muted">Total: <strong>Rp <?php echo number_format($sum_total, 0, ',', '.'); ?></strong></span>
                    <span class="small text-success">Dibayar: <strong>Rp <?php echo number_format($sum_paid, 0, ',', '.'); ?></strong></span>
                </div>
            </div>
        </div>

        <!-- Payment Method Info -->
        <?php if ($primary_bank): ?>
        <div class="alert alert-info alert-dismissible fade show mb-3">
            <strong><i class="bi bi-bank"></i> Metode Pembayaran Utama:</strong>
            <?php
            $tipe_labels = [
                'bank' => 'Bank',
                'mobile_banking' => 'Mobile Banking',
                'ewallet' => 'E-Wallet',
                'qris' => 'QRIS',
                'virtual_account' => 'Virtual Account'
            ];
            ?>
            <span class="badge bg-primary"><?php echo $tipe_labels[$primary_bank['tipe_pembayaran']] ?? ucfirst($primary_bank['tipe_pembayaran']); ?></span>
            <?php echo htmlspecialchars($primary_bank['bank_name'] ?? ''); ?> -
            <?php if ($primary_bank['tipe_pembayaran'] === 'ewallet'): ?>
            No. HP: <code><?php echo htmlspecialchars($primary_bank['nomor_hp'] ?? ''); ?></code>
            <?php elseif ($primary_bank['tipe_pembayaran'] === 'qris'): ?>
            QR Code
            <?php else: ?>
            No. Rek: <code><?php echo htmlspecialchars($primary_bank['nomor_rekening'] ?? ''); ?></code>
            <?php endif; ?>
            <?php if (!empty($primary_bank['nama_pemilik'])): ?> - a.n. <?php echo htmlspecialchars($primary_bank['nama_pemilik']); ?><?php endif; ?>
            <?php if (!empty($primary_bank['cabang'])): ?> (<?php echo htmlspecialchars($primary_bank['cabang']); ?>)<?php endif; ?>
        </div>
        <?php else: ?>
        <div class="alert alert-warning mb-3">
            <strong><i class="bi bi-exclamation-triangle"></i> Belum ada metode pembayaran utama.</strong>
            Silakan set metode pembayaran di <a href="<?php echo baseUrl('pages/app_owner/settings.php'); ?>">Settings</a> agar koperasi bisa melihat info pembayaran.
        </div>
        <?php endif; ?>

        <!-- Invoice list -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-receipt"></i> Invoice <?php echo date('F', mktime(0,0,0,$filter_month)); ?> <?php echo $filter_year; ?></h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Kode</th>
                                <th>Koperasi</th>
                                <th>Plan</th>
                                <th>Fixed</th>
                                <th>% Revenue</th>
                                <th>Usage</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($invoices)): ?>
                            <tr><td colspan="9" class="text-center text-muted py-3">Belum ada invoice untuk periode ini</td></tr>
                            <?php else: foreach ($invoices as $inv): ?>
                            <tr>
                                <td><code class="small"><?php echo htmlspecialchars($inv['kode_invoice']); ?></code></td>
                                <td><strong><?php echo htmlspecialchars($inv['nama_usaha'] ?? $inv['bos_nama']); ?></strong></td>
                                <td><span class="badge bg-light text-dark"><?php echo htmlspecialchars($inv['plan_nama']); ?></span></td>
                                <td class="text-end"><small><?php echo $inv['biaya_fixed'] > 0 ? number_format($inv['biaya_fixed'], 0, ',', '.') : '-'; ?></small></td>
                                <td class="text-end"><small><?php echo $inv['biaya_persentase'] > 0 ? number_format($inv['biaya_persentase'], 0, ',', '.') : '-'; ?></small></td>
                                <td class="text-end"><small><?php echo $inv['biaya_usage'] > 0 ? number_format($inv['biaya_usage'], 0, ',', '.') : '-'; ?></small></td>
                                <td class="text-end"><strong>Rp <?php echo number_format($inv['total'], 0, ',', '.'); ?></strong></td>
                                <td>
                                    <?php
                                    $sc = ['draft'=>'secondary','terbit'=>'warning','dibayar'=>'success','overdue'=>'danger','cancelled'=>'dark'];
                                    ?>
                                    <span class="badge bg-<?php echo $sc[$inv['status']] ?? 'secondary'; ?>"><?php echo ucfirst($inv['status']); ?></span>
                                </td>
                                <td>
                                    <?php if ($inv['status'] === 'terbit'): ?>
                                    <form method="POST" class="d-inline" onsubmit="return confirm('Tandai invoice ini sebagai dibayar?')">
                                        <?= csrfField() ?>
                                        <input type="hidden" name="action" value="mark_paid">
                                        <input type="hidden" name="invoice_id" value="<?php echo $inv['id']; ?>">
                                        <input type="hidden" name="metode_bayar" value="transfer">
                                        <button class="btn btn-success btn-sm"><i class="bi bi-check2"></i> Dibayar</button>
                                    </form>
                                    <?php elseif ($inv['status'] === 'dibayar'): ?>
                                    <small class="text-success"><i class="bi bi-check-circle-fill"></i> <?php echo $inv['tanggal_bayar'] ? date('d M', strtotime($inv['tanggal_bayar'])) : ''; ?></small>
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
