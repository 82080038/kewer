<?php
require_once __DIR__ . '/../../config/path.php';
require_once BASE_PATH . '/includes/functions.php';
requireLogin();

$user = getCurrentUser();
$role = $user['role'];
$user_cabang_id = $user['cabang_id'] ?? null;

$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? '';
$bulan = $_GET['bulan'] ?? date('Y-m');

// Get cabang filter based on role (using shared function from includes/functions.php)
$cabang_filter = getPageCabangFilter($role, $user_cabang_id, $user['id'], 'p');
if ($cabang_filter) {
    $cabang_filter = "AND " . $cabang_filter;
}

// Build query
$where = ["1=1"];
$params = [];

if ($search) {
    $where[] = "(a.no_angsuran LIKE ? OR n.nama LIKE ? OR n.kode_nasabah LIKE ? OR p.kode_pinjaman LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($status) {
    $where[] = "a.status = ?";
    $params[] = $status;
}

if ($bulan) {
    $where[] = "DATE_FORMAT(a.jatuh_tempo, '%Y-%m') = ?";
    $params[] = $bulan;
}

// Add cabang filter
if ($cabang_filter) {
    $where[] = ltrim($cabang_filter, 'AND ');
}

$where_clause = "WHERE " . implode(" AND ", $where);

// Get angsuran data
$angsuran = query("
    SELECT a.*, n.nama, n.kode_nasabah, p.kode_pinjaman, p.tenor
    FROM angsuran a
    JOIN pinjaman p ON a.pinjaman_id = p.id
    JOIN nasabah n ON p.nasabah_id = n.id
    $where_clause
    ORDER BY a.jatuh_tempo ASC
", $params);

// Ensure angsuran is an array
if (!is_array($angsuran)) {
    $angsuran = [];
}

// Get statistics with cabang filter (need to join through pinjaman)
$stats_where = "";
if ($cabang_filter) {
    // Transform filter for angsuran table (need to join through pinjaman)
    if ($role === 'bos') {
        $owned_cabangs = getBosOwnedCabangIds();
        if (!empty($owned_cabangs)) {
            $stats_where = "WHERE a.pinjaman_id IN (SELECT id FROM pinjaman WHERE cabang_id IN (" . implode(',', array_map('intval', $owned_cabangs)) . "))";
        }
    } elseif ($user_cabang_id) {
        $stats_where = "WHERE a.pinjaman_id IN (SELECT id FROM pinjaman WHERE cabang_id = " . intval($user_cabang_id) . ")";
    }
}

$stats_result = query("
    SELECT
        COUNT(*) as total,
        SUM(CASE WHEN status = 'belum' THEN 1 ELSE 0 END) as belum,
        SUM(CASE WHEN status = 'lunas' THEN 1 ELSE 0 END) as lunas,
        SUM(CASE WHEN status = 'telat' THEN 1 ELSE 0 END) as telat,
        SUM(total_angsuran) as total_tagihan,
        SUM(total_bayar) as total_dibayar,
        SUM(denda) as total_denda
    FROM angsuran a
    $stats_where
");
$stats = is_array($stats_result) && isset($stats_result[0]) ? $stats_result[0] : ['total' => 0, 'belum' => 0, 'lunas' => 0, 'telat' => 0, 'total_tagihan' => 0, 'total_dibayar' => 0, 'total_denda' => 0];

// Get late payments with cabang filter
$late_payments = checkLatePayments();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Angsuran - <?php echo APP_NAME; ?></title>
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
    <div class="main-container">
        <?php require_once BASE_PATH . '/includes/sidebar.php'; ?>

        <main class="content-area">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Data Angsuran</h1>
                    <a href="bayar.php" class="btn btn-success">
                        <i class="bi bi-cash"></i> Bayar Angsuran
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
                        <div class="card bg-warning text-white">
                            <div class="card-body">
                                <h6 class="card-title">Belum Bayar</h6>
                                <h4><?php echo $stats['belum']; ?></h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <h6 class="card-title">Lunas</h6>
                                <h4><?php echo $stats['lunas']; ?></h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card bg-danger text-white">
                            <div class="card-body">
                                <h6 class="card-title">Telat</h6>
                                <h4><?php echo $stats['telat']; ?></h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card bg-info text-white">
                            <div class="card-body">
                                <h6 class="card-title">Total Tagihan</h6>
                                <h5><?php echo formatRupiah($stats['total_tagihan']); ?></h5>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card bg-secondary text-white">
                            <div class="card-body">
                                <h6 class="card-title">Total Dibayar</h6>
                                <h5><?php echo formatRupiah($stats['total_dibayar']); ?></h5>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Late Payments Alert -->
                <?php if (!empty($late_payments)): ?>
                <div class="alert alert-danger">
                    <h5><i class="bi bi-exclamation-triangle"></i> Tunggakan Terdeteksi!</h5>
                    <p>Ada <?php echo count($late_payments); ?> angsuran yang terlambat pembayarannya.</p>
                    <button class="btn btn-outline-danger btn-sm" onclick="showLatePayments()">
                        <i class="bi bi-eye"></i> Lihat Detail
                    </button>
                </div>
                
                <!-- Late Payments Modal -->
                <div class="modal fade" id="latePaymentsModal" tabindex="-1">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Daftar Tunggakan</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Nasabah</th>
                                                <th>Pinjaman</th>
                                                <th>Angsuran Ke</th>
                                                <th>Jatuh Tempo</th>
                                                <th>Tagihan</th>
                                                <th>Telepon</th>
                                                <th>Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($late_payments as $lp): ?>
                                                <tr>
                                                    <td><?php echo $lp['nama']; ?></td>
                                                    <td><?php echo $lp['kode_pinjaman']; ?></td>
                                                    <td><?php echo $lp['no_angsuran']; ?></td>
                                                    <td><?php echo formatDate($lp['jatuh_tempo']); ?></td>
                                                    <td><?php echo formatRupiah($lp['total_angsuran']); ?></td>
                                                    <td>
                                                        <a href="https://wa.me/<?php echo str_replace(['+', '-'], '', $lp['telp']); ?>" target="_blank" class="btn btn-sm btn-success">
                                                            <i class="bi bi-whatsapp"></i> WA
                                                        </a>
                                                    </td>
                                                    <td>
                                                        <a href="bayar.php?id=<?php echo $lp['id']; ?>" class="btn btn-sm btn-primary">
                                                            <i class="bi bi-cash"></i> Bayar
                                                        </a>
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
                <?php endif; ?>
                
                <!-- Filter and Search -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-3">
                                <input type="text" name="search" class="form-control" placeholder="Cari nama, kode..." value="<?php echo htmlspecialchars($search); ?>">
                            </div>
                            <div class="col-md-2">
                                <select name="status" class="form-select">
                                    <option value="">Semua Status</option>
                                    <option value="belum" <?php echo $status === 'belum' ? 'selected' : ''; ?>>Belum Bayar</option>
                                    <option value="lunas" <?php echo $status === 'lunas' ? 'selected' : ''; ?>>Lunas</option>
                                    <option value="telat" <?php echo $status === 'telat' ? 'selected' : ''; ?>>Telat</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <input type="month" name="bulan" class="form-control" value="<?php echo htmlspecialchars($bulan); ?>">
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
                            <table class="table table-striped table-hover" id="angsuranTable">
                                <thead class="table-dark">
                                    <tr>
                                        <th>No</th>
                                        <th>Nasabah</th>
                                        <th>Pinjaman</th>
                                        <th>Jatuh Tempo</th>
                                        <th>Tagihan</th>
                                        <th>Denda</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($angsuran)): ?>
                                        <tr>
                                            <td colspan="9" class="text-center text-muted">Tidak ada data angsuran</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($angsuran as $a): ?>
                                            <tr>
                                                <td><?php echo $a['no_angsuran']; ?></td>
                                                <td>
                                                    <div>
                                                        <strong><?php echo $a['nama']; ?></strong>
                                                        <br>
                                                        <small class="text-muted"><?php echo $a['kode_nasabah']; ?></small>
                                                    </div>
                                                </td>
                                                <td><?php echo $a['kode_pinjaman']; ?></td>
                                                <td><?php echo formatDate($a['jatuh_tempo']); ?></td>
                                                <td><?php echo formatRupiah($a['total_angsuran']); ?></td>
                                                <td>
                                                    <?php if ($a['denda'] > 0): ?>
                                                        <span class="text-danger"><?php echo formatRupiah($a['denda']); ?></span>
                                                    <?php else: ?>
                                                        -
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php 
                                                    $total = $a['total_angsuran'] + $a['denda'];
                                                    echo formatRupiah($total);
                                                    ?>
                                                </td>
                                                <td>
                                                    <?php
                                                    $status_class = [
                                                        'belum' => 'warning',
                                                        'lunas' => 'success',
                                                        'telat' => 'danger'
                                                    ];
                                                    ?>
                                                    <span class="badge bg-<?php echo $status_class[$a['status']]; ?>">
                                                        <?php echo ucfirst($a['status']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm" role="group">
                                                        <a href="../pinjaman/detail.php?id=<?php echo $a['pinjaman_id']; ?>" class="btn btn-outline-primary" title="Detail Pinjaman">
                                                            <i class="bi bi-eye"></i>
                                                        </a>
                                                        <?php if ($a['status'] !== 'lunas'): ?>
                                                            <a href="bayar.php?id=<?php echo $a['id']; ?>" class="btn btn-outline-success" title="Bayar">
                                                                <i class="bi bi-cash"></i>
                                                            </a>
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
            var hasData = <?php echo !empty($angsuran) ? 'true' : 'false'; ?>;

            if (hasData) {
                try {
                    var table = $('#angsuranTable').DataTable({
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
                        order: [[3, 'asc']]
                    });
                } catch (e) {
                    console.error('DataTables initialization error:', e);
                    $('#angsuranTable').removeClass('table-striped table-hover');
                }
            } else {
                // Hide DataTables controls when no data
                $('#angsuranTable').removeClass('table-striped table-hover');
                $('#angsuranTable_wrapper').hide();
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
            
            // Initialize Flatpickr for month input
            flatpickr('input[type="month"]', {
                locale: 'id',
                dateFormat: 'Y-m',
                allowInput: true,
                altInput: true,
                altFormat: 'F Y',
                theme: 'light'
            });
        });
        
        function showLatePayments() {
            new bootstrap.Modal(document.getElementById('latePaymentsModal')).show();
        }
        
        function showPending() {
            window.location.href = '?status=pending';
        }

        // Convert session alerts to SweetAlert2
        <?= getSessionAlertsJS() ?>
    </script>
</body>
</html>
