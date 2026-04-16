<?php
require_once __DIR__ . '/../../config/path.php';
require_once BASE_PATH . '/includes/functions.php';
requireLogin();

$cabang_id = getCurrentCabang();
$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? '';

// Build query
$where = ["n.cabang_id = ?"];
$params = [$cabang_id];

if ($search) {
    $where[] = "(n.nama LIKE ? OR n.kode_nasabah LIKE ? OR n.ktp LIKE ? OR n.telp LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($status) {
    $where[] = "n.status = ?";
    $params[] = $status;
}

$where_clause = "WHERE " . implode(" AND ", $where);

// Get nasabah data
$nasabah = query("
    SELECT n.*, c.nama_cabang
    FROM nasabah n
    LEFT JOIN cabang c ON n.cabang_id = c.id
    $where_clause
    ORDER BY n.created_at DESC
", $params);

// Ensure nasabah is an array
if (!is_array($nasabah)) {
    $nasabah = [];
}

// Get statistics
$stats_result = query("
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'aktif' THEN 1 ELSE 0 END) as aktif,
        SUM(CASE WHEN status = 'nonaktif' THEN 1 ELSE 0 END) as nonaktif,
        SUM(CASE WHEN status = 'blacklist' THEN 1 ELSE 0 END) as blacklist
    FROM nasabah 
    WHERE cabang_id = ?
", [$cabang_id]);

$stats = is_array($stats_result) && isset($stats_result[0]) ? $stats_result[0] : ['total' => 0, 'aktif' => 0, 'nonaktif' => 0, 'blacklist' => 0];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Nasabah - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
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
                            <a class="nav-link active" href="index.php">
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
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Data Nasabah</h1>
                    <a href="tambah.php" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Tambah Nasabah
                    </a>
                </div>
                
                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <h5 class="card-title">Total Nasabah</h5>
                                <h3><?php echo $stats['total']; ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <h5 class="card-title">Aktif</h5>
                                <h3><?php echo $stats['aktif']; ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-warning text-white">
                            <div class="card-body">
                                <h5 class="card-title">Nonaktif</h5>
                                <h3><?php echo $stats['nonaktif']; ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-danger text-white">
                            <div class="card-body">
                                <h5 class="card-title">Blacklist</h5>
                                <h3><?php echo $stats['blacklist']; ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Filter and Search -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-4">
                                <input type="text" name="search" class="form-control" placeholder="Cari nama, KTP, telepon..." value="<?php echo htmlspecialchars($search); ?>">
                            </div>
                            <div class="col-md-3">
                                <select name="status" class="form-select">
                                    <option value="">Semua Status</option>
                                    <option value="aktif" <?php echo $status === 'aktif' ? 'selected' : ''; ?>>Aktif</option>
                                    <option value="nonaktif" <?php echo $status === 'nonaktif' ? 'selected' : ''; ?>>Nonaktif</option>
                                    <option value="blacklist" <?php echo $status === 'blacklist' ? 'selected' : ''; ?>>Blacklist</option>
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
                            <table class="table table-striped table-hover" id="nasabahTable">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Kode</th>
                                        <th>Nama</th>
                                        <th>KTP</th>
                                        <th>Telepon</th>
                                        <th>Jenis Usaha</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($nasabah)): ?>
                                        <tr id="noDataRow">
                                            <td colspan="7" class="text-center text-muted">Tidak ada data nasabah</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($nasabah as $n): ?>
                                            <tr>
                                                <td><?php echo $n['kode_nasabah']; ?></td>
                                                <td>
                                                    <?php echo $n['nama']; ?>
                                                    <?php if ($n['foto_selfie']): ?>
                                                        <i class="bi bi-camera-fill text-success" title="Ada foto"></i>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo $n['ktp']; ?></td>
                                                <td>
                                                    <a href="https://wa.me/<?php echo str_replace(['+', '-'], '', $n['telp']); ?>" target="_blank">
                                                        <?php echo $n['telp']; ?>
                                                        <i class="bi bi-whatsapp text-success"></i>
                                                    </a>
                                                </td>
                                                <td><?php echo $n['jenis_usaha'] ?: '-'; ?></td>
                                                <td>
                                                    <?php
                                                    $status_class = [
                                                        'aktif' => 'success',
                                                        'nonaktif' => 'warning',
                                                        'blacklist' => 'danger'
                                                    ];
                                                    $status = $n['status'] ?? 'aktif';
                                                    ?>
                                                    <span class="badge bg-<?php echo $status_class[$status] ?? 'secondary'; ?>">
                                                        <?php echo ucfirst($status); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm" role="group">
                                                        <a href="detail.php?id=<?php echo $n['id']; ?>" class="btn btn-outline-primary" title="Detail">
                                                            <i class="bi bi-eye"></i>
                                                        </a>
                                                        <a href="edit.php?id=<?php echo $n['id']; ?>" class="btn btn-outline-warning" title="Edit">
                                                            <i class="bi bi-pencil"></i>
                                                        </a>
                                                        <button onclick="confirmDelete(<?php echo $n['id']; ?>, '<?php echo $n['nama']; ?>')" class="btn btn-outline-danger" title="Hapus">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
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
            var hasData = <?php echo !empty($nasabah) ? 'true' : 'false'; ?>;
            
            if (hasData) {
                try {
                    var table = $('#nasabahTable').DataTable({
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
                        order: [[0, 'desc']],
                        columnDefs: [
                            { targets: '_all', defaultContent: '' },
                            { targets: [0, 1, 2, 3, 4, 5, 6], className: 'align-middle' }
                        ],
                        autoWidth: false
                    });
                } catch (e) {
                    console.error('DataTables initialization error:', e);
                    // Fallback: show table without DataTables functionality
                    $('#nasabahTable').removeClass('table-striped table-hover');
                }
            } else {
                // Hide DataTables controls when no data
                $('#nasabahTable').removeClass('table-striped table-hover');
                $('#nasabahTable_wrapper').hide();
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
        
        function confirmDelete(id, nama) {
            Swal.fire({
                title: 'Hapus Nasabah',
                text: `Apakah Anda yakin ingin menghapus nasabah "${nama}"?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Hapus',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = `hapus.php?id=${id}`;
                }
            });
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
