-- Migration Script for New Features
-- Created: 2026-04-14
-- This script adds new tables and modifies existing structure

-- ============================================
-- 1. Add setting_bunga table for dynamic interest rates
-- ============================================
CREATE TABLE IF NOT EXISTS setting_bunga (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cabang_id INT NULL,
    jenis_pinjaman VARCHAR(50) NOT NULL,
    tenor_min INT NOT NULL DEFAULT 1,
    tenor_max INT NOT NULL DEFAULT 24,
    bunga_default DECIMAL(5,2) NOT NULL,
    bunga_min DECIMAL(5,2) NOT NULL,
    bunga_max DECIMAL(5,2) NOT NULL,
    faktor_risiko DECIMAL(5,2) DEFAULT 0,
    jaminan_adjustment DECIMAL(5,2) DEFAULT 0,
    status ENUM('aktif', 'nonaktif') DEFAULT 'aktif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (cabang_id) REFERENCES cabang(id) ON DELETE CASCADE
);

-- Insert default interest rate settings
INSERT INTO setting_bunga (jenis_pinjaman, tenor_min, tenor_max, bunga_default, bunga_min, bunga_max, faktor_risiko, jaminan_adjustment) VALUES
('harian', 1, 1, 2.5, 1.0, 5.0, 0.5, 1.0),
('mingguan', 1, 4, 2.0, 1.5, 3.0, 0.5, 0.5),
('bulanan', 1, 24, 2.5, 1.5, 4.0, 1.0, 0.5),
('multi_guna', 2, 24, 2.5, 1.5, 4.0, 1.0, -0.5);

-- ============================================
-- 2. Add kas_petugas table for cash tracking
-- ============================================
CREATE TABLE IF NOT EXISTS kas_petugas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cabang_id INT NOT NULL,
    petugas_id INT NOT NULL,
    tanggal DATE NOT NULL,
    saldo_awal DECIMAL(12,0) DEFAULT 0,
    total_terima DECIMAL(12,0) DEFAULT 0,
    total_disetor DECIMAL(12,0) DEFAULT 0,
    saldo_akhir DECIMAL(12,0) DEFAULT 0,
    status ENUM('lengkap', 'kurang', 'lebih') DEFAULT 'lengkap',
    catatan TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (cabang_id) REFERENCES cabang(id) ON DELETE CASCADE,
    FOREIGN KEY (petugas_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Create indexes for kas_petugas
CREATE INDEX idx_kas_petugas_cabang ON kas_petugas(cabang_id);
CREATE INDEX idx_kas_petugas_petugas ON kas_petugas(petugas_id);
CREATE INDEX idx_kas_petugas_tanggal ON kas_petugas(tanggal);

-- ============================================
-- 3. Add pengeluaran table for expense tracking
-- ============================================
CREATE TABLE IF NOT EXISTS pengeluaran (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cabang_id INT NOT NULL,
    kategori ENUM('gaji', 'lembur', 'bonus', 'operasional', 'belanja', 'lainnya') NOT NULL,
    sub_kategori VARCHAR(50),
    jumlah DECIMAL(12,0) NOT NULL,
    tanggal DATE NOT NULL,
    keterangan TEXT,
    bukti VARCHAR(255),
    petugas_id INT NULL,
    approved_by INT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (cabang_id) REFERENCES cabang(id) ON DELETE CASCADE,
    FOREIGN KEY (petugas_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Create indexes for pengeluaran
CREATE INDEX idx_pengeluaran_cabang ON pengeluaran(cabang_id);
CREATE INDEX idx_pengeluaran_kategori ON pengeluaran(kategori);
CREATE INDEX idx_pengeluaran_tanggal ON pengeluaran(tanggal);
CREATE INDEX idx_pengeluaran_status ON pengeluaran(status);

-- ============================================
-- 4. Modify pinjaman table for structured collateral
-- ============================================
-- Add new columns for structured collateral
ALTER TABLE pinjaman 
ADD COLUMN jaminan_tipe ENUM('tanpa', 'bpkb', 'shm', 'ajb', 'tabungan') DEFAULT 'tanpa' AFTER jaminan,
ADD COLUMN jaminan_nilai DECIMAL(12,0) NULL AFTER jaminan_tipe,
ADD COLUMN jaminan_dokumen VARCHAR(255) NULL AFTER jaminan_nilai;

-- Update existing records to have default values
UPDATE pinjaman SET jaminan_tipe = 'tanpa' WHERE jaminan_tipe IS NULL;

-- ============================================
-- 5. Update settings table with new parameters
-- ============================================
INSERT INTO settings (setting_key, setting_value, description) VALUES
('min_plafon_tanpa_jaminan', '1000000', 'Minimal plafon tanpa jaminan'),
('min_plafon_dengan_jaminan', '5000000', 'Minimal plafon dengan jaminan'),
('alert_kas_petugas_selisih', '100000', 'Alert jika selisih kas petugas melebihi nominal ini'),
('require_approval_pengeluaran', '500000', 'Minimal nominal pengeluaran yang butuh approval')
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value);

-- ============================================
-- 6. Create views for reporting
-- ============================================

-- View for daily cash report per branch
CREATE OR REPLACE VIEW v_laporan_kas_harian AS
SELECT 
    cabang_id,
    tanggal,
    SUM(saldo_awal) as total_saldo_awal,
    SUM(total_terima) as total_terima,
    SUM(total_disetor) as total_disetor,
    SUM(saldo_akhir) as total_saldo_akhir,
    COUNT(*) as jumlah_petugas
FROM kas_petugas
GROUP BY cabang_id, tanggal;

-- View for expense report by category
CREATE OR REPLACE VIEW v_laporan_pengeluaran_kategori AS
SELECT 
    cabang_id,
    kategori,
    COUNT(*) as jumlah_transaksi,
    SUM(jumlah) as total_pengeluaran
FROM pengeluaran
WHERE status = 'approved'
GROUP BY cabang_id, kategori;

-- View for interest rate summary
CREATE OR REPLACE VIEW v_ringkasan_bunga AS
SELECT 
    cabang_id,
    jenis_pinjaman,
    AVG(bunga_default) as rata_rata_bunga,
    MIN(bunga_min) as bunga_terendah,
    MAX(bunga_max) as bunga_tertinggi
FROM setting_bunga
WHERE status = 'aktif'
GROUP BY cabang_id, jenis_pinjaman;

-- ============================================
-- 7. Create triggers for automatic calculations
-- ============================================

DELIMITER //

-- Trigger to calculate saldo_akhir before insert
CREATE TRIGGER trg_kas_petugas_before_insert
BEFORE INSERT ON kas_petugas
FOR EACH ROW
BEGIN
    SET NEW.saldo_akhir = NEW.saldo_awal + NEW.total_terima - NEW.total_disetor;
    
    -- Determine status based on calculation
    IF NEW.total_disetor = 0 THEN
        SET NEW.status = 'kurang';
    ELSEIF NEW.saldo_akhir > 0 THEN
        SET NEW.status = 'kurang';
    ELSEIF NEW.saldo_akhir < 0 THEN
        SET NEW.status = 'lebih';
    ELSE
        SET NEW.status = 'lengkap';
    END IF;
END//

-- Trigger to calculate saldo_akhir before update
CREATE TRIGGER trg_kas_petugas_before_update
BEFORE UPDATE ON kas_petugas
FOR EACH ROW
BEGIN
    SET NEW.saldo_akhir = NEW.saldo_awal + NEW.total_terima - NEW.total_disetor;
    
    -- Determine status based on calculation
    IF NEW.total_disetor = 0 THEN
        SET NEW.status = 'kurang';
    ELSEIF NEW.saldo_akhir > 0 THEN
        SET NEW.status = 'kurang';
    ELSEIF NEW.saldo_akhir < 0 THEN
        SET NEW.status = 'lebih';
    ELSE
        SET NEW.status = 'lengkap';
    END IF;
END//

DELIMITER ;

-- ============================================
-- Migration Complete
-- ============================================
SELECT 'Migration completed successfully!' as message;
