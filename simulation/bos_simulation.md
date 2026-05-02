# Bos Simulation

## Overview
Bos adalah pemilik usaha yang mendaftar dan disetujui oleh superadmin. Bos wajib memiliki kantor pusat dan dapat membuat cabang tambahan.

## Login Credentials
- **Username**: bos (setelah disetujui superadmin)
- **Password**: password (diset saat registrasi)

## Registration Process
1. **Registrasi**
   - Buka `http://localhost/kewer/pages/bos/register.php`
   - Isi form registrasi:
     - Username, password, konfirmasi password
     - Nama lengkap, nama perusahaan
     - Email, no. telepon
     - Alamat lengkap (provinsi, kabupaten, kecamatan, desa)
   - Submit form
   - Status: pending approval

2. **Menunggu Approval**
   - Superadmin menyetujui pendaftaran
   - User account dibuat dengan role 'bos'
   - Bos dapat login

## First-Time Setup (Wajib)

### Setup Kantor Pusat
1. **Login Pertama**
   - Login dengan username dan password
   - Otomatis diarahkan ke `pages/bos/setup_headquarters.php`

2. **Membuat Kantor Pusat**
   - Isi form:
     - Kode cabang (misal: HQ)
     - Nama kantor pusat
     - No. telepon, email
     - Alamat lengkap dengan dropdown wilayah
     - Status: aktif
   - Submit form
   - Kantor pusat dibuat dengan `is_headquarters = 1`
   - Bos `cabang_id` diupdate ke ID kantor pusat
   - Redirect ke dashboard

## Daily Activities

### Morning Routine
1. **Dashboard Review**
   - Lihat statistik dari seluruh cabang milik bos
   - Total nasabah, pinjaman aktif, outstanding
   - Aktivitas terbaru

2. **Cabang Management**
   - Navigasi ke `pages/cabang/index.php`
   - Lihat kantor pusat dan cabang yang dimiliki
   - Status dan tipe cabang

### Mid-Day Activities

1. **Tambah Karyawan**
   - Navigasi ke `pages/petugas/tambah.php`
   - Tambah karyawan ke kantor pusat atau cabang
   - Pilih role (karyawan, admin_pusat, manager_pusat, dll)
   - Pilih cabang assignment
   - Submit - `owner_bos_id` otomatis di-set ke ID bos

2. **Delegasi Permission**
   - Navigasi ke `pages/bos/delegated_permissions.php`
   - Pilih karyawan untuk didelegasi permission
   - Pilih scope:
     - employee_crud: CRUD karyawan
     - branch_crud: CRUD cabang
     - branch_employee_crud: CRUD karyawan cabang
     - all_operations: Semua operasi
   - Set tanggal expired (opsional)
   - Tambah catatan
   - Submit

3. **Tambah Cabang Baru**
   - Navigasi ke `pages/cabang/tambah.php`
   - Isi form cabang baru
   - JANGAN centang "Jadikan Kantor Pusat" (hanya boleh 1)
   - Submit - cabang dibuat dengan `is_headquarters = 0`

4. **Assignment Manajer Cabang**
   - Gunakan API `api/branch_managers.php?action=assign`
   - POST data:
     - cabang_id: ID cabang
     - manager_user_id: ID user manajer
     - manager_type: manager_cabang/admin_cabang/petugas_cabang
     - can_add_employees: 1/0
     - can_manage_branch: 1/0
   - Submit via curl atau Postman

### Afternoon Activities
1. **Monitoring Karyawan**
   - Lihat daftar karyawan dan role mereka
   - Review delegated permissions yang aktif
   - Cabut permission jika diperlukan

2. **Cabang Monitoring**
   - Lihat performa setiap cabang
   - Review karyawan di setiap cabang
   - Identifikasi cabang yang butuh perhatian

## Weekly Activities
1. **Performance Review**
   - Review kinerja setiap cabang
   - Evaluasi performa karyawan
   - Identifikasi area yang perlu improvement

2. **Permission Audit**
   - Review semua delegated permissions
   - Cabut permission yang tidak lagi diperlukan
   - Delegate permission baru jika diperlukan

3. **Branch Manager Review**
   - Review assignment manajer cabang
   - Ganti manajer jika diperlukan
   - Update capabilities manajer

## Monthly Activities
1. **Financial Review**
   - Review outstanding pinjaman di seluruh cabang
   - Analisis tingkat koleksi
   - Identifikasi cabang dengan performa buruk

2. **Strategic Planning**
   - Plan pembukaan cabang baru
   - Evaluasi kebutuhan karyawan tambahan
   - Review struktur organisasi

## Special Workflows

### Fallback Scenario
Jika admin_pusat tidak ada:
- Bos dapat delegate admin_pusat permissions ke manager_pusat
- Manager_pusat dapat melakukan tugas admin_pusat

Jika manager_cabang tidak ada:
- Bos dapat delegate manager_cabang permissions ke admin_cabang
- Admin_cabang dapat menambah karyawan di cabang

### Delegasi Permission Flexible
- Scope bisa disesuaikan dengan kebutuhan
- Bisa set tanggal expired
- Bisa dicabut kapan saja
- History tersimpan di tabel delegated_permissions

## Key Permissions
- Otomatis memiliki semua permissions kecuali teknis sistem
- Dapat melihat dan mengelola data di cabang miliknya saja
- Dapat delegate permissions ke karyawan
- Dapat menambah cabang (hanya 1 kantor pusat)
- Dapat menambah karyawan di seluruh cabang miliknya

## Important Notes
- Bos WAJIB memiliki kantor pusat
- Bos hanya dapat memiliki 1 kantor pusat
- Semua karyawan yang ditambah bos memiliki `owner_bos_id` = ID bos
- Semua cabang yang dibuat bos memiliki `owner_bos_id` = ID bos
- Bos hanya dapat melihat data milik organisasinya sendiri
