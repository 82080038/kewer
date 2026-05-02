# Manager Cabang Simulation

## Overview
Manager Cabang adalah role opsional yang mengelola operasional cabang tunggal. Ditentukan oleh bos melalui branch_managers table.

## Login Credentials
- **Username**: manager_cabang (ditambahkan oleh bos)
- **Password**: password (diset saat pembuatan user)

## Access Scope
- Akses: Cabang tunggal (cabang tempat manager ditugaskan)
- Izin: Manajemen operasional cabang
- Data Visibility: Hanya data cabang tempat manager ditugaskan

## Assignment Process
1. Bos menambah user dengan role manager_cabang
2. Bos menugaskan manager ke cabang melalui API:
   ```
   POST api/branch_managers.php?action=assign
   cabang_id: [ID cabang]
   manager_user_id: [ID user manager]
   manager_type: manager_cabang
   can_add_employees: 1
   can_manage_branch: 1
   ```
3. Manager dapat menambah karyawan di cabang tersebut

## Daily Activities

### Morning Routine
1. **Dashboard Review**
   - Lihat statistik cabang tempat manager ditugaskan
   - Total nasabah, pinjaman aktif, outstanding
   - Aktivitas terbaru di cabang

2. **Karyawan Management**
   - Navigasi ke `pages/petugas/index.php`
   - Lihat karyawan di cabang
   - Tambah karyawan baru (jika can_add_employees = 1)
   - Review role dan performance karyawan

### Mid-Day Activities

1. **Nasabah Management**
   - Navigasi ke `pages/nasabah/index.php`
   - Lihat nasabah di cabang
   - Tambah nasabah baru
   - Edit data nasabah
   - Review status nasabah

2. **Pinjaman Management**
   - Navigasi ke `pages/pinjaman/index.php`
   - Review pinjaman di cabang
   - Setujui pinjaman (jika memiliki permission)
   - Monitor status pinjaman

3. **Cabang Management**
   - Navigasi ke `pages/cabang/index.php`
   - Lihat data cabang (view only)
   - Tidak dapat mengubah cabang lain
   - Hanya dapat melihat cabang tempat ditugaskan

### Afternoon Activities
1. **Angsuran Monitoring**
   - Navigasi ke `pages/angsuran/index.php`
   - Lihat jadwal angsuran di cabang
   - Monitor pembayaran tertunggak
   - Review kinerja koleksi cabang

2. **Kas Petugas**
   - Navigasi ke `pages/kas_petugas/index.php`
   - Monitor kas petugas di cabang
   - Review setoran dan penarikan
   - Rekonkasi kas harian

## Weekly Activities
1. **Performance Review**
   - Review kinerja cabang
   - Evaluasi performa karyawan
   - Identifikasi area yang perlu improvement

2. **Collection Strategy**
   - Review nasabah dengan pembayaran tertunggak
   - Plan strategi penagihan
   - Follow-up dengan petugas

## Monthly Activities
1. **Target Review**
   - Review target vs aktual
   - Analisis gap dan penyebab
   - Plan strategi bulan berikutnya

2. **Karyawan Evaluation**
   - Review performa karyawan
   - Rekomendasikan training
   - Identifikasi karyawan berkinerja baik/buruk

## Key Permissions
- Dapat melihat data hanya di cabang tempat ditugaskan
- Dapat menambah karyawan di cabang (jika can_add_employees = 1)
- Dapat mengelola cabang (jika can_manage_branch = 1)
- Dapat melihat laporan cabang
- Tidak dapat melihat cabang lain

## Important Notes
- Manager Cabang adalah role OPSIONAL
- Jika tidak ada, bos dapat delegate manager_cabang permissions ke admin_cabang atau petugas_cabang
- Manager Cabang ditentukan oleh bos melalui branch_managers table
- Manager Cabang memiliki `cabang_id` = cabang tempat ditugaskan
- Manager Cabang memiliki `owner_bos_id` = ID bos
- Manager Cabang hanya dapat melihat data cabang tempat ditugaskan

## Fallback Scenario
Jika Manager Cabang tidak ada:
- Bos dapat delegate manager_cabang permissions ke Admin Cabang
- Admin Cabang dapat menambah karyawan di cabang
- Bos dapat delegate ke Petugas Cabang jika Admin Cabang juga tidak ada
- System tetap berfungsi tanpa Manager Cabang

## Branch Manager Capabilities
- **can_add_employees**: Bisa/tidak bisa menambah karyawan di cabang
- **can_manage_branch**: Bisa/tidak bisa mengelola data cabang
- Ditentukan oleh bos saat assignment
