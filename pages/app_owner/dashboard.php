<?php
require_once __DIR__ . '/../../config/path.php';
require_once BASE_PATH . '/includes/functions.php';
requireLogin();
$user = getCurrentUser();
$page_title = 'Dashboard';

// Helper
function qval($sql) {
    $r = query($sql);
    return (is_array($r) && isset($r[0])) ? (int)($r[0]['c'] ?? $r[0]['cnt'] ?? 0) : 0;
}

$total_pending = qval("SELECT COUNT(*) as c FROM bos_registrations WHERE status = 'pending'");
$total_approved = qval("SELECT COUNT(*) as c FROM bos_registrations WHERE status = 'approved'");
$total_bos = qval("SELECT COUNT(*) as c FROM users WHERE role = 'bos' AND status = 'aktif'");
$total_users = qval("SELECT COUNT(*) as c FROM users WHERE role != 'appOwner' AND status = 'aktif'");

// Billing stats
$total_revenue = query("SELECT COALESCE(SUM(total),0) as c FROM koperasi_invoices WHERE status = 'dibayar'");
$total_revenue = (is_array($total_revenue) && isset($total_revenue[0])) ? (float)$total_revenue[0]['c'] : 0;
$overdue_invoices = qval("SELECT COUNT(*) as c FROM koperasi_invoices WHERE status = 'overdue'");

// Usage today
$today_usage = query("SELECT COALESCE(SUM(total_api_calls),0) as api, COALESCE(SUM(total_renders),0) as renders FROM usage_daily_summary WHERE tanggal = CURDATE()");
$today_api = (is_array($today_usage) && isset($today_usage[0])) ? (int)$today_usage[0]['api'] : 0;
$today_renders = (is_array($today_usage) && isset($today_usage[0])) ? (int)$today_usage[0]['renders'] : 0;

// Recent registrations
$recent = query("SELECT id, username, nama, nama_usaha, status, created_at FROM bos_registrations ORDER BY created_at DESC LIMIT 10");
if (!is_array($recent)) $recent = [];

// Recent AI advice
$recent_advice = query("SELECT id, judul, kategori, prioritas, created_at FROM ai_advice ORDER BY created_at DESC LIMIT 5");
if (!is_array($recent_advice)) $recent_advice = [];
?>
<?php include __DIR__ . '/_header.php'; ?>

        <!-- Stats Row 1: Platform -->
        <div class="row g-3 mb-3">
            <div class="col-md-2 col-6">
                <div class="card stat-card">
                    <div class="card-body py-3 text-center">
                        <div class="text-warning mb-1"><i class="bi bi-hourglass-split fs-4"></i></div>
                        <h3 class="mb-0"><?php echo $total_pending; ?></h3>
                        <small class="text-muted">Pending</small>
                    </div>
                </div>
            </div>
            <div class="col-md-2 col-6">
                <div class="card stat-card">
                    <div class="card-body py-3 text-center">
                        <div class="text-success mb-1"><i class="bi bi-building-check fs-4"></i></div>
                        <h3 class="mb-0"><?php echo $total_approved; ?></h3>
                        <small class="text-muted">Koperasi Aktif</small>
                    </div>
                </div>
            </div>
            <div class="col-md-2 col-6">
                <div class="card stat-card">
                    <div class="card-body py-3 text-center">
                        <div class="text-primary mb-1"><i class="bi bi-people fs-4"></i></div>
                        <h3 class="mb-0"><?php echo $total_users; ?></h3>
                        <small class="text-muted">Total User</small>
                    </div>
                </div>
            </div>
            <div class="col-md-2 col-6">
                <div class="card stat-card">
                    <div class="card-body py-3 text-center">
                        <div class="text-info mb-1"><i class="bi bi-currency-dollar fs-4"></i></div>
                        <h3 class="mb-0"><?php echo 'Rp ' . number_format($total_revenue, 0, ',', '.'); ?></h3>
                        <small class="text-muted">Revenue</small>
                    </div>
                </div>
            </div>
            <div class="col-md-2 col-6">
                <div class="card stat-card">
                    <div class="card-body py-3 text-center">
                        <div class="text-secondary mb-1"><i class="bi bi-cloud-arrow-up fs-4"></i></div>
                        <h3 class="mb-0"><?php echo number_format($today_api); ?></h3>
                        <small class="text-muted">API Today</small>
                    </div>
                </div>
            </div>
            <div class="col-md-2 col-6">
                <div class="card stat-card">
                    <div class="card-body py-3 text-center">
                        <div class="text-danger mb-1"><i class="bi bi-display fs-4"></i></div>
                        <h3 class="mb-0"><?php echo number_format($today_renders); ?></h3>
                        <small class="text-muted">Renders Today</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <!-- Recent Registrations -->
            <div class="col-md-7">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h6 class="mb-0"><i class="bi bi-clock-history"></i> Pendaftaran Terbaru</h6>
                        <a href="<?php echo baseUrl('pages/app_owner/approvals.php'); ?>" class="btn btn-sm btn-outline-primary">Semua</a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0">
                                <thead class="table-light">
                                    <tr><th>Nama</th><th>Koperasi</th><th>Tanggal</th><th>Status</th></tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($recent)): ?>
                                    <tr><td colspan="4" class="text-center text-muted py-3">Belum ada pendaftaran</td></tr>
                                    <?php else: foreach ($recent as $reg): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($reg['nama']); ?></strong><br><small class="text-muted"><?php echo htmlspecialchars($reg['username']); ?></small></td>
                                        <td><?php echo htmlspecialchars($reg['nama_usaha'] ?? '-'); ?></td>
                                        <td><small><?php echo date('d M Y', strtotime($reg['created_at'])); ?></small></td>
                                        <td>
                                            <?php $sc = ['pending'=>'badge-pending','approved'=>'badge-approved','rejected'=>'badge-rejected']; ?>
                                            <span class="badge <?php echo $sc[$reg['status']] ?? 'bg-secondary'; ?>"><?php echo ucfirst($reg['status']); ?></span>
                                        </td>
                                    </tr>
                                    <?php endforeach; endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <!-- AI Advisor -->
            <div class="col-md-5">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h6 class="mb-0"><i class="bi bi-robot"></i> AI Advisor</h6>
                        <a href="<?php echo baseUrl('pages/app_owner/ai_advisor.php'); ?>" class="btn btn-sm btn-outline-primary">Semua</a>
                    </div>
                    <div class="card-body">
                        <?php if (empty($recent_advice)): ?>
                        <p class="text-muted text-center py-3"><i class="bi bi-robot fs-3 d-block mb-2"></i>Belum ada saran AI. Generate saran dari halaman AI Advisor.</p>
                        <?php else: foreach ($recent_advice as $adv): ?>
                        <div class="d-flex gap-2 mb-3 pb-2 border-bottom">
                            <?php $pcolor = ['kritis'=>'danger','tinggi'=>'warning','sedang'=>'info','rendah'=>'secondary']; ?>
                            <span class="badge bg-<?php echo $pcolor[$adv['prioritas']] ?? 'secondary'; ?> align-self-start"><?php echo $adv['prioritas']; ?></span>
                            <div>
                                <strong class="small"><?php echo htmlspecialchars($adv['judul']); ?></strong>
                                <br><small class="text-muted"><?php echo ucfirst($adv['kategori']); ?> &middot; <?php echo date('d M', strtotime($adv['created_at'])); ?></small>
                            </div>
                        </div>
                        <?php endforeach; endif; ?>
                    </div>
                </div>
            </div>
        </div>

<?php include __DIR__ . '/_footer.php'; ?>
