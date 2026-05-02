<?php
require_once __DIR__ . '/../../config/path.php';
require_once BASE_PATH . '/includes/functions.php';
requireLogin();

if (!hasPermission('view_laporan') && !hasPermission('assign_permissions')) {
    header('Location: ' . baseUrl('dashboard.php'));
    exit();
}

$cabang_id = getCurrentCabang();

// Filters
$filter_action = $_GET['action'] ?? '';
$filter_table = $_GET['table_name'] ?? '';
$filter_user = $_GET['user_id'] ?? '';
$filter_date_from = $_GET['date_from'] ?? date('Y-m-d', strtotime('-7 days'));
$filter_date_to = $_GET['date_to'] ?? date('Y-m-d');

// Build query
$where = ["1=1"];
$params = [];

if ($filter_action) {
    $where[] = "a.action = ?";
    $params[] = $filter_action;
}
if ($filter_table) {
    $where[] = "a.table_name = ?";
    $params[] = $filter_table;
}
if ($filter_user) {
    $where[] = "a.user_id = ?";
    $params[] = $filter_user;
}
if ($filter_date_from) {
    $where[] = "DATE(a.created_at) >= ?";
    $params[] = $filter_date_from;
}
if ($filter_date_to) {
    $where[] = "DATE(a.created_at) <= ?";
    $params[] = $filter_date_to;
}

$where_clause = implode(" AND ", $where);

$logs = query("
    SELECT a.*, u.nama as nama_user, u.role
    FROM audit_log a
    LEFT JOIN users u ON a.user_id = u.id
    WHERE $where_clause
    ORDER BY a.created_at DESC
    LIMIT 500
", $params);
if (!is_array($logs)) $logs = [];

// Get distinct actions and tables for filter dropdowns
$actions = query("SELECT DISTINCT action FROM audit_log ORDER BY action");
if (!is_array($actions)) $actions = [];
$tables = query("SELECT DISTINCT table_name FROM audit_log ORDER BY table_name");
if (!is_array($tables)) $tables = [];
$users = query("SELECT id, nama FROM users ORDER BY nama");
if (!is_array($users)) $users = [];

// Stats
$stats = query("
    SELECT 
        COUNT(*) as total,
        COUNT(DISTINCT user_id) as unique_users,
        COUNT(DISTINCT action) as unique_actions,
        COUNT(DISTINCT table_name) as unique_tables
    FROM audit_log
    WHERE DATE(created_at) >= ? AND DATE(created_at) <= ?
", [$filter_date_from, $filter_date_to]);
$stat = is_array($stats) && isset($stats[0]) ? $stats[0] : ['total' => 0, 'unique_users' => 0, 'unique_actions' => 0, 'unique_tables' => 0];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Audit Trail - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .json-preview { max-height: 200px; overflow-y: auto; font-size: 11px; background: #f8f9fa; padding: 8px; border-radius: 4px; white-space: pre-wrap; word-break: break-all; }
        .sidebar { min-height: calc(100vh - 56px); }
    </style>
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
                    <h1 class="h2"><i class="bi bi-shield-check"></i> Audit Trail</h1>
                </div>
                
                <!-- Stats -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body py-2">
                                <h6>Total Log</h6>
                                <h4><?php echo number_format($stat['total']); ?></h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-info text-white">
                            <div class="card-body py-2">
                                <h6>User Aktif</h6>
                                <h4><?php echo $stat['unique_users']; ?></h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body py-2">
                                <h6>Jenis Aksi</h6>
                                <h4><?php echo $stat['unique_actions']; ?></h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-warning text-white">
                            <div class="card-body py-2">
                                <h6>Tabel Terdampak</h6>
                                <h4><?php echo $stat['unique_tables']; ?></h4>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Filters -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-2 align-items-end">
                            <div class="col-md-2">
                                <label class="form-label form-label-sm">Aksi</label>
                                <select name="action" class="form-select form-select-sm">
                                    <option value="">Semua Aksi</option>
                                    <?php foreach ($actions as $a): ?>
                                        <option value="<?php echo $a['action']; ?>" <?php echo $filter_action === $a['action'] ? 'selected' : ''; ?>>
                                            <?php echo ucfirst($a['action']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label form-label-sm">Tabel</label>
                                <select name="table_name" class="form-select form-select-sm">
                                    <option value="">Semua Tabel</option>
                                    <?php foreach ($tables as $t): ?>
                                        <option value="<?php echo $t['table_name']; ?>" <?php echo $filter_table === $t['table_name'] ? 'selected' : ''; ?>>
                                            <?php echo $t['table_name']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label form-label-sm">User</label>
                                <select name="user_id" class="form-select form-select-sm">
                                    <option value="">Semua User</option>
                                    <?php foreach ($users as $u): ?>
                                        <option value="<?php echo $u['id']; ?>" <?php echo $filter_user == $u['id'] ? 'selected' : ''; ?>>
                                            <?php echo $u['nama']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label form-label-sm">Dari</label>
                                <input type="date" name="date_from" class="form-control form-control-sm" value="<?php echo $filter_date_from; ?>">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label form-label-sm">Sampai</label>
                                <input type="date" name="date_to" class="form-control form-control-sm" value="<?php echo $filter_date_to; ?>">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-sm btn-primary me-1"><i class="bi bi-search"></i> Filter</button>
                                <a href="index.php" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-clockwise"></i></a>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Audit Log Table -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-sm table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th style="width:5%">#</th>
                                        <th style="width:14%">Waktu</th>
                                        <th style="width:12%">User</th>
                                        <th style="width:10%">Aksi</th>
                                        <th style="width:10%">Tabel</th>
                                        <th style="width:5%">ID</th>
                                        <th style="width:10%">IP</th>
                                        <th style="width:34%">Detail</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($logs)): ?>
                                        <tr><td colspan="8" class="text-center text-muted">Tidak ada data audit</td></tr>
                                    <?php else: ?>
                                        <?php foreach ($logs as $log): ?>
                                            <tr>
                                                <td><?php echo $log['id']; ?></td>
                                                <td><small><?php echo date('d/m/Y H:i:s', strtotime($log['created_at'])); ?></small></td>
                                                <td>
                                                    <small>
                                                        <?php echo $log['nama_user'] ?? 'System'; ?>
                                                        <?php if ($log['role']): ?>
                                                            <br><span class="badge bg-secondary"><?php echo $log['role']; ?></span>
                                                        <?php endif; ?>
                                                    </small>
                                                </td>
                                                <td>
                                                    <?php
                                                    $action_colors = [
                                                        'create' => 'success', 'insert' => 'success',
                                                        'update' => 'warning', 'edit' => 'warning',
                                                        'delete' => 'danger', 'hapus' => 'danger',
                                                        'login' => 'info', 'logout' => 'secondary',
                                                        'approve' => 'primary', 'reject' => 'danger',
                                                        'blacklist' => 'danger', 'unblacklist' => 'success',
                                                        'pembayaran' => 'success'
                                                    ];
                                                    $color = $action_colors[$log['action']] ?? 'secondary';
                                                    ?>
                                                    <span class="badge bg-<?php echo $color; ?>"><?php echo ucfirst($log['action']); ?></span>
                                                </td>
                                                <td><small><?php echo $log['table_name']; ?></small></td>
                                                <td><?php echo $log['record_id'] ?? '-'; ?></td>
                                                <td><small><?php echo $log['ip_address'] ?? '-'; ?></small></td>
                                                <td>
                                                    <?php if ($log['old_value'] || $log['new_value']): ?>
                                                        <button class="btn btn-xs btn-outline-info" type="button" data-bs-toggle="collapse" data-bs-target="#detail-<?php echo $log['id']; ?>">
                                                            <i class="bi bi-eye"></i> Detail
                                                        </button>
                                                        <div class="collapse mt-1" id="detail-<?php echo $log['id']; ?>">
                                                            <?php if ($log['old_value']): ?>
                                                                <div class="mb-1"><small class="text-danger">Sebelum:</small></div>
                                                                <div class="json-preview"><?php echo htmlspecialchars($log['old_value']); ?></div>
                                                            <?php endif; ?>
                                                            <?php if ($log['new_value']): ?>
                                                                <div class="mb-1 mt-1"><small class="text-success">Sesudah:</small></div>
                                                                <div class="json-preview"><?php echo htmlspecialchars($log['new_value']); ?></div>
                                                            <?php endif; ?>
                                                        </div>
                                                    <?php else: ?>
                                                        <small class="text-muted">-</small>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="text-muted mt-2">
                            <small>Menampilkan <?php echo count($logs); ?> log terbaru (max 500)</small>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
