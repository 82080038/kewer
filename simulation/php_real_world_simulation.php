<?php
/**
 * PHP-based Real-World Simulation for Kewer Application
 * 
 * This script creates realistic test data for a koperasi pasar scenario
 */

require_once __DIR__ . '/../config/database.php';

echo "=== PHP Real-World Simulation ===\n\n";

// Phase 1: Create organizational structure
echo "PHASE 1: Creating Organizational Structure\n";

// Create BOS user
$bos_password = password_hash('password123', PASSWORD_DEFAULT);
$check = mysqli_query($conn, "SELECT id FROM users WHERE username = 'bos_simulasi'");
if (mysqli_num_rows($check) == 0) {
    mysqli_query($conn, "INSERT INTO users (username, password, nama, email, role, cabang_id, status) VALUES ('bos_simulasi', '$bos_password', 'Bos Simulasi', 'bos@kewer.id', 'bos', 1, 'aktif')");
    echo "✓ BOS user created\n";
} else {
    echo "✓ BOS user already exists\n";
}

// Create Manager Pusat
$manager_password = password_hash('password123', PASSWORD_DEFAULT);
$check = mysqli_query($conn, "SELECT id FROM users WHERE username = 'manager_pusat_sim'");
if (mysqli_num_rows($check) == 0) {
    mysqli_query($conn, "INSERT INTO users (username, password, nama, email, role, cabang_id, gaji, limit_kasbon, status) VALUES ('manager_pusat_sim', '$manager_password', 'Manager Pusat Simulasi', 'manager.pusat@kewer.id', 'manager_pusat', 1, 5000000, 1000000, 'aktif')");
    echo "✓ Manager Pusat user created\n";
} else {
    echo "✓ Manager Pusat user already exists\n";
}

echo "\n";

// Phase 2: Create Cabang (if not exists)
echo "PHASE 2: Creating Cabang\n";

$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM cabang");
$count = mysqli_fetch_assoc($result)['count'];

if ($count == 0) {
    mysqli_query($conn, "INSERT INTO cabang (kode_cabang, nama_cabang, alamat, is_headquarters) VALUES ('HQ001', 'Kantor Pusat', 'Jalan Pusat No. 1', 1)");
    $cabang_id = mysqli_insert_id($conn);
    echo "✓ Main cabang created with ID: $cabang_id\n";
} else {
    $result = mysqli_query($conn, "SELECT id FROM cabang LIMIT 1");
    $cabang_id = mysqli_fetch_assoc($result)['id'];
    echo "✓ Using existing cabang with ID: $cabang_id\n";
}

echo "\n";

// Create Petugas (after cabang is created)
echo "PHASE 1.5: Creating Petugas\n";

$petugas_password = password_hash('password123', PASSWORD_DEFAULT);
$check = mysqli_query($conn, "SELECT id FROM users WHERE username = 'petugas1_sim'");
if (mysqli_num_rows($check) == 0) {
    mysqli_query($conn, "INSERT INTO users (username, password, nama, email, role, cabang_id, gaji, limit_kasbon, status) VALUES ('petugas1_sim', '$petugas_password', 'Petugas Lapangan 1', 'petugas1@kewer.id', 'petugas_cabang', $cabang_id, 3000000, 500000, 'aktif')");
    $petugas_id = mysqli_insert_id($conn);
    echo "✓ Petugas user created\n";
} else {
    $petugas_id = mysqli_fetch_assoc($check)['id'];
    echo "✓ Petugas user already exists\n";
}

$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM cabang");
$count = mysqli_fetch_assoc($result)['count'];

if ($count == 0) {
    mysqli_query($conn, "INSERT INTO cabang (kode_cabang, nama_cabang, alamat, is_headquarters) VALUES ('HQ001', 'Kantor Pusat', 'Jalan Pusat No. 1', 1)");
    $cabang_id = mysqli_insert_id($conn);
    echo "✓ Main cabang created with ID: $cabang_id\n";
} else {
    $result = mysqli_query($conn, "SELECT id FROM cabang LIMIT 1");
    $cabang_id = mysqli_fetch_assoc($result)['id'];
    echo "✓ Using existing cabang with ID: $cabang_id\n";
}

echo "\n";

// Phase 3: Create Nasabah
echo "PHASE 3: Creating Nasabah\n";

$nasabah_data = [
    ['kode' => 'NSB001', 'nama' => 'Budi Santoso', 'alamat' => 'Pasar Induk Blok A No. 10', 'ktp' => '3201010101010001', 'telp' => '6281111111111', 'jenis_usaha' => 'Pedagang Sayur', 'lokasi_pasar' => 'Pasar Induk'],
    ['kode' => 'NSB002', 'nama' => 'Siti Aminah', 'alamat' => 'Pasar Induk Blok B No. 5', 'ktp' => '3201010101010002', 'telp' => '6282222222222', 'jenis_usaha' => 'Pedagang Buah', 'lokasi_pasar' => 'Pasar Induk'],
    ['kode' => 'NSB003', 'nama' => 'Ahmad Yani', 'alamat' => 'Pasar Induk Blok C No. 15', 'ktp' => '3201010101010003', 'telp' => '6283333333333', 'jenis_usaha' => 'Warung Sembako', 'lokasi_pasar' => 'Pasar Induk']
];

foreach ($nasabah_data as $nasabah) {
    $check = mysqli_query($conn, "SELECT id FROM nasabah WHERE kode_nasabah = '{$nasabah['kode']}'");
    if (mysqli_num_rows($check) == 0) {
        mysqli_query($conn, "INSERT INTO nasabah (cabang_id, kode_nasabah, nama, alamat, ktp, telp, jenis_usaha, lokasi_pasar) VALUES ($cabang_id, '{$nasabah['kode']}', '{$nasabah['nama']}', '{$nasabah['alamat']}', '{$nasabah['ktp']}', '{$nasabah['telp']}', '{$nasabah['jenis_usaha']}', '{$nasabah['lokasi_pasar']}')");
        echo "✓ Nasabah {$nasabah['nama']} created\n";
    } else {
        echo "✓ Nasabah {$nasabah['nama']} already exists\n";
    }
}

echo "\n";

// Phase 4: Create Pinjaman
echo "PHASE 4: Creating Pinjaman\n";

// Get nasabah IDs
$nasabah_result = mysqli_query($conn, "SELECT id, kode_nasabah FROM nasabah ORDER BY id");
$nasabah_ids = [];
while ($row = mysqli_fetch_assoc($nasabah_result)) {
    $nasabah_ids[$row['kode_nasabah']] = $row['id'];
}

$pinjaman_data = [
    ['kode' => 'PIN001', 'nasabah_kode' => 'NSB001', 'plafon' => 2000000, 'tenor' => 10, 'frekuensi' => 'harian', 'bunga' => 2, 'tujuan' => 'Modal usaha tambahan'],
    ['kode' => 'PIN002', 'nasabah_kode' => 'NSB002', 'plafon' => 5000000, 'tenor' => 12, 'frekuensi' => 'mingguan', 'bunga' => 1.5, 'tujuan' => 'Pembelian stok barang'],
    ['kode' => 'PIN003', 'nasabah_kode' => 'NSB003', 'plafon' => 10000000, 'tenor' => 24, 'frekuensi' => 'bulanan', 'bunga' => 1, 'tujuan' => 'Renovasi warung']
];

foreach ($pinjaman_data as $pinjaman) {
    $nasabah_id = $nasabah_ids[$pinjaman['nasabah_kode']];
    $total_bunga = $pinjaman['plafon'] * ($pinjaman['bunga'] / 100) * $pinjaman['tenor'];
    $total_pembayaran = $pinjaman['plafon'] + $total_bunga;
    $angsuran_pokok = $pinjaman['plafon'] / $pinjaman['tenor'];
    $angsuran_bunga = $total_bunga / $pinjaman['tenor'];
    $angsuran_total = $angsuran_pokok + $angsuran_bunga;
    
    $check = mysqli_query($conn, "SELECT id FROM pinjaman WHERE kode_pinjaman = '{$pinjaman['kode']}'");
    if (mysqli_num_rows($check) == 0) {
        mysqli_query($conn, "INSERT INTO pinjaman (cabang_id, kode_pinjaman, nasabah_id, plafon, tenor, frekuensi, bunga_per_bulan, total_bunga, total_pembayaran, angsuran_pokok, angsuran_bunga, angsuran_total, tanggal_akad, tanggal_jatuh_tempo, tujuan_pinjaman, status, petugas_id) VALUES ($cabang_id, '{$pinjaman['kode']}', $nasabah_id, {$pinjaman['plafon']}, {$pinjaman['tenor']}, '{$pinjaman['frekuensi']}', {$pinjaman['bunga']}, $total_bunga, $total_pembayaran, $angsuran_pokok, $angsuran_bunga, $angsuran_total, CURDATE(), DATE_ADD(CURDATE(), INTERVAL {$pinjaman['tenor']} MONTH), '{$pinjaman['tujuan']}', 'disetujui', $petugas_id)");
        
        // Create angsuran
        $pinjaman_id = mysqli_insert_id($conn);
        for ($i = 1; $i <= $pinjaman['tenor']; $i++) {
            mysqli_query($conn, "INSERT INTO angsuran (cabang_id, pinjaman_id, frekuensi, no_angsuran, pokok, bunga, total_angsuran, jatuh_tempo, status) VALUES ($cabang_id, $pinjaman_id, '{$pinjaman['frekuensi']}', $i, $angsuran_pokok, $angsuran_bunga, $angsuran_total, DATE_ADD(CURDATE(), INTERVAL $i MONTH), 'belum')");
        }
        
        echo "✓ Pinjaman {$pinjaman['kode']} created with {$pinjaman['tenor']} angsuran\n";
    } else {
        echo "✓ Pinjaman {$pinjaman['kode']} already exists\n";
    }
}

echo "\n";

// Verification
echo "=== Verification ===\n";
$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM users WHERE role != 'bos'");
echo "Users created: " . mysqli_fetch_assoc($result)['count'] . "\n";

$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM nasabah");
echo "Nasabah created: " . mysqli_fetch_assoc($result)['count'] . "\n";

$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM pinjaman");
echo "Pinjaman created: " . mysqli_fetch_assoc($result)['count'] . "\n";

$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM angsuran");
echo "Angsuran created: " . mysqli_fetch_assoc($result)['count'] . "\n";

echo "\n=== Simulation Complete ===\n";
echo "Test credentials:\n";
echo "  bos_simulasi / password123\n";
echo "  manager_pusat_sim / password123\n";
echo "  petugas1_sim / password123\n";
