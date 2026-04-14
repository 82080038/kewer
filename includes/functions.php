<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';

// Generate unique code
function generateKode($prefix, $table, $field) {
    $result = query("SELECT MAX(CAST(SUBSTRING($field, 4) AS UNSIGNED)) as max_num FROM $table WHERE $field LIKE ?", ["$prefix%"]);
    $next_num = ($result[0]['max_num'] ?? 0) + 1;
    return $prefix . str_pad($next_num, 3, '0', STR_PAD_LEFT);
}

// Calculate loan interest (Flat Rate)
function calculateLoan($plafon, $tenor, $bunga_per_bulan) {
    $total_bunga = $plafon * ($bunga_per_bulan / 100) * $tenor;
    $total_pembayaran = $plafon + $total_bunga;
    $angsuran_pokok = $plafon / $tenor;
    $angsuran_bunga = $total_bunga / $tenor;
    $angsuran_total = $angsuran_pokok + $angsuran_bunga;
    
    return [
        'total_bunga' => $total_bunga,
        'total_pembayaran' => $total_pembayaran,
        'angsuran_pokok' => $angsuran_pokok,
        'angsuran_bunga' => $angsuran_bunga,
        'angsuran_total' => $angsuran_total
    ];
}

// Create loan schedule
function createLoanSchedule($pinjaman_id, $plafon, $tenor, $bunga_per_bulan, $tanggal_akad) {
    $calc = calculateLoan($plafon, $tenor, $bunga_per_bulan);
    $cabang_id = getCurrentCabang();
    
    for ($i = 1; $i <= $tenor; $i++) {
        $jatuh_tempo = date('Y-m-d', strtotime("+$i month", strtotime($tanggal_akad)));
        
        query("INSERT INTO angsuran (cabang_id, pinjaman_id, no_angsuran, jatuh_tempo, pokok, bunga, total_angsuran) VALUES (?, ?, ?, ?, ?, ?, ?)", [
            $cabang_id,
            $pinjaman_id,
            $i,
            $jatuh_tempo,
            $calc['angsuran_pokok'],
            $calc['angsuran_bunga'],
            $calc['angsuran_total']
        ]);
    }
}

// Check late payments
function checkLatePayments() {
    $cabang_id = getCurrentCabang();
    
    // Update status to 'telat' for payments past due date
    query("UPDATE angsuran SET status = 'telat' WHERE cabang_id = ? AND status = 'belum' AND jatuh_tempo < CURDATE()", [$cabang_id]);
    
    // Get list of late payments
    return query("SELECT a.*, n.nama, n.telp, p.kode_pinjaman 
                  FROM angsuran a 
                  JOIN pinjaman p ON a.pinjaman_id = p.id 
                  JOIN nasabah n ON p.nasabah_id = n.id 
                  WHERE a.cabang_id = ? AND a.status = 'telat' 
                  ORDER BY a.jatuh_tempo", [$cabang_id]);
}

// Format currency
function formatRupiah($amount) {
    return 'Rp ' . number_format($amount, 0, ',', '.');
}

// Format date
function formatDate($date, $format = 'd M Y') {
    return date($format, strtotime($date));
}

// Validate KTP
function validateKTP($ktp) {
    return preg_match('/^[0-9]{16}$/', $ktp);
}

// Validate phone
function validatePhone($phone) {
    return preg_match('/^08[0-9]{9,12}$/', $phone);
}

// Send WhatsApp notification (placeholder)
function sendWhatsApp($phone, $message) {
    // Implement WhatsApp API integration here
    // For now, just log the message
    error_log("WA to $phone: $message");
    return true;
}
?>
