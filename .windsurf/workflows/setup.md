---
description: Setup dan konfigurasi awal aplikasi Kewer
---

## Setup Aplikasi Kewer

### 1. Start XAMPP
```bash
cd /opt/lampp
echo "8208" | sudo -S ./lampp start
```

### 2. Database Setup
```bash
# Set MySQL password ke root
echo "8208" | sudo -S /opt/lampp/bin/mysql -u root -p'\ -e "ALTER USER 'root'@'localhost' IDENTIFIED BY 'root'; FLUSH PRIVILEGES;"

# Buat database
echo "8208" | sudo -S /opt/lampp/bin/mysql -u root -proot -e "CREATE DATABASE IF NOT EXISTS kewer;"

# Import database schema
cd /opt/lampp/htdocs/kewer
echo "8208" | sudo -S /opt/lampp/bin/mysql -u root -proot kewer < database.sql
```

### 3. Verifikasi Database
```bash
echo "8208" | sudo -S /opt/lampp/bin/mysql -u root -proot -e "USE kewer; SHOW TABLES;"
```

### 4. Install Dependencies
```bash
cd /opt/lampp/htdocs/kewer
composer install
```

### 5. Set Permissions
```bash
echo "8208" | sudo -S chmod -R 755 /opt/lampp/htdocs/kewer
echo "8208" | sudo -S mkdir -p uploads
echo "8208" | sudo -S chmod 777 uploads
```

### 6. Akses Aplikasi
Buka browser: http://localhost/kewer/login.php

### Default Credentials
- Username: admin
- Password: password
