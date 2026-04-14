<?php
require_once '../../includes/functions.php';
requireLogin();

if (!hasRole('superadmin')) {
    header('Location: ../../dashboard.php');
    exit();
}

$id = $_GET['id'] ?? '';
if (!$id) {
    header('Location: index.php');
    exit();
}

$cabang = query("SELECT * FROM cabang WHERE id = ?", [$id]);
if (!$cabang) {
    header('Location: index.php');
    exit();
}
$cabang = $cabang[0];

if ($_POST) {
    $nama_cabang = $_POST['nama_cabang'] ?? '';
    $alamat = $_POST['alamat'] ?? '';
    $telp = $_POST['telp'] ?? '';
    $email = $_POST['email'] ?? '';
    $kota = $_POST['kota'] ?? '';
    $provinsi = $_POST['provinsi'] ?? '';
    $kode_pos = $_POST['kode_pos'] ?? '';
    $status = $_POST['status'] ?? 'aktif';
    
    $result = query("UPDATE cabang SET nama_cabang = ?, alamat = ?, telp = ?, email = ?, kota = ?, provinsi = ?, kode_pos = ?, status = ? WHERE id = ?", [
        $nama_cabang,
        $alamat,
        $telp,
        $email,
        $kota,
        $provinsi,
        $kode_pos,
        $status,
        $id
    ]);
    
    if ($result) {
        logAudit('UPDATE', 'cabang', $id, $cabang, ['nama_cabang' => $nama_cabang, 'status' => $status]);
        $_SESSION['success'] = 'Cabang berhasil diupdate';
        header('Location: index.php');
        exit();
    } else {
        $_SESSION['error'] = 'Gagal mengupdate cabang';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Cabang - Kewer</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="../../dashboard.php">Kewer</a>
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
                                <i class="bi bi-building"></i> Cabang
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Edit Cabang</h1>
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
                                <label>Kode Cabang</label>
                                <input type="text" class="form-control" value="<?= $cabang['kode_cabang'] ?>" disabled>
                            </div>
                            <div class="mb-3">
                                <label>Nama Cabang *</label>
                                <input type="text" name="nama_cabang" class="form-control" value="<?= $cabang['nama_cabang'] ?>" required>
                            </div>
                            <div class="mb-3">
                                <label>Alamat</label>
                                <textarea name="alamat" class="form-control" rows="2"><?= $cabang['alamat'] ?? '' ?></textarea>
                            </div>
                            <div class="mb-3">
                                <label>Telp</label>
                                <input type="text" name="telp" class="form-control" value="<?= $cabang['telp'] ?? '' ?>">
                            </div>
                            <div class="mb-3">
                                <label>Email</label>
                                <input type="email" name="email" class="form-control" value="<?= $cabang['email'] ?? '' ?>">
                            </div>
                            <div class="mb-3">
                                <label>Kota</label>
                                <input type="text" name="kota" class="form-control" value="<?= $cabang['kota'] ?? '' ?>">
                            </div>
                            <div class="mb-3">
                                <label>Provinsi</label>
                                <input type="text" name="provinsi" class="form-control" value="<?= $cabang['provinsi'] ?? '' ?>">
                            </div>
                            <div class="mb-3">
                                <label>Kode Pos</label>
                                <input type="text" name="kode_pos" class="form-control" value="<?= $cabang['kode_pos'] ?? '' ?>">
                            </div>
                            <div class="mb-3">
                                <label>Status</label>
                                <select name="status" class="form-select">
                                    <option value="aktif" <?= $cabang['status'] == 'aktif' ? 'selected' : '' ?>>Aktif</option>
                                    <option value="nonaktif" <?= $cabang['status'] == 'nonaktif' ? 'selected' : '' ?>>Nonaktif</option>
                                </select>
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
