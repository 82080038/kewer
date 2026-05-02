<?php
require_once 'config/path.php';
require_once BASE_PATH . '/includes/functions.php';

try {
    // Delete all users with role bos from users table
    $result = query("DELETE FROM users WHERE role = 'bos'");
    
    // Also delete bos registrations
    $result2 = query("DELETE FROM bos_registrations");
    
    echo "✅ Berhasil menghapus seluruh user dengan role bos dari database\n";
    echo "User bos dan pendaftaran bos telah dihapus\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
