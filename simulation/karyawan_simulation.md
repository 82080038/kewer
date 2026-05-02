# Karyawan Simulation

## Overview
Karyawan adalah role umum dengan akses berdasarkan role dan delegated permissions dari bos. Ini adalah role default untuk karyawan baru.

## Login Credentials
- **Username**: karyawan (ditambahkan oleh bos)
- **Password**: password (diset saat pembuatan user)

## Access Scope
- Akses: Berdasarkan role (karyawan) dan delegated permissions dari bos
- Izin: Role-based + delegated permissions
- Data Visibility: Sesuai scope delegated permissions

## Assignment Process
1. Bos menambah user dengan role karyawan
2. Bos set `cabang_id` saat pembuatan user
3. Bos dapat delegate permissions ke karyawan melalui `pages/bos/delegated_permissions.php`

## Daily Activities

### Morning Routine
1. **Dashboard Review**
   - Lihat statistik cabang tempat ditugaskan
   - Total nasabah, pinjaman aktif, outstanding
   - Aktivitas terbaru

2. **Nasabah Management**
   - Navigasi ke `pages/nasabah/index.php`
   - Lihat nasabah (jika memiliki delegated permission)
   - Tambah nasabah (jika memiliki delegated permission)
   - Edit data nasabah (jika memiliki delegated permission)

### Mid-Day Activities

1. **Pinjaman Processing**
   - Navigasi ke `pages/pinjaman/index.php`
   - Lihat pinjaman (jika memiliki delegated permission)
   - Proses pinjaman (jika memiliki delegated permission)
   - Update status pinjaman (jika memiliki delegated permission)

2. **Angsuran Collection**
   - Navigasi ke `pages/angsuran/index.php`
   - Lihat jadwal angsuran (jika memiliki delegated permission)
   - Input pembayaran angsuran (jika memiliki delegated permission)
   - Update status pembayaran (jika memiliki delegated permission)

### Afternoon Activities
1. **Cabang Management**
   - Navigasi ke `pages/cabang/index.php`
   - Lihat cabang (jika memiliki delegated permission branch_crud)
   - Edit cabang (jika memiliki delegated permission branch_crud)

2. **Karyawan Management**
   - Navigasi ke `pages/petugas/index.php`
   - Lihat karyawan (jika memiliki delegated permission employee_crud)
   - Tambah karyawan (jika memiliki delegated permission employee_crud)
   - Edit karyawan (jika memiliki delegated permission employee_crud)

## Weekly Activities
1. **Permission Review**
   - Review delegated permissions yang dimiliki
   - Identifikasi permissions yang tidak lagi diperlukan
   - Request additional permissions jika diperlukan

2. **Performance Review**
   - Review performa tugas
   - Identifikasi area yang perlu improvement
   - Buat rencana pengembangan

## Monthly Activities
1. **Performance Evaluation**
   - Review performa bulanan
   - Identifikasi achievements
   - Set goals bulan berikutnya

2. **Permission Audit**
   - Review semua delegated permissions
   - Identifikasi permissions yang expired
   - Request renewal jika diperlukan

## Delegated Permissions

### Permission Scopes
1. **employee_crud**
   - Dapat menambah karyawan
   - Dapat mengedit karyawan
   - Dapat menghapus karyawan
   - Scope: sesuai scope yang ditentukan bos

2. **branch_crud**
   - Dapat menambah cabang
   - Dapat mengedit cabang
   - Dapat menghapus cabang
   - Scope: sesuai scope yang ditentukan bos

3. **branch_employee_crud**
   - Dapat menambah karyawan di cabang tertentu
   - Dapat mengedit karyawan di cabang tertentu
   - Dapat menghapus karyawan di cabang tertentu
   - Scope: cabang tertentu

4. **all_operations**
   - Dapat melakukan semua operasi
   - Dapat mengelola semua data
   - Scope: sesuai scope yang ditentukan bos

### Permission Request Process
1. Karyawan membutuhkan permission tambahan
2. Karyawan menghubungi bos
3. Bos meninjau request
4. Bos memberikan delegated permission melalui `pages/bos/delegated_permissions.php`
5. Karyawan dapat menggunakan permission
6. Bos dapat mencabut permission kapan saja

## Key Permissions
- Default: Hanya akses dasar sesuai role karyawan
- Dapat memiliki delegated permissions dari bos
- Akses data sesuai scope delegated permissions
- Tidak dapat melihat data di luar scope delegated permissions

## Important Notes
- Karyawan adalah role DEFAULT untuk user baru
- Karyawan memiliki `cabang_id` = cabang tempat ditugaskan
- Karyawan memiliki `owner_bos_id` = ID bos
- Karyawan hanya dapat melihat data sesuai delegated permissions
- Tanpa delegated permissions, karyawan hanya memiliki akses dasar

## Role Progression
Karyawan dapat dipromosikan ke role lain:
- Bos dapat mengubah role karyawan ke petugas_pusat, petugas_cabang, admin_pusat, admin_cabang, manager_pusat, manager_cabang
- Role change memerlukan permission dari bos
- Role change akan mengubah akses dan permissions otomatis

## Delegated Permissions vs Role
- **Role**: Menentukan akses dasar dan permissions default
- **Delegated Permissions**: Menambah permissions tambahan di luar role
- Karyawan dengan role karyawan + delegated permissions = akses fleksibel
- Bos dapat memberikan delegated permissions tanpa mengubah role
