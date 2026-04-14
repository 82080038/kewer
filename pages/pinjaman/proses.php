<?php
require_once '../../includes/functions.php';
requireLogin();

$action = $_GET['action'] ?? '';
$id = $_GET['id'] ?? '';

if (!$action || !$id) {
    header('Location: index.php');
    exit();
}

// Get pinjaman data
$pinjaman = query("SELECT * FROM pinjaman WHERE id = ? AND cabang_id = ?", [$id, getCurrentCabang()]);

if (!$pinjaman) {
    header('Location: index.php');
    exit();
}

$pinjaman = $pinjaman[0];

switch ($action) {
    case 'approve':
        if ($pinjaman['status'] !== 'pengajuan') {
            $_SESSION['error'] = 'Hanya dapat menyetujui pinjaman dengan status pengajuan';
        } else {
            // Update status to disetujui
            $result = query("UPDATE pinjaman SET status = 'disetujui' WHERE id = ?", [$id]);
            
            if ($result) {
                // Create loan schedule
                createLoanSchedule($id, $pinjaman['plafon'], $pinjaman['tenor'], $pinjaman['bunga_per_bulan'], $pinjaman['tanggal_akad']);
                
                // Update to aktif
                query("UPDATE pinjaman SET status = 'aktif' WHERE id = ?", [$id]);
                
                $_SESSION['success'] = 'Pinjaman berhasil disetujui dan diaktifkan';
                
                // Send notification
                $nasabah = query("SELECT telp FROM nasabah WHERE id = ?", [$pinjaman['nasabah_id']])[0];
                $message = "Pinjaman Anda dengan kode {$pinjaman['kode_pinjaman']} telah disetujui. Plafon: " . formatRupiah($pinjaman['plafon']);
                sendWhatsApp($nasabah['telp'], $message);
            } else {
                $_SESSION['error'] = 'Gagal menyetujui pinjaman';
            }
        }
        break;
        
    case 'reject':
        if ($pinjaman['status'] !== 'pengajuan') {
            $_SESSION['error'] = 'Hanya dapat menolak pinjaman dengan status pengajuan';
        } else {
            $result = query("UPDATE pinjaman SET status = 'ditolak' WHERE id = ?", [$id]);
            
            if ($result) {
                $_SESSION['success'] = 'Pinjaman berhasil ditolak';
                
                // Send notification
                $nasabah = query("SELECT telp FROM nasabah WHERE id = ?", [$pinjaman['nasabah_id']])[0];
                $message = "Mohon maaf, pengajuan pinjaman Anda dengan kode {$pinjaman['kode_pinjaman']} ditolak.";
                sendWhatsApp($nasabah['telp'], $message);
            } else {
                $_SESSION['error'] = 'Gagal menolak pinjaman';
            }
        }
        break;
        
    case 'lunas':
        if ($pinjaman['status'] !== 'aktif') {
            $_SESSION['error'] = 'Hanya dapat melunasi pinjaman dengan status aktif';
        } else {
            // Check if all installments are paid
            $angsuran = query("SELECT COUNT(*) as total, SUM(CASE WHEN status = 'lunas' THEN 1 ELSE 0 END) as lunas FROM angsuran WHERE pinjaman_id = ?", [$id])[0];
            
            if ($angsuran['total'] == $angsuran['lunas']) {
                $result = query("UPDATE pinjaman SET status = 'lunas' WHERE id = ?", [$id]);
                
                if ($result) {
                    $_SESSION['success'] = 'Pinjaman berhasil dilunasi';
                } else {
                    $_SESSION['error'] = 'Gagal melunasi pinjaman';
                }
            } else {
                $_SESSION['error'] = 'Masih ada angsuran yang belum lunas';
            }
        }
        break;
        
    default:
        $_SESSION['error'] = 'Aksi tidak valid';
        break;
}

header('Location: index.php');
exit();
?>
