-- Migration Script for Family Risk Management
-- Created: 2026-04-14
-- This script adds family relationship tracking and risk assessment

-- ============================================
-- 1. Add family relationship fields to nasabah table (if not exists)
-- ============================================
-- Check and add each column individually
SET @dbname = DATABASE();
SET @tablename = 'nasabah';
SET @columnname = 'nama_ayah';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
    (table_schema = @dbname)
    AND (table_name = @tablename)
    AND (column_name = @columnname)
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE nasabah ADD COLUMN nama_ayah VARCHAR(100) NULL AFTER nama')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

SET @columnname = 'nama_ibu';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
    (table_schema = @dbname)
    AND (table_name = @tablename)
    AND (column_name = @columnname)
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE nasabah ADD COLUMN nama_ibu VARCHAR(100) NULL AFTER nama_ayah')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

SET @columnname = 'alamat_rumah';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
    (table_schema = @dbname)
    AND (table_name = @tablename)
    AND (column_name = @columnname)
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE nasabah ADD COLUMN alamat_rumah TEXT NULL AFTER alamat')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

SET @columnname = 'hubungan_keluarga';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
    (table_schema = @dbname)
    AND (table_name = @tablename)
    AND (column_name = @columnname)
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE nasabah ADD COLUMN hubungan_keluarga TEXT NULL AFTER alamat_rumah')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

SET @columnname = 'referensi_nasabah_id';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
    (table_schema = @dbname)
    AND (table_name = @tablename)
    AND (column_name = @columnname)
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE nasabah ADD COLUMN referensi_nasabah_id INT NULL AFTER foto_selfie')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

SET @columnname = 'skor_risiko_keluarga';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
    (table_schema = @dbname)
    AND (table_name = @tablename)
    AND (column_name = @columnname)
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE nasabah ADD COLUMN skor_risiko_keluarga INT DEFAULT 0 AFTER status')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

SET @columnname = 'catatan_risiko';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
    (table_schema = @dbname)
    AND (table_name = @tablename)
    AND (column_name = @columnname)
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE nasabah ADD COLUMN catatan_risiko TEXT NULL AFTER skor_risiko_keluarga')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Add index if not exists
SET @indexname = 'idx_referensi_nasabah';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
    WHERE
    (table_schema = @dbname)
    AND (table_name = @tablename)
    AND (index_name = @indexname)
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE nasabah ADD INDEX idx_referensi_nasabah (referensi_nasabah_id)')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- ============================================
-- 2. Create family_risk table for tracking problematic families
-- ============================================
CREATE TABLE IF NOT EXISTS family_risk (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cabang_id INT NOT NULL,
    nama_kepala_keluarga VARCHAR(100) NOT NULL,
    alamat_keluarga TEXT NOT NULL,
    tingkat_risiko ENUM('rendah', 'sedang', 'tinggi', 'sangat_tinggi') NOT NULL DEFAULT 'rendah',
    total_pinjaman_gagal INT DEFAULT 0,
    total_nasabah_bermasalah INT DEFAULT 0,
    tanggal_ditandai DATE NOT NULL,
    alasan TEXT,
    status ENUM('aktif', 'nonaktif') DEFAULT 'aktif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (cabang_id) REFERENCES cabang(id) ON DELETE CASCADE
);

-- Create indexes for family_risk (if not exists)
SET @indexname = 'idx_family_risk_cabang';
SET @tablename = 'family_risk';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
    WHERE
    (table_schema = @dbname)
    AND (table_name = @tablename)
    AND (index_name = @indexname)
  ) > 0,
  'SELECT 1',
  CONCAT('CREATE INDEX idx_family_risk_cabang ON family_risk(cabang_id)')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

SET @indexname = 'idx_family_risk_risiko';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
    WHERE
    (table_schema = @dbname)
    AND (table_name = @tablename)
    AND (index_name = @indexname)
  ) > 0,
  'SELECT 1',
  CONCAT('CREATE INDEX idx_family_risk_risiko ON family_risk(tingkat_risiko)')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

SET @indexname = 'idx_family_risk_alamat';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
    WHERE
    (table_schema = @dbname)
    AND (table_name = @tablename)
    AND (index_name = @indexname)
  ) > 0,
  'SELECT 1',
  CONCAT('CREATE INDEX idx_family_risk_alamat ON family_risk(alamat_keluarga(255))')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- ============================================
-- 3. Create nasabah_family_link table for family relationships
-- ============================================
CREATE TABLE IF NOT EXISTS nasabah_family_link (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nasabah_id INT NOT NULL,
    jenis_hubungan ENUM('ayah', 'ibu', 'suami', 'istri', 'anak', 'saudara', 'kerabat', 'lainnya') NOT NULL,
    nama_keluarga VARCHAR(100) NOT NULL,
    ktp_keluarga VARCHAR(16) NULL,
    alamat_keluarga TEXT NULL,
    telp_keluarga VARCHAR(15) NULL,
    catatan TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (nasabah_id) REFERENCES nasabah(id) ON DELETE CASCADE
);

-- Create indexes for nasabah_family_link (if not exists)
SET @tablename = 'nasabah_family_link';
SET @indexname = 'idx_family_link_nasabah';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
    WHERE
    (table_schema = @dbname)
    AND (table_name = @tablename)
    AND (index_name = @indexname)
  ) > 0,
  'SELECT 1',
  CONCAT('CREATE INDEX idx_family_link_nasabah ON nasabah_family_link(nasabah_id)')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

SET @indexname = 'idx_family_link_ktp';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
    WHERE
    (table_schema = @dbname)
    AND (table_name = @tablename)
    AND (index_name = @indexname)
  ) > 0,
  'SELECT 1',
  CONCAT('CREATE INDEX idx_family_link_ktp ON nasabah_family_link(ktp_keluarga)')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- ============================================
-- 4. Create loan_risk_log table for tracking risk events
-- ============================================
CREATE TABLE IF NOT EXISTS loan_risk_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cabang_id INT NOT NULL,
    nasabah_id INT NOT NULL,
    pinjaman_id INT NOT NULL,
    jenis_risiko ENUM('gagal_bayar', 'macet', 'keluarga_bermasalah', 'blacklist_keluarga', 'lainnya') NOT NULL,
    tingkat_risiko ENUM('rendah', 'sedang', 'tinggi', 'sangat_tinggi') NOT NULL,
    deskripsi TEXT,
    tindakan_diambil TEXT,
    tanggal_kejadian DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cabang_id) REFERENCES cabang(id) ON DELETE CASCADE,
    FOREIGN KEY (nasabah_id) REFERENCES nasabah(id) ON DELETE CASCADE,
    FOREIGN KEY (pinjaman_id) REFERENCES pinjaman(id) ON DELETE CASCADE
);

-- Create indexes for loan_risk_log (if not exists)
SET @tablename = 'loan_risk_log';
SET @indexname = 'idx_loan_risk_cabang';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
    WHERE
    (table_schema = @dbname)
    AND (table_name = @tablename)
    AND (index_name = @indexname)
  ) > 0,
  'SELECT 1',
  CONCAT('CREATE INDEX idx_loan_risk_cabang ON loan_risk_log(cabang_id)')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

SET @indexname = 'idx_loan_risk_nasabah';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
    WHERE
    (table_schema = @dbname)
    AND (table_name = @tablename)
    AND (index_name = @indexname)
  ) > 0,
  'SELECT 1',
  CONCAT('CREATE INDEX idx_loan_risk_nasabah ON loan_risk_log(nasabah_id)')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

SET @indexname = 'idx_loan_risk_jenis';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
    WHERE
    (table_schema = @dbname)
    AND (table_name = @tablename)
    AND (index_name = @indexname)
  ) > 0,
  'SELECT 1',
  CONCAT('CREATE INDEX idx_loan_risk_jenis ON loan_risk_log(jenis_risiko)')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

SET @indexname = 'idx_loan_risk_tanggal';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
    WHERE
    (table_schema = @dbname)
    AND (table_name = @tablename)
    AND (index_name = @indexname)
  ) > 0,
  'SELECT 1',
  CONCAT('CREATE INDEX idx_loan_risk_tanggal ON loan_risk_log(tanggal_kejadian)')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- ============================================
-- 5. Create view for family risk summary
-- ============================================
DROP VIEW IF EXISTS v_risiko_keluarga;
CREATE VIEW v_risiko_keluarga AS
SELECT 
    n.id as nasabah_id,
    n.nama,
    n.alamat,
    n.ktp,
    n.status as status_nasabah,
    n.skor_risiko_keluarga,
    fr.tingkat_risiko,
    fr.nama_kepala_keluarga,
    fr.alamat_keluarga,
    fr.total_pinjaman_gagal,
    fr.total_nasabah_bermasalah,
    COUNT(DISTINCT lrl.id) as total_log_risiko
FROM nasabah n
LEFT JOIN family_risk fr ON n.alamat LIKE CONCAT('%', SUBSTRING_INDEX(fr.alamat_keluarga, ' ', 3), '%')
LEFT JOIN loan_risk_log lrl ON n.id = lrl.nasabah_id
WHERE fr.status = 'aktif'
GROUP BY n.id, n.nama, n.alamat, n.ktp, n.status, n.skor_risiko_keluarga, fr.tingkat_risiko, fr.nama_kepala_keluarga, fr.alamat_keluarga, fr.total_pinjaman_gagal, fr.total_nasabah_bermasalah;

-- ============================================
-- 6. Create trigger to auto-update family risk on loan default
-- ============================================
DROP TRIGGER IF EXISTS trg_auto_update_family_risk;
DELIMITER //

CREATE TRIGGER trg_auto_update_family_risk
AFTER UPDATE ON pinjaman
FOR EACH ROW
BEGIN
    DECLARE v_nasabah_id INT;
    DECLARE v_alamat TEXT;
    DECLARE v_cabang_id INT;
    DECLARE v_family_risk_id INT;
    DECLARE v_nama_nasabah VARCHAR(100);
    
    -- When loan status changes to 'macet' (defaulted)
    IF NEW.status = 'macet' AND OLD.status != 'macet' THEN
        -- Get nasabah information
        SELECT nasabah_id, cabang_id INTO v_nasabah_id, v_cabang_id
        FROM pinjaman WHERE id = NEW.id;
        
        SELECT alamat, nama INTO v_alamat, v_nama_nasabah
        FROM nasabah WHERE id = v_nasabah_id;
        
        -- Log the risk event
        INSERT INTO loan_risk_log (cabang_id, nasabah_id, pinjaman_id, jenis_risiko, tingkat_risiko, deskripsi, tindakan_diambil, tanggal_kejadian)
        VALUES (v_cabang_id, v_nasabah_id, NEW.id, 'gagal_bayar', 'tinggi', 'Pinjaman gagal bayar', 'Auto-tagged as family risk', CURDATE());
        
        -- Update nasabah risk score
        UPDATE nasabah 
        SET skor_risiko_keluarga = skor_risiko_keluarga + 10
        WHERE id = v_nasabah_id;
        
        -- Check if family_risk record exists for this address
        SELECT id INTO v_family_risk_id FROM family_risk WHERE alamat_keluarga LIKE CONCAT('%', SUBSTRING_INDEX(v_alamat, ' ', 3), '%') AND cabang_id = v_cabang_id LIMIT 1;
        
        IF v_family_risk_id IS NULL THEN
            -- Create new family_risk record
            INSERT INTO family_risk (cabang_id, nama_kepala_keluarga, alamat_keluarga, tingkat_risiko, total_pinjaman_gagal, total_nasabah_bermasalah, tanggal_ditandai, alasan)
            VALUES (v_cabang_id, v_nama_nasabah, v_alamat, 'tinggi', 1, 1, CURDATE(), 'Pinjaman gagal bayar');
        ELSE
            -- Update existing family_risk record
            UPDATE family_risk 
            SET total_pinjaman_gagal = total_pinjaman_gagal + 1,
                total_nasabah_bermasalah = total_nasabah_bermasalah + 1,
                tingkat_risiko = CASE 
                    WHEN total_pinjaman_gagal + 1 >= 3 THEN 'sangat_tinggi'
                    WHEN total_pinjaman_gagal + 1 = 2 THEN 'tinggi'
                    ELSE 'sedang'
                END,
                updated_at = NOW()
            WHERE id = v_family_risk_id;
        END IF;
    END IF;
END//

DELIMITER ;

-- ============================================
-- 7. Update settings with risk management parameters
-- ============================================
INSERT INTO settings (setting_key, setting_value, description) VALUES
('auto_blacklist_family', '3', 'Otomatis blacklist keluarga jika ada 3+ nasabah bermasalah'),
('family_risk_threshold', '20', 'Threshold skor risiko keluarga untuk peringatan'),
('require_family_verification', '500000', 'Minimal plafon untuk verifikasi keluarga')
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value);

-- ============================================
-- Migration Complete
-- ============================================
SELECT 'Family risk management migration completed successfully!' as message;
