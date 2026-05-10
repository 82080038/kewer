<?php
require_once __DIR__ . '/../../config/path.php';
require_once BASE_PATH . '/includes/functions.php';
requireLogin();
$user = getCurrentUser();
$page_title = 'Usage';

$days = (int)($_GET['days'] ?? 30);
if ($days < 7) $days = 7;
if ($days > 90) $days = 90;

// Per-koperasi usage summary
$koperasi_usage = query("
    SELECT 
        uds.bos_user_id,
        u.nama as bos_nama,
        br.nama_usaha,
        SUM(uds.total_api_calls) as api_calls,
        SUM(uds.total_renders) as renders,
        SUM(uds.total_api_calls) + SUM(uds.total_renders) as total
    FROM usage_daily_summary uds
    JOIN users u ON u.id = uds.bos_user_id
    LEFT JOIN bos_registrations br ON br.username = u.username
    WHERE uds.tanggal >= DATE_SUB(CURDATE(), INTERVAL $days DAY)
    GROUP BY uds.bos_user_id, u.nama, br.nama_usaha
    ORDER BY total DESC
");
if (!is_array($koperasi_usage)) $koperasi_usage = [];

// Daily trend (total)
$daily_trend = query("
    SELECT tanggal, SUM(total_api_calls) as api, SUM(total_renders) as renders
    FROM usage_daily_summary
    WHERE tanggal >= DATE_SUB(CURDATE(), INTERVAL $days DAY)
    GROUP BY tanggal ORDER BY tanggal
");
if (!is_array($daily_trend)) $daily_trend = [];

// Totals
$grand_api = 0; $grand_renders = 0;
foreach ($koperasi_usage as $ku) {
    $grand_api += (int)$ku['api_calls'];
    $grand_renders += (int)$ku['renders'];
}

// Top endpoints
$top_endpoints = query("
    SELECT endpoint, tipe, COUNT(*) as cnt
    FROM usage_log
    WHERE tanggal >= DATE_SUB(CURDATE(), INTERVAL $days DAY)
    GROUP BY endpoint, tipe
    ORDER BY cnt DESC
    LIMIT 15
");
if (!is_array($top_endpoints)) $top_endpoints = [];
?>
<?php include __DIR__ . '/_header.php'; ?>

        <!-- Stats -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card stat-card">
                    <div class="card-body py-3 text-center">
                        <div class="text-primary mb-1"><i class="bi bi-cloud-arrow-up fs-4"></i></div>
                        <h3 class="mb-0"><?php echo number_format($grand_api); ?></h3>
                        <small class="text-muted">API Calls (<?php echo $days; ?>d)</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card">
                    <div class="card-body py-3 text-center">
                        <div class="text-success mb-1"><i class="bi bi-display fs-4"></i></div>
                        <h3 class="mb-0"><?php echo number_format($grand_renders); ?></h3>
                        <small class="text-muted">Page Renders (<?php echo $days; ?>d)</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card">
                    <div class="card-body py-3 text-center">
                        <div class="text-info mb-1"><i class="bi bi-building fs-4"></i></div>
                        <h3 class="mb-0"><?php echo count($koperasi_usage); ?></h3>
                        <small class="text-muted">Koperasi Aktif</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card">
                    <div class="card-body py-3 text-center">
                        <div class="text-warning mb-1"><i class="bi bi-graph-up fs-4"></i></div>
                        <h3 class="mb-0"><?php echo $grand_api + $grand_renders > 0 && $days > 0 ? number_format(($grand_api + $grand_renders) / $days) : 0; ?></h3>
                        <small class="text-muted">Avg/day</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Period selector -->
        <div class="mb-3">
            <div class="btn-group btn-group-sm">
                <a href="?days=7" class="btn btn-<?php echo $days == 7 ? 'dark' : 'outline-dark'; ?>">7 Hari</a>
                <a href="?days=30" class="btn btn-<?php echo $days == 30 ? 'dark' : 'outline-dark'; ?>">30 Hari</a>
                <a href="?days=60" class="btn btn-<?php echo $days == 60 ? 'dark' : 'outline-dark'; ?>">60 Hari</a>
                <a href="?days=90" class="btn btn-<?php echo $days == 90 ? 'dark' : 'outline-dark'; ?>">90 Hari</a>
            </div>
        </div>

        <div class="row g-3">
            <!-- Per-koperasi -->
            <div class="col-md-7">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white"><h6 class="mb-0"><i class="bi bi-bar-chart"></i> Usage per Koperasi</h6></div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0">
                                <thead class="table-light">
                                    <tr><th>Koperasi</th><th class="text-end">API Calls</th><th class="text-end">Renders</th><th class="text-end">Total</th></tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($koperasi_usage)): ?>
                                    <tr><td colspan="4" class="text-center text-muted py-3">Belum ada data usage</td></tr>
                                    <?php else: foreach ($koperasi_usage as $ku): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($ku['nama_usaha'] ?? $ku['bos_nama']); ?></strong>
                                            <br><small class="text-muted"><?php echo htmlspecialchars($ku['bos_nama']); ?></small>
                                        </td>
                                        <td class="text-end"><?php echo number_format($ku['api_calls']); ?></td>
                                        <td class="text-end"><?php echo number_format($ku['renders']); ?></td>
                                        <td class="text-end"><strong><?php echo number_format($ku['total']); ?></strong></td>
                                    </tr>
                                    <?php endforeach; endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Top Endpoints -->
            <div class="col-md-5">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white"><h6 class="mb-0"><i class="bi bi-list-ol"></i> Top Endpoints</h6></div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm mb-0">
                                <thead class="table-light">
                                    <tr><th>Endpoint</th><th>Tipe</th><th class="text-end">Hits</th></tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($top_endpoints)): ?>
                                    <tr><td colspan="3" class="text-center text-muted py-3">-</td></tr>
                                    <?php else: foreach ($top_endpoints as $ep): ?>
                                    <tr>
                                        <td><code class="small"><?php echo htmlspecialchars(basename($ep['endpoint'])); ?></code></td>
                                        <td><span class="badge bg-<?php echo $ep['tipe'] === 'api_call' ? 'primary' : 'success'; ?>"><?php echo $ep['tipe'] === 'api_call' ? 'API' : 'Page'; ?></span></td>
                                        <td class="text-end"><?php echo number_format($ep['cnt']); ?></td>
                                    </tr>
                                    <?php endforeach; endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Daily Trend mini -->
                <?php if (!empty($daily_trend)): ?>
                <div class="card border-0 shadow-sm mt-3">
                    <div class="card-header bg-white"><h6 class="mb-0"><i class="bi bi-graph-up"></i> Trend Harian</h6></div>
                    <div class="card-body" style="max-height:250px; overflow-y:auto;">
                        <?php
                        $max_total = 1;
                        foreach ($daily_trend as $dt) { $t = (int)$dt['api'] + (int)$dt['renders']; if ($t > $max_total) $max_total = $t; }
                        foreach ($daily_trend as $dt):
                            $t = (int)$dt['api'] + (int)$dt['renders'];
                            $pct = ($t / $max_total) * 100;
                        ?>
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <small class="text-muted" style="width:60px"><?php echo date('d M', strtotime($dt['tanggal'])); ?></small>
                            <div class="flex-grow-1">
                                <div class="progress" style="height:16px">
                                    <div class="progress-bar bg-primary" style="width:<?php echo ($dt['api'] / max($max_total,1)) * 100; ?>%" title="API: <?php echo number_format($dt['api']); ?>"></div>
                                    <div class="progress-bar bg-success" style="width:<?php echo ($dt['renders'] / max($max_total,1)) * 100; ?>%" title="Render: <?php echo number_format($dt['renders']); ?>"></div>
                                </div>
                            </div>
                            <small class="text-muted" style="width:50px; text-align:right"><?php echo number_format($t); ?></small>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>

<?php include __DIR__ . '/_footer.php'; ?>
