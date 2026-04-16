---
description: Testing workflow untuk aplikasi Kewer
---

## Testing Workflow

### 1. Frontend-to-Backend (F2E) Testing
```bash
cd /opt/lampp/htdocs/kewer
php tests/f2e_test.php
```

### 2. API Test Client
```bash
# Buka test client di browser
# http://localhost/kewer/tests/api_test_client.html
```

### 3. End-to-End Testing dengan Playwright/Puppeteer
```bash
cd /opt/lampp/htdocs/kewer/tests/e2e
npm install
npm test
```

### 4. Manual Testing Checklist

#### Authentication
- [ ] Login dengan admin credentials
- [ ] Login dengan petugas credentials
- [ ] Test logout functionality
- [ ] Test session timeout (2 jam inactivity)
- [ ] Test role-based access control
- [ ] Test CSRF protection pada semua forms
- [ ] Test quick login development mode
- [ ] Test session timeout message

#### Nasabah Management
- [ ] Tambah nasabah baru
- [ ] Edit data nasabah
- [ ] Hapus nasabah
- [ ] Upload KTP dan selfie
- [ ] Test OCR KTP processing
- [ ] Search nasabah
- [ ] Filter by status
- [ ] Test DataTable.js functionality
- [ ] Test SweetAlert2 alerts
- [ ] Test Select2 dropdowns
- [ ] Test Flatpickr date picker

#### Pinjaman Management
- [ ] Buat pengajuan pinjaman
- [ ] Hitung bunga otomatis
- [ ] Approve pinjaman
- [ ] Generate jadwal angsuran
- [ ] Track status pinjaman
- [ ] Test loan risk tracking

#### Angsuran & Pembayaran
- [ ] Lihat jadwal angsuran
- [ ] Input pembayaran
- [ ] Hitung denda keterlambatan
- [ ] Update status pembayaran
- [ ] Lihat riwayat pembayaran

#### New Features Testing
- [ ] Family risk assessment
- [ ] Kas bon (cash advance) management
- [ ] Kas petugas (staff cash) management
- [ ] Pengeluaran (expense) management
- [ ] Setting bunga (interest rate) management
- [ ] Test audit log functionality

#### Frontend Libraries Testing
- [ ] DataTable.js pagination and sorting
- [ ] SweetAlert2 confirmations and alerts
- [ ] Select2 searchable dropdowns
- [ ] Flatpickr date selection and formatting

### 5. API Testing dengan curl (12 endpoints)
```bash
# Test dashboard API
curl -H "Authorization: Bearer kewer-api-token-2024" \
  "http://localhost/kewer/api/dashboard?cabang_id=1"

# Test nasabah API
curl -H "Authorization: Bearer kewer-api-token-2024" \
  "http://localhost/kewer/api/nasabah?cabang_id=1"

# Test pinjaman API
curl -H "Authorization: Bearer kewer-api-token-2024" \
  "http://localhost/kewer/api/pinjaman?cabang_id=1"

# Test angsuran API
curl -H "Authorization: Bearer kewer-api-token-2024" \
  "http://localhost/kewer/api/angsuran?cabang_id=1"

# Test family_risk API
curl -H "Authorization: Bearer kewer-api-token-2024" \
  "http://localhost/kewer/api/family_risk?cabang_id=1"

# Test kas_bon API
curl -H "Authorization: Bearer kewer-api-token-2024" \
  "http://localhost/kewer/api/kas_bon?cabang_id=1"

# Test kas_petugas API
curl -H "Authorization: Bearer kewer-api-token-2024" \
  "http://localhost/kewer/api/kas_petugas?cabang_id=1"

# Test pengeluaran API
curl -H "Authorization: Bearer kewer-api-token-2024" \
  "http://localhost/kewer/api/pengeluaran?cabang_id=1"

# Test setting_bunga API
curl -H "Authorization: Bearer kewer-api-token-2024" \
  "http://localhost/kewer/api/setting_bunga?cabang_id=1"

# Test auth API
curl -X POST -H "Authorization: Bearer kewer-api-token-2024" \
  "http://localhost/kewer/api/auth" \
  -d "username=admin&password=admin123"

# Test OCR API
curl -X POST -H "Authorization: Bearer kewer-api-token-2024" \
  "http://localhost/kewer/api/ocr" \
  -F "ktp_image=@path/to/ktp.jpg"
```

### 6. Database Testing
```bash
# Cek integritas data
echo "8208" | sudo -S /opt/lampp/bin/mysql -u root -proot kewer -e "
SELECT COUNT(*) as total FROM nasabah WHERE status = 'aktif';
SELECT COUNT(*) as total FROM pinjaman WHERE status = 'aktif';
SELECT COUNT(*) as total FROM angsuran WHERE status = 'belum';
SELECT COUNT(*) as total FROM family_risk;
SELECT COUNT(*) as total FROM kas_bon;
SELECT COUNT(*) as total FROM pengeluaran;
"

# Cek jumlah tabel (harus 28)
echo "8208" | sudo -S /opt/lampp/bin/mysql -u root -proot -e "USE kewer; SELECT COUNT(*) as total_tables FROM information_schema.tables WHERE table_schema = 'kewer';"
```

### 7. Security Testing
- [ ] Test SQL injection prevention
- [ ] Test XSS protection
- [ ] Test CSRF token validation pada semua forms
- [ ] Test session hijacking prevention
- [ ] Test session timeout (2 jam inactivity)
- [ ] Test role-based access control consistency
- [ ] Test file upload security
- [ ] Test API rate limiting (60 requests/minute)
- [ ] Test input validation layer
- [ ] Test error handling global
- [ ] Test permission check pada semua halaman
- [ ] Test quick login hanya di development mode

### 8. Performance Testing
- [ ] Test database query performance
- [ ] Test DataTable.js with large datasets
- [ ] Test API response times
- [ ] Test page load times
