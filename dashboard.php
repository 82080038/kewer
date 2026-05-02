<?php
require_once 'config/path.php';
require_once BASE_PATH . '/includes/functions.php';
requireLogin();

$user = getCurrentUser();
$role = $user['role'];

// appOwner has their own dashboard — redirect
if ($role === 'appOwner') {
    header('Location: ' . baseUrl('pages/app_owner/dashboard.php'));
    exit();
}

// Single office model - get consolidated stats for all data
$nasabah_result = query("SELECT COUNT(*) as total FROM nasabah WHERE status = 'aktif'");
$total_nasabah = is_array($nasabah_result) && isset($nasabah_result[0]) ? $nasabah_result[0]['total'] : 0;

$pinjaman_result = query("SELECT COUNT(*) as total FROM pinjaman WHERE status = 'aktif'");
$total_pinjaman = is_array($pinjaman_result) && isset($pinjaman_result[0]) ? $pinjaman_result[0]['total'] : 0;

$outstanding_result = query("SELECT SUM(plafon) as total FROM pinjaman WHERE status = 'aktif'");
$outstanding = is_array($outstanding_result) && isset($outstanding_result[0]) ? $outstanding_result[0]['total'] : 0;

$late_payments = count(checkLatePayments());

// Get recent activities from audit_log
$recent_activities_result = mysqli_query($conn, "
    SELECT 
        a.action as activity,
        a.created_at
    FROM audit_log a
    ORDER BY a.created_at DESC
    LIMIT 5
");

$recent_activities = [];
if ($recent_activities_result) {
    while ($row = mysqli_fetch_assoc($recent_activities_result)) {
        $recent_activities[] = $row;
    }
}

// Ensure recent_activities is an array
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
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="#"><?php echo APP_NAME; ?></a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text me-3">
                    <i class="bi bi-person-circle"></i> <?php echo $user['nama']; ?>
                </span>
                <a class="nav-link" href="logout.php">Logout</a>
            </div>
        </div>
    </nav>
    
    <div class="main-container">
        <?php require_once BASE_PATH . '/includes/sidebar.php'; ?>
        
        <main class="content-area">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Dashboard</h1>
                    <?php if ($user['role'] === 'bos'): ?>
                        <select class="form-select" id="cabangSelector" style="width: 200px;">
                            <option value="">Semua Cabang</option>
                            <?php
                            $cabangs = query("SELECT * FROM cabang WHERE status = 'aktif'");
                            foreach ($cabangs as $cabang):
                            ?>
                                <option value="<?php echo $cabang['id']; ?>" <?php echo $cabang_id == $cabang['id'] ? 'selected' : ''; ?>>
                                    <?php echo $cabang['nama_cabang']; ?>
                                </option>
                            <?php endforeach; ?>
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
                                            <?php echo formatRupiah($outstanding); ?>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="bi bi-credit-card fa-2x text-gray-300"></i>
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
                                            Tunggakan
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
            // Monthly loan data for last 6 months
            $monthly_loans = [];
            for ($i = 5; $i >= 0; $i--) {
                $month = date('Y-m', strtotime("-$i months"));
                $month_name = formatDate(strtotime("-$i months"), 'M Y');
                
                if ($cabang_id) {
                    $count = query("SELECT COUNT(*) as total FROM pinjaman WHERE DATE_FORMAT(created_at, '%Y-%m') = ? AND cabang_id = ?", [$month, $cabang_id]);
                } else {
                    $count = query("SELECT COUNT(*) as total FROM pinjaman WHERE DATE_FORMAT(created_at, '%Y-%m') = ?", [$month]);
                }
                
                $monthly_loans[] = [
                    'month' => $month_name,
                    'count' => is_array($count) && isset($count[0]) ? $count[0]['total'] : 0
                ];
            }

            // Loan status distribution
            if ($cabang_id) {
                $status_data = query("SELECT status, COUNT(*) as total FROM pinjaman WHERE cabang_id = ? GROUP BY status", [$cabang_id]);
            } else {
                $status_data = query("SELECT status, COUNT(*) as total FROM pinjaman GROUP BY status");
            }

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

        // Pinjaman per Bulan Chart
        const pinjamanCtx = document.getElementById('pinjamanChart').getContext('2d');
        new Chart(pinjamanCtx, {
            type: 'bar',
            data: {
                labels: chartData.monthly.map(d => d.month),
                datasets: [{
                    label: 'Jumlah Pinjaman',
                    data: chartData.monthly.map(d => d.count),
                    backgroundColor: 'rgba(78, 115, 223, 0.8)',
                    borderColor: 'rgba(78, 115, 223, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });

        // Status Pinjaman Chart
        const statusCtx = document.getElementById('statusChart').getContext('2d');
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: chartData.status.labels,
                datasets: [{
                    data: chartData.status.counts,
                    backgroundColor: [
                        'rgba(28, 200, 138, 0.8)',
                        'rgba(246, 194, 62, 0.8)',
                        'rgba(54, 185, 204, 0.8)',
                        'rgba(231, 74, 59, 0.8)',
                        'rgba(78, 115, 223, 0.8)'
                    ],
                    borderColor: [
                        'rgba(28, 200, 138, 1)',
                        'rgba(246, 194, 62, 1)',
                        'rgba(54, 185, 204, 1)',
                        'rgba(231, 74, 59, 1)',
                        'rgba(78, 115, 223, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    </script>
</body>
</html>
