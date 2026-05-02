<?php
require_once __DIR__ . '/../../config/path.php';
require_once BASE_PATH . '/includes/functions.php';
requireLogin();

// Permission check
if (!hasPermission('manage_pembayaran')) {
    header('Location: ' . baseUrl('dashboard.php'));
    exit();
}

$kantor_id = 1; // Single office
$id = $_GET['id'] ?? '';
if (!$id) {
    header('Location: index.php');
    exit();
}

$pembayaran = query("SELECT * FROM pembayaran WHERE id = ?", [$id]);
if (!$pembayaran) {
    header('Location: index.php');
    exit();
}
$pembayaran = $pembayaran[0];

if ($_POST) {
    $jumlah_bayar = $_POST['jumlah_bayar'] ?? 0;
    $denda = $_POST['denda'] ?? 0;
    $tanggal_bayar = $_POST['tanggal_bayar'] ?? '';
    $cara_bayar = $_POST['cara_bayar'] ?? '';
    $keterangan = $_POST['keterangan'] ?? '';
    
    $total_bayar = $jumlah_bayar + $denda;
    
    $result = query("UPDATE pembayaran SET jumlah_bayar = ?, denda = ?, total_bayar = ?, tanggal_bayar = ?, cara_bayar = ?, keterangan = ? WHERE id = ?", [
        $jumlah_bayar,
        $denda,
        $total_bayar,
        $tanggal_bayar,
        $cara_bayar,
        $keterangan,
        $id
    ]);
    
    if ($result) {
        logAudit('UPDATE', 'pembayaran', $id, $pembayaran, ['jumlah_bayar' => $jumlah_bayar, 'total_bayar' => $total_bayar]);
        $_SESSION['success'] = 'Pembayaran berhasil diupdate';
        header('Location: index.php');
        exit();
    } else {
        $_SESSION['error'] = 'Gagal mengupdate pembayaran';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Pembayaran - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
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
                        <li class="nav-item">
                            <a class="nav-link" href="../../dashboard.php">
                                <i class="bi bi-speedometer2"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="index.php">
                                <i class="bi bi-cash"></i> Pembayaran
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Edit Pembayaran</h1>
                    <a href="index.php" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Kembali
                    </a>
                </div>
                
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
                <?php endif; ?>
                
                <div class="card">
                    <div class="card-body">
                        <form method="POST">
                            <div class="mb-3">
                                <label>Kode Pembayaran</label>
                                <input type="text" class="form-control" value="<?= $pembayaran['kode_pembayaran'] ?>" disabled>
                            </div>
                            <div class="mb-3">
                                <label>Jumlah Bayar *</label>
                                <input type="number" name="jumlah_bayar" class="form-control" value="<?= $pembayaran['jumlah_bayar'] ?>" required>
                            </div>
                            <div class="mb-3">
                                <label>Denda</label>
                                <input type="number" name="denda" class="form-control" value="<?= $pembayaran['denda'] ?>">
                            </div>
                            <div class="mb-3">
                                <label>Tanggal Bayar *</label>
                                <input type="date" name="tanggal_bayar" class="form-control" value="<?= $pembayaran['tanggal_bayar'] ?>" required>
                            </div>
                            <div class="mb-3">
                                <label>Cara Bayar *</label>
                                <select name="cara_bayar" class="form-select" required>
                                    <option value="tunai" <?= $pembayaran['cara_bayar'] == 'tunai' ? 'selected' : '' ?>>Tunai</option>
                                    <option value="transfer" <?= $pembayaran['cara_bayar'] == 'transfer' ? 'selected' : '' ?>>Transfer</option>
                                    <option value="digital" <?= $pembayaran['cara_bayar'] == 'digital' ? 'selected' : '' ?>>Digital</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label>Keterangan</label>
                                <textarea name="keterangan" class="form-control" rows="2"><?= $pembayaran['keterangan'] ?? '' ?></textarea>
                            </div>
                            <div class="text-end">
                                <a href="index.php" class="btn btn-secondary">Batal</a>
                                <button type="submit" class="btn btn-primary">Simpan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
