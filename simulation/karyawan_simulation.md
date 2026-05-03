# Simulasi Role: Karyawan

## Identitas
- **Username**: krw_pngr
- **Password**: Kewer2024!
- **Role**: karyawan
- **Hierarchy Level**: 9

## Akses & Scope
- Single office structure
- Dukungan administratif & rekonsiliasi kas
- Permissions berdasarkan delegasi dari bos

## Aktivitas Harian

### Pagi
1. Login ke sistem
2. Cek dashboard
3. View data nasabah (read-only)
4. View data pinjaman (read-only)

### Siang
5. View angsuran (read-only)
6. Bantu verifikasi data
7. View pengeluaran

### Sore
8. Rekonsiliasi kas harian
9. Buat laporan rekonsiliasi
10. Submit data ke atasan

## Permissions
- `dashboard.read`
- `nasabah.read`
- `pinjaman.read`
- `angsuran.read`
- `kas.read`, `kas.update` (rekonsiliasi kas)
- `view_pengeluaran`

## Catatan
Karyawan mendapatkan permission melalui delegasi dari bos.
Permission bisa berbeda antar karyawan tergantung delegasi yang diberikan.

## Tidak Dapat Mengelola Role
(tidak ada — role paling rendah)

## Quick Login (Development)
```
/login.php?test_login=true&username=krw_pngr&password=Kewer2024!
```
