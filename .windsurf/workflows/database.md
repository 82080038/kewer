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

### Initial Database Setup
```bash
# Start XAMPP MySQL
cd /opt/lampp
echo "8208" | sudo -S ./lampp start mysql

# Create database
echo "8208" | sudo -S /opt/lampp/bin/mysql -u root -proot -e "CREATE DATABASE IF NOT EXISTS kewer;"

# Import main database schema
cd /opt/lampp/htdocs/kewer
echo "8208" | sudo -S /opt/lampp/bin/mysql -u root -proot kewer < kewer_database.sql

# Import migration files (in order)
echo "8208" | sudo -S /opt/lampp/bin/mysql -u root -proot kewer < database_migration_family_risk.sql
echo "8208" | sudo -S /opt/lampp/bin/mysql -u root -proot kewer < database_migration_kas_bon.sql
echo "8208" | sudo -S /opt/lampp/bin/mysql -u root -proot kewer < database_migration_new_features.sql
echo "8208" | sudo -S /opt/lampp/bin/mysql -u root -proot kewer < database_migration_priority_fixes.sql
```

### Reset Database
```bash
# Drop dan recreate database
echo "8208" | sudo -S /opt/lampp/bin/mysql -u root -proot -e "DROP DATABASE IF EXISTS kewer; CREATE DATABASE kewer;"
cd /opt/lampp/htdocs/kewer
echo "8208" | sudo -S /opt/lampp/bin/mysql -u root -proot kewer < kewer_database.sql
echo "8208" | sudo -S /opt/lampp/bin/mysql -u root -proot kewer < database_migration_family_risk.sql
echo "8208" | sudo -S /opt/lampp/bin/mysql -u root -proot kewer < database_migration_kas_bon.sql
```

### Cek Database Status
```bash
# Lihat semua tabel
echo "8208" | sudo -S /opt/lampp/bin/mysql -u root -proot -e "USE kewer; SHOW TABLES;"

# Cek jumlah tabel
echo "8208" | sudo -S /opt/lampp/bin/mysql -u root -proot -e "USE kewer; SELECT COUNT(*) as total_tables FROM information_schema.tables WHERE table_schema = 'kewer';"

# Cek jumlah data per tabel utama
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
SELECT 'pembayaran', COUNT(*) FROM pembayaran
UNION ALL
SELECT 'family_risk', COUNT(*) FROM family_risk
UNION ALL
SELECT 'kas_bon', COUNT(*) FROM kas_bon;
"
```

### Optimasi Database
```bash
# Optimasi semua tabel
echo "8208" | sudo -S /opt/lampp/bin/mysql -u root -proot kewer -e "OPTIMIZE TABLE users, cabang, nasabah, pinjaman, angsuran, pembayaran, settings, family_risk, kas_bon, kas_petugas, pengeluaran;"
```

### Database Index Optimization (Baru)
```bash
# Jalankan database optimization untuk performa query
cd /opt/lampp/htdocs/kewer
echo "8208" | sudo -S /opt/lampp/bin/mysql -u root -proot kewer < database_optimization_indexes.sql
```

### Cek Index yang Tersedia
```bash
# Lihat semua indexes di database
echo "8208" | sudo -S /opt/lampp/bin/mysql -u root -proot kewer -e "
SELECT 
    TABLE_NAME,
    INDEX_NAME,
    COLUMN_NAME,
    SEQ_IN_INDEX
FROM information_schema.STATISTICS
WHERE TABLE_SCHEMA = 'kewer'
ORDER BY TABLE_NAME, INDEX_NAME, SEQ_IN_INDEX;
"
```

### Cek Koneksi Database
```bash
# Test koneksi
echo "8208" | sudo -S /opt/lampp/bin/mysql -u root -proot -e "SELECT 'Database connection OK' as status;"
```

### Available SQL Files
- `kewer_database.sql` - Main database schema (28 tables)
- `database_migration_family_risk.sql` - Family risk management migration
- `database_migration_kas_bon.sql` - Cash advance management migration
- `database_migration_new_features.sql` - New features migration (indexes)
- `database_migration_priority_fixes.sql` - Priority fixes migration (indexes)
- `database_migration_role_hierarchy.sql` - Role hierarchy and permissions migration
- `database_migration_alamat.sql` - Address data integration migration
- `database_optimization_indexes.sql` - Database index optimization for performance
