# Manager Pusat Simulation

## Overview
Manager Pusat adalah role opsional yang mengelola operasional lintas cabang untuk bos. Jika tidak ada, bos dapat delegate permissions ke role lain.

## Login Credentials
- **Username**: manager_pusat (ditambahkan oleh bos)
- **Password**: password (diset saat pembuatan user)

## Access Scope
- Akses: Kantor pusat dan semua cabang (data konsolidasi)
- Izin: Manajemen operasional lintas cabang
- Data Visibility: Data dari seluruh cabang milik bos

## Daily Activities

### Morning Routine
1. **Dashboard Review**
   - Lihat statistik konsolidasi dari seluruh cabang
   - Total nasabah, pinjaman aktif, outstanding
   - Aktivitas terbaru di seluruh cabang

2. **Cabang Monitoring**
   - Navigasi ke `pages/cabang/index.php`
   - Lihat status semua cabang
   - Identifikasi cabang yang butuh perhatian

### Mid-Day Activities

1. **Nasabah Management**
   - Navigasi ke `pages/nasabah/index.php`
   - Lihat nasabah dari seluruh cabang
   - Tambah nasabah baru
   - Edit data nasabah
   - Review status nasabah

2. **Pinjaman Management**
   - Navigasi ke `pages/pinjaman/index.php`
   - Review pinjaman dari seluruh cabang
   - Setujui pinjaman (jika memiliki permission)
   - Monitor status pinjaman

3. **Karyawan Management**
   - Navigasi ke `pages/petugas/index.php`
   - Lihat karyawan di seluruh cabang
   - Tambah karyawan baru (jika memiliki permission)
   - Review role karyawan

### Afternoon Activities
1. **Angsuran Monitoring**
   - Navigasi ke `pages/angsuran/index.php`
   - Lihat jadwal angsuran dari seluruh cabang
   - Monitor pembayaran tertunggak
   - Review kinerja koleksi

2. **Laporan Review**
   - Navigasi ke `pages/laporan/index.php`
   - Lihat laporan konsolidasi
   - Review kinerja per cabang
   - Identifikasi tren

## Weekly Activities
1. **Cabang Performance Review**
   - Review kinerja setiap cabang
   - Identifikasi cabang dengan performa terbaik/terburuk
   - Buat rekomendasi improvement

2. **Karyawan Evaluation**
   - Review performa karyawan di seluruh cabang
   - Identifikasi training needs
   - Rekomendasikan promosi/demosi

## Monthly Activities
1. **Strategic Planning**
   - Review target vs aktual
   - Plan strategi untuk bulan berikutnya
   - Identifikasi area untuk improvement

2. **Compliance Check**
   - Verifikasi kepatuhan cabang terhadap SOP
   - Review audit trail
   - Identifikasi area risiko

## Key Permissions
- Dapat melihat data konsolidasi dari seluruh cabang
- Dapat mengelola nasabah di seluruh cabang
- Dapat mengelola pinjaman di seluruh cabang (tergantung permission)
- Dapat melihat laporan konsolidasi
- Dapat menambah karyawan (tergantung delegated permission dari bos)

## Important Notes
- Manager Pusat adalah role OPSIONAL
- Jika tidak ada, bos dapat delegate manager_pusat permissions ke admin_pusat
- Manager Pusat memiliki `cabang_id` = kantor pusat
- Manager Pusat memiliki `owner_bos_id` = ID bos
- Manager Pusat hanya dapat melihat data milik bosnya

## Fallback Scenario
Jika Manager Pusat tidak ada:
- Bos dapat delegate manager_pusat permissions ke Admin Pusat
- Admin Pusat dapat melakukan tugas operasional lintas cabang
- System tetap berfungsi tanpa Manager Pusat
