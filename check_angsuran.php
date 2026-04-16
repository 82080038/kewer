<?php
require_once 'config/path.php';
require_once 'includes/functions.php';

// Get latest pinjaman
$pinjaman = query("SELECT * FROM pinjaman ORDER BY id DESC LIMIT 1");
if ($pinjaman) {
    $p = $pinjaman[0];
    echo "Latest Pinjaman:\n";
    echo "ID: {$p['id']}\n";
    echo "Kode: {$p['kode_pinjaman']}\n";
    echo "Tenor: {$p['tenor']}\n";
    echo "Status: {$p['status']}\n\n";
    
    // Get angsuran for this pinjaman
    $angsuran = query("SELECT * FROM angsuran WHERE pinjaman_id = ? ORDER BY no_angsuran", [$p['id']]);
    echo "Jumlah Angsuran: " . count($angsuran) . "\n";
    
    if ($angsuran) {
        foreach ($angsuran as $a) {
            echo "- No: {$a['no_angsuran']}, Jatuh Tempo: {$a['jatuh_tempo']}, Total: {$a['total_angsuran']}, Status: {$a['status']}\n";
        }
    }
} else {
    echo "No pinjaman found\n";
}
?>
