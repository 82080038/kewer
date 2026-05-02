<?php
require_once __DIR__ . '/../../config/path.php';
require_once BASE_PATH . '/includes/functions.php';
requireLogin();

$user = getCurrentUser();
$page_title = 'Persetujuan Bos';

$error = '';
$success = $_SESSION['success'] ?? '';
unset($_SESSION['success']);

// Handle POST (approve/reject)
if ($_POST) {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid CSRF token';
    } else {
        $action = $_POST['action'] ?? '';
        $registration_id = $_POST['registration_id'] ?? '';
        $rejection_reason = $_POST['rejection_reason'] ?? '';

        if (empty($registration_id)) {
            $error = 'Registration ID diperlukan';
        } else {
            $registration = query("SELECT * FROM bos_registrations WHERE id = ?", [$registration_id]);

            if (!is_array($registration) || empty($registration)) {
                $error = 'Pendaftaran tidak ditemukan';
            } else {
                $reg = $registration[0];

                if ($action === 'approve' && $reg['status'] === 'pending') {
                    // Create user account
                    $user_result = query(
                        "INSERT INTO users (username, password, nama, email, telp, role, status) VALUES (?, ?, ?, ?, ?, 'bos', 'aktif')",
                        [$reg['username'], $reg['password'], $reg['nama'], $reg['email'] ?? '', $reg['telp'] ?? '']
                    );

                    if ($user_result) {
                        $bos_user_id = query("SELECT LAST_INSERT_ID() as id")[0]['id'];

                        query("UPDATE bos_registrations SET status = 'approved', approved_at = NOW(), approved_by = ? WHERE id = ?",
                            [$user['id'], $registration_id]);

                        $_SESSION['success'] = "Bos '{$reg['nama']}' ({$reg['nama_usaha']}) berhasil disetujui. User aktif dengan username: {$reg['username']}";
                    } else {
                        $_SESSION['success'] = '';
                        $error = 'Gagal membuat akun user Bos';
                    }

                    header('Location: ' . baseUrl('pages/app_owner/approvals.php'));
                    exit();

                } elseif ($action === 'reject' && $reg['status'] === 'pending') {
                    query("UPDATE bos_registrations SET status = 'rejected', rejected_reason = ?, approved_by = ?, approved_at = NOW() WHERE id = ?",
                        [$rejection_reason, $user['id'], $registration_id]);

                    $_SESSION['success'] = "Pendaftaran '{$reg['nama']}' ditolak.";
                    header('Location: ' . baseUrl('pages/app_owner/approvals.php'));
                    exit();
                } else {
                    $error = 'Aksi tidak valid atau pendaftaran sudah diproses';
                }
            }
        }
    }
}

// Get registrations
$filter = $_GET['status'] ?? 'pending';
$valid_filters = ['pending', 'approved', 'rejected', 'all'];
if (!in_array($filter, $valid_filters)) $filter = 'pending';

if ($filter === 'all') {
    $registrations = query("SELECT * FROM bos_registrations ORDER BY created_at DESC");
} else {
    $registrations = query("SELECT * FROM bos_registrations WHERE status = ? ORDER BY created_at DESC", [$filter]);
}
if (!is_array($registrations)) $registrations = [];

// Stats
$cnt_pending = query("SELECT COUNT(*) as c FROM bos_registrations WHERE status = 'pending'");
$cnt_pending = (is_array($cnt_pending) && isset($cnt_pending[0])) ? (int)$cnt_pending[0]['c'] : 0;
$cnt_approved = query("SELECT COUNT(*) as c FROM bos_registrations WHERE status = 'approved'");
$cnt_approved = (is_array($cnt_approved) && isset($cnt_approved[0])) ? (int)$cnt_approved[0]['c'] : 0;
$cnt_rejected = query("SELECT COUNT(*) as c FROM bos_registrations WHERE status = 'rejected'");
$cnt_rejected = (is_array($cnt_rejected) && isset($cnt_rejected[0])) ? (int)$cnt_rejected[0]['c'] : 0;
?>
<?php include __DIR__ . '/_header.php'; ?>

        <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show"><?php echo htmlspecialchars($success); ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>
        <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show"><?php echo htmlspecialchars($error); ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>

        <!-- Filter tabs -->
        <ul class="nav nav-tabs mb-4">
            <li class="nav-item">
                <a class="nav-link <?php echo $filter === 'pending' ? 'active' : ''; ?>" href="?status=pending">
                    <i class="bi bi-hourglass-split"></i> Pending <span class="badge bg-warning text-dark"><?php echo $cnt_pending; ?></span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $filter === 'approved' ? 'active' : ''; ?>" href="?status=approved">
                    <i class="bi bi-check-circle"></i> Approved <span class="badge bg-success"><?php echo $cnt_approved; ?></span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $filter === 'rejected' ? 'active' : ''; ?>" href="?status=rejected">
                    <i class="bi bi-x-circle"></i> Rejected <span class="badge bg-danger"><?php echo $cnt_rejected; ?></span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $filter === 'all' ? 'active' : ''; ?>" href="?status=all">
                    <i class="bi bi-list"></i> Semua
                </a>
            </li>
        </ul>

        <!-- Registrations list -->
        <?php if (empty($registrations)): ?>
        <div class="text-center text-muted py-5">
            <i class="bi bi-inbox" style="font-size:3rem;"></i>
            <p class="mt-2">Tidak ada pendaftaran <?php echo $filter !== 'all' ? "dengan status '$filter'" : ''; ?></p>
        </div>
        <?php else: ?>
        <?php foreach ($registrations as $reg): ?>
        <div class="card mb-3 border-0 shadow-sm">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h5 class="mb-1"><?php echo htmlspecialchars($reg['nama']); ?></h5>
                        <p class="mb-1 text-muted">
                            <i class="bi bi-building"></i> <strong><?php echo htmlspecialchars($reg['nama_usaha'] ?? '-'); ?></strong>
                        </p>
                        <small class="text-muted">
                            <i class="bi bi-person"></i> <?php echo htmlspecialchars($reg['username']); ?> &nbsp;
                            <?php if ($reg['email']): ?><i class="bi bi-envelope"></i> <?php echo htmlspecialchars($reg['email']); ?> &nbsp;<?php endif; ?>
                            <?php if ($reg['telp']): ?><i class="bi bi-telephone"></i> <?php echo htmlspecialchars($reg['telp']); ?><?php endif; ?>
                        </small>
                        <?php if ($reg['alamat_usaha']): ?>
                        <br><small class="text-muted"><i class="bi bi-geo-alt"></i> <?php echo htmlspecialchars($reg['alamat_usaha']); ?></small>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-3 text-center">
                        <small class="text-muted d-block">Tanggal Daftar</small>
                        <strong><?php echo date('d M Y', strtotime($reg['created_at'])); ?></strong>
                        <br><small class="text-muted"><?php echo date('H:i', strtotime($reg['created_at'])); ?> WIB</small>
                    </div>
                    <div class="col-md-3 text-end">
                        <?php if ($reg['status'] === 'pending'): ?>
                        <form method="POST" class="d-inline" onsubmit="return confirm('Setujui pendaftaran ini?')">
                            <?= csrfField() ?>
                            <input type="hidden" name="registration_id" value="<?php echo $reg['id']; ?>">
                            <input type="hidden" name="action" value="approve">
                            <button type="submit" class="btn btn-success btn-sm"><i class="bi bi-check-lg"></i> Setujui</button>
                        </form>
                        <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#rejectModal<?php echo $reg['id']; ?>">
                            <i class="bi bi-x-lg"></i> Tolak
                        </button>
                        <!-- Reject Modal -->
                        <div class="modal fade" id="rejectModal<?php echo $reg['id']; ?>" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form method="POST">
                                        <?= csrfField() ?>
                                        <input type="hidden" name="registration_id" value="<?php echo $reg['id']; ?>">
                                        <input type="hidden" name="action" value="reject">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Tolak Pendaftaran</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body text-start">
                                            <p>Tolak pendaftaran <strong><?php echo htmlspecialchars($reg['nama']); ?></strong> (<?php echo htmlspecialchars($reg['nama_usaha'] ?? ''); ?>)?</p>
                                            <div class="mb-3">
                                                <label class="form-label">Alasan Penolakan</label>
                                                <textarea name="rejection_reason" class="form-control" rows="3" required placeholder="Jelaskan alasan penolakan..."></textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                            <button type="submit" class="btn btn-danger">Tolak Pendaftaran</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <?php elseif ($reg['status'] === 'approved'): ?>
                        <span class="badge bg-success px-3 py-2"><i class="bi bi-check-circle"></i> Approved</span>
                        <?php if ($reg['approved_at']): ?>
                        <br><small class="text-muted"><?php echo date('d M Y H:i', strtotime($reg['approved_at'])); ?></small>
                        <?php endif; ?>
                        <?php else: ?>
                        <span class="badge bg-danger px-3 py-2"><i class="bi bi-x-circle"></i> Rejected</span>
                        <?php if ($reg['rejected_reason']): ?>
                        <br><small class="text-muted" title="<?php echo htmlspecialchars($reg['rejected_reason']); ?>">
                            <?php echo htmlspecialchars(mb_substr($reg['rejected_reason'], 0, 50)); ?><?php echo strlen($reg['rejected_reason']) > 50 ? '...' : ''; ?>
                        </small>
                        <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
<?php include __DIR__ . '/_footer.php'; ?>
