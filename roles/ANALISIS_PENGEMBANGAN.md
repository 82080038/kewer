# Analisis dan Rekomendasi Pengembangan Aplikasi Kewer

**Tanggal Analisis:** 2026-04-16
**Jenis Usaha:** Koperasi Pasar / Usaha Pinjaman Pribadi (Bank Keliling)
**Target Nasabah:** Pedagang pasar, pelaku UMKM
**Model Bisnis:** Meminjamkan uang dengan angsuran harian, mingguan, atau bulanan. Petugas keliling mengutip angsuran langsung ke lokasi nasabah.
**Sumber Riset:** KSP Kemuning, Koperasi Sentra Dana, Koperasi ASA, Berkah Mulya, Serambi Dana, dan referensi microfinance (Loandisk, Clappia, LendFusion)

---

## A. Fitur yang SUDAH ADA di Kewer

| No | Fitur | Status | Catatan |
|----|-------|--------|---------|
| 1 | Dashboard statistik per cabang | ✅ Ada | |
| 2 | Manajemen Nasabah | ✅ Ada | Ada field `lokasi_pasar`, `jenis_usaha` |
| 3 | Manajemen Pinjaman | ✅ Ada | ⚠️ Hanya tenor bulanan (1-12 bulan) |
| 4 | Manajemen Angsuran | ✅ Ada | ⚠️ Jadwal hanya dibuat per bulan |
| 5 | Pembayaran | ✅ Ada | |
| 6 | Setting Bunga Dinamis | ✅ Ada | Flat, efektif, anuitas |
| 7 | Multi-Cabang | ✅ Ada | |
| 8 | Auto-Confirm Pinjaman | ✅ Ada | Persetujuan otomatis berdasarkan threshold |
| 9 | Aktivitas Lapangan (GPS, Foto) | ✅ Ada | Survey, kutip angsuran, follow up |
| 10 | Kas Petugas | ✅ Ada | Saldo awal, terima, setor |
| 11 | Rekonsiliasi Kas Harian | ✅ Ada | |
| 12 | Pengeluaran | ✅ Ada | Gaji, operasional, dll |
| 13 | Kas Bon Karyawan | ✅ Ada | Untuk internal karyawan |
| 14 | Family Risk | ✅ Ada | Risiko keluarga per alamat |
| 15 | Jaminan | ✅ Sebagian | Ada field `jaminan_tipe` (tanpa/bpkb/shm/ajb/tabungan) tapi belum ada UI kelola |
| 16 | RBAC (Role-Based Access) | ✅ Ada | 7 level role |
| 17 | Laporan | ✅ Ada | Baru terintegrasi ke UI |
| 18 | Blacklist Nasabah | ✅ Sebagian | Status `blacklist` ada di tabel nasabah, tapi belum ada UI kelola |
| 19 | Audit Log | ✅ Sebagian | Tabel `audit_log` ada, tapi belum ada UI dan belum aktif pencatatan |
| 20 | Denda | ✅ Sebagian | Kolom `denda` ada di tabel angsuran & pembayaran, tapi belum ada perhitungan otomatis |

---

## B. Masalah Kritis: Frekuensi Angsuran Hanya Bulanan

**Ini masalah paling mendasar.** Koperasi pasar / bank keliling umumnya menawarkan:
- **Pinjaman Harian** — angsuran setiap hari kerja (contoh: 60-100 hari)
- **Pinjaman Mingguan** — angsuran setiap minggu (contoh: 8-15 minggu)
- **Pinjaman Bulanan** — angsuran setiap bulan (contoh: 1-12 bulan)

**Kondisi saat ini di Kewer:**
- `createLoanSchedule()` hanya generate jadwal **per bulan** (`+$i month`)
- Validasi tenor dibatasi **1-12 (bulan)**
- Tabel `pinjaman` tidak punya kolom frekuensi pembayaran
- Tabel `setting_bunga` tidak punya kolom frekuensi

**Referensi dari industri:**
- KSP Kemuning: Kasbon Mingguan (Rp500rb-Rp5jt) + Kemuning Harian
- Koperasi Sentra Dana: Tenor 11 minggu, angsuran per minggu
- Koperasi ASA: Tenor 8 minggu
- KSP Berkah Mulya: Tenor 8 minggu
- Plafon tipikal: Rp300rb sampai Rp50jt

---

## C. Fitur yang PERLU Dikembangkan

### 🔴 Prioritas TINGGI (Inti Bisnis Koperasi Pasar)

#### 1. Frekuensi Angsuran: Harian, Mingguan, Bulanan
**Masalah:** Saat ini hanya mendukung angsuran bulanan. Padahal bisnis utama koperasi pasar adalah kutipan harian/mingguan.

**Perubahan yang Dibutuhkan:**

**Database:**
- Tambah kolom `frekuensi` di tabel `pinjaman`: `enum('harian','mingguan','bulanan')`
- Tambah kolom `frekuensi` di tabel `setting_bunga`
- Ubah validasi tenor: harian (max 100), mingguan (max 52), bulanan (max 12)

**Backend:**
- Update `createLoanSchedule()`:
  - Harian: `+$i weekday` (hari kerja) atau `+$i day`
  - Mingguan: `+$i week`
  - Bulanan: `+$i month` (sudah ada)
- Update `calculateLoan()` untuk menghitung bunga sesuai frekuensi
- Update `BungaCalculator` untuk mendukung jenis frekuensi
- Update API `api/pinjaman.php` — hapus validasi tenor max 12

**Frontend:**
- Tambah dropdown "Frekuensi Angsuran" di form pengajuan pinjaman
- Tampilkan jadwal angsuran sesuai frekuensi
- Filter pinjaman berdasarkan frekuensi

**Role yang Terlibat:** Semua role yang mengelola pinjaman

---

#### 2. Denda Keterlambatan Otomatis
**Masalah:** Kolom `denda` sudah ada di tabel `angsuran` dan `pembayaran`, tapi tidak ada perhitungan otomatis.

**Fitur yang Dibutuhkan:**
- Konfigurasi tarif denda per cabang:
  - Persentase dari angsuran per hari terlambat, ATAU
  - Nominal tetap per hari terlambat
- Grace period (masa toleransi) yang dapat dikonfigurasi
- Auto-calculate denda saat petugas mengutip pembayaran
- Waive denda (pembebasan) oleh Manager/Owner
- Laporan denda per nasabah dan per periode
- Kolom `denda` sudah tersedia, tinggal aktifkan logikanya

**Role yang Terlibat:**
| Role | Akses |
|------|-------|
| Owner | Konfigurasi tarif denda, lihat laporan |
| Manager | Konfigurasi cabang, waive denda, laporan |
| Admin | Lihat laporan denda |
| Petugas | Kutip angsuran + denda di lapangan |
| Karyawan | Input pembayaran + denda di kantor |

---

#### 3. Blacklist Nasabah (Lengkapi UI)
**Masalah:** Status `blacklist` sudah ada di enum tabel `nasabah`, tapi belum ada antarmuka untuk mengelolanya.

**Fitur yang Dibutuhkan:**
- Halaman daftar nasabah blacklist
- Tombol "Blacklist" di detail nasabah + form alasan
- Blokir otomatis pengajuan pinjaman baru jika nasabah blacklist
- Proses "Unblocklist" hanya oleh Manager/Owner
- Histori blacklist (siapa yang mem-blacklist, kapan, alasan)
- Integrasi dengan Family Risk — blacklist otomatis jika skor risiko keluarga tinggi

---

#### 4. Cetak Kwitansi & Kartu Angsuran
**Referensi:** Semua koperasi pasar memberikan bukti bayar ke nasabah.

**Fitur yang Dibutuhkan:**
- **Cetak kwitansi pembayaran** — saat petugas mengutip angsuran
- **Cetak kwitansi pencairan** — saat uang dicairkan ke nasabah
- **Cetak kartu angsuran** — daftar jadwal angsuran lengkap nasabah
- Format kertas kecil (thermal printer 58mm) untuk petugas lapangan
- Format A4 untuk cetak di kantor
- Template dengan logo dan info cabang

---

### 🟡 Prioritas SEDANG (Meningkatkan Efisiensi Operasional)

#### 5. Notifikasi WhatsApp Otomatis
**Referensi:** Semua koperasi modern menggunakan notifikasi digital untuk mengurangi tunggakan.

**Fitur yang Dibutuhkan:**
- Pengingat jatuh tempo: H-1 untuk harian, H-1 untuk mingguan, H-3 untuk bulanan
- Notifikasi tunggakan otomatis
- Konfirmasi pembayaran diterima
- Notifikasi pencairan pinjaman
- Integrasi WhatsApp API (Fonnte, Wablas, atau WA Gateway lokal)

**Role yang Terlibat:**
| Role | Akses |
|------|-------|
| Owner | Konfigurasi API & template |
| Manager | Lihat log pengiriman |
| Admin | Kelola template pesan |
| Petugas | Kirim pengingat manual ke nasabah |

---

#### 6. Audit Trail (Aktifkan & Buat UI)
**Masalah:** Tabel `audit_log` dan `permission_audit_log` sudah ada, tapi tidak ada:
- Pencatatan otomatis saat operasi CRUD
- Halaman UI untuk melihat log
- Export log

**Fitur yang Dibutuhkan:**
- Auto-log setiap aksi: buat pinjaman, bayar angsuran, approve, reject, blacklist, dll.
- Log login/logout
- Halaman "Audit Trail" dengan filter: user, tanggal, modul, aksi
- Export CSV/Excel untuk audit

---

#### 7. Manajemen Jaminan (Lengkapi UI)
**Masalah:** Field `jaminan_tipe`, `jaminan_nilai`, `jaminan_dokumen` sudah ada di tabel `pinjaman`, tapi belum dikelola dengan baik.

**Fitur yang Dibutuhkan:**
- Form input jaminan yang lebih lengkap saat pengajuan pinjaman
- Upload foto/scan dokumen jaminan (BPKB, sertifikat, dll.)
- Status jaminan: disimpan di kantor / dikembalikan / disita
- Daftar jaminan yang disimpan per cabang
- Peringatan jaminan saat pinjaman lunas (harus dikembalikan)

---

#### 8. Rute Harian Petugas
**Referensi:** Koperasi pasar keliling membutuhkan perencanaan rute kutipan.

**Fitur yang Dibutuhkan:**
- Daftar nasabah yang harus dikunjungi hari ini (berdasarkan jadwal angsuran)
- Urutkan berdasarkan lokasi pasar / alamat
- Tandai sudah dikunjungi / belum
- Ringkasan hasil kutipan harian
- Integrasi dengan GPS (sudah ada field `latitude`/`longitude` di `field_officer_activities`)

**Role:** Petugas Pusat & Cabang

---

#### 9. Dashboard Kinerja Petugas
**Deskripsi:** Owner dan Manager perlu memantau kinerja petugas lapangan.

**Fitur yang Dibutuhkan:**
- Total kutipan per petugas per hari/minggu/bulan
- Persentase berhasil vs gagal kutip
- Jumlah nasabah yang dikunjungi
- Selisih kas petugas (sudah ada di `kas_petugas`)
- Ranking petugas berdasarkan kinerja

---

### 🟢 Prioritas RENDAH (Pengembangan Jangka Panjang)

#### 10. Pinjaman Top-Up / Perpanjangan
**Deskripsi:** Nasabah yang sudah lunas bisa langsung pinjam lagi (top-up) tanpa proses pengajuan dari awal.

**Fitur:**
- Quick re-loan untuk nasabah yang track record-nya bagus
- Plafon otomatis naik jika pembayaran lancar
- Proses persetujuan lebih cepat

---

#### 11. Laporan Laba Rugi Sederhana
**Deskripsi:** Bukan SAK EP formal, tapi ringkasan sederhana untuk pemilik usaha.

**Fitur:**
- Pendapatan bunga per periode
- Total pengeluaran operasional
- Laba/rugi bersih
- Perbandingan antar periode
- Perbandingan antar cabang

---

#### 12. Credit Scoring Sederhana
**Deskripsi:** Penilaian otomatis kelayakan nasabah berdasarkan histori.

**Fitur:**
- Skor berdasarkan riwayat pembayaran (sudah ada dasar di `BungaCalculator::getRisikoAdjustment`)
- Skor berdasarkan family risk (sudah ada `skor_risiko_keluarga`)
- Rekomendasi plafon dan tenor otomatis
- Indikator visual (hijau/kuning/merah) di profil nasabah

---

#### 13. Integrasi Pembayaran Digital
**Fitur:**
- Transfer Bank (pembayaran non-tunai)
- QRIS untuk pembayaran di tempat
- E-wallet

---

## D. Rekomendasi Fitur per Role

### Owner
| Fitur Baru | Deskripsi |
|------------|-----------|
| Konfigurasi frekuensi angsuran | Atur jenis pinjaman: harian/mingguan/bulanan |
| Konfigurasi denda | Atur tarif denda keterlambatan |
| Blacklist management | Persetujuan blacklist/unblocklist |
| Kinerja petugas | Dashboard performa petugas semua cabang |
| Laporan laba rugi | Ringkasan pendapatan vs pengeluaran |
| Audit trail | Akses penuh ke log aktivitas |

### Manager
| Fitur Baru | Deskripsi |
|------------|-----------|
| Pinjaman multi-frekuensi | Input pinjaman harian/mingguan/bulanan |
| Waive denda | Pembebasan denda nasabah |
| Blacklist nasabah | Tandai nasabah bermasalah |
| Kelola jaminan | Input dan pengelolaan jaminan |
| Cetak dokumen | Kwitansi, kartu angsuran |
| Kinerja petugas | Dashboard performa petugas cabang |
| Kirim notifikasi | WhatsApp ke nasabah |

### Admin (Pusat & Cabang)
| Fitur Baru | Deskripsi |
|------------|-----------|
| Pinjaman multi-frekuensi | Input pinjaman dengan pilihan frekuensi |
| Cetak dokumen | Kwitansi pencairan, kartu angsuran |
| Kelola jaminan | Input dan tracking jaminan |
| Audit trail | Lihat log aktivitas |

### Petugas (Pusat & Cabang)
| Fitur Baru | Deskripsi |
|------------|-----------|
| Rute harian | Daftar nasabah yang harus dikunjungi hari ini |
| Kutipan + denda | Catat pembayaran + denda otomatis |
| Cetak kwitansi | Bukti pembayaran di lapangan |
| Notifikasi manual | Kirim pengingat ke nasabah via WhatsApp |

### Karyawan
| Fitur Baru | Deskripsi |
|------------|-----------|
| Input pembayaran + denda | Catat pembayaran di kantor |
| Cetak kwitansi | Cetak bukti transaksi |

---

## E. Roadmap Pengembangan

### Fase 1 — Inti Bisnis (KRITIS)
1. ✅ ~~Modul Laporan~~ (sudah dibuat)
2. 🔲 **Frekuensi Angsuran Harian/Mingguan/Bulanan** ← PALING PENTING
3. 🔲 Denda Keterlambatan Otomatis
4. 🔲 Blacklist Nasabah (UI)

### Fase 2 — Operasional Lapangan
5. 🔲 Cetak Kwitansi & Kartu Angsuran
6. 🔲 Rute Harian Petugas
7. 🔲 Notifikasi WhatsApp
8. 🔲 Dashboard Kinerja Petugas

### Fase 3 — Tata Kelola & Audit
9. 🔲 Audit Trail (aktifkan & buat UI)
10. 🔲 Manajemen Jaminan (UI)
11. 🔲 Laporan Laba Rugi Sederhana

### Fase 4 — Pengembangan Lanjut
12. 🔲 Pinjaman Top-Up
13. 🔲 Credit Scoring Sederhana
14. 🔲 Integrasi Pembayaran Digital

---

## F. Sumber Referensi

| Sumber | URL | Catatan |
|--------|-----|---------|
| KSP Kemuning | kspkemuning.co.id | Kasbon mingguan + harian, plafon Rp500rb-Rp5jt |
| Koperasi Sentra Dana | - | Bank keliling, tenor 11 minggu |
| Koperasi ASA | - | Bank keliling, tenor 8 minggu, Rp300rb-Rp3.5jt |
| KSP Berkah Mulya | - | Bank keliling, tenor 8 minggu, pencairan 1 hari |
| KSP Serambi Dana | - | Pinjaman mingguan, Rp1jt-Rp3jt |
| Loandisk | loandisk.com | Microfinance management system |
| LendFusion | lendfusion.com | Microfinance lending platform |
| Clappia | clappia.com | Loan collection app dengan GPS tracking |

---

**Versi Dokumen:** 2.0 (Revisi — disesuaikan untuk model bisnis Koperasi Pasar / Bank Keliling)
**Dibuat Oleh:** Cascade AI Assistant
**Tanggal:** 2026-04-16
