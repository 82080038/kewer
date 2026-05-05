<?php
require_once __DIR__ . '/../../config/path.php';
require_once BASE_PATH . '/includes/functions.php';
requireLogin();

// Only users with petugas management permission can access
if (!hasPermission('manage_petugas') && !hasPermission('view_petugas')) {
    header('Location: ' . baseUrl('dashboard.php'));
    exit();
}

$kantor_id = 1; // Single office
$search = $_GET['search'] ?? '';
$role_filter = $_GET['role'] ?? '';
$cabang_filter = $_GET['cabang_id'] ?? '';

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

$where_clause = "WHERE " . implode(" AND ", $where);

// Get petugas data
$petugas = query("
    SELECT u.*, c.nama_cabang
    FROM users u
    LEFT JOIN cabang c ON u.cabang_id = c.id
    $where_clause
    ORDER BY u.created_at DESC
", $params);

// Ensure petugas is an array
if (!is_array($petugas)) {
    $petugas = [];
}

// Get statistics
$stats_result = query("
    SELECT
        COUNT(*) as total,
        SUM(CASE WHEN role = 'bos' THEN 1 ELSE 0 END) as bos,
        SUM(CASE WHEN role IN ('manager_pusat','manager_cabang') THEN 1 ELSE 0 END) as manager,
        SUM(CASE WHEN role IN ('admin_pusat','admin_cabang') THEN 1 ELSE 0 END) as admin,
        SUM(CASE WHEN role IN ('petugas_pusat','petugas_cabang') THEN 1 ELSE 0 END) as petugas,
        SUM(CASE WHEN status = 'aktif' THEN 1 ELSE 0 END) as aktif,
        SUM(CASE WHEN status = 'nonaktif' THEN 1 ELSE 0 END) as nonaktif
    FROM users
", []);
$stats = is_array($stats_result) && isset($stats_result[0]) ? $stats_result[0] : ['total' => 0, 'bos' => 0, 'manager' => 0, 'admin' => 0, 'petugas' => 0, 'aktif' => 0, 'nonaktif' => 0];

// Get cabang list
$cabang_list = query("SELECT * FROM cabang WHERE status = 'aktif' ORDER BY nama_cabang");
if (!is_array($cabang_list)) {
    $cabang_list = [];
}

// Ensure petugas is an array
if (!is_array($petugas)) {
    $petugas = [];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Petugas - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
    <div class="main-container">
        <?php require_once BASE_PATH . '/includes/sidebar.php'; ?>

        <main class="content-area">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Data Petugas</h1>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
                        <i class="bi bi-plus-circle"></i> Tambah Petugas
                    </button>
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
                    <div class="col">
                        <div class="card bg-danger text-white">
                            <div class="card-body py-2">
                                <h6 class="card-title mb-0">Bos</h6>
                                <h4 class="mb-0"><?php echo $stats['bos']; ?></h4>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card bg-success text-white">
                            <div class="card-body py-2">
                                <h6 class="card-title mb-0">Manager</h6>
                                <h4 class="mb-0"><?php echo $stats['manager']; ?></h4>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card bg-warning text-white">
                            <div class="card-body py-2">
                                <h6 class="card-title mb-0">Admin</h6>
                                <h4 class="mb-0"><?php echo $stats['admin']; ?></h4>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card bg-info text-white">
                            <div class="card-body py-2">
                                <h6 class="card-title mb-0">Petugas</h6>
                                <h4 class="mb-0"><?php echo $stats['petugas']; ?></h4>
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
                                    <option value="bos" <?php echo $role_filter === 'bos' ? 'selected' : ''; ?>>Bos</option>
                                    <option value="manager_pusat" <?php echo $role_filter === 'manager_pusat' ? 'selected' : ''; ?>>Manager Pusat</option>
                                    <option value="manager_cabang" <?php echo $role_filter === 'manager_cabang' ? 'selected' : ''; ?>>Manager Cabang</option>
                                    <option value="admin_pusat" <?php echo $role_filter === 'admin_pusat' ? 'selected' : ''; ?>>Admin Pusat</option>
                                    <option value="admin_cabang" <?php echo $role_filter === 'admin_cabang' ? 'selected' : ''; ?>>Admin Cabang</option>
                                    <option value="petugas_pusat" <?php echo $role_filter === 'petugas_pusat' ? 'selected' : ''; ?>>Petugas Pusat</option>
                                    <option value="petugas_cabang" <?php echo $role_filter === 'petugas_cabang' ? 'selected' : ''; ?>>Petugas Cabang</option>
                                    <option value="karyawan" <?php echo $role_filter === 'karyawan' ? 'selected' : ''; ?>>Karyawan</option>
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
                                                        'bos' => 'danger',
                                                        'manager_pusat' => 'primary',
                                                        'manager_cabang' => 'success',
                                                        'admin_pusat' => 'warning',
                                                        'admin_cabang' => 'info',
                                                        'petugas_pusat' => 'secondary',
                                                        'petugas_cabang' => 'dark',
                                                        'karyawan' => 'light'
                                                    ];
                                                    ?>
                                                    <span class="badge bg-<?php echo $role_class[$p['role']] ?? 'secondary'; ?>">
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
                                                            <?php if ($p['role'] !== 'bos' || getCurrentUser()['role'] === 'bos'): ?>
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
    
    <!-- Add Petugas Modal -->
    <div class="modal fade" id="addModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Petugas</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addForm">
                        <?= csrfField() ?>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Username *</label>
                                <input type="text" name="username" class="form-control" required>
                                <small class="form-text">Unik, tidak boleh sama dengan petugas lain</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Password *</label>
                                <input type="password" name="password" class="form-control" required>
                                <small class="form-text">Minimal 6 karakter</small>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nama Lengkap *</label>
                                <input type="text" name="nama" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control">
                                <small class="form-text">Opsional</small>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Role *</label>
                                <select name="role" class="form-select" id="roleSelect" required>
                                    <option value="">Pilih Role</option>
                                    <option value="bos">Bos</option>
                                    <option value="manager_pusat">Manager Pusat</option>
                                    <option value="manager_cabang">Manager Cabang</option>
                                    <option value="admin_pusat">Admin Pusat</option>
                                    <option value="admin_cabang">Admin Cabang</option>
                                    <option value="petugas_pusat">Petugas Pusat</option>
                                    <option value="petugas_cabang">Petugas Cabang</option>
                                    <option value="karyawan">Karyawan</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3" id="cabangField">
                                <label class="form-label">Cabang *</label>
                                <select name="cabang_id" class="form-select" id="cabangSelect">
                                    <option value="">Pilih Cabang</option>
                                    <?php foreach ($cabang_list as $c): ?>
                                        <option value="<?php echo $c['id']; ?>">
                                            <?php echo $c['nama_cabang']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <small class="form-text">Tidak wajib untuk Bos</small>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Gaji</label>
                                <input type="number" name="gaji" class="form-control" value="0">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Limit Kasbon</label>
                                <input type="number" name="limit_kasbon" class="form-control" value="0">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tanggal Lahir</label>
                                <input type="date" name="tanggal_lahir" class="form-control flatpickr-date">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tanggal Masuk</label>
                                <input type="date" name="tanggal_masuk" class="form-control flatpickr-date">
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" onclick="savePetugas()">Simpan</button>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Initialize modal when shown
        const addModal = document.getElementById('addModal');
        if (addModal) {
            addModal.addEventListener('shown.bs.modal', function() {
                // Toggle cabang field based on role
                const roleSelect = document.getElementById('roleSelect');
                if (roleSelect) {
                    roleSelect.addEventListener('change', function() {
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
                    roleSelect.dispatchEvent(new Event('change'));
                }
            });
        }
        
        function savePetugas() {
            const form = document.getElementById('addForm');
            const formData = new FormData(form);
            const data = Object.fromEntries(formData.entries());
            
            // Add CSRF token
            const csrfInput = document.querySelector('input[name="csrf_token"]');
            if (csrfInput) {
                data.csrf_token = csrfInput.value;
            }
            
            fetch('tambah.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams(data)
            })
            .then(response => response.text())
            .then(html => {
                // Check if there's an error message in the response
                if (html.includes('alert alert-danger')) {
                    // Extract error message
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const errorDiv = doc.querySelector('.alert-danger');
                    Swal.fire('Error', errorDiv ? errorDiv.textContent : 'Gagal menambahkan petugas', 'error');
                } else if (html.includes('alert alert-success')) {
                    Swal.fire('Sukses', 'Petugas berhasil ditambahkan', 'success').then(() => {
                        location.reload();
                    });
                } else {
                    // Check if it was successful by redirecting
                    if (html.includes('Petugas berhasil ditambahkan')) {
                        Swal.fire('Sukses', 'Petugas berhasil ditambahkan', 'success').then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire('Error', 'Gagal menambahkan petugas', 'error');
                    }
                }
            })
            .catch(error => {
                Swal.fire('Error', 'Terjadi kesalahan: ' + error.message, 'error');
            });
        }
        
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
    <script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/l10n/id.js"></script>
    <script>
        $(document).ready(function() {
            flatpickr('.flatpickr-date', {
                locale: 'id',
                dateFormat: 'Y-m-d',
                allowInput: true,
                altInput: true,
                altFormat: 'd F Y',
                theme: 'light'
            });
        });
    </script>
</body>
</html>
