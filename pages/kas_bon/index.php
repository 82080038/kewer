<?php
require_once __DIR__ . '/../../config/path.php';
require_once BASE_PATH . '/includes/functions.php';
requireLogin();

// Only users with kas_bon management permission can access
if (!hasPermission('manage_kas_bon') && !hasPermission('view_kas_bon')) {
    header('Location: ' . baseUrl('dashboard.php'));
    exit();
}

$cabang_id = getCurrentCabang();
require_once BASE_PATH . '/includes/kas_bon.php';
$kasBon = new KasBon($cabang_id);

$status = $_GET['status'] ?? '';
$kasbons = $kasBon->getAll(['status' => $status]);
if (!is_array($kasbons)) {
    $kasbons = [];
}
$pending = $kasBon->getPendingRequests();
$stats = $kasBon->getStatistics();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kas Bon - <?php echo APP_NAME; ?></title>
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
                                <i class="bi bi-cash"></i> Kas Bon
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
                    <h2><i class="bi bi-cash"></i> Kas Bon Karyawan</h2>
                    <a href="../../dashboard.php" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Kembali
                    </a>
                </div>

                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i> 
                    Kas bon adalah pinjaman dana untuk karyawan yang akan dipotong dari gaji bulanan.
                </div>

                <!-- Pending Approvals Alert -->
                <?php if (!empty($pending)): ?>
                <div class="alert alert-warning mb-4">
                    <h6><i class="bi bi-exclamation-triangle"></i> <?= count($pending) ?> Pengajuan Kas Bon Menunggu Approval</h6>
                    <button class="btn btn-sm btn-primary" onclick="showPending()">
                        <i class="bi bi-eye"></i> Lihat Pending
                    </button>
                </div>
                <?php endif; ?>

                <!-- Summary Cards -->
                <?php if ($stats): ?>
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <h6>Total Kas Bon</h6>
                                <h3><?= $stats['total_kasbon'] ?? 0 ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-info text-white">
                            <div class="card-body">
                                <h6>Total Jumlah</h6>
                                <h3><?= formatRupiah($stats['total_jumlah'] ?? 0) ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-warning text-dark">
                            <div class="card-body">
                                <h6>Total Sisa</h6>
                                <h3><?= formatRupiah($stats['total_sisa'] ?? 0) ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <h6>Selesai</h6>
                                <h3><?= $stats['selesai'] ?? 0 ?></h3>
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
                                <label>Status</label>
                                <select class="form-select" name="status">
                                    <option value="">Semua</option>
                                    <option value="pengajuan" <?= $status == 'pengajuan' ? 'selected' : '' ?>>Pengajuan</option>
                                    <option value="disetujui" <?= $status == 'disetujui' ? 'selected' : '' ?>>Disetujui</option>
                                    <option value="diberikan" <?= $status == 'diberikan' ? 'selected' : '' ?>>Diberikan</option>
                                    <option value="dipotong" <?= $status == 'dipotong' ? 'selected' : '' ?>>Dipotong</option>
                                    <option value="selesai" <?= $status == 'selesai' ? 'selected' : '' ?>>Selesai</option>
                                    <option value="ditolak" <?= $status == 'ditolak' ? 'selected' : '' ?>>Ditolak</option>
                                </select>
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

                <!-- Kas Bon Table -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Daftar Kas Bon</h5>
                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addModal">
                            <i class="bi bi-plus"></i> Ajukan Kas Bon
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover" id="kasBonTable">
                                <thead>
                                    <tr>
                                        <th>Kode</th>
                                        <th>Karyawan</th>
                                        <th>Tanggal Pengajuan</th>
                                        <th>Jumlah</th>
                                        <th>Tenor</th>
                                        <th>Potongan/Bulan</th>
                                        <th>Sisa</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($kasbons as $kb): ?>
                                    <tr>
                                        <td><?= $kb['kode_kasbon'] ?></td>
                                        <td><?= $kb['nama_karyawan'] ?></td>
                                        <td><?= formatDate($kb['tanggal_pengajuan']) ?></td>
                                        <td><?= formatRupiah($kb['jumlah']) ?></td>
                                        <td><?= $kb['tenor_bulan'] ?> bulan</td>
                                        <td><?= formatRupiah($kb['potongan_per_bulan']) ?></td>
                                        <td><?= formatRupiah($kb['sisa_bon']) ?></td>
                                        <td>
                                            <?php if ($kb['status'] == 'pengajuan'): ?>
                                                <span class="badge bg-warning">Pengajuan</span>
                                            <?php elseif ($kb['status'] == 'disetujui'): ?>
                                                <span class="badge bg-info">Disetujui</span>
                                            <?php elseif ($kb['status'] == 'diberikan'): ?>
                                                <span class="badge bg-primary">Diberikan</span>
                                            <?php elseif ($kb['status'] == 'dipotong'): ?>
                                                <span class="badge bg-secondary">Dipotong</span>
                                            <?php elseif ($kb['status'] == 'selesai'): ?>
                                                <span class="badge bg-success">Selesai</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Ditolak</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($kb['status'] == 'pengajuan' && (hasRole('superadmin') || hasRole('admin'))): ?>
                                            <button class="btn btn-sm btn-success" onclick="approveKasBon(<?= $kb['id'] ?>)">
                                                <i class="bi bi-check"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger" onclick="rejectKasBon(<?= $kb['id'] ?>)">
                                                <i class="bi bi-x"></i>
                                            </button>
                                            <?php elseif ($kb['status'] == 'disetujui' && (hasRole('superadmin') || hasRole('admin'))): ?>
                                            <button class="btn btn-sm btn-primary" onclick="giveKasBon(<?= $kb['id'] ?>)">
                                                <i class="bi bi-cash"></i> Berikan
                                            </button>
                                            <?php elseif ($kb['status'] == 'diberikan' || $kb['status'] == 'dipotong'): ?>
                                            <button class="btn btn-sm btn-warning" onclick="potongKasBon(<?= $kb['id'] ?>)">
                                                <i class="bi bi-arrow-down-circle"></i> Potong
                                            </button>
                                            <?php endif; ?>
                                            <button class="btn btn-sm btn-info" onclick="viewDetail(<?= $kb['id'] ?>)">
                                                <i class="bi bi-eye"></i>
                                            </button>
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
                    <h5 class="modal-title">Ajukan Kas Bon</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addForm">
                        <div class="mb-3">
                            <label>Karyawan</label>
                            <select class="form-select" name="karyawan_id" required>
                                <option value="">Pilih Karyawan</option>
                                <?php
                                $karyawan = query("SELECT * FROM users WHERE (role = 'karyawan' OR role = 'petugas') AND cabang_id = ? AND status = 'aktif'", [$cabang_id]);
                                if (!is_array($karyawan)) {
                                    $karyawan = [];
                                }
                                foreach ($karyawan as $k): ?>
                                    <option value="<?= $k['id'] ?>"><?= $k['nama'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label>Jumlah</label>
                            <input type="number" class="form-control" name="jumlah" required>
                        </div>
                        <div class="mb-3">
                            <label>Tenor (bulan)</label>
                            <input type="number" class="form-control" name="tenor_bulan" value="1" min="1" required>
                        </div>
                        <div class="mb-3">
                            <label>Tujuan</label>
                            <textarea class="form-control" name="tujuan" rows="2" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label>Catatan</label>
                            <textarea class="form-control" name="catatan" rows="2"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" onclick="saveKasBon()">Ajukan</button>
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
            var hasData = <?php echo !empty($kasbons) ? 'true' : 'false'; ?>;

            if (hasData) {
                try {
                    var table = $('#kasBonTable').DataTable({
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
                    $('#kasBonTable').removeClass('table-striped table-hover');
                }
            } else {
                // Hide DataTables controls when no data
                $('#kasBonTable').removeClass('table-striped table-hover');
                $('#kasBonTable_wrapper').hide();
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
        function saveKasBon() {
            const form = document.getElementById('addForm');
            const formData = new FormData(form);
            const data = Object.fromEntries(formData.entries());

            fetch('/api/kas_bon?action=create', {
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
                    alert('Kas bon berhasil diajukan');
                    location.reload();
                } else {
                    alert('Gagal mengajukan kas bon: ' + data.error);
                }
            });
        }

        function approveKasBon(id) {
            if (confirm('Setujui kas bon ini?')) {
                fetch(`/api/kas_bon?id=${id}&action=approve`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': 'Bearer kewer-api-token-2024'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Kas bon berhasil disetujui');
                        location.reload();
                    }
                });
            }
        }

        function rejectKasBon(id) {
            const alasan = prompt('Alasan penolakan:');
            if (alasan) {
                fetch(`/api/kas_bon?id=${id}&action=reject`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': 'Bearer kewer-api-token-2024'
                    },
                    body: JSON.stringify({ alasan: alasan })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Kas bon berhasil ditolak');
                        location.reload();
                    }
                });
            }
        }

        function giveKasBon(id) {
            if (confirm('Berikan kas bon ini ke karyawan?')) {
                fetch(`/api/kas_bon?id=${id}&action=give`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': 'Bearer kewer-api-token-2024'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Kas bon berhasil diberikan');
                        location.reload();
                    }
                });
            }
        }

        function potongKasBon(id) {
            const bulan = prompt('Masukkan bulan potong (YYYY-MM):', new Date().toISOString().slice(0, 7));
            const jumlah = prompt('Masukkan jumlah potong:');
            
            if (bulan && jumlah) {
                fetch('/api/kas_bon?action=potong', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': 'Bearer kewer-api-token-2024'
                    },
                    body: JSON.stringify({
                        kas_bon_id: id,
                        bulan_potong: bulan,
                        jumlah_potong: jumlah,
                        potong_oleh: 1 // Get from session
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Potongan berhasil dicatat');
                        location.reload();
                    }
                });
            }
        }

        function viewDetail(id) {
            window.location.href = `detail.php?id=${id}`;
        }

        function formatRupiah(amount) {
            return 'Rp ' + parseInt(amount).toLocaleString('id-ID');
        }

        function formatDate(date) {
            const d = new Date(date);
            return d.toLocaleDateString('id-ID');
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
