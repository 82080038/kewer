-- ============================================
-- KEWER Fresh Install - Reset Script
-- ============================================
-- Jalankan script ini untuk mereset database
-- ke kondisi bersih (fresh install).
-- Hanya struktur tabel dan data referensi
-- yang dipertahankan.
-- 
-- Usage: mysql -u root -p kewer < fresh_install.sql
-- ============================================

SET FOREIGN_KEY_CHECKS = 0;

-- Hapus semua data transaksional
TRUNCATE TABLE pembayaran;
TRUNCATE TABLE angsuran;
TRUNCATE TABLE pinjaman;
TRUNCATE TABLE nasabah;
TRUNCATE TABLE nasabah_family_link;
TRUNCATE TABLE nasabah_orang_mapping;
TRUNCATE TABLE blacklist_log;
TRUNCATE TABLE family_risk;
TRUNCATE TABLE loan_risk_log;

-- Hapus data keuangan
TRUNCATE TABLE jurnal;
TRUNCATE TABLE jurnal_detail;
TRUNCATE TABLE kas_bon;
TRUNCATE TABLE kas_bon_potongan;
TRUNCATE TABLE kas_petugas;
TRUNCATE TABLE kas_petugas_setoran;
TRUNCATE TABLE pengeluaran;
TRUNCATE TABLE daily_cash_reconciliation;
TRUNCATE TABLE consolidated_reports;
TRUNCATE TABLE transaksi_log;

-- Hapus data user & cabang
TRUNCATE TABLE users;
TRUNCATE TABLE cabang;
TRUNCATE TABLE bos_registrations;
TRUNCATE TABLE delegated_permissions;
TRUNCATE TABLE user_permissions;
TRUNCATE TABLE permission_audit_log;
TRUNCATE TABLE audit_log;

-- Hapus settings per-cabang
TRUNCATE TABLE auto_confirm_settings;
TRUNCATE TABLE setting_bunga;
TRUNCATE TABLE setting_denda;
TRUNCATE TABLE field_officer_activities;

-- Hapus akun keuangan
TRUNCATE TABLE akun;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================
-- Buat App Owner (pemilik platform)
-- Password: AppOwner2024!
-- ============================================
INSERT INTO users (username, password, nama, email, role, status)
VALUES ('appowner', '$2y$10$ajZboWPqy1m4wxL6/G13eO.gnKFjJxX5jU1Qklv9cdLcYmtGGli9.', 'App Owner', 'admin@kewer.app', 'appOwner', 'aktif');

-- Pastikan role appOwner ada di ref_roles
INSERT INTO ref_roles (role_kode, role_nama, deskripsi, urutan_tampil, status)
SELECT 'appOwner', 'App Owner', 'Pemilik aplikasi yang mengelola pendaftaran koperasi dan persetujuan Bos', 0, 'aktif'
FROM dual WHERE NOT EXISTS (SELECT 1 FROM ref_roles WHERE role_kode = 'appOwner');

-- Pastikan appOwner permissions ada
INSERT IGNORE INTO permissions (kode, nama, deskripsi, kategori) VALUES
('manage_app', 'Kelola Aplikasi', 'Akses pengelolaan level aplikasi', 'app'),
('approve_bos', 'Approve Bos', 'Menyetujui pendaftaran Bos koperasi baru', 'app'),
('view_koperasi', 'Lihat Koperasi', 'Melihat daftar semua koperasi terdaftar', 'app'),
('suspend_koperasi', 'Suspend Koperasi', 'Menangguhkan koperasi', 'app');

INSERT INTO role_permissions (role, permission_code, granted) VALUES
('appOwner', 'manage_app', 1),
('appOwner', 'approve_bos', 1),
('appOwner', 'view_koperasi', 1),
('appOwner', 'suspend_koperasi', 1)
ON DUPLICATE KEY UPDATE granted = 1;

-- ============================================
-- Data referensi berikut TIDAK dihapus:
-- - ref_roles (9 role termasuk appOwner)
-- - role_permissions (hak akses per role)
-- - permissions (daftar permission)
-- - ref_jaminan_tipe
-- - ref_jenis_usaha
-- - ref_kategori_pengeluaran
-- - ref_metode_pembayaran
-- - ref_status_pinjaman
-- - denda_settings
-- - settings
-- - provinces, regencies, districts, villages
-- ============================================

SELECT 'Fresh install complete. Login sebagai appowner / AppOwner2024! untuk approve Bos koperasi.' AS message;
