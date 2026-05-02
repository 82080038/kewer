<?php
require_once __DIR__ . '/../../config/path.php';
require_once BASE_PATH . '/includes/functions.php';
require_once BASE_PATH . '/includes/alamat_helper.php';
requireLogin();

$kantor_id = 1; // Single office
$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? '';

// Build query
$where = ["1=1"];
$params = [];

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
");

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
    
    <div class="main-container">
        <?php require_once BASE_PATH . '/includes/sidebar.php'; ?>
        
        <main class="content-area">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Data Nasabah</h1>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
                        <i class="bi bi-plus-circle"></i> Tambah Nasabah
                    </button>
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
    
    <!-- Add Nasabah Modal -->
    <div class="modal fade" id="addModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Nasabah</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addForm" enctype="multipart/form-data">
                        <?= csrfField() ?>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nama Lengkap *</label>
                                <input type="text" name="nama" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">No. KTP *</label>
                                <input type="text" name="ktp" class="form-control" maxlength="16" required>
                                <small class="form-text">16 digit angka</small>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">No. Telepon *</label>
                                <input type="tel" name="telp" class="form-control" required>
                                <small class="form-text">08xxxxxxxxxx</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Jenis Usaha</label>
                                <select name="jenis_usaha" class="form-select">
                                    <option value="">Pilih Jenis Usaha</option>
                                    <option value="Pedagang Sayur">Pedagang Sayur</option>
                                    <option value="Pedagang Buah">Pedagang Buah</option>
                                    <option value="Warung Makan">Warung Makan</option>
                                    <option value="Warung Kelontong">Warung Kelontong</option>
                                    <option value="Toko Baju">Toko Baju</option>
                                    <option value="Lainnya">Lainnya</option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Provinsi</label>
                                <?php echo provinceDropdown('province_id', '', 'onchange="loadRegencies(this.value)" class="form-select"'); ?>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Kabupaten/Kota</label>
                                <select name="regency_id" id="regency_id" class="form-select" onchange="loadDistricts(this.value)">
                                    <option value="">Pilih Kabupaten/Kota</option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Kecamatan</label>
                                <select name="district_id" id="district_id" class="form-select" onchange="loadVillages(this.value)">
                                    <option value="">Pilih Kecamatan</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Desa/Kelurahan</label>
                                <select name="village_id" id="village_id" class="form-select">
                                    <option value="">Pilih Desa/Kelurahan</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Keterangan Tambahan (Opsional)</label>
                            <textarea name="alamat" class="form-control" rows="2" placeholder="Nama jalan, nomor rumah, RT/RW (opsional)"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Lokasi Pasar/Warung</label>
                            <input type="text" name="lokasi_pasar" class="form-control">
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Foto KTP</label>
                                <input type="file" name="foto_ktp" class="form-control" accept="image/*">
                                <small class="form-text">Upload foto KTP</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Foto Selfie + KTP</label>
                                <input type="file" name="foto_selfie" class="form-control" accept="image/*">
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" onclick="saveNasabah()">Simpan</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../includes/js/auto-focus.js"></script>
    <script src="../includes/js/enter-navigation.js"></script>
    <script src="../includes/js/alamat-loader.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/i18n/id.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/l10n/id.js"></script>
    <script>
        // Initialize modal when shown
        const addModal = document.getElementById('addModal');
        if (addModal) {
            addModal.addEventListener('shown.bs.modal', function() {
                // Initialize any modal-specific functionality here if needed
            });
        }
        
        function saveNasabah() {
            const form = document.getElementById('addForm');
            const formData = new FormData(form);
            
            fetch('tambah.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(html => {
                if (html.includes('alert alert-danger')) {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const errorDiv = doc.querySelector('.alert-danger');
                    Swal.fire('Error', errorDiv ? errorDiv.textContent : 'Gagal menambahkan nasabah', 'error');
                } else if (html.includes('alert alert-success')) {
                    Swal.fire('Sukses', 'Nasabah berhasil ditambahkan', 'success').then(() => {
                        location.reload();
                    });
                } else {
                    if (html.includes('Nasabah berhasil ditambahkan')) {
                        Swal.fire('Sukses', 'Nasabah berhasil ditambahkan', 'success').then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire('Error', 'Gagal menambahkan nasabah', 'error');
                    }
                }
            })
            .catch(error => {
                Swal.fire('Error', 'Terjadi kesalahan: ' + error.message, 'error');
            });
        }
        
        function showPending() {
            window.location.href = '?status=pending';
        }

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
        
        function showPending() {
            window.location.href = '?status=pending';
        }

        // Convert session alerts to SweetAlert2
        <?= getSessionAlertsJS() ?>
    </script>
</body>
</html>
