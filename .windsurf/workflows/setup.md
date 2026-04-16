---
description: Setup dan konfigurasi awal aplikasi Kewer
---

## Setup Aplikasi Kewer

### 1. Clone Repository
```bash
git clone https://github.com/82080038/kewer.git
cd kewer
```

### 2. Start XAMPP
```bash
cd /opt/lampp
echo "8208" | sudo -S ./lampp start
```

### 3. Database Setup
```bash
# Buat database
echo "8208" | sudo -S /opt/lampp/bin/mysql -u root -proot -e "CREATE DATABASE IF NOT EXISTS kewer;"

# Import main database schema
cd /opt/lampp/htdocs/kewer
echo "8208" | sudo -S /opt/lampp/bin/mysql -u root -proot kewer < kewer_database.sql

# Import migration files (in order)
echo "8208" | sudo -S /opt/lampp/bin/mysql -u root -proot kewer < database_migration_family_risk.sql
echo "8208" | sudo -S /opt/lampp/bin/mysql -u root -proot kewer < database_migration_kas_bon.sql
echo "8208" | sudo -S /opt/lampp/bin/mysql -u root -proot kewer < database_migration_new_features.sql
echo "8208" | sudo -S /opt/lampp/bin/mysql -u root -proot kewer < database_migration_priority_fixes.sql
echo "8208" | sudo -S /opt/lampp/bin/mysql -u root -proot kewer < database_migration_role_hierarchy.sql
echo "8208" | sudo -S /opt/lampp/bin/mysql -u root -proot kewer < database_migration_alamat.sql
```

### 4. Verifikasi Database
```bash
# Cek jumlah tabel (harus 28 tabel)
echo "8208" | sudo -S /opt/lampp/bin/mysql -u root -proot -e "USE kewer; SELECT COUNT(*) as total_tables FROM information_schema.tables WHERE table_schema = 'kewer';"

# Lihat semua tabel
echo "8208" | sudo -S /opt/lampp/bin/mysql -u root -proot -e "USE kewer; SHOW TABLES;"
```

### 5. Database Optimization (Baru)
```bash
# Jalankan database optimization untuk performa
cd /opt/lampp/htdocs/kewer
echo "8208" | sudo -S /opt/lampp/bin/mysql -u root -proot kewer < database_optimization_indexes.sql
```

### 6. Install Dependencies
```bash
cd /opt/lampp/htdocs/kewer
composer install
```

### 7. Set Permissions
```bash
echo "8208" | sudo -S chmod -R 755 /opt/lampp/htdocs/kewer
echo "8208" | sudo -S mkdir -p uploads
echo "8208" | sudo -S chmod 777 uploads
```

### 8. Konfigurasi Environment
File konfigurasi sudah di-set dengan:
- Host: localhost
- Database: kewer
- User: root
- Password: root

Environment variables tersedia di `.env`:
- APP_ENV=development (untuk quick login)
- SESSION_LIFETIME=7200 (2 jam)
- RATE_LIMIT_PER_MINUTE=60

### 9. Akses Aplikasi
Buka browser: http://localhost/kewer/login.php

### Development Credentials (Quick Login)
- Superadmin: admin / admin (development only)
- Owner: owner / password (development only)
- Manager: manager1 / password (development only)
- Petugas: petugas1 / password (development only)
- Petugas: petugas2 / password (development only)
- Karyawan: karyawan1 / password (development only)
- Karyawan: karyawan2 / password (development only)

**Catatan:** Quick login hanya aktif jika APP_ENV=development di .env

### Security Features yang Telah Diimplementasi:
- ✅ CSRF Protection di semua forms
- ✅ Session Timeout (2 jam inactivity)
- ✅ Error Handling Global
- ✅ Rate Limiting untuk API
- ✅ Input Validation Layer
- ✅ Permission Check Consistency
- ✅ Database Indexes untuk optimasi

### System Requirements
- PHP 8.0 atau lebih tinggi
- MySQL/MariaDB 5.7+
- Web server (Apache/NginX)
- Extensions: mysqli, gd, json
