<?php
require_once '../../includes/functions.php';
requireLogin();

if (!hasRole('superadmin')) {
    header('Location: ../../dashboard.php');
    exit();
}

if ($_POST) {
    $kode_cabang = $_POST['kode_cabang'] ?? '';
    $nama_cabang = $_POST['nama_cabang'] ?? '';
    $alamat = $_POST['alamat'] ?? '';
    $telp = $_POST['telp'] ?? '';
    $email = $_POST['email'] ?? '';
    $kota = $_POST['kota'] ?? '';
    $provinsi = $_POST['provinsi'] ?? '';
    $kode_pos = $_POST['kode_pos'] ?? '';
    $status = $_POST['status'] ?? 'aktif';
    
    // Check duplicate kode_cabang
    $check = query("SELECT id FROM cabang WHERE kode_cabang = ?", [$kode_cabang]);
    if ($check) {
        $_SESSION['error'] = 'Kode cabang sudah digunakan';
    } else {
        // Insert cabang
        $result = query("INSERT INTO cabang (kode_cabang, nama_cabang, alamat, telp, email, kota, provinsi, kode_pos, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)", [
            $kode_cabang,
            $nama_cabang,
            $alamat,
            $telp,
            $email,
            $kota,
            $provinsi,
            $kode_pos,
            $status
        ]);
        
        if ($result) {
            logAudit('CREATE', 'cabang', $result, null, ['kode_cabang' => $kode_cabang, 'nama_cabang' => $nama_cabang]);
            $_SESSION['success'] = 'Cabang berhasil ditambahkan';
            header('Location: index.php');
            exit();
        } else {
            $_SESSION['error'] = 'Gagal menambahkan cabang';
        }
    }
}

header('Location: index.php');
exit();
