<?php
require_once __DIR__ . '/../../config/path.php';
require_once BASE_PATH . '/includes/functions.php';
requireLogin();

// Only users with users management permission can access
if (!hasPermission('users.create') && !hasPermission('users.read')) {
    header('Location: ' . baseUrl('dashboard.php'));
    exit();
}

$currentUser = getCurrentUser();
$role = $currentUser['role'];
$user_cabang_id = $currentUser['cabang_id'] ?? null;

// Filter users based on role
if ($role === 'appOwner') {
    // appOwner can see all users except other appOwners
    $users = query("SELECT u.*, c.nama_cabang FROM users u LEFT JOIN cabang c ON u.cabang_id = c.id WHERE u.role != 'appOwner' ORDER BY u.created_at DESC");
} elseif ($role === 'bos') {
    // Bos can see users from their branches
    $owned_cabangs = getBosOwnedCabangIds();
    if (!empty($owned_cabangs)) {
        $placeholders = implode(',', array_fill(0, count($owned_cabangs), '?'));
        $users = query("SELECT u.*, c.nama_cabang FROM users u LEFT JOIN cabang c ON u.cabang_id = c.id WHERE u.cabang_id IN ($placeholders) AND u.role != 'appOwner' AND u.role != 'bos' ORDER BY u.created_at DESC", $owned_cabangs);
    } else {
        $users = [];
    }
} elseif (in_array($role, ['manager_pusat', 'admin_pusat'])) {
    // Manager/Admin pusat can see all users from all branches
    $users = query("SELECT u.*, c.nama_cabang FROM users u LEFT JOIN cabang c ON u.cabang_id = c.id WHERE u.role != 'appOwner' ORDER BY u.created_at DESC");
} else {
    // Other roles can only see users from their own branch
    if ($user_cabang_id) {
        $users = query("SELECT u.*, c.nama_cabang FROM users u LEFT JOIN cabang c ON u.cabang_id = c.id WHERE u.cabang_id = ? AND u.role != 'appOwner' ORDER BY u.created_at DESC", [$user_cabang_id]);
    } else {
        $users = [];
    }
}

if (!is_array($users)) {
    $users = [];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Users - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/themes/light.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <div class="main-container">
        <?php require_once BASE_PATH . '/includes/sidebar.php'; ?>
        
        <main class="content-area">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Manajemen Users</h1>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
                        <i class="bi bi-plus"></i> Tambah User
                    </button>
                </div>
                
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover" id="usersTable">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Username</th>
                                        <th>Nama</th>
                                        <th>Email</th>
                                        <th>Role</th>
                                        <th>Cabang</th>
                                        <th>Gaji</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($users as $u): ?>
                                    <tr>
                                        <td><?= $u['id'] ?></td>
                                        <td><?= $u['username'] ?></td>
                                        <td><?= $u['nama'] ?></td>
                                        <td><?= $u['email'] ?? '-' ?></td>
                                        <td>
                                            <?php
                                            $roleColors = [
                                                'bos' => 'danger',
                                                'manager_pusat' => 'primary',
                                                'manager_cabang' => 'success',
                                                'admin_pusat' => 'warning',
                                                'admin_cabang' => 'info',
                                                'petugas_pusat' => 'secondary',
                                                'petugas_cabang' => 'dark',
                                                'teller' => 'light text-dark'
                                            ];
                                            ?>
                                            <span class="badge bg-<?= $roleColors[$u['role']] ?? 'secondary' ?>">
                                                <?= ucfirst($u['role']) ?>
                                            </span>
                                        </td>
                                        <td><?= $u['nama_cabang'] ?? '-' ?></td>
                                        <td><?= formatRupiah($u['gaji'] ?? 0) ?></td>
                                        <td>
                                            <?php if ($u['status'] == 'aktif'): ?>
                                                <span class="badge bg-success">Aktif</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Nonaktif</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="edit.php?id=<?= $u['id'] ?>" class="btn btn-sm btn-warning">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <?php if (hasPermission('assign_permissions') && canManageRole($u['role'])): ?>
                                            <a href="permissions/index.php?user_id=<?= $u['id'] ?>" class="btn btn-sm btn-info">
                                                <i class="bi bi-shield-lock"></i>
                                            </a>
                                            <?php endif; ?>
                                            <?php if ($u['id'] != getCurrentUser()['id']): ?>
                                            <a href="hapus.php?id=<?= $u['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Hapus user ini?')">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Add Modal -->
    <div class="modal fade" id="addModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" action="tambah.php">
                        <?= csrfField() ?>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>Username *</label>
                                <input type="text" name="username" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Password *</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>Nama *</label>
                                <input type="text" name="nama" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Email</label>
                                <input type="email" name="email" class="form-control">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label>Role *</label>
                            <select name="role" class="form-select" required id="roleSelect">
                                <option value="">Pilih Role</option>
                                <optgroup label="Pusat (Tanpa Cabang)">
                                    <option value="bos">Bos</option>
                                    <option value="manager_pusat">Manager Pusat</option>
                                    <option value="admin_pusat">Admin Pusat</option>
                                    <option value="petugas_pusat">Petugas Pusat</option>
                                </optgroup>
                                <optgroup label="Cabang">
                                    <option value="manager_cabang">Manager Cabang</option>
                                    <option value="admin_cabang">Admin Cabang</option>
                                    <option value="petugas_cabang">Petugas Cabang</option>
                                    <option value="karyawan">Karyawan</option>
                                </optgroup>
                            </select>
                            <small class="text-muted" id="roleHint"></small>
                        </div>
                        <div class="mb-3" id="cabangField">
                            <label>Cabang</label>
                            <select name="cabang_id" class="form-select">
                                <option value="">Tanpa Cabang</option>
                                <?php
                                $cabang = query("SELECT * FROM cabang");
                                if (!is_array($cabang)) {
                                    $cabang = [];
                                }
                                foreach ($cabang as $c): ?>
                                    <option value="<?= $c['id'] ?>"><?= $c['nama_cabang'] ?></option>
                                <?php endforeach; ?>
                            </select>
                            <small class="text-muted">Role pusat (bos, manager_pusat, admin_pusat, petugas_pusat) tidak memerlukan cabang</small>
                        </div>
                        <div class="mb-3">
                            <label>Gaji</label>
                            <input type="number" name="gaji" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label>Limit Kas Bon</label>
                            <input type="number" name="limit_kasbon" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label>Status</label>
                            <select name="status" class="form-select">
                                <option value="aktif">Aktif</option>
                                <option value="nonaktif">Nonaktif</option>
                            </select>
                        </div>
                        <div class="text-end">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/i18n/id.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/l10n/id.js"></script>
    <script>
        $(document).ready(function() {
            // Handle role selection to show/hide cabang field
            $('#roleSelect').on('change', function() {
                const role = $(this).val();
                const pusatRoles = ['bos', 'manager_pusat', 'admin_pusat', 'petugas_pusat'];
                const cabangRoles = ['manager_cabang', 'admin_cabang', 'petugas_cabang', 'teller'];
                
                if (pusatRoles.includes(role)) {
                    $('#cabangField').hide();
                    $('select[name="cabang_id"]').val('');
                    $('#roleHint').text('Role pusat tidak memerlukan cabang');
                } else if (cabangRoles.includes(role)) {
                    $('#cabangField').show();
                    $('#roleHint').text('Role cabang harus memiliki cabang');
                } else {
                    $('#cabangField').show();
                    $('#roleHint').text('');
                }
            });
            
            // Initialize with current selection
            $('#roleSelect').trigger('change');
            
            // Only initialize DataTable if there's data
            var hasData = <?php echo !empty($users) ? 'true' : 'false'; ?>;

            if (hasData) {
                try {
                    var table = $('#usersTable').DataTable({
                        language: {
                            search: "Cari:",
                            lengthMenu: "Tampilkan _MENU_ data per halaman",
                            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                            infoEmpty: "Menampilkan 0 sampai 0 dari 0 data",
                            infoFiltered: "(difilter dari _MAX_ total data)",
                            paginate: {
                                first: "Pertama",
                                last: "Terakhir",
                                next: "Selanjutnya",
                                previous: "Sebelumnya"
                            },
                            emptyTable: "Tidak ada data tersedia",
                            zeroRecords: "Tidak ada data yang cocok"
                        },
                        pageLength: 25,
                        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
                        responsive: true,
                        order: [[0, 'desc']]
                    });
                } catch (e) {
                    console.error('DataTables initialization error:', e);
                    $('#usersTable').removeClass('table-striped table-hover');
                }
            } else {
                // Hide DataTables controls when no data
                $('#usersTable').removeClass('table-striped table-hover');
                $('#usersTable_wrapper').hide();
            }
            
            // Initialize Select2
            $('.form-select').select2({
                theme: 'bootstrap-5',
                language: 'id',
                width: '100%'
            });
            
            // Initialize Flatpickr for date inputs
            flatpickr('input[type="date"]', {
                locale: 'id',
                dateFormat: 'Y-m-d',
                allowInput: true,
                altInput: true,
                altFormat: 'd F Y',
                theme: 'light'
            });
        });
        
        function showPending() {
            window.location.href = '?status=pending';
        }

        // Convert session alerts to SweetAlert2
        <?= getSessionAlertsJS() ?>
    </script>
</body>
</html>
