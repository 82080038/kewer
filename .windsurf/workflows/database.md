---
description: Database operations untuk aplikasi Kewer
---

## Database Operations

### Backup Database
```bash
# Export database
cd /opt/lampp/htdocs/kewer
echo "8208" | sudo -S /opt/lampp/bin/mysqldump -u root -proot kewer > backup_$(date +%Y%m%d_%H%M%S).sql
```

### Restore Database
```bash
# Restore dari backup
echo "8208" | sudo -S /opt/lampp/bin/mysql -u root -proot kewer < backup_file.sql
```

### Reset Database
```bash
# Drop dan recreate database
echo "8208" | sudo -S /opt/lampp/bin/mysql -u root -proot -e "DROP DATABASE IF EXISTS kewer; CREATE DATABASE kewer;"
cd /opt/lampp/htdocs/kewer
echo "8208" | sudo -S /opt/lampp/bin/mysql -u root -proot kewer < database.sql
```

### Cek Database Status
```bash
# Lihat semua tabel
echo "8208" | sudo -S /opt/lampp/bin/mysql -u root -proot -e "USE kewer; SHOW TABLES;"

# Cek jumlah data per tabel
echo "8208" | sudo -S /opt/lampp/bin/mysql -u root -proot -e "
USE kewer;
SELECT 'users' as table_name, COUNT(*) as count FROM users
UNION ALL
SELECT 'cabang', COUNT(*) FROM cabang
UNION ALL
SELECT 'nasabah', COUNT(*) FROM nasabah
UNION ALL
SELECT 'pinjaman', COUNT(*) FROM pinjaman
UNION ALL
SELECT 'angsuran', COUNT(*) FROM angsuran
UNION ALL
SELECT 'pembayaran', COUNT(*) FROM pembayaran;
"
```

### Optimasi Database
```bash
# Optimasi semua tabel
echo "8208" | sudo -S /opt/lampp/bin/mysql -u root -proot kewer -e "OPTIMIZE TABLE users, cabang, nasabah, pinjaman, angsuran, pembayaran, settings;"
```

### Cek Koneksi Database
```bash
# Test koneksi
echo "8208" | sudo -S /opt/lampp/bin/mysql -u root -proot -e "SELECT 'Database connection OK' as status;"
```
