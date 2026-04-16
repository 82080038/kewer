<?php
require_once __DIR__ . '/../../config/path.php';
require_once BASE_PATH . '/includes/functions.php';
require_once BASE_PATH . '/config/session.php';
requireLogin();

// Permission check
if (!hasPermission('kas_petugas.read')) {
    header('Location: ' . baseUrl('dashboard.php'));
    exit();
}

$user = getCurrentUser();
$cabang_id = getCurrentCabang();
$role = $user['role'];

// Get setoran data via API
$apiUrl = baseUrl('api/kas_petugas_setoran.php');
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $apiUrl . '?cabang_id=' . $cabang_id);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer kewer-api-token-2024',
    'Content-Type: application/json'
]);
$response = curl_exec($ch);
curl_close($ch);

$setoranData = json_decode($response, true);
$setoranList = $setoranData['success'] && isset($setoranData['data']) ? $setoranData['data'] : [];

// Get petugas list for filter (manager and above)
$petugasList = [];
if ($role !== 'petugas' && $cabang_id) {
    $petugasList = query("SELECT id, nama FROM users WHERE cabang_id = ? AND role = 'petugas'", [$cabang_id]);
    if (!is_array($petugasList)) {
        $petugasList = [];
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kas Petugas - <?php echo APP_NAME; ?></title>
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
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="../../dashboard.php"><?php echo APP_NAME; ?></a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="../../dashboard.php">Dashboard</a>
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
                        <?php if (hasPermission('manage_petugas') || hasPermission('view_petugas')): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="../petugas/index.php">
                                <i class="bi bi-person-badge"></i> Petugas
                            </a>
                        </li>
                        <?php endif; ?>
                        <?php if (hasPermission('manage_users') || hasPermission('view_users')): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="../users/index.php">
                                <i class="bi bi-person-gear"></i> Users
                            </a>
                        </li>
                        <?php endif; ?>
                        <li class="nav-item">
                            <a class="nav-link active" href="index.php">
                                <i class="bi bi-cash-coin"></i> Kas Petugas
                            </a>
                        </li>
                        <?php if (hasPermission('manage_cabang') || hasPermission('view_cabang')): ?>
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
            <div class="col-md-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="bi bi-cash-coin"></i> Tracking Dana Petugas</h2>
                    <a href="../../dashboard.php" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Kembali
                    </a>
                </div>

                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i> 
                    Tracking dana yang dipegang petugas lapangan: saldo awal, total dikutip, total disetor, dan saldo akhir.
                </div>

                <!-- Date Filter -->
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="row align-items-end">
                            <div class="col-md-3">
                                <label>Tanggal</label>
                                <input type="date" class="form-control" id="tanggal" value="<?= $tanggal ?>" onchange="filterByDate()">
                            </div>
                            <div class="col-md-3">
                                <button class="btn btn-primary" onclick="filterByDate()">
                                    <i class="bi bi-filter"></i> Filter
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Summary Cards -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <h6>Total Setoran</h6>
                                <h3><?php echo count($setoranList); ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-info text-white">
                            <div class="card-body">
                                <h6>Total Kas Petugas</h6>
                                <h3><?php echo 'Rp' . number_format(array_sum(array_column($setoranList, 'total_kas_petugas')), 0, ',', '.'); ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <h6>Total Disetor</h6>
                                <h3><?php echo 'Rp' . number_format(array_sum(array_column($setoranList, 'total_setoran')), 0, ',', '.'); ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-warning text-dark">
                            <div class="card-body">
                                <h6>Pending Approval</h6>
                                <h3><?php echo count(array_filter($setoranList, fn($s) => $s['status'] === 'pending')); ?></h3>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Setoran Table -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Setoran Kas Petugas</h5>
                        <?php if ($role === 'petugas'): ?>
                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addModal">
                            <i class="bi bi-plus"></i> Catat Setoran
                        </button>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <!-- Filters -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-3">
                                <label class="form-label">Petugas</label>
                                <select class="form-select" id="filterPetugas">
                                    <option value="">Semua Petugas</option>
                                    <?php foreach ($petugasList as $p): ?>
                                    <option value="<?php echo $p['id']; ?>"><?php echo htmlspecialchars($p['nama']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Status</label>
                                <select class="form-select" id="filterStatus">
                                    <option value="">Semua Status</option>
                                    <option value="pending">Pending</option>
                                    <option value="approved">Approved</option>
                                    <option value="rejected">Rejected</option>
                                </select>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover" id="kasPetugasTable">
                                <thead>
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>Petugas</th>
                                        <th>Total Kas</th>
                                        <th>Total Setoran</th>
                                        <th>Selisih</th>
                                        <th>Status</th>
                                        <th>Catatan</th>
                                        <?php if ($role === 'manager' || $role === 'owner' || $role === 'superadmin'): ?>
                                        <th>Aksi</th>
                                        <?php endif; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($setoranList as $setoran): ?>
                                    <tr data-petugas="<?php echo $setoran['petugas_id']; ?>" data-status="<?php echo $setoran['status']; ?>">
                                        <td><?php echo date('d/m/Y', strtotime($setoran['tanggal'])); ?></td>
                                        <td><?php echo htmlspecialchars($setoran['petugas_nama']); ?></td>
                                        <td><?php echo 'Rp' . number_format($setoran['total_kas_petugas'], 0, ',', '.'); ?></td>
                                        <td><?php echo 'Rp' . number_format($setoran['total_setoran'], 0, ',', '.'); ?></td>
                                        <td><?php echo 'Rp' . number_format($setoran['selisih'], 0, ',', '.'); ?></td>
                                        <td>
                                            <span class="badge <?php echo $setoran['status'] === 'approved' ? 'bg-success' : ($setoran['status'] === 'pending' ? 'bg-warning' : 'bg-danger'); ?>">
                                                <?php echo ucfirst($setoran['status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($setoran['keterangan'] ?? '-'); ?></td>
                                        <?php if ($role === 'manager' || $role === 'owner' || $role === 'superadmin'): ?>
                                        <td>
                                            <?php if ($setoran['status'] === 'pending'): ?>
                                            <button class="btn btn-sm btn-success" onclick="approveSetoran(<?php echo $setoran['id']; ?>)">
                                                <i class="bi bi-check"></i> Approve
                                            </button>
                                            <button class="btn btn-sm btn-danger" onclick="rejectSetoran(<?php echo $setoran['id']; ?>)">
                                                <i class="bi bi-x"></i> Reject
                                            </button>
                                            <?php endif; ?>
                                        </td>
                                        <?php endif; ?>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Modal (for petugas) -->
    <?php if ($role === 'petugas'): ?>
    <div class="modal fade" id="addModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Catat Setoran Kas</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="setoranForm">
                        <div class="mb-3">
                            <label class="form-label">Tanggal *</label>
                            <input type="date" class="form-control" name="tanggal" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Total Kas Petugas *</label>
                            <input type="number" class="form-control" name="total_kas_petugas" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Total Setoran *</label>
                            <input type="number" class="form-control" name="total_setoran" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Catatan</label>
                            <textarea class="form-control" name="keterangan" rows="2"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" onclick="saveSetoran()">Simpan</button>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

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
            // Only initialize DataTable if there's data
            var hasData = <?php echo !empty($setoranList) ? 'true' : 'false'; ?>;

            if (hasData) {
                try {
                    var table = $('#kasPetugasTable').DataTable({
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
                    $('#kasPetugasTable').removeClass('table-striped table-hover');
                }
            } else {
                $('#kasPetugasTable').removeClass('table-striped table-hover');
            }
            
            // Filter functionality
            $('#filterPetugas, #filterStatus').on('change', function() {
                const petugas = $('#filterPetugas').val();
                const status = $('#filterStatus').val();
                
                $('#kasPetugasTable tbody tr').each(function() {
                    const row = $(this);
                    const show = true;
                    
                    if (petugas && row.data('petugas') != petugas) {
                        row.hide();
                        return;
                    }
                    
                    if (status && row.data('status') !== status) {
                        row.hide();
                        return;
                    }
                    
                    row.show();
                });
            });
        });
        
        function saveSetoran() {
            const form = document.getElementById('setoranForm');
            const formData = new FormData(form);
            const data = Object.fromEntries(formData.entries());

            fetch('<?php echo baseUrl('api/kas_petugas_setoran.php'); ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    Swal.fire('Sukses', result.message, 'success');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    Swal.fire('Error', result.error || 'Gagal menyimpan setoran', 'error');
                }
            })
            .catch(error => {
                Swal.fire('Error', 'Terjadi kesalahan', 'error');
            });
        }
        
        function approveSetoran(id) {
            Swal.fire({
                title: 'Approve Setoran?',
                text: 'Anda yakin ingin menyetujui setoran ini?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, Approve',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('<?php echo baseUrl('api/kas_petugas_setoran.php'); ?>?id=' + id, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({ status: 'approved' })
                    })
                    .then(response => response.json())
                    .then(result => {
                        if (result.success) {
                            Swal.fire('Sukses', result.message, 'success');
                            setTimeout(() => location.reload(), 1500);
                        } else {
                            Swal.fire('Error', result.error || 'Gagal approve setoran', 'error');
                        }
                    })
                    .catch(error => {
                        Swal.fire('Error', 'Terjadi kesalahan', 'error');
                    });
                }
            });
        }
        
        function rejectSetoran(id) {
            Swal.fire({
                title: 'Reject Setoran?',
                text: 'Anda yakin ingin menolak setoran ini?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, Reject',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('<?php echo baseUrl('api/kas_petugas_setoran.php'); ?>?id=' + id, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({ status: 'rejected' })
                    })
                    .then(response => response.json())
                    .then(result => {
                        if (result.success) {
                            Swal.fire('Sukses', result.message, 'success');
                            setTimeout(() => location.reload(), 1500);
                        } else {
                            Swal.fire('Error', result.error || 'Gagal reject setoran', 'error');
                        }
                    })
                    .catch(error => {
                        Swal.fire('Error', 'Terjadi kesalahan', 'error');
                    });
                }
            });
        }
    </script>
</body>
</html>
