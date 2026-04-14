<?php
require_once '../../includes/functions.php';
requireLogin();
requireRole('superadmin');

$id = $_GET['id'];

// Get petugas data
$petugas = query("SELECT * FROM users WHERE id = ?", [$id]);

if (!$petugas) {
    header('Location: index.php');
    exit();
}

$petugas = $petugas[0];

// Prevent editing superadmin if current user is not superadmin
if ($petugas['role'] === 'superadmin' && getCurrentUser()['role'] !== 'superadmin') {
    header('Location: index.php');
    exit();
}

$error = '';
$success = '';

// Get cabang list
$cabang_list = query("SELECT * FROM cabang WHERE status = 'aktif' ORDER BY nama_cabang");

if ($_POST) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $nama = $_POST['nama'] ?? '';
    $email = $_POST['email'] ?? '';
    $role = $_POST['role'] ?? '';
    $cabang_id = $_POST['cabang_id'] ?? '';
    
    // Validation
    if (!$username || !$nama || !$role) {
        $error = 'Field wajib harus diisi';
    } elseif ($password && $password !== $confirm_password) {
        $error = 'Konfirmasi password tidak cocok';
    } elseif ($password && strlen($password) < 6) {
        $error = 'Password minimal 6 karakter';
    } elseif ($role !== 'superadmin' && !$cabang_id) {
        $error = 'Cabang harus dipilih untuk role selain superadmin';
    } else {
        // Check duplicate username (excluding current user)
        $check = query("SELECT id FROM users WHERE username = ? AND id != ?", [$username, $id]);
        if ($check) {
            $error = 'Username sudah digunakan';
        } else {
            // Check duplicate email (excluding current user)
            if ($email) {
                $check_email = query("SELECT id FROM users WHERE email = ? AND id != ?", [$email, $id]);
                if ($check_email) {
                    $error = 'Email sudah digunakan';
                }
            }
            
            if (!$error) {
                // Build update query
                $fields = "username = ?, nama = ?, email = ?, role = ?, cabang_id = ?";
                $params = [$username, $nama, $email, $role, $cabang_id ?: null];
                
                // Add password if changed
                if ($password) {
                    $fields .= ", password = ?";
                    $params[] = password_hash($password, PASSWORD_DEFAULT);
                }
                
                $params[] = $id;
                
                // Update user
                $result = query("UPDATE users SET $fields WHERE id = ?", $params);
                
                if ($result) {
                    $success = 'Data petugas berhasil diperbarui';
                    // Refresh data
                    $petugas = query("SELECT * FROM users WHERE id = ?", [$id])[0];
                } else {
                    $error = 'Gagal memperbarui data petugas';
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Petugas - Kewer</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="../../dashboard.php">Kewer</a>
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
                            <a class="nav-link" href="../angsuran/index.php">
                                <i class="bi bi-calendar-check"></i> Angsuran
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="index.php">
                                <i class="bi bi-person-badge"></i> Petugas
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="../cabang/index.php">
                                <i class="bi bi-building"></i> Cabang
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Edit Petugas</h1>
                    <a href="index.php" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Kembali
                    </a>
                </div>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <div class="card">
                    <div class="card-body">
                        <form method="POST">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Username *</label>
                                        <input type="text" name="username" class="form-control" value="<?php echo $petugas['username']; ?>" required>
                                        <small class="form-text">Unik, tidak boleh sama dengan petugas lain</small>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Password</label>
                                        <input type="password" name="password" class="form-control" placeholder="Kosongkan jika tidak ingin mengubah">
                                        <small class="form-text">Minimal 6 karakter</small>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Konfirmasi Password</label>
                                        <input type="password" name="confirm_password" class="form-control">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Nama Lengkap *</label>
                                        <input type="text" name="nama" class="form-control" value="<?php echo $petugas['nama']; ?>" required>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Email</label>
                                        <input type="email" name="email" class="form-control" value="<?php echo $petugas['email']; ?>">
                                        <small class="form-text">Opsional</small>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Role *</label>
                                        <select name="role" class="form-select" id="roleSelect" required>
                                            <option value="">Pilih Role</option>
                                            <option value="superadmin" <?php echo $petugas['role'] === 'superadmin' ? 'selected' : ''; ?>>Superadmin</option>
                                            <option value="admin" <?php echo $petugas['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                                            <option value="petugas" <?php echo $petugas['role'] === 'petugas' ? 'selected' : ''; ?>>Petugas</option>
                                        </select>
                                    </div>
                                    
                                    <div class="mb-3" id="cabangField">
                                        <label class="form-label">Cabang *</label>
                                        <select name="cabang_id" class="form-select" id="cabangSelect">
                                            <option value="">Pilih Cabang</option>
                                            <?php foreach ($cabang_list as $c): ?>
                                                <option value="<?php echo $c['id']; ?>" <?php echo $petugas['cabang_id'] == $c['id'] ? 'selected' : ''; ?>>
                                                    <?php echo $c['nama_cabang']; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <small class="form-text">Tidak wajib untuk Superadmin</small>
                                    </div>
                                    
                                    <div class="card bg-light">
                                        <div class="card-body">
                                            <h6><i class="bi bi-info-circle"></i> Informasi Role</h6>
                                            <ul class="mb-0 small">
                                                <li><strong>Superadmin:</strong> Akses penuh ke semua fitur</li>
                                                <li><strong>Admin:</strong> Kelola cabang tertentu</li>
                                                <li><strong>Petugas:</strong> Input data dasar, tidak bisa approve pinjaman</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="text-end">
                                <a href="index.php" class="btn btn-secondary me-2">Batal</a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save"></i> Simpan Perubahan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle cabang field based on role
        document.getElementById('roleSelect').addEventListener('change', function() {
            const role = this.value;
            const cabangField = document.getElementById('cabangField');
            const cabangSelect = document.getElementById('cabangSelect');
            
            if (role === 'superadmin') {
                cabangField.style.display = 'none';
                cabangSelect.required = false;
            } else {
                cabangField.style.display = 'block';
                cabangSelect.required = true;
            }
        });
        
        // Initialize
        document.getElementById('roleSelect').dispatchEvent(new Event('change'));
    </script>
</body>
</html>
