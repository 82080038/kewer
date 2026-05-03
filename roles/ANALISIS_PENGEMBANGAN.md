# Analisis dan Roadmap Pengembangan — Aplikasi Kewer

**Terakhir Diperbarui:** 2026-05-03  
**Model Bisnis:** Koperasi Pasar / Bank Keliling  
**Target:** Pedagang pasar, pelaku UMKM  
**Struktur Saat Ini:** Single Office (`kantor_id = 1`)

---

## A. Fitur yang SUDAH ADA

| No | Fitur | Status | Catatan |
|----|-------|--------|---------|
| 1 | Dashboard statistik | ✅ | Single office, tidak ada branch selector |
| 2 | Manajemen Nasabah | ✅ | KTP, foto, OCR, blacklist, family risk |
| 3 | Pinjaman harian/mingguan/bulanan | ✅ | Frekuensi angsuran lengkap |
| 4 | Angsuran & Jadwal | ✅ | Auto-generate sesuai frekuensi |
| 5 | Pembayaran & Denda | ✅ | Denda otomatis saat bayar terlambat |
| 6 | Setting Bunga Dinamis | ✅ | Flat, efektif, anuitas |
| 7 | Auto-Confirm Pinjaman | ✅ | Approval otomatis berdasarkan threshold |
| 8 | Aktivitas Lapangan | ✅ | GPS, foto, survey, kutip angsuran |
| 9 | Kas Petugas | ✅ | Saldo, terima, setoran |
| 10 | Rekonsiliasi Kas Harian | ✅ | |
| 11 | Pengeluaran | ✅ | Gaji, operasional, dll |
| 12 | Kas Bon Karyawan | ✅ | |
| 13 | Family Risk | ✅ | Skor risiko per keluarga/alamat |
| 14 | Blacklist Nasabah | ✅ | Status + history log |
| 15 | RBAC (Role-Based Access) | ✅ | 9 role levels + appOwner |
| 16 | Laporan | ✅ | Keuangan, pinjaman, nasabah |
| 17 | Audit Log | ✅ Sebagian | Tabel ada, UI perlu dilengkapi |
| 18 | Jaminan | ✅ Sebagian | Field ada, UI manajemen belum lengkap |
| 19 | Cetak Kwitansi/Kartu Angsuran | ✅ Sebagian | Dasar ada, perlu format thermal |
| 20 | WhatsApp Notifikasi | ✅ | Twilio, Wablas, Fonnte |
| 21 | PDF Export | ✅ | DomPDF |
| 22 | OCR KTP | ✅ | Tesseract |
| 23 | appOwner Platform Layer | ✅ | Billing, usage, AI advisor |
| 24 | Multi-DB (3 database) | ✅ | kewer, db_alamat_simple, db_orang |
| 25 | Simulasi Puppeteer | ✅ | 8 role × 14 hari, single office |

---

## B. Role & Database (Status Saat Ini)

### Role yang Ada di Database
| Role | Username | Level | Password |
|------|----------|-------|----------|
| appOwner | appowner | 0 | AppOwner2024! |
| bos | patri | 1 | Kewer2024! |
| manager_pusat | mgr_pusat | 3 | Kewer2024! |
| manager_cabang | mgr_balige | 4 | Kewer2024! |
| admin_pusat | adm_pusat | 5 | Kewer2024! |
| petugas_pusat | ptr_pngr1 | 7 | Kewer2024! |
| petugas_cabang | ptr_pngr2 | 8 | Kewer2024! |
| karyawan | krw_pngr | 9 | Kewer2024! |

### Role yang SUDAH DIHAPUS (tidak ada di DB)
- ~~superadmin~~ — diganti dengan `appOwner`
- ~~manager~~ — duplikat, diganti `manager_pusat`/`manager_cabang`

---

## C. Fitur yang PERLU Dikembangkan

### 🔴 Prioritas TINGGI

#### 1. Audit Trail — Lengkapi UI
- Auto-log setiap aksi CRUD (nasabah, pinjaman, angsuran, pembayaran)
- Log login/logout
- Halaman UI dengan filter: user, tanggal, modul, aksi
- Export CSV

#### 2. Manajemen Jaminan — Lengkapi UI
- Field `jaminan_tipe`, `jaminan_nilai` sudah ada di tabel `pinjaman`
- Upload foto/scan dokumen jaminan
- Status: disimpan / dikembalikan / disita
- Daftar jaminan per kantor
- Notifikasi saat pinjaman lunas

#### 3. Cetak Kwitansi Format Thermal
- Format thermal 58mm untuk petugas lapangan
- Format A4 untuk kantor
- Template dengan logo dan info kantor

### 🟡 Prioritas SEDANG

#### 4. Rute Harian Petugas
- Daftar nasabah yang harus dikunjungi hari ini (berdasarkan jadwal angsuran)
- Urutkan berdasarkan lokasi pasar/alamat
- Tandai sudah dikunjungi / belum
- Ringkasan hasil kutipan harian
- Integrasi GPS (`latitude`/`longitude` sudah ada di `field_officer_activities`)

#### 5. Dashboard Kinerja Petugas
- Total kutipan per petugas per hari/minggu/bulan
- Persentase berhasil vs gagal kutip
- Ranking petugas berdasarkan kinerja

#### 6. Laporan Laba Rugi Sederhana
- Pendapatan bunga per periode
- Total pengeluaran operasional
- Laba/rugi bersih
- Perbandingan antar periode

### 🟢 Prioritas RENDAH

#### 7. Pinjaman Top-Up / Perpanjangan
- Re-loan cepat untuk nasabah dengan track record bagus
- Plafon otomatis naik jika pembayaran lancar

#### 8. Credit Scoring Sederhana
- Skor berdasarkan riwayat pembayaran
- Integrasi dengan `skor_risiko_keluarga` (sudah ada)
- Rekomendasi plafon & tenor otomatis
- Indikator visual (hijau/kuning/merah) di profil nasabah

#### 9. Integrasi Pembayaran Digital
- Transfer bank (non-tunai)
- QRIS

---

## D. Rekomendasi Fitur per Role

### appOwner
| Fitur | Keterangan |
|-------|-----------|
| Platform audit | Log aktivitas semua koperasi |
| Usage analytics | Grafik penggunaan per koperasi |
| Billing automation | Invoice otomatis |

### bos
| Fitur | Keterangan |
|-------|-----------|
| Dashboard kinerja petugas | Ranking, total kutipan, % berhasil |
| Laporan laba rugi | Pendapatan bunga vs pengeluaran |
| Konfigurasi denda | Tarif per hari, grace period |
| Audit trail | Akses penuh log aktivitas |

### manager_pusat / manager_cabang
| Fitur | Keterangan |
|-------|-----------|
| Kelola jaminan | Input, status, daftar jaminan |
| Waive denda | Pembebasan denda nasabah |
| Dashboard kinerja petugas | Performa petugas di bawahnya |

### admin_pusat / admin_cabang
| Fitur | Keterangan |
|-------|-----------|
| Cetak kwitansi & kartu angsuran | Format A4 |
| Kelola jaminan | Input saat pengajuan pinjaman |

### petugas_pusat / petugas_cabang
| Fitur | Keterangan |
|-------|-----------|
| Rute harian | Daftar kunjungan berdasarkan jadwal angsuran |
| Cetak kwitansi thermal | Bukti pembayaran di lapangan |
| Notifikasi manual | Kirim pengingat WhatsApp ke nasabah |

### karyawan
| Fitur | Keterangan |
|-------|-----------|
| Cetak kwitansi | Format A4 untuk pembayaran di kantor |
| Rekonsiliasi otomatis | Matching kas masuk vs angsuran terbayar |

---

## E. Roadmap

### Fase 1 — Tata Kelola & Audit (Segera)
- [ ] Audit Trail UI (halaman, filter, export)
- [ ] Manajemen Jaminan UI (upload, status)
- [ ] Cetak Kwitansi Thermal

### Fase 2 — Operasional Lapangan
- [ ] Rute Harian Petugas
- [ ] Dashboard Kinerja Petugas
- [ ] Notifikasi WhatsApp pengingat jatuh tempo

### Fase 3 — Analitik & Keuangan
- [ ] Laporan Laba Rugi Sederhana
- [ ] Credit Scoring Sederhana
- [ ] Top-Up / Perpanjangan Pinjaman

### Fase 4 — Integrasi Lanjut
- [ ] QRIS / Transfer Bank
- [ ] Mobile app (PWA)
- [ ] Multi-language support

---

## F. Referensi Industri

| Sumber | Catatan |
|--------|---------|
| KSP Kemuning | Kasbon mingguan + harian, Rp500rb–Rp5jt |
| Koperasi Sentra Dana | Bank keliling, tenor 11 minggu |
| Koperasi ASA | Bank keliling, tenor 8 minggu, Rp300rb–Rp3.5jt |
| KSP Berkah Mulya | Bank keliling, tenor 8 minggu, pencairan 1 hari |
| KSP Serambi Dana | Pinjaman mingguan, Rp1jt–Rp3jt |
| Loandisk | Microfinance management system |
| LendFusion | Microfinance lending platform |
| Clappia | Loan collection app dengan GPS tracking |
