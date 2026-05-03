# Kewer Application Analysis

> **Terakhir diperbarui**: 3 Mei 2026
> **Versi Aplikasi**: v2.0.0

## Overview
**Project Name**: Kewer - Koperasi Warga Ekonomi Rakyat
**Type**: Sistem Manajemen Pinjaman untuk Koperasi Pasar / Bank Keliling
**Business Model**: Usaha pinjaman pribadi — meminjamkan uang ke pedagang pasar/UMKM dengan angsuran harian, mingguan, atau bulanan. Petugas keliling mengutip angsuran langsung ke lokasi nasabah.
**Target Users**: Pedagang pasar, warung, pelaku UMKM informal
**Repository**: https://github.com/82080038/kewer.git

> **PENTING**: Ini BUKAN koperasi simpan pinjam formal. Tidak ada modul simpanan, SHU, atau laporan SAK EP. Fokus utama adalah pencairan pinjaman dan kutipan angsuran lapangan.

## Business Model
- **Target**: Market vendors (pedagang pasar) needing micro-loans
- **Loan Types**: Daily (harian), Weekly (mingguan), Monthly (bulanan) repayment frequencies
- **Interest**: Fixed percentage per month (typically 2-5%)
- **Collection**: Field officers (petugas) collect payments from customers in the field
- **Platform**: Multi-tenant — setiap Bos punya koperasi sendiri, appOwner mengelola platform

## Architecture
- **Backend**: PHP 8.0+ with MySQL/MariaDB
- **Frontend**: Bootstrap 5.3 + Vanilla JavaScript
- **Database**: 3 database terpisah (kewer, db_alamat_simple, db_orang)
- **Authentication**: Session-based with role-based permissions
- **Platform**: appOwner layer (billing, usage tracking, AI advisor)
- **Audit Trail**: CRUD operations logged to audit_log table

---

## 3-Database Architecture

```
┌─────────────────────────────────┐
│  kewer (49 tabel + 3 view)      │  Transaksi koperasi, users, billing, usage
│  $conn → query()                │
│  ├── users, permissions, roles  │  Auth & RBAC
│  ├── nasabah, pinjaman          │  Bisnis inti
│  ├── angsuran, pembayaran       │  Cicilan & pembayaran
│  ├── jurnal, akun               │  Akuntansi
│  ├── billing_plans, invoices    │  Platform (appOwner)
│  ├── usage_log, ai_advice       │  Usage tracking & AI
│  ├── koperasi_activities        │  Log aktivitas koperasi
│  └── petugas_daerah_tugas       │  Daerah tugas petugas
└────────┬────────────────────────┘
         │ province_id, regency_id, district_id, village_id
         ▼
┌─────────────────────────────────┐
│  db_alamat_simple (4 tabel)     │  Referensi lokasi Sumatera Utara
│  $conn_alamat → query_alamat()  │
│  └── provinces → regencies      │  1 provinsi, 33 kab, 448 kec, 6.101 desa
│      → districts → villages     │
└─────────────────────────────────┘
         ▲ cross-DB JOIN
┌────────┴────────────────────────┐
│  db_orang (19 tabel + 6 view)   │  Identitas orang + geospasial nasional
│  $conn_orang → query_orang()    │
│  ├── people                     │  Data orang (link ke kewer.users/nasabah)
│  ├── addresses                  │  Alamat per orang
│  ├── provinces → regencies      │  38 prov, 541 kab, 8K kec, 81K desa
│  │   → districts → villages     │
│  └── GPS, boundaries, metadata  │  Geospasial
└─────────────────────────────────┘
```

### Cross-DB Links
- `kewer.users.db_orang_person_id` → `db_orang.people.id`
- `kewer.nasabah.db_orang_user_id` → `db_orang.people.id`
- `kewer.cabang.db_orang_person_id` → `db_orang.people.id`
- Models (Nasabah.php, Cabang.php) use `LEFT JOIN db_alamat_simple.provinces` etc.

### db_alamat_simple Notes
- Sumatera Utara: **id=3** (code=12). Semua regencies punya province_id=3.

---

## Role Hierarchy (9 Levels + appOwner)

```
appOwner          — Platform owner (billing, usage, AI advisor, approvals)
  └── Credentials: appowner / AppOwner2024!

Level 1: Bos (Pemilik Usaha)
  └── Wajib punya kantor pusat, delegasi permission, kelola cabang
Level 2: Manager Pusat — koordinasi lintas cabang
Level 3: Manager Cabang — operasi cabang tunggal
Level 4: Admin Pusat — administrasi lintas cabang
Level 5: Admin Cabang — administrasi cabang tunggal
Level 6: Petugas Pusat — kutipan lapangan lintas cabang
Level 7: Petugas Cabang — kutipan lapangan cabang tunggal
Level 8: Karyawan — berdasarkan delegated permissions dari bos
```

### Test Users (password: Kewer2024!)
- **patri** (bos, cabang 1), mgr_pusat, mgr_pangururan, mgr_balige
- **adm_pusat**, adm_pangururan, adm_balige
- **ptr_pngr1** (petugas_pusat), ptr_pngr2 (petugas_cabang), ptr_blg1
- **krw_pngr** (karyawan), krw_blg

### 2 Branches
- Cabang 1: Kantor Pusat Pangururan (HQ)
- Cabang 2: Cabang Balige

---

## Critical Business Requirements (Koperasi Pasar Model)

### 🔴 KRITIS - Inti Bisnis (COMPLETED)

1. **Frekuensi Angsuran** ✅ — Harian, mingguan, bulanan
2. **Denda Otomatis** ✅ — Grace period, auto-calc, waive by manager
3. **Blacklist UI** ✅ — Kelola blacklist, validasi pinjaman aktif

### 🟡 SEDANG - Operasional Lapangan (PARTIAL)

4. Cetak Kwitansi & Kartu Angsuran — PARTIAL (cetak_kwitansi.php)
5. Notifikasi WhatsApp — NOT IMPLEMENTED
6. Rute Harian Petugas — NOT IMPLEMENTED
7. Dashboard Kinerja Petugas — NOT IMPLEMENTED

### 🟢 RENDAH - Tata Kelola (PENDING)

8. Audit Trail UI
9. Manajemen Jaminan UI
10. Pinjaman Top-Up
11. Laporan Laba Rugi Sederhana
12. Credit Scoring Sederhana
13. Integrasi Pembayaran Digital

## Database Schema — kewer (49 base tables + 3 views)

### Operational Tables
| # | Tabel | Fungsi |
|---|-------|--------|
| 1 | users | Auth, role, cabang_id, db_orang_person_id |
| 2 | cabang | Kantor pusat & cabang, db_orang_person_id |
| 3 | nasabah | Data nasabah, db_orang_user_id/address_id |
| 4 | pinjaman | Pengajuan & tracking pinjaman |
| 5 | angsuran | Jadwal cicilan |
| 6 | pembayaran | Riwayat pembayaran |
| 7 | settings | Konfigurasi sistem |
| 8 | audit_log | Activity logging |
| 9 | family_risk | Penilaian risiko keluarga |
| 10 | nasabah_family_link | Relasi keluarga nasabah |
| 11 | kas_bon | Kasbon karyawan |
| 12 | kas_bon_potongan | Potongan kasbon |
| 13 | kas_petugas | Kas petugas lapangan |
| 14 | kas_petugas_setoran | Setoran kas petugas |
| 15 | pengeluaran | Pengeluaran operasional |
| 16 | loan_risk_log | Tracking risiko pinjaman |
| 17 | field_officer_activities | Aktivitas petugas + daerah_province/regency/district/village_id |
| 18 | daily_cash_reconciliation | Rekonsiliasi kas harian |
| 19 | consolidated_reports | Laporan konsolidasi |
| 20 | transaksi_log | Log transaksi |
| 21 | blacklist_log | Log blacklist nasabah |
| 22 | nasabah_orang_mapping | Mapping nasabah ↔ db_orang |

### Organizational & Auth Tables
| # | Tabel | Fungsi |
|---|-------|--------|
| 23 | permissions | Semua permission tersedia |
| 24 | role_permissions | Permission per role |
| 25 | user_permissions | Override permission per user |
| 26 | permission_audit_log | Audit perubahan permission |
| 27 | bos_registrations | Registrasi bos + province/regency/district/village_id |
| 28 | delegated_permissions | Delegasi permission dari bos |
| 29 | koperasi_activities | Log aktivitas koperasi (16 kategori + JSON) |
| 30 | petugas_daerah_tugas | Daerah tugas petugas (district/village level) |

### Platform Tables (appOwner)
| # | Tabel | Fungsi |
|---|-------|--------|
| 31 | billing_plans | 5 paket billing |
| 32 | koperasi_billing | Assign paket ke bos |
| 33 | koperasi_invoices | Invoice bulanan |
| 34 | usage_log | Log per-request |
| 35 | usage_daily_summary | Aggregate harian |
| 36 | ai_advice | AI advisor per koperasi |

### Accounting Tables
| # | Tabel | Fungsi |
|---|-------|--------|
| 37 | akun | Chart of accounts |
| 38 | jurnal | Jurnal entries |
| 39 | jurnal_detail | Detail jurnal |
| 40 | labarugi | Laba rugi |
| 41 | neraca | Neraca |
| 42 | neraca_saldo | Neraca saldo |

### Reference Tables
| # | Tabel | Fungsi |
|---|-------|--------|
| 43 | ref_jaminan_tipe | Tipe jaminan |
| 44 | ref_jenis_usaha | Jenis usaha |
| 45 | ref_kategori_pengeluaran | Kategori pengeluaran |
| 46 | ref_metode_pembayaran | Metode pembayaran |
| 47 | ref_roles | Daftar role |
| 48 | ref_status_pinjaman | Status pinjaman |
| 49 | setting_bunga | Setting bunga |
| — | auto_confirm_settings | Auto-confirm pinjaman |
| — | denda_settings | Setting denda |
| — | setting_denda | Setting denda per cabang |

### Views (3)
- v_karyawan_kasbon, v_kasbon_summary, v_laporan_kas_harian

---

## Application Structure

### API Endpoints (25 files)
| File | Fungsi |
|------|--------|
| alamat.php | Cascade dropdown lokasi (db_alamat_simple) |
| accounting.php | Akuntansi (jurnal, ledger, neraca) |
| angsuran.php | CRUD angsuran |
| auth.php | Login/logout API |
| auto_confirm_settings.php | Auto-confirm pinjaman |
| bos_registration.php | Registrasi & approval bos |
| branch_managers.php | Assign manager ke cabang |
| cabang.php | CRUD cabang |
| daily_cash_reconciliation.php | Rekonsiliasi kas |
| dashboard.php | Statistik dashboard |
| delegated_permissions.php | Delegasi permission |
| family_risk.php | Penilaian risiko keluarga |
| field_officer_activities.php | Aktivitas petugas |
| kas_bon.php | Kasbon karyawan |
| kas_petugas.php | Kas petugas |
| kas_petugas_setoran.php | Setoran petugas |
| nasabah.php | CRUD nasabah |
| nasabah_blacklist.php | Blacklist nasabah |
| ocr.php | OCR KTP |
| pembayaran.php | CRUD pembayaran |
| pengeluaran.php | CRUD pengeluaran |
| pinjaman.php | CRUD pinjaman |
| roles.php | Manajemen role & permission |
| setting_bunga.php | Setting bunga |

### Includes (24 files)
| File | Fungsi |
|------|--------|
| functions.php | Core business logic |
| people_helper.php | CRUD db_orang.people + addresses |
| alamat_helper.php | Dropdown lokasi (query_alamat) |
| address_helper.php | Format alamat |
| accounting_helper.php | Helper akuntansi |
| bunga_calculator.php | Kalkulator bunga |
| usage_tracker.php | Track API/render per koperasi |
| csrf.php | CSRF protection |
| validation.php | Input validation |
| error_handler.php | Error handling |
| database_class.php | Database wrapper |
| datatable_helper.php | DataTable.js |
| sweetalert_helper.php | SweetAlert2 |
| select2_helper.php | Select2 |
| flatpickr_helper.php | Flatpickr |
| family_risk.php | Logika risiko keluarga |
| kas_bon.php | Logika kasbon |
| kas_petugas.php | Logika kas petugas |
| pengeluaran.php | Logika pengeluaran |
| ocr_ktp.php | OCR KTP |
| jwt_auth.php | JWT auth |
| jwt_helper.php | JWT helper |
| auto_confirm.php | Auto-confirm logic |
| sidebar.php | Dynamic sidebar rendering |

### Page Modules (24 directories)
angsuran, app_owner, audit, auto_confirm, bos, cabang, cash_reconciliation, family_risk, field_activities, jaminan, kas_bon, kas_petugas, kinerja, laporan, nasabah, pembayaran, pengeluaran, permissions, petugas, pinjaman, rute_harian, setting_bunga, superadmin, users

### appOwner Pages (7)
dashboard, approvals, koperasi, billing, usage, ai_advisor, settings

---

## Technology Stack

- **Backend**: PHP 8.0+, MySQL/MariaDB, MySQLi prepared statements
- **Frontend**: Bootstrap 5.3, DataTable.js 1.13.6, SweetAlert2 v11, Select2 4.1.0, Flatpickr 4.6.13
- **Testing**: F2E (PHP), E2E (Playwright/Puppeteer)
- **Server**: XAMPP on Linux (Apache 2.4.58, MariaDB 10.4.32, PHP 8.2.12)

## Security Features
- SQL injection prevention (prepared statements)
- CSRF protection (semua forms)
- Session timeout (2 jam)
- Rate limiting API (60 req/min)
- Input validation layer
- Role-based access control
- Password hashing (bcrypt)
- Error logging

## Configuration
- **Host**: localhost
- **Path**: /opt/lampp/htdocs/kewer
- **MySQL**: root / root
- **SUDO**: 8208

## Database Files (database/)
- `kewer.sql` — Full export database kewer (49 tabel + 3 views + data)
- `db_alamat_simple.sql` — Export db_alamat_simple (4 tabel + data Sumut)
- `db_orang.sql` — Export db_orang (19 tabel + 6 views + geospasial nasional)
