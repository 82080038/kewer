<?php
require_once __DIR__ . '/../../config/path.php';
require_once BASE_PATH . '/includes/functions.php';
require_once BASE_PATH . '/src/Reporting/ReportGenerator.php';
requireLogin();

$user = getCurrentUser();
$role = $user['role'];
$pusat_roles = ['bos'];

// Permission check
if (!hasPermission('view_laporan') && !in_array($role, $pusat_roles)) {
    header('Location: ' . baseUrl('dashboard.php'));
    exit();
}

$kantor_id = 1; // Single office
$start_date = $_GET['tanggal_mulai'] ?? date('Y-m-01');
$end_date = $_GET['tanggal_selesai'] ?? date('Y-m-t');
$report_type = $_GET['jenis_laporan'] ?? 'comprehensive';
$report_cabang = $_GET['cabang_id'] ?? '';

$reportGen = new \Kewer\Reporting\ReportGenerator($kantor_id, $start_date, $end_date);

$report = [];
switch ($report_type) {
    case 'financial':
        $report = $reportGen->financialReport();
        break;
    case 'loan_performance':
        $report = $reportGen->loanPerformanceReport();
        break;
    case 'customer':
        $report = $reportGen->customerReport();
        break;
    case 'comprehensive':
    default:
        $report = $reportGen->comprehensiveReport();
        break;
}

// Get branch list for filter
$branches = [];
if (in_array($role, $pusat_roles)) {
    $branches = query("SELECT * FROM cabang WHERE status = 'aktif' ORDER BY nama_cabang");
    if (!is_array($branches)) $branches = [];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <style>
        .report-card { transition: transform 0.2s; }
        .report-card:hover { transform: translateY(-2px); }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="../../dashboard.php"><?php echo APP_NAME; ?></a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text me-3">
                    <i class="bi bi-person-circle"></i> <?php echo $user['nama']; ?>
                </span>
                <a class="nav-link" href="../../logout.php">Logout</a>
            </div>
        </div>
    </nav>
    
    <div class="main-container">
        <?php require_once BASE_PATH . '/includes/sidebar.php'; ?>
        
        <main class="content-area">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2"><i class="bi bi-file-earmark-bar-graph"></i> Laporan</h1>
                    <button class="btn btn-outline-secondary" onclick="window.print()">
                        <i class="bi bi-printer"></i> Cetak
                    </button>
                </div>

                <!-- Filter -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Jenis Laporan</label>
                                <select name="jenis_laporan" class="form-select">
                                    <option value="comprehensive" <?php echo $report_type === 'comprehensive' ? 'selected' : ''; ?>>Komprehensif</option>
                                    <option value="financial" <?php echo $report_type === 'financial' ? 'selected' : ''; ?>>Keuangan</option>
                                    <option value="loan_performance" <?php echo $report_type === 'loan_performance' ? 'selected' : ''; ?>>Kinerja Pinjaman</option>
                                    <option value="customer" <?php echo $report_type === 'customer' ? 'selected' : ''; ?>>Nasabah</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Tanggal Mulai</label>
                                <input type="date" name="tanggal_mulai" class="form-control datepicker flatpickr-date" value="<?php echo $start_date; ?>">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Tanggal Selesai</label>
                                <input type="date" name="tanggal_selesai" class="form-control datepicker flatpickr-date" value="<?php echo $end_date; ?>">
                            </div>
                            <?php if (in_array($role, $pusat_roles)): ?>
                            <div class="col-md-3">
                                <label class="form-label">Cabang</label>
                                <select name="cabang_id" class="form-select">
                                    <option value="">Semua Cabang</option>
                                    <?php foreach ($branches as $branch): ?>
                                        <option value="<?php echo $branch['id']; ?>" <?php echo ($report_cabang == $branch['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($branch['nama_cabang']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <?php endif; ?>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="bi bi-search"></i> Tampilkan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Periode Laporan -->
                <div class="alert alert-info">
                    <i class="bi bi-calendar3"></i> 
                    Periode: <strong><?php echo formatDate($start_date, 'd F Y'); ?></strong> 
                    s/d <strong><?php echo formatDate($end_date, 'd F Y'); ?></strong>
                    <?php if ($report_cabang): ?>
                        | Cabang: <strong><?php 
                            $cab = query("SELECT nama_cabang FROM cabang WHERE id = ?", [$report_cabang]);
                            echo $cab ? htmlspecialchars($cab[0]['nama_cabang']) : '-';
                        ?></strong>
                    <?php else: ?>
                        | <strong>Semua Cabang</strong>
                    <?php endif; ?>
                </div>

                <?php if ($report_type === 'comprehensive' || $report_type === 'financial'): ?>
                <!-- Laporan Keuangan -->
                <h4 class="mb-3"><i class="bi bi-currency-exchange"></i> Laporan Keuangan</h4>
                <div class="row mb-4">
                    <?php $fin = ($report_type === 'comprehensive') ? ($report['financial'] ?? []) : $report; ?>
                    <div class="col-md-3">
                        <div class="card report-card border-primary">
                            <div class="card-body text-center">
                                <h6 class="text-muted">Total Pinjaman Disalurkan</h6>
                                <h3 class="text-primary"><?php echo formatRupiah($fin['loans_disbursed']['total'] ?? 0); ?></h3>
                                <small class="text-muted"><?php echo $fin['loans_disbursed']['count'] ?? 0; ?> pinjaman</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card report-card border-success">
                            <div class="card-body text-center">
                                <h6 class="text-muted">Total Penagihan</h6>
                                <h3 class="text-success"><?php echo formatRupiah($fin['collections']['total'] ?? 0); ?></h3>
                                <small class="text-muted"><?php echo $fin['collections']['count'] ?? 0; ?> pembayaran</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card report-card border-info">
                            <div class="card-body text-center">
                                <h6 class="text-muted">Saldo Outstanding</h6>
                                <h3 class="text-info"><?php echo formatRupiah($fin['outstanding_balance'] ?? 0); ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card report-card border-danger">
                            <div class="card-body text-center">
                                <h6 class="text-muted">Tunggakan</h6>
                                <h3 class="text-danger"><?php echo formatRupiah($fin['overdue']['total'] ?? 0); ?></h3>
                                <small class="text-muted"><?php echo $fin['overdue']['count'] ?? 0; ?> angsuran</small>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <?php if ($report_type === 'comprehensive' || $report_type === 'loan_performance'): ?>
                <!-- Laporan Kinerja Pinjaman -->
                <h4 class="mb-3"><i class="bi bi-graph-up"></i> Kinerja Pinjaman</h4>
                <div class="row mb-4">
                    <?php $loan = ($report_type === 'comprehensive') ? ($report['loan_performance'] ?? []) : $report; ?>
                    <div class="col-md-6">
                        <div class="card report-card">
                            <div class="card-header"><strong>Distribusi Status Pinjaman</strong></div>
                            <div class="card-body">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Status</th>
                                            <th class="text-end">Jumlah</th>
                                            <th class="text-end">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $statusDist = $loan['status_distribution'] ?? [];
                                        if (is_array($statusDist)):
                                            foreach ($statusDist as $status): ?>
                                            <tr>
                                                <td>
                                                    <span class="badge bg-<?php 
                                                        echo match($status['status'] ?? '') {
                                                            'aktif' => 'success',
                                                            'lunas' => 'primary',
                                                            'macet' => 'danger',
                                                            default => 'secondary'
                                                        };
                                                    ?>"><?php echo ucfirst($status['status'] ?? '-'); ?></span>
                                                </td>
                                                <td class="text-end"><?php echo $status['count'] ?? 0; ?></td>
                                                <td class="text-end"><?php echo formatRupiah($status['total'] ?? 0); ?></td>
                                            </tr>
                                        <?php endforeach; endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card report-card">
                            <div class="card-header"><strong>Kinerja Pembayaran</strong></div>
                            <div class="card-body">
                                <?php $perf = $loan['payment_performance'] ?? []; ?>
                                <div class="row text-center">
                                    <div class="col-4">
                                        <h4 class="text-success"><?php echo $perf['on_time'] ?? 0; ?></h4>
                                        <small class="text-muted">Tepat Waktu</small>
                                    </div>
                                    <div class="col-4">
                                        <h4 class="text-danger"><?php echo $perf['late'] ?? 0; ?></h4>
                                        <small class="text-muted">Terlambat</small>
                                    </div>
                                    <div class="col-4">
                                        <h4 class="text-primary"><?php echo $perf['total'] ?? 0; ?></h4>
                                        <small class="text-muted">Total</small>
                                    </div>
                                </div>
                                <?php 
                                $total_perf = ($perf['total'] ?? 0) > 0 ? $perf['total'] : 1;
                                $on_time_pct = round(($perf['on_time'] ?? 0) / $total_perf * 100);
                                ?>
                                <div class="progress mt-3" style="height: 25px;">
                                    <div class="progress-bar bg-success" style="width: <?php echo $on_time_pct; ?>%">
                                        <?php echo $on_time_pct; ?>% Tepat Waktu
                                    </div>
                                    <div class="progress-bar bg-danger" style="width: <?php echo 100 - $on_time_pct; ?>%">
                                        <?php echo 100 - $on_time_pct; ?>% Terlambat
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <?php if ($report_type === 'comprehensive' || $report_type === 'customer'): ?>
                <!-- Laporan Nasabah -->
                <h4 class="mb-3"><i class="bi bi-people"></i> Laporan Nasabah</h4>
                <div class="row mb-4">
                    <?php $cust = ($report_type === 'comprehensive') ? ($report['customer'] ?? []) : $report; ?>
                    <div class="col-md-4">
                        <div class="card report-card border-primary">
                            <div class="card-body text-center">
                                <h6 class="text-muted">Nasabah Baru</h6>
                                <h3 class="text-primary"><?php echo $cust['new_customers'] ?? 0; ?></h3>
                                <small class="text-muted">periode ini</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card report-card border-success">
                            <div class="card-body text-center">
                                <h6 class="text-muted">Nasabah Aktif</h6>
                                <h3 class="text-success"><?php echo $cust['active_customers'] ?? 0; ?></h3>
                                <small class="text-muted">total keseluruhan</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card report-card border-warning">
                            <div class="card-body text-center">
                                <h6 class="text-muted">Nasabah dengan Pinjaman Aktif</h6>
                                <h3 class="text-warning"><?php echo $cust['customers_with_loans'] ?? 0; ?></h3>
                                <small class="text-muted">memiliki pinjaman</small>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

            </main>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/l10n/id.js"></script>
    <script>
        $(document).ready(function() {
            flatpickr('.flatpickr-date', {
                locale: 'id',
                dateFormat: 'Y-m-d',
                allowInput: true,
                altInput: true,
                altFormat: 'd F Y',
                theme: 'light'
            });
        });
    </script>
</body>
</html>
