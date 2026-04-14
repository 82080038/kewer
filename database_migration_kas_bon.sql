-- Migration Script for Kas Bon (Employee Cash Advance)
-- Created: 2026-04-14
-- This script adds kas bon management for employees

-- ============================================
-- 1. Create kas_bon table for employee cash advances
-- ============================================
DROP VIEW IF EXISTS v_karyawan_kasbon;
DROP VIEW IF EXISTS v_kasbon_summary;
DROP TABLE IF EXISTS kas_bon_potongan;
DROP TABLE IF EXISTS kas_bon;

CREATE TABLE kas_bon (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cabang_id INT NOT NULL,
    karyawan_id INT NOT NULL,
    kode_kasbon VARCHAR(20) UNIQUE NOT NULL,
    tanggal_pengajuan DATE NOT NULL,
    tanggal_pemberian DATE NULL,
    tanggal_potong DATE NULL,
    jumlah DECIMAL(12,0) NOT NULL,
    tenor_bulan INT DEFAULT 1,
    potongan_per_bulan DECIMAL(12,0) DEFAULT 0,
    potongan_ke INT DEFAULT 0,
    sisa_bon DECIMAL(12,0) DEFAULT 0,
    tujuan TEXT,
    status ENUM('pengajuan', 'disetujui', 'diberikan', 'dipotong', 'selesai', 'ditolak') DEFAULT 'pengajuan',
    catatan TEXT,
    disetujui_oleh INT NULL,
    tanggal_disetujui DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (cabang_id) REFERENCES cabang(id) ON DELETE CASCADE,
    FOREIGN KEY (karyawan_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (disetujui_oleh) REFERENCES users(id) ON DELETE SET NULL
);

-- Create indexes for kas_bon (if not exists)
SET @tablename = 'kas_bon';
SET @indexname = 'idx_kasbon_cabang';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
    WHERE
    (table_schema = @dbname)
    AND (table_name = @tablename)
    AND (index_name = @indexname)
  ) > 0,
  'SELECT 1',
  CONCAT('CREATE INDEX idx_kasbon_cabang ON kas_bon(cabang_id)')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

SET @indexname = 'idx_kasbon_karyawan';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
    WHERE
    (table_schema = @dbname)
    AND (table_name = @tablename)
    AND (index_name = @indexname)
  ) > 0,
  'SELECT 1',
  CONCAT('CREATE INDEX idx_kasbon_karyawan ON kas_bon(karyawan_id)')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

SET @indexname = 'idx_kasbon_status';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
    WHERE
    (table_schema = @dbname)
    AND (table_name = @tablename)
    AND (index_name = @indexname)
  ) > 0,
  'SELECT 1',
  CONCAT('CREATE INDEX idx_kasbon_status ON kas_bon(status)')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

SET @indexname = 'idx_kasbon_tanggal';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
    WHERE
    (table_schema = @dbname)
    AND (table_name = @tablename)
    AND (index_name = @indexname)
  ) > 0,
  'SELECT 1',
  CONCAT('CREATE INDEX idx_kasbon_tanggal ON kas_bon(tanggal_pengajuan)')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- ============================================
-- 2. Create kas_bon_potongan table for tracking deductions
-- ============================================
CREATE TABLE IF NOT EXISTS kas_bon_potongan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kas_bon_id INT NOT NULL,
    bulan_potong VARCHAR(7) NOT NULL, -- Format: YYYY-MM
    jumlah_potong DECIMAL(12,0) NOT NULL,
    tanggal_potong DATE NOT NULL,
    potong_oleh INT NULL,
    catatan TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (kas_bon_id) REFERENCES kas_bon(id) ON DELETE CASCADE,
    FOREIGN KEY (potong_oleh) REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE KEY unique_potongan (kas_bon_id, bulan_potong)
);

-- Create indexes for kas_bon_potongan (if not exists)
SET @tablename = 'kas_bon_potongan';
SET @indexname = 'idx_potongan_kasbon';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
    WHERE
    (table_schema = @dbname)
    AND (table_name = @tablename)
    AND (index_name = @indexname)
  ) > 0,
  'SELECT 1',
  CONCAT('CREATE INDEX idx_potongan_kasbon ON kas_bon_potongan(kas_bon_id)')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

SET @indexname = 'idx_potongan_bulan';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
    WHERE
    (table_schema = @dbname)
    AND (table_name = @tablename)
    AND (index_name = @indexname)
  ) > 0,
  'SELECT 1',
  CONCAT('CREATE INDEX idx_potongan_bulan ON kas_bon_potongan(bulan_potong)')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- ============================================
-- 3. Update users table to add kas bon limit
-- ============================================
SET @dbname = DATABASE();
SET @tablename = 'users';
SET @columnname = 'limit_kasbon';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
    (table_schema = @dbname)
    AND (table_name = @tablename)
    AND (column_name = @columnname)
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE users ADD COLUMN limit_kasbon DECIMAL(12,0) DEFAULT 0')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- ============================================
-- 4. Create view for kas bon summary
-- ============================================
CREATE OR REPLACE VIEW v_kasbon_summary AS
SELECT 
    kb.id,
    kb.kode_kasbon,
    kb.cabang_id,
    kb.karyawan_id,
    u.nama as nama_karyawan,
    kb.tanggal_pengajuan,
    kb.tanggal_pemberian,
    kb.tanggal_potong,
    kb.jumlah,
    kb.tenor_bulan,
    kb.potongan_per_bulan,
    kb.sisa_bon,
    kb.potongan_ke,
    kb.status,
    kb.tujuan,
    kb.catatan,
    kb.disetujui_oleh,
    kb.tanggal_disetujui,
    (SELECT COUNT(*) FROM kas_bon_potongan kbp WHERE kbp.kas_bon_id = kb.id) as jumlah_potongan,
    (SELECT SUM(kbp.jumlah_potong) FROM kas_bon_potongan kbp WHERE kbp.kas_bon_id = kb.id) as total_dipotong
FROM kas_bon kb
JOIN users u ON kb.karyawan_id = u.id
WHERE kb.status != 'deleted'
ORDER BY kb.tanggal_pengajuan DESC;

-- ============================================
-- 5. Create view for employee kas bon balance
-- ============================================
CREATE OR REPLACE VIEW v_karyawan_kasbon AS
SELECT 
    u.id as karyawan_id,
    u.nama as nama_karyawan,
    u.cabang_id,
    u.limit_kasbon,
    COUNT(kb.id) as total_kasbon,
    SUM(CASE WHEN kb.status IN ('disetujui', 'diberikan', 'dipotong', 'selesai') THEN kb.jumlah ELSE 0 END) as total_dipinjam,
    SUM(CASE WHEN kb.status IN ('dipotong', 'selesai') THEN kb.sisa_bon ELSE 0 END) as total_sisa,
    SUM(CASE WHEN kb.status = 'selesai' THEN kb.jumlah ELSE 0 END) as total_lunas
FROM users u
LEFT JOIN kas_bon kb ON u.id = kb.karyawan_id AND kb.status != 'deleted'
WHERE u.role = 'karyawan' OR u.role = 'petugas'
GROUP BY u.id, u.nama, u.cabang_id, u.limit_kasbon;

-- ============================================
-- 6. Create trigger to auto-calculate potongan_ke
-- ============================================
DELIMITER //

CREATE TRIGGER trg_kasbon_before_insert
BEFORE INSERT ON kas_bon
FOR EACH ROW
BEGIN
    SET NEW.potongan_ke = 0;
END//

DELIMITER ;

-- ============================================
-- 7. Update settings with kas bon parameters
-- ============================================
INSERT INTO settings (setting_key, setting_value, description) VALUES
('max_kasbon_percentage', '50', 'Maksimal kasbon dalam persentase dari gaji (default 50%)'),
('min_gaji_kasbon', '1000000', 'Minimal gaji untuk bisa kasbon'),
('auto_approve_kasbon', '0', 'Otomatis approve kasbon jika <= limit'),
('kasbon_require_approval', '500000', 'Minimal kasbon yang butuh approval')
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value);

-- ============================================
-- Migration Complete
-- ============================================
SELECT 'Kas bon management migration completed successfully!' as message;
