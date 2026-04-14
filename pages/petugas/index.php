<?php
require_once '../../includes/functions.php';
requireLogin();
requireRole('superadmin');

$cabang_id = getCurrentCabang();
$search = $_GET['search'] ?? '';
$role_filter = $_GET['role'] ?? '';
$cabang_filter = $_GET['cabang'] ?? '';

// Build query
$where = ["1=1"];
$params = [];

if ($search) {
    $where[] = "(u.username LIKE ? OR u.nama LIKE ? OR u.email LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($role_filter) {
    $where[] = "u.role = ?";
    $params[] = $role_filter;
}

if ($cabang_filter) {
    $where[] = "u.cabang_id = ?";
    $params[] = $cabang_filter;
}

$where_clause = "WHERE " . implode(" AND ", $where);

// Get petugas data
$petugas = query("
    SELECT u.*, c.nama_cabang 
    FROM users u 
    LEFT JOIN cabang c ON u.cabang_id = c.id 
    $where_clause 
    ORDER BY u.created_at DESC
", $params);

// Get statistics
$stats = query("
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN role = 'superadmin' THEN 1 ELSE 0 END) as superadmin,
        SUM(CASE WHEN role = 'admin' THEN 1 ELSE 0 END) as admin,
        SUM(CASE WHEN role = 'petugas' THEN 1 ELSE 0 END) as petugas,
        SUM(CASE WHEN status = 'aktif' THEN 1 ELSE 0 END) as aktif,
        SUM(CASE WHEN status = 'nonaktif' THEN 1 ELSE 0 END) as nonaktif
    FROM users
", [])[0];

// Get cabang list
$cabang_list = query("SELECT * FROM cabang WHERE status = 'aktif' ORDER BY nama_cabang");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Petugas - Kewer</title>
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
                    <h1 class="h2">Data Petugas</h1>
                    <a href="tambah.php" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Tambah Petugas
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
                        <div class="card bg-danger text-white">
                            <div class="card-body">
                                <h6 class="card-title">Superadmin</h6>
                                <h4><?php echo $stats['superadmin']; ?></h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card bg-warning text-white">
                            <div class="card-body">
                                <h6 class="card-title">Admin</h6>
                                <h4><?php echo $stats['admin']; ?></h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card bg-info text-white">
                            <div class="card-body">
                                <h6 class="card-title">Petugas</h6>
                                <h4><?php echo $stats['petugas']; ?></h4>
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
                                <h6 class="card-title">Nonaktif</h6>
                                <h4><?php echo $stats['nonaktif']; ?></h4>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Filter and Search -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-3">
                                <input type="text" name="search" class="form-control" placeholder="Cari nama, username, email..." value="<?php echo htmlspecialchars($search); ?>">
                            </div>
                            <div class="col-md-2">
                                <select name="role" class="form-select">
                                    <option value="">Semua Role</option>
                                    <option value="superadmin" <?php echo $role_filter === 'superadmin' ? 'selected' : ''; ?>>Superadmin</option>
                                    <option value="admin" <?php echo $role_filter === 'admin' ? 'selected' : ''; ?>>Admin</option>
                                    <option value="petugas" <?php echo $role_filter === 'petugas' ? 'selected' : ''; ?>>Petugas</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select name="cabang" class="form-select">
                                    <option value="">Semua Cabang</option>
                                    <?php foreach ($cabang_list as $c): ?>
                                        <option value="<?php echo $c['id']; ?>" <?php echo $cabang_filter == $c['id'] ? 'selected' : ''; ?>>
                                            <?php echo $c['nama_cabang']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-outline-primary">
                                    <i class="bi bi-search"></i> Cari
                                </button>
                            </div>
                            <div class="col-md-2">
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
                                        <th>Username</th>
                                        <th>Nama</th>
                                        <th>Email</th>
                                        <th>Role</th>
                                        <th>Cabang</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($petugas)): ?>
                                        <tr>
                                            <td colspan="7" class="text-center text-muted">Tidak ada data petugas</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($petugas as $p): ?>
                                            <tr>
                                                <td><?php echo $p['username']; ?></td>
                                                <td><?php echo $p['nama']; ?></td>
                                                <td><?php echo $p['email'] ?: '-'; ?></td>
                                                <td>
                                                    <?php
                                                    $role_class = [
                                                        'superadmin' => 'danger',
                                                        'admin' => 'warning',
                                                        'petugas' => 'info'
                                                    ];
                                                    ?>
                                                    <span class="badge bg-<?php echo $role_class[$p['role']]; ?>">
                                                        <?php echo ucfirst($p['role']); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo $p['nama_cabang'] ?: '-'; ?></td>
                                                <td>
                                                    <?php
                                                    $status_class = [
                                                        'aktif' => 'success',
                                                        'nonaktif' => 'secondary'
                                                    ];
                                                    ?>
                                                    <span class="badge bg-<?php echo $status_class[$p['status']]; ?>">
                                                        <?php echo ucfirst($p['status']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm" role="group">
                                                        <a href="edit.php?id=<?php echo $p['id']; ?>" class="btn btn-outline-warning" title="Edit">
                                                            <i class="bi bi-pencil"></i>
                                                        </a>
                                                        <?php if ($p['id'] != getCurrentUser()['id']): ?>
                                                            <button onclick="toggleStatus(<?php echo $p['id']; ?>, '<?php echo $p['status']; ?>')" class="btn btn-outline-<?php echo $p['status'] === 'aktif' ? 'secondary' : 'success'; ?>" title="<?php echo $p['status'] === 'aktif' ? 'Nonaktifkan' : 'Aktifkan'; ?>">
                                                                <i class="bi bi-<?php echo $p['status'] === 'aktif' ? 'pause' : 'play'; ?>"></i>
                                                            </button>
                                                            <?php if ($p['role'] !== 'superadmin' || getCurrentUser()['role'] === 'superadmin'): ?>
                                                                <button onclick="confirmDelete(<?php echo $p['id']; ?>, '<?php echo $p['nama']; ?>')" class="btn btn-outline-danger" title="Hapus">
                                                                    <i class="bi bi-trash"></i>
                                                                </button>
                                                            <?php endif; ?>
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
        function confirmDelete(id, nama) {
            if (confirm(`Apakah Anda yakin ingin menghapus petugas "${nama}"?`)) {
                window.location.href = `hapus.php?id=${id}`;
            }
        }
        
        function toggleStatus(id, currentStatus) {
            const newStatus = currentStatus === 'aktif' ? 'nonaktif' : 'aktif';
            const action = currentStatus === 'aktif' ? 'menonaktifkan' : 'mengaktifkan';
            
            if (confirm(`Apakah Anda yakin ingin ${action} petugas ini?`)) {
                window.location.href = `toggle_status.php?id=${id}&status=${newStatus}`;
            }
        }
    </script>
</body>
</html>
