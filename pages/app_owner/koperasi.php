<?php
require_once __DIR__ . '/../../config/path.php';
require_once BASE_PATH . '/includes/functions.php';
requireLogin();
$user = getCurrentUser();
$page_title = 'Koperasi';

$success = $_SESSION['success'] ?? '';
unset($_SESSION['success']);
$error = '';

// Handle POST actions (suspend/activate/assign billing)
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
                                    <span class="badge bg-success">Aktif</span>
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
