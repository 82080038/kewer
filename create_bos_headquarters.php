<?php
require_once 'config/path.php';
require_once BASE_PATH . '/includes/functions.php';
require_once BASE_PATH . '/config/database.php';

// Get bos user
$bos_user = query("SELECT id, username, nama FROM users WHERE role = 'bos' AND status = 'aktif' LIMIT 1");

if (!$bos_user) {
    echo "No bos user found\n";
    exit;
}

$bos = $bos_user[0];
echo "Creating headquarters for bos: ID {$bos['id']}, Username {$bos['username']}\n";

// Check if bos already has headquarters
$existing_hq = query("SELECT * FROM cabang WHERE owner_bos_id = ? AND is_headquarters = 1", [$bos['id']]);

if ($existing_hq) {
    echo "Bos already has headquarters: {$existing_hq[0]['nama_cabang']}\n";
    exit;
}

// Create headquarters
$kode_cabang = 'HQ-' . strtoupper($bos['username']);
$nama_cabang = 'Headquarters ' . $bos['nama'];
$alamat = 'Jalan Test No. 123, RT/RW 001/002';
$province_id = 3; // SUMATERA UTARA
$regency_id = 31;
$district_id = 402;
$village_id = 8197;

$result = query(
    "INSERT INTO cabang (kode_cabang, nama_cabang, alamat, province_id, regency_id, district_id, village_id, is_headquarters, owner_bos_id, created_by_user_id, status) VALUES (?, ?, ?, ?, ?, ?, ?, 1, ?, ?, 'aktif')",
    [$kode_cabang, $nama_cabang, $alamat, $province_id, $regency_id, $district_id, $village_id, $bos['id'], $bos['id']]
);

if ($result) {
    echo "✅ Headquarters created successfully\n";
    echo "Kode Cabang: {$kode_cabang}\n";
    echo "Nama Cabang: {$nama_cabang}\n";
    
    // Get the created headquarters
    $hq = query("SELECT * FROM cabang WHERE owner_bos_id = ? AND is_headquarters = 1", [$bos['id']]);
    if ($hq) {
        echo "Cabang ID: {$hq[0]['id']}\n";
    }
} else {
    echo "❌ Failed to create headquarters\n";
}
