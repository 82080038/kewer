<?php
require_once __DIR__ . '/../../config/path.php';
require_once BASE_PATH . '/includes/functions.php';
require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/includes/alamat_helper.php';
require_once BASE_PATH . '/includes/people_helper.php';

$error = '';
$success = '';
$role = $_POST['role'] ?? '';
$cabang_list = query("SELECT id, nama_cabang FROM cabang WHERE status = 'aktif' ORDER BY is_headquarters DESC, nama_cabang ASC");
if (!is_array($cabang_list)) $cabang_list = [];

if ($_POST) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $nama = $_POST['nama'] ?? '';
    $email = $_POST['email'] ?? '';
    $telp = $_POST['telp'] ?? '';
    $role = $_POST['role'] ?? '';
    $cabang_id_post = $_POST['cabang_id'] ?? '';
    $kantor_id = 1; // Single office
    $gaji = $_POST['gaji'] ?? 0;
    $limit_kasbon = $_POST['limit_kasbon'] ?? 0;
    $tanggal_lahir = $_POST['tanggal_lahir'] ?? '';
    $tanggal_masuk = $_POST['tanggal_masuk'] ?? '';
    
    // Get current user to set owner_bos_id
    $current_user = getCurrentUser();
    $owner_bos_id = null;
    
    // If current user is bos, set owner_bos_id to current user's id
    if ($current_user && $current_user['role'] === 'bos') {
        $owner_bos_id = $current_user['id'];
    } 
    // If current user is employee, use their owner_bos_id
    elseif ($current_user && $current_user['owner_bos_id']) {
        $owner_bos_id = $current_user['owner_bos_id'];
    }
    
    // Validation
    if (empty($username) || empty($password) || empty($nama) || empty($role)) {
        $error = 'Username, password, nama, dan role wajib diisi';
    } else {
        // Check duplicate username
        $check = query("SELECT id FROM users WHERE username = ?", [$username]);
        if ($check) {
            $error = 'Username sudah digunakan';
        } else {
            // Hash password
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert user
            $result = query("INSERT INTO users (username, password, nama, email, telp, role, cabang_id, status, owner_bos_id, gaji, limit_kasbon, tanggal_lahir, tanggal_masuk) VALUES (?, ?, ?, ?, ?, ?, ?, 'aktif', ?, ?, ?, ?, ?)", [
                $username, $password_hash, $nama, $email, $telp, $role, $cabang_id_post ?: $kantor_id, $owner_bos_id, $gaji, $limit_kasbon, $tanggal_lahir ?: null, $tanggal_masuk ?: null
            ]);
            
            if ($result) {
                $user_id = query("SELECT LAST_INSERT_ID() as id")[0]['id'];
                
                // Create person record in db_orang for address management
                try {
                    createPerson([
                        'user_id' => 'user_' . $user_id,
                        'street_address' => '',
                        'house_number' => '',
                        'province_id' => null,
                        'regency_id' => null,
                        'district_id' => null,
                        'village_id' => null,
                        'postal_code' => ''
                    ]);
                } catch (Exception $e) {
                    // Log error but don't fail the petugas creation
                    error_log("Failed to create person record: " . $e->getMessage());
                }
                
                $success = 'Petugas berhasil ditambahkan';
                $_POST = [];
            } else {
                $error = 'Gagal menambahkan petugas';
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
    <title>Tambah Petugas - <?php echo APP_NAME; ?></title>
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
                    <h1 class="h2">Tambah Petugas</h1>
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
                            <?= csrfField() ?>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Username *</label>
                                        <input type="text" name="username" class="form-control" value="<?php echo $_POST['username'] ?? ''; ?>" required>
                                        <small class="form-text">Unik, tidak boleh sama dengan petugas lain</small>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Password *</label>
                                        <input type="password" name="password" class="form-control" required>
                                        <small class="form-text">Minimal 6 karakter</small>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Konfirmasi Password *</label>
                                        <input type="password" name="confirm_password" class="form-control" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Nama Lengkap *</label>
                                        <input type="text" name="nama" class="form-control" value="<?php echo $_POST['nama'] ?? ''; ?>" required>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Email</label>
                                        <input type="email" name="email" class="form-control" value="<?php echo $_POST['email'] ?? ''; ?>">
                                        <small class="form-text">Opsional</small>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Role *</label>
                                        <select name="role" class="form-select" id="roleSelect" required>
                                            <option value="">Pilih Role</option>
                                            <option value="bos" <?php echo $role === 'bos' ? 'selected' : ''; ?>>Bos</option>
                                            <option value="manager_pusat" <?php echo $role === 'manager_pusat' ? 'selected' : ''; ?>>Manager Pusat</option>
                                            <option value="manager_cabang" <?php echo $role === 'manager_cabang' ? 'selected' : ''; ?>>Manager Cabang</option>
                                            <option value="admin_pusat" <?php echo $role === 'admin_pusat' ? 'selected' : ''; ?>>Admin Pusat</option>
                                            <option value="admin_cabang" <?php echo $role === 'admin_cabang' ? 'selected' : ''; ?>>Admin Cabang</option>
                                            <option value="petugas_pusat" <?php echo $role === 'petugas_pusat' ? 'selected' : ''; ?>>Petugas Pusat</option>
                                            <option value="petugas_cabang" <?php echo $role === 'petugas_cabang' ? 'selected' : ''; ?>>Petugas Cabang</option>
                                            <option value="karyawan" <?php echo $role === 'karyawan' ? 'selected' : ''; ?>>Karyawan</option>
                                        </select>
                                    </div>
                                    
                                    <div class="mb-3" id="cabangField">
                                        <label class="form-label">Cabang *</label>
                                        <select name="cabang_id" class="form-select" id="cabangSelect">
                                            <option value="">Pilih Cabang</option>
                                            <?php foreach ($cabang_list as $c): ?>
                                                <option value="<?php echo $c['id']; ?>" <?php echo ($_POST['cabang_id'] ?? '') == $c['id'] ? 'selected' : ''; ?>>
                                                    <?php echo $c['nama_cabang']; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <small class="form-text">Tidak wajib untuk Bos</small>
                                    </div>
                                    
                                    <div class="card bg-light">
                                        <div class="card-body">
                                            <h6><i class="bi bi-info-circle"></i> Informasi Role</h6>
                                            <ul class="mb-0 small">
                                                <li><strong>Bos:</strong> Akses penuh ke semua fitur</li>
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
                                    <i class="bi bi-save"></i> Simpan
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
            
            const pusatRoles = ['bos', 'manager_pusat', 'admin_pusat', 'petugas_pusat'];
            if (pusatRoles.includes(role)) {
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
