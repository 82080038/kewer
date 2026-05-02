# Petugas Pusat Simulation

## Overview
Petugas Pusat adalah karyawan yang bekerja di kantor pusat dan melakukan aktivitas lapangan lintas cabang.

## Login Credentials
- **Username**: petugas_pusat (ditambahkan oleh bos)
- **Password**: password (diset saat pembuatan user)

## Access Scope
- Akses: Kantor pusat
- Izin: Input data dasar pusat dan aktivitas lapangan lintas cabang
- Data Visibility: Data dari kantor pusat dan cabang (untuk aktivitas lapangan)

## Daily Activities

### Morning Routine
1. **Dashboard Review**
   - Lihat statistik kantor pusat
   - Total nasabah, pinjaman aktif, outstanding
   - Aktivitas terbaru

2. **Nasabah Management**
   - Navigasi ke `pages/nasabah/index.php`
   - Lihat nasabah di kantor pusat
   - Tambah nasabah baru
   - Edit data nasabah
   - Review status nasabah

### Mid-Day Activities

1. **Pinjaman Processing**
   - Navigasi ke `pages/pinjaman/index.php`
   - Lihat pinjaman di kantor pusat
   - Proses pinjaman baru (jika memiliki permission)
   - Update status pinjaman

2. **Field Activities**
   - Navigasi ke `pages/field_activities/index.php`
   - Log aktivitas lapangan
   - Upload foto dokumentasi
   - Update GPS tracking

3. **Angsuran Collection**
   - Navigasi ke `pages/angsuran/index.php`
   - Lihat jadwal angsuran
   - Input pembayaran angsuran
   - Update status pembayaran

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
- Dapat melihat data kantor pusat
- Dapat menambah nasabah di kantor pusat
- Dapat melakukan aktivitas lapangan
- Dapat mengelola kas petugas sendiri
- Dapat melihat rute harian

## Important Notes
- Petugas Pusat memiliki `cabang_id` = kantor pusat
- Petugas Pusat memiliki `owner_bos_id` = ID bos
- Petugas Pusat dapat melakukan aktivitas lapangan di seluruh cabang milik bos
- Petugas Pusat tidak dapat melihat cabang lain untuk data administratif

## Field Activities
- Log kunjungan ke nasabah
- Upload foto dokumentasi
- Update GPS location
- Catat hasil kunjungan (berhasil/gagal)
- Catat alasan jika gagal
