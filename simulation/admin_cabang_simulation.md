# Simulasi Role: Admin Cabang

## Identitas
- **Username**: adm_balige
- **Password**: Kewer2024!
- **Role**: admin_cabang
- **Hierarchy Level**: 6

## Akses & Scope
- Single office structure
- Full data visibility
- Fungsi administratif

## Aktivitas Harian

### Pagi
1. Login ke sistem
2. Cek dashboard
3. Input atau update data nasabah
4. Proses pengajuan pinjaman

### Siang
5. Catat pembayaran angsuran
6. Update pengeluaran operasional

### Sore
7. Kelola kas bon
8. Laporan harian singkat

## Permissions
- `nasabah.read`, `manage_nasabah`, `manage_blacklist`
- `pinjaman.read`, `manage_pinjaman`
- `angsuran.read`, `manage_pembayaran`
- `users.read`, `users.create`, `manage_users`
- `manage_pengeluaran`, `view_pengeluaran`
- `manage_kas_bon`, `view_kas_bon`
- `view_laporan`
- `kas_petugas.read`, `kas_petugas.update`
- `manage_petugas`, `view_petugas`

## Dapat Mengelola Role
petugas_cabang, karyawan

## Quick Login (Development)
```
/login.php?test_login=true&username=adm_balige&password=Kewer2024!
```
