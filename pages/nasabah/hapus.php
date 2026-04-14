<?php
require_once '../../includes/functions.php';
requireLogin();

$id = $_GET['id'];
$cabang_id = getCurrentCabang();

// Check if nasabah exists and belongs to current cabang
$nasabah = query("SELECT * FROM nasabah WHERE id = ? AND cabang_id = ?", [$id, $cabang_id]);

if (!$nasabah) {
    header('Location: index.php');
    exit();
}

$nasabah = $nasabah[0];

// Check if nasabah has active loans
$active_loans = query("SELECT COUNT(*) as count FROM pinjaman WHERE nasabah_id = ? AND status IN ('pengajuan', 'disetujui', 'aktif')", [$id])[0]['count'];

if ($active_loans > 0) {
    $_SESSION['error'] = 'Tidak dapat menghapus nasabah yang masih memiliki pinjaman aktif';
    header('Location: index.php');
    exit();
}

// Delete files
if ($nasabah['foto_ktp'] && file_exists('../../' . $nasabah['foto_ktp'])) {
    unlink('../../' . $nasabah['foto_ktp']);
}

if ($nasabah['foto_selfie'] && file_exists('../../' . $nasabah['foto_selfie'])) {
    unlink('../../' . $nasabah['foto_selfie']);
}

// Delete nasabah
$result = query("DELETE FROM nasabah WHERE id = ?", [$id]);

if ($result) {
    $_SESSION['success'] = 'Nasabah berhasil dihapus';
} else {
    $_SESSION['error'] = 'Gagal menghapus nasabah';
}

header('Location: index.php');
exit();
?>
