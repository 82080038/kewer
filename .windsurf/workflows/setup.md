---
description: Setup dan konfigurasi awal aplikasi Kewer
---

## Setup Aplikasi Kewer

### 1. Clone Repository
```bash
cd /opt/lampp/htdocs
git clone https://github.com/82080038/kewer.git
cd kewer
```

### 2. Start XAMPP
```bash
echo "8208" | sudo -S /opt/lampp/lampp start
```

### 3. Database Setup (3 database)
```bash
cd /opt/lampp/htdocs/kewer

# Buat ketiga database
/opt/lampp/bin/mysql -u root -proot -e "CREATE DATABASE IF NOT EXISTS kewer;"
/opt/lampp/bin/mysql -u root -proot -e "CREATE DATABASE IF NOT EXISTS db_alamat_simple;"
/opt/lampp/bin/mysql -u root -proot -e "CREATE DATABASE IF NOT EXISTS db_orang;"

# Import dari folder database/
/opt/lampp/bin/mysql -u root -proot kewer < database/kewer.sql
/opt/lampp/bin/mysql -u root -proot db_alamat_simple < database/db_alamat_simple.sql
/opt/lampp/bin/mysql -u root -proot db_orang < database/db_orang.sql
```

### 4. Verifikasi Database
```bash
# Cek jumlah tabel (kewer: 52, alamat: 4, orang: 25)
/opt/lampp/bin/mysql -u root -proot -e "
SELECT 'kewer' as db, COUNT(*) as tables FROM information_schema.TABLES WHERE TABLE_SCHEMA='kewer'
UNION ALL SELECT 'db_alamat_simple', COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA='db_alamat_simple'
UNION ALL SELECT 'db_orang', COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA='db_orang';
"
```

### 5. Install Dependencies
```bash
cd /opt/lampp/htdocs/kewer
composer install
```

### 6. Set Permissions
```bash
echo "8208" | sudo -S chmod -R 755 /opt/lampp/htdocs/kewer
echo "8208" | sudo -S mkdir -p uploads
echo "8208" | sudo -S chmod 777 uploads
```

### 7. Akses Aplikasi
Buka browser: http://localhost/kewer/login.php

### Credentials

**appOwner (Platform Owner):**
- Username: appowner / Password: AppOwner2024!

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

### System Requirements
- PHP 8.0+ (extensions: mysqli, gd, mbstring, curl, json)
- MySQL/MariaDB 5.7+
- Apache web server (XAMPP recommended)
- Composer (PHP dependency manager)
