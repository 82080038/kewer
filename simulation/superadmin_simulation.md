# Superadmin Simulation

## Overview
Superadmin adalah pemilik aplikasi yang tidak memiliki kantor, memonitor seluruh bos dan aktivitas organisasi mereka.

## Login Credentials
- **Username**: admin
- **Password**: password

## Daily Activities

### Morning Routine
1. **Login ke Dashboard**
   - Buka `http://localhost/kewer/login.php`
   - Login dengan kredensial superadmin
   - Lihat statistik konsolidasi dari seluruh cabang dan bos

2. **Review Pendaftaran Bos Pending**
   - Navigasi ke `pages/superadmin/bos_approvals.php`
   - Periksa pendaftaran bos yang menunggu persetujuan
   - Review data perusahaan dan alamat bos
   - Setujui atau tolak pendaftaran dengan alasan

3. **Monitoring Aktivitas Bos**
   - Lihat daftar bos yang sudah disetujui
   - Monitor kantor pusat dan cabang yang dimiliki setiap bos
   - Periksa aktivitas karyawan di setiap organisasi

### Mid-Day Activities
1. **Audit Trail Review**
   - Navigasi ke `pages/audit/index.php`
   - Periksa aktivitas penting di seluruh sistem
   - Identifikasi aktivitas yang mencurigakan

2. **Konfigurasi Sistem**
   - Akses pengaturan bunga jika diperlukan
   - Review konfigurasi auto-confirm pinjaman
   - Sesuaikan pengaturan global jika perlu

3. **Laporan Konsolidasi**
   - Navigasi ke `pages/laporan/index.php`
   - Lihat laporan keuangan konsolidasi
   - Review kinerja pinjaman di seluruh cabang
   - Analisis data nasabah secara global

### Afternoon Activities
1. **Manajemen User Global**
   - Navigasi ke `pages/users/index.php`
   - Lihat seluruh user di sistem
   - Review role assignment
   - Nonaktifkan user jika diperlukan

2. **Manajemen Cabang Global**
   - Navigasi ke `pages/cabang/index.php`
   - Lihat seluruh cabang di sistem
   - Review owner cabang (bos)
   - Identifikasi cabang tanpa bos atau bos tanpa cabang

3. **Review Risiko**
   - Navigasi ke `pages/family_risk/index.php`
   - Periksa risiko nasabah di seluruh cabang
   - Identifikasi nasabah high-risk

## Weekly Activities
1. **Performance Review**
   - Review kinerja setiap bos berdasarkan:
     - Jumlah cabang
     - Jumlah nasabah
     - Total pinjaman
     - Tingkat koleksi

2. **Compliance Check**
   - Verifikasi kepatuhan bos terhadap aturan
   - Periksa apakah bos memiliki kantor pusat
   - Identifikasi bos yang belum membuat kantor pusat

3. **System Maintenance**
   - Review log error
   - Periksa kinerja database
   - Backup data jika diperlukan

## Monthly Activities
1. **Strategic Planning**
   - Evaluasi pertumbuhan aplikasi
   - Identifikasi area yang perlu perbaikan
   - Plan fitur baru berdasarkan feedback bos

2. **Financial Overview**
   - Review laporan keuangan konsolidasi bulanan
   - Analisis tren peminjaman
   - Evaluasi kinerja koleksi

## Special Tasks

### Bos Approval Workflow
1. Bos mendaftar melalui `pages/bos/register.php`
2. Data tersimpan di tabel `bos_registrations` dengan status 'pending'
3. Superadmin melihat pendaftaran di `pages/superadmin/bos_approvals.php`
4. Superadmin memilih:
   - **Setujui**: Membuat user account dengan role 'bos', bos dapat login
   - **Tolak**: Update status menjadi 'rejected' dengan alasan

### Monitoring Bos Activities
- Lihat kantor pusat dan cabang yang dimiliki bos
- Monitor jumlah karyawan di setiap organisasi
- Review delegated permissions yang diberikan bos
- Periksa branch manager assignments

## Key Permissions
- Semua permissions otomatis diberikan
- Dapat melihat semua data di seluruh cabang
- Dapat mengelola semua user kecuali diri sendiri
- Dapat menyetujui/tolak pendaftaran bos
- Dapat menghapus cabang dan user

## Important Notes
- Superadmin TIDAK memiliki cabang_id (NULL)
- Superadmin TIDAK memiliki owner_bos_id
- Superadmin dapat melihat data dari seluruh bos dan organisasi
- Superadmin tidak dapat dihapus oleh user lain
