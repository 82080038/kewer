# Dokumentasi Role — Aplikasi Kewer

**Terakhir Diperbarui:** 2026-05-03  
**Struktur:** Single Office (kantor_id = 1)

---

## Ringkasan

Direktori ini berisi file JSON konfigurasi untuk setiap role dalam aplikasi Kewer. File-file ini mendefinisikan permissions, akses modul, dan parameter simulasi untuk setiap role sesuai dengan yang ada di database.

---

## Format Aplikasi

| Aspek | Format |
|-------|--------|
| Bahasa UI | Bahasa Indonesia |
| Mata Uang | `Rp1.000.000` (titik ribuan, koma desimal) |
| Tanggal tampilan | `d F Y` → contoh: `16 April 2026` |
| Tanggal database | `Y-m-d` → contoh: `2026-04-16` |
| Library tanggal | Flatpickr `altFormat: 'd F Y'` |

---

## Hierarki Role (Sesuai Database)

```
appOwner (level 0)
  — Platform owner: billing, approvals, AI advisor
  — Tidak akses data koperasi (nasabah, pinjaman, dll.)
  — Login: appowner / AppOwner2024!

bos (level 1)
  — Pemilik koperasi, akses penuh operasional
  — Login: patri / Kewer2024!

manager_pusat (level 3)
  — Kontrol operasional, approve pinjaman, kelola staff
  — Login: mgr_pusat / Kewer2024!

manager_cabang (level 4)
  — Operasional harian, approve pinjaman
  — Login: mgr_balige / Kewer2024!

admin_pusat (level 5)
  — Input nasabah, pinjaman, angsuran, laporan
  — Login: adm_pusat / Kewer2024!

petugas_pusat (level 7)
  — Koleksi angsuran lapangan, kas petugas
  — Login: ptr_pngr1 / Kewer2024!

petugas_cabang (level 8)
  — Koleksi angsuran lapangan, aktivitas lapangan
  — Login: ptr_pngr2 / Kewer2024!

karyawan (level 9)
  — Dukungan administratif, rekonsiliasi kas
  — Login: krw_pngr / Kewer2024!
```

---

## File Role yang Tersedia

| File | Role | Level |
|------|------|-------|
| `appOwner.json` | appOwner | 0 |
| `bos.json` | bos | 1 |
| `manager_pusat.json` | manager_pusat | 3 |
| `manager_cabang.json` | manager_cabang | 4 |
| `admin_pusat.json` | admin_pusat | 5 |
| `admin_cabang.json` | admin_cabang | 6 |
| `petugas_pusat.json` | petugas_pusat | 7 |
| `petugas_cabang.json` | petugas_cabang | 8 |
| `karyawan.json` | karyawan | 9 |

> File `superadmin.json` dan `manager.json` telah dihapus karena tidak ada di database.

---

## Struktur File JSON

```json
{
  "role": {
    "name": "Nama Role",
    "code": "kode_role",
    "hierarchy_level": 1,
    "description": "Deskripsi role",
    "status": "aktif"
  },
  "access_scope": {
    "type": "single_office",
    "branches": "single",
    "data_visibility": "all"
  },
  "permissions": {
    "automatic_grant": true,
    "can_manage": ["role_dibawahnya"]
  },
  "modules": {
    "nama_modul": {
      "access": true,
      "permissions": ["permission.code"],
      "features": ["Deskripsi fitur"]
    }
  },
  "simulation_parameters": {
    "login": {
      "username": "username",
      "password": "Kewer2024!",
      "test_login_url": "/login.php?test_login=true&username=username&password=Kewer2024!"
    }
  }
}
```

---

## Matriks Akses Modul

| Modul | appOwner | bos | mgr_pusat | mgr_cabang | adm_pusat | ptr_pusat | ptr_cabang | karyawan |
|-------|----------|-----|-----------|------------|-----------|-----------|------------|----------|
| Dashboard | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| Nasabah | ❌ | ✅ | ✅ | ✅ | ✅ | 👁 | 👁 | 👁 |
| Pinjaman | ❌ | ✅+✓ | ✅+✓ | ✅+✓ | ✅ | 👁 | 👁 | 👁 |
| Angsuran | ❌ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | 👁 |
| Aktivitas Lapangan | ❌ | 👁 | 👁 | 👁 | 👁 | ✅ | ✅ | ❌ |
| Kas Petugas | ❌ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ❌ |
| Rekonsiliasi Kas | ❌ | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ✅ |
| Auto-Confirm | ❌ | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ |
| Users | ❌ | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ |
| Cabang | ❌ | ❌ | ❌ | ❌ | 👁 | ❌ | ❌ | ❌ |
| Laporan | ❌ | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ |
| Pengeluaran | ❌ | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ | 👁 |
| Kas Bon | ❌ | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ |
| Setting Bunga | ❌ | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ |
| Family Risk | ❌ | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ |
| Petugas | ❌ | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ |
| Audit | ❌ | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| Platform Mgmt | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |

**Keterangan:** ✅ akses penuh | 👁 read-only | ✓ approve | ❌ tidak ada akses

---

## Aturan Hierarki (canManageRole)

| Role | Dapat Mengelola |
|------|----------------|
| bos | semua role kecuali bos |
| manager_pusat | admin_pusat, admin_cabang, manager_cabang, petugas_pusat, petugas_cabang, karyawan |
| manager_cabang | admin_cabang, petugas_cabang, karyawan |
| admin_pusat | petugas_pusat, petugas_cabang, karyawan |
| admin_cabang | petugas_cabang, karyawan |
| appOwner | tidak mengelola role koperasi |

---

## Catatan Penting

1. **Single Office:** Aplikasi saat ini dikonfigurasi untuk satu kantor (`kantor_id = 1`). Tidak ada fitur multi-cabang aktif.
2. **`hasPermission()`:** Implementasi di `includes/functions.php`. Bos mendapat semua permission otomatis. appOwner hanya punya: `manage_app`, `approve_bos`, `view_koperasi`, `suspend_koperasi`.
3. **Delegated Permissions:** Karyawan mendapatkan permission melalui tabel `delegated_permissions` — bisa berbeda antar karyawan.
4. **Quick Login:** Hanya aktif saat `APP_ENV=development` di file `.env`.
5. **Password:** Semua user koperasi: `Kewer2024!` | appOwner: `AppOwner2024!`
