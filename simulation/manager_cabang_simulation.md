# Simulasi Role: Manager Cabang

## Identitas
- **Username**: mgr_balige
- **Password**: Kewer2024!
- **Role**: manager_cabang
- **Hierarchy Level**: 4

## Akses & Scope
- Single office structure
- Full data visibility
- Dapat approve pinjaman

## Aktivitas Harian

### Pagi
1. Login ke sistem
2. Cek dashboard operasional
3. Review pinjaman yang perlu diproses
4. Approve pinjaman dalam limit wewenang

### Siang
5. Pantau aktivitas petugas cabang
6. Kelola angsuran dan pembayaran
7. Review pengeluaran operasional

### Sore
8. Input atau approve kas bon
9. Setujui setoran petugas
10. Laporan harian

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

## Dapat Mengelola Role
admin_cabang, petugas_cabang, karyawan

## Quick Login (Development)
```
/login.php?test_login=true&username=mgr_balige&password=Kewer2024!
```
