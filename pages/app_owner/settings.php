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

    } elseif ($action === 'add_bank_account') {
        $tipe_pembayaran = trim($_POST['tipe_pembayaran'] ?? 'bank');
        $nama_bank = trim($_POST['nama_bank'] ?? '');
        $nomor_rekening = trim($_POST['nomor_rekening'] ?? '');
        $nomor_hp = trim($_POST['nomor_hp'] ?? '');
        $nama_pemilik = trim($_POST['nama_pemilik'] ?? '');
        $cabang = trim($_POST['cabang'] ?? '');
        $qris_code = trim($_POST['qris_code'] ?? '');
        $keterangan = trim($_POST['keterangan'] ?? '');
        $is_primary = isset($_POST['is_primary']) ? 1 : 0;

        // Validation based on payment type
        $valid = true;
        if ($tipe_pembayaran === 'bank' || $tipe_pembayaran === 'mobile_banking') {
            if (!$nama_bank || !$nomor_rekening || !$nama_pemilik) {
                $error = 'Nama bank, nomor rekening, dan nama pemilik wajib diisi.';
                $valid = false;
            }
        } elseif ($tipe_pembayaran === 'ewallet') {
            if (!$nama_bank || !$nomor_hp || !$nama_pemilik) {
                $error = 'Nama aplikasi, nomor HP, dan nama pemilik wajib diisi.';
                $valid = false;
            }
        } elseif ($tipe_pembayaran === 'qris') {
            if (!$nama_bank || !$qris_code) {
                $error = 'Nama merchant dan QR code wajib diisi.';
                $valid = false;
            }
        } elseif ($tipe_pembayaran === 'virtual_account') {
            if (!$nama_bank || !$nomor_rekening) {
                $error = 'Nama bank dan nomor virtual account wajib diisi.';
                $valid = false;
            }
        }

        if ($valid) {
            // If setting as primary, unset other primary
            if ($is_primary) {
                query("UPDATE platform_bank_accounts SET is_primary = 0");
            }
            query("INSERT INTO platform_bank_accounts (tipe_pembayaran, nama_bank, nomor_rekening, nomor_hp, nama_pemilik, cabang, qris_code, keterangan, is_primary) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
                [$tipe_pembayaran, $nama_bank, $nomor_rekening, $nomor_hp, $nama_pemilik, $cabang, $qris_code, $keterangan, $is_primary]);
            $_SESSION['success'] = 'Metode pembayaran berhasil ditambahkan.';
            header('Location: ' . baseUrl('pages/app_owner/settings.php'));
            exit();
        }

    } elseif ($action === 'delete_bank_account') {
        $account_id = (int)($_POST['account_id'] ?? 0);
        if ($account_id) {
            query("DELETE FROM platform_bank_accounts WHERE id = ?", [$account_id]);
            $_SESSION['success'] = 'Rekening bank berhasil dihapus.';
            header('Location: ' . baseUrl('pages/app_owner/settings.php'));
            exit();
        }

    } elseif ($action === 'set_primary_bank') {
        $account_id = (int)($_POST['account_id'] ?? 0);
        if ($account_id) {
            query("UPDATE platform_bank_accounts SET is_primary = 0");
            query("UPDATE platform_bank_accounts SET is_primary = 1 WHERE id = ?", [$account_id]);
            $_SESSION['success'] = 'Rekening bank utama berhasil diubah.';
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

// Get bank accounts
$bank_accounts = query("SELECT * FROM platform_bank_accounts ORDER BY is_primary DESC, created_at DESC");
if (!is_array($bank_accounts)) $bank_accounts = [];

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

        <!-- Bank Accounts Management -->
        <div class="card border-0 shadow-sm mt-4">
            <div class="card-header bg-white"><h6 class="mb-0"><i class="bi bi-bank"></i> Rekening Bank Platform</h6></div>
            <div class="card-body">
                <!-- Add Payment Method Form -->
                <form method="POST" class="mb-4">
                    <?= csrfField() ?>
                    <input type="hidden" name="action" value="add_bank_account">
                    <div class="row g-2 mb-2">
                        <div class="col-md-3">
                            <label class="form-label small mb-0">Tipe Pembayaran</label>
                            <select name="tipe_pembayaran" class="form-select form-select-sm" id="tipe_pembayaran" onchange="togglePaymentFields()" required>
                                <option value="bank">Bank Tradisional</option>
                                <option value="mobile_banking">Mobile Banking</option>
                                <option value="ewallet">E-Wallet (DANA/OVO/GoPay)</option>
                                <option value="qris">QRIS</option>
                                <option value="virtual_account">Virtual Account</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small mb-0" id="label_nama_bank">Nama Bank/Aplikasi</label>
                            <input type="text" name="nama_bank" class="form-control form-control-sm" placeholder="Contoh: BCA / DANA" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small mb-0" id="label_nomor">Nomor Rekening/HP</label>
                            <input type="text" name="nomor_rekening" class="form-control form-control-sm" id="field_nomor_rekening" placeholder="Nomor Rekening">
                            <input type="text" name="nomor_hp" class="form-control form-control-sm d-none" id="field_nomor_hp" placeholder="08xxxxxxxx">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small mb-0" id="label_pemilik">Nama Pemilik</label>
                            <input type="text" name="nama_pemilik" class="form-control form-control-sm" id="field_nama_pemilik" placeholder="Nama Pemilik" required>
                        </div>
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col-md-3">
                            <label class="form-label small mb-0" id="label_cabang">Cabang (opsional)</label>
                            <input type="text" name="cabang" class="form-control form-control-sm" placeholder="Cabang">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small mb-0 d-none" id="label_qris">QR Code String/URL</label>
                            <input type="text" name="qris_code" class="form-control form-control-sm d-none" id="field_qris_code" placeholder="Paste QR code string atau URL">
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_primary" id="is_primary">
                                <label class="form-check-label small" for="is_primary">Utama</label>
                            </div>
                            <button type="submit" class="btn btn-primary btn-sm ms-2"><i class="bi bi-plus"></i> Tambah</button>
                        </div>
                    </div>
                    <div class="row g-2">
                        <div class="col-md-12">
                            <label class="form-label small mb-0">Keterangan (opsional)</label>
                            <input type="text" name="keterangan" class="form-control form-control-sm" placeholder="Catatan tambahan">
                        </div>
                    </div>
                </form>

                <script>
                function togglePaymentFields() {
                    const tipe = document.getElementById('tipe_pembayaran').value;
                    const labelNamaBank = document.getElementById('label_nama_bank');
                    const labelNomor = document.getElementById('label_nomor');
                    const labelPemilik = document.getElementById('label_pemilik');
                    const labelCabang = document.getElementById('label_cabang');
                    const labelQris = document.getElementById('label_qris');
                    const fieldNomorRekening = document.getElementById('field_nomor_rekening');
                    const fieldNomorHp = document.getElementById('field_nomor_hp');
                    const fieldNamaPemilik = document.getElementById('field_nama_pemilik');
                    const fieldQrisCode = document.getElementById('field_qris_code');

                    // Reset
                    labelNamaBank.textContent = 'Nama Bank/Aplikasi';
                    labelNomor.classList.remove('d-none');
                    labelPemilik.classList.remove('d-none');
                    labelCabang.classList.remove('d-none');
                    labelQris.classList.add('d-none');
                    fieldNomorRekening.classList.remove('d-none');
                    fieldNomorHp.classList.add('d-none');
                    fieldNamaPemilik.classList.remove('d-none');
                    fieldQrisCode.classList.add('d-none');

                    if (tipe === 'bank' || tipe === 'mobile_banking') {
                        labelNamaBank.textContent = 'Nama Bank';
                        labelNomor.textContent = 'Nomor Rekening';
                        labelPemilik.textContent = 'Nama Pemilik';
                        fieldNomorRekening.required = true;
                        fieldNamaPemilik.required = true;
                    } else if (tipe === 'ewallet') {
                        labelNamaBank.textContent = 'Nama Aplikasi';
                        labelNomor.textContent = 'Nomor HP';
                        labelPemilik.textContent = 'Nama Pemilik';
                        labelCabang.classList.add('d-none');
                        fieldNomorRekening.classList.add('d-none');
                        fieldNomorHp.classList.remove('d-none');
                        fieldNomorHp.required = true;
                        fieldNamaPemilik.required = true;
                    } else if (tipe === 'qris') {
                        labelNamaBank.textContent = 'Nama Merchant';
                        labelNomor.classList.add('d-none');
                        labelPemilik.classList.add('d-none');
                        labelCabang.classList.add('d-none');
                        labelQris.classList.remove('d-none');
                        fieldNomorRekening.classList.add('d-none');
                        fieldNamaPemilik.classList.remove('d-none');
                        fieldNamaPemilik.required = false;
                        fieldQrisCode.classList.remove('d-none');
                        fieldQrisCode.required = true;
                    } else if (tipe === 'virtual_account') {
                        labelNamaBank.textContent = 'Nama Bank';
                        labelNomor.textContent = 'Nomor VA';
                        labelPemilik.classList.add('d-none');
                        labelCabang.classList.add('d-none');
                        fieldNomorRekening.required = true;
                        fieldNamaPemilik.required = false;
                    }
                }
                // Initialize on load
                togglePaymentFields();
                </script>

                <!-- Payment Methods List -->
                <?php if (empty($bank_accounts)): ?>
                <div class="text-center text-muted py-3">Belum ada metode pembayaran yang terdaftar</div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Tipe</th>
                                <th>Bank/Aplikasi</th>
                                <th>Nomor</th>
                                <th>Pemilik</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($bank_accounts as $acc): ?>
                            <tr>
                                <td>
                                    <?php
                                    $tipe_labels = [
                                        'bank' => 'Bank',
                                        'mobile_banking' => 'Mobile Banking',
                                        'ewallet' => 'E-Wallet',
                                        'qris' => 'QRIS',
                                        'virtual_account' => 'VA'
                                    ];
                                    $tipe_colors = [
                                        'bank' => 'primary',
                                        'mobile_banking' => 'info',
                                        'ewallet' => 'success',
                                        'qris' => 'warning',
                                        'virtual_account' => 'secondary'
                                    ];
                                    ?>
                                    <span class="badge bg-<?php echo $tipe_colors[$acc['tipe_pembayaran']] ?? 'secondary'; ?>">
                                        <?php echo $tipe_labels[$acc['tipe_pembayaran']] ?? ucfirst($acc['tipe_pembayaran']); ?>
                                    </span>
                                </td>
                                <td><strong><?php echo htmlspecialchars($acc['nama_bank']); ?></strong></td>
                                <td>
                                    <?php if ($acc['tipe_pembayaran'] === 'ewallet'): ?>
                                    <code><?php echo htmlspecialchars($acc['nomor_hp'] ?? '-'); ?></code>
                                    <?php elseif ($acc['tipe_pembayaran'] === 'qris'): ?>
                                    <small>QR Code</small>
                                    <?php else: ?>
                                    <code><?php echo htmlspecialchars($acc['nomor_rekening'] ?? '-'); ?></code>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($acc['nama_pemilik'] ?? '-'); ?></td>
                                <td>
                                    <?php if ($acc['is_primary']): ?>
                                    <span class="badge bg-primary">Utama</span>
                                    <?php else: ?>
                                    <span class="badge bg-secondary">Cadangan</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <?php if (!$acc['is_primary']): ?>
                                        <form method="POST" class="d-inline">
                                            <?= csrfField() ?>
                                            <input type="hidden" name="action" value="set_primary_bank">
                                            <input type="hidden" name="account_id" value="<?php echo $acc['id']; ?>">
                                            <button class="btn btn-outline-primary btn-sm" title="Jadikan Utama"><i class="bi bi-star"></i></button>
                                        </form>
                                        <?php endif; ?>
                                        <form method="POST" class="d-inline" onsubmit="return confirm('Hapus metode pembayaran ini?')">
                                            <?= csrfField() ?>
                                            <input type="hidden" name="action" value="delete_bank_account">
                                            <input type="hidden" name="account_id" value="<?php echo $acc['id']; ?>">
                                            <button class="btn btn-outline-danger btn-sm" title="Hapus"><i class="bi bi-trash"></i></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
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
