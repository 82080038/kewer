<?php
require_once __DIR__ . '/../../config/path.php';
require_once BASE_PATH . '/includes/functions.php';
requireLogin();

// Permission check
if (!hasPermission('view_nasabah')) {
    header('Location: ' . baseUrl('dashboard.php'));
    exit();
}

$id = $_GET['id'];
$cabang_id = getCurrentCabang();

// Get nasabah data
$nasabah = query("
    SELECT n.*, c.nama_cabang 
    FROM nasabah n 
    LEFT JOIN cabang c ON n.cabang_id = c.id 
    WHERE n.id = ? AND n.cabang_id = ?
", [$id, $cabang_id]);

if (!$nasabah) {
    header('Location: index.php');
    exit();
}

$nasabah = $nasabah[0];

// Get loan history
$pinjaman = query("
    SELECT * FROM pinjaman
    WHERE nasabah_id = ?
    ORDER BY created_at DESC
", [$id]);
if (!is_array($pinjaman)) {
    $pinjaman = [];
}

// Get active loan
$pinjaman_aktif = query("
    SELECT *,
           (SELECT COUNT(*) FROM angsuran WHERE pinjaman_id = pinjaman.id AND status = 'lunas') as angsuran_lunas,
           (SELECT COUNT(*) FROM angsuran WHERE pinjaman_id = pinjaman.id) as total_angsuran
    FROM pinjaman
    WHERE nasabah_id = ? AND status = 'aktif'
", [$id]);
if (!is_array($pinjaman_aktif)) {
    $pinjaman_aktif = [];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Nasabah - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .img-thumbnail { max-width: 200px; }
    </style>
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
                            <a class="nav-link" href="index.php">
                                <i class="bi bi-people"></i> Nasabah
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="../pinjaman/index.php">
                                <i class="bi bi-cash-stack"></i> Pinjaman
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Detail Nasabah</h1>
                    <div>
                        <a href="edit.php?id=<?php echo $nasabah['id']; ?>" class="btn btn-warning me-2">
                            <i class="bi bi-pencil"></i> Edit
                        </a>
                        <a href="index.php" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>
                
                <!-- Nasabah Information -->
                <div class="row mb-4">
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="bi bi-person"></i> Informasi Nasabah</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <table class="table table-borderless">
                                            <tr>
                                                <td><strong>Kode Nasabah:</strong></td>
                                                <td><?php echo $nasabah['kode_nasabah']; ?></td>
                                            </tr>
                                            <tr>
                                                <td><strong>Nama Lengkap:</strong></td>
                                                <td><?php echo $nasabah['nama']; ?></td>
                                            </tr>
                                            <tr>
                                                <td><strong>No. KTP:</strong></td>
                                                <td><?php echo $nasabah['ktp']; ?></td>
                                            </tr>
                                            <tr>
                                                <td><strong>No. Telepon:</strong></td>
                                                <td>
                                                    <a href="https://wa.me/<?php echo str_replace(['+', '-'], '', $nasabah['telp']); ?>" target="_blank">
                                                        <?php echo $nasabah['telp']; ?>
                                                        <i class="bi bi-whatsapp text-success"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><strong>Email:</strong></td>
                                                <td><?php echo $nasabah['email'] ?: '-'; ?></td>
                                            </tr>
                                        </table>
                                    </div>
                                    <div class="col-md-6">
                                        <table class="table table-borderless">
                                            <tr>
                                                <td><strong>Alamat:</strong></td>
                                                <td><?php echo nl2br($nasabah['alamat']); ?></td>
                                            </tr>
                                            <tr>
                                                <td><strong>Jenis Usaha:</strong></td>
                                                <td><?php echo $nasabah['jenis_usaha'] ?: '-'; ?></td>
                                            </tr>
                                            <tr>
                                                <td><strong>Lokasi Pasar/Warung:</strong></td>
                                                <td><?php echo $nasabah['lokasi_pasar'] ?: '-'; ?></td>
                                            </tr>
                                            <tr>
                                                <td><strong>Status:</strong></td>
                                                <td>
                                                    <?php
                                                    $status_class = [
                                                        'aktif' => 'success',
                                                        'nonaktif' => 'warning',
                                                        'blacklist' => 'danger'
                                                    ];
                                                    ?>
                                                    <span class="badge bg-<?php echo $status_class[$nasabah['status']]; ?>">
                                                        <?php echo ucfirst($nasabah['status']); ?>
                                                    </span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><strong>Tanggal Daftar:</strong></td>
                                                <td><?php echo formatDate($nasabah['created_at']); ?></td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="bi bi-camera"></i> Dokumen</h5>
                            </div>
                            <div class="card-body text-center">
                                <?php if ($nasabah['foto_ktp']): ?>
                                    <p><strong>Foto KTP</strong></p>
                                    <img src="../../<?php echo $nasabah['foto_ktp']; ?>" class="img-thumbnail mb-3" alt="KTP">
                                <?php else: ?>
                                    <p class="text-muted">Foto KTP tidak tersedia</p>
                                <?php endif; ?>
                                
                                <?php if ($nasabah['foto_selfie']): ?>
                                    <p><strong>Foto Selfie + KTP</strong></p>
                                    <img src="../../<?php echo $nasabah['foto_selfie']; ?>" class="img-thumbnail" alt="Selfie">
                                <?php else: ?>
                                    <p class="text-muted">Foto selfie tidak tersedia</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Active Loan Summary -->
                <?php if ($pinjaman_aktif): ?>
                <div class="card mb-4">
                    <div class="card-header bg-warning text-dark">
                        <h5><i class="bi bi-cash-stack"></i> Pinjaman Aktif</h5>
                    </div>
                    <div class="card-body">
                        <?php foreach ($pinjaman_aktif as $pinj): ?>
                        <div class="row">
                            <div class="col-md-3">
                                <strong>Kode:</strong><br>
                                <?php echo $pinj['kode_pinjaman']; ?>
                            </div>
                            <div class="col-md-3">
                                <strong>Plafon:</strong><br>
                                <?php echo formatRupiah($pinj['plafon']); ?>
                            </div>
                            <div class="col-md-3">
                                <strong>Angsuran:</strong><br>
                                <?php echo $pinj['angsuran_lunas']; ?>/<?php echo $pinj['total_angsuran']; ?>
                            </div>
                            <div class="col-md-3">
                                <strong>Status:</strong><br>
                                <span class="badge bg-warning">Aktif</span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Loan History -->
                <div class="card">
                    <div class="card-header">
                        <h5><i class="bi bi-clock-history"></i> Riwayat Pinjaman</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($pinjaman)): ?>
                            <p class="text-muted">Belum ada riwayat pinjaman</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Kode Pinjaman</th>
                                            <th>Plafon</th>
                                            <th>Tenor</th>
                                            <th>Bunga/Bulan</th>
                                            <th>Tanggal Akad</th>
                                            <th>Status</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($pinjaman as $p): ?>
                                            <tr>
                                                <td><?php echo $p['kode_pinjaman']; ?></td>
                                                <td><?php echo formatRupiah($p['plafon']); ?></td>
                                                <td><?php echo $p['tenor']; ?> bulan</td>
                                                <td><?php echo $p['bunga_per_bulan']; ?>%</td>
                                                <td><?php echo formatDate($p['tanggal_akad']); ?></td>
                                                <td>
                                                    <?php
                                                    $status_class = [
                                                        'pengajuan' => 'info',
                                                        'disetujui' => 'primary',
                                                        'aktif' => 'success',
                                                        'lunas' => 'secondary',
                                                        'ditolak' => 'danger'
                                                    ];
                                                    ?>
                                                    <span class="badge bg-<?php echo $status_class[$p['status']]; ?>">
                                                        <?php echo ucfirst($p['status']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="../pinjaman/detail.php?id=<?php echo $p['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
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
