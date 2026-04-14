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

$user = query("SELECT * FROM users WHERE id = ?", [$id]);
if (!$user) {
    header('Location: index.php');
    exit();
}
$user = $user[0];

if ($_POST) {
    $nama = $_POST['nama'] ?? '';
    $email = $_POST['email'] ?? '';
    $role = $_POST['role'] ?? '';
    $cabang_id = $_POST['cabang_id'] ?? null;
    $gaji = $_POST['gaji'] ?? 0;
    $limit_kasbon = $_POST['limit_kasbon'] ?? 0;
    $status = $_POST['status'] ?? 'aktif';
    
    // Update user
    $result = query("UPDATE users SET nama = ?, email = ?, role = ?, cabang_id = ?, gaji = ?, limit_kasbon = ?, status = ? WHERE id = ?", [
        $nama,
        $email,
        $role,
        $cabang_id,
        $gaji,
        $limit_kasbon,
        $status,
        $id
    ]);
    
    if ($result) {
        // Log audit
        logAudit('UPDATE', 'users', $id, $user, ['nama' => $nama, 'email' => $email, 'role' => $role, 'status' => $status]);
        
        $_SESSION['success'] = 'User berhasil diupdate';
        header('Location: index.php');
        exit();
    } else {
        $_SESSION['error'] = 'Gagal mengupdate user';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User - Kewer</title>
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
                                <i class="bi bi-people"></i> Users
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Edit User</h1>
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
                                <label>Username</label>
                                <input type="text" class="form-control" value="<?= $user['username'] ?>" disabled>
                            </div>
                            <div class="mb-3">
                                <label>Nama *</label>
                                <input type="text" name="nama" class="form-control" value="<?= $user['nama'] ?>" required>
                            </div>
                            <div class="mb-3">
                                <label>Email</label>
                                <input type="email" name="email" class="form-control" value="<?= $user['email'] ?>">
                            </div>
                            <div class="mb-3">
                                <label>Role *</label>
                                <select name="role" class="form-select" required>
                                    <option value="superadmin" <?= $user['role'] == 'superadmin' ? 'selected' : '' ?>>Superadmin</option>
                                    <option value="admin" <?= $user['role'] == 'admin' ? 'selected' : '' ?>>Admin</option>
                                    <option value="petugas" <?= $user['role'] == 'petugas' ? 'selected' : '' ?>>Petugas</option>
                                    <option value="karyawan" <?= $user['role'] == 'karyawan' ? 'selected' : '' ?>>Karyawan</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label>Cabang</label>
                                <select name="cabang_id" class="form-select">
                                    <option value="">Tanpa Cabang</option>
                                    <?php
                                    $cabang = query("SELECT * FROM cabang");
                                    foreach ($cabang as $c): ?>
                                        <option value="<?= $c['id'] ?>" <?= $user['cabang_id'] == $c['id'] ? 'selected' : '' ?>><?= $c['nama_cabang'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label>Gaji</label>
                                <input type="number" name="gaji" class="form-control" value="<?= $user['gaji'] ?? 0 ?>">
                            </div>
                            <div class="mb-3">
                                <label>Limit Kas Bon</label>
                                <input type="number" name="limit_kasbon" class="form-control" value="<?= $user['limit_kasbon'] ?? 0 ?>">
                            </div>
                            <div class="mb-3">
                                <label>Status</label>
                                <select name="status" class="form-select">
                                    <option value="aktif" <?= $user['status'] == 'aktif' ? 'selected' : '' ?>>Aktif</option>
                                    <option value="nonaktif" <?= $user['status'] == 'nonaktif' ? 'selected' : '' ?>>Nonaktif</option>
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
