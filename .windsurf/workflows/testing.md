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

### 3. End-to-End Testing dengan Playwright
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
- [ ] Test session timeout
- [ ] Test role-based access control

#### Nasabah Management
- [ ] Tambah nasabah baru
- [ ] Edit data nasabah
- [ ] Hapus nasabah
- [ ] Upload KTP dan selfie
- [ ] Search nasabah
- [ ] Filter by status

#### Pinjaman Management
- [ ] Buat pengajuan pinjaman
- [ ] Hitung bunga otomatis
- [ ] Approve pinjaman
- [ ] Generate jadwal angsuran
- [ ] Track status pinjaman

#### Angsuran & Pembayaran
- [ ] Lihat jadwal angsuran
- [ ] Input pembayaran
- [ ] Hitung denda keterlambatan
- [ ] Update status pembayaran
- [ ] Lihat riwayat pembayaran

### 5. API Testing dengan curl
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
```

### 6. Database Testing
```bash
# Cek integritas data
echo "8208" | sudo -S /opt/lampp/bin/mysql -u root -proot kewer -e "
SELECT COUNT(*) as total FROM nasabah WHERE status = 'aktif';
SELECT COUNT(*) as total FROM pinjaman WHERE status = 'aktif';
SELECT COUNT(*) as total FROM angsuran WHERE status = 'belum';
"
```
