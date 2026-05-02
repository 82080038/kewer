# Kewer - Sistem Pinjaman Modal Pedagang

Aplikasi web berbasis PHP untuk mengelola pinjaman modal pedagang dengan fitur lengkap manajemen nasabah, pinjaman, angsuran, dan pembayaran.

## [![GitHub license](https://img.shields.io/github/license/82080038/kewer.svg)](https://github.com/82080038/kewer/blob/main/LICENSE)
## [![PHP version](https://img.shields.io/badge/PHP-8.0%2B-blue.svg)](https://php.net/)
## [![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3.0-purple.svg)](https://getbootstrap.com/)

## Fitur Utama

### Platform Multi-Tenant
- **appOwner**: Platform owner mengelola semua koperasi (billing, usage, AI advisor)
- **Bos Registration**: Bos mendaftar dan disetujui oleh appOwner
- **Usage Tracking**: Monitor API calls dan page renders per koperasi
- **Billing Plans**: 5 paket billing (STARTER, GROWTH, PRO, REVENUE_SHARE, PAY_AS_YOU_GO)
- **AI Advisor**: Generate advice otomatis per koperasi

### Manajemen Pengguna
- **Multi-role Access**: 9 role dengan hierarki jelas
  - appOwner: Platform owner (billing, approvals, AI advisor)
  - Bos: Pemilik koperasi dengan akses operasional penuh
  - Manager Pusat: Kontrol operasional lintas cabang
  - Manager Cabang: Kontrol operasional cabang
  - Admin Pusat: Akses administratif lintas cabang
  - Admin Cabang: Akses administratif cabang
  - Petugas Pusat: Akses lapangan lintas cabang
  - Petugas Cabang: Akses lapangan cabang
  - Karyawan: Akses berdasarkan delegated permissions dari bos
- **Authentication System**: Login/logout dengan session management
- **Cabang Management**: Multi-cabang dengan akses terbatas
- **Role-based Permissions**: Sistem permission granular per role

### Manajemen Nasabah
- **Data Nasabah Lengkap**: KTP, alamat, kontak, jenis usaha
- **Upload Dokumen**: KTP dan selfie verification
- **OCR KTP Integration**: Ekstrak data KTP otomatis menggunakan Tesseract OCR
- **Status Management**: Aktif, nonaktif, blacklist
- **Search & Filter**: Pencarian berdasarkan nama, KTP, telepon
- **Address Management**: Integrasi db_orang untuk manajemen alamat lengkap

### Manajemen Pinjaman
- **Pengajuan Pinjaman**: Form lengkap dengan validasi
- **Loan Calculator**: Perhitungan otomatis bunga dan angsuran
- **Approval Workflow**: Proses persetujuan oleh admin
- **Status Tracking**: Pengajuan, disetujui, aktif, lunas, ditolak
- **Auto Confirm Settings**: Konfigurasi auto-approval berdasarkan kriteria
- **Export**: PDF export untuk laporan pinjaman

### Manajemen Angsuran
- **Jadwal Angsuran**: Otomatis generate berdasarkan tenor
- **Pembayaran**: Input pembayaran dengan denda keterlambatan
- **Late Payment Detection**: Tracking tunggakan otomatis
- **Payment History**: Riwayat pembayaran lengkap
- **Daily Cash Reconciliation**: Rekonciliasi kas harian
- **Export**: PDF export untuk laporan angsuran

### Dashboard & Analytics
- **Real-time Statistics**: Total nasabah, pinjaman aktif, outstanding
- **Recent Activities**: Log aktivitas terkini
- **Cabang Selector**: Filter berdasarkan cabang (admin)
- **Performance Metrics**: Kinerja petugas dan cabang
- **Audit Trail**: Log audit untuk tracking perubahan

### API Integration
- **RESTful API**: CRUD operations untuk semua entity
- **Authentication**: Token-based API security
- **CORS Support**: Cross-origin resource sharing
- **Error Handling**: Comprehensive error responses
- **Role Management API**: PUT/DELETE handlers untuk role dan permission
- **Multi-database Support**: Integrasi kewer, db_alamat_simple, db_orang

## Teknologi

### Backend
- **PHP 8.0+**: Server-side programming
- **MySQL/MariaDB**: Database management
- **MySQLi**: Database connection dengan prepared statements
- **Session Management**: User authentication

### Frontend
- **Bootstrap 5.3**: UI framework
- **Bootstrap Icons**: Icon library
- **JavaScript**: Client-side validation
- **Responsive Design**: Mobile-friendly

### Testing
- **Frontend-to-Backend (F2E)**: API testing
- **End-to-End (E2E)**: Playwright tests
- **Manual Testing**: Interactive test client

## Instalasi

### Prerequisites
- PHP 8.0 atau lebih tinggi
- MySQL/MariaDB 5.7+
- Web server (Apache/NginX)
- Extensions: mysqli, gd, json

### Langkah 1: Clone Repository
```bash
git clone https://github.com/82080038/kewer.git
cd kewer
```

### Langkah 2: Database Setup (3 database)

Buat 3 database dan import via CLI atau phpMyAdmin:

```bash
# Buat database
/opt/lampp/bin/mysql -u root -proot -e "
  CREATE DATABASE IF NOT EXISTS kewer;
  CREATE DATABASE IF NOT EXISTS db_alamat_simple;
  CREATE DATABASE IF NOT EXISTS db_orang;
"

# Import dari folder database/
/opt/lampp/bin/mysql -u root -proot kewer           < database/kewer.sql
/opt/lampp/bin/mysql -u root -proot db_alamat_simple < database/db_alamat_simple.sql
/opt/lampp/bin/mysql -u root -proot db_orang        < database/db_orang.sql
```

**Via phpMyAdmin:**
1. Buka `http://localhost/phpmyadmin`
2. Buat 3 database: `kewer`, `db_alamat_simple`, `db_orang`
3. Import tiap file SQL dari folder `database/`

### Langkah 3: Install Dependencies
```bash
composer install
```

### Langkah 4: Install Tesseract OCR (Opsional - untuk OCR KTP)
**Linux:**
```bash
sudo apt-get install tesseract-ocr tesseract-ocr-ind
```

**Windows:**
Download dari https://github.com/UB-Mannheim/tesseract/wiki

### Langkah 5: Konfigurasi
Edit file `config/database.php` jika diperlukan:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'kewer');
define('DB_USER', 'root');
define('DB_PASS', 'password_anda');
```

Buat file `.env` di root directory untuk konfigurasi fitur tambahan:
```env
# WhatsApp Configuration
WA_ENABLED=true
WA_PROVIDER=twilio
TWILIO_SID=your_sid
TWILIO_TOKEN=your_token
TWILIO_WHATSAPP_FROM=whatsapp:+14155238886

# Email Configuration
SMTP_HOST=smtp.gmail.com
SMTP_USERNAME=your_email@gmail.com
SMTP_PASSWORD=your_app_password
SMTP_PORT=587
SMTP_FROM=noreply@kewer.com
```

### Langkah 6: Web Server Setup
**Apache:**
```bash
# Copy ke web root
sudo cp -r kewer /var/www/html/
# Set permissions
sudo chown -R www-data:www-data /var/www/html/kewer
```

**XAMPP:**
```bash
# Copy ke htdocs
cp -r kewer /opt/lampp/htdocs/
```

### Langkah 7: Akses Aplikasi
Buka browser: `http://localhost/kewer/login.php`

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

> **Quick Login:** Halaman login memiliki tombol quick login untuk semua role (development mode)

## 3-Database Architecture

### kewer (Main Database - 49 tabel + 3 view)
- **Purpose**: Transaksi koperasi, users, billing, usage, activities
- **Tables**: users, nasabah, pinjaman, angsuran, pembayaran, cabang, jurnal, akun, koperasi_activities, petugas_daerah_tugas, dll
- **Connection**: `$conn` / `query()`

### db_alamat_simple (Address Database - 4 tabel)
- **Purpose**: Referensi lokasi Sumatera Utara
- **Tables**: provinces (1), regencies (33), districts (448), villages (6.101)
- **Connection**: `$conn_alamat` / `query_alamat()`
- **Note**: Sumatera Utara province_id = 3

### db_orang (People Database - 19 tabel + 6 view)
- **Purpose**: Identitas orang + alamat + geospasial nasional
- **Tables**: people, addresses, provinces (38), regencies (541), districts (8K), villages (81K)
- **Connection**: `$conn_orang` / `query_orang()`
- **Cross-DB Links**: users.db_orang_person_id, nasabah.db_orang_user_id, cabang.db_orang_person_id

### Relasi Database
```
cabang (1) -> (N) users
cabang (1) -> (N) nasabah
cabang (1) -> (N) pinjaman
nasabah (1) -> (N) pinjaman
pinjaman (1) -> (N) angsuran
angsuran (1) -> (N) pembayaran
users (1) -> (N) pinjaman
users (1) -> (N) pembayaran

Cross-DB:
kewer.users -> db_orang.people (db_orang_person_id)
kewer.nasabah -> db_orang.people (db_orang_user_id)
kewer.cabang -> db_orang.people (db_orang_person_id)
kewer.location -> db_alamat_simple (province_id, regency_id, district_id, village_id)
```

## API Documentation

### Base URL
```
http://localhost/kewer/api
```

### Authentication
Header: `Authorization: Bearer kewer-api-token-2024`

### Endpoints

#### Dashboard
```
GET /api/dashboard?cabang_id=1
```

#### Nasabah
```
GET    /api/nasabah?cabang_id=1&search=test&status=aktif
POST   /api/nasabah?cabang_id=1
PUT    /api/nasabah?id=123&cabang_id=1
DELETE /api/nasabah?id=123&cabang_id=1
```

#### Pinjaman
```
GET  /api/pinjaman?cabang_id=1&status=aktif
POST /api/pinjaman?cabang_id=1
PUT  /api/pinjaman?id=123&action=approve&cabang_id=1
```

## Testing

### Frontend-to-Backend Testing
```bash
# Jalankan API test
php tests/f2e_test.php

# Buka test client
http://localhost/kewer/tests/api_test_client.html
```

### End-to-End Testing
```bash
cd tests/e2e
npm install
npm test
```

## Fitur Tambahan

### WhatsApp Integration
- **Full Implementation**: Notifikasi WhatsApp dengan dukungan multiple provider
- **Supported Providers**: Twilio, Wablas, Fonnte
- **Features**: Normalisasi nomor telepon, template pesan, graceful fallback
- **Configuration**: Environment variable-based provider selection

### PDF Export
- **Full Implementation**: Export data ke PDF menggunakan DomPDF
- **Features**: HTML table generation, custom styling, A4 landscape format
- **Integration**: Terintegrasi dengan Exporter class

### OCR KTP
- **Full Implementation**: Ekstrak data KTP menggunakan Tesseract OCR
- **Features**: Auto-detect Tesseract path, Indonesian & English language support
- **Extracted Data**: NIK, Nama, Tempat/Tanggal Lahir, Alamat
- **Requirements**: Tesseract OCR installation (Linux/Windows)

### Email Notifications
- **Bos Approval/Rejection**: Notifikasi email otomatis untuk approval bos
- **Features**: HTML & plain text templates, SMTP configuration
- **Integration**: Terintegrasi dengan bos registration workflow

### Multi-Database Integration
- **db_orang**: Active - people + addresses untuk semua users, nasabah, cabang
- **db_alamat_simple**: Active - dropdown lokasi (provinsi, kabupaten, kecamatan, desa)
- **Helper Functions**: people_helper.php, alamat_helper.php, address_helper.php terintegrasi di seluruh aplikasi

### File Upload
- Upload KTP dan foto selfie
- Validasi file type dan size
- Storage management

### Security
- SQL injection prevention dengan prepared statements
- XSS protection dengan input sanitization
- Session hijacking prevention
- Role-based access control

## Simulasi Multi-Role (Puppeteer)

Simulasi realistis Puppeteer untuk menguji semua 8 role selama 2 minggu.

### Prerequisites
```bash
cd /opt/lampp/htdocs/kewer
npm install puppeteer
```

### Jalankan Setup (bos daftar → approve → buat cabang & staff)
```bash
node simulation/run_simulation.js setup
```

### Jalankan Simulasi 2 Minggu (semua role, window terpisah)
```bash
node simulation/run_simulation.js sim
```

### Jalankan Keduanya Sekaligus
```bash
node simulation/run_simulation.js all
```

### File Simulasi
| File | Fungsi |
|------|--------|
| `simulation/run_simulation.js` | Entry point utama |
| `simulation/sim_setup.js` | Fase setup: daftar bos, approve, buat cabang & staff |
| `simulation/sim_daily.js` | Fase simulasi harian 14 hari × 13 role |
| `simulation/sim_helpers.js` | Helper functions, login, wilayah, dll |

### Flow Setup yang Benar
1. Bos daftar via `pages/bos/register.php` → masuk `bos_registrations` pending
2. appOwner approve via `pages/app_owner/approvals.php` → user bos dibuat
3. Bos login → setup HQ via `pages/bos/setup_headquarters.php`
4. Bos tambah cabang cabang via `pages/cabang/tambah.php`
5. Bos tambah semua staff via `pages/petugas/tambah.php`

## Kontribusi

1. Fork repository
2. Buat branch feature (`git checkout -b feature/AmazingFeature`)
3. Commit changes (`git commit -m 'Add some AmazingFeature'`)
4. Push ke branch (`git push origin feature/AmazingFeature`)
5. Open Pull Request

## License

Proyek ini dilisensikan under MIT License - lihat file [LICENSE](LICENSE) untuk detail.

## Support

Jika mengalami masalah:
1. Cek [Issues](https://github.com/82080038/kewer/issues) page
2. Buat new issue dengan detail error
3. Sertakan screenshot jika perlu

## Changelog

### v2.0.0 (2026-05-02)
- ✅ 3-Database architecture refactor (kewer, db_alamat_simple, db_orang)
- ✅ Removed duplicate location tables from kewer
- ✅ Added koperasi_activities, petugas_daerah_tugas tables
- ✅ Cross-DB links: users/nasabah/cabang → db_orang.people
- ✅ appOwner platform layer (billing, usage, AI advisor)
- ✅ 7 appOwner pages (dashboard, approvals, koperasi, billing, usage, ai_advisor, settings)
- ✅ Updated all documentation files
- ✅ Fresh database exports (kewer.sql, db_alamat_simple.sql, db_orang.sql)

### v1.2.0 (2026-04-30)
- ✅ Simulasi multi-role Puppeteer (13 role × 14 hari, headed mode)
- ✅ Flow setup realistis: bos daftar → SA approve → buat cabang & staff
- ✅ Quick login buttons untuk semua role di halaman login
- ✅ Fix bug `validateCSRF` → `validateCsrfToken` di `bos_approvals.php`
- ✅ Database diupdate: 2 cabang, 13 user (superadmin + bos + 11 staff)
- ✅ `dev_credentials` login.php diupdate ke semua username simulasi

### v1.1.0 (2026-04-28)
- ✅ PUT/DELETE handlers implementation di api/roles.php
- ✅ PDF export menggunakan DomPDF
- ✅ OCR KTP dengan Tesseract OCR integration
- ✅ WhatsApp notifications dengan dukungan multiple provider (Twilio, Wablas, Fonnte)
- ✅ Email notifications untuk bos approval/rejection
- ✅ people_helper.php integration ke seluruh aplikasi
- ✅ Multi-database integration (kewer, db_alamat_simple, db_orang)

### v1.0.0 (2026-04-14)
- Initial release
- Complete CRUD operations
- API integration
- Testing suite
- Database schema
- Documentation

## Roadmap

- [ ] Mobile app development
- [ ] Advanced reporting dengan PDF export enhanced
- [ ] SMS notifications (sebagai alternatif WhatsApp)
- [ ] Multi-language support
- [ ] Cloud deployment
- [ ] Machine learning untuk credit scoring

---

**Developed by:** 82080038  
**Repository:** https://github.com/82080038/kewer