# Panduan Setup Kewer — Development Environment

**Versi:** 2.4.0  
**Terakhir Diperbarui:** 2026-05-07  
**Server:** XAMPP di Linux (Apache 2.4, MariaDB 10.4, PHP 8.2)

---

## Persyaratan Sistem

- OS: Ubuntu 20.04+ / Linux
- RAM: minimal 4GB
- Disk: minimal 5GB free
- PHP 8.0+ dengan extension: `mysqli`, `gd`, `mbstring`, `curl`, `zip`, `json`
- MySQL/MariaDB 5.7+ (via XAMPP)
- Node.js v18+ (untuk simulasi Puppeteer)
- Composer (untuk PDF export, OCR)

---

## 1. Install XAMPP

```bash
# Download XAMPP untuk Linux dari apachefriends.org
chmod +x xampp-linux-x64-*.run
sudo ./xampp-linux-x64-*.run

# Start XAMPP
sudo /opt/lampp/lampp start

# Cek status
sudo /opt/lampp/lampp status
```

---

## 2. Clone & Setup Project

```bash
git clone https://github.com/82080038/kewer.git /opt/lampp/htdocs/kewer
cd /opt/lampp/htdocs/kewer

# Install PHP dependencies
composer install

# Install Node dependencies (simulasi)
npm install
```

---

## 3. Setup Database (3 Database)

```bash
# Buat 3 database
/opt/lampp/bin/mysql -u root -proot --socket=/opt/lampp/var/mysql/mysql.sock -e "
  CREATE DATABASE IF NOT EXISTS kewer;
  CREATE DATABASE IF NOT EXISTS db_alamat_simple;
  CREATE DATABASE IF NOT EXISTS db_orang;
"

# Import data (gunakan export terbaru)
/opt/lampp/bin/mysql -u root -proot --socket=/opt/lampp/var/mysql/mysql.sock kewer           < database/kewer_export.sql
/opt/lampp/bin/mysql -u root -proot --socket=/opt/lampp/var/mysql/mysql.sock db_alamat_simple < database/db_alamat_simple_export.sql
/opt/lampp/bin/mysql -u root -proot --socket=/opt/lampp/var/mysql/mysql.sock db_orang        < database/db_orang_export.sql

# Atau jalankan fresh install (reset ke kondisi awal)
/opt/lampp/bin/mysql -u root -proot --socket=/opt/lampp/var/mysql/mysql.sock kewer < scripts/fresh_install.sql

# Verifikasi
/opt/lampp/bin/mysql -u root -proot --socket=/opt/lampp/var/mysql/mysql.sock -e "
  SELECT schema_name, COUNT(*) AS tables
  FROM information_schema.tables
  WHERE schema_name IN ('kewer','db_alamat_simple','db_orang')
  GROUP BY schema_name;
"
```

### Migration Files
Migration tersedia di `database/migrations/`:
- `009_platform_features.sql` - Platform features
- `010_v230_columns.sql` - v2.3.0 columns
- `011_payment_methods.sql` - Payment methods support (bank, ewallet, qris, virtual_account, mobile_banking)

---

## 4. Konfigurasi

### `config/database.php`
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', 'root');
define('DB_NAME', 'kewer');
// Socket path untuk XAMPP Linux
define('DB_SOCKET', '/opt/lampp/var/mysql/mysql.sock');
```

### `.env`
```env
APP_ENV=development
APP_DEBUG=true
APP_URL=http://localhost/kewer

# Session
SESSION_LIFETIME=7200

# WhatsApp (opsional)
WA_ENABLED=false
WA_PROVIDER=fonnte
FONNTE_TOKEN=your_token

# Email (opsional)
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USERNAME=your@email.com
SMTP_PASSWORD=your_app_password
```

> **Penting:** `APP_ENV=development` mengaktifkan quick login di halaman login.

---

## 5. Install Tesseract OCR (Opsional — untuk OCR KTP)

```bash
sudo apt-get install tesseract-ocr tesseract-ocr-ind
tesseract --version
```

---

## 6. Verifikasi Instalasi

```bash
# Cek PHP extensions
php -m | grep -E "mysqli|gd|mbstring|curl|zip"

# Cek koneksi database
/opt/lampp/bin/mysql -u root -proot --socket=/opt/lampp/var/mysql/mysql.sock kewer -e "SELECT COUNT(*) FROM users;"

# Cek users terdaftar di DB
/opt/lampp/bin/mysql -u root -proot --socket=/opt/lampp/var/mysql/mysql.sock kewer -e "SELECT id, username, role FROM users ORDER BY id;"
```

---

## 7. Akses Aplikasi

```
http://localhost/kewer/login.php
```

---

## Default Credentials

### Platform Owner
| Role | Username | Password |
|------|----------|----------|
| appOwner | appowner | AppOwner2024! |

### Koperasi Users (semua password: `Kewer2024!`)
| Username | Role | Level |
|----------|------|-------|
| patri | bos | 1 |
| mgr_pusat | manager_pusat | 3 |
| mgr_balige | manager_cabang | 4 |
| adm_pusat | admin_pusat | 5 |
| ptr_pngr1 | petugas_pusat | 7 |
| ptr_blg1 | petugas_cabang | 8 |
| krw_pngr | karyawan | 9 |

> Quick login tersedia di halaman login saat `APP_ENV=development`

---

## Struktur Role (Sesuai Database)

```
appOwner (level 0) — Platform owner, tidak akses data koperasi
  └── bos (level 1) — Pemilik koperasi, akses penuh
       ├── manager_pusat (level 3) — Approve pinjaman, kelola staff
       ├── manager_cabang (level 4) — Operasional, approve pinjaman
       ├── admin_pusat (level 5) — Input nasabah, pinjaman, angsuran
       ├── petugas_pusat (level 7) — Koleksi lapangan
       ├── petugas_cabang (level 8) — Koleksi lapangan cabang
       └── karyawan (level 9) — Rekonsiliasi kas, view data
```

> **Multi-cabang:** Pusat (id=1, Pematangsiantar) + Cabang Balige (id=2).

---

## Menjalankan Simulasi

```bash
cd /opt/lampp/htdocs/kewer

# Test login semua user
node simulation/test_login.js

# Setup + tambah staff (jika user belum ada di DB)
node simulation/run_simulation.js setup

# Simulasi aktivitas harian 14 hari
node simulation/run_simulation.js sim

# Setup + simulasi sekaligus
node simulation/run_simulation.js all
```

---

## Testing dengan Puppeteer

```bash
cd /opt/lampp/htdocs/kewer

# Test payment methods feature
node tests/puppeteer-payment-methods.test.js

# Comprehensive test untuk appOwner
node tests/puppeteer-appowner-comprehensive.test.js

# Comprehensive test untuk bos
node tests/puppeteer-bos-comprehensive.test.js
```

Screenshots disimpan di `tests/screenshots/`

---

## Changelog

### v2.4.0 (2026-05-07)

**Fitur Baru:**
- **Payment Methods Support**: Mendukung berbagai metode pembayaran (bank, ewallet, qris, virtual_account, mobile_banking)
  - Tambah kolom: `tipe_pembayaran`, `nomor_hp`, `qris_code`, `keterangan` pada tabel `platform_bank_accounts`
  - Update `pages/app_owner/settings.php`: Form dinamis untuk berbagai tipe pembayaran
  - Update `pages/app_owner/billing.php`: Tampilkan metode pembayaran utama
  - Update `pages/bos/billing.php`: Bos dapat melihat informasi pembayaran untuk invoice
  - API endpoint: `api/search_people.php` untuk pencarian data orang

- **Auto-populate Form Nasabah**: Form nasabah otomatis terisi jika KTP/telepon ditemukan di database
  - Input No. Telepon di atas form (wajib)
  - Input No. KTP opsional
  - Auto-search di `db_orang.people` saat input
  - Form terisi otomatis jika data ditemukan
  - Bos dapat mengedit data yang sudah diisi

**Testing:**
- Comprehensive Puppeteer test untuk appOwner (10 steps)
- Comprehensive Puppeteer test untuk bos (6 steps)
- Payment methods test script

**Database Changes:**
- Migration: `database/migrations/011_payment_methods.sql`
- Fresh install updated: `scripts/fresh_install.sql`
- Database exports: `database/*_export.sql`

**File Baru:**
- `api/search_people.php` - API untuk pencarian data orang
- `tests/puppeteer-appowner-comprehensive.test.js`
- `tests/puppeteer-bos-comprehensive.test.js`

---

---

## Troubleshooting

### MySQL socket error
```bash
# Gunakan socket eksplisit
/opt/lampp/bin/mysql -u root -proot --socket=/opt/lampp/var/mysql/mysql.sock
```

### Apache tidak start
```bash
sudo /opt/lampp/lampp stop
sudo /opt/lampp/lampp start
tail -f /opt/lampp/logs/error_log
```

### Permission error pada upload
```bash
sudo chmod -R 777 /opt/lampp/htdocs/kewer/uploads/
```

### Node modules tidak ada
```bash
cd /opt/lampp/htdocs/kewer
npm install
```

---

## Development Workflow

```bash
# 1. Start server
sudo /opt/lampp/lampp start

# 2. Buka IDE
# Edit file di /opt/lampp/htdocs/kewer/

# 3. Test di browser
# http://localhost/kewer/login.php

# 4. Jalankan simulasi (opsional)
node simulation/run_simulation.js setup

# 5. Commit
git add -A
git commit -m "feat: deskripsi perubahan"
git push origin main
```
