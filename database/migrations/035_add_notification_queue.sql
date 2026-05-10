-- Migration 035: Add Notification Queue for WA Rate Limiting
-- Date: 2026-05-10
-- Description: Queue system untuk mengelola rate limiting WA notifikasi

CREATE TABLE IF NOT EXISTS notification_queue (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nasabah_id INT NULL COMMENT 'ID nasabah (opsional)',
    petugas_id INT NULL COMMENT 'ID petugas yang mengirim (opsional)',
    tipe ENUM('jatuh_tempo', 'konfirmasi_bayar', 'blacklist', 'approval_pinjaman', 'tagihan', 'lainnya') NOT NULL DEFAULT 'lainnya',
    nomor_wa VARCHAR(20) NOT NULL COMMENT 'Nomor WhatsApp tujuan',
    pesan TEXT NOT NULL COMMENT 'Isi pesan',
    priority TINYINT(1) NOT NULL DEFAULT 5 COMMENT '1=highest, 10=lowest',
    status ENUM('pending', 'processing', 'sent', 'failed', 'cancelled') NOT NULL DEFAULT 'pending',
    provider VARCHAR(20) NOT NULL DEFAULT 'fonnte' COMMENT 'Fonnte, Twilio, dll',
    retry_count INT NOT NULL DEFAULT 0 COMMENT 'Jumlah retry gagal',
    max_retry INT NOT NULL DEFAULT 3 COMMENT 'Max retry sebelum放弃',
    scheduled_at TIMESTAMP NULL COMMENT 'Waktu terjadwal (jika delayed)',
    sent_at TIMESTAMP NULL COMMENT 'Waktu terkirim',
    response_code INT NULL COMMENT 'HTTP response code',
    response_body TEXT NULL COMMENT 'Response body dari provider',
    error_message TEXT NULL COMMENT 'Error message jika gagal',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_status (status),
    KEY idx_priority (priority),
    KEY idx_scheduled (scheduled_at),
    KEY idx_tipe (tipe)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
COMMENT='Queue untuk notifikasi WA dengan rate limiting';

-- Index untuk performance
ALTER TABLE notification_queue ADD INDEX idx_status_priority (status, priority, created_at);
