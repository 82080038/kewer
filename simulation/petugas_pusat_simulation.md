# Simulasi Role: Petugas Pusat

## Identitas
- **Username**: ptr_pngr1
- **Password**: Kewer2024!
- **Role**: petugas_pusat
- **Hierarchy Level**: 7

## Akses & Scope
- Single office structure
- Field activities & koleksi pembayaran

## Aktivitas Harian

### Pagi
1. Login ke sistem
2. Cek daftar angsuran yang harus ditagih hari ini
3. Siapkan daftar kunjungan nasabah

### Siang (Di lapangan)
4. Kunjungi nasabah satu per satu
5. Catat pembayaran angsuran via sistem
6. Cetak/kirim kwitansi pembayaran ke nasabah

### Sore (Kembali ke kantor)
7. Setorkan kas hasil koleksi
8. Input setoran ke kas petugas
9. Submit laporan aktivitas lapangan harian

## Permissions
- `nasabah.read`
- `pinjaman.read`
- `angsuran.read`, `manage_pembayaran`
- `kas_petugas.read`, `kas_petugas.update`

## Tidak Dapat Mengelola Role
(tidak ada — role operasional)

## Quick Login (Development)
```
/login.php?test_login=true&username=ptr_pngr1&password=Kewer2024!
```
