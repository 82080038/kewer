# Kewer Simulation Report

## Struktur Aplikasi
- **Model**: Single-office koperasi pinjaman modal pedagang
- **Arsitektur**: PHP 8 + MySQL (3 database: kewer, db_alamat_simple, db_orang)
- **Auth**: Session-based, 9 role levels + appOwner

## Role yang Aktif (Sesuai Database)

| Role | Username | Password | Level |
|------|----------|----------|-------|
| appOwner | appowner | AppOwner2024! | 0 |
| bos | patri | Kewer2024! | 1 |
| manager_pusat | mgr_pusat | Kewer2024! | 3 |
| manager_cabang | mgr_balige | Kewer2024! | 4 |
| admin_pusat | adm_pusat | Kewer2024! | 5 |
| petugas_pusat | ptr_pngr1 | Kewer2024! | 7 |
| petugas_cabang | ptr_pngr2 | Kewer2024! | 8 |
| karyawan | krw_pngr | Kewer2024! | 9 |

## Flow Simulasi Harian

```
[Admin Pusat]  →  Input nasabah baru + pengajuan pinjaman
      ↓
[Bos/Manager]  →  Review dan approve pinjaman
      ↓
[Petugas]      →  Koleksi pembayaran angsuran di lapangan
      ↓
[Manager]      →  Review angsuran & operasional
      ↓
[Bos]          →  Review dashboard & laporan akhir hari
      ↓
[Karyawan]     →  Rekonsiliasi kas harian
      ↓
[AppOwner]     →  Monitoring platform (tidak akses data koperasi)
```

## Cara Menjalankan Simulasi

```bash
cd /opt/lampp/htdocs/kewer

# Jalankan setup dulu (tambah staff jika belum ada)
node simulation/run_simulation.js setup

# Jalankan simulasi 14 hari
node simulation/run_simulation.js sim

# Jalankan keduanya sekaligus
node simulation/run_simulation.js all

# Test login saja
node simulation/test_login.js
```

## File Simulasi

| File | Keterangan |
|------|-----------|
| `sim_helpers.js` | Helper functions: login, browser, wilayah, state |
| `sim_setup.js` | Setup awal: verifikasi bos + tambah staff |
| `sim_daily.js` | Simulasi aktivitas harian 14 hari per role |
| `run_simulation.js` | Entry point utama |
| `test_login.js` | Test login credentials |

## Catatan Penting
- Simulasi menggunakan `test_login=true` di `login.php` (hanya di APP_ENV=development)
- Password semua user koperasi: `Kewer2024!`
- Password appOwner: `AppOwner2024!`
- Single office structure — tidak ada multi-cabang
- kantor_id = 1 (hardcoded untuk struktur single office)
