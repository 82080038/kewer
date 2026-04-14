# Kewer - Sistem Pinjaman Modal Pedagang

Aplikasi web berbasis PHP untuk mengelola pinjaman modal pedagang dengan fitur lengkap manajemen nasabah, pinjaman, angsuran, dan pembayaran.

## [![GitHub license](https://img.shields.io/github/license/82080038/kewer.svg)](https://github.com/82080038/kewer/blob/main/LICENSE)
## [![PHP version](https://img.shields.io/badge/PHP-8.0%2B-blue.svg)](https://php.net/)
## [![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3.0-purple.svg)](https://getbootstrap.com/)

## Fitur Utama

### Manajemen Pengguna
- **Multi-role Access**: Superadmin, Admin, Petugas
- **Authentication System**: Login/logout dengan session management
- **Cabang Management**: Multi-cabang dengan akses terbatas

### Manajemen Nasabah
- **Data Nasabah Lengkap**: KTP, alamat, kontak, jenis usaha
- **Upload Dokumen**: KTP dan selfie verification
- **Status Management**: Aktif, nonaktif, blacklist
- **Search & Filter**: Pencarian berdasarkan nama, KTP, telepon

### Manajemen Pinjaman
- **Pengajuan Pinjaman**: Form lengkap dengan validasi
- **Loan Calculator**: Perhitungan otomatis bunga dan angsuran
- **Approval Workflow**: Proses persetujuan oleh admin
- **Status Tracking**: Pengajuan, disetujui, aktif, lunas, ditolak

### Manajemen Angsuran
- **Jadwal Angsuran**: Otomatis generate berdasarkan tenor
- **Pembayaran**: Input pembayaran dengan denda keterlambatan
- **Late Payment Detection**: Tracking tunggakan otomatis
- **Payment History**: Riwayat pembayaran lengkap

### Dashboard & Analytics
- **Real-time Statistics**: Total nasabah, pinjaman aktif, outstanding
- **Recent Activities**: Log aktivitas terkini
- **Cabang Selector**: Filter berdasarkan cabang (admin)

### API Integration
- **RESTful API**: CRUD operations untuk semua entity
- **Authentication**: Token-based API security
- **CORS Support**: Cross-origin resource sharing
- **Error Handling**: Comprehensive error responses

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

### Langkah 2: Database Setup
1. Buat database `kewer` di phpMyAdmin
2. Import file `kewer_database_complete.sql`
3. Verifikasi semua tabel terbuat (7 tabel)

### Langkah 3: Konfigurasi
Edit file `config/database.php`:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'kewer');
define('DB_USER', 'root');
define('DB_PASS', 'password_anda');
```

### Langkah 4: Web Server Setup
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

### Langkah 5: Akses Aplikasi
Buka browser: `http://localhost/kewer/login.php`

## Default Credentials

| Role | Username | Password |
|------|----------|----------|
| Superadmin | admin | admin123 |
| Petugas | petugas1 | petugas123 |

## Struktur Database

### Tabel Utama
- `users` - Data pengguna dan authentication
- `cabang` - Data cabang/kantor
- `nasabah` - Data nasabah/pelanggan
- `pinjaman` - Data pinjaman
- `angsuran` - Jadwal angsuran
- `pembayaran` - Riwayat pembayaran
- `settings` - Konfigurasi sistem

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
- Placeholder untuk notifikasi WhatsApp
- Template pesan untuk pengingat pembayaran
- Integration ready untuk WhatsApp API

### File Upload
- Upload KTP dan foto selfie
- Validasi file type dan size
- Storage management

### Security
- SQL injection prevention dengan prepared statements
- XSS protection dengan input sanitization
- Session hijacking prevention
- Role-based access control

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

### v1.0.0 (2026-04-14)
- Initial release
- Complete CRUD operations
- API integration
- Testing suite
- Database schema
- Documentation

## Roadmap

- [ ] Mobile app development
- [ ] Advanced reporting
- [ ] SMS notifications
- [ ] Multi-language support
- [ ] Cloud deployment

---

**Developed by:** 82080038  
**Repository:** https://github.com/82080038/kewer  
**Live Demo:** (Coming soon)