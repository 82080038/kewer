# Simulasi Role: Petugas Cabang

## Identitas
- **Username**: ptr_pngr2
- **Password**: Kewer2024!
- **Role**: petugas_cabang
- **Hierarchy Level**: 8

## Akses & Scope
- Single office structure
- Field activities & koleksi pembayaran

## Aktivitas Harian

### Pagi
1. Login ke sistem
2. Cek daftar angsuran yang harus ditagih
3. Rencanakan rute kunjungan hari ini

### Siang (Di lapangan)
4. Kunjungi nasabah sesuai jadwal
5. Catat pembayaran (tunai/transfer)
6. Berikan kwitansi ke nasabah
7. Catat aktivitas lapangan di sistem

### Sore
8. Kembali ke kantor / setoran
9. Input setoran ke kas petugas
10. Submit laporan harian

## Permissions
- `nasabah.read`
- `pinjaman.read`
- `angsuran.read`, `manage_pembayaran`
- `angsuran.create` (aktivitas lapangan)
- `kas_petugas.read`, `kas_petugas.update`

## Tidak Dapat Mengelola Role
(tidak ada — role operasional)

## Quick Login (Development)
```
/login.php?test_login=true&username=ptr_pngr2&password=Kewer2024!
```
