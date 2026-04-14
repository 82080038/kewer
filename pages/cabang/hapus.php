<?php
require_once '../../includes/functions.php';
requireLogin();

if (!hasRole('superadmin')) {
    header('Location: ../../dashboard.php');
    exit();
}

$id = $_GET['id'] ?? '';
if (!$id) {
    header('Location: index.php');
    exit();
}

$cabang = query("SELECT * FROM cabang WHERE id = ?", [$id]);
if (!$cabang) {
    header('Location: index.php');
    exit();
}
$cabang = $cabang[0];

// Check if cabang has users or other dependencies
$users_count = query("SELECT COUNT(*) as count FROM users WHERE cabang_id = ?", [$id])[0]['count'];
$nasabah_count = query("SELECT COUNT(*) as count FROM nasabah WHERE cabang_id = ?", [$id])[0]['count'];

if ($users_count > 0 || $nasabah_count > 0) {
    $_SESSION['error'] = 'Cabang tidak dapat dihapus karena masih memiliki data terkait';
    header('Location: index.php');
    exit();
}

$oldValue = $cabang;
$result = query("DELETE FROM cabang WHERE id = ?", [$id]);

if ($result) {
    logAudit('DELETE', 'cabang', $id, $oldValue, null);
    $_SESSION['success'] = 'Cabang berhasil dihapus';
} else {
    $_SESSION['error'] = 'Gagal menghapus cabang';
}

header('Location: index.php');
exit();
