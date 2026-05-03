<?php
/**
 * WhatsApp Notifikasi — Kewer v2.3.0
 * Provider: Fonnte (fonnte.com) — mudah dan murah untuk Indonesia
 *
 * Konfigurasi via environment atau config/env.php:
 *   WA_TOKEN = <fonnte device token>
 *   WA_ENABLED = true
 *
 * Dokumentasi Fonnte: https://fonnte.com/docs
 */

define('WA_API_URL', 'https://api.fonnte.com/send');

/**
 * Kirim pesan WA ke satu nomor via Fonnte.
 */
function kirimWA(string $nomor, string $pesan, ?int $nasabah_id = null, ?int $petugas_id = null, string $tipe = 'pengingat'): array {
    $token = getenv('WA_TOKEN') ?: (defined('WA_TOKEN') ? WA_TOKEN : '');
    if (!$token) {
        return ['success' => false, 'error' => 'WA_TOKEN tidak dikonfigurasi'];
    }

    // Format nomor: hapus leading 0, tambah 62
    $nomor = preg_replace('/[^0-9]/', '', $nomor);
    if (substr($nomor, 0, 1) === '0') {
        $nomor = '62' . substr($nomor, 1);
    }

    $payload = ['target' => $nomor, 'message' => $pesan, 'delay' => '2', 'schedule' => '0'];

    $ch = curl_init(WA_API_URL);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query($payload),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 15,
        CURLOPT_HTTPHEADER => ["Authorization: $token"],
    ]);
    $resp    = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $success = ($httpCode === 200 && $resp !== false);
    $respBody = $resp ?: '';

    // Log ke wa_log
    query(
        "INSERT INTO wa_log (nasabah_id, petugas_id, tipe, nomor_wa, pesan, status, provider, response_code, response_body, sent_at)
         VALUES (?, ?, ?, ?, ?, ?, 'fonnte', ?, ?, ?)",
        [$nasabah_id, $petugas_id, $tipe, $nomor, $pesan,
         $success ? 'sent' : 'failed', $httpCode, $respBody,
         $success ? date('Y-m-d H:i:s') : null]
    );

    return ['success' => $success, 'nomor' => $nomor, 'http_code' => $httpCode];
}

/**
 * Template pesan WA: pengingat angsuran jatuh tempo.
 */
function templateWaPengingat(array $nasabah, array $pinjaman, array $angsuran, string $tipe = 'H-1'): string {
    $tgl = date('d/m/Y', strtotime($angsuran['jatuh_tempo']));
    $angsuran_fmt = 'Rp ' . number_format($angsuran['total_bayar'], 0, ',', '.');

    if ($tipe === 'H-0') {
        return "Yth. *{$nasabah['nama']}*,\n\nAngsuran Anda *hari ini* ({$tgl}) jatuh tempo.\n\nDetail:\n• Kode Pinjaman: {$pinjaman['kode_pinjaman']}\n• Angsuran ke-{$angsuran['ke']}: *{$angsuran_fmt}*\n\nMohon segera lakukan pembayaran kepada petugas kami.\n\nTerima kasih — *Kewer Koperasi*";
    } elseif ($tipe === 'TELAT') {
        $hari = $angsuran['hari_telat'] ?? 1;
        return "Yth. *{$nasabah['nama']}*,\n\nAngsuran Anda *telah melewati jatuh tempo* sejak {$tgl} ({$hari} hari lalu).\n\nSegera hubungi petugas kami untuk menghindari denda tambahan.\n\nTerima kasih — *Kewer Koperasi*";
    }

    // H-1
    return "Yth. *{$nasabah['nama']}*,\n\nPengingat: Angsuran Anda jatuh tempo *besok* ({$tgl}).\n\nDetail:\n• Kode Pinjaman: {$pinjaman['kode_pinjaman']}\n• Angsuran ke-{$angsuran['ke']}: *{$angsuran_fmt}*\n\nSiapkan pembayaran Anda.\n\nTerima kasih — *Kewer Koperasi*";
}

/**
 * Template pesan WA: konfirmasi pembayaran berhasil.
 */
function templateWaKonfirmasiBayar(array $nasabah, array $pinjaman, array $pembayaran): string {
    $tgl  = date('d/m/Y', strtotime($pembayaran['tanggal_bayar']));
    $jml  = 'Rp ' . number_format($pembayaran['total_bayar'], 0, ',', '.');
    return "Yth. *{$nasabah['nama']}*,\n\nPembayaran Anda telah diterima ✅\n\nDetail:\n• Kode: {$pembayaran['kode_pembayaran']}\n• Jumlah: *{$jml}*\n• Tanggal: {$tgl}\n• Pinjaman: {$pinjaman['kode_pinjaman']}\n\nTerima kasih atas pembayaran tepat waktu Anda.\n\n*Kewer Koperasi*";
}

/**
 * Batch kirim WA pengingat (dipanggil dari cron).
 * Kirim ke nasabah dengan angsuran jatuh tempo H-1 dan H-0.
 */
function kirimWaPengingatBatch(): array {
    $sent   = 0;
    $failed = 0;
    $besok  = date('Y-m-d', strtotime('+1 day'));
    $hari_ini = date('Y-m-d');

    // Angsuran H-1 dan H-0 yang belum lunas
    $angsuran_list = query(
        "SELECT a.*, p.kode_pinjaman, p.nasabah_id, n.nama as nama_nasabah, n.telepon,
                DATEDIFF(CURDATE(), a.jatuh_tempo) as hari_telat
         FROM angsuran a
         JOIN pinjaman p ON a.pinjaman_id = p.id
         JOIN nasabah n ON p.nasabah_id = n.id
         WHERE a.status != 'lunas'
           AND a.jatuh_tempo IN (?, ?)
           AND n.telepon IS NOT NULL AND n.telepon != ''
           AND p.status = 'aktif'",
        [$hari_ini, $besok]
    );

    if (!$angsuran_list) return ['sent' => 0, 'failed' => 0];

    foreach ($angsuran_list as $row) {
        $tipe = ($row['jatuh_tempo'] === $hari_ini) ? 'H-0' : 'H-1';
        $nasabah  = ['nama' => $row['nama_nasabah']];
        $pinjaman = ['kode_pinjaman' => $row['kode_pinjaman']];
        $angsuran_data = ['ke' => $row['ke'] ?? '-', 'jatuh_tempo' => $row['jatuh_tempo'],
                          'total_bayar' => $row['total_bayar'], 'hari_telat' => $row['hari_telat']];

        $pesan = templateWaPengingat($nasabah, $pinjaman, $angsuran_data, $tipe);
        $r = kirimWA($row['telepon'], $pesan, $row['nasabah_id'], null, 'jatuh_tempo');

        if ($r['success']) $sent++; else $failed++;

        // Hindari rate-limit Fonnte: jeda 1 detik tiap 10 pesan
        if (($sent + $failed) % 10 === 0) sleep(1);
    }

    return ['sent' => $sent, 'failed' => $failed];
}
