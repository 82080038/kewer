<?php
require_once 'includes/functions.php';
requireLogin();

$user = getCurrentUser();
$cabang_id = getCurrentCabang();

// Get dashboard stats
$total_nasabah = query("SELECT COUNT(*) as total FROM nasabah WHERE cabang_id = ? AND status = 'aktif'", [$cabang_id])[0]['total'];
$total_pinjaman = query("SELECT COUNT(*) as total FROM pinjaman WHERE cabang_id = ? AND status = 'aktif'", [$cabang_id])[0]['total'];
$outstanding = query("SELECT SUM(plafon) as total FROM pinjaman WHERE cabang_id = ? AND status = 'aktif'", [$cabang_id])[0]['total'];
$late_payments = count(checkLatePayments());

// Get recent activities
$recent_activities = query("
    SELECT 
        CASE 
            WHEN p.id IS NOT NULL THEN CONCAT('Pinjaman ', p.kode_pinjaman, ' untuk ', n.nama)
            WHEN pemb.id IS NOT NULL THEN CONCAT('Pembayaran ', pemb.jumlah, ' dari ', n.nama)
            ELSE 'Aktivitas lain'
        END as activity,
        created_at
    FROM (
        SELECT id, kode_pinjaman, nasabah_id, created_at FROM pinjaman WHERE cabang_id = ?
        UNION ALL
        SELECT id, NULL as kode_pinjaman, angsuran_id as nasabah_id, created_at FROM pembayaran WHERE cabang_id = ?
    ) recent
    LEFT JOIN pinjaman p ON recent.id = p.id AND recent.kode_pinjaman IS NOT NULL
    LEFT JOIN pembayaran pemb ON recent.id = pemb.id AND pemb.kode_pinjaman IS NULL
    LEFT JOIN nasabah n ON 
        (p.nasabah_id = n.id OR pemb.angsuran_id IN (SELECT id FROM angsuran WHERE pinjaman_id = p.id))
    ORDER BY created_at DESC
    LIMIT 5
", [$cabang_id, $cabang_id]);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Kewer</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .border-left-primary { border-left: 4px solid #4e73df !important; }
        .border-left-success { border-left: 4px solid #1cc88a !important; }
        .border-left-info { border-left: 4px solid #36b9cc !important; }
        .border-left-warning { border-left: 4px solid #f6c23e !important; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">Kewer</a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text me-3">
                    <i class="bi bi-person-circle"></i> <?php echo $user['nama']; ?>
                </span>
                <a class="nav-link" href="logout.php">Logout</a>
            </div>
        </div>
    </nav>
    
    <div class="container-fluid">
        <div class="row">
            <nav class="col-md-3 col-lg-2 d-md-block bg-light sidebar">
                <div class="position-sticky pt-3">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="dashboard.php">
                                <i class="bi bi-speedometer2"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="pages/nasabah/index.php">
                                <i class="bi bi-people"></i> Nasabah
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="pages/pinjaman/index.php">
                                <i class="bi bi-cash-stack"></i> Pinjaman
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="pages/angsuran/index.php">
                                <i class="bi bi-calendar-check"></i> Angsuran
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="pages/petugas/index.php">
                                <i class="bi bi-person-badge"></i> Petugas
                            </a>
                        </li>
                        <?php if ($user['role'] === 'superadmin'): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="pages/cabang/index.php">
                                <i class="bi bi-building"></i> Cabang
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </nav>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Dashboard</h1>
                    <?php if ($user['role'] === 'superadmin'): ?>
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
                
                <!-- Recent Activities -->
                <div class="card">
                    <div class="card-header">
                        <h5>Aktivitas Terkini</h5>
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
                                            <?php echo formatDate($activity['created_at'], 'd M Y H:i'); ?>
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
    <script>
        // Cabang selector
        document.getElementById('cabangSelector')?.addEventListener('change', function() {
            const url = new URL(window.location);
            url.searchParams.set('cabang_id', this.value);
            window.location = url;
        });
    </script>
</body>
</html>
