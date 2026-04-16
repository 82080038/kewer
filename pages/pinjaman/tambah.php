<?php
require_once __DIR__ . '/../../config/path.php';
require_once BASE_PATH . '/includes/functions.php';
requireLogin();

// Permission check
if (!hasPermission('manage_pinjaman')) {
    header('Location: ' . baseUrl('dashboard.php'));
    exit();
}

$cabang_id = getCurrentCabang();
$error = '';
$success = '';

// Get active nasabah list
$nasabah_list = query("SELECT id, kode_nasabah, nama FROM nasabah WHERE cabang_id = ? AND status = 'aktif' ORDER BY nama", [$cabang_id]);
if (!is_array($nasabah_list)) {
    $nasabah_list = [];
}

if ($_POST) {
    $nasabah_id = $_POST['nasabah_id'] ?? '';
    $plafon = str_replace(['.', ','], '', $_POST['plafon'] ?? '');
    $tenor = $_POST['tenor'] ?? '';
    $bunga_per_bulan = $_POST['bunga_per_bulan'] ?? '';
    $tanggal_akad = $_POST['tanggal_akad'] ?? '';
    $tujuan_pinjaman = $_POST['tujuan_pinjaman'] ?? '';
    $jaminan = $_POST['jaminan'] ?? '';
    
    // Validation
    if (!$nasabah_id || !$plafon || !$tenor || !$bunga_per_bulan || !$tanggal_akad) {
        $error = 'Semua field wajib diisi';
    } elseif (!is_numeric($plafon) || $plafon <= 0) {
        $error = 'Plafon harus berupa angka positif';
    } elseif (!is_numeric($tenor) || $tenor <= 0 || $tenor > 12) {
        $error = 'Tenor harus antara 1-12 bulan';
    } elseif (!is_numeric($bunga_per_bulan) || $bunga_per_bulan < 0 || $bunga_per_bulan > 10) {
        $error = 'Bunga harus antara 0-10%';
    } else {
        // Check if nasabah has active loan
        $active_loan = query("SELECT id FROM pinjaman WHERE nasabah_id = ? AND status IN ('disetujui', 'aktif')", [$nasabah_id]);
        if ($active_loan) {
            $error = 'Nasabah masih memiliki pinjaman aktif';
        } else {
            // Calculate loan
            $calc = calculateLoan($plafon, $tenor, $bunga_per_bulan);
            
            // Generate kode pinjaman
            $kode_pinjaman = generateKode('PNJ', 'pinjaman', 'kode_pinjaman');
            
            // Calculate due date
            $tanggal_jatuh_tempo = date('Y-m-d', strtotime("+$tenor month", strtotime($tanggal_akad)));
            
            // Insert pinjaman
            $result = query("INSERT INTO pinjaman (
                cabang_id, kode_pinjaman, nasabah_id, plafon, tenor, bunga_per_bulan, 
                total_bunga, total_pembayaran, angsuran_pokok, angsuran_bunga, angsuran_total,
                tanggal_akad, tanggal_jatuh_tempo, tujuan_pinjaman, jaminan, status, petugas_id
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pengajuan', ?)", [
                $cabang_id, $kode_pinjaman, $nasabah_id, $plafon, $tenor, $bunga_per_bulan,
                $calc['total_bunga'], $calc['total_pembayaran'], $calc['angsuran_pokok'], 
                $calc['angsuran_bunga'], $calc['angsuran_total'], $tanggal_akad, 
                $tanggal_jatuh_tempo, $tujuan_pinjaman, $jaminan, getCurrentUser()['id']
            ]);
            
            if ($result) {
                $success = 'Pengajuan pinjaman berhasil dibuat';
                $_POST = [];
            } else {
                $error = 'Gagal membuat pengajuan pinjaman';
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
    <title>Ajukan Pinjaman - <?php echo APP_NAME; ?></title>
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
                            <a class="nav-link active" href="index.php">
                                <i class="bi bi-cash-stack"></i> Pinjaman
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="../angsuran/index.php">
                                <i class="bi bi-calendar-check"></i> Angsuran
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Ajukan Pinjaman Baru</h1>
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
                        <form method="POST" id="loanForm">
                            <?= csrfField() ?>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Nasabah *</label>
                                        <select name="nasabah_id" class="form-select" required>
                                            <option value="">Pilih Nasabah</option>
                                            <?php foreach ($nasabah_list as $n): ?>
                                                <option value="<?php echo $n['id']; ?>" <?php echo ($_POST['nasabah_id'] ?? '') == $n['id'] ? 'selected' : ''; ?>>
                                                    <?php echo $n['kode_nasabah']; ?> - <?php echo $n['nama']; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Plafon Pinjaman *</label>
                                        <div class="input-group">
                                            <span class="input-group-text">Rp</span>
                                            <input type="text" name="plafon" class="form-control" id="plafon" value="<?php echo $_POST['plafon'] ?? ''; ?>" required>
                                        </div>
                                        <small class="form-text">Maksimal: <?php echo formatRupiah(10000000); ?></small>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Tenor *</label>
                                        <div class="input-group">
                                            <input type="number" name="tenor" class="form-control" id="tenor" value="<?php echo $_POST['tenor'] ?? ''; ?>" min="1" max="12" required>
                                            <span class="input-group-text">bulan</span>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Bunga per Bulan *</label>
                                        <div class="input-group">
                                            <input type="number" name="bunga_per_bulan" class="form-control" id="bunga" value="<?php echo $_POST['bunga_per_bulan'] ?? '2.5'; ?>" step="0.1" min="0" max="10" required>
                                            <span class="input-group-text">%</span>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Tanggal Akad *</label>
                                        <input type="date" name="tanggal_akad" class="form-control" value="<?php echo $_POST['tanggal_akad'] ?? date('Y-m-d'); ?>" required>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Tujuan Pinjaman</label>
                                        <textarea name="tujuan_pinjaman" class="form-control" rows="3"><?php echo $_POST['tujuan_pinjaman'] ?? ''; ?></textarea>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Jaminan</label>
                                        <textarea name="jaminan" class="form-control" rows="3"><?php echo $_POST['jaminan'] ?? ''; ?></textarea>
                                    </div>
                                    
                                    <!-- Loan Calculation Preview -->
                                    <div class="card bg-light">
                                        <div class="card-header">
                                            <h6><i class="bi bi-calculator"></i> Simulasi Pinjaman</h6>
                                        </div>
                                        <div class="card-body" id="loanPreview">
                                            <p class="text-muted">Isi data pinjaman untuk melihat simulasi</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="text-end">
                                <a href="index.php" class="btn btn-secondary me-2">Batal</a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-send"></i> Ajukan Pinjaman
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
        function formatRupiah(angka) {
            return new Intl.NumberFormat('id-ID').format(angka);
        }
        
        function calculatePreview() {
            const plafon = parseFloat(document.getElementById('plafon').value.replace(/[^\d]/g, '')) || 0;
            const tenor = parseInt(document.getElementById('tenor').value) || 0;
            const bunga = parseFloat(document.getElementById('bunga').value) || 0;
            
            if (plafon > 0 && tenor > 0 && bunga >= 0) {
                const totalBunga = plafon * (bunga / 100) * tenor;
                const totalPembayaran = plafon + totalBunga;
                const angsuranPokok = plafon / tenor;
                const angsuranBunga = totalBunga / tenor;
                const angsuranTotal = angsuranPokok + angsuranBunga;
                
                document.getElementById('loanPreview').innerHTML = `
                    <table class="table table-sm">
                        <tr>
                            <td>Total Pinjaman:</td>
                            <td class="text-end">Rp ${formatRupiah(totalPembayaran)}</td>
                        </tr>
                        <tr>
                            <td>Total Bunga:</td>
                            <td class="text-end">Rp ${formatRupiah(totalBunga)}</td>
                        </tr>
                        <tr>
                            <td>Angsuran/Bulan:</td>
                            <td class="text-end fw-bold">Rp ${formatRupiah(angsuranTotal)}</td>
                        </tr>
                        <tr>
                            <td>Pokok/Bulan:</td>
                            <td class="text-end">Rp ${formatRupiah(angsuranPokok)}</td>
                        </tr>
                        <tr>
                            <td>Bunga/Bulan:</td>
                            <td class="text-end">Rp ${formatRupiah(angsuranBunga)}</td>
                        </tr>
                    </table>
                `;
            } else {
                document.getElementById('loanPreview').innerHTML = '<p class="text-muted">Isi data pinjaman untuk melihat simulasi</p>';
            }
        }
        
        // Format rupiah input
        document.getElementById('plafon').addEventListener('input', function(e) {
            e.target.value = formatRupiah(e.target.value.replace(/[^\d]/g, ''));
            calculatePreview();
        });
        
        document.getElementById('tenor').addEventListener('input', calculatePreview);
        document.getElementById('bunga').addEventListener('input', calculatePreview);
        
        // Initial calculation
        calculatePreview();
    </script>
</body>
</html>
