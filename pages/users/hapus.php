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

$user = query("SELECT * FROM users WHERE id = ?", [$id]);
if (!$user) {
    header('Location: index.php');
    exit();
}
$user = $user[0];

// Check if user has active loans or other dependencies
$active_loans = query("SELECT COUNT(*) as count FROM pinjaman WHERE petugas_id = ?", [$id])[0]['count'];

if ($active_loans > 0) {
    $_SESSION['error'] = 'User tidak dapat dihapus karena memiliki pinjaman aktif';
    header('Location: index.php');
    exit();
}

// Get old value for audit
$oldValue = $user;

// Delete user
$result = query("DELETE FROM users WHERE id = ?", [$id]);

if ($result) {
    // Log audit
    logAudit('DELETE', 'users', $id, $oldValue, null);
    
    $_SESSION['success'] = 'User berhasil dihapus';
} else {
    $_SESSION['error'] = 'Gagal menghapus user';
}

header('Location: index.php');
exit();
