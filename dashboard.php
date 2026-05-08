<?php
require_once 'config/path.php';
require_once BASE_PATH . '/includes/functions.php';
require_once BASE_PATH . '/includes/feature_flags.php';
require_once BASE_PATH . '/includes/chart_helper.php';
requireLogin();

$user = getCurrentUser();
$role = $user['role'];
$user_cabang_id = $user['cabang_id'] ?? null;

// appOwner has their own dashboard — redirect
if ($role === 'appOwner') {
    header('Location: ' . baseUrl('pages/app_owner/dashboard.php'));
    exit();
}

// Get cabang filter based on role
$cabang_filter = getCabangFilterForRole($role, $user_cabang_id, $user['id']);
if ($cabang_filter) {
    $cabang_filter = "AND " . $cabang_filter;
}

// Get stats with cabang filter
$nasabah_result = query("SELECT COUNT(*) as total FROM nasabah WHERE status = 'aktif' $cabang_filter");
$total_nasabah = is_array($nasabah_result) && isset($nasabah_result[0]) ? $nasabah_result[0]['total'] : 0;

$pinjaman_result = query("SELECT COUNT(*) as total FROM pinjaman WHERE status = 'aktif' $cabang_filter");
$total_pinjaman = is_array($pinjaman_result) && isset($pinjaman_result[0]) ? $pinjaman_result[0]['total'] : 0;

$outstanding_result = query("SELECT SUM(plafon) as total FROM pinjaman WHERE status = 'aktif' $cabang_filter");
$outstanding = is_array($outstanding_result) && isset($outstanding_result[0]) ? ($outstanding_result[0]['total'] ?? 0) : 0;

// Get late payments with cabang filter
$late_payments_result = query("SELECT COUNT(*) as total FROM pinjaman WHERE status = 'aktif' AND tanggal_jatuh_tempo < CURDATE() $cabang_filter");
$late_payments = is_array($late_payments_result) && isset($late_payments_result[0]) ? $late_payments_result[0]['total'] : 0;

// Get today's payments (for petugas)
$today_payments_result = query("SELECT COUNT(*) as total FROM pembayaran WHERE DATE(created_at) = CURDATE() $cabang_filter");
$today_payments = is_array($today_payments_result) && isset($today_payments_result[0]) ? $today_payments_result[0]['total'] : 0;

// Get pending approvals (for bos/manager)
$pending_approvals_result = query("SELECT COUNT(*) as total FROM pinjaman WHERE status = 'pengajuan' $cabang_filter");
$pending_approvals = is_array($pending_approvals_result) && isset($pending_approvals_result[0]) ? $pending_approvals_result[0]['total'] : 0;

// Get recent activities from audit_log
$recent_activities = query("
    SELECT 
        a.action as activity,
        a.created_at
    FROM audit_log a
    ORDER BY a.created_at DESC
    LIMIT 5
");

if (!is_array($recent_activities)) {
    $recent_activities = [];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?php echo APP_NAME; ?></title>
    <?php if (isFeatureEnabled('pwa')): ?>
    <link rel="manifest" href="/kewer/manifest.json">
    <meta name="theme-color" content="#2c3e50">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <?php endif; ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .border-left-primary { border-left: 4px solid #4e73df !important; }
        .border-left-success { border-left: 4px solid #1cc88a !important; }
        .border-left-info { border-left: 4px solid #36b9cc !important; }
        .border-left-warning { border-left: 4px solid #f6c23e !important; }
        .chart-container {
            position: relative;
            height: 300px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="main-container">
        <?php require_once BASE_PATH . '/includes/sidebar.php'; ?>

        <main class="content-area">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Dashboard</h1>
                    <?php if ($user['role'] === 'bos'): ?>
                        <select class="form-select" id="cabangSelector" style="width: 200px;">
                            <option value="">Semua Cabang</option>
                            <?php
                            // Only show cabangs owned by this bos
                            $owned_cabangs = getBosOwnedCabangIds();
                            if (!empty($owned_cabangs)) {
                                $placeholders = implode(',', array_fill(0, count($owned_cabangs), '?'));
                                $cabangs = query("SELECT * FROM cabang WHERE status = 'aktif' AND id IN ($placeholders)", $owned_cabangs);
                                if (is_array($cabangs)) {
                                    foreach ($cabangs as $cabang):
                                    ?>
                                        <option value="<?php echo $cabang['id']; ?>">
                                            <?php echo $cabang['nama_cabang']; ?>
                                        </option>
                                    <?php endforeach;
                                }
                            }
                            ?>
                        </select>
                    <?php endif; ?>
                </div>
                
                <!-- Stats Cards -->
                <div class="row mb-4">
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-primary shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                            Total Nasabah
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            <?php echo $total_nasabah; ?>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="bi bi-people fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-success shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                            Pinjaman Aktif
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            <?php echo $total_pinjaman; ?>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="bi bi-cash-stack fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-info shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                            Outstanding
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            Rp <?php echo number_format($outstanding, 0, ',', '.'); ?>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="bi bi-currency-dollar fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-warning shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                            Telat Bayar
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            <?php echo $late_payments; ?>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="bi bi-exclamation-triangle fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Additional Stats for Petugas -->
                <?php if (in_array($role, ['petugas_pusat', 'petugas_cabang'])): ?>
                <div class="row mb-4">
                    <div class="col-xl-6 col-md-6 mb-4">
                        <div class="card border-left-primary shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                            Pembayaran Hari Ini
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            <?php echo $today_payments; ?>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="bi bi-calendar-check fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-6 col-md-6 mb-4">
                        <div class="card border-left-success shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                            Aktivitas Lapangan
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            <?php echo $today_payments; ?> Kunjungan
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="bi bi-geo-alt fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Additional Stats for Bos/Manager -->
                <?php if (in_array($role, ['bos', 'manager_pusat'])): ?>
                <div class="row mb-4">
                    <div class="col-xl-6 col-md-6 mb-4">
                        <div class="card border-left-warning shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                            Menunggu Approval
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            <?php echo $pending_approvals; ?>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="bi bi-clock fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-6 col-md-6 mb-4">
                        <div class="card border-left-danger shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                            Total Cabang
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            <?php 
                                            if ($role === 'bos') {
                                                echo count(getBosOwnedCabangIds());
                                            } else {
                                                $cabang_count = query("SELECT COUNT(*) as total FROM cabang WHERE status = 'aktif'");
                                                echo $cabang_count[0]['total'] ?? 0;
                                            }
                                            ?>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="bi bi-building fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Recent Activities -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Aktivitas Terbaru</h6>
                            </div>
                            <div class="card-body">
                                <?php if (is_array($recent_activities) && count($recent_activities) > 0): ?>
                                    <div class="list-group">
                                        <?php foreach ($recent_activities as $activity): ?>
                                            <div class="list-group-item">
                                                <div class="d-flex w-100 justify-content-between">
                                                    <h6 class="mb-1"><?php echo htmlspecialchars($activity['activity']); ?></h6>
                                                    <small><?php echo date('d/m/Y H:i', strtotime($activity['created_at'])); ?></small>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <p class="text-muted">Belum ada aktivitas.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts Row -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="bi bi-bar-chart"></i> Pinjaman per Bulan</h5>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="pinjamanChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="bi bi-pie-chart"></i> Status Pinjaman</h5>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="statusChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Activities -->
                <div class="card">
                    <div class="card-header">
                        <h5><i class="bi bi-clock-history"></i> Aktivitas Terkini</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($recent_activities)): ?>
                            <p class="text-muted">Belum ada aktivitas</p>
                        <?php else: ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($recent_activities as $activity): ?>
                                    <div class="list-group-item d-flex justify-content-between">
                                        <div>
                                            <?php echo $activity['activity']; ?>
                                        </div>
                                        <small class="text-muted">
                                            <?php echo formatDate($activity['created_at'], 'd F Y H:i'); ?>
                                        </small>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        // Cabang selector
        document.getElementById('cabangSelector')?.addEventListener('change', function() {
            const url = new URL(window.location);
            url.searchParams.set('cabang_id', this.value);
            window.location = url;
        });

        // Get chart data from PHP
        const chartData = <?php
            // Monthly loan data for last 6 months with cabang filter
            $monthly_loans = [];
            for ($i = 5; $i >= 0; $i--) {
                $month = date('Y-m', strtotime("-$i months"));
                $month_name = formatDate(strtotime("-$i months"), 'M Y');

                $count = query("SELECT COUNT(*) as total FROM pinjaman WHERE DATE_FORMAT(created_at, '%Y-%m') = ? $cabang_filter", [$month]);

                $monthly_loans[] = [
                    'month' => $month_name,
                    'count' => is_array($count) && isset($count[0]) ? $count[0]['total'] : 0
                ];
            }

            // Loan status distribution with cabang filter
            $status_data = query("SELECT status, COUNT(*) as total FROM pinjaman WHERE 1=1 $cabang_filter GROUP BY status");

            $status_labels = [];
            $status_counts = [];
            if ($status_data && is_array($status_data)) {
                foreach ($status_data as $s) {
                    $status_labels[] = ucfirst($s['status']);
                    $status_counts[] = $s['total'];
                }
            }

            echo json_encode([
                'monthly' => $monthly_loans,
                'status' => [
                    'labels' => $status_labels,
                    'counts' => $status_counts
                ]
            ]);
        ?>;

        // Use chart helper functions
        const monthlyChart = createBarChart('pinjamanChart', 
            chartData.monthly.map(d => d.month),
            [chartData.monthly.map(d => d.count)],
            'Jumlah Pinjaman'
        );

        const statusChart = createDoughnutChart('statusChart',
            chartData.status.labels,
            chartData.status.counts,
            'Status Pinjaman'
        );

        // Service worker disabled to prevent MIME type errors
        // If PWA is needed in production, re-enable with proper configuration
    </script>
</body>
</html>
