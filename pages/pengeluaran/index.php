<?php
require_once __DIR__ . '/../../config/path.php';
require_once BASE_PATH . '/includes/functions.php';
require_once BASE_PATH . '/includes/pengeluaran.php';
requireLogin();

$cabang_id = getCurrentCabang();
$pengeluaran = new Pengeluaran($cabang_id);

$filters = [
    'kategori' => $_GET['kategori'] ?? null,
    'status' => $_GET['status'] ?? null,
    'tanggal_mulai' => $_GET['tanggal_mulai'] ?? date('Y-m-01'),
    'tanggal_selesai' => $_GET['tanggal_selesai'] ?? date('Y-m-t')
];

$expenses = $pengeluaran->getBranchExpenses($filters);
if (!is_array($expenses)) {
    $expenses = [];
}
$pending = $pengeluaran->getPendingExpenses();
$total = $pengeluaran->getTotalExpenses($filters['tanggal_mulai'], $filters['tanggal_selesai']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengeluaran - <?php echo APP_NAME; ?></title>
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
                                <i class="bi bi-receipt"></i> Pengeluaran
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
                    <h2><i class="bi bi-receipt"></i> Pengeluaran Operasional</h2>
                    <a href="../../dashboard.php" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Kembali
                    </a>
                </div>

                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i> 
                    Tracking biaya operasional: gaji, lembur, bonus, operasional, belanja rutin.
                </div>

                <!-- Pending Approvals Alert -->
                <?php if (!empty($pending)): ?>
                <div class="alert alert-warning mb-4">
                    <h6><i class="bi bi-exclamation-triangle"></i> <?= count($pending) ?> Pengeluaran Menunggu Approval</h6>
                    <button class="btn btn-sm btn-primary" onclick="showPending()">
                        <i class="bi bi-eye"></i> Lihat Pending
                    </button>
                </div>
                <?php endif; ?>

                <!-- Summary Cards -->
                <?php if ($total): ?>
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <h6>Total Pengeluaran</h6>
                                <h3><?= formatRupiah($total['total_pengeluaran']) ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-info text-white">
                            <div class="card-body">
                                <h6>Jumlah Transaksi</h6>
                                <h3><?= $total['jumlah_transaksi'] ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Filter -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row">
                            <div class="col-md-3">
                                <label>Kategori</label>
                                <select class="form-select" name="kategori">
                                    <option value="">Semua</option>
                                    <option value="gaji" <?= $filters['kategori'] == 'gaji' ? 'selected' : '' ?>>Gaji</option>
                                    <option value="lembur" <?= $filters['kategori'] == 'lembur' ? 'selected' : '' ?>>Lembur</option>
                                    <option value="bonus" <?= $filters['kategori'] == 'bonus' ? 'selected' : '' ?>>Bonus</option>
                                    <option value="operasional" <?= $filters['kategori'] == 'operasional' ? 'selected' : '' ?>>Operasional</option>
                                    <option value="belanja" <?= $filters['kategori'] == 'belanja' ? 'selected' : '' ?>>Belanja</option>
                                    <option value="lainnya" <?= $filters['kategori'] == 'lainnya' ? 'selected' : '' ?>>Lainnya</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label>Status</label>
                                <select class="form-select" name="status">
                                    <option value="">Semua</option>
                                    <option value="pending" <?= $filters['status'] == 'pending' ? 'selected' : '' ?>>Pending</option>
                                    <option value="approved" <?= $filters['status'] == 'approved' ? 'selected' : '' ?>>Approved</option>
                                    <option value="rejected" <?= $filters['status'] == 'rejected' ? 'selected' : '' ?>>Rejected</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label>Tanggal Mulai</label>
                                <input type="date" class="form-control" name="tanggal_mulai" value="<?= $filters['tanggal_mulai'] ?>">
                            </div>
                            <div class="col-md-2">
                                <label>Tanggal Selesai</label>
                                <input type="date" class="form-control" name="tanggal_selesai" value="<?= $filters['tanggal_selesai'] ?>">
                            </div>
                            <div class="col-md-2">
                                <label>&nbsp;</label>
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="bi bi-filter"></i> Filter
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Expenses Table -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Daftar Pengeluaran</h5>
                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addModal">
                            <i class="bi bi-plus"></i> Tambah Pengeluaran
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover" id="pengeluaranTable">
                                <thead>
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>Kategori</th>
                                        <th>Sub Kategori</th>
                                        <th>Jumlah</th>
                                        <th>Keterangan</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($expenses as $expense): ?>
                                    <tr>
                                        <td><?= formatDate($expense['tanggal']) ?></td>
                                        <td><?= ucfirst($expense['kategori']) ?></td>
                                        <td><?= $expense['sub_kategori'] ?? '-' ?></td>
                                        <td><?= formatRupiah($expense['jumlah']) ?></td>
                                        <td><?= $expense['keterangan'] ?? '-' ?></td>
                                        <td>
                                            <?php if ($expense['status'] == 'pending'): ?>
                                                <span class="badge bg-warning">Pending</span>
                                            <?php elseif ($expense['status'] == 'approved'): ?>
                                                <span class="badge bg-success">Approved</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Rejected</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($expense['status'] == 'pending' && (hasPermission('manage_pengeluaran') || hasPermission('view_pengeluaran'))): ?>
                                            <button class="btn btn-sm btn-success" onclick="approveExpense(<?= $expense['id'] ?>)">
                                                <i class="bi bi-check"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger" onclick="rejectExpense(<?= $expense['id'] ?>)">
                                                <i class="bi bi-x"></i>
                                            </button>
                                            <?php endif; ?>
                                        </td>
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

    <!-- Add Modal -->
    <div class="modal fade" id="addModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Pengeluaran</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addForm">
                        <div class="mb-3">
                            <label>Kategori</label>
                            <select class="form-select" name="kategori" required>
                                <option value="gaji">Gaji</option>
                                <option value="lembur">Lembur</option>
                                <option value="bonus">Bonus</option>
                                <option value="operasional">Operasional</option>
                                <option value="belanja">Belanja</option>
                                <option value="lainnya">Lainnya</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label>Sub Kategori</label>
                            <input type="text" class="form-control" name="sub_kategori">
                        </div>
                        <div class="mb-3">
                            <label>Jumlah</label>
                            <input type="number" class="form-control" name="jumlah" required>
                        </div>
                        <div class="mb-3">
                            <label>Tanggal</label>
                            <input type="date" class="form-control" name="tanggal" value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="mb-3">
                            <label>Keterangan</label>
                            <textarea class="form-control" name="keterangan" rows="2"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" onclick="saveExpense()">Simpan</button>
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
            // Only initialize DataTable if there's data
            var hasData = <?php echo !empty($expenses) ? 'true' : 'false'; ?>;

            if (hasData) {
                try {
                    var table = $('#pengeluaranTable').DataTable({
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
                    $('#pengeluaranTable').removeClass('table-striped table-hover');
                }
            } else {
                // Hide DataTables controls when no data
                $('#pengeluaranTable').removeClass('table-striped table-hover');
                $('#pengeluaranTable_wrapper').hide();
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
        function saveExpense() {
            const form = document.getElementById('addForm');
            const formData = new FormData(form);
            const data = Object.fromEntries(formData.entries());

            fetch('/api/pengeluaran', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': 'Bearer kewer-api-token-2024'
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Pengeluaran berhasil ditambahkan');
                    location.reload();
                } else {
                    alert('Gagal menambahkan pengeluaran: ' + data.error);
                }
            });
        }

        function approveExpense(id) {
            if (confirm('Approve pengeluaran ini?')) {
                fetch(`/api/pengeluaran?id=${id}&action=approve`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': 'Bearer kewer-api-token-2024'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Pengeluaran berhasil di-approve');
                        location.reload();
                    }
                });
            }
        }

        function rejectExpense(id) {
            if (confirm('Reject pengeluaran ini?')) {
                fetch(`/api/pengeluaran?id=${id}&action=reject`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': 'Bearer kewer-api-token-2024'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Pengeluaran berhasil di-reject');
                        location.reload();
                    }
                });
            }
        }

        function formatRupiah(amount) {
            return 'Rp ' + parseInt(amount).toLocaleString('id-ID');
        }
        
        // Convert session alerts to SweetAlert2
        <?php
        if (isset($_SESSION['success'])) {
            echo "Swal.fire({icon: 'success', title: 'Berhasil', text: '" . $_SESSION['success'] . "', timer: 3000, showConfirmButton: false});";
            unset($_SESSION['success']);
        }
        if (isset($_SESSION['error'])) {
            echo "Swal.fire({icon: 'error', title: 'Gagal', text: '" . $_SESSION['error'] . "', timer: 3000, showConfirmButton: false});";
            unset($_SESSION['error']);
        }
        ?>
    </script>
</body>
</html>
