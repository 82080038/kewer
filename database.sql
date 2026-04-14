-- Database Schema for Kewer System
-- Created: 2026-04-14

-- CREATE DATABASE IF NOT EXISTS kewer;
-- USE kewer;

-- Users table for authentication
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    role ENUM('superadmin', 'admin', 'petugas') NOT NULL DEFAULT 'petugas',
    cabang_id INT NULL,
    status ENUM('aktif', 'nonaktif') NOT NULL DEFAULT 'aktif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Cabang (Branch) table
CREATE TABLE IF NOT EXISTS cabang (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kode_cabang VARCHAR(10) UNIQUE NOT NULL,
    nama_cabang VARCHAR(100) NOT NULL,
    alamat TEXT,
    telp VARCHAR(20),
    email VARCHAR(100),
    kota VARCHAR(50),
    provinsi VARCHAR(50),
    kode_pos VARCHAR(10),
    status ENUM('aktif', 'nonaktif') NOT NULL DEFAULT 'aktif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Nasabah (Customers) table
CREATE TABLE IF NOT EXISTS nasabah (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cabang_id INT NOT NULL,
    kode_nasabah VARCHAR(20) UNIQUE NOT NULL,
    nama VARCHAR(100) NOT NULL,
    alamat TEXT,
    ktp VARCHAR(16) UNIQUE NOT NULL,
    telp VARCHAR(15) NOT NULL,
    email VARCHAR(100),
    jenis_usaha VARCHAR(50),
    lokasi_pasar VARCHAR(100),
    foto_ktp VARCHAR(255),
    foto_selfie VARCHAR(255),
    status ENUM('aktif', 'nonaktif', 'blacklist') NOT NULL DEFAULT 'aktif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (cabang_id) REFERENCES cabang(id)
);

-- Pinjaman (Loans) table
CREATE TABLE IF NOT EXISTS pinjaman (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cabang_id INT NOT NULL,
    kode_pinjaman VARCHAR(20) UNIQUE NOT NULL,
    nasabah_id INT NOT NULL,
    plafon DECIMAL(12,2) NOT NULL,
    tenor INT NOT NULL,
    bunga_per_bulan DECIMAL(5,2) NOT NULL,
    total_bunga DECIMAL(12,2) NOT NULL,
    total_pembayaran DECIMAL(12,2) NOT NULL,
    angsuran_pokok DECIMAL(12,2) NOT NULL,
    angsuran_bunga DECIMAL(12,2) NOT NULL,
    angsuran_total DECIMAL(12,2) NOT NULL,
    tanggal_akad DATE NOT NULL,
    tanggal_jatuh_tempo DATE NOT NULL,
    tujuan_pinjaman TEXT,
    jaminan TEXT,
    status ENUM('pengajuan', 'disetujui', 'aktif', 'lunas', 'ditolak') NOT NULL DEFAULT 'pengajuan',
    petugas_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (cabang_id) REFERENCES cabang(id),
    FOREIGN KEY (nasabah_id) REFERENCES nasabah(id),
    FOREIGN KEY (petugas_id) REFERENCES users(id)
);

-- Angsuran (Installments) table
CREATE TABLE IF NOT EXISTS angsuran (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cabang_id INT NOT NULL,
    pinjaman_id INT NOT NULL,
    no_angsuran INT NOT NULL,
    jatuh_tempo DATE NOT NULL,
    pokok DECIMAL(12,2) NOT NULL,
    bunga DECIMAL(12,2) NOT NULL,
    total_angsuran DECIMAL(12,2) NOT NULL,
    denda DECIMAL(12,2) DEFAULT 0,
    total_bayar DECIMAL(12,2) DEFAULT 0,
    status ENUM('belum', 'lunas', 'telat') NOT NULL DEFAULT 'belum',
    tanggal_bayar DATE NULL,
    cara_bayar VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (cabang_id) REFERENCES cabang(id),
    FOREIGN KEY (pinjaman_id) REFERENCES pinjaman(id)
);

-- Pembayaran (Payments) table
CREATE TABLE IF NOT EXISTS pembayaran (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cabang_id INT NOT NULL,
    pinjaman_id INT NOT NULL,
    angsuran_id INT NOT NULL,
    kode_pembayaran VARCHAR(20) UNIQUE NOT NULL,
    jumlah_bayar DECIMAL(12,2) NOT NULL,
    denda DECIMAL(12,2) DEFAULT 0,
    total_bayar DECIMAL(12,2) NOT NULL,
    tanggal_bayar DATE NOT NULL,
    cara_bayar ENUM('tunai', 'transfer', 'digital') NOT NULL,
    bukti_bayar VARCHAR(255),
    petugas_id INT NOT NULL,
    keterangan TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (cabang_id) REFERENCES cabang(id),
    FOREIGN KEY (pinjaman_id) REFERENCES pinjaman(id),
    FOREIGN KEY (angsuran_id) REFERENCES angsuran(id),
    FOREIGN KEY (petugas_id) REFERENCES users(id)
);

-- Settings table for system configuration
CREATE TABLE IF NOT EXISTS settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert default data
INSERT INTO users (username, password, nama, email, role, cabang_id) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'admin@kewer.com', 'superadmin', NULL),
('petugas1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Petugas Satu', 'petugas1@kewer.com', 'petugas', 1);

INSERT INTO cabang (kode_cabang, nama_cabang, alamat, telp, kota, provinsi) VALUES 
('CBG001', 'Pusat', 'Jl. Merdeka No. 123', '021-12345678', 'Jakarta', 'DKI Jakarta'),
('CBG002', 'Cabang A', 'Jl. Sudirman No. 456', '021-87654321', 'Jakarta', 'DKI Jakarta');

INSERT INTO settings (setting_key, setting_value, description) VALUES 
('bunga_default', '2.5', 'Bunga default per bulan (%)'),
('max_plafon', '10000000', 'Maksimal plafon pinjaman'),
('max_tenor', '12', 'Maksimal tenor (bulan)'),
('denda_keterlambatan', '0.5', 'Denda keterlambatan per hari (%)');

-- Create indexes for better performance
CREATE INDEX idx_nasabah_cabang ON nasabah(cabang_id);
CREATE INDEX idx_nasabah_ktp ON nasabah(ktp);
CREATE INDEX idx_pinjaman_nasabah ON pinjaman(nasabah_id);
CREATE INDEX idx_pinjaman_cabang ON pinjaman(cabang_id);
CREATE INDEX idx_angsuran_pinjaman ON angsuran(pinjaman_id);
CREATE INDEX idx_angsuran_jatuh_tempo ON angsuran(jatuh_tempo);
CREATE INDEX idx_pembayaran_pinjaman ON pembayaran(pinjaman_id);
