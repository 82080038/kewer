<?php
require_once __DIR__ . '/../../config/path.php';
require_once BASE_PATH . '/includes/functions.php';
requireLogin();

// Only users with assign_permissions permission can access
if (!hasPermission('assign_permissions')) {
    header('Location: ' . baseUrl('dashboard.php'));
    exit();
}

$user_id = $_GET['user_id'] ?? '';

if (!$user_id) {
    header('Location: ' . baseUrl('pages/users/index.php'));
    exit();
}

// Get target user
$target_user = query("SELECT * FROM users WHERE id = ?", [$user_id]);
if (!$target_user) {
    header('Location: ' . baseUrl('pages/users/index.php'));
    exit();
}

$target_user = $target_user[0];

// Check if current user can manage this target user
if (!canManageRole($target_user['role'])) {
    header('Location: ' . baseUrl('pages/users/index.php'));
    exit();
}

// Get all permissions grouped by category
$all_permissions = query("SELECT * FROM permissions ORDER BY kategori, nama");
$permissions_by_category = [];
foreach ($all_permissions as $perm) {
    $permissions_by_category[$perm['kategori']][] = $perm;
}

// Get user's current permissions
$user_permissions = getUserPermissions($user_id);
$user_permission_codes = [];
foreach ($user_permissions as $perm) {
    if ($perm['granted']) {
        $user_permission_codes[] = $perm['kode'];
    }
}

// Handle permission updates
if ($_POST) {
    $permissions_to_grant = $_POST['permissions'] ?? [];
    
    // Get all permission codes
    foreach ($all_permissions as $perm) {
        $granted = in_array($perm['kode'], $permissions_to_grant);
        grantPermission($user_id, $perm['kode'], $granted);
    }
    
    header('Location: index.php?user_id=' . $user_id . '&success=1');
    exit();
}

// Get current user info
$current_user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Permissions - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        .permission-category {
            margin-bottom: 20px;
        }
        .permission-category h5 {
            margin-bottom: 15px;
            color: #495057;
            border-bottom: 2px solid #dee2e6;
            padding-bottom: 10px;
        }
        .permission-item {
            padding: 10px;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            margin-bottom: 10px;
            background: #f8f9fa;
        }
        .permission-item:hover {
            background: #e9ecef;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <nav class="col-md-3 d-none d-md-block bg-light sidebar">
                <div class="position-sticky pt-3">
                    <h5 class="text-center mb-4"><?php echo APP_NAME; ?></h5>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="../../dashboard.php">
                                <i class="bi bi-house"></i> Dashboard
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
                            <a class="nav-link active" href="../users/index.php">
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
                    <h1 class="h2">Kelola Permissions: <?= htmlspecialchars($target_user['nama']) ?></h1>
                    <a href="../users/index.php" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Kembali
                    </a>
                </div>

                <?php if (isset($_GET['success'])): ?>
                    <div class="alert alert-success">
                        <i class="bi bi-check-circle"></i> Permissions berhasil diperbarui!
                    </div>
                <?php endif; ?>

                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Informasi User</h5>
                        <table class="table table-sm">
                            <tr>
                                <th>Username:</th>
                                <td><?= htmlspecialchars($target_user['username']) ?></td>
                            </tr>
                            <tr>
                                <th>Nama:</th>
                                <td><?= htmlspecialchars($target_user['nama']) ?></td>
                            </tr>
                            <tr>
                                <th>Role:</th>
                                <td><?= htmlspecialchars($target_user['role']) ?></td>
                            </tr>
                            <tr>
                                <th>Email:</th>
                                <td><?= htmlspecialchars($target_user['email']) ?></td>
                            </tr>
                        </table>
                    </div>
                </div>

                <form method="POST">
                    <?= csrfField() ?>
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title mb-4">Permissions</h5>
                            
                            <?php foreach ($permissions_by_category as $category => $permissions): ?>
                                <div class="permission-category">
                                    <h5><?= ucfirst(htmlspecialchars($category)) ?></h5>
                                    <?php foreach ($permissions as $perm): ?>
                                        <div class="permission-item">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" 
                                                       name="permissions[]" 
                                                       value="<?= htmlspecialchars($perm['kode']) ?>"
                                                       id="perm_<?= htmlspecialchars($perm['kode']) ?>"
                                                       <?= in_array($perm['kode'], $user_permission_codes) ? 'checked' : '' ?>>
                                                <label class="form-check-label" for="perm_<?= htmlspecialchars($perm['kode']) ?>">
                                                    <strong><?= htmlspecialchars($perm['nama']) ?></strong>
                                                    <small class="text-muted d-block"><?= htmlspecialchars($perm['deskripsi']) ?></small>
                                                </label>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endforeach; ?>
                            
                            <div class="mt-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save"></i> Simpan Permissions
                                </button>
                                <a href="../users/index.php" class="btn btn-secondary">
                                    <i class="bi bi-x"></i> Batal
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
