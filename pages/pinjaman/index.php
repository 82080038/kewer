<?php
require_once '../../includes/functions.php';
requireLogin();

$cabang_id = getCurrentCabang();
$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? '';

// Build query
$where = ["p.cabang_id = ?"];
$params = [$cabang_id];

if ($search) {
    $where[] = "(p.kode_pinjaman LIKE ? OR n.nama LIKE ? OR n.ktp LIKE ? OR n.telp LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($status) {
    $where[] = "p.status = ?";
    $params[] = $status;
}

$where_clause = "WHERE " . implode(" AND ", $where);

// Get pinjaman data
$pinjaman = query("
    SELECT p.*, n.nama, n.telp, n.kode_nasabah 
    FROM pinjaman p 
    JOIN nasabah n ON p.nasabah_id = n.id 
    $where_clause 
    ORDER BY p.created_at DESC
", $params);

// Get statistics
$stats = query("
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'pengajuan' THEN 1 ELSE 0 END) as pengajuan,
        SUM(CASE WHEN status = 'disetujui' THEN 1 ELSE 0 END) as disetujui,
        SUM(CASE WHEN status = 'aktif' THEN 1 ELSE 0 END) as aktif,
        SUM(CASE WHEN status = 'lunas' THEN 1 ELSE 0 END) as lunas,
        SUM(plafon) as total_plafon
    FROM pinjaman 
    WHERE cabang_id = ?
", [$cabang_id])[0];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Pinjaman - Kewer</title>
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
                            <a class="nav-link active" href="index.php">
                                <i class="bi bi-cash-stack"></i> Pinjaman
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="../angsuran/index.php">
                                <i class="bi bi-calendar-check"></i> Angsuran
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="../petugas/index.php">
                                <i class="bi bi-person-badge"></i> Petugas
                            </a>
                        </li>
                        <?php if (getCurrentUser()['role'] === 'superadmin'): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="../cabang/index.php">
                                <i class="bi bi-building"></i> Cabang
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </nav>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Data Pinjaman</h1>
                    <a href="tambah.php" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Ajukan Pinjaman
                    </a>
                </div>
                
                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-md-2">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <h6 class="card-title">Total</h6>
                                <h4><?php echo $stats['total']; ?></h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card bg-info text-white">
                            <div class="card-body">
                                <h6 class="card-title">Pengajuan</h6>
                                <h4><?php echo $stats['pengajuan']; ?></h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card bg-warning text-white">
                            <div class="card-body">
                                <h6 class="card-title">Disetujui</h6>
                                <h4><?php echo $stats['disetujui']; ?></h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <h6 class="card-title">Aktif</h6>
                                <h4><?php echo $stats['aktif']; ?></h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card bg-secondary text-white">
                            <div class="card-body">
                                <h6 class="card-title">Lunas</h6>
                                <h4><?php echo $stats['lunas']; ?></h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card bg-dark text-white">
                            <div class="card-body">
                                <h6 class="card-title">Total Plafon</h6>
                                <h5><?php echo formatRupiah($stats['total_plafon']); ?></h5>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Filter and Search -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-4">
                                <input type="text" name="search" class="form-control" placeholder="Cari kode, nama, KTP, telepon..." value="<?php echo htmlspecialchars($search); ?>">
                            </div>
                            <div class="col-md-3">
                                <select name="status" class="form-select">
                                    <option value="">Semua Status</option>
                                    <option value="pengajuan" <?php echo $status === 'pengajuan' ? 'selected' : ''; ?>>Pengajuan</option>
                                    <option value="disetujui" <?php echo $status === 'disetujui' ? 'selected' : ''; ?>>Disetujui</option>
                                    <option value="aktif" <?php echo $status === 'aktif' ? 'selected' : ''; ?>>Aktif</option>
                                    <option value="lunas" <?php echo $status === 'lunas' ? 'selected' : ''; ?>>Lunas</option>
                                    <option value="ditolak" <?php echo $status === 'ditolak' ? 'selected' : ''; ?>>Ditolak</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-outline-primary">
                                    <i class="bi bi-search"></i> Cari
                                </button>
                            </div>
                            <div class="col-md-3">
                                <a href="index.php" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-clockwise"></i> Reset
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Data Table -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Kode</th>
                                        <th>Nasabah</th>
                                        <th>Plafon</th>
                                        <th>Tenor</th>
                                        <th>Bunga/Bln</th>
                                        <th>Angsuran</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($pinjaman)): ?>
                                        <tr>
                                            <td colspan="8" class="text-center text-muted">Tidak ada data pinjaman</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($pinjaman as $p): ?>
                                            <tr>
                                                <td><?php echo $p['kode_pinjaman']; ?></td>
                                                <td>
                                                    <div>
                                                        <strong><?php echo $p['nama']; ?></strong>
                                                        <br>
                                                        <small class="text-muted"><?php echo $p['kode_nasabah']; ?></small>
                                                    </div>
                                                </td>
                                                <td><?php echo formatRupiah($p['plafon']); ?></td>
                                                <td><?php echo $p['tenor']; ?> bln</td>
                                                <td><?php echo $p['bunga_per_bulan']; ?>%</td>
                                                <td><?php echo formatRupiah($p['angsuran_total']); ?></td>
                                                <td>
                                                    <?php
                                                    $status_class = [
                                                        'pengajuan' => 'info',
                                                        'disetujui' => 'warning',
                                                        'aktif' => 'success',
                                                        'lunas' => 'secondary',
                                                        'ditolak' => 'danger'
                                                    ];
                                                    ?>
                                                    <span class="badge bg-<?php echo $status_class[$p['status']]; ?>">
                                                        <?php echo ucfirst($p['status']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm" role="group">
                                                        <a href="detail.php?id=<?php echo $p['id']; ?>" class="btn btn-outline-primary" title="Detail">
                                                            <i class="bi bi-eye"></i>
                                                        </a>
                                                        <?php if ($p['status'] === 'pengajuan'): ?>
                                                            <a href="edit.php?id=<?php echo $p['id']; ?>" class="btn btn-outline-warning" title="Edit">
                                                                <i class="bi bi-pencil"></i>
                                                            </a>
                                                        <?php endif; ?>
                                                        <?php if ($p['status'] === 'pengajuan' && getCurrentUser()['role'] !== 'petugas'): ?>
                                                            <button onclick="approveLoan(<?php echo $p['id']; ?>)" class="btn btn-outline-success" title="Setujui">
                                                                <i class="bi bi-check-circle"></i>
                                                            </button>
                                                            <button onclick="rejectLoan(<?php echo $p['id']; ?>)" class="btn btn-outline-danger" title="Tolak">
                                                                <i class="bi bi-x-circle"></i>
                                                            </button>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function approveLoan(id) {
            if (confirm('Apakah Anda yakin ingin menyetujui pinjaman ini?')) {
                window.location.href = `proses.php?action=approve&id=${id}`;
            }
        }
        
        function rejectLoan(id) {
            if (confirm('Apakah Anda yakin ingin menolak pinjaman ini?')) {
                window.location.href = `proses.php?action=reject&id=${id}`;
            }
        }
    </script>
</body>
</html>
