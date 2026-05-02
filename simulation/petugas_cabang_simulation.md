# Petugas Cabang Simulation

## Overview
Petugas Cabang adalah karyawan yang bekerja di cabang dan melakukan aktivitas lapangan di wilayah cabang tersebut.

## Login Credentials
- **Username**: petugas_cabang (ditambahkan oleh bos)
- **Password**: password (diset saat pembuatan user)

## Access Scope
- Akses: Cabang tunggal
- Izin: Input data dasar cabang dan aktivitas lapangan
- Data Visibility: Hanya data cabang tempat petugas ditugaskan

## Assignment Process
1. Bos menambah user dengan role petugas_cabang
2. Bos set `cabang_id` saat pembuatan user
3. Petugas dapat melakukan tugas di cabang tersebut

## Daily Activities

### Morning Routine
1. **Dashboard Review**
   - Lihat statistik cabang
   - Total nasabah, pinjaman aktif, outstanding
   - Aktivitas terbaru di cabang

2. **Nasabah Management**
   - Navigasi ke `pages/nasabah/index.php`
   - Lihat nasabah di cabang
   - Tambah nasabah baru
   - Edit data nasabah
   - Review status nasabah

### Mid-Day Activities

1. **Pinjaman Processing**
   - Navigasi ke `pages/pinjaman/index.php`
   - Lihat pinjaman di cabang
   - Proses pinjaman baru (jika memiliki permission)
   - Update status pinjaman

2. **Field Activities**
   - Navigasi ke `pages/field_activities/index.php`
   - Log aktivitas lapangan
   - Upload foto dokumentasi
   - Update GPS tracking

3. **Angsuran Collection**
   - Navigasi ke `pages/angsuran/index.php`
   - Lihat jadwal angsuran di cabang
   - Input pembayaran angsuran
   - Update status pembayaran
   - Cetak kwitansi pembayaran

### Afternoon Activities
1. **Kas Petugas**
   - Navigasi ke `pages/kas_petugas/index.php`
   - Lihat saldo kas petugas
   - Input setoran
   - Request penarikan

2. **Rute Harian**
   - Navigasi ke `pages/rute_harian/index.php`
   - Lihat daftar nasabah yang harus dikunjungi hari ini
   - Update status kunjungan
   - Catat hasil kunjungan

## Weekly Activities
1. **Performance Review**
   - Review performa kutipan angsuran
   - Identifikasi nasabah yang sulit ditagih
   - Buat rencana follow-up

2. **Kas Reconciliation**
   - Rekonkasi kas petugas
   - Verifikasi setoran
   - Identifikasi selisih

## Monthly Activities
1. **Target Review**
   - Review target vs aktual penagihan
   - Analisis gap dan penyebab
   - Plan strategi bulan berikutnya

2. **Nasabah Visit Review**
   - Review kunjungan ke nasabah
   - Identifikasi nasabah yang butuh follow-up
   - Plan rute bulan berikutnya

## Key Permissions
- Dapat melihat data hanya di cabang tempat ditugaskan
- Dapat menambah nasabah di cabang
- Dapat melakukan aktivitas lapangan di wilayah cabang
- Dapat mengelola kas petugas sendiri
- Dapat melihat rute harian cabang

## Important Notes
- Petugas Cabang memiliki `cabang_id` = cabang tempat ditugaskan
- Petugas Cabang memiliki `owner_bos_id` = ID bos
- Petugas Cabang hanya dapat melihat data cabang tempat ditugaskan
- Petugas Cabang dapat ditentukan sebagai manager cabang oleh bos (flexible role)

## Field Activities
- Log kunjungan ke nasabah
- Upload foto dokumentasi
- Update GPS location
- Catat hasil kunjungan (berhasil/gagal)
- Catat alasan jika gagal

## Branch Manager Capability
- Jika bos menentukan petugas_cabang sebagai manager melalui branch_managers:
  - Dapat menambah karyawan di cabang (jika can_add_employees = 1)
  - Dapat mengelola data cabang (jika can_manage_branch = 1)
  - Menjadi de facto manager jika manager_cabang/admin_cabang tidak ada
