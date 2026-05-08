---
description: Development rules untuk aplikasi Kewer
---

# Development Rules - Kewer Application

> **Terakhir diperbarui**: 8 Mei 2026
> **Versi Aplikasi**: v2.4.0 (Nasabah Portal + Penagihan System)

## Core Principles

### 1. Multi-Database Architecture
- **3 database terpisah**: `kewer` (transaksi), `db_alamat` (lokasi nasional), `db_orang` (identitas orang + master data + relasi + audit)
- Gunakan fungsi yang sesuai:
  - `$conn` / `query()` untuk database `kewer`
  - `$conn_alamat` / `query_alamat()` untuk database `db_alamat`
  - `$conn_orang` / `query_orang()` untuk database `db_orang`
- Cross-DB links: `users.db_orang_person_id`, `nasabah.db_orang_user_id`, `cabang.db_orang_person_id` → `db_orang.people.id`
- `db_orang.addresses` → `db_alamat.provinces/regencies/districts/villages`
- **db_orang Master Tables**: `ref_agama`, `ref_jenis_kelamin`, `ref_golongan_darah`, `ref_status_perkawinan`, `ref_suku`, `ref_pekerjaan`, `ref_jenis_alamat`, `ref_jenis_identitas`, `ref_jenis_telepon`, `ref_jenis_email`, `ref_jenis_gelar`, `ref_jenis_relasi`, `ref_jenis_properti`
- **db_orang Supporting Tables**: `people_phones`, `people_emails`, `people_documents`, `family_relations`, `people_audit_log`

### 2. Role-Based Access Control (RBAC)
- 9 role levels + appOwner
- Gunakan `hasPermission('permission_code')` untuk cek akses
- Sidebar menu di-generate berdasarkan role dan permissions
- Delegated permissions untuk karyawan (bos → karyawan)

### 3. Feature Flags System (v2.3.1+)
- Semua fitur baru harus menggunakan feature flags
- Gunakan `isFeatureEnabled('feature_key')` sebelum mengakses fitur
- Feature flags dikelola di tabel `platform_features`
- API: `api/feature_flags.php`

### 4. Frekuensi Angsuran (v2.4.0)
- **Gunakan frekuensi_id (INT)** - kolom `frekuensi` enum sudah dihapus di migration 024
- Tabel reference: `ref_frekuensi_angsuran`
  - ID 1: Harian (max 100 hari)
  - ID 2: Mingguan (max 52 minggu)
  - ID 3: Bulanan (max 36 bulan)
- Helper functions:
  - `getFrequencyCode($frekuensi_id)` - konversi ID ke kode (HARIAN/MINGGUAN/BULANAN)
  - `getFrequencyId($frekuensi_code)` - konversi kode ke ID
  - `getFrequencyLabel($frekuensi_id)` - label Indonesian (Harian/Mingguan/Bulanan)
  - `getMaxTenor($frekuensi_id)` - max tenor per frekuensi
  - `getActiveFrequencies()` - semua frekuensi aktif untuk dropdown
- **JANGAN** gunakan kolom `frekuensi` enum - sudah dihapus

### 5. Nasabah Portal (v2.4.0)
- **Self-service portal untuk nasabah** - 7 pages: dashboard, profil, pengajuan_pinjaman, pengajuan_simpanan, pinjaman, angsuran, pembayaran, data_keluarga
- **Authentication**: Nasabah login dengan KTP + password/OTP
- **API**: `api/nasabah_portal.php` untuk semua nasabah operations
- **Integration**: Gunakan `db_orang` untuk data keluarga (people_helper.php)
- **Security**: Nasabah hanya bisa akses data mereka sendiri (filter by nasabah_id)
- **Layout**: Gunakan layout khusus nasabah (tanpa sidebar admin)

### 6. Penagihan System (v2.4.0)
- **Manajemen penagihan dengan pengingat otomatis**
- **Tables**: `penagihan`, `penagihan_log`, `ref_jenis_penagihan`, `v_penagihan_hari_ini`
- **API**: `api/penagihan.php`, `api/pengingat_penagihan.php`
- **Integration**: Integrasikan dengan WA notifikasi system (jika aktif)
- **Cron Jobs**: Gunakan `cron_daily_tasks.php` untuk auto-generate pengingat
- **Status Tracking**: Pending → In Progress → Completed → Cancelled

### 7. Cron Jobs (v2.4.0)
- **File**: `cron_daily_tasks.php`
- **Schedule**: Jalankan setiap hari via cron job
- **Tasks**:
  - Auto-calculate denda untuk angsuran overdue
  - Update status pinjaman (overdue, write-off)
  - Generate pengingat penagihan (H-3, H-1, hari H)
  - Daily summary reports
- **Setup**: Add ke crontab: `0 0 * * * php /opt/lampp/htdocs/kewer/cron_daily_tasks.php`

### 8. Koperasi Isolation (v2.4.0)
- **Multi-tenant support** - Setiap bos punya koperasi sendiri
- **Helper**: `includes/koperasi_isolation.php`
- **API**: `api/cross_koperasi.php` untuk cross-koperasi operations
- **Data Isolation**: Filter semua data by bos_user_id (koperasi_id)
- **Cross-DB**: Gunakan koperasi_isolation untuk cross-koperasi queries

### 9. Province Activation (v2.4.0)
- **Rollout management per provinsi**
- **Pages**: `pages/app_owner/provinsi_activation.php`
- **API**: `api/provinsi_activation.php`
- **Purpose**: Aktifkan provinsi untuk rollout bertahap

### 10. Page Layout Consistency
- Semua halaman (kecuali compact/standalone) menggunakan layout standar:
  ```html
  <div class="main-container">
      <?php require_once BASE_PATH . '/includes/sidebar.php'; ?>
      <main class="content-area">
          <!-- page content -->
      </main>
  </div>
  ```
- **JANGAN** duplikasi navbar - `sidebar.php` sudah menyediakan navbar
- Standalone pages: `bayar_compact.php`, `index_compact.php`, `blacklist_compact.php`, `transaksi.php`, `riwayat_harian.php`, `gabungan.php`, `setup_headquarters.php`

### 6. Error Handling
- Gunakan prepared statements untuk semua query SQL
- Tambahkan pengecekan array sebelum akses: `is_array($result) && isset($result[0]) ? $result[0] : ['default' => 0]`
- Inisialisasi variabel sebelum digunakan di template: `$error = ''; $success = '';`
- Log error ke `logs/error.log`

### 7. Security
- CSRF protection di semua forms (gunakan `includes/csrf.php`)
- Session timeout 2 jam
- Rate limiting API (60 req/min)
- Input validation via `includes/validation.php`
- Password hashing dengan bcrypt

## Code Patterns

### Database Query Pattern
```php
// Correct - gunakan prepared statements
$result = query("SELECT * FROM nasabah WHERE id = ?", [$id]);

// Incorrect - jangan gunakan query langsung dengan variabel
$result = query("SELECT * FROM nasabah WHERE id = $id"); // XSS/SQL injection risk
```

### Array Access Pattern
```php
// Correct - cek array sebelum akses
$result = query("SELECT nama FROM nasabah WHERE id = ?", [$id]);
$nama = is_array($result) && isset($result[0]) ? $result[0]['nama'] : 'Unknown';

// Incorrect - langsung akses tanpa cek
$nama = query("SELECT nama FROM nasabah WHERE id = ?", [$id])[0]['nama']; // Fatal error jika kosong
```

### Permission Check Pattern
```php
// Correct - gunakan hasPermission()
if (!hasPermission('nasabah_crud')) {
    redirect('dashboard.php');
}

// Incorrect - jangan hardcode role
if ($_SESSION['role'] !== 'admin') { // Tidak scalable
    redirect('dashboard.php');
}
```

### Feature Flag Pattern
```php
// Correct - cek feature flag
if (isFeatureEnabled('wa_notifikasi')) {
    // Kirim notifikasi WA
}

// Incorrect - langsung akses tanpa cek
// Kirim notifikasi WA // Fitur mungkin belum aktif
```

## File Structure

### API Endpoints
- Lokasi: `api/`
- Pattern: RESTful dengan action parameter
- Response format: `{ "success": true/false, "data": ..., "message": ... }`
- Authentication: Bearer token atau session-based

### Page Modules
- Lokasi: `pages/{module}/`
- Setiap module memiliki: `index.php`, `tambah.php`, `edit.php`, `detail.php` (jika perlu)
- Compact/mobile pages: `*_compact.php`

### Helpers
- Lokasi: `includes/`
- Core: `functions.php`, `csrf.php`, `validation.php`
- UI: `datatable_helper.php`, `sweetalert_helper.php`, `select2_helper.php`, `flatpickr_helper.php`
- Business logic: `bunga_calculator.php`, `family_risk.php`, `kas_bon.php`, `kas_petugas.php`

### Models
- Lokasi: `models/`
- Pattern: Class-based dengan method CRUD
- Gunakan `query()` untuk database operations

## Testing Rules

### Before Committing
1. Run PHP syntax check: `find . -name "*.php" -not -path "*/vendor/*" | xargs -I{} php -l {}`
2. Check error log: `cat logs/error.log` (harus kosong)
3. Test API endpoints yang diubah
4. Test halaman yang diubah

### Manual Testing Checklist
- [ ] Login semua role (appowner, bos, manager_pusat, manager_cabang, admin_pusat, admin_cabang, petugas_pusat, petugas_cabang, karyawan)
- [ ] CRUD operations yang diubah
- [ ] Permission checks
- [ ] Feature flags (jika ada)
- [ ] Cross-DB links (alamat dropdown, people data)

## Common Mistakes to Avoid

### 1. SQL Column Name Mismatch
```php
// Common mistakes:
- cabang.nama → harus cabang.nama_cabang
- pembayaran.metode → harus pembayaran.cara_bayar
- pembayaran.dibayar_oleh → harus pembayaran.petugas_id
- angsuran.ke → harus angsuran.no_angsuran
```

### 2. Undefined Variable in Templates
```php
// Incorrect:
if ($_POST) {
    $error = 'Validation failed';
}
echo $error; // Undefined if POST not set

// Correct:
$error = '';
if ($_POST) {
    $error = 'Validation failed';
}
echo $error;
```

### 3. Double Navbar
```php
// Incorrect:
<nav>...</nav> // Duplikat dengan sidebar.php
<?php require_once BASE_PATH . '/includes/sidebar.php'; ?>

// Correct:
<?php require_once BASE_PATH . '/includes/sidebar.php'; ?>
// sidebar.php sudah menyediakan navbar
```

### 4. Hardcoded Paths
```php
// Incorrect:
require_once '/opt/lampp/htdocs/kewer/includes/functions.php';

// Correct:
require_once BASE_PATH . '/includes/functions.php';
```

## Development Workflow

### 1. Setup New Feature
1. Cek apakah fitur sudah ada di workflow yang relevan
2. Jika fitur baru, tambahkan ke feature flags system
3. Buat API endpoint di `api/`
4. Buat page module di `pages/{module}/`
5. Update sidebar menu jika perlu
6. Tambahkan permissions jika perlu
7. Test dengan semua role yang terdampak

### 2. Bug Fix
1. Ikuti workflow `.windsurf/workflows/bugfix.md`
2. Cari pola error serupa di seluruh aplikasi
3. Perbaiki secara menyeluruh
4. Periksa dampak perbaikan
5. Update todo list

### 3. Database Changes
1. Ikuti workflow `.windsurf/workflows/database.md`
2. Backup semua 3 database
3. Buat migration script di `database/migrations/`
4. Update `database/kewer.sql` (dan database lain jika perlu)
5. Test cross-DB links
6. Update models jika perlu

### 4. Role/Permission Changes
1. Ikuti workflow `.windsurf/workflows/role_management.md`
2. Update file role definition di `roles/`
3. Update `includes/functions.php` jika hierarchy berubah
4. Update sidebar menu jika perlu
5. Seed permissions di database
6. Test semua role yang terdampak

## Environment Configuration

### Development
```env
APP_ENV=development
APP_URL=http://localhost/kewer
```

### Production
```env
APP_ENV=production
APP_URL=https://domain.com
```

### Windows (XAMPP)
- Path: `c:\xampp\htdocs\kewer`
- MySQL socket: Tidak perlu (XAMPP Windows menggunakan named pipe)
- Database config di `config/database.php` sudah otomatis handle

### Linux (XAMPP)
- Path: `/opt/lampp/htdocs/kewer`
- MySQL socket: `/opt/lampp/var/mysql/mysql.sock`
- SUDO password: `8208`

## Quick Reference

### Database Counts
- kewer: 64 base tables + 3 views
- db_alamat: 4 tables (Sumut: 1 prov, 33 kab, 448 kec, 6.101 desa)
- db_orang: 19 tables + 6 views (nasional: 38 prov, 541 kab, 8K kec, 81K desa)

### API Endpoints
- Total: 32+ endpoints
- Base: `http://localhost/kewer/api/`

### Page Modules
- Total: 25+ modules
- appOwner pages: 8 pages

### Test Users (password: Kewer2024!)
- patri (bos), mgr_pusat, mgr_balige, adm_pusat, adm_pangururan, adm_balige
- ptr_pngr1 (petugas_pusat), ptr_pngr2 (petugas_cabang), ptr_blg1
- krw_pngr (karyawan), krw_blg
- appowner (AppOwner2024!)

## Workflows Available

Lihat `.windsurf/workflows/` untuk workflow lengkap:
- `setup.md` - Setup awal aplikasi
- `database.md` - Operasi database (backup, restore, reset)
- `bugfix.md` - Perbaikan error secara menyeluruh
- `security.md` - Security improvements dan maintenance
- `testing.md` - Testing procedures
- `deployment.md` - Deployment workflow
- `organizational_structure.md` - Struktur organisasi (bos registration, delegasi permissions)
- `role_management.md` - Manajemen role dan permissions
