# Admin Pusat Simulation

## Overview
Admin Pusat adalah role opsional yang menangani administrasi lintas cabang untuk bos. Jika tidak ada, bos dapat delegate permissions ke manager_pusat.

## Login Credentials
- **Username**: admin_pusat (ditambahkan oleh bos)
- **Password**: password (diset saat pembuatan user)

## Access Scope
- Akses: Kantor pusat dan semua cabang (data konsolidasi)
- Izin: Administrasi lintas cabang
- Data Visibility: Data dari seluruh cabang milik bos

## Daily Activities

### Morning Routine
1. **Dashboard Review**
   - Lihat statistik konsolidasi dari seluruh cabang
   - Total nasabah, pinjaman aktif, outstanding
   - Aktivitas terbaru di seluruh cabang

2. **User Management**
   - Navigasi ke `pages/users/index.php`
   - Lihat user di seluruh cabang
   - Tambah user baru (jika memiliki permission)
   - Edit data user
   - Review role assignment

### Mid-Day Activities

1. **Cabang Administration**
   - Navigasi ke `pages/cabang/index.php`
   - Lihat status semua cabang
   - Edit data cabang (jika memiliki permission)
   - Review karyawan per cabang

2. **Nasabah Administration**
   - Navigasi ke `pages/nasabah/index.php`
   - Lihat nasabah dari seluruh cabang
   - Edit data nasabah
   - Update status nasabah
   - Review blacklist status

3. **Pengeluaran Management**
   - Navigasi ke `pages/pengeluaran/index.php`
   - Lihat pengeluaran dari seluruh cabang
   - Tambah pengeluaran baru
   - Edit pengeluaran
   - Review kategori pengeluaran

### Afternoon Activities
1. **Kas Bon Administration**
   - Navigasi ke `pages/kas_bon/index.php`
   - Lihat kas bon karyawan dari seluruh cabang
   - Setujui kas bon (jika memiliki permission)
   - Review potongan kas bon

2. **Laporan Administration**
   - Navigasi ke `pages/laporan/index.php`
   - Lihat laporan pengeluaran
   - Review laporan kas
   - Cetak laporan jika diperlukan

## Weekly Activities
1. **Administrative Review**
   - Review aktivitas administratif di seluruh cabang
   - Identifikasi area yang butuh perhatian
   - Buat rekomendasi improvement

2. **User Audit**
   - Review user account di seluruh cabang
   - Identifikasi user tidak aktif
   - Rekomendasikan deaktivasi

## Monthly Activities
1. **Administrative Reporting**
   - Buat laporan administratif bulanan
   - Review pengeluaran per cabang
   - Identifikasi tren pengeluaran

2. **Compliance Check**
   - Verifikasi kepatuhan cabang terhadap SOP administratif
   - Review dokumen dan data
   - Identifikasi area risiko

## Key Permissions
- Dapat melihat data konsolidasi dari seluruh cabang
- Dapat mengelola user di seluruh cabang (tergantung permission)
- Dapat mengelola pengeluaran
- Dapat mengelola kas bon
- Dapat melihat laporan administratif

## Important Notes
- Admin Pusat adalah role OPSIONAL
- Jika tidak ada, bos dapat delegate admin_pusat permissions ke manager_pusat
- Admin Pusat memiliki `cabang_id` = kantor pusat
- Admin Pusat memiliki `owner_bos_id` = ID bos
- Admin Pusat hanya dapat melihat data milik bosnya

## Fallback Scenario
Jika Admin Pusat tidak ada:
- Bos dapat delegate admin_pusat permissions ke Manager Pusat
- Manager Pusat dapat melakukan tugas administratif lintas cabang
- System tetap berfungsi tanpa Admin Pusat
