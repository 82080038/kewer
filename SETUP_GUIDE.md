# Kewer Development Environment Setup Guide

Complete setup requirements for developing Kewer application on Windsurf IDE.

## Prerequisites

- Ubuntu Linux (or similar)
- Sudo access
- Internet connection

## Required Software

### 1. XAMPP
Download and install XAMPP from https://www.apachefriends.org/
- Apache web server
- MySQL/MariaDB database
- PHP 8.0+

### 2. Development Tools

#### Node.js and npm
```bash
sudo apt update
sudo apt install nodejs npm
```

Verify installation:
```bash
node --version  # Should be v18+
npm --version
```

#### PHP Composer
```bash
sudo apt install composer
```

Verify installation:
```bash
composer --version
```

#### MySQL Client
```bash
sudo apt install mysql-client-core-8.0
```

### 3. PHP Extensions

Required PHP extensions for the application:
```bash
sudo apt install php8.3-mysql php8.3-gd php8.3-mbstring php8.3-curl php8.3-xml php8.3-zip
```

Verify extensions:
```bash
php -m | grep -E "mysqli|gd|mbstring|curl|xml|zip"
```

## Project Setup

### Langkah 1: Clone Repository

```bash
git clone https://github.com/your-repo/kewer.git
cd kewer
```

### Langkah 2: Konfigurasi Environment

1. Copy file `.env.example` ke `.env`:
```bash
cp .env.example .env
```

2. Edit file `.env` sesuai konfigurasi Anda:
```env
APP_NAME=Kewer
APP_ENV=production
APP_DEBUG=false
APP_URL=http://localhost/kewer

# Database Configuration
DB_HOST=localhost
DB_PORT=3306
DB_NAME=kewer
DB_USER=root
DB_PASS=your_password

# Session Configuration
SESSION_LIFETIME=7200
```

### Langkah 3: Setup Database (3 database)

1. Buat 3 database MySQL:
```bash
/opt/lampp/bin/mysql -u root -proot -e "
  CREATE DATABASE IF NOT EXISTS kewer;
  CREATE DATABASE IF NOT EXISTS db_alamat_simple;
  CREATE DATABASE IF NOT EXISTS db_orang;
"
```

2. Import dari folder database/:
```bash
/opt/lampp/bin/mysql -u root -proot kewer           < database/kewer.sql
/opt/lampp/bin/mysql -u root -proot db_alamat_simple < database/db_alamat_simple.sql
/opt/lampp/bin/mysql -u root -proot db_orang        < database/db_orang.sql
```

**Via phpMyAdmin:**
1. Buka `http://localhost/phpmyadmin`
2. Buat 3 database: `kewer`, `db_alamat_simple`, `db_orang`
3. Import tiap file SQL dari folder `database/`

### Langkah 4: Setup User Awal

**Platform Owner:**
- Username: appowner / Password: AppOwner2024!
- Akses: pages/app_owner/* (dashboard, approvals, koperasi, billing, usage, ai_advisor, settings)

**Koperasi Users (password: Kewer2024!):**
| Username | Role | Cabang |
|----------|------|--------|
| patri | bos | Kantor Pusat Pangururan |
| mgr_pusat | manager_pusat | Cabang 1 |
| mgr_balige | manager_cabang | Cabang Balige |
| adm_pusat | admin_pusat | Cabang 1 |
| ptr_pngr1 | petugas_pusat | Cabang 1 |
| ptr_pngr2 | petugas_cabang | Cabang 1 |
| krw_pngr | karyawan | Cabang 1 |

**Catatan:** Semua users sudah dibuat otomatis saat import database. Role permissions sudah terkonfigurasi.

## Database Setup

### 1. Start XAMPP
```bash
sudo /opt/lampp/lampp start
```

### 2. Create Database
```bash
/opt/lampp/bin/mysql -u root -proot -e "
  CREATE DATABASE IF NOT EXISTS kewer;
  CREATE DATABASE IF NOT EXISTS db_alamat_simple;
  CREATE DATABASE IF NOT EXISTS db_orang;
"
```

### 3. Import Database Schema
```bash
/opt/lampp/bin/mysql -u root -proot kewer           < database/kewer.sql
/opt/lampp/bin/mysql -u root -proot db_alamat_simple < database/db_alamat_simple.sql
/opt/lampp/bin/mysql -u root -proot db_orang        < database/db_orang.sql
```

### 4. Configure Database Connection
Edit `config/database.php` jika diperlukan:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', 'root');
define('DB_NAME', 'kewer');
```

## Verify Installation

### Check Database Connection
```bash
/opt/lampp/bin/mysql -u root -proot -e "SHOW DATABASES;"
/opt/lampp/bin/mysql -u root -proot kewer -e "SHOW TABLES;"
/opt/lampp/bin/mysql -u root -proot -e "SELECT 'kewer' as db, COUNT(*) as tables FROM information_schema.TABLES WHERE TABLE_SCHEMA='kewer'
UNION ALL SELECT 'db_alamat_simple', COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA='db_alamat_simple'
UNION ALL SELECT 'db_orang', COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA='db_orang';"
```

### Check PHP Extensions
```bash
php -m | grep -E "mysqli|gd|mbstring|curl|json|zip"
```

### Test Application
1. Open browser: `http://localhost/kewer/login.php`
2. Login sebagai appOwner: appowner / AppOwner2024!

## Development Tools

### Windsurf IDE
- Install Windsurf from https://windsurf.ai/
- Open project folder in Windsurf

### Git
```bash
sudo apt install git
```

### Browser Testing
- Chrome/Chromium (for Playwright)
- Firefox (for cross-browser testing)

## Troubleshooting

### MySQL Connection Issues
If MySQL client can't connect:
```bash
# Use XAMPP's MySQL
/opt/lampp/bin/mysql -u root -p
```

### PHP Extension Issues
If Composer complains about missing extensions:
```bash
# Install specific extension
sudo apt install php8.3-[extension-name]
```

### Playwright Issues
If Playwright browsers not found:
```bash
cd tests/e2e
npx playwright install
```

## Default Credentials

### Platform Owner
| Role | Username | Password |
|------|----------|----------|
| appOwner | appowner | AppOwner2024! |

### Koperasi Users (password: Kewer2024!)
| Username | Role | Cabang |
|----------|------|--------|
| patri | bos | Kantor Pusat Pangururan |
| mgr_pusat | manager_pusat | Cabang 1 |
| mgr_balige | manager_cabang | Cabang Balige |
| adm_pusat | admin_pusat | Cabang 1 |
| ptr_pngr1 | petugas_pusat | Cabang 1 |
| ptr_pngr2 | petugas_cabang | Cabang 1 |
| krw_pngr | karyawan | Cabang 1 |

## Development Workflow

1. Start XAMPP: `sudo /opt/lampp/lampp start`
2. Open Windsurf IDE
3. Make code changes
4. Test in browser: `http://localhost/kewer`
5. Run tests: `cd tests/e2e && npm test`
6. Commit changes: `git commit -am "description"`

## Additional Resources

- README.md: Project overview and features
- docs/role_definitions.json: Role and permission structure
- tests/: Testing suite
- api/: API documentation

## System Requirements

- RAM: Minimum 4GB (8GB recommended)
- Disk Space: Minimum 2GB free
- CPU: Dual-core or better
- OS: Ubuntu 20.04+ or similar

## Support

For issues:
1. Check XAMPP status: `sudo /opt/lampp/lampp status`
2. Check MySQL logs: `/opt/lampp/var/mysql/*.err`
3. Check Apache logs: `/opt/lampp/logs/error_log`

# Panduan Setup Aplikasi Kewer

**Versi Dokumen:** 2.0
**Terakhir Diperbarui:** 2026-04-28
**Aplikasi:** Koperasi Warga Ekonomi Rakyat (Kewer)

## Ringkasan

Dokumen ini menyediakan panduan lengkap untuk setup, konfigurasi, dan deployment aplikasi Kewer. Kewer adalah sistem manajemen pinjaman untuk koperasi pasar/bank keliling yang meminjamkan uang ke pedagang pasar/UMKM dengan angsuran harian, mingguan, atau bulanan.

## Persyaratan Sistem

### Server Requirements
- PHP 8.0 atau lebih tinggi
- MySQL 5.7+ atau MariaDB 10.3+
- Apache 2.4+ atau Nginx 1.18+
- Minimal 2GB RAM
- Minimal 10GB disk space

### PHP Extensions
- mysqli
- mbstring
- json
- session
- gd
- curl
- zip
- fileinfo

## 3-Database Architecture

### kewer (Main Database - 49 tabel + 3 view)
- **Purpose**: Transaksi koperasi, users, billing, usage, activities
- **Connection**: `$conn` / `query()`

### db_alamat_simple (Address Database - 4 tabel)
- **Purpose**: Referensi lokasi Sumatera Utara
- **Connection**: `$conn_alamat` / `query_alamat()`
- **Note**: Sumatera Utara province_id = 3

### db_orang (People Database - 19 tabel + 6 view)
- **Purpose**: Identitas orang + alamat + geospasial nasional
- **Connection**: `$conn_orang` / `query_orang()`
- **Cross-DB Links**: users.db_orang_person_id, nasabah.db_orang_user_id, cabang.db_orang_person_id

## Struktur Role (9 Levels + appOwner)

```
appOwner: Platform owner (billing, approvals, AI advisor)

Level 1: Bos (Pemilik Koperasi)
Level 2: Manager Pusat
Level 3: Manager Cabang
Level 4: Admin Pusat
Level 5: Admin Cabang
Level 6: Petugas Pusat
Level 7: Petugas Cabang
Level 8: Karyawan (delegated permissions dari bos)
