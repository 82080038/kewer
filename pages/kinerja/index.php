<?php
require_once __DIR__ . '/../../config/path.php';
require_once BASE_PATH . '/includes/functions.php';
requireLogin();

$user = getCurrentUser();
$cabang_id = getCurrentCabang();

// Only manager+ can view
if (in_array($user['role'], ['karyawan'])) {
    header('Location: ' . baseUrl('dashboard.php'));
    exit();
}

$bulan = $_GET['bulan'] ?? date('Y-m');
$bulan_start = $bulan . '-01';
$bulan_end = date('Y-m-t', strtotime($bulan_start));
$bulan_label = date('F Y', strtotime($bulan_start));

// Get petugas performance
$where_cabang = "";
$params = [$bulan_start, $bulan_end, $bulan_start, $bulan_end, $bulan_start, $bulan_end, $bulan_start, $bulan_end];
if ($cabang_id && !in_array($user['role'], ['owner', 'admin_pusat', 'petugas_pusat'])) {
    $where_cabang = "AND u.cabang_id = ?";
    $params[] = $cabang_id;
}

$kinerja = query("
    SELECT 
        u.id, u.nama, u.role,
        (SELECT COUNT(*) FROM pinjaman p2 WHERE p2.petugas_id = u.id AND p2.created_at BETWEEN ? AND ?) as total_pinjaman_baru,
        (SELECT COALESCE(SUM(a2.total_bayar), 0) FROM angsuran a2 JOIN pinjaman p3 ON a2.pinjaman_id = p3.id WHERE p3.petugas_id = u.id AND a2.tanggal_bayar BETWEEN ? AND ? AND a2.status = 'lunas') as total_kutipan,
        (SELECT COUNT(*) FROM angsuran a3 JOIN pinjaman p4 ON a3.pinjaman_id = p4.id WHERE p4.petugas_id = u.id AND a3.tanggal_bayar BETWEEN ? AND ? AND a3.status = 'lunas') as total_angsuran_terbayar,
        (SELECT COUNT(*) FROM angsuran a4 JOIN pinjaman p5 ON a4.pinjaman_id = p5.id WHERE p5.petugas_id = u.id AND a4.status = 'telat' AND a4.jatuh_tempo BETWEEN ? AND ?) as total_telat
    FROM users u
    WHERE u.role IN ('petugas_cabang','petugas_pusat') AND u.status = 'aktif' $where_cabang
    ORDER BY total_kutipan DESC
", $params);
if (!is_array($kinerja)) $kinerja = [];

// Calculate rankings & totals
$grand_kutipan = 0;
$grand_terbayar = 0;
$grand_pinjaman = 0;
$grand_telat = 0;
foreach ($kinerja as $k) {
    $grand_kutipan += $k['total_kutipan'];
    $grand_terbayar += $k['total_angsuran_terbayar'];
    $grand_pinjaman += $k['total_pinjaman_baru'];
    $grand_telat += $k['total_telat'];
}

// Collection rate per petugas
foreach ($kinerja as &$k) {
    $target = query("SELECT COUNT(*) as c FROM angsuran a JOIN pinjaman p ON a.pinjaman_id = p.id WHERE p.petugas_id = ? AND a.jatuh_tempo BETWEEN ? AND ?", [$k['id'], $bulan_start, $bulan_end]);
    $k['target_angsuran'] = (is_array($target) && isset($target[0])) ? (int)$target[0]['c'] : 0;
    $k['collection_rate'] = $k['target_angsuran'] > 0 ? round(($k['total_angsuran_terbayar'] / $k['target_angsuran']) * 100, 1) : 0;
}
unset($k);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kinerja Petugas - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .sidebar { min-height: calc(100vh - 56px); }
        .perf-bar { height: 8px; border-radius: 4px; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="../../dashboard.php"><?php echo APP_NAME; ?></a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="../../dashboard.php">Dashboard</a>
                <a class="nav-link" href="../../logout.php">Logout</a>
            </div>
        </div>
    </nav>
    
    <div class="container-fluid">
        <div class="row">
            <nav class="col-md-3 col-lg-2 d-md-block bg-light sidebar">
                <div class="position-sticky pt-3">
                    <ul class="nav flex-column">
                        <li class="nav-item"><a class="nav-link" href="../../dashboard.php"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
                        <li class="nav-item"><a class="nav-link" href="../pinjaman/index.php"><i class="bi bi-cash-stack"></i> Pinjaman</a></li>
                        <li class="nav-item"><a class="nav-link" href="../angsuran/index.php"><i class="bi bi-calendar-check"></i> Angsuran</a></li>
                        <li class="nav-item"><a class="nav-link" href="../rute_harian/index.php"><i class="bi bi-map"></i> Rute Harian</a></li>
                        <li class="nav-item"><a class="nav-link active" href="index.php"><i class="bi bi-bar-chart"></i> Kinerja Petugas</a></li>
                    </ul>
                </div>
            </nav>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2"><i class="bi bi-bar-chart"></i> Dashboard Kinerja Petugas</h1>
                </div>
                
                <!-- Month Filter -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-2 align-items-end">
                            <div class="col-md-3">
                                <label class="form-label form-label-sm">Bulan</label>
                                <input type="month" name="bulan" class="form-control form-control-sm" value="<?php echo $bulan; ?>">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-sm btn-primary"><i class="bi bi-search"></i> Filter</button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Summary -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body py-2">
                                <h6>Total Petugas</h6>
                                <h4><?php echo count($kinerja); ?></h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body py-2">
                                <h6>Total Kutipan</h6>
                                <h4><?php echo formatRupiah($grand_kutipan); ?></h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-info text-white">
                            <div class="card-body py-2">
                                <h6>Angsuran Terbayar</h6>
                                <h4><?php echo number_format($grand_terbayar); ?></h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-danger text-white">
                            <div class="card-body py-2">
                                <h6>Angsuran Telat</h6>
                                <h4><?php echo number_format($grand_telat); ?></h4>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Performance Table -->
                <div class="card">
                    <div class="card-header">
                        <h5><i class="bi bi-trophy"></i> Peringkat Kinerja - <?php echo $bulan_label; ?></h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($kinerja)): ?>
                            <div class="alert alert-info"><i class="bi bi-info-circle"></i> Tidak ada data petugas</div>
                        <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>#</th>
                                        <th>Petugas</th>
                                        <th class="text-end">Pinjaman Baru</th>
                                        <th class="text-end">Angsuran Terbayar</th>
                                        <th class="text-end">Target</th>
                                        <th>Collection Rate</th>
                                        <th class="text-end">Total Kutipan</th>
                                        <th class="text-end">Telat</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $rank = 1; foreach ($kinerja as $k): ?>
                                    <tr>
                                        <td>
                                            <?php if ($rank === 1): ?>
                                                <span class="badge bg-warning text-dark"><i class="bi bi-trophy-fill"></i> 1</span>
                                            <?php elseif ($rank === 2): ?>
                                                <span class="badge bg-secondary">2</span>
                                            <?php elseif ($rank === 3): ?>
                                                <span class="badge bg-danger">3</span>
                                            <?php else: ?>
                                                <?php echo $rank; ?>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <strong><?php echo $k['nama']; ?></strong>
                                            <br><small class="text-muted"><?php echo $k['role']; ?></small>
                                        </td>
                                        <td class="text-end"><?php echo $k['total_pinjaman_baru']; ?></td>
                                        <td class="text-end"><?php echo $k['total_angsuran_terbayar']; ?></td>
                                        <td class="text-end"><?php echo $k['target_angsuran']; ?></td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="flex-grow-1">
                                                    <div class="progress perf-bar">
                                                        <?php 
                                                        $rate = $k['collection_rate'];
                                                        $bar_color = $rate >= 90 ? 'success' : ($rate >= 70 ? 'warning' : 'danger');
                                                        ?>
                                                        <div class="progress-bar bg-<?php echo $bar_color; ?>" style="width: <?php echo min($rate, 100); ?>%"></div>
                                                    </div>
                                                </div>
                                                <small class="fw-bold <?php echo $rate >= 90 ? 'text-success' : ($rate >= 70 ? 'text-warning' : 'text-danger'); ?>"><?php echo $rate; ?>%</small>
                                            </div>
                                        </td>
                                        <td class="text-end"><strong><?php echo formatRupiah($k['total_kutipan']); ?></strong></td>
                                        <td class="text-end">
                                            <?php if ($k['total_telat'] > 0): ?>
                                                <span class="badge bg-danger"><?php echo $k['total_telat']; ?></span>
                                            <?php else: ?>
                                                <span class="badge bg-success">0</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php $rank++; endforeach; ?>
                                </tbody>
                                <tfoot class="table-secondary">
                                    <tr>
                                        <td colspan="2"><strong>Total</strong></td>
                                        <td class="text-end"><strong><?php echo $grand_pinjaman; ?></strong></td>
                                        <td class="text-end"><strong><?php echo $grand_terbayar; ?></strong></td>
                                        <td class="text-end"><strong><?php echo array_sum(array_column($kinerja, 'target_angsuran')); ?></strong></td>
                                        <td>
                                            <?php 
                                            $total_target_all = array_sum(array_column($kinerja, 'target_angsuran'));
                                            $overall_rate = $total_target_all > 0 ? round(($grand_terbayar / $total_target_all) * 100, 1) : 0;
                                            ?>
                                            <strong><?php echo $overall_rate; ?>%</strong>
                                        </td>
                                        <td class="text-end"><strong><?php echo formatRupiah($grand_kutipan); ?></strong></td>
                                        <td class="text-end"><strong><?php echo $grand_telat; ?></strong></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
