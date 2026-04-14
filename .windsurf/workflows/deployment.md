---
description: Deployment workflow untuk aplikasi Kewer
---

## Deployment Workflow

### 1. Pre-Deployment Checklist
- [ ] Backup current database
- [ ] Test all features locally
- [ ] Update configuration if needed
- [ ] Check file permissions
- [ ] Verify dependencies

### 2. Backup Database Sebelum Deploy
```bash
cd /opt/lampp/htdocs/kewer
echo "8208" | sudo -S /opt/lampp/bin/mysqldump -u root -proot kewer > pre_deploy_backup_$(date +%Y%m%d_%H%M%S).sql
```

### 3. Update Configuration
```bash
# Edit config/database.php jika perlu
nano config/database.php
```

### 4. Update Dependencies
```bash
cd /opt/lampp/htdocs/kewer
composer update
```

### 5. Clear Cache (jika ada)
```bash
# Hapus file cache temporary jika ada
echo "8208" | sudo -S rm -rf uploads/temp/*
```

### 6. Set File Permissions
```bash
echo "8208" | sudo -S chmod -R 755 /opt/lampp/htdocs/kewer
echo "8208" | sudo -S chmod 777 uploads
echo "8208" | sudo -S chmod 644 config/*.php
echo "8208" | sudo -S chmod 644 includes/*.php
```

### 7. Restart XAMPP Services
```bash
cd /opt/lampp
echo "8208" | sudo -S ./lampp restart
```

### 8. Verify Deployment
```bash
# Cek status XAMPP
cd /opt/lampp
echo "8208" | sudo -S ./lampp status

# Test database connection
echo "8208" | sudo -S /opt/lampp/bin/mysql -u root -proot -e "USE kewer; SELECT COUNT(*) as count FROM users;"

# Akses aplikasi di browser
# http://localhost/kewer/login.php
```

### 9. Post-Deployment Testing
- [ ] Test login functionality
- [ ] Test dashboard loading
- [ ] Test CRUD operations
- [ ] Test API endpoints
- [ ] Check error logs

### 10. Monitor Error Logs
```bash
# Lihat server log
tail -f /opt/lampp/logs/error_log

# Lihat application log
tail -f server.log
```

### Rollback Procedure (jika ada masalah)
```bash
# Restore database dari backup
echo "8208" | sudo -S /opt/lampp/bin/mysql -u root -proot kewer < pre_deploy_backup_YYYYMMDD_HHMMSS.sql

# Restart services
cd /opt/lampp
echo "8208" | sudo -S ./lampp restart
```
