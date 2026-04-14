-- Database Kewer - Sistem Pinjaman Modal Pedagang
-- Complete SQL Export for phpMyAdmin Import
-- Generated: 2026-04-14

-- Create Database
CREATE DATABASE IF NOT EXISTS `kewer` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `kewer`;

-- Users table for authentication
CREATE TABLE IF NOT EXISTS `users` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `username` varchar(50) NOT NULL,
    `password` varchar(255) NOT NULL,
    `nama` varchar(100) NOT NULL,
    `email` varchar(100) DEFAULT NULL,
    `role` enum('superadmin','admin','petugas') NOT NULL DEFAULT 'petugas',
    `cabang_id` int(11) DEFAULT NULL,
    `status` enum('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    PRIMARY KEY (`id`),
    UNIQUE KEY `username` (`username`),
    KEY `cabang_id` (`cabang_id`),
    CONSTRAINT `users_ibfk_1` FOREIGN KEY (`cabang_id`) REFERENCES `cabang` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Cabang (Branch) table
CREATE TABLE IF NOT EXISTS `cabang` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `kode_cabang` varchar(10) NOT NULL,
    `nama_cabang` varchar(100) NOT NULL,
    `alamat` text DEFAULT NULL,
    `telp` varchar(20) DEFAULT NULL,
    `email` varchar(100) DEFAULT NULL,
    `kota` varchar(50) DEFAULT NULL,
    `provinsi` varchar(50) DEFAULT NULL,
    `kode_pos` varchar(10) DEFAULT NULL,
    `status` enum('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    PRIMARY KEY (`id`),
    UNIQUE KEY `kode_cabang` (`kode_cabang`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Nasabah (Customers) table
CREATE TABLE IF NOT EXISTS `nasabah` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `cabang_id` int(11) NOT NULL,
    `kode_nasabah` varchar(20) NOT NULL,
    `nama` varchar(100) NOT NULL,
    `alamat` text DEFAULT NULL,
    `ktp` varchar(16) NOT NULL,
    `telp` varchar(15) NOT NULL,
    `email` varchar(100) DEFAULT NULL,
    `jenis_usaha` varchar(50) DEFAULT NULL,
    `lokasi_pasar` varchar(100) DEFAULT NULL,
    `foto_ktp` varchar(255) DEFAULT NULL,
    `foto_selfie` varchar(255) DEFAULT NULL,
    `status` enum('aktif','nonaktif','blacklist') NOT NULL DEFAULT 'aktif',
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    PRIMARY KEY (`id`),
    UNIQUE KEY `kode_nasabah` (`kode_nasabah`),
    UNIQUE KEY `ktp` (`ktp`),
    KEY `cabang_id` (`cabang_id`),
    CONSTRAINT `nasabah_ibfk_1` FOREIGN KEY (`cabang_id`) REFERENCES `cabang` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Pinjaman (Loans) table
CREATE TABLE IF NOT EXISTS `pinjaman` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `cabang_id` int(11) NOT NULL,
    `kode_pinjaman` varchar(20) NOT NULL,
    `nasabah_id` int(11) NOT NULL,
    `plafon` decimal(12,2) NOT NULL,
    `tenor` int(11) NOT NULL,
    `bunga_per_bulan` decimal(5,2) NOT NULL,
    `total_bunga` decimal(12,2) NOT NULL,
    `total_pembayaran` decimal(12,2) NOT NULL,
    `angsuran_pokok` decimal(12,2) NOT NULL,
    `angsuran_bunga` decimal(12,2) NOT NULL,
    `angsuran_total` decimal(12,2) NOT NULL,
    `tanggal_akad` date NOT NULL,
    `tanggal_jatuh_tempo` date NOT NULL,
    `tujuan_pinjaman` text DEFAULT NULL,
    `jaminan` text DEFAULT NULL,
    `status` enum('pengajuan','disetujui','aktif','lunas','ditolak') NOT NULL DEFAULT 'pengajuan',
    `petugas_id` int(11) NOT NULL,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    PRIMARY KEY (`id`),
    UNIQUE KEY `kode_pinjaman` (`kode_pinjaman`),
    KEY `cabang_id` (`cabang_id`),
    KEY `nasabah_id` (`nasabah_id`),
    KEY `petugas_id` (`petugas_id`),
    CONSTRAINT `pinjaman_ibfk_1` FOREIGN KEY (`cabang_id`) REFERENCES `cabang` (`id`),
    CONSTRAINT `pinjaman_ibfk_2` FOREIGN KEY (`nasabah_id`) REFERENCES `nasabah` (`id`),
    CONSTRAINT `pinjaman_ibfk_3` FOREIGN KEY (`petugas_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Angsuran (Installments) table
CREATE TABLE IF NOT EXISTS `angsuran` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `cabang_id` int(11) NOT NULL,
    `pinjaman_id` int(11) NOT NULL,
    `no_angsuran` int(11) NOT NULL,
    `jatuh_tempo` date NOT NULL,
    `pokok` decimal(12,2) NOT NULL,
    `bunga` decimal(12,2) NOT NULL,
    `total_angsuran` decimal(12,2) NOT NULL,
    `denda` decimal(12,2) DEFAULT 0.00,
    `total_bayar` decimal(12,2) DEFAULT 0.00,
    `status` enum('belum','lunas','telat') NOT NULL DEFAULT 'belum',
    `tanggal_bayar` date DEFAULT NULL,
    `cara_bayar` varchar(50) DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    PRIMARY KEY (`id`),
    KEY `cabang_id` (`cabang_id`),
    KEY `pinjaman_id` (`pinjaman_id`),
    KEY `idx_angsuran_jatuh_tempo` (`jatuh_tempo`),
    CONSTRAINT `angsuran_ibfk_1` FOREIGN KEY (`cabang_id`) REFERENCES `cabang` (`id`),
    CONSTRAINT `angsuran_ibfk_2` FOREIGN KEY (`pinjaman_id`) REFERENCES `pinjaman` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Pembayaran (Payments) table
CREATE TABLE IF NOT EXISTS `pembayaran` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `cabang_id` int(11) NOT NULL,
    `pinjaman_id` int(11) NOT NULL,
    `angsuran_id` int(11) NOT NULL,
    `kode_pembayaran` varchar(20) NOT NULL,
    `jumlah_bayar` decimal(12,2) NOT NULL,
    `denda` decimal(12,2) DEFAULT 0.00,
    `total_bayar` decimal(12,2) NOT NULL,
    `tanggal_bayar` date NOT NULL,
    `cara_bayar` enum('tunai','transfer','digital') NOT NULL,
    `bukti_bayar` varchar(255) DEFAULT NULL,
    `petugas_id` int(11) NOT NULL,
    `keterangan` text DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    PRIMARY KEY (`id`),
    UNIQUE KEY `kode_pembayaran` (`kode_pembayaran`),
    KEY `cabang_id` (`cabang_id`),
    KEY `pinjaman_id` (`pinjaman_id`),
    KEY `angsuran_id` (`angsuran_id`),
    KEY `petugas_id` (`petugas_id`),
    CONSTRAINT `pembayaran_ibfk_1` FOREIGN KEY (`cabang_id`) REFERENCES `cabang` (`id`),
    CONSTRAINT `pembayaran_ibfk_2` FOREIGN KEY (`pinjaman_id`) REFERENCES `pinjaman` (`id`),
    CONSTRAINT `pembayaran_ibfk_3` FOREIGN KEY (`angsuran_id`) REFERENCES `angsuran` (`id`),
    CONSTRAINT `pembayaran_ibfk_4` FOREIGN KEY (`petugas_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Settings table for system configuration
CREATE TABLE IF NOT EXISTS `settings` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `setting_key` varchar(100) NOT NULL,
    `setting_value` text DEFAULT NULL,
    `description` text DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    PRIMARY KEY (`id`),
    UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert default data
INSERT INTO `users` (`username`, `password`, `nama`, `email`, `role`, `cabang_id`) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'admin@kewer.com', 'superadmin', NULL),
('petugas1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Petugas Satu', 'petugas1@kewer.com', 'petugas', 1);

INSERT INTO `cabang` (`kode_cabang`, `nama_cabang`, `alamat`, `telp`, `kota`, `provinsi`) VALUES
('CBG001', 'Pusat', 'Jl. Merdeka No. 123', '021-12345678', 'Jakarta', 'DKI Jakarta'),
('CBG002', 'Cabang A', 'Jl. Sudirman No. 456', '021-87654321', 'Jakarta', 'DKI Jakarta');

INSERT INTO `settings` (`setting_key`, `setting_value`, `description`) VALUES
('bunga_default', '2.5', 'Bunga default per bulan (%)'),
('max_plafon', '10000000', 'Maksimal plafon pinjaman'),
('max_tenor', '12', 'Maksimal tenor (bulan)'),
('denda_keterlambatan', '0.5', 'Denda keterlambatan per hari (%)');

-- Create indexes for better performance
CREATE INDEX `idx_nasabah_cabang` ON `nasabah`(`cabang_id`);
CREATE INDEX `idx_pinjaman_cabang` ON `pinjaman`(`cabang_id`);
CREATE INDEX `idx_pembayaran_pinjaman` ON `pembayaran`(`pinjaman_id`);

-- Final output
SELECT 'Database Kewer berhasil dibuat!' as status;
