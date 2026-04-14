<?php
require_once '../../includes/functions.php';
requireLogin();

$cabang_id = getCurrentCabang();
require_once '../../includes/kas_petugas.php';
$kasPetugas = new KasPetugas($cabang_id);

$tanggal = $_GET['tanggal'] ?? date('Y-m-d');
$records = $kasPetugas->getBranchRecords($tanggal);
$discrepancies = $kasPetugas->getDiscrepancies($tanggal);
$summary = $kasPetugas->getDailySummary($tanggal);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kas Petugas - Kewer</title>
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
    <div class="container-fluid">
        <div class="row">
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
                <?php if ($summary): ?>
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <h6>Total Petugas</h6>
                                <h3><?= $summary['jumlah_petugas'] ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-info text-white">
                            <div class="card-body">
                                <h6>Total Saldo Awal</h6>
                                <h3><?= formatRupiah($summary['total_saldo_awal']) ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-warning text-dark">
                            <div class="card-body">
                                <h6>Total Terima</h6>
                                <h3><?= formatRupiah($summary['total_terima']) ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <h6>Total Disetor</h6>
                                <h3><?= formatRupiah($summary['total_disetor']) ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Discrepancies Alert -->
                <?php if (!empty($discrepancies)): ?>
                <div class="alert alert-warning mb-4">
                    <h6><i class="bi bi-exclamation-triangle"></i> Ada Selisih Kas:</h6>
                    <ul class="mb-0">
                        <?php foreach ($discrepancies as $discrepancy): ?>
                        <li>
                            <?= $discrepancy['nama_petugas'] ?> - 
                            Status: <?= $discrepancy['status'] ?>, 
                            Selisih: <?= formatRupiah(abs($discrepancy['saldo_akhir'])) ?>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>

                <!-- Records Table -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Rekas Kas Petugas - <?= formatDate($tanggal) ?></h5>
                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addModal">
                            <i class="bi bi-plus"></i> Tambah Record
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover" id="kasPetugasTable">
                                <thead>
                                    <tr>
                                        <th>Petugas</th>
                                        <th>Saldo Awal</th>
                                        <th>Total Terima</th>
                                        <th>Total Disetor</th>
                                        <th>Saldo Akhir</th>
                                        <th>Status</th>
                                        <th>Catatan</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($records as $record): ?>
                                    <tr>
                                        <td><?= $record['nama_petugas'] ?></td>
                                        <td><?= formatRupiah($record['saldo_awal']) ?></td>
                                        <td><?= formatRupiah($record['total_terima']) ?></td>
                                        <td><?= formatRupiah($record['total_disetor']) ?></td>
                                        <td><?= formatRupiah($record['saldo_akhir']) ?></td>
                                        <td>
                                            <?php if ($record['status'] == 'lengkap'): ?>
                                                <span class="badge bg-success">Lengkap</span>
                                            <?php elseif ($record['status'] == 'kurang'): ?>
                                                <span class="badge bg-warning">Kurang</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Lebih</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= $record['catatan'] ?? '-' ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-info" onclick="setorKas(<?= $record['id'] ?>)">
                                                <i class="bi bi-cash"></i> Setor
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
                    <h5 class="modal-title">Tambah Record Kas Petugas</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addForm">
                        <div class="mb-3">
                            <label>Petugas</label>
                            <select class="form-select" name="petugas_id" required>
                                <option value="">Pilih Petugas</option>
                                <?php
                                $petugas = query("SELECT * FROM users WHERE role = 'petugas' AND cabang_id = ? AND status = 'aktif'", [$cabang_id]);
                                foreach ($petugas as $p): ?>
                                    <option value="<?= $p['id'] ?>"><?= $p['nama'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label>Tanggal</label>
                            <input type="date" class="form-control" name="tanggal" value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="mb-3">
                            <label>Saldo Awal</label>
                            <input type="number" class="form-control" name="saldo_awal" value="0">
                        </div>
                        <div class="mb-3">
                            <label>Total Terima</label>
                            <input type="number" class="form-control" name="total_terima" value="0">
                        </div>
                        <div class="mb-3">
                            <label>Total Disetor</label>
                            <input type="number" class="form-control" name="total_disetor" value="0">
                        </div>
                        <div class="mb-3">
                            <label>Catatan</label>
                            <textarea class="form-control" name="catatan" rows="2"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" onclick="saveRecord()">Simpan</button>
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
            // Initialize DataTable
            $('#kasPetugasTable').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json'
                },
                pageLength: 25,
                lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
                responsive: true,
                order: [[0, 'desc']]
            });
            
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
        function filterByDate() {
            const tanggal = document.getElementById('tanggal').value;
            window.location.href = '?tanggal=' + tanggal;
        }

        function saveRecord() {
            const form = document.getElementById('addForm');
            const formData = new FormData(form);
            const data = Object.fromEntries(formData.entries());

            fetch('/api/kas_petugas', {
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
                    alert('Record berhasil ditambahkan');
                    location.reload();
                } else {
                    alert('Gagal menambahkan record: ' + data.error);
                }
            });
        }

        function setorKas(id) {
            const jumlah = prompt('Masukkan jumlah yang disetor:');
            if (jumlah) {
                // Update the record with deposit
                // This would need an API call to update the record
                alert('Fitur setor akan diimplementasikan');
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
