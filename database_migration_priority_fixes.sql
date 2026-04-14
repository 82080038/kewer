-- Migration Script for Priority Fixes
-- Created: 2026-04-14
-- This script implements Priority 1 fixes from analysis reports

-- ============================================
-- 1. Create audit_log table for fraud prevention
-- ============================================
CREATE TABLE IF NOT EXISTS audit_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    action VARCHAR(50) NOT NULL,
    table_name VARCHAR(50) NOT NULL,
    record_id INT NULL,
    old_value TEXT NULL,
    new_value TEXT NULL,
    ip_address VARCHAR(45) NULL,
    user_agent VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

CREATE INDEX idx_audit_log_user ON audit_log(user_id);
CREATE INDEX idx_audit_log_action ON audit_log(action);
CREATE INDEX idx_audit_log_table ON audit_log(table_name);
CREATE INDEX idx_audit_log_created ON audit_log(created_at);

-- ============================================
-- 2. Create reference tables for ENUMs (normalization)
-- ============================================

-- Roles reference table
CREATE TABLE IF NOT EXISTS ref_roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role_kode VARCHAR(20) UNIQUE NOT NULL,
    role_nama VARCHAR(50) NOT NULL,
    deskripsi TEXT,
    permissions JSON NULL,
    urutan_tampil INT DEFAULT 0,
    status ENUM('aktif', 'nonaktif') DEFAULT 'aktif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

INSERT INTO ref_roles (role_kode, role_nama, deskripsi, urutan_tampil) VALUES
('superadmin', 'Super Administrator', 'Full access to all features', 1),
('admin', 'Administrator', 'Manage branch operations', 2),
('petugas', 'Petugas Lapangan', 'Field operations and collections', 3),
('karyawan', 'Karyawan', 'Employee access', 4);

-- Status pinjaman reference table
CREATE TABLE IF NOT EXISTS ref_status_pinjaman (
    id INT AUTO_INCREMENT PRIMARY KEY,
    status_kode VARCHAR(20) UNIQUE NOT NULL,
    status_nama VARCHAR(50) NOT NULL,
    deskripsi TEXT,
    urutan_tampil INT DEFAULT 0,
    status ENUM('aktif', 'nonaktif') DEFAULT 'aktif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

INSERT INTO ref_status_pinjaman (status_kode, status_nama, deskripsi, urutan_tampil) VALUES
('pengajuan', 'Pengajuan', 'Loan application submitted', 1),
('disetujui', 'Disetujui', 'Loan approved', 2),
('aktif', 'Aktif', 'Loan is active', 3),
('lunas', 'Lunas', 'Loan fully paid', 4),
('ditolak', 'Ditolak', 'Loan rejected', 5),
('macet', 'Macet', 'Loan defaulted', 6);

-- Metode pembayaran reference table
CREATE TABLE IF NOT EXISTS ref_metode_pembayaran (
    id INT AUTO_INCREMENT PRIMARY KEY,
    metode_kode VARCHAR(20) UNIQUE NOT NULL,
    metode_nama VARCHAR(50) NOT NULL,
    deskripsi TEXT,
    status ENUM('aktif', 'nonaktif') DEFAULT 'aktif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

INSERT INTO ref_metode_pembayaran (metode_kode, metode_nama, deskripsi) VALUES
('tunai', 'Tunai', 'Cash payment'),
('transfer', 'Transfer Bank', 'Bank transfer'),
('digital', 'E-Wallet/Digital', 'Digital payment');

-- Kategori pengeluaran reference table
CREATE TABLE IF NOT EXISTS ref_kategori_pengeluaran (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kategori_kode VARCHAR(20) UNIQUE NOT NULL,
    kategori_nama VARCHAR(50) NOT NULL,
    deskripsi TEXT,
    status ENUM('aktif', 'nonaktif') DEFAULT 'aktif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

INSERT INTO ref_kategori_pengeluaran (kategori_kode, kategori_nama, deskripsi) VALUES
('gaji', 'Gaji', 'Employee salaries'),
('lembur', 'Lembur', 'Overtime pay'),
('bonus', 'Bonus', 'Performance bonuses'),
('operasional', 'Operasional', 'Operational expenses'),
('belanja', 'Belanja', 'Purchases'),
('lainnya', 'Lainnya', 'Other expenses');

-- Jaminan tipe reference table
CREATE TABLE IF NOT EXISTS ref_jaminan_tipe (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tipe_kode VARCHAR(20) UNIQUE NOT NULL,
    tipe_nama VARCHAR(50) NOT NULL,
    deskripsi TEXT,
    status ENUM('aktif', 'nonaktif') DEFAULT 'aktif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

INSERT INTO ref_jaminan_tipe (tipe_kode, tipe_nama, deskripsi) VALUES
('tanpa', 'Tanpa Jaminan', 'No collateral'),
('bpkb', 'BPKB Kendaraan', 'Vehicle registration'),
('shm', 'SHM Tanah', 'Land certificate'),
('ajb', 'AJB', 'Sale deed'),
('tabungan', 'Tabungan', 'Savings collateral');

-- Jenis usaha reference table
CREATE TABLE IF NOT EXISTS ref_jenis_usaha (
    id INT AUTO_INCREMENT PRIMARY KEY,
    jenis_kode VARCHAR(20) UNIQUE NOT NULL,
    jenis_nama VARCHAR(50) NOT NULL,
    kategori VARCHAR(50),
    deskripsi TEXT,
    status ENUM('aktif', 'nonaktif') DEFAULT 'aktif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

INSERT INTO ref_jenis_usaha (jenis_kode, jenis_nama, kategori, deskripsi) VALUES
('pedagang_sayur', 'Pedagang Sayur', 'Pertanian', 'Vegetable seller'),
('pedagang_buah', 'Pedagang Buah', 'Pertanian', 'Fruit seller'),
('warung_makan', 'Warung Makan', 'Kuliner', 'Food stall'),
('warung_kelontong', 'Warung Kelontong', 'Retail', 'Grocery store'),
('toko_baju', 'Toko Baju', 'Retail', 'Clothing store'),
('lainnya', 'Lainnya', 'Lainnya', 'Other businesses');

-- ============================================
-- 3. Add CHECK constraints for data validation
-- ============================================

-- Check constraint for pinjaman plafon
ALTER TABLE pinjaman ADD CONSTRAINT chk_pinjaman_plafon CHECK (plafon > 0);

-- Check constraint for angsuran jatuh tempo
ALTER TABLE angsuran ADD CONSTRAINT chk_angsuran_jatuh_tempo CHECK (jatuh_tempo >= '2000-01-01');

-- Check constraint for kas_bon jumlah
ALTER TABLE kas_bon ADD CONSTRAINT chk_kas_bon_jumlah CHECK (jumlah > 0);

-- Check constraint for kas_bon tenor
ALTER TABLE kas_bon ADD CONSTRAINT chk_kas_bon_tenor CHECK (tenor_bulan > 0);

-- ============================================
-- 4. Add UNIQUE constraints for email validation
-- ============================================

-- Unique constraint for nasabah email
SET @dbname = DATABASE();
SET @tablename = 'nasabah';
SET @columnname = 'email';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
    (table_schema = @dbname)
    AND (table_name = @tablename)
    AND (column_name = @columnname)
    AND (is_nullable = 'NO')
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE nasabah ADD UNIQUE INDEX idx_nasabah_email (email)')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Unique constraint for users email
SET @tablename = 'users';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
    (table_schema = @dbname)
    AND (table_name = @tablename)
    AND (column_name = @columnname)
    AND (is_nullable = 'NO')
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE users ADD UNIQUE INDEX idx_users_email (email)')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- ============================================
-- 5. Add composite indexes for performance
-- ============================================

-- Composite index for nasabah (cabang + status)
CREATE INDEX idx_nasabah_cabang_status ON nasabah(cabang_id, status);

-- Composite index for pinjaman (cabang + status)
CREATE INDEX idx_pinjaman_cabang_status ON pinjaman(cabang_id, status);

-- Composite index for pinjaman (nasabah + status)
CREATE INDEX idx_pinjaman_nasabah_status ON pinjaman(nasabah_id, status);

-- Composite index for angsuran (pinjaman + status)
CREATE INDEX idx_angsuran_pinjaman_status ON angsuran(pinjaman_id, status);

-- Composite index for angsuran (cabang + status)
CREATE INDEX idx_angsuran_cabang_status ON angsuran(cabang_id, status);

-- Composite index for pembayaran (pinjaman + tanggal)
CREATE INDEX idx_pembayaran_pinjaman_tanggal ON pembayaran(pinjaman_id, tanggal_bayar);

-- Composite index for kas_petugas (petugas + tanggal)
CREATE INDEX idx_kas_petugas_petugas_tanggal ON kas_petugas(petugas_id, tanggal);

-- Composite index for pengeluaran (cabang + status)
CREATE INDEX idx_pengeluaran_cabang_status ON pengeluaran(cabang_id, status);

-- Composite index for pengeluaran (kategori + tanggal)
CREATE INDEX idx_pengeluaran_kategori_tanggal ON pengeluaran(kategori, tanggal);

-- ============================================
-- Migration Complete
-- ============================================
SELECT 'Priority fixes migration completed successfully!' as message;
