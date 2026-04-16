<?php
require_once __DIR__ . '/../../config/path.php';
require_once BASE_PATH . '/includes/functions.php';
requireLogin();

// Permission check
if (!hasPermission('manage_nasabah')) {
    header('Location: ' . baseUrl('dashboard.php'));
    exit();
}

$id = $_GET['id'];
$cabang_id = getCurrentCabang();

// Get nasabah data
$nasabah = query("SELECT * FROM nasabah WHERE id = ? AND cabang_id = ?", [$id, $cabang_id]);

if (!$nasabah) {
    header('Location: index.php');
    exit();
}

$nasabah = $nasabah[0];

$error = '';
$success = '';

if ($_POST) {
    $nama = $_POST['nama'] ?? '';
    $alamat = $_POST['alamat'] ?? '';
    $province_id = $_POST['province_id'] ?? '';
    $regency_id = $_POST['regency_id'] ?? '';
    $district_id = $_POST['district_id'] ?? '';
    $village_id = $_POST['village_id'] ?? '';
    $telp = $_POST['telp'] ?? '';
    $jenis_usaha = $_POST['jenis_usaha'] ?? '';
    $lokasi_pasar = $_POST['lokasi_pasar'] ?? '';
    $status = $_POST['status'] ?? 'aktif';
    
    // Validation
    if (!validatePhone($telp)) {
        $error = 'Format telepon tidak valid (08xxxxxxxxxx)';
    } else {
        // Handle file uploads
        $foto_ktp = $nasabah['foto_ktp'];
        $foto_selfie = $nasabah['foto_selfie'];
        
        if (isset($_FILES['foto_ktp']) && $_FILES['foto_ktp']['error'] === 0) {
            // Delete old file
            if ($foto_ktp && file_exists('../../' . $foto_ktp)) {
                unlink('../../' . $foto_ktp);
            }
            $foto_ktp = 'uploads/ktp_' . $nasabah['kode_nasabah'] . '_' . time() . '.jpg';
            move_uploaded_file($_FILES['foto_ktp']['tmp_name'], '../../' . $foto_ktp);
        }
        
        if (isset($_FILES['foto_selfie']) && $_FILES['foto_selfie']['error'] === 0) {
            // Delete old file
            if ($foto_selfie && file_exists('../../' . $foto_selfie)) {
                unlink('../../' . $foto_selfie);
            }
            $foto_selfie = 'uploads/selfie_' . $nasabah['kode_nasabah'] . '_' . time() . '.jpg';
            move_uploaded_file($_FILES['foto_selfie']['tmp_name'], '../../' . $foto_selfie);
        }
        
        // Update nasabah
        $result = query("UPDATE nasabah SET nama = ?, alamat = ?, province_id = ?, regency_id = ?, district_id = ?, village_id = ?, telp = ?, jenis_usaha = ?, lokasi_pasar = ?, status = ?, foto_ktp = ?, foto_selfie = ? WHERE id = ?", [
            $nama, $alamat, $province_id ?: null, $regency_id ?: null, $district_id ?: null, $village_id ?: null, $telp, $jenis_usaha, $lokasi_pasar, $status, $foto_ktp, $foto_selfie, $id
        ]);
        
        if ($result) {
            $success = 'Data nasabah berhasil diperbarui';
            // Refresh data
            $nasabah_result = query("SELECT * FROM nasabah WHERE id = ?", [$id]);
            $nasabah = is_array($nasabah_result) && isset($nasabah_result[0]) ? $nasabah_result[0] : null;
        } else {
            $error = 'Gagal memperbarui data nasabah';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Nasabah - <?php echo APP_NAME; ?></title>
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
                    <h1 class="h2">Edit Nasabah</h1>
                    <a href="detail.php?id=<?php echo $nasabah['id']; ?>" class="btn btn-secondary">
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
                        <form method="POST" enctype="multipart/form-data">
                            <?= csrfField() ?>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Kode Nasabah</label>
                                        <input type="text" class="form-control" value="<?php echo $nasabah['kode_nasabah']; ?>" readonly>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Nama Lengkap *</label>
                                        <input type="text" name="nama" class="form-control" value="<?php echo $nasabah['nama']; ?>" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">No. KTP</label>
                                        <input type="text" class="form-control" value="<?php echo $nasabah['ktp']; ?>" readonly>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">No. Telepon *</label>
                                        <input type="tel" name="telp" class="form-control" value="<?php echo $nasabah['telp']; ?>" required>
                                        <small class="form-text">08xxxxxxxxxx</small>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Status</label>
                                        <select name="status" class="form-select">
                                            <option value="aktif" <?php echo $nasabah['status'] === 'aktif' ? 'selected' : ''; ?>>Aktif</option>
                                            <option value="nonaktif" <?php echo $nasabah['status'] === 'nonaktif' ? 'selected' : ''; ?>>Nonaktif</option>
                                            <option value="blacklist" <?php echo $nasabah['status'] === 'blacklist' ? 'selected' : ''; ?>>Blacklist</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Provinsi</label>
                                        <?php echo provinceDropdown('province_id', $nasabah['province_id'] ?? '', 'onchange="loadRegencies(this.value)"'); ?>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Kabupaten/Kota</label>
                                        <?php echo regencyDropdown('regency_id', $nasabah['regency_id'] ?? '', $nasabah['province_id'] ?? '', 'onchange="loadDistricts(this.value)"'); ?>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Kecamatan</label>
                                        <?php echo districtDropdown('district_id', $nasabah['district_id'] ?? '', $nasabah['regency_id'] ?? '', 'onchange="loadVillages(this.value)"'); ?>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Desa/Kelurahan</label>
                                        <?php echo villageDropdown('village_id', $nasabah['village_id'] ?? '', $nasabah['district_id'] ?? ''); ?>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Keterangan Tambahan (Opsional)</label>
                                        <textarea name="alamat" class="form-control" rows="2" placeholder="Nama jalan, nomor rumah, RT/RW (opsional)"><?php echo $nasabah['alamat'] ?? ''; ?></textarea>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Jenis Usaha</label>
                                        <select name="jenis_usaha" class="form-select">
                                            <option value="">Pilih Jenis Usaha</option>
                                            <option value="Pedagang Sayur" <?php echo $nasabah['jenis_usaha'] === 'Pedagang Sayur' ? 'selected' : ''; ?>>Pedagang Sayur</option>
                                            <option value="Pedagang Buah" <?php echo $nasabah['jenis_usaha'] === 'Pedagang Buah' ? 'selected' : ''; ?>>Pedagang Buah</option>
                                            <option value="Warung Makan" <?php echo $nasabah['jenis_usaha'] === 'Warung Makan' ? 'selected' : ''; ?>>Warung Makan</option>
                                            <option value="Warung Kelontong" <?php echo $nasabah['jenis_usaha'] === 'Warung Kelontong' ? 'selected' : ''; ?>>Warung Kelontong</option>
                                            <option value="Toko Baju" <?php echo $nasabah['jenis_usaha'] === 'Toko Baju' ? 'selected' : ''; ?>>Toko Baju</option>
                                            <option value="Lainnya" <?php echo $nasabah['jenis_usaha'] === 'Lainnya' ? 'selected' : ''; ?>>Lainnya</option>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Lokasi Pasar/Warung</label>
                                        <input type="text" name="lokasi_pasar" class="form-control" value="<?php echo $nasabah['lokasi_pasar']; ?>">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Foto KTP</label>
                                        <input type="file" name="foto_ktp" class="form-control" accept="image/*">
                                        <?php if ($nasabah['foto_ktp']): ?>
                                            <small class="form-text">
                                                <a href="../../<?php echo $nasabah['foto_ktp']; ?>" target="_blank">Lihat foto saat ini</a>
                                            </small>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Foto Selfie + KTP</label>
                                        <input type="file" name="foto_selfie" class="form-control" accept="image/*">
                                        <?php if ($nasabah['foto_selfie']): ?>
                                            <small class="form-text">
                                                <a href="../../<?php echo $nasabah['foto_selfie']; ?>" target="_blank">Lihat foto saat ini</a>
                                            </small>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="text-end">
                                <a href="detail.php?id=<?php echo $nasabah['id']; ?>" class="btn btn-secondary me-2">Batal</a>
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
