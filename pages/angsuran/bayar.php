<?php
require_once __DIR__ . '/../../config/path.php';
require_once BASE_PATH . '/includes/functions.php';
requireLogin();

// Permission check
if (!hasPermission('manage_pembayaran')) {
    header('Location: ' . baseUrl('dashboard.php'));
    exit();
}

$cabang_id = getCurrentCabang();
$angsuran_id = $_GET['id'] ?? '';
$pinjaman_id = $_GET['pinjaman_id'] ?? '';

$error = '';
$success = '';

if ($angsuran_id) {
    // Get specific angsuran
    $angsuran = query("
        SELECT a.*, p.kode_pinjaman, p.nasabah_id, n.nama, n.telp
        FROM angsuran a
        JOIN pinjaman p ON a.pinjaman_id = p.id
        JOIN nasabah n ON p.nasabah_id = n.id
        WHERE a.id = ? AND a.cabang_id = ? AND a.status != 'lunas'
    ", [$angsuran_id, $cabang_id]);
    
    if (!$angsuran) {
        header('Location: index.php');
        exit();
    }
    $angsuran = $angsuran[0];
    $pinjaman_id = $angsuran['pinjaman_id'];
} elseif ($pinjaman_id) {
    // Get next unpaid installment
    $angsuran = query("
        SELECT a.*, p.kode_pinjaman, p.nasabah_id, n.nama, n.telp
        FROM angsuran a
        JOIN pinjaman p ON a.pinjaman_id = p.id
        JOIN nasabah n ON p.nasabah_id = n.id
        WHERE a.pinjaman_id = ? AND a.cabang_id = ? AND a.status = 'belum'
        ORDER BY a.no_angsuran ASC
        LIMIT 1
    ", [$pinjaman_id, $cabang_id]);
    
    if (!$angsuran) {
        $_SESSION['error'] = 'Tidak ada angsuran yang belum dibayar';
        header('Location: ../pinjaman/detail.php?id=' . $pinjaman_id);
        exit();
    }
    $angsuran = $angsuran[0];
    $angsuran_id = $angsuran['id'];
} else {
    header('Location: index.php');
    exit();
}

// Calculate late penalty
$hari_terlambat = 0;
$denda = 0;
if ($angsuran['jatuh_tempo'] < date('Y-m-d')) {
    $hari_terlambat = (strtotime(date('Y-m-d')) - strtotime($angsuran['jatuh_tempo'])) / (60 * 60 * 24);
    $denda_setting = query("SELECT setting_value FROM settings WHERE setting_key = 'denda_keterlambatan'")[0]['setting_value'] ?? 0.5;
    $denda = $angsuran['total_angsuran'] * ($denda_setting / 100) * $hari_terlambat;
}

$total_bayar = $angsuran['total_angsuran'] + $denda;

if ($_POST) {
    $jumlah_bayar = str_replace(['.', ','], '', $_POST['jumlah_bayar'] ?? '');
    $cara_bayar = $_POST['cara_bayar'] ?? '';
    $tanggal_bayar = $_POST['tanggal_bayar'] ?? date('Y-m-d');
    
    if (!$jumlah_bayar || !$cara_bayar) {
        $error = 'Semua field wajib diisi';
    } elseif ($jumlah_bayar < $total_bayar) {
        $error = 'Jumlah bayar kurang dari total yang harus dibayar';
    } else {
        // Generate kode pembayaran
        $kode_pembayaran = generateKode('BYR', 'pembayaran', 'kode_pembayaran');
        
        // Insert pembayaran
        $result = query("INSERT INTO pembayaran (
            cabang_id, pinjaman_id, angsuran_id, kode_pembayaran, jumlah_bayar, denda, 
            total_bayar, tanggal_bayar, cara_bayar, petugas_id
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)", [
            $cabang_id, $angsuran['pinjaman_id'], $angsuran_id, $kode_pembayaran,
            $jumlah_bayar, $denda, $total_bayar, $tanggal_bayar, $cara_bayar, getCurrentUser()['id']
        ]);
        
        if ($result) {
            // Update angsuran status
            query("UPDATE angsuran SET status = 'lunas', tanggal_bayar = ?, total_bayar = ?, denda = ? WHERE id = ?", [
                $tanggal_bayar, $jumlah_bayar, $denda, $angsuran_id
            ]);
            
            // Check if all installments are paid
            $angsuran_count_result = query("SELECT COUNT(*) as total, SUM(CASE WHEN status = 'lunas' THEN 1 ELSE 0 END) as lunas FROM angsuran WHERE pinjaman_id = ?", [$angsuran['pinjaman_id']]);
            $angsuran_count = is_array($angsuran_count_result) && isset($angsuran_count_result[0]) ? $angsuran_count_result[0] : ['total' => 0, 'lunas' => 0];
            
            if ($angsuran_count['total'] == $angsuran_count['lunas']) {
                query("UPDATE pinjaman SET status = 'lunas' WHERE id = ?", [$angsuran['pinjaman_id']]);
            }
            
            $success = 'Pembayaran angsuran berhasil';
            
            // Send WhatsApp notification
            $message = "Pembayaran angsuran ke-{$angsuran['no_angsuran']} untuk pinjaman {$angsuran['kode_pinjaman']} telah diterima. Terima kasih.";
            sendWhatsApp($angsuran['telp'], $message);
            
            // Redirect if paid from loan detail
            if ($_GET['from'] === 'loan') {
                header('Location: ../pinjaman/detail.php?id=' . $angsuran['pinjaman_id']);
                exit();
            }
        } else {
            $error = 'Gagal memproses pembayaran';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bayar Angsuran - <?php echo APP_NAME; ?></title>
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
                            <a class="nav-link" href="../pinjaman/index.php">
                                <i class="bi bi-cash-stack"></i> Pinjaman
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="index.php">
                                <i class="bi bi-calendar-check"></i> Angsuran
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Bayar Angsuran</h1>
                    <a href="index.php" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Kembali
                    </a>
                </div>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                    <div class="text-center mb-4">
                        <a href="index.php" class="btn btn-primary">Lanjutkan Pembayaran Lainnya</a>
                        <a href="../pinjaman/detail.php?id=<?php echo $angsuran['pinjaman_id']; ?>" class="btn btn-outline-primary ms-2">Lihat Detail Pinjaman</a>
                    </div>
                <?php else: ?>
                    <!-- Payment Information -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5><i class="bi bi-person"></i> Informasi Nasabah</h5>
                                </div>
                                <div class="card-body">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td><strong>Nama:</strong></td>
                                            <td><?php echo $angsuran['nama']; ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Telepon:</strong></td>
                                            <td>
                                                <a href="https://wa.me/<?php echo str_replace(['+', '-'], '', $angsuran['telp']); ?>" target="_blank">
                                                    <?php echo $angsuran['telp']; ?>
                                                    <i class="bi bi-whatsapp text-success"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5><i class="bi bi-cash-stack"></i> Detail Pinjaman</h5>
                                </div>
                                <div class="card-body">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td><strong>Kode Pinjaman:</strong></td>
                                            <td><?php echo $angsuran['kode_pinjaman']; ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Angsuran Ke:</strong></td>
                                            <td><?php echo $angsuran['no_angsuran']; ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Jatuh Tempo:</strong></td>
                                            <td><?php echo formatDate($angsuran['jatuh_tempo']); ?></td>
                                        </tr>
                                        <?php if ($hari_terlambat > 0): ?>
                                            <tr>
                                                <td><strong>Terlambat:</strong></td>
                                                <td class="text-danger"><?php echo $hari_terlambat; ?> hari</td>
                                            </tr>
                                        <?php endif; ?>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Payment Form -->
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="bi bi-calculator"></i> Rincian Pembayaran</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <div class="row mb-4">
                                    <div class="col-md-4">
                                        <div class="card bg-light">
                                            <div class="card-body text-center">
                                                <h6>Tagihan Angsuran</h6>
                                                <h4><?php echo formatRupiah($angsuran['total_angsuran']); ?></h4>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="card bg-light">
                                            <div class="card-body text-center">
                                                <h6>Denda Keterlambatan</h6>
                                                <h4 class="<?php echo $denda > 0 ? 'text-danger' : ''; ?>">
                                                    <?php echo formatRupiah($denda); ?>
                                                </h4>
                                                <?php if ($hari_terlambat > 0): ?>
                                                    <small class="text-muted"><?php echo $hari_terlambat; ?> hari x 0.5%</small>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="card bg-primary text-white">
                                            <div class="card-body text-center">
                                                <h6>Total Pembayaran</h6>
                                                <h4><?php echo formatRupiah($total_bayar); ?></h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label">Tanggal Bayar *</label>
                                            <input type="date" name="tanggal_bayar" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label">Cara Bayar *</label>
                                            <select name="cara_bayar" class="form-select" required>
                                                <option value="">Pilih Cara Bayar</option>
                                                <option value="tunai">Tunai</option>
                                                <option value="transfer">Transfer Bank</option>
                                                <option value="digital">E-Wallet/Digital</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label">Jumlah Bayar *</label>
                                            <div class="input-group">
                                                <span class="input-group-text">Rp</span>
                                                <input type="text" name="jumlah_bayar" class="form-control" value="<?php echo number_format($total_bayar, 0, ',', '.'); ?>" required>
                                            </div>
                                            <small class="form-text">Minimal: <?php echo formatRupiah($total_bayar); ?></small>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="text-end">
                                    <a href="index.php" class="btn btn-secondary me-2">Batal</a>
                                    <button type="submit" class="btn btn-success">
                                        <i class="bi bi-cash"></i> Proses Pembayaran
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>
            </main>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function formatRupiah(angka) {
            return new Intl.NumberFormat('id-ID').format(angka);
        }
        
        // Format rupiah input
        document.querySelector('input[name="jumlah_bayar"]').addEventListener('input', function(e) {
            e.target.value = formatRupiah(e.target.value.replace(/[^\d]/g, ''));
        });
    </script>
</body>
</html>
