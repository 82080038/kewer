# Simulasi Role: Bos

## Identitas
- **Username**: patri
- **Password**: Kewer2024!
- **Role**: bos
- **Hierarchy Level**: 1

## Akses & Scope
- Single office structure
- Full access ke semua data dan fitur koperasi
- Automatic permission grant

## Aktivitas Harian

### Pagi
1. Login ke sistem
2. Cek dashboard — total nasabah, pinjaman aktif, outstanding balance
3. Review pinjaman pending yang menunggu approval
4. Approve/reject pinjaman berdasarkan analisa risiko

### Siang
5. Review laporan keuangan harian
6. Pantau koleksi pembayaran petugas
7. Review kas petugas & setoran masuk

### Sore
8. Review performa keseluruhan
9. Cek audit log aktivitas staff
10. Kelola setting bunga jika diperlukan

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
- `view_laporan`

## Dapat Mengelola Role
admin_pusat, admin_cabang, manager_pusat, manager_cabang, petugas_pusat, petugas_cabang, karyawan

## Quick Login (Development)
```
/login.php?test_login=true&username=patri&password=Kewer2024!
```
