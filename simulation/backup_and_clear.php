<?php
// Direct XAMPP MySQL connection
$mysqli = new mysqli('localhost', 'root', 'root', 'kewer', 3306, '/opt/lampp/var/mysql/mysql.sock');
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Backup database
$backup_file = __DIR__ . '/backup_' . date('Y-m-d_H-i-s') . '.sql';
$command = "/opt/lampp/bin/mysqldump -u root -proot -S /opt/lampp/var/mysql/mysql.sock kewer > " . $backup_file;
system($command, $return_var);

if ($return_var === 0) {
    echo "Database backed up to: $backup_file\n";
} else {
    echo "Backup failed. Using XAMPP mysqldump...\n";
    $command = "/opt/lampp/bin/mysqldump -u root -proot kewer > " . $backup_file;
    system($command, $return_var);
    if ($return_var === 0) {
        echo "Database backed up to: $backup_file\n";
    } else {
        echo "Backup failed completely.\n";
        exit(1);
    }
}

// Tables to clear for simulation (order matters for foreign keys)
$tables_to_clear = [
    'angsuran',
    'pembayaran',
    'pinjaman',
    'nasabah',
    'field_officer_activities',
    'kas_petugas',
    'kas_petugas_setoran',
    'daily_cash_reconciliation',
    'kas_bon',
    'kas_bon_potongan',
    'pengeluaran',
    'family_risk',
    'nasabah_family_link'
];

// Clear tables
foreach ($tables_to_clear as $table) {
    $check = $mysqli->query("SHOW TABLES LIKE '$table'");
    if ($check && $check->num_rows > 0) {
        $result = $mysqli->query("DELETE FROM $table");
        echo "Cleared table: $table\n";
    } else {
        echo "Table not found: $table (skipping)\n";
    }
}

// Reset auto-increment
foreach ($tables_to_clear as $table) {
    $check = $mysqli->query("SHOW TABLES LIKE '$table'");
    if ($check && $check->num_rows > 0) {
        $mysqli->query("ALTER TABLE $table AUTO_INCREMENT = 1");
        echo "Reset auto-increment for: $table\n";
    }
}

$mysqli->close();
echo "\nDatabase cleared and ready for simulation.\n";
?>
