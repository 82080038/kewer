<?php
require_once __DIR__ . '/../../config/path.php';
require_once BASE_PATH . '/includes/functions.php';
requireLogin();
$user = getCurrentUser();
$page_title = 'Settings';

$success = $_SESSION['success'] ?? '';
unset($_SESSION['success']);
$error = '';

if ($_POST && validateCsrfToken($_POST['csrf_token'] ?? '')) {
    $action = $_POST['action'] ?? '';

    if ($action === 'update_profile') {
        $nama = trim($_POST['nama'] ?? '');
        $email = trim($_POST['email'] ?? '');
        if ($nama) {
            query("UPDATE users SET nama = ?, email = ? WHERE id = ?", [$nama, $email, $user['id']]);
            $_SESSION['success'] = 'Profil berhasil diperbarui.';
            header('Location: ' . baseUrl('pages/app_owner/settings.php'));
            exit();
        } else {
            $error = 'Nama tidak boleh kosong.';
        }

    } elseif ($action === 'change_password') {
        $old_pw = $_POST['old_password'] ?? '';
        $new_pw = $_POST['new_password'] ?? '';
        $confirm_pw = $_POST['confirm_password'] ?? '';

        if (!password_verify($old_pw, $user['password'])) {
            $error = 'Password lama salah.';
        } elseif (strlen($new_pw) < 8) {
            $error = 'Password baru minimal 8 karakter.';
        } elseif ($new_pw !== $confirm_pw) {
            $error = 'Konfirmasi password tidak cocok.';
        } else {
            $hash = password_hash($new_pw, PASSWORD_DEFAULT);
            query("UPDATE users SET password = ? WHERE id = ?", [$hash, $user['id']]);
            $_SESSION['success'] = 'Password berhasil diubah.';
            header('Location: ' . baseUrl('pages/app_owner/settings.php'));
            exit();
        }

    } elseif ($action === 'manage_plan') {
        $plan_id = (int)($_POST['plan_id'] ?? 0);
        $field = $_POST['field'] ?? '';
        $value = $_POST['value'] ?? '';

        if ($plan_id && $field && in_array($field, ['harga_bulanan', 'persentase_keuntungan', 'harga_per_api_call', 'harga_per_render', 'api_call_gratis', 'render_gratis', 'max_users', 'max_cabang', 'max_nasabah', 'is_active'])) {
            query("UPDATE billing_plans SET $field = ? WHERE id = ?", [$value, $plan_id]);
            $_SESSION['success'] = 'Billing plan berhasil diperbarui.';
            header('Location: ' . baseUrl('pages/app_owner/settings.php'));
            exit();
        }
    }
}

// Re-fetch user
$user = getCurrentUser();

// Get billing plans
$plans = query("SELECT * FROM billing_plans ORDER BY harga_bulanan");
if (!is_array($plans)) $plans = [];

// Platform stats
$total_koperasi = query("SELECT COUNT(*) as c FROM bos_registrations WHERE status = 'approved'");
$total_koperasi = (is_array($total_koperasi) && isset($total_koperasi[0])) ? (int)$total_koperasi[0]['c'] : 0;
$total_invoices = query("SELECT COUNT(*) as c FROM koperasi_invoices");
$total_invoices = (is_array($total_invoices) && isset($total_invoices[0])) ? (int)$total_invoices[0]['c'] : 0;
$total_advice = query("SELECT COUNT(*) as c FROM ai_advice");
$total_advice = (is_array($total_advice) && isset($total_advice[0])) ? (int)$total_advice[0]['c'] : 0;
?>
<?php include __DIR__ . '/_header.php'; ?>

        <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show"><?php echo htmlspecialchars($success); ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>
        <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show"><?php echo htmlspecialchars($error); ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>

        <div class="row g-4">
            <!-- Profile -->
            <div class="col-md-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white"><h6 class="mb-0"><i class="bi bi-person-circle"></i> Profil</h6></div>
                    <div class="card-body">
                        <form method="POST">
                            <?= csrfField() ?>
                            <input type="hidden" name="action" value="update_profile">
                            <div class="mb-3">
                                <label class="form-label">Username</label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['username']); ?>" disabled>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Nama</label>
                                <input type="text" name="nama" class="form-control" value="<?php echo htmlspecialchars($user['nama']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Role</label>
                                <input type="text" class="form-control" value="App Owner" disabled>
                            </div>
                            <button type="submit" class="btn btn-primary"><i class="bi bi-check"></i> Simpan</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Password -->
            <div class="col-md-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white"><h6 class="mb-0"><i class="bi bi-key"></i> Ganti Password</h6></div>
                    <div class="card-body">
                        <form method="POST">
                            <?= csrfField() ?>
                            <input type="hidden" name="action" value="change_password">
                            <div class="mb-3">
                                <label class="form-label">Password Lama</label>
                                <input type="password" name="old_password" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Password Baru</label>
                                <input type="password" name="new_password" class="form-control" required minlength="8">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Konfirmasi Password</label>
                                <input type="password" name="confirm_password" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-warning"><i class="bi bi-key"></i> Ubah Password</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Billing Plans Management -->
        <div class="card border-0 shadow-sm mt-4">
            <div class="card-header bg-white"><h6 class="mb-0"><i class="bi bi-receipt"></i> Billing Plans</h6></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Kode</th>
                                <th>Nama</th>
                                <th>Tipe</th>
                                <th>Harga/bln</th>
                                <th>% Profit</th>
                                <th>Per API</th>
                                <th>Per Render</th>
                                <th>Limits</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($plans as $p): ?>
                            <tr>
                                <td><code><?php echo htmlspecialchars($p['kode']); ?></code></td>
                                <td><strong><?php echo htmlspecialchars($p['nama']); ?></strong></td>
                                <td><span class="badge bg-light text-dark"><?php echo $p['tipe']; ?></span></td>
                                <td>Rp <?php echo number_format($p['harga_bulanan'], 0, ',', '.'); ?></td>
                                <td><?php echo $p['persentase_keuntungan'] > 0 ? $p['persentase_keuntungan'] . '%' : '-'; ?></td>
                                <td><?php echo $p['harga_per_api_call'] > 0 ? 'Rp ' . number_format($p['harga_per_api_call']) : '-'; ?></td>
                                <td><?php echo $p['harga_per_render'] > 0 ? 'Rp ' . number_format($p['harga_per_render']) : '-'; ?></td>
                                <td class="small">
                                    <?php echo $p['max_users'] ? $p['max_users'] . ' users' : '∞'; ?> /
                                    <?php echo $p['max_cabang'] ? $p['max_cabang'] . ' cabang' : '∞'; ?> /
                                    <?php echo $p['max_nasabah'] ? $p['max_nasabah'] . ' nasabah' : '∞'; ?>
                                </td>
                                <td>
                                    <form method="POST" class="d-inline">
                                        <?= csrfField() ?>
                                        <input type="hidden" name="action" value="manage_plan">
                                        <input type="hidden" name="plan_id" value="<?php echo $p['id']; ?>">
                                        <input type="hidden" name="field" value="is_active">
                                        <input type="hidden" name="value" value="<?php echo $p['is_active'] ? 0 : 1; ?>">
                                        <button class="btn btn-sm btn-<?php echo $p['is_active'] ? 'success' : 'secondary'; ?>">
                                            <?php echo $p['is_active'] ? 'Aktif' : 'Nonaktif'; ?>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Platform Info -->
        <div class="card border-0 shadow-sm mt-4">
            <div class="card-header bg-white"><h6 class="mb-0"><i class="bi bi-info-circle"></i> Platform Info</h6></div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-3"><h4 class="mb-0"><?php echo $total_koperasi; ?></h4><small class="text-muted">Koperasi</small></div>
                    <div class="col-md-3"><h4 class="mb-0"><?php echo $total_invoices; ?></h4><small class="text-muted">Invoices</small></div>
                    <div class="col-md-3"><h4 class="mb-0"><?php echo $total_advice; ?></h4><small class="text-muted">AI Advice</small></div>
                    <div class="col-md-3"><h4 class="mb-0"><?php echo APP_NAME; ?></h4><small class="text-muted">v1.0</small></div>
                </div>
            </div>
        </div>

<?php include __DIR__ . '/_footer.php'; ?>
