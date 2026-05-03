# Simulasi Role: Admin Pusat

## Identitas
- **Username**: adm_pusat
- **Password**: Kewer2024!
- **Role**: admin_pusat
- **Hierarchy Level**: 5

## Akses & Scope
- Single office structure
- Full data visibility
- Input nasabah, pinjaman, angsuran

## Aktivitas Harian

### Pagi
1. Login ke sistem
2. Cek dashboard statistik
3. Input nasabah baru (sesuai antrian)
4. Input pengajuan pinjaman untuk nasabah

### Siang
5. Verifikasi data nasabah & dokumen
6. Update status angsuran
7. Input pengeluaran operasional

### Sore
8. Rekap laporan harian
9. Kelola data petugas jika diperlukan
10. Input permintaan kas bon

## Permissions
- `nasabah.read`, `manage_nasabah`, `manage_blacklist`
- `pinjaman.read`, `manage_pinjaman`
- `angsuran.read`, `manage_pembayaran`, `manage_denda`
- `users.read`, `users.create`, `manage_users`, `assign_permissions`
- `view_laporan`
- `manage_pengeluaran`, `view_pengeluaran`
- `manage_kas_bon`, `view_kas_bon`
- `manage_bunga`, `view_settings`
- `kas_petugas.read`, `kas_petugas.update`
- `manage_petugas`, `view_petugas`
- `assign_permissions`

## Dapat Mengelola Role
petugas_pusat, petugas_cabang, karyawan

## Quick Login (Development)
```
/login.php?test_login=true&username=adm_pusat&password=Kewer2024!
```
