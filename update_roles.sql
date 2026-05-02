-- Update user roles to match new standardized structure
-- Run this script to update existing users in the database

-- Update admin to superadmin
UPDATE users SET role = 'superadmin' WHERE username = 'admin';

-- Update petugas1 to petugas_cabang
UPDATE users SET role = 'petugas_cabang' WHERE username = 'petugas1';

-- Optional: Add bos user (if not exists)
-- INSERT INTO users (username, password, nama, email, role, cabang_id, status)
-- VALUES ('bos', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Bos', 'bos@kewer.com', 'bos', NULL, 'aktif');
