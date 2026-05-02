<?php
require_once 'config/path.php';
require_once BASE_PATH . '/includes/functions.php';

echo "==========================================\n";
echo "DATA BOS YANG BARU DIDAFTARKAN\n";
echo "==========================================\n\n";

// Check bos_registrations table
$registrations = query("SELECT * FROM bos_registrations ORDER BY created_at DESC");
if ($registrations && count($registrations) > 0) {
    echo "PENDAFTARAN BOS (bos_registrations):\n";
    echo "----------------------------------------\n";
    foreach ($registrations as $reg) {
        echo "ID: " . $reg['id'] . "\n";
        echo "Username: " . $reg['username'] . "\n";
        echo "Nama: " . $reg['nama'] . "\n";
        echo "Email: " . ($reg['email'] ?? '-') . "\n";
        echo "No. Telepon: " . ($reg['telp'] ?? '-') . "\n";
        echo "Nama Perusahaan: " . $reg['nama_perusahaan'] . "\n";
        echo "Alamat: " . ($reg['alamat'] ?? '-') . "\n";
        echo "Province ID: " . ($reg['province_id'] ?? '-') . "\n";
        echo "Regency ID: " . ($reg['regency_id'] ?? '-') . "\n";
        echo "District ID: " . ($reg['district_id'] ?? '-') . "\n";
        echo "Village ID: " . ($reg['village_id'] ?? '-') . "\n";
        echo "Status: " . $reg['status'] . "\n";
        echo "Created At: " . $reg['created_at'] . "\n";
        echo "----------------------------------------\n";
    }
} else {
    echo "Tidak ada pendaftaran bos di tabel bos_registrations\n";
}

echo "\n";

// Check users table for bos role
$users = query("SELECT * FROM users WHERE role = 'bos' ORDER BY created_at DESC");
if ($users && count($users) > 0) {
    echo "USER DENGAN ROLE BOS (users):\n";
    echo "----------------------------------------\n";
    foreach ($users as $user) {
        echo "ID: " . $user['id'] . "\n";
        echo "Username: " . $user['username'] . "\n";
        echo "Nama: " . $user['nama'] . "\n";
        echo "Email: " . ($user['email'] ?? '-') . "\n";
        echo "Cabang ID: " . ($user['cabang_id'] ?? 'NULL') . "\n";
        echo "Status: " . $user['status'] . "\n";
        echo "Created At: " . $user['created_at'] . "\n";
        echo "----------------------------------------\n";
    }
} else {
    echo "Tidak ada user dengan role bos di tabel users\n";
}

echo "\n==========================================\n";
