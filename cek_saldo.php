<?php
require_once __DIR__ . '/config/path.php';
require_once BASE_PATH . '/includes/functions.php';

// No login required for public balance check

$error = '';
$success = '';
$nasabah = null;
$pinjaman_aktif = [];
$total_pinjaman = 0;
$total_angsuran = 0;
$total_dibayar = 0;
$sisa_pinjaman = 0;

if ($_POST) {
    $kode_nasabah = $_POST['kode_nasabah'] ?? '';
    $ktp = $_POST['ktp'] ?? '';
    
    if (empty($kode_nasabah) && empty($ktp)) {
        $error = 'Masukkan Kode Nasabah atau Nomor KTP';
    } else {
        // Search by kode nasabah or KTP
        $where = [];
        $params = [];
        
        if ($kode_nasabah) {
            $where[] = "kode_nasabah = ?";
            $params[] = $kode_nasabah;
        }
        
        if ($ktp) {
            $where[] = "ktp = ?";
            $params[] = $ktp;
        }
        
        $where_clause = implode(" OR ", $where);
        
        $nasabah_result = query("SELECT * FROM nasabah WHERE ($where_clause) AND status = 'aktif'", $params);
        
        if (!$nasabah_result) {
            $error = 'Nasabah tidak ditemukan atau status tidak aktif';
        } else {
            $nasabah = $nasabah_result[0];
            
            // Get active loans
            $pinjaman_aktif_result = query("
                SELECT p.*, 
                    (SELECT COUNT(*) FROM angsuran WHERE pinjaman_id = p.id) as total_angsuran,
                    (SELECT COUNT(*) FROM angsuran WHERE pinjaman_id = p.id AND status = 'lunas') as angsuran_lunas,
                    (SELECT SUM(total_angsuran) FROM angsuran WHERE pinjaman_id = p.id) as total_tagihan,
                    (SELECT SUM(total_bayar) FROM angsuran WHERE pinjaman_id = p.id) as total_dibayar
                FROM pinjaman p
                WHERE p.nasabah_id = ? AND p.status IN ('aktif', 'disetujui')
                ORDER BY p.created_at DESC
            ", [$nasabah['id']]);
            
            if ($pinjaman_aktif_result) {
                $pinjaman_aktif = $pinjaman_aktif_result;
                
                foreach ($pinjaman_aktif as $p) {
                    $total_pinjaman += $p['plafon'];
                    $total_tagihan = $p['total_tagihan'] ?? 0;
                    $total_dibayar_pinjaman = $p['total_dibayar'] ?? 0;
                    $total_angsuran += $total_tagihan;
                    $total_dibayar += $total_dibayar_pinjaman;
                }
                
                $sisa_pinjaman = $total_angsuran - $total_dibayar;
            }
            
            $success = 'Data nasabah ditemukan';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cek Saldo - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        .card {
            border-radius: 20px;
            border: none;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
        }
        .balance-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 20px;
        }
        .balance-amount {
            font-size: 2.5rem;
            font-weight: 700;
        }
        .info-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 15px;
        }
        .loan-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 15px;
            border-left: 5px solid #667eea;
        }
        .progress-bar-animated {
            animation: progress 1s ease-in-out;
        }
        @keyframes progress {
            from { width: 0; }
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <!-- Header -->
                <div class="text-center text-white mb-4">
                    <h1><i class="bi bi-wallet2"></i> Cek Saldo</h1>
                    <p class="mb-0">Cek sisa pinjaman Anda dengan mudah</p>
                </div>

                <!-- Search Form -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Kode Nasabah</label>
                                <input type="text" name="kode_nasabah" class="form-control form-control-lg" placeholder="Contoh: NSB001" value="<?php echo htmlspecialchars($_POST['kode_nasabah'] ?? ''); ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Atau Nomor KTP</label>
                                <input type="text" name="ktp" class="form-control form-control-lg" placeholder="Contoh: 3201xxxxxxxxxxxx" value="<?php echo htmlspecialchars($_POST['ktp'] ?? ''); ?>">
                            </div>
                            <button type="submit" class="btn btn-primary btn-lg w-100">
                                <i class="bi bi-search"></i> Cek Saldo
                            </button>
                        </form>
                    </div>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle"></i> <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <?php if ($nasabah): ?>
                    <!-- Nasabah Info -->
                    <div class="info-card">
                        <h5 class="mb-3"><i class="bi bi-person"></i> Informasi Nasabah</h5>
                        <table class="table table-borderless">
                            <tr>
                                <td width="40%">Nama</td>
                                <td width="60%" class="fw-bold"><?php echo htmlspecialchars($nasabah['nama']); ?></td>
                            </tr>
                            <tr>
                                <td>Kode Nasabah</td>
                                <td class="fw-bold"><?php echo htmlspecialchars($nasabah['kode_nasabah']); ?></td>
                            </tr>
                            <tr>
                                <td>Lokasi Pasar</td>
                                <td><?php echo htmlspecialchars($nasabah['lokasi_pasar'] ?? '-'); ?></td>
                            </tr>
                            <tr>
                                <td>Jenis Usaha</td>
                                <td><?php echo htmlspecialchars($nasabah['jenis_usaha'] ?? '-'); ?></td>
                            </tr>
                        </table>
                    </div>

                    <!-- Balance Summary -->
                    <div class="balance-card">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span><i class="bi bi-cash-stack"></i> Sisa Pinjaman</span>
                            <span class="badge bg-light text-dark">Aktif</span>
                        </div>
                        <div class="balance-amount">
                            Rp<?php echo number_format($sisa_pinjaman, 0, ',', '.'); ?>
                        </div>
                        <div class="row mt-3">
                            <div class="col-6">
                                <small>Total Tagihan</small>
                                <div class="fw-bold">Rp<?php echo number_format($total_angsuran, 0, ',', '.'); ?></div>
                            </div>
                            <div class="col-6">
                                <small>Sudah Dibayar</small>
                                <div class="fw-bold">Rp<?php echo number_format($total_dibayar, 0, ',', '.'); ?></div>
                            </div>
                        </div>
                    </div>

                    <!-- Active Loans -->
                    <?php if (!empty($pinjaman_aktif)): ?>
                        <h5 class="text-white mb-3"><i class="bi bi-list-check"></i> Pinjaman Aktif</h5>
                        <?php foreach ($pinjaman_aktif as $p): ?>
                            <?php 
                                $progress = ($p['total_dibayar'] / $p['total_tagihan']) * 100;
                                $sisa = $p['total_tagihan'] - $p['total_dibayar'];
                            ?>
                            <div class="loan-card">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <div class="fw-bold"><?php echo htmlspecialchars($p['kode_pinjaman']); ?></div>
                                        <small class="text-muted">
                                            Plafon: Rp<?php echo number_format($p['plafon'], 0, ',', '.'); ?> | 
                                            Tenor: <?php echo $p['tenor']; ?> <?php echo $p['frekuensi']; ?>
                                        </small>
                                    </div>
                                    <div class="text-end">
                                        <div class="fw-bold text-primary">Rp<?php echo number_format($sisa, 0, ',', '.'); ?></div>
                                        <small class="text-muted">Sisa</small>
                                    </div>
                                </div>
                                <div class="progress mb-2" style="height: 10px;">
                                    <div class="progress-bar progress-bar-animated bg-success" style="width: <?php echo round($progress); ?>%"></div>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <small class="text-muted">
                                        <?php echo $p['angsuran_lunas']; ?> / <?php echo $p['total_angsuran']; ?> angsuran lunas
                                    </small>
                                    <small class="text-success fw-bold"><?php echo round($progress); ?>%</small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="info-card text-center">
                            <i class="bi bi-check-circle text-success" style="font-size: 3rem;"></i>
                            <p class="mt-3 mb-0 fw-bold">Tidak ada pinjaman aktif</p>
                        </div>
                    <?php endif; ?>

                    <!-- Contact Info -->
                    <div class="info-card text-center">
                        <p class="mb-2 text-muted">Butuh bantuan?</p>
                        <a href="https://wa.me/628xxxxxxxxxx" class="btn btn-success btn-lg w-100">
                            <i class="bi bi-whatsapp"></i> Hubungi Kami
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
