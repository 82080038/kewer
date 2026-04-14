<?php
require_once '../../includes/functions.php';
requireLogin();

$cabang_id = getCurrentCabang();

if ($_POST) {
    $pinjaman_id = $_POST['pinjaman_id'] ?? '';
    $angsuran_id = $_POST['angsuran_id'] ?? '';
    $jumlah_bayar = $_POST['jumlah_bayar'] ?? 0;
    $denda = $_POST['denda'] ?? 0;
    $tanggal_bayar = $_POST['tanggal_bayar'] ?? '';
    $cara_bayar = $_POST['cara_bayar'] ?? '';
    $keterangan = $_POST['keterangan'] ?? '';
    
    $total_bayar = $jumlah_bayar + $denda;
    $petugas_id = getCurrentUser()['id'];
    
    // Generate kode pembayaran
    $kode_pembayaran = generateKode('BYR', 'pembayaran', 'kode_pembayaran');
    
    // Insert pembayaran
    $result = query("INSERT INTO pembayaran (cabang_id, pinjaman_id, angsuran_id, kode_pembayaran, jumlah_bayar, denda, total_bayar, tanggal_bayar, cara_bayar, petugas_id, keterangan) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)", [
        $cabang_id,
        $pinjaman_id,
        $angsuran_id,
        $kode_pembayaran,
        $jumlah_bayar,
        $denda,
        $total_bayar,
        $tanggal_bayar,
        $cara_bayar,
        $petugas_id,
        $keterangan
    ]);
    
    if ($result) {
        // Update angsuran status
        $angsuran = query("SELECT * FROM angsuran WHERE id = ?", [$angsuran_id])[0];
        if ($jumlah_bayar >= $angsuran['total_angsuran']) {
            query("UPDATE angsuran SET status = 'lunas', tanggal_bayar = ?, total_bayar = ? WHERE id = ?", [$tanggal_bayar, $jumlah_bayar, $angsuran_id]);
        } else {
            query("UPDATE angsuran SET total_bayar = total_bayar + ?, status = 'telat' WHERE id = ?", [$jumlah_bayar, $angsuran_id]);
        }
        
        logAudit('CREATE', 'pembayaran', $result, null, ['kode_pembayaran' => $kode_pembayaran, 'jumlah_bayar' => $jumlah_bayar]);
        
        $_SESSION['success'] = 'Pembayaran berhasil ditambahkan';
        header('Location: index.php');
        exit();
    } else {
        $_SESSION['error'] = 'Gagal menambahkan pembayaran';
    }
}

header('Location: index.php');
exit();
