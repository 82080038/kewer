<?php
require_once __DIR__ . '/../../config/path.php';
require_once BASE_PATH . '/includes/functions.php';
require_once BASE_PATH . '/includes/alamat_helper.php';
requireLogin();

// Only users with manage_cabang permission can add cabang
if (!hasPermission('manage_cabang')) {
    header('Location: ' . baseUrl('dashboard.php'));
    exit();
}

$error = '';
$success = '';

if ($_POST) {
    $kode_cabang = $_POST['kode_cabang'] ?? '';
    $nama_cabang = $_POST['nama_cabang'] ?? '';
    $alamat = $_POST['alamat'] ?? '';
    $province_id = $_POST['province_id'] ?? '';
    $regency_id = $_POST['regency_id'] ?? '';
    $district_id = $_POST['district_id'] ?? '';
    $village_id = $_POST['village_id'] ?? '';
    $telp = $_POST['telp'] ?? '';
    $email = $_POST['email'] ?? '';
    $status = $_POST['status'] ?? 'aktif';
    
    // Check duplicate kode_cabang
    $check = query("SELECT id FROM cabang WHERE kode_cabang = ?", [$kode_cabang]);
    if ($check) {
        $error = 'Kode cabang sudah digunakan';
    } else {
        // Insert cabang
        $result = query("INSERT INTO cabang (kode_cabang, nama_cabang, alamat, province_id, regency_id, district_id, village_id, telp, email, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)", [
            $kode_cabang,
            $nama_cabang,
            $alamat,
            $province_id ?: null,
            $regency_id ?: null,
            $district_id ?: null,
            $village_id ?: null,
            $telp,
            $email,
            $status
        ]);
        
        if ($result) {
            logAudit('CREATE', 'cabang', $result, null, ['kode_cabang' => $kode_cabang, 'nama_cabang' => $nama_cabang]);
            $success = 'Cabang berhasil ditambahkan';
            $_POST = [];
        } else {
            $error = 'Gagal menambahkan cabang';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Cabang - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="../../dashboard.php"><?php echo APP_NAME; ?></a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="index.php">Kembali</a>
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
                                <i class="bi bi-building"></i> Cabang
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Tambah Cabang</h1>
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
                        <a href="index.php" class="btn btn-primary">Lihat Daftar Cabang</a>
                    </div>
                <?php else: ?>
                    <div class="card">
                        <div class="card-body">
                            <form method="POST">
                                <?= csrfField() ?>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Kode Cabang *</label>
                                            <input type="text" name="kode_cabang" class="form-control" value="<?php echo $_POST['kode_cabang'] ?? ''; ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Nama Cabang *</label>
                                            <input type="text" name="nama_cabang" class="form-control" value="<?php echo $_POST['nama_cabang'] ?? ''; ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Telp</label>
                                            <input type="text" name="telp" class="form-control" value="<?php echo $_POST['telp'] ?? ''; ?>">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Email</label>
                                            <input type="email" name="email" class="form-control" value="<?php echo $_POST['email'] ?? ''; ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Provinsi</label>
                                            <?php echo provinceDropdown('province_id', $_POST['province_id'] ?? '', 'onchange="loadRegencies(this.value)"'); ?>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Kabupaten/Kota</label>
                                            <?php echo regencyDropdown('regency_id', $_POST['regency_id'] ?? '', $_POST['province_id'] ?? '', 'onchange="loadDistricts(this.value)"'); ?>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Kecamatan</label>
                                            <?php echo districtDropdown('district_id', $_POST['district_id'] ?? '', $_POST['regency_id'] ?? '', 'onchange="loadVillages(this.value)"'); ?>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Desa/Kelurahan</label>
                                            <?php echo villageDropdown('village_id', $_POST['village_id'] ?? '', $_POST['district_id'] ?? ''); ?>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Keterangan Tambahan (Opsional)</label>
                                            <textarea name="alamat" class="form-control" rows="2" placeholder="Nama jalan, nomor rumah, RT/RW (opsional)"><?php echo $_POST['alamat'] ?? ''; ?></textarea>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Status</label>
                                            <select name="status" class="form-select">
                                                <option value="aktif" <?php echo ($_POST['status'] ?? '') == 'aktif' ? 'selected' : ''; ?>>Aktif</option>
                                                <option value="nonaktif" <?php echo ($_POST['status'] ?? '') == 'nonaktif' ? 'selected' : ''; ?>>Nonaktif</option>
                                            </select>
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
                <?php endif; ?>
            </main>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Load regencies when province changes
        function loadRegencies(provinceId) {
            const regencySelect = document.getElementById('regency_id');
            const districtSelect = document.getElementById('district_id');
            const villageSelect = document.getElementById('village_id');
            
            // Reset dependent dropdowns
            regencySelect.innerHTML = '<option value="">Pilih Kabupaten/Kota</option>';
            districtSelect.innerHTML = '<option value="">Pilih Kecamatan</option>';
            villageSelect.innerHTML = '<option value="">Pilih Desa/Kelurahan</option>';
            
            if (!provinceId) return;
            
            fetch('/kewer/api/alamat.php?action=regencies&province_id=' + provinceId)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        data.data.forEach(regency => {
                            const option = document.createElement('option');
                            option.value = regency.id;
                            option.textContent = regency.name;
                            regencySelect.appendChild(option);
                        });
                    }
                })
                .catch(error => console.error('Error loading regencies:', error));
        }
        
        // Load districts when regency changes
        function loadDistricts(regencyId) {
            const districtSelect = document.getElementById('district_id');
            const villageSelect = document.getElementById('village_id');
            
            // Reset dependent dropdown
            districtSelect.innerHTML = '<option value="">Pilih Kecamatan</option>';
            villageSelect.innerHTML = '<option value="">Pilih Desa/Kelurahan</option>';
            
            if (!regencyId) return;
            
            fetch('/kewer/api/alamat.php?action=districts&regency_id=' + regencyId)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        data.data.forEach(district => {
                            const option = document.createElement('option');
                            option.value = district.id;
                            option.textContent = district.name;
                            districtSelect.appendChild(option);
                        });
                    }
                })
                .catch(error => console.error('Error loading districts:', error));
        }
        
        // Load villages when district changes
        function loadVillages(districtId) {
            const villageSelect = document.getElementById('village_id');
            
            // Reset dependent dropdown
            villageSelect.innerHTML = '<option value="">Pilih Desa/Kelurahan</option>';
            
            if (!districtId) return;
            
            fetch('/kewer/api/alamat.php?action=villages&district_id=' + districtId)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        data.data.forEach(village => {
                            const option = document.createElement('option');
                            option.value = village.id;
                            option.textContent = village.name;
                            villageSelect.appendChild(option);
                        });
                    }
                })
                .catch(error => console.error('Error loading villages:', error));
        }
    </script>
</body>
</html>
