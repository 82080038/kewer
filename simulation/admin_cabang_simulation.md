# Admin Cabang Simulation

## Overview
Admin Cabang adalah role opsional yang menangani administrasi cabang tunggal. Jika tidak ada, bos dapat delegate permissions ke manager_cabang atau petugas_cabang.

## Login Credentials
- **Username**: admin_cabang (ditambahkan oleh bos)
- **Password**: password (diset saat pembuatan user)

## Access Scope
- Akses: Cabang tunggal (cabang tempat admin ditugaskan)
- Izin: Administrasi cabang
- Data Visibility: Hanya data cabang tempat admin ditugaskan

## Assignment Process
1. Bos menambah user dengan role admin_cabang
2. Bos menugaskan admin ke cabang melalui API atau set cabang_id saat pembuatan user
3. Admin dapat melakukan tugas administratif di cabang tersebut

## Daily Activities

### Morning Routine
1. **Dashboard Review**
   - Lihat statistik cabang tempat admin ditugaskan
   - Total nasabah, pinjaman aktif, outstanding
   - Aktivitas terbaru di cabang

2. **Karyawan Administration**
   - Navigasi ke `pages/petugas/index.php`
   - Lihat karyawan di cabang
   - Tambah karyawan baru (jika memiliki delegated permission)
   - Edit data karyawan
   - Review role karyawan

### Mid-Day Activities

1. **Nasabah Administration**
   - Navigasi ke `pages/nasabah/index.php`
   - Lihat nasabah di cabang
   - Edit data nasabah
   - Update status nasabah
   - Review blacklist status

2. **Cabang Administration**
   - Navigasi ke `pages/cabang/index.php`
   - Lihat data cabang (view only untuk cabang sendiri)
   - Edit data cabang (jika memiliki permission)
   - Tidak dapat melihat cabang lain

3. **Pengeluaran Management**
   - Navigasi ke `pages/pengeluaran/index.php`
   - Lihat pengeluaran di cabang
   - Tambah pengeluaran baru
   - Edit pengeluaran
   - Review kategori pengeluaran

### Afternoon Activities
1. **Kas Bon Administration**
   - Navigasi ke `pages/kas_bon/index.php`
   - Lihat kas bon karyawan di cabang
   - Setujui kas bon (jika memiliki permission)
   - Review potongan kas bon

2. **Laporan Cabang**
   - Navigasi ke `pages/laporan/index.php`
   - Lihat laporan pengeluaran cabang
   - Review laporan kas cabang
   - Cetak laporan jika diperlukan

## Weekly Activities
1. **Administrative Review**
   - Review aktivitas administratif di cabang
   - Identifikasi area yang butuh perhatian
   - Buat rekomendasi improvement

2. **Karyawan Review**
   - Review data karyawan di cabang
   - Identifikasi karyawan yang perlu update data
   - Rekomendasikan training

## Monthly Activities
1. **Administrative Reporting**
   - Buat laporan administratif bulanan cabang
   - Review pengeluaran cabang
   - Identifikasi tren pengeluaran

2. **Compliance Check**
   - Verifikasi kepatuhan cabang terhadap SOP administratif
   - Review dokumen dan data
   - Identifikasi area risiko

## Key Permissions
- Dapat melihat data hanya di cabang tempat ditugaskan
- Dapat menambah karyawan di cabang (jika memiliki delegated permission)
- Dapat mengelola pengeluaran di cabang
- Dapat mengelola kas bon di cabang
- Tidak dapat melihat cabang lain

## Important Notes
- Admin Cabang adalah role OPSIONAL
- Jika tidak ada, bos dapat delegate admin_cabang permissions ke manager_cabang atau petugas_cabang
- Admin Cabang memiliki `cabang_id` = cabang tempat ditugaskan
- Admin Cabang memiliki `owner_bos_id` = ID bos
- Admin Cabang hanya dapat melihat data cabang tempat ditugaskan

## Fallback Scenario
Jika Admin Cabang tidak ada:
- Bos dapat delegate admin_cabang permissions ke Manager Cabang
- Manager Cabang dapat melakukan tugas administratif di cabang
- Jika Manager Cabang juga tidak ada, bos dapat delegate ke Petugas Cabang
- System tetap berfungsi tanpa Admin Cabang
