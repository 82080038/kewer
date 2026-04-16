# Dokumentasi Role Aplikasi Kewer

**Terakhir Diperbarui:** 2026-04-16
**Aplikasi:** Koperasi Kewer (Koperasi Warga Ekonomi Rakyat)

## Ringkasan

Direktori ini berisi dokumentasi lengkap untuk setiap role dalam aplikasi Kewer. File-file JSON ini mendefinisikan set fitur lengkap, izin, tanggung jawab, dan parameter simulasi untuk setiap role. File-file ini dirancang untuk digunakan dalam pengujian simulasi dan untuk memastikan pemahaman yang konsisten tentang kemampuan role di seluruh tim pengembangan dan pengujian.

## Bahasa dan Format Aplikasi

**Bahasa Antarmuka:**
- Aplikasi menggunakan **Bahasa Indonesia** untuk semua antarmuka pengguna
- Semua label, tombol, pesan, dan notifikasi dalam Bahasa Indonesia

**Format Mata Uang:**
- Simbol: **Rp** (Rupiah)
- Format: `Rp1.000.000` (titik sebagai pemisah ribuan)
- Contoh: Rp5.000.000, Rp50.000.000
- Fungsi PHP: `number_format($value, 0, ',', '.')`

**Format Tanggal:**
- Format tampilan: **d F Y** (Hari Bulan Tahun dalam Bahasa Indonesia)
- Contoh: 16 April 2026, 1 Januari 2026
- Format database: **Y-m-d** (YYYY-MM-DD)
- Contoh: 2026-04-16
- Library: Flatpickr dengan altFormat 'd F Y'

**Format Angka:**
- Pemisah ribuan: **titik (.)**
- Pemisah desimal: **koma (,)**
- Contoh: 1.000,50

## Hirarki Role

Aplikasi Kewer menggunakan sistem role hierarkis dengan 7 level:

```
Level 1: Owner (Tertinggi)
Level 2: Manajer Cabang
Level 3: Admin Pusat
Level 4: Admin Cabang
Level 5: Petugas Pusat
Level 6: Petugas Cabang
Level 7: Karyawan (Terendah)
```

**Aturan Hirarki:**
- Role level tinggi dapat mengelola role di bawahnya
- Owner memiliki akses otomatis ke semua izin
- Setiap role memiliki cakupan tertentu (pusat/cabang)
- Visibilitas data ditentukan oleh level role dan cakupannya

## File Role yang Tersedia

1. **owner.json** - Dokumentasi role Owner
2. **manager.json** - Dokumentasi role Manajer Cabang
3. **admin_pusat.json** - Dokumentasi role Admin Pusat
4. **admin_cabang.json** - Dokumentasi role Admin Cabang
5. **petugas_pusat.json** - Dokumentasi role Petugas Pusat
6. **petugas_cabang.json** - Dokumentasi role Petugas Cabang
7. **karyawan.json** - Dokumentasi role Karyawan

## Struktur File Role

Setiap file JSON role berisi bagian-bagian berikut:

### 1. Informasi Role
- **name**: Nama tampilan role
- **code**: Kode role internal yang digunakan dalam sistem
- **hierarchy_level**: Level numerik dalam hirarki (1-7)
- **description**: Deskripsi detail tujuan role
- **status**: Status saat ini (aktif/nonaktif)

### 2. Cakupan Akses
- **type**: "pusat" (pusat) atau "cabang" (cabang)
- **branches**: "all" (semua) atau "single" (tunggal)
- **data_visibility**: "consolidated" (konsolidasi) atau "branch_specific" (spesifik cabang)

### 3. Izin
- **automatic_grant**: Apakah izin diberikan secara otomatis
- **can_manage**: Daftar role yang dapat dikelola oleh role ini

### 4. Modul
Rincian setiap akses modul termasuk:
- **access**: Boolean yang menunjukkan apakah modul dapat diakses
- **permissions**: Daftar izin spesifik yang diperlukan
- **features**: Array fitur spesifik yang tersedia dalam modul

**Modul yang Tersedia (Berdasarkan Dashboard Menu):**
- Dashboard
- Nasabah (Manajemen Nasabah)
- Pinjaman (Manajemen Pinjaman)
- Angsuran (Manajemen Angsuran)
- Aktivitas Lapangan (Field Activities)
- Kas Petugas (Manajemen Kas Lapangan)
- Rekonsiliasi Kas (Cash Reconciliation)
- Auto-Confirm (Persetujuan Pinjaman Otomatis)
- Users (Manajemen Pengguna)
- Cabang (Manajemen Cabang)
- Setting Bunga (Konfigurasi Suku Bunga)
- Pengeluaran (Manajemen Pengeluaran)
- Kas Bon (Manajemen Kas Bon)
- Family Risk (Penilaian Risiko Keluarga)
- Petugas (Manajemen Petugas Lapangan)
- Laporan (Laporan Keuangan, Kinerja Pinjaman, Nasabah)

**Catatan:**
- Modul Permissions terintegrasi di halaman Users (sub-halaman)
- Modul Laporan menggunakan ReportGenerator dari `src/Reporting/ReportGenerator.php`

### 5. Tugas dan Tanggung Jawab
Daftar terkategoris dari:
- Tugas strategis
- Tugas operasional
- Tugas administratif
- Tugas keuangan
- Aktivitas lapangan
- Layanan nasabah
- Tugas pelaporan

### 6. Hak
Hak spesifik yang diberikan kepada role:
- Hak akses sistem
- Hak manajemen pengguna
- Hak pengambilan keputusan
- Otoritas keuangan
- Hak akses lapangan
- Hak akses pembayaran

### 7. Kewajiban
Kewajiban dan tanggung jawab:
- Kewajiban kinerja
- Kewajiban kepatuhan
- Kewajiban manajemen risiko
- Kewajiban pelaporan
- Kewajiban layanan nasabah

### 8. Parameter Simulasi
Parameter untuk simulasi otomatis:
- **login**: Kredensial login (username, password, test_login_url)
- **daily_activities**: Daftar tugas harian untuk simulasi
- **weekly_activities**: Daftar tugas mingguan untuk simulasi
- **monthly_activities**: Daftar tugas bulanan untuk simulasi

## Ringkasan Role

### Owner (Level 1)
- **Cakupan:** Semua cabang (pusat)
- **Fitur Utama:** Akses sistem penuh, dapat mengelola semua role, pengawasan lengkap
- **Tanggung Jawab Utama:** Pengawasan strategis, tata kelola sistem, pengambilan keputusan akhir
- **Login:** admin/password

### Manajer Cabang (Level 2)
- **Cakupan:** Cabang tunggal (cabang)
- **Fitur Utama:** Manajemen cabang, persetujuan pinjaman, manajemen staf
- **Tanggung Jawab Utama:** Operasi cabang, manajemen portofolio pinjaman, pengawasan keuangan
- **Login:** admin/password

### Admin Pusat (Level 3)
- **Cakupan:** Semua cabang (pusat)
- **Fitur Utama:** Administrasi lintas cabang, manajemen pengguna, pelaporan
- **Tanggung Jawab Utama:** Administrasi pusat, konfigurasi sistem, koordinasi
- **Login:** admin/password

### Admin Cabang (Level 4)
- **Cakupan:** Cabang tunggal (cabang)
- **Fitur Utama:** Administrasi cabang, manajemen pengguna terbatas
- **Tanggung Jawab Utama:** Dukungan administratif cabang, koordinasi staf
- **Login:** admin/password

### Petugas Pusat (Level 5)
- **Cakupan:** Semua cabang (pusat)
- **Fitur Utama:** Aktivitas lapangan, penagihan pembayaran, layanan nasabah
- **Tanggung Jawab Utama:** Pekerjaan lapangan lintas cabang, penagihan pembayaran, hubungan nasabah
- **Login:** petugas1/password

### Petugas Cabang (Level 6)
- **Cakupan:** Cabang tunggal (cabang)
- **Fitur Utama:** Aktivitas lapangan cabang, penagihan pembayaran
- **Tanggung Jawab Utama:** Pekerjaan lapangan cabang, penagihan pembayaran lokal
- **Login:** petugas1/password

### Karyawan (Level 7)
- **Cakupan:** Cabang tunggal (cabang)
- **Fitur Utama:** Dukungan administratif, penanganan kas
- **Tanggung Jawab Utama:** Entri data, rekonsiliasi kas, layanan nasabah dasar
- **Login:** petugas1/password

## Menggunakan File Role untuk Simulasi

File-file role ini dapat digunakan dalam skrip simulasi untuk:

1. **Mendefinisikan Perilaku Role:** Memuat aktivitas dan tugas spesifik role
2. **Mengatur Kredensial Login:** Menggunakan parameter login yang telah ditentukan sebelumnya
3. **Memvalidasi Izin:** Memeriksa apakah role seharusnya memiliki akses ke fitur spesifik
4. **Membuat Skenario Pengujian:** Membuat kasus uji spesifik role
5. **Menghindari Miskomunikasi:** Memastikan pemahaman yang konsisten tentang kemampuan role

### Contoh Penggunaan dalam Skrip Simulasi

```javascript
const roleConfig = require('./roles/owner.json');

// Mendapatkan kredensial login
const loginUrl = roleConfig.simulation_parameters.login.test_login_url;

// Mendapatkan aktivitas harian
const dailyTasks = roleConfig.simulation_parameters.daily_activities;

// Memeriksa akses modul
const canAccessNasabah = roleConfig.modules.nasabah.access;

// Mendapatkan izin
const requiredPermissions = roleConfig.modules.pinjaman.permissions;
```

## Matriks Akses Modul

| Modul | Owner | Manager | Admin Pusat | Admin Cabang | Petugas Pusat | Petugas Cabang | Karyawan |
|--------|-------|---------|-------------|--------------|---------------|---------------|----------|
| Dashboard | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| Nasabah | Lihat | ✅ | ✅ | ✅ | Lihat | Lihat | Lihat |
| Pinjaman | Lihat+Approve | ✅ | ✅ | ✅ | Lihat | Lihat | Lihat |
| Angsuran | Lihat | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| Aktivitas Lapangan | Lihat | Lihat | Lihat | Lihat | ✅ | ✅ | ❌ |
| Kas Petugas | Lihat | ✅ | ✅ | ✅ | ✅ | ✅ | ❌ |
| Rekonsiliasi Kas | Lihat | ✅ | ❌ | ❌ | ❌ | ❌ | ✅ |
| Auto-Confirm | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ |
| Users | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ |
| Cabang | ✅ | Lihat | Lihat | Lihat | ❌ | ❌ | ❌ |
| Setting Bunga | ✅ | ✅ | ✅ | Lihat | ❌ | ❌ | ❌ |
| Pengeluaran | Lihat+Approve | ✅ | ✅ | ✅ | ❌ | ❌ | Lihat |
| Kas Bon | Lihat+Approve | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ |
| Family Risk | Lihat | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| Petugas | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| Permissions | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| Laporan | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |

**Keterangan:**
- ✅ Akses penuh (baca/tulis/kelola)
- Lihat = Akses baca saja
- Lihat+Approve = Akses baca dan persetujuan level tinggi
- ❌ Tidak ada akses

## Catatan Penting

1. **Role Superadmin:** Dalam database saat ini, role "superadmin" ada dan berfungsi mirip dengan "owner". File role menggunakan "owner" sebagai terminologi standar.

2. **Sistem Izin:** Pemeriksaan izin sebenarnya diimplementasikan dalam `includes/functions.php` menggunakan fungsi `hasPermission()`. File-file role ini mendokumentasikan struktur izin yang dimaksudkan.

3. **Seeding Database:** Tabel izin (ref_roles, permissions, role_permissions) perlu diisi dengan data yang didokumentasikan dalam file-file ini agar sistem berfungsi sebagaimana dirancang.

4. **Kredensial Pengujian:** Untuk tujuan simulasi, kredensial yang sama digunakan untuk beberapa role (admin/password, petugas1/password). Dalam produksi, setiap pengguna harus memiliki kredensial yang unik.

5. **Batasan Cakupan:** Implementasi saat ini mungkin tidak sepenuhnya menegakkan semua pembatasan cakupan yang didokumentasikan di sini. File-file ini mewakili desain yang dimaksudkan.

## Pemeliharaan

Saat memperbarui aplikasi:

1. Perbarui file role yang sesuai jika:
   - Modul baru ditambahkan
   - Izin berubah
   - Tanggung jawab role berubah
   - Fitur baru ditambahkan

2. Pertahankan file role sinkron dengan:
   - Tabel izin database
   - Pemeriksaan izin berbasis kode
   - Logika rendering menu UI
   - Skrip pengujian

3. Lakukan kontrol versi pada file-file ini untuk melacak perubahan definisi role dari waktu ke waktu.

## Kontak

Untuk pertanyaan atau pembaruan dokumentasi role, hubungi tim pengembangan.

---

**Versi Dokumen:** 1.0
**Dibuat Oleh:** Cascade AI Assistant
**Tanggal:** 2026-04-16
