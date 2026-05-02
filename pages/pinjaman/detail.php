<?php
require_once __DIR__ . '/../../config/path.php';
require_once BASE_PATH . '/includes/functions.php';
requireLogin();

// Permission check
if (!hasPermission('view_pinjaman')) {
    header('Location: ' . baseUrl('dashboard.php'));
    exit();
}

$id = $_GET['id'];
$kantor_id = 1; // Single office

// Get pinjaman data
$pinjaman = query("
    SELECT p.*, n.nama, n.telp, n.kode_nasabah, n.alamat, u.nama as petugas_nama
    FROM pinjaman p 
    JOIN nasabah n ON p.nasabah_id = n.id 
    LEFT JOIN users u ON p.petugas_id = u.id
    WHERE p.id = ?
", [$id]);

if (!$pinjaman) {
    header('Location: ' . baseUrl('pages/pinjaman/index.php'));
    exit();
}

$pinjaman = $pinjaman[0];

// Get installments
$angsuran = query("
    SELECT * FROM angsuran
    WHERE pinjaman_id = ?
    ORDER BY no_angsuran
", [$id]);
if (!is_array($angsuran)) {
    $angsuran = [];
}

// Get payments
$pembayaran = query("
    SELECT p.*, a.no_angsuran
    FROM pembayaran p
    JOIN angsuran a ON p.angsuran_id = a.id
    WHERE p.pinjaman_id = ?
    ORDER BY p.tanggal_bayar DESC
", [$id]);
if (!is_array($pembayaran)) {
    $pembayaran = [];
}

// Get statistics
$stats = query("
    SELECT 
        COUNT(*) as total_angsuran,
        SUM(CASE WHEN status = 'lunas' THEN 1 ELSE 0 END) as lunas,
        SUM(CASE WHEN status = 'telat' THEN 1 ELSE 0 END) as telat,
        SUM(CASE WHEN status = 'belum' THEN 1 ELSE 0 END) as belum,
        SUM(total_bayar) as total_dibayar,
        SUM(denda) as total_denda
    FROM angsuran 
    WHERE pinjaman_id = ?
", [$id])[0];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pinjaman - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="../../dashboard.php"><?php echo APP_NAME; ?></a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="../../logout.php">Logout</a>
            </div>
        </div>
    </nav>
    
    <div class="container-fluid">
        <div class="row">
            <nav class="col-md-3 col-lg-2 d-md-block bg-light sidebar">
                <div class="position-sticky pt-3">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="../../dashboard.php">
                                <i class="bi bi-speedometer2"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="../nasabah/index.php">
                                <i class="bi bi-people"></i> Nasabah
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="index.php">
                                <i class="bi bi-cash-stack"></i> Pinjaman
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="../angsuran/index.php">
                                <i class="bi bi-calendar-check"></i> Angsuran
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Detail Pinjaman</h1>
                    <div>
                        <?php if (in_array($pinjaman['status'], ['aktif', 'lunas'])): ?>
                            <a href="cetak_kartu.php?id=<?php echo $pinjaman['id']; ?>" class="btn btn-outline-primary me-2" target="_blank">
                                <i class="bi bi-printer"></i> Cetak Kartu
                            </a>
                        <?php endif; ?>
                        <?php if ($pinjaman['status'] === 'aktif'): ?>
                            <a href="../angsuran/bayar.php?pinjaman_id=<?php echo $pinjaman['id']; ?>" class="btn btn-success me-2">
                                <i class="bi bi-cash"></i> Bayar Angsuran
                            </a>
                        <?php endif; ?>
                        <a href="index.php" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>
                
                <!-- Loan Information -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="bi bi-info-circle"></i> Informasi Pinjaman</h5>
                            </div>
                            <div class="card-body">
                                <table class="table table-borderless">
                                    <tr>
                                        <td><strong>Kode Pinjaman:</strong></td>
                                        <td><?php echo $pinjaman['kode_pinjaman']; ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Nasabah:</strong></td>
                                        <td>
                                            <a href="../nasabah/detail.php?id=<?php echo $pinjaman['nasabah_id']; ?>">
                                                <?php echo $pinjaman['nama']; ?> (<?php echo $pinjaman['kode_nasabah']; ?>)
                                            </a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>Plafon:</strong></td>
                                        <td><?php echo formatRupiah($pinjaman['plafon']); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Frekuensi:</strong></td>
                                        <td>
                                            <?php
                                            $frek = $pinjaman['frekuensi'] ?? 'bulanan';
                                            $freq_class = ['harian' => 'warning', 'mingguan' => 'info', 'bulanan' => 'primary'];
                                            ?>
                                            <span class="badge bg-<?php echo $freq_class[$frek] ?? 'primary'; ?>">
                                                <?php echo getFrequencyLabel($frek); ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>Tenor:</strong></td>
                                        <td><?php echo $pinjaman['tenor']; ?> <?php echo getFrequencyPeriodLabel($frek); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Bunga/Bulan:</strong></td>
                                        <td><?php echo $pinjaman['bunga_per_bulan']; ?>%</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Total Pembayaran:</strong></td>
                                        <td><?php echo formatRupiah($pinjaman['total_pembayaran']); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Angsuran/<?php echo getFrequencyPeriodLabel($frek); ?>:</strong></td>
                                        <td><?php echo formatRupiah($pinjaman['angsuran_total']); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Status:</strong></td>
                                        <td>
                                            <?php
                                            $status_class = [
                                                'pengajuan' => 'info',
                                                'disetujui' => 'warning',
                                                'aktif' => 'success',
                                                'lunas' => 'secondary',
                                                'ditolak' => 'danger'
                                            ];
                                            ?>
                                            <span class="badge bg-<?php echo $status_class[$pinjaman['status']]; ?>">
                                                <?php echo ucfirst($pinjaman['status']); ?>
                                            </span>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="bi bi-calendar"></i> Informasi Tanggal</h5>
                            </div>
                            <div class="card-body">
                                <table class="table table-borderless">
                                    <tr>
                                        <td><strong>Tanggal Akad:</strong></td>
                                        <td><?php echo formatDate($pinjaman['tanggal_akad']); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Jatuh Tempo:</strong></td>
                                        <td><?php echo formatDate($pinjaman['tanggal_jatuh_tempo']); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Petugas:</strong></td>
                                        <td><?php echo $pinjaman['petugas_nama']; ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Tujuan Pinjaman:</strong></td>
                                        <td><?php echo $pinjaman['tujuan_pinjaman'] ?: '-'; ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Tipe Jaminan:</strong></td>
                                        <td>
                                            <?php 
                                            $jaminan_labels = ['tanpa'=>'Tanpa Jaminan','bpkb'=>'BPKB Kendaraan','shm'=>'SHM','ajb'=>'AJB','tabungan'=>'Tabungan/Deposito'];
                                            $jt = $pinjaman['jaminan_tipe'] ?? 'tanpa';
                                            $jt_color = ['tanpa'=>'secondary','bpkb'=>'info','shm'=>'success','ajb'=>'primary','tabungan'=>'warning'];
                                            ?>
                                            <span class="badge bg-<?php echo $jt_color[$jt] ?? 'secondary'; ?>"><?php echo $jaminan_labels[$jt] ?? '-'; ?></span>
                                        </td>
                                    </tr>
                                    <?php if (($pinjaman['jaminan_nilai'] ?? 0) > 0): ?>
                                    <tr>
                                        <td><strong>Nilai Jaminan:</strong></td>
                                        <td><?php echo formatRupiah($pinjaman['jaminan_nilai']); ?></td>
                                    </tr>
                                    <?php endif; ?>
                                    <tr>
                                        <td><strong>Ket. Jaminan:</strong></td>
                                        <td><?php echo $pinjaman['jaminan'] ?: '-'; ?></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Progress Card -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5><i class="bi bi-graph-up"></i> Progress Pembayaran</h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-md-3">
                                <h4><?php echo $stats['total_angsuran']; ?></h4>
                                <p class="text-muted">Total Angsuran</p>
                            </div>
                            <div class="col-md-3">
                                <h4 class="text-success"><?php echo $stats['lunas']; ?></h4>
                                <p class="text-muted">Lunas</p>
                            </div>
                            <div class="col-md-3">
                                <h4 class="text-warning"><?php echo $stats['telat']; ?></h4>
                                <p class="text-muted">Telat</p>
                            </div>
                            <div class="col-md-3">
                                <h4 class="text-danger"><?php echo $stats['belum']; ?></h4>
                                <p class="text-muted">Belum Bayar</p>
                            </div>
                        </div>
                        
                        <div class="progress mt-3" style="height: 25px;">
                            <?php
                            $progress = ($stats['total_angsuran'] > 0) ? ($stats['lunas'] / $stats['total_angsuran']) * 100 : 0;
                            ?>
                            <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo $progress; ?>%">
                                <?php echo number_format($progress, 1); ?>% Lunas
                            </div>
                        </div>
                        
                        <div class="mt-3">
                            <strong>Total Dibayar:</strong> <?php echo formatRupiah($stats['total_dibayar']); ?>
                            <?php if ($stats['total_denda'] > 0): ?>
                                <br>
                                <strong>Total Denda:</strong> <span class="text-danger"><?php echo formatRupiah($stats['total_denda']); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Installment Schedule -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5><i class="bi bi-calendar-check"></i> Jadwal Angsuran</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Jatuh Tempo</th>
                                        <th>Pokok</th>
                                        <th>Bunga</th>
                                        <th>Total</th>
                                        <th>Denda</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($angsuran as $a): ?>
                                        <tr>
                                            <td><?php echo $a['no_angsuran']; ?></td>
                                            <td><?php echo formatDate($a['jatuh_tempo']); ?></td>
                                            <td><?php echo formatRupiah($a['pokok']); ?></td>
                                            <td><?php echo formatRupiah($a['bunga']); ?></td>
                                            <td><?php echo formatRupiah($a['total_angsuran']); ?></td>
                                            <td>
                                                <?php if ($a['denda'] > 0): ?>
                                                    <span class="text-danger"><?php echo formatRupiah($a['denda']); ?></span>
                                                <?php else: ?>
                                                    -
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php
                                                $status_class = [
                                                    'belum' => 'warning',
                                                    'lunas' => 'success',
                                                    'telat' => 'danger'
                                                ];
                                                ?>
                                                <span class="badge bg-<?php echo $status_class[$a['status']]; ?>">
                                                    <?php echo ucfirst($a['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($a['status'] !== 'lunas' && $pinjaman['status'] === 'aktif'): ?>
                                                    <a href="../angsuran/bayar.php?id=<?php echo $a['id']; ?>" class="btn btn-sm btn-success">
                                                        <i class="bi bi-cash"></i> Bayar
                                                    </a>
                                                <?php elseif ($a['status'] === 'lunas'): ?>
                                                    <a href="cetak_kwitansi.php?angsuran_id=<?php echo $a['id']; ?>" class="btn btn-sm btn-outline-primary" target="_blank" title="Cetak Kwitansi">
                                                        <i class="bi bi-printer"></i>
                                                    </a>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <!-- Payment History -->
                <div class="card">
                    <div class="card-header">
                        <h5><i class="bi bi-clock-history"></i> Riwayat Pembayaran</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($pembayaran)): ?>
                            <p class="text-muted">Belum ada riwayat pembayaran</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Tanggal Bayar</th>
                                            <th>Angsuran Ke</th>
                                            <th>Jumlah</th>
                                            <th>Denda</th>
                                            <th>Total</th>
                                            <th>Cara Bayar</th>
                                            <th>Petugas</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($pembayaran as $p): ?>
                                            <tr>
                                                <td><?php echo formatDate($p['tanggal_bayar']); ?></td>
                                                <td><?php echo $p['no_angsuran']; ?></td>
                                                <td><?php echo formatRupiah($p['jumlah_bayar']); ?></td>
                                                <td><?php echo formatRupiah($p['denda']); ?></td>
                                                <td><?php echo formatRupiah($p['total_bayar']); ?></td>
                                                <td><?php echo ucfirst($p['cara_bayar']); ?></td>
                                                <td>
                                                    <?php 
                                                    $petugas = query("SELECT nama FROM users WHERE id = ?", [$p['petugas_id']]);
                                                    echo $petugas[0]['nama'] ?? '-';
                                                    ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
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
