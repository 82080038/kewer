---
description: Database operations untuk aplikasi Kewer (3 database)
---

## Database Operations

> Kewer menggunakan 3 database: `kewer`, `db_alamat_simple`, `db_orang`
> Semua SQL dump ada di folder `database/`

### Backup Semua Database
```bash
cd /opt/lampp/htdocs/kewer
# Export ketiga database ke folder database/
/opt/lampp/bin/mysqldump -u root -proot --routines --triggers kewer > database/kewer.sql
/opt/lampp/bin/mysqldump -u root -proot --routines --triggers db_alamat_simple > database/db_alamat_simple.sql
/opt/lampp/bin/mysqldump -u root -proot --routines --triggers db_orang > database/db_orang.sql
```

### Restore Semua Database
```bash
cd /opt/lampp/htdocs/kewer
# Create databases
/opt/lampp/bin/mysql -u root -proot -e "CREATE DATABASE IF NOT EXISTS kewer;"
/opt/lampp/bin/mysql -u root -proot -e "CREATE DATABASE IF NOT EXISTS db_alamat_simple;"
/opt/lampp/bin/mysql -u root -proot -e "CREATE DATABASE IF NOT EXISTS db_orang;"

# Import dari folder database/
/opt/lampp/bin/mysql -u root -proot kewer < database/kewer.sql
/opt/lampp/bin/mysql -u root -proot db_alamat_simple < database/db_alamat_simple.sql
/opt/lampp/bin/mysql -u root -proot db_orang < database/db_orang.sql
```

### Reset Database
```bash
cd /opt/lampp/htdocs/kewer
/opt/lampp/bin/mysql -u root -proot -e "DROP DATABASE IF EXISTS kewer; CREATE DATABASE kewer;"
/opt/lampp/bin/mysql -u root -proot -e "DROP DATABASE IF EXISTS db_alamat_simple; CREATE DATABASE db_alamat_simple;"
/opt/lampp/bin/mysql -u root -proot -e "DROP DATABASE IF EXISTS db_orang; CREATE DATABASE db_orang;"
/opt/lampp/bin/mysql -u root -proot kewer < database/kewer.sql
/opt/lampp/bin/mysql -u root -proot db_alamat_simple < database/db_alamat_simple.sql
/opt/lampp/bin/mysql -u root -proot db_orang < database/db_orang.sql
```

### Cek Database Status
```bash
# Cek jumlah tabel ketiga database (base tables only)
/opt/lampp/bin/mysql -u root -proot -e "
SELECT 'kewer' as db, COUNT(*) as tables FROM information_schema.TABLES WHERE TABLE_SCHEMA='kewer' AND TABLE_TYPE='BASE TABLE'
UNION ALL SELECT 'db_alamat_simple', COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA='db_alamat_simple' AND TABLE_TYPE='BASE TABLE'
UNION ALL SELECT 'db_orang', COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA='db_orang' AND TABLE_TYPE='BASE TABLE';
"

# Cek data utama
/opt/lampp/bin/mysql -u root -proot kewer -e "
SELECT 'users' as tbl, COUNT(*) as cnt FROM users
UNION ALL SELECT 'cabang', COUNT(*) FROM cabang
UNION ALL SELECT 'nasabah', COUNT(*) FROM nasabah
UNION ALL SELECT 'pinjaman', COUNT(*) FROM pinjaman
UNION ALL SELECT 'angsuran', COUNT(*) FROM angsuran
UNION ALL SELECT 'pembayaran', COUNT(*) FROM pembayaran
UNION ALL SELECT 'koperasi_activities', COUNT(*) FROM koperasi_activities;
"

# Cek cross-DB links
/opt/lampp/bin/mysql -u root -proot -e "
SELECT 'users linked' as item, COUNT(*) as cnt FROM kewer.users WHERE db_orang_person_id IS NOT NULL
UNION ALL SELECT 'nasabah linked', COUNT(*) FROM kewer.nasabah WHERE db_orang_user_id IS NOT NULL
UNION ALL SELECT 'cabang linked', COUNT(*) FROM kewer.cabang WHERE db_orang_person_id IS NOT NULL
UNION ALL SELECT 'people records', COUNT(*) FROM db_orang.people
UNION ALL SELECT 'addresses', COUNT(*) FROM db_orang.addresses;
"
```

### Cek Koneksi Database
```bash
/opt/lampp/bin/mysql -u root -proot -e "SELECT 'Connection OK' as status;"
```

### Database Files (database/)
- `kewer.sql` — Full export kewer (64 tabel + 3 views + data)
- `db_alamat_simple.sql` — Referensi lokasi Sumut (4 tabel, 1 prov + 33 kab + 448 kec + 6.101 desa)
- `db_orang.sql` — Identitas orang + geospasial nasional (19 tabel + 6 views)

### 3-Database Architecture
| DB | Koneksi | Fungsi |
|---|---|---|
| `kewer` | `$conn` → `query()` | Transaksi koperasi, users, billing, usage, activities |
| `db_alamat_simple` | `$conn_alamat` → `query_alamat()` | Referensi lokasi Sumut |
| `db_orang` | `$conn_orang` → `query_orang()` | Identitas orang + alamat + geospasial nasional |

### Cross-DB Links
- `kewer.users.db_orang_person_id` → `db_orang.people.id`
- `kewer.nasabah.db_orang_user_id` → `db_orang.people.id`
- `kewer.cabang.db_orang_person_id` → `db_orang.people.id`
- Models use `LEFT JOIN db_alamat_simple.provinces` etc. for location names
- `db_alamat_simple` Sumut: province **id=3** (code=12)
