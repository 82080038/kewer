# Kewer — Sistem Pinjaman Modal Pedagang

Aplikasi web berbasis PHP untuk mengelola pinjaman modal pedagang koperasi pasar / bank keliling, dengan fitur manajemen nasabah, pinjaman, angsuran harian/mingguan/bulanan, dan pembayaran.

[![PHP](https://img.shields.io/badge/PHP-8.2+-blue.svg)](https://php.net/)
[![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-purple.svg)](https://getbootstrap.com/)
[![MariaDB](https://img.shields.io/badge/MariaDB-10.4-orange.svg)](https://mariadb.org/)

---

## Model Bisnis

Koperasi pasar / bank keliling meminjamkan uang ke pedagang pasar dan UMKM dengan skema:
- **Angsuran harian** — petugas kutip tiap hari kerja (tenor 30–100 hari)
- **Angsuran mingguan** — petugas kutip tiap minggu (tenor 4–52 minggu)
- **Angsuran bulanan** — tenor 1–12 bulan

Petugas keliling mengutip angsuran langsung ke lokasi nasabah (pasar, lapak, rumah).

---

## Arsitektur

- **Backend:** PHP 8.2, MySQLi prepared statements
- **Frontend:** Bootstrap 5.3, DataTable.js, SweetAlert2, Select2, Flatpickr
- **Database:** 3 database terpisah (lihat di bawah)
- **Auth:** Session-based, 9 role levels + appOwner
- **Platform:** Multi-tenant — appOwner mengelola platform, setiap Bos punya koperasi sendiri
- **Server:** XAMPP (Apache 2.4, MariaDB 10.4, PHP 8.2) di Linux

---

## 3-Database Architecture

### 1. `kewer` — Database Utama (49 tabel + 3 view)
- Transaksi koperasi: users, nasabah, pinjaman, angsuran, pembayaran, cabang
- Platform: billing, usage, AI advisor, koperasi_activities
- **Koneksi:** `$conn` / `query()`

### 2. `db_alamat_simple` — Referensi Lokasi Sumut (4 tabel)
- Provinsi (1), Kabupaten (33), Kecamatan (448), Desa (6.101) — Sumatera Utara
- **Koneksi:** `$conn_alamat` / `query_alamat()`
- `province_id = 3` = Sumatera Utara

### 3. `db_orang` — Identitas Orang Nasional (19 tabel + 6 view)
- Data orang + alamat geospasial nasional (38 prov, 541 kab, 8K kec, 81K desa)
- **Koneksi:** `$conn_orang` / `query_orang()`
- Cross-DB: `users.db_orang_person_id`, `nasabah.db_orang_user_id`, `cabang.db_orang_person_id`

---

## Role Hierarchy

| Level | Role | Username | Keterangan |
|-------|------|----------|-----------|
| 0 | appOwner | appowner | Platform owner — billing, approvals, AI advisor |
| 1 | bos | patri | Pemilik koperasi — akses penuh operasional |
| 3 | manager_pusat | mgr_pusat | Kontrol operasional, approve pinjaman |
| 4 | manager_cabang | mgr_balige | Operasional cabang, approve pinjaman |
| 5 | admin_pusat | adm_pusat | Input nasabah, pinjaman, angsuran |
| 7 | petugas_pusat | ptr_pngr1 | Koleksi pembayaran lapangan |
| 8 | petugas_cabang | ptr_blg1 | Koleksi pembayaran lapangan cabang |
| 9 | karyawan | krw_pngr | Dukungan administratif, rekonsiliasi kas |

> **Catatan:** Multi-cabang aktif — Cabang Pusat (Pematangsiantar, id=1) + Cabang Balige (id=2).

---

## Default Credentials

| Role | Username | Password |
|------|----------|----------|
| appOwner | appowner | AppOwner2024! |
| bos | patri | Kewer2024! |
| manager_pusat | mgr_pusat | Kewer2024! |
| manager_cabang | mgr_balige | Kewer2024! |
| admin_pusat | adm_pusat | Kewer2024! |
| petugas_pusat | ptr_pngr1 | Kewer2024! |
| petugas_cabang | ptr_blg1 | Kewer2024! |
| karyawan | krw_pngr | Kewer2024! |

> Quick login tersedia di halaman login (development mode `APP_ENV=development`).

---

## Fitur Utama

### Platform (appOwner)
- Approve/suspend koperasi bos
- Usage tracking & billing management
- AI advisor per koperasi
- Platform-wide audit logs

### Operasional Koperasi
- **Nasabah:** CRUD, upload KTP, OCR KTP (Tesseract), blacklist, family risk
- **Pinjaman:** Pengajuan, approval workflow, harian/mingguan/bulanan, auto-confirm
- **Angsuran:** Jadwal otomatis, denda keterlambatan, cetak kartu angsuran
- **Pembayaran:** Input tunai, kwitansi, rekonsiliasi kas harian
- **Petugas Lapangan:** GPS tracking, foto, aktivitas lapangan, kas petugas
- **Kas & Keuangan:** Pengeluaran, kas bon, rekonsiliasi kas
- **Laporan:** Keuangan, kinerja pinjaman, nasabah, audit trail
- **Users:** RBAC granular, delegated permissions

### Integrasi
- WhatsApp (Twilio, Wablas, Fonnte)
- PDF export (DomPDF)
- OCR KTP (Tesseract)
- Email notifications (SMTP)

---

## Instalasi

### Prerequisites
- PHP 8.0+ (`mysqli`, `gd`, `mbstring`, `curl`, `zip`)
- MySQL/MariaDB 5.7+
- XAMPP (Linux/Windows) atau Apache + MySQL

### 1. Clone Repository
```bash
git clone https://github.com/82080038/kewer.git /opt/lampp/htdocs/kewer
```

### 2. Setup Database
```bash
# Buat 3 database
/opt/lampp/bin/mysql -u root -proot --socket=/opt/lampp/var/mysql/mysql.sock -e "
  CREATE DATABASE IF NOT EXISTS kewer;
  CREATE DATABASE IF NOT EXISTS db_alamat_simple;
  CREATE DATABASE IF NOT EXISTS db_orang;
"

# Import SQL
/opt/lampp/bin/mysql -u root -proot kewer           < database/kewer.sql
/opt/lampp/bin/mysql -u root -proot db_alamat_simple < database/db_alamat_simple.sql
/opt/lampp/bin/mysql -u root -proot db_orang        < database/db_orang.sql
```

### 3. Konfigurasi
Edit `config/database.php`:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', 'root');
define('DB_NAME', 'kewer');
```

Buat file `.env`:
```env
APP_ENV=development
APP_URL=http://localhost/kewer

# WhatsApp (opsional)
WA_ENABLED=false
WA_PROVIDER=fonnte

# Email (opsional)
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
```

### 4. Install Dependencies
```bash
# PHP (PDF export, OCR)
composer install

# Node.js (simulasi Puppeteer)
npm install
```

### 5. Akses Aplikasi
```
http://localhost/kewer/login.php
```

---

## Simulasi Multi-Role (Puppeteer)

```bash
# Setup: verifikasi bos login + tambah staff
node simulation/run_simulation.js setup

# Simulasi 14 hari × semua role
node simulation/run_simulation.js sim

# Keduanya sekaligus
node simulation/run_simulation.js all

# Test login saja
node simulation/test_login.js
```

Flow simulasi harian:
```
Admin Pusat → input nasabah & pinjaman
Bos/Manager → approve pinjaman
Petugas     → koleksi angsuran di lapangan
Manager     → review operasional
Bos         → dashboard & laporan akhir hari
Karyawan    → rekonsiliasi kas
AppOwner    → monitoring platform
```

---

## API

### Base URL
```
http://localhost/kewer/api/
```

### Endpoints Utama
| Method | Endpoint | Keterangan |
|--------|----------|-----------|
| GET/POST/PUT/DELETE | `/api/nasabah.php` | Manajemen nasabah |
| GET/POST/PUT | `/api/pinjaman.php` | Manajemen pinjaman |
| GET/POST/PUT | `/api/angsuran.php` | Jadwal angsuran |
| GET/POST | `/api/pembayaran.php` | Input pembayaran |
| GET/POST | `/api/kas_petugas.php` | Kas lapangan |
| GET | `/api/dashboard.php` | Statistik dashboard |
| GET/POST/PUT/DELETE | `/api/users.php` | Manajemen user |
| GET/POST/PUT/DELETE | `/api/roles.php` | Role & permissions |

---

## Troubleshooting

### MySQL tidak konek
```bash
/opt/lampp/bin/mysql -u root -proot --socket=/opt/lampp/var/mysql/mysql.sock
```

### XAMPP start
```bash
sudo /opt/lampp/lampp start
sudo /opt/lampp/lampp status
```

### Log error
```bash
tail -f /opt/lampp/logs/error_log
tail -f /opt/lampp/var/mysql/*.err
```

---

## Changelog

### v2.3.1 (2026-05-06)
- ✅ Feature Flags System — dynamic toggle semua fitur baru
- ✅ Layout consistency fix — semua halaman pakai shared `sidebar.php`
- ✅ SQL column bugfixes (cetak_kwitansi, slip_harian, dashboard)
- ✅ 8 appOwner pages (+ features.php)
- ✅ Cleanup: hapus file temp/debug, consolidasi SQL ke `database/`
- ✅ E2E test: 189 tests × 9 roles — 0 failures

### v2.3.0 (2026-05-05)
- ✅ Kolektibilitas OJK, Cron harian, WA Pengingat
- ✅ Target Petugas, Slip Harian, Export Laporan
- ✅ GPS Pembayaran, PWA, 2FA TOTP
- ✅ Simulasi pinjaman real-time

### v2.1.0 (2026-05-03)
- ✅ Single office structure refactor (hapus multi-branch, `kantor_id = 1`)
- ✅ Role JSON files sync dengan database (hapus superadmin, manager, tambah appOwner)
- ✅ Simulasi Puppeteer diperbarui sesuai role & flow aplikasi
- ✅ Credentials semua user koperasi: `Kewer2024!`
- ✅ `loginUser()` default password `Kewer2024!`

### v2.0.0 (2026-05-02)
- ✅ 3-Database architecture (kewer, db_alamat_simple, db_orang)
- ✅ appOwner platform layer (billing, usage, AI advisor)
- ✅ 7 appOwner pages
- ✅ Cross-DB links: users/nasabah/cabang → db_orang.people

### v1.2.0 (2026-04-30)
- ✅ Simulasi multi-role Puppeteer (13 role × 14 hari)
- ✅ WhatsApp integration (Twilio, Wablas, Fonnte)
- ✅ PDF export (DomPDF), OCR KTP (Tesseract)

### v1.0.0 (2026-04-14)
- Initial release

---

**Repository:** https://github.com/82080038/kewer  
**Developed by:** 82080038
