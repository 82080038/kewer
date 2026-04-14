<?php
require_once '../../includes/functions.php';
requireLogin();

$error = '';
$success = '';

if ($_POST) {
    $nama = $_POST['nama'] ?? '';
    $alamat = $_POST['alamat'] ?? '';
    $ktp = $_POST['ktp'] ?? '';
    $telp = $_POST['telp'] ?? '';
    $jenis_usaha = $_POST['jenis_usaha'] ?? '';
    $lokasi_pasar = $_POST['lokasi_pasar'] ?? '';
    $cabang_id = getCurrentCabang();
    
    // Validation
    if (!validateKTP($ktp)) {
        $error = 'Format KTP tidak valid (16 digit angka)';
    } elseif (!validatePhone($telp)) {
        $error = 'Format telepon tidak valid (08xxxxxxxxxx)';
    } else {
        // Check duplicate KTP
        $check = query("SELECT id FROM nasabah WHERE ktp = ?", [$ktp]);
        if ($check) {
            $error = 'KTP sudah terdaftar';
        } else {
            // Generate kode nasabah
            $kode_nasabah = generateKode('NSB', 'nasabah', 'kode_nasabah');
            
            // Handle file uploads
            $foto_ktp = '';
            $foto_selfie = '';
            
            if (isset($_FILES['foto_ktp']) && $_FILES['foto_ktp']['error'] === 0) {
                $foto_ktp = 'uploads/ktp_' . $kode_nasabah . '_' . time() . '.jpg';
                move_uploaded_file($_FILES['foto_ktp']['tmp_name'], '../../' . $foto_ktp);
            }
            
            if (isset($_FILES['foto_selfie']) && $_FILES['foto_selfie']['error'] === 0) {
                $foto_selfie = 'uploads/selfie_' . $kode_nasabah . '_' . time() . '.jpg';
                move_uploaded_file($_FILES['foto_selfie']['tmp_name'], '../../' . $foto_selfie);
            }
            
            // Insert nasabah
            $result = query("INSERT INTO nasabah (cabang_id, kode_nasabah, nama, alamat, ktp, telp, jenis_usaha, lokasi_pasar, foto_ktp, foto_selfie) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)", [
                $cabang_id, $kode_nasabah, $nama, $alamat, $ktp, $telp, $jenis_usaha, $lokasi_pasar, $foto_ktp, $foto_selfie
            ]);
            
            if ($result) {
                $success = 'Nasabah berhasil ditambahkan';
                // Clear form
                $_POST = [];
            } else {
                $error = 'Gagal menambahkan nasabah';
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
    <title>Tambah Nasabah - Kewer</title>
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
                            <a class="nav-link active" href="index.php">
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
                    <h1 class="h2">Tambah Nasabah</h1>
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
                        <form method="POST" enctype="multipart/form-data">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Nama Lengkap *</label>
                                        <input type="text" name="nama" class="form-control" value="<?php echo $_POST['nama'] ?? ''; ?>" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">No. KTP *</label>
                                        <input type="text" name="ktp" class="form-control" value="<?php echo $_POST['ktp'] ?? ''; ?>" maxlength="16" required>
                                        <small class="form-text">16 digit angka</small>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">No. Telepon *</label>
                                        <input type="tel" name="telp" class="form-control" value="<?php echo $_POST['telp'] ?? ''; ?>" required>
                                        <small class="form-text">08xxxxxxxxxx</small>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Jenis Usaha</label>
                                        <select name="jenis_usaha" class="form-select">
                                            <option value="">Pilih Jenis Usaha</option>
                                            <option value="Pedagang Sayur">Pedagang Sayur</option>
                                            <option value="Pedagang Buah">Pedagang Buah</option>
                                            <option value="Warung Makan">Warung Makan</option>
                                            <option value="Warung Kelontong">Warung Kelontong</option>
                                            <option value="Toko Baju">Toko Baju</option>
                                            <option value="Lainnya">Lainnya</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Alamat</label>
                                        <textarea name="alamat" class="form-control" rows="3"><?php echo $_POST['alamat'] ?? ''; ?></textarea>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Lokasi Pasar/Warung</label>
                                        <input type="text" name="lokasi_pasar" class="form-control" value="<?php echo $_POST['lokasi_pasar'] ?? ''; ?>">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Foto KTP</label>
                                        <div class="input-group">
                                            <input type="file" name="foto_ktp" class="form-control" accept="image/*" id="foto_ktp">
                                            <button type="button" class="btn btn-outline-secondary" onclick="scanKTP()">
                                                <i class="bi bi-camera"></i> Scan OCR
                                            </button>
                                        </div>
                                        <small class="form-text">Upload foto KTP dan klik Scan OCR untuk ekstrak data otomatis</small>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Foto Selfie + KTP</label>
                                        <input type="file" name="foto_selfie" class="form-control" accept="image/*">
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
        function scanKTP() {
            const fileInput = document.getElementById('foto_ktp');
            const file = fileInput.files[0];
            
            if (!file) {
                alert('Pilih foto KTP terlebih dahulu');
                return;
            }
            
            const formData = new FormData();
            formData.append('ktp_image', file);
            
            fetch('/api/ocr?action=scan', {
                method: 'POST',
                headers: {
                    'Authorization': 'Bearer kewer-api-token-2024'
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const extracted = data.data;
                    
                    // Fill form fields with extracted data
                    if (extracted.nama) {
                        document.querySelector('input[name="nama"]').value = extracted.nama;
                    }
                    if (extracted.nik) {
                        document.querySelector('input[name="ktp"]').value = extracted.nik;
                    }
                    if (extracted.alamat) {
                        document.querySelector('textarea[name="alamat"]').value = extracted.alamat;
                    }
                    if (extracted.tempat_lahir) {
                        // Could add tempat_lahir field if needed
                    }
                    if (extracted.tanggal_lahir) {
                        // Could add tanggal_lahir field if needed
                    }
                    
                    alert('Data KTP berhasil diekstrak! Silakan periksa dan lengkapi data yang kurang.');
                } else {
                    alert('Gagal scan KTP: ' + data.error);
                }
            })
            .catch(error => {
                alert('Terjadi kesalahan: ' + error.message);
            });
        }
    </script>
</body>
</html>
