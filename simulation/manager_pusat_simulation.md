# Simulasi Role: Manager Pusat

## Identitas
- **Username**: mgr_pusat
- **Password**: Kewer2024!
- **Role**: manager_pusat
- **Hierarchy Level**: 3

## Akses & Scope
- Single office structure
- Full data visibility
- Dapat approve pinjaman

## Aktivitas Harian

### Pagi
1. Login ke sistem
2. Cek dashboard — statistik konsolidasi
3. Review pinjaman pending
4. Approve pinjaman sesuai limit wewenang

### Siang
5. Kelola user & staff
6. Review laporan keuangan
7. Pantau aktivitas petugas lapangan

### Sore
8. Review kas petugas & rekonsiliasi
9. Laporan performa harian ke bos

## Permissions
- `dashboard.read`
- `nasabah.read`, `manage_nasabah`
- `pinjaman.read`, `manage_pinjaman`, `pinjaman.approve`
- `angsuran.read`, `manage_pembayaran`
- `users.read`, `users.create`, `manage_users`
- `view_laporan`
- `manage_pengeluaran`, `view_pengeluaran`
- `manage_kas_bon`, `view_kas_bon`
- `manage_bunga`, `view_settings`
- `kas_petugas.read`, `kas_petugas.update`
- `kas.read`, `kas.update`
- `manage_petugas`, `view_petugas`
- `pinjaman.auto_confirm`

## Dapat Mengelola Role
admin_pusat, admin_cabang, manager_cabang, petugas_pusat, petugas_cabang, karyawan

## Quick Login (Development)
```
/login.php?test_login=true&username=mgr_pusat&password=Kewer2024!
```
