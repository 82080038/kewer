-- Migration 033: Add platform_bank_accounts table
-- Date: 8 Mei 2026
-- Description: Table untuk menyimpan rekening bank platform untuk pembayaran

CREATE TABLE IF NOT EXISTS platform_bank_accounts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    bank_name VARCHAR(100) NOT NULL COMMENT 'Nama bank',
    account_number VARCHAR(50) NOT NULL COMMENT 'Nomor rekening',
    account_name VARCHAR(100) NOT NULL COMMENT 'Nama pemilik rekening',
    bank_code VARCHAR(10) COMMENT 'Kode bank (misal: 014 untuk BCA)',
    branch VARCHAR(100) COMMENT 'Nama cabang bank',
    is_primary BOOLEAN DEFAULT FALSE COMMENT 'Apakah rekening utama',
    is_active BOOLEAN DEFAULT TRUE COMMENT 'Status aktif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Tanggal dibuat',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Tanggal diupdate',
    INDEX idx_primary_active (is_primary, is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Rekening bank platform untuk pembayaran';

-- Insert default bank account
INSERT INTO platform_bank_accounts (bank_name, account_number, account_name, bank_code, branch, is_primary, is_active)
VALUES ('BCA', '1234567890', 'Kewer Platform', '014', 'Jakarta Pusat', 1, 1)
ON DUPLICATE KEY UPDATE account_name = VALUES(account_name);
