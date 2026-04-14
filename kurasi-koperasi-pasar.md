# Kurasi Analisa & Pengetahuan Usaha Pinjaman Pasar
## Digitalisasi Usaha Pinjaman Modal Pedagang (Pemilik Perorangan)

---

## 1. PENDAHULUAN

### 1.1 Konteks Bisnis
- **Model Bisnis**: Bisnis pinjaman uang pribadi (perorangan) dengan bunga dinamis
- **Bentuk Usaha**: Bukan koperasi berbadan hukum, bukan bank — bisnis pinjaman perorangan yang dikelola sendiri dengan beberapa cabang dan petugas lapangan
- **Posisi di Masyarakat**: Secara informal disebut "kewer-kewer" atau "koperasi", namun sesungguhnya adalah layanan pinjaman modal cepat untuk pedagang kecil
- **Peran Sosial**: Sangat membantu pedagang pasar, warung, dan UMKM yang tidak bisa akses bank karena syarat administrasi sulit, proses lambat, dan tidak ada agunan formal
- **Target Pasar**: Pedagang pasar, warung, emak-emak pedagang kecil, UMKM informal
- **Metode Tradisional**: Petugas lapangan mendatangi pasar/warung setiap hari
- **Tujuan Digitalisasi**: Transformasi proses manual menjadi sistem berbasis aplikasi web

### 1.2 Teknologi yang Digunakan
- **Backend**: PHP (Native / Framework)
- **Frontend**: Bootstrap 5, jQuery, AJAX
- **Database**: MySQL
- **API**: REST API untuk komunikasi data
- **Responsive**: Mobile-first design, adaptive layout

---

## 2. KARAKTERISTIK BISNIS PINJAMAN PASAR

### 2.1 Definisi & Posisi Usaha
Usaha ini adalah **bisnis pinjaman modal perorangan** yang melayani pedagang pasar, warung, dan UMKM informal. Di masyarakat sering disebut *"kewer-kewer"*, *"bank harian"*, *"bank plecit"*, atau *"koperasi"* meski tidak berbadan hukum koperasi resmi.

**Perbedaan dengan Koperasi Resmi**:
| Aspek | Koperasi Resmi | Bisnis Pinjaman Perorangan (Anda) |
|-------|---------------|-----------------------------------|
| Badan Hukum | Ada (Kemenkop) | Tidak (milik pribadi) |
| Anggota | Banyak pemilik | 1 pemilik (Anda) |
| Regulasi | Ketat (OJK/Kemenkop) | Fleksibel |
| Modal | Dari simpanan anggota | Modal pribadi/investor |
| Keputusan | Rapat anggota | Anda sendiri |
| Bunga | Terbatas regulasi | Dinamis, sesuai kesepakatan |
| Risiko | Kolektif | Ditanggung Anda |

**Mengapa Usaha Ini Dibutuhkan Masyarakat**:
- Bank mensyaratkan rekening, NPWP, agunan, proses 2-4 minggu
- Pedagang kecil butuh modal **hari ini, saat ini juga**
- Pinjaman Rp 200.000 - Rp 5.000.000 tidak dilayani bank
- Fleksibilitas angsuran (harian/mingguan) sesuai ritme usaha pedagang
- Hubungan personal: Petugas kenal nasabah langsung

**Posisi Legal**:
- Pinjaman antar individu (tanpa badan hukum) **tidak dilarang** di Indonesia
- Masuk ranah hukum perdata (perjanjian utang-piutang)
- Perjanjian tertulis/surat pernyataan menjadi dasar hukum yang sah
- Risiko legal: Jika dikelola etis & transparan, relatif aman

### 2.2 Pola Operasional Tradisional
| Aspek | Deskripsi |
|-------|-----------|
| **Kunjungan** | Petugas lapangan berkeliling ke pasar/warung setiap hari |
| **Pencarian Nasabah** | Mendatangi pedagang untuk menawarkan pinjaman |
| **Pengumpulan** | Mengumpulkan angsuran/cicilan harian/mingguan |
| **Monitoring** | Menjalin hubungan dan mengingatkan angsuran yang telat |
| **Tabungan** | Menabung rutin tiap minggu/hari (dijemput/diantar) |

### 2.3 Produk Pinjaman yang Umum

#### A. Pinjaman Harian Wirausaha
- **Jangka waktu**: 1 hari (pagi pinjam, sore bayar)
- **Tujuan**: Modal usaha dagang di pasar
- **Jasa/bunga**: 2.5% per hari atau flat
- **Jaminan**: Aset (SHM, AJB) atau tanpa agunan untuk pinjaman kecil

#### B. Pinjaman Mingguan/Bulanan
- **Jangka waktu**: 1 minggu / 1 bulan
- **Angsuran**: Harian/mingguan sesuai perjanjian
- **Jasa/bunga**: 2-2.5% per bulan (variatif)
- **Sistem**: Angsuran pokok + jasa dipotong dari saldo simpanan

#### C. Pinjaman Multi Guna
- **Jangka waktu**: 2-24 bulan
- **Jaminan**: Aset tetap (SHM, AJB, BPKB)
- **Angsuran**: Maksimal 30% dari penghasilan bersih
- **Jasa**: 2-2.5% per bulan

### 2.4 Sumber Referensi Koperasi Nyata
1. **KSP Makmur Mandiri** - Pinjaman Harian Wirausaha (dipinjam pagi, kembali sore)
2. **KSP Kemuning** - Kasbon Mingguan dengan tenor harian
3. **Koperasi Pasar tradisional** - Petugas lapangan berkeliling ke pedagang

---

## 3. ANALISA KEBUTUHAN DIGITALISASI

### 3.1 User/Pengguna Sistem

#### A. Admin/Pemilik (Anda)
- Dashboard manajemen keseluruhan
- Monitoring pinjaman dan kolektibilitas
- Laporan keuangan dan statistik
- Manajemen bunga dinamis
- Pengaturan parameter sistem

#### B. Petugas Lapangan (Collector)
- Aplikasi mobile/tablet untuk kunjungan
- Daftar jadwal kunjungan harian
- Input pembayaran angsuran di lapangan
- Update status kunjungan
- Tracking lokasi (opsional)

#### C. Nasabah (Pedagang)
- Cek saldo pinjaman dan sisa angsuran
- Riwayat pembayaran
- Pengajuan pinjaman baru
- Notifikasi jatuh tempo
- Profil dan dokumen nasabah

### 3.2 Modul Sistem yang Diperlukan

#### A. Modul Master Data
```
- Manajemen Nasabah
  - Data pribadi (KTP, KK, foto)
  - Data usaha (jenis dagangan, lokasi pasar/warung)
  - Data keluarga (penjamin, emergency contact)
  - Dokumen (upload scan KTP, KK, surat perjanjian)

- Manajemen Petugas Lapangan
  - Data petugas
  - Area/jalur kunjungan
  - Target kunjungan harian

- Manajemen Wilayah/Lokasi
  - Pasar (nama, alamat, zona)
  - Warung/cluster pedagang
  - Zona untuk petugas
```

#### B. Modul Pinjaman
```
- Pengajuan Pinjaman
  - Form pengajuan online/offline
  - Analisa kredit (scoring)
  - Approval workflow

- Manajemen Pinjaman Aktif
  - Detail pinjaman (plafon, tenor, bunga)
  - Jadwal angsuran (harian/mingguan/bulanan)
  - Sisa pokok dan bunga
  - Status kolektibilitas (Lancar, Kurang Lancar, Diragukan, Macet)

- Perhitungan Bunga Dinamis
  - Sistem perhitungan bunga: Flat, Efektif, Anuitas
  - Pengaturan suku bunga berdasarkan:
    - Jenis pinjaman
    - Tenor
    - Profil risiko nasabah
    - History pembayaran
```

#### C. Modul Pembayaran/Angsuran
```
- Input Pembayaran
  - Via petugas lapangan (offline/online sync)
  - Via nasabah (transfer/manual record)
  - Multiple payment: cash, transfer, e-wallet

- Jadwal Angsuran
  - Generate otomatis jadwal angsuran
  - Reminder jatuh tempo (hari H-1, H)
  - Penalti/keterlambatan

- Koleksi/Collection
  - Daftar tunggakan per petugas
  - Status kunjungan (sudah dikunjungi/belum)
  - Catatan hasil kunjungan
```

#### D. Modul Tabungan (Opsional)
```
- Tabungan Umum (untuk nasabah yang mau menabung)
- Tabungan Auto-debet (angsuran otomatis dari tabungan)
- Tabungan Berjangka (dengan bunga)
- Setor/Tarik via petugas lapangan
- *Catatan: Tabungan bersifat opsional, bukan wajib*
```

#### E. Modul Laporan & Analisa
```
- Laporan Keuangan
  - Neraca
  - Laba Rugi
  - Arus Kas
  - Rekonsiliasi

- Laporan Pinjaman
  - Portofolio pinjaman
  - Kolektibilitas (NPL - Non Performing Loan)
  - Aging analysis (umur tunggakan)

- Laporan Collection
  - Target vs realisasi kunjungan petugas
  - Collection rate per petugas
  - Effectiveness rate

- Statistik & Dashboard
  - Total outstanding pinjaman (per cabang + konsolidasi)
  - Total nasabah aktif (per cabang + konsolidasi)
  - Grafik pertumbuhan (perbandingan cabang)
  - Heat map wilayah (pinjaman & risiko per cabang)
  
- Laporan Multi-Cabang (khusus Super Admin)
  - Laporan konsolidasi (merge semua cabang)
  - Perbandingan performa antar cabang
  - Top/Bottom performer cabang
  - Kas per cabang (stok kas, mutasi, rekap)
```

### 3.3 Struktur Multi-Cabang (Multi-Branch)

Karena Anda memiliki beberapa kantor cabang, sistem perlu mendukung:

#### A. Hierarki Organisasi
```
Pusat (Head Office - Anda)
    ├── Cabang 1 (Pasar A)
    │       ├── Petugas A1, A2
    │       └── Nasabah wilayah A
    ├── Cabang 2 (Pasar B)
    │       ├── Petugas B1, B2
    │       └── Nasabah wilayah B
    ├── Cabang 3 (Pasar C)
    │       ├── Petugas C1, C2
    │       └── Nasabah wilayah C
    └── ...
```

#### B. Keperluan Multi-Cabang
| Aspek | Deskripsi |
|-------|-----------|
| **Isolasi Data** | Cabang hanya lihat data cabangnya sendiri (kecuali admin pusat) |
| **Laporan Terpisah** | Laporan per cabang + konsolidasi pusat |
| **Transfer Nasabah** | Nasabah bisa pindah cabang dengan migrasi data |
| **Kode Unik** | Kode nasabah/petugas mengandung identifikasi cabang |
| **Stok Kas** | Kas per cabang terpisah, rekap ke pusat |
| **Performa** | Dashboard performa tiap cabang + perbandingan |

#### C. Role Berdasarkan Hierarki
| Role | Akses Cabang | Kewenangan |
|------|--------------|------------|
| **Super Admin (Anda)** | Semua cabang | Full access, lihat konsolidasi, setting sistem |
| **Manager Cabang** | Cabang tertentu | Approval pinjaman, lihat laporan cabangnya |
| **Petugas Lapangan** | Cabang tertentu | Input kunjungan, pembayaran cabangnya |
| **Nasabah** | Cabang terdaftar | Lihat data sendiri, bisa pindah cabang |

### 3.4 Struktur Database (Konsep)

#### Tabel Master Multi-Cabang:
```sql
-- Kantor Cabang
cabang (id, kode_cabang, nama_cabang, alamat, telp, 
        manager_id, tanggal_buka, status: aktif/nonaktif)

-- Setting per Cabang (bunga bisa berbeda antar cabang)
setting_cabang (id, cabang_id, parameter, nilai, deskripsi)
```

#### Tabel Utama (dengan cabang_id):
```sql
-- Nasabah (cabang_id untuk isolasi data)
nasabah (id, cabang_id, kode_nasabah, nama, alamat, ktp, telp, 
          jenis_usaha, lokasi_pasar, status, created_at)

-- Petugas Lapangan (terassign ke cabang tertentu)
petugas (id, cabang_id, kode_petugas, nama, telp, area_wilayah, 
         target_harian, status)

-- Pinjaman (inherit cabang dari nasabah)
pinjaman (id, cabang_id, kode_pinjaman, nasabah_id, jenis_pinjaman,
          plafon, tenor, bunga_per_hari/bulan, 
          total_bunga, total_angsuran,
          status: aktif/lunas/macet, created_at)

-- Jadwal Angsuran (inherit dari pinjaman)
angsuran (id, cabang_id, pinjaman_id, no_angsuran, jatuh_tempo,
          pokok, bunga, total_angsuran, 
          status: lunas/belum/telat, 
          tanggal_bayar, petugas_id)

-- Pembayaran (catat cabang untuk laporan kas per cabang)
pembayaran (id, cabang_id, angsuran_id, tanggal_bayar, jumlah,
             metode: cash/transfer, petugas_id, 
             bukti_transfer, catatan)

-- Tabungan (opsional, catat cabang untuk laporan kas per cabang)
tabungan (id, cabang_id, nasabah_id, jenis: umum/berjangka/autodebet,
            jumlah, tipe: setor/tarik, petugas_id, created_at)

-- Jadwal Kunjungan Petugas (per cabang)
kunjungan (id, cabang_id, petugas_id, tanggal, anggota_id_list,
           status: selesai/belum, hasil_kunjungan, catatan)

-- Setting Bunga Dinamis (bisa global atau per cabang)
setting_bunga (id, cabang_id [nullable], jenis_pinjaman, tenor_min, tenor_max,
               bunga_min, bunga_max, 
               faktor_risiko, created_at)
               
-- Users (admin, manager, petugas dengan cabang_id)
users (id, cabang_id [nullable], nama, username, password, 
       role: superadmin/manager/petugas, status)
```

---

## 4. DESAIN APLIKASI RESPONSIVE

### 4.1 Breakpoint & Device Strategy

| Device | Ukuran Layar | Fokus Penggunaan |
|--------|--------------|------------------|
| **Mobile** | < 576px | Petugas lapangan (input cepat), Nasabah (cek saldo) |
| **Tablet** | 576px - 991px | Petugas (form lebih lengkap), Admin (dashboard ringkas) |
| **Desktop** | > 991px | Admin (full dashboard, laporan, analisa) |

### 4.2 Adaptive Data Display

#### Mobile (Petugas Lapangan)
- Dashboard ringkas: target kunjungan hari ini
- List nasabah dengan quick-action (telpon, bayar, catatan)
- Form pembayaran minimalis (nominal saja, auto-recognize angsuran)
- Maps view (opsional untuk tracking kunjungan)

#### Tablet (Petugas/Admin Mobile)
- Split view: list nasabah + detail pinjaman
- Form input lengkap dengan validasi
- Preview jadwal angsuran

#### Desktop (Admin)
- Full dashboard dengan grafik dan statistik
- Multi-window: pinjaman, angsuran, laporan
- Data table dengan filter, sort, export
- Form pengaturan bunga dinamis

### 4.4 UI/UX untuk Multi-Cabang

#### A. Selector Cabang (untuk Super Admin)
```html
<!-- Dropdown selector cabang di navbar -->
<select class="form-select" id="cabangSelector">
  <option value="all">Semua Cabang (Konsolidasi)</option>
  <option value="1">Cabang Pasar A</option>
  <option value="2">Cabang Pasar B</option>
  <option value="3">Cabang Pasar C</option>
</select>
```

#### B. Badge/Indikator Cabang
- **Manager/Petugas**: Badge dengan nama cabang (hanya bisa lihat 1 cabang)
- **Super Admin**: Badge berubah sesuai cabang yang dipilih

#### C. Dashboard Multi-Cabang
- **Super Admin**: Dashboard konsolidasi (total semua cabang) + perbandingan cabang
- **Manager**: Dashboard khusus cabangnya saja
- **Petugas**: Dashboard target kunjungan cabangnya

### 4.5 Komponen UI Bootstrap 5 yang Direkomendasikan

```html
<!-- Layout Responsive -->
<nav class="navbar navbar-expand-lg"> <!-- Navigation -->
<div class="container-fluid">
  <div class="row">
    <div class="col-12 col-md-3 col-lg-2"> <!-- Sidebar (desktop) -->
    <div class="col-12 col-md-9 col-lg-10"> <!-- Content -->
  </div>
</div>

<!-- Cards untuk Dashboard -->
<div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4">
  <div class="col"> <!-- Stat Card -->
</div>

<!-- Table Responsive -->
<div class="table-responsive">
  <table class="table table-striped">
</div>

<!-- Modal untuk Form Input -->
<div class="modal fade" id="modalInput">
  <div class="modal-dialog modal-dialog-centered">

<!-- Offcanvas untuk Mobile Menu -->
<div class="offcanvas offcanvas-start" id="mobileMenu">
```

---

## 5. FITUR BUNGA DINAMIS

### 5.1 Parameter Bunga Dinamis
Bunga dapat diatur berdasarkan:

#### A. Jenis Pinjaman
| Jenis | Tenor | Bunga Dasar |
|-------|-------|-------------|
| Harian | 1 hari | 0.5-1% per hari |
| Mingguan | 7 hari | 2-3% per minggu |
| Bulanan | 1-6 bulan | 2-3% per bulan |
| Tahunan | 12-24 bulan | 24-36% per tahun |

#### B. Profil Risiko Nasabah
| Tingkat | Kriteria | Adjustment |
|---------|----------|------------|
| A (Excellent) | Lancar > 12 bulan, tidak pernah telat | Bunga -0.5% |
| B (Good) | Lancar 6-12 bulan, telat < 3x | Bunga dasar |
| C (Fair) | Pernah telat > 3x, kurang dari 6 bulan | Bunga +0.5% |
| D (High Risk) | Pernah macet, baru bergabung | Bunga +1-2% |

#### C. Agunan/Jaminan
| Jenis Jaminan | Adjustment |
|---------------|------------|
| Tanpa agunan (pinjaman kecil) | Bunga +1% |
| BPKB kendaraan | Bunga dasar |
| SHM/SHGB | Bunga -0.5% |
| Jaminan Tabungan | Bunga -0.5% |

### 5.2 Formula Perhitungan

#### Flat Rate (bunga tetap dari pokok awal)
```
Bunga per periode = Pokok × Suku Bunga × Tenor
Angsuran per periode = (Pokok / Tenor) + Bunga per periode
```

#### Efektif Rate (bunga menurun dari sisa pokok)
```
Bunga per periode = Sisa Pokok × Suku Bunga
Angsuran per periode = (Pokok / Tenor) + Bunga per periode
Pokok berkurang setiap periode
```

#### Anuitas (angsuran tetap)
```
Angsuran = Pokok × [i(1+i)^n / ((1+i)^n - 1)]
Dimana:
i = suku bunga per periode
n = jumlah periode
```

### 5.3 Implementasi di PHP
```php
class BungaCalculator {
    // Hitung bunga berdasarkan profil
    public function hitungBungaDinamis($pokok, $tenor, $jenis, $profilRisiko, $jaminan) {
        $bungaDasar = $this->getBungaDasar($jenis, $tenor);
        $adjustment = $this->getAdjustment($profilRisiko, $jaminan);
        $sukuBunga = $bungaDasar + $adjustment;
        
        return $this->hitungAngsuran($pokok, $tenor, $sukuBunga, $jenis);
    }
}

// Query dengan filter cabang (untuk isolasi data)
class DataAccess {
    protected $userRole;
    protected $cabangId;
    
    // Constructor menerima data user dari session
    public function __construct($userRole, $cabangId) {
        $this->userRole = $userRole;
        $this->cabangId = $cabangId;
    }
    
    // Generate WHERE clause untuk filter cabang
    public function getCabangFilter($tableAlias = '') {
        $prefix = $tableAlias ? $tableAlias . '.' : '';
        
        // Super Admin bisa lihat semua cabang (atau filter spesifik)
        if ($this->userRole == 'superadmin') {
            return ""; // Tidak ada filter, atau bisa ditambahkan filter dari parameter
        }
        
        // Manager & Petugas hanya lihat cabang mereka
        return " AND {$prefix}cabang_id = " . (int)$this->cabangId;
    }
    
    // Contoh: Get nasabah dengan filter cabang
    public function getNasabah($filter = '') {
        $sql = "SELECT * FROM nasabah WHERE 1=1";
        $sql .= $this->getCabangFilter(); // Auto-filter berdasarkan role
        $sql .= $filter; // Additional filter
        return $sql;
    }
}

// Penggunaan
$user = new DataAccess($_SESSION['role'], $_SESSION['cabang_id']);
$sql = $user->getNasabah(" AND status = 'aktif'");
// Result: "SELECT * FROM nasabah WHERE 1=1 AND cabang_id = 1 AND status = 'aktif'"
```

---

## 6. WORKFLOW PROSES BISNIS

### 6.1 Alur Pengajuan Pinjaman (Digital)
```
1. Nasabah/Petugas input pengajuan
   ↓
2. Sistem analisa kredit (scoring otomatis)
   ↓
3. Appraisal/penilaian (petugas verifikasi lapangan)
   ↓
4. Approval oleh Admin (disetujui/ditolak)
   ↓
5. Generate surat perjanjian
   ↓
6. Pencairan dana
   ↓
7. Generate jadwal angsuran otomatis
```

### 6.2 Alur Pembayaran Angsuran (Hybrid)
```
A. Via Petugas Lapangan (Offline/Online):
   Petugas kunjungi nasabah → Input pembayaran di mobile
   → Sinkronisasi saat ada internet → Update status angsuran

B. Via Nasabah (Online):
   Nasabah transfer → Upload bukti → Verifikasi admin
   → Update status angsuran
```

### 6.3 Alur Collection/Kunjungan
```
1. Sistem generate daftar kunjungan harian per petugas
   (berdasarkan jadwal angsuran yang jatuh tempo)
   ↓
2. Petugas terima notifikasi di aplikasi mobile
   ↓
3. Petugas kunjungi nasabah → Update status kunjungan
   (sudah bayar/janji bayar/nolak/tidak ketemu)
   ↓
4. Input hasil kunjungan dan pembayaran (jika ada)
   ↓
5. Sync data ke server
```

---

## 7. KEAMANAN & BEST PRACTICES

### 7.1 Keamanan Data
- **Enkripsi password**: bcrypt/Argon2
- **SQL Injection prevention**: Prepared statements/PDO
- **XSS protection**: Output escaping, CSP headers
- **CSRF protection**: Token validation
- **Session management**: Secure cookies, timeout
- **File upload**: Validasi tipe dan ukuran file
- **HTTPS**: SSL/TLS untuk semua komunikasi

### 7.2 Validasi Data
```php
// Contoh validasi input
$rules = [
    'nik' => 'required|numeric|digits:16',
    'nama' => 'required|string|max:100',
    'telp' => 'required|numeric|digits_between:10,13',
    'plafon' => 'required|numeric|min:100000',
    'tenor' => 'required|integer|min:1|max:24'
];
```

### 7.3 Backup & Recovery
- Backup database otomatis harian/mingguan
- Export data ke Excel/CSV secara berkala
- Sistem log untuk audit trail

---

## 8. API ENDPOINT (REST API)

### 8.1 Autentikasi
```
POST /api/auth/login
POST /api/auth/logout
POST /api/auth/refresh-token
```

### 8.2 Nasabah
```
GET    /api/nasabah              # List semua nasabah
GET    /api/nasabah/{id}         # Detail nasabah
POST   /api/nasabah              # Tambah nasabah
PUT    /api/nasabah/{id}         # Update nasabah
DELETE /api/nasabah/{id}         # Hapus nasabah (soft delete)
GET    /api/nasabah/{id}/pinjaman # List pinjaman nasabah
GET    /api/nasabah/search?q=...  # Cari nasabah
```

### 8.3 Pinjaman
```
GET    /api/pinjaman
GET    /api/pinjaman/{id}
POST   /api/pinjaman                    # Ajukan pinjaman
PUT    /api/pinjaman/{id}/approve       # Approval pinjaman
PUT    /api/pinjaman/{id}/reject        # Tolak pinjaman
GET    /api/pinjaman/{id}/angsuran      # Jadwal angsuran
POST   /api/pinjaman/{id}/calculate     # Simulasi perhitungan
```

### 8.4 Angsuran & Pembayaran
```
GET    /api/angsuran?jatuh_tempo={date}  # List angsuran jatuh tempo
POST   /api/angsuran/{id}/bayar          # Bayar angsuran
GET    /api/angsuran/tunggakan           # List tunggakan
GET    /api/pembayaran                   # Riwayat pembayaran
```

### 8.5 Petugas & Kunjungan
```
GET    /api/petugas/{id}/jadwal          # Jadwal kunjungan hari ini
POST   /api/kunjungan                    # Input hasil kunjungan
GET    /api/kunjungan/statistik          # Statistik kunjungan petugas
```

### 8.6 Laporan
```
GET    /api/laporan/neraca?periode={month}
GET    /api/laporan/laba-rugi?periode={month}
GET    /api/laporan/portofolio
GET    /api/laporan/kolektibilitas
GET    /api/dashboard/stats              # Statistik dashboard

### 8.7 Multi-Cabang (khusus Super Admin)
```
GET    /api/cabang                       # List semua cabang
POST   /api/cabang                       # Tambah cabang baru
PUT    /api/cabang/{id}                  # Update data cabang
GET    /api/cabang/{id}/stats            # Statistik per cabang
GET    /api/cabang/konsolidasi           # Laporan konsolidasi semua cabang
PUT    /api/cabang/{id}/transfer-nasabah # Transfer nasabah antar cabang
POST   /api/cabang/{id}/setting          # Setting khusus cabang
```

### 8.8 Filter Cabang (untuk semua endpoint)
```
GET    /api/nasabah?cabang_id=1          # Filter nasabah cabang 1
GET    /api/pinjaman?cabang_id=1         # Filter pinjaman cabang 1
GET    /api/laporan/portofolio?cabang_id=1 # Laporan portofolio cabang 1
```

**Note**: Jika user login adalah Manager/Petugas, `cabang_id` otomatis ter-filter berdasarkan cabang user tersebut. Super Admin bisa lihat semua cabang atau filter spesifik.

---

## 9. STRUKTUR FOLDER PROYEK

```
/kewer-app/
├── /assets/
│   ├── /css/           # Custom CSS
│   ├── /js/            # Custom JS, jQuery plugins
│   ├── /images/        # Logo, icons, uploads
│   └── /fonts/         # Font files
├── /config/
│   ├── database.php    # Konfigurasi DB
│   └── config.php      # Konfigurasi umum
├── /includes/
│   ├── header.php      # Header template
│   ├── footer.php      # Footer template
│   ├── sidebar.php     # Sidebar navigation
│   ├── functions.php   # Fungsi umum
│   └── auth.php        # Autentikasi
├── /api/               # API endpoints
│   ├── index.php       # Router API
│   ├── nasabah.php
│   ├── pinjaman.php
│   ├── angsuran.php
│   └── ...
├── /pages/             # Halaman aplikasi
│   ├── dashboard.php   # Dashboard dengan filter cabang
│   ├── nasabah/        # Semua nasabah (dengan filter cabang)
│   │   ├── index.php   # List nasabah
│   │   ├── tambah.php
│   │   ├── detail.php
│   │   ├── edit.php
│   │   └── transfer.php # Transfer antar cabang
│   ├── pinjaman/
│   ├── angsuran/
│   ├── petugas/
│   ├── cabang/         # Master data & laporan cabang
│   │   ├── index.php   # List cabang
│   │   ├── tambah.php
│   │   ├── detail.php  # Statistik per cabang
│   │   └── setting.php # Setting per cabang
│   ├── laporan/
│   │   ├── index.php   # Pilihan laporan per cabang
│   │   ├── konsolidasi.php # Laporan merge semua cabang
│   │   └── perbandingan.php # Perbandingan cabang
│   └── pengaturan/
├── /mobile/            # Versi mobile optimized
│   └── petugas/
│       ├── login.php
│       ├── dashboard.php
│       ├── kunjungan.php
│       └── pembayaran.php
├── /database/          # SQL dump & migrations
│   └── schema.sql
├── /docs/              # Dokumentasi
├── index.php           # Entry point
├── .htaccess           # Rewrite rules
└── README.md
```

---

## 10. ROADMAP PENGEMBANGAN

### Phase 1: MVP (Minimum Viable Product)
- [ ] Setup database & struktur folder (dengan support multi-cabang)
- [ ] Sistem autentikasi dengan role-based access (Super Admin, Manager Cabang, Petugas)
- [ ] Master Data Cabang (CRUD cabang, setting per cabang)
- [ ] CRUD Master Data (Anggota, Petugas) dengan isolasi cabang
- [ ] Modul Pinjaman (pengajuan, approval, perhitungan bunga flat)
- [ ] Modul Angsuran (generate jadwal, input pembayaran)
- [ ] Dashboard basic dengan filter cabang
- [ ] Versi mobile untuk petugas (input pembayaran, dengan cabang_id auto-assign)

### Phase 2: Enhancement
- [ ] Perhitungan bunga dinamis (efektif, anuitas) per cabang
- [ ] Modul Simpanan dengan kas per cabang
- [ ] Laporan lengkap: per cabang + konsolidasi pusat (neraca, laba rugi, portofolio)
- [ ] API REST lengkap dengan filter cabang
- [ ] Transfer anggota antar cabang
- [ ] Notifikasi (email, WhatsApp gateway)
- [ ] Maps/tracking kunjungan per cabang

### Phase 3: Advanced Features
- [ ] Scoring kredit otomatis (machine learning) per wilayah/cabang
- [ ] Mobile app native (Android/iOS)
- [ ] Integrasi payment gateway (virtual account, e-wallet)
- [ ] Business intelligence dashboard (perbandingan cabang)
- [ ] Franchise/Sub-cabang management
- [ ] Integrasi akuntansi (laporan keuangan konsolidasi)

---

## 11. REFERENSI & SUMBER

### Sumber Data Koperasi Pasar:
1. **KSP Makmur Mandiri** - Produk Pinjaman Harian Wirausaha
2. **LPDB (Lembaga Pengelola Dana Bergulir)** - Data koperasi di Indonesia
3. **BBC Indonesia** - Laporan kredit mikro & koperasi
4. **Invelli** - Jenis-jenis usaha koperasi

### Teknologi:
- Bootstrap 5: https://getbootstrap.com/
- jQuery: https://jquery.com/
- PHP: https://www.php.net/
- MySQL: https://www.mysql.com/

### Regulasi:
- UU No. 25 Tahun 1992 tentang Perkoperasian
- PP No. 17 Tahun 1994 tentang Pelaksanaan UU Perkoperasian

---

## 12. ANALISIS MASALAH & SOLUSI DIGITAL

> **Berdasarkan riset dari berbagai sumber**: Kemenkop UKM, AKSES Indonesia, Media Indonesia, Telusur, dan berbagai referensi manajemen koperasi.

---

### 12.1 MASALAH LIKUIDITAS & ARUS KAS

#### A. Definisi Masalah (Konteks Bisnis Pinjaman Perorangan)
Karena usaha ini **milik pribadi** (bukan koperasi berbadan hukum), tidak ada risiko "gagal bayar ke anggota" atau "rush money". Risiko likuiditas yang relevan adalah:

- **Modal mengendap**: Terlalu banyak pinjaman yang belum kembali, modal baru tidak ada untuk dicairkan ke nasabah baru
- **Default nasabah**: Nasabah tidak bayar → kas tidak kembali → tidak bisa cairkan pinjaman baru
- **Ekspansi tidak terencana**: Buka cabang baru tapi kas tidak cukup
- **Timing mismatch**: Nasabah banyak yang mau pinjam, tapi modal sedang keluar semua

#### B. Penyebab Utama Likuiditas Bermasalah (Spesifik untuk Anda)
| Penyebab | Deskripsi | Risiko Level |
|----------|-----------|--------------|
| **Default Nasabah** | Pedagang gagal bayar, usaha sepi, pindah lokasi | Tinggi |
| **Petugas Curang** | Angsuran dikumpulkan tapi tidak disetor penuh ke kas | Tinggi |
| **Over-ekspansi** | Terlalu banyak pinjaman dicairkan melebihi kemampuan modal | Sedang |
| **Timing Mismatch** | Banyak yang mau pinjam saat modal sedang penuh disalurkan | Rendah |
| **Faktor Eksternal** | Pasar tutup, banjir, pandemi → pedagang tidak bisa jualan | Sedang |

#### C. Solusi Digital
```
1. REAL-TIME LIKUIDITAS MONITORING
   ├── Dashboard kas harian per cabang
   ├── Alert otomatis jika kas mendekoti batas minimum
   ├── Prediksi likuiditas 7/14/30 hari ke depan
   └── Auto-stop pinjaman baru jika likuiditas kritis

2. EARLY WARNING SYSTEM (NPL)
   ├── Monitoring kolektibilitas otomatis (Lancar→Macet)
   ├── Alert nasabah bermasalah (telat > 3x, > 7 hari)
   ├── Heat map tunggakan per wilayah/petugas
   └── Prediksi risiko default berdasarkan pola bayar

3. TRANSPARANSI KE ANGGOTA
   ├── Laporan keuangan real-time (bukan manual)
   ├── Status pinjaman & simpanan nasabah bisa dicek sendiri
   ├── Rapat anggota virtual dengan dokumen terstruktur
   └── Audit trail semua transaksi (tidak bisa dihapus/edit sembarangan)
```

---

### 12.2 MASALAH HUMAN ERROR & PENCATATAN MANUAL

#### A. Definisi Masalah
Berdasarkan penelitian dan praktik:
- **Pencatatan manual berisiko duplikasi, terhapus, teredit**
- **Kesalahan pemberian pinjaman** (data anggota tidak akurat)
- **Saldo pinjaman sulit diketahui** secara real-time
- **Laporan tidak bisa dihasilkan otomatis**

**Sumber**: ResearchGate (PERANCANGAN SISTEM INFORMASI PINJAMAN), IDstar

#### B. Jenis-Jenis Human Error di Koperasi
| Jenis Kesalahan | Contoh Kejadian | Dampak |
|-----------------|-----------------|--------|
| **Input Error** | Salah ketik nominal angsuran (1jt jadi 100rb) | Kerugian finansial |
| **Omission** | Lupa mencatat pembayaran angsuran | Data tidak valid |
| **Duplikasi** | Nasabah tercatat 2x dengan KTP sama | Data kacau |
| **Kehilangan Data** | Buku kas hilang/rusak | Riwayat transaksi hilang |
| **Kesalahan Perhitungan** | Hitung bunga manual salah | Penarikan keuntungan salah |
| **Keterlambatan Input** | Pembayaran tercatat beberapa hari kemudian | Laporan tidak akurat |

#### C. Solusi Digital
```
1. AUTOMATED DATA VALIDATION
   ├── Validasi KTP 16 digit (format & unique check)
   ├── Konfirmasi sebelum submit (preview data)
   ├── Duplicate detection (KTP, telepon, nama mirip)
   └── Auto-save draft (jika koneksi terputus)

2. AUTOMATED CALCULATION
   ├── Perhitungan bunga otomatis (tidak manual)
   ├── Generate jadwal angsuran otomatis
   ├── Sisa pokok & bunga real-time
   └── Jurnal akuntansi otomatis (double entry)

3. AUDIT TRAIL & IMMUTABLE LOG
   ├── Setiap transaksi tercatat: siapa, kapan, apa yang diubah
   ├── Tidak bisa hapus data (soft delete saja)
   ├── History perubahan bisa diliak kapan saja
   └── Backup otomatis harian ke cloud

4. DIGITAL COLLECTION SYSTEM
   ├── Petugas scan/cek angsuran via mobile
   ├── QR Code atau ID unik untuk verifikasi nasabah
   ├── GPS tracking kunjungan (optional)
   └── Real-time sync ke server saat online
```

---

### 12.3 MASALAH FRAUD & KECURANGAN

#### A. Definisi Masalah (Spesifik Bisnis Pinjaman Perorangan)
Karena bisnis ini **tidak berbadan hukum koperasi**, risiko fraud dari "pengurus" tidak ada — Anda adalah pemilik tunggal. Namun risiko fraud yang relevan justru datang dari:
- **Petugas lapangan** yang mengumpulkan angsuran
- **Nasabah** yang memberikan identitas/jaminan palsu
- **Orang dalam** (keluarga/kenalan petugas) yang kongkalikong

#### B. Jenis Fraud (Relevan untuk Bisnis Pinjaman Perorangan)
| Jenis | Pelaku | Modus | Deteksi Digital |
|-------|--------|-------|-----------------|
| **Pinjaman Fiktif** | Petugas | Input nasabah palsu, cairkan pinjaman sendiri | Foto KTP + selfie wajib, approval pemilik |
| **Skimming Angsuran** | Petugas | Terima Rp 100rb, setor Rp 80rb ke kas | Digital receipt ke nasabah, rekonsiliasi harian |
| **Nasabah Fiktif** | Petugas + Kongsi | Daftar nasabah fiktif untuk ambil pinjaman | Validasi KTP, verifikasi telepon via OTP |
| **Identitas Palsu** | Nasabah | KTP pinjam, foto bukan sendiri | Foto selfie holding KTP saat daftar |
| **Double-claim** | Petugas | Klaim sudah kunjungi nasabah padahal tidak | GPS log kunjungan, foto check-in lokasi |
| **Jaminan Palsu** | Nasabah | BPKB/SHM sudah dijaminkan ke tempat lain | Foto dokumen, cek fisik jaminan, watermark foto |

#### C. Solusi Digital
```
1. MULTI-LEVEL APPROVAL
   ├── Pinjaman > X juta: butuh approval Manager
   ├── Transfer > Y juta: butuh approval Super Admin
   ├── Perubahan data kritis: butuh verifikasi 2 level
   └── Tidak ada transaksi tanpa approval (kecuali angsuran rutin)

2. BIOMETRIC & IDENTITY VERIFICATION
   ├── Upload foto KTP + selfie (face matching)
   ├── Verifikasi telepon via OTP
   ├── Digital signature untuk perjanjian pinjaman
   └── Geotagging saat pengajuan/pencairan

3. REAL-TIME RECONCILIATION
   ├── Rekonsiliasi kas harian otomatis
   ├── Alert jika ada selisih kas vs transaksi tercatat
   ├── Setoran bank otomatis match dengan pembayaran
   └── Laporan anomali (transaksi di luar jam kerja, nominal aneh)

4. WHISTLEBLOWER SYSTEM
   ├── Laporkan kecurangan via aplikasi (anonim opsional)
   ├── Tracking kasus laporan
   └── Eskalasi ke Super Admin
```

---

### 12.4 MASALAH KOLEKSI (COLLECTION) & TUNGGAKAN

#### A. Definisi Masalah
Masalah yang sering terjadi di koperasi pasar dengan angsuran harian/mingguan:

- **Nasabah sulit dihubungi** (pindah lokasi, ganti telepon)
- **Petugas tidak konsisten** kunjungi jadwal yang ditentukan
- **Tidak ada sistem reminder** untuk angsuran jatuh tempo
- **Catatan kunjungan tidak terstruktur** (hanya ingatan petugas)
- **Tidak ada escalations procedure** untuk tunggakan lama

#### B. Solusi Digital
```
1. SMART COLLECTION SYSTEM
   ├── Auto-generate daftar kunjungan harian berdasarkan:
   │   - Jatuh tempo angsuran
   │   - History pembayaran (prioritaskan yang sering telat)
   │   - Lokasi (clustering kunjungan per wilayah)
   └── Optimasi rute kunjungan (shortest path algorithm)

2. OMNICHANNEL REMINDER
   ├── WhatsApp otomatis H-1 jatuh tempo
   ├── SMS reminder pagi hari jatuh tempo
   ├── Notifikasi in-app untuk nasabah
   └── Voice call reminder (auto-dial untuk tunggakan > 7 hari)

3. COLLECTION PERFORMANCE TRACKING
   ├── Target vs realisasi kunjungan per petugas
   ├── Collection rate (%) per petugas per hari
   ├── Average Time to Collect (berapa hari setelah jatuh tempo)
   └── Nasabah PTP (Promise to Pay) tracking

4. ESCALATION MATRIX
   ├── Tunggakan 1-7 hari: Petugas lapangan
   ├── Tunggakan 8-30 hari: Manager Cabang + petugas
   ├── Tunggakan > 30 hari: Super Admin + tim khusus
   └── Tunggakan > 90 hari: Restrukturisasi atau penyelesaian hukum

5. COLLECTION STRATEGY PER PROFIL
   ├── Nasabah "lupa": Reminder intensif
   ├── Nasabah "sulit bayar": Restrukturisasi angsuran
   ├── Nasabah "enggan bayar": Escalasi ke manager
   └── Nasabah "tidak bisa ditemukan": Update data via relasi
```

---

### 12.5 MASALAH TRANSPARANSI & KEpercayaan Anggota

#### A. Definisi Masalah
Berdasarkan kasus KSP gagal bayar:
- Anggota tidak tahu kondisi keuangan koperasi sebenarnya
- Tidak ada akses real-time ke saldo pinjaman/simpanan
- Informasi hanya dari pengurus (one-way)
- Rasa "tidak memiliki" koperasi (anggota = nasabah, bukan pemilik)

#### B. Solusi Digital (Transparency Portal)
```
1. NASABAH SELF-SERVICE PORTAL
   ├── Login dengan KTP + OTP
   ├── Lihat sisa pinjaman & jadwal angsuran real-time
   ├── Riwayat pembayaran (semua transaksi tercatat)
   ├── Download kwitansi/bukti pembayaran digital
   ├── Pengajuan pinjaman baru online
   └── Komplain & tracking status komplain

2. PUBLIC FINANCIAL DASHBOARD (Super Admin)
   ├── Total aset koperasi (konsolidasi semua cabang)
   ├── Total outstanding pinjaman
   ├── NPL ratio (Non Performing Loan)
   ├── Suku bunga rata-rata yang berlaku
   └── Laporan tahunan (download PDF)

3. TWO-WAY COMMUNICATION
   ├── Pengumuman dari pusat ke semua cabang/nasabah
   ├── Rapat anggota virtual (live streaming + voting)
   ├── Survei kepuasan anggota
   └── FAQ & Knowledge Base
```

---

### 12.6 MASALAH MULTI-CABANG (Spesifik untuk Anda)

#### A. Risiko yang Muncul dengan Banyak Cabang
| Risiko | Deskripsi | Solusi Digital |
|--------|-----------|----------------|
| **Data Tidak Sinkron** | Cabang A update data, Cabang B lihat data lama | Real-time sync via API, offline mode dengan queue |
| **Kas Cabang Tidak Terkontrol** | Manager cabang gunakan semaunya | Approval limit, daily reconciliation, alert anomali |
| **Petugas Cabang "Silos"** | Tidak ada koordinasi antar cabang | Shared dashboard, transfer anggota antar cabang |
| **Laporan Terlambat** | Laporan cabang manual, terlambat ke pusat | Auto-generate laporan, push ke pusat real-time |
| **Standar Tidak Sama** | Cabang A beda prosedur dengan Cabang B | SOP digital terstandarisasi, approval workflow sama |

#### B. Solusi Digital Multi-Cabang
```
1. CENTRALIZED DATABASE
   ├── Satu database untuk semua cabang
   ├── Isolasi data dengan cabang_id
   ├── Super Admin bisa lihat konsolidasi
   └── Manager hanya lihat cabangnya

2. INTER-BRANCH FEATURES
   ├── Transfer anggota antar cabang (history ikut)
   ├── Pinjaman anggota Cabang A, bayar di Cabang B
   ├── Konsolidasi laporan otomatis (neraca, laba rugi)
   └── Perbandingan performa cabang (benchmarking)

3. REMOTE MONITORING
   ├── CCTV integration (opsional untuk cabang)
   ├── Real-time transaction monitoring
   ├── Geo-fencing petugas lapangan
   └── Daily activity report otomatis ke pusat
```

---

### 12.7 RINGKASAN: MATRIK MASALAH vs SOLUSI DIGITAL

| No | Masalah | Solusi Utama | Fitur Aplikasi | Prioritas |
|----|---------|--------------|----------------|-----------|
| 1 | Likuiditas kritis | Real-time monitoring | Dashboard kas, Alert system | Tinggi |
| 2 | Human error input | Validation & automation | Auto-calculation, duplicate check | Tinggi |
| 3 | Fraud pinjaman fiktif | Identity verification | KTP+Selfie, biometric | Tinggi |
| 4 | Tunggakan sulit dikelola | Smart collection | Auto-reminder, escalation matrix | Tinggi |
| 5 | Data tidak transparan | Self-service portal | Nasabah login, real-time balance | Sedang |
| 6 | Multi-cabang chaos | Centralized DB + sync | cabang_id isolasi, konsolidasi | Tinggi |
| 7 | Petugas tidak terkontrol | GPS + performance tracking | Kunjungan tracking, collection rate | Sedang |
| 8 | Laporan manual lambat | Auto-report generation | PDF/Excel auto-export | Sedang |
| 9 | Skimming uang angsuran | Receipt otomatis | Digital kwitansi, rekonsiliasi | Tinggi |
| 10 | Komunikasi satu arah | Two-way portal | Announcement, komplain system | Rendah |

---

### 12.8 Sumber Referensi Analisis
1. **Media Indonesia** - "Kesulitan Likuiditas Sebabkan Koperasi Gagal Bayar" (30/9/2020)
2. **Telusur** - "Koperasi Gagal Bayar, dan Gagalnya Kementerian Koperasi" (Suroto, 2022)
3. **Validnews** - "AKSES Indonesia Beberkan Penyebab KSP Gagal Bayar" (21/1/2022)
4. **IDstar** - "Cara Mengatasi Human Error dalam Bisnis" (12/12/2023)
5. **ResearchGate** - "Perancangan Sistem Informasi Pinjaman dan Angsuran di Koperasi"
6. **Kemenkop UKM** - Data statistik permasalahan koperasi (46% masalah permodalan)
7. **Antaranews** - "Satgas Ungkap Penyebab Koperasi Simpan Pinjam Bermasalah"
8. **Hukumonline** - "Advokat Beberkan 3 Persoalan Koperasi Simpan Pinjam Bermasalah"
9. **Kompas** - "Koperasi Bermasalah" (Opini, 2023)
10. **CNBC Indonesia** - "8 Kasus Koperasi Bermasalah yang Gagal Bayar"

---

## 13. CATATAN PENTING

### Tips Implementasi:
1. **Mulai sederhana**: Fokus pada fitur core (pinjaman & angsuran) dulu
2. **Test dengan data nyata**: Gunakan contoh data dari operasional manual Anda
3. **Training petugas**: Siapkan SOP dan pelatihan untuk petugas lapangan
4. **Backup rutin**: Jangan lupa backup database secara berkala
5. **Keamanan**: Prioritaskan keamanan data nasabah (data pribadi & finansial)
6. **Iterasi**: Kembangkan fitur secara bertahap berdasarkan feedback

### Risiko yang Perlu Diwaspadai:
- **Koneksi internet di lapangan**: Siapkan mode offline/sync
- **Human error input**: Validasi ketat dan konfirmasi sebelum submit
- **Fraud**: Sistem audit trail dan verifikasi pembayaran
- **Data loss**: Backup otomatis dan disaster recovery plan

---

## 14. ANALISIS FITUR LENGKAP DENGAN JUSTIFIKASI

> **Tujuan**: Menjelaskan setiap fitur yang harus ada di aplikasi dan alasan mengapa fitur tersebut kritis untuk kesuksesan digitalisasi koperasi pasar Anda.

---

### 14.1 FITUR AUTENTIKASI & MANAJEMEN USER

#### A. Role-Based Access Control (RBAC)
**Fitur Detail**:
- 4 Level Role: Super Admin, Manager Cabang, Petugas Lapangan, Nasabah
- Permission matrix (apa yang boleh dilakukan setiap role)
- Session management & timeout
- Login dengan username/password + 2FA opsional

**Alasan/JUSTIFIKASI**:
```
1. ISOLASI DATA & KEAMANAN
   - Tanpa RBAC: Petugas Cabang A bisa lihat data Cabang B (bocor)
   - Dengan RBAC: Setiap user hanya akses data sesuai wilayahnya
   
2. PREVENT FRAUD
   - Petugas tidak bisa approve pinjaman sendiri (harus manager)
   - Manager tidak bisa hapus transaksi (hanya Super Admin bisa)
   
3. ACCOUNTABILITY
   - Setiap transaksi tercatat "siapa" yang melakukan
   - Tidak ada transaksi anonim/tanpa identitas petugas
   
4. MULTI-CABANG MANAGEMENT
   - Super Admin lihat konsolidasi, Manager lihat cabangnya saja
   - Tidak ada "campur tangan" antar cabang tanpa izin
```

#### B. Profil User & Audit Trail
**Fitur Detail**:
- Profil lengkap dengan foto, data pribadi, area kerja
- Log aktivitas (login, transaksi, perubahan data)
- Ganti password & reset password
- Status aktif/nonaktif user

**Alasan/JUSTIFIKASI**:
```
1. AUDIT TRAIL UNTUK FRAUD DETECTION
   - Jika ada uang hilang, bisa dilacak siapa terakhir yang akses
   - Contoh kasus: Petugas mencatat angsuran 500rb tapi setor 400rb
     → Audit trail tunjukkan discrepancy
     
2. HUMAN ERROR TRACKING
   - Jika ada kesalahan input, bisa dilihat siapa & kapan
   - Bisa digunakan untuk training ulang petugas yang sering salah
   
3. COMPLIANCE & LEGAL
   - Jika ada sengketa dengan nasabah, ada bukti siapa yang input data
   - Memenuhi prinsip "transparansi" koperasi
```

---

### 14.2 FITUR MASTER DATA (ANGGOTA/PETUGAS/CABANG)

#### A. Manajemen Anggota (Nasabah) - CRUD Lengkap
**Fitur Detail**:
- Input data anggota: KTP, KK, foto, alamat, telepon, pekerjaan
- Upload dokumen (scan KTP, KK, surat perjanjian)
- Status anggota: aktif, nonaktif, blacklisted
- Pencarian anggota (by KTP, nama, telepon)
- Riwayat pinjaman & simpanan per anggota
- Profil risiko (scoring internal)

**Alasan/JUSTIFIKASI**:
```
1. CENTRALIZED DATABASE (vs Buku Manual)
   - Tanpa aplikasi: Data anggota di buku-buku terpisah, risiko hilang/rusak
   - Dengan aplikasi: Data tersimpan permanen, backup otomatis
   
2. DUPLICATE PREVENTION
   - Validasi KTP unik → Mencegah "ghost member" (satu orang daftar 2x)
   - Alert jika ada KTP/Nama/Telepon yang sudah terdaftar
   
3. RISK PROFILING
   - Sistem simpan history pinjaman (lancar/telat/macet)
   - Untuk bunga dinamis: nasabah dengan history telat bisa dikenakan bunga lebih tinggi
   
4. LEGAL COMPLIANCE
   - Foto KTP & tanda tangan digital sebagai bukti legal
   - Jika ada wanprestasi, dokumen lengkap untuk proses hukum
   
5. MARKETING & EXPANSION
   - Data jenis usaha anggota bisa dianalisa → Target pasar baru
   - Contoh: Banyak anggota pedagang sayur di Pasar A → Buka cabang di Pasar B dekat situ
```

#### B. Manajemen Petugas Lapangan
**Fitur Detail**:
- Data petugas: nama, telepon, area/wilayah tugas
- Target kunjungan harian/mingguan
- Assign petugas ke cabang tertentu
- Performance tracking (collection rate, jumlah kunjungan)
- GPS tracking (opsional untuk monitoring)

**Alasan/JUSTIFIKASI**:
```
1. PERFORMANCE MONITORING
   - Tanpa data: Tidak tahu petugas mana yang rajin/malas
   - Dengan data: Bisa lihat collection rate per petugas
   → Reward petugas performa tinggi, training untuk yang rendah
   
2. AREA OPTIMIZATION
   - Assign petugas ke area spesifik (tidak tumpang tindih)
   - Contoh: Petugas A → Pasar A, Petugas B → Pasar B
   → Efisiensi rute kunjungan
   
3. PREVENT FRAUD BY PETUGAS
   - Jika petugas catat angsuran tapi uang tidak masuk kas
   → Bisa dilacak siapa petugas yang input transaksi tersebut
   
4. WORKLOAD BALANCING
   - Distribusi nasabah secara merata ke petugas
   - Jangan sampai 1 petugas handle 100 nasabah, yang lain handle 20
```

#### C. Manajemen Cabang (Multi-Branch)
**Fitur Detail**:
- CRUD data cabang (nama, alamat, telepon, manager)
- Setting per cabang (bunga dasar bisa beda antar cabang)
- Isolasi data cabang (security)
- Laporan konsolidasi & per cabang
- Transfer anggota antar cabang

**Alasan/JUSTIFIKASI**:
```
1. OPERATIONAL AUTONOMY
   - Setiap cabang punya karakteristik pasar berbeda
   - Contoh: Cabang di pasar elite bisa bunga 2%, cabang di pasar tradisional bunga 3%
   → Fleksibilitas bisnis
   
2. CENTRALIZED CONTROL
   - Super Admin tetap bisa lihat performa semua cabang
   - Laporan konsolidasi otomatis (tidak perlu nunggu laporan manual dari tiap cabang)
   
3. SCALABILITY
   - Jika Anda buka cabang baru ke-5, ke-6, dst
   → Tinggal tambah data cabang di sistem, tidak perlu bikin aplikasi baru
   
4. RISK ISOLATION
   - Jika 1 cabang bermasalah (misal: fraud), data cabang lain tetap aman
   - Bisa "freeze" operasi 1 cabang tanpa ganggu cabang lain
```

---

### 14.3 FITUR PINJAMAN & PERHITUNGAN BUNGA

#### A. Pengajuan Pinjaman (Loan Origination)
**Fitur Detail**:
- Form pengajuan online/offline (petugas bisa input untuk nasabah)
- Simulasi perhitungan bunga sebelum apply
- Upload dokumen jaminan (foto BPKB, SHM, dll)
- Workflow approval (Petugas → Manager → Pencairan)
- Tracking status pengajuan (pending, approved, rejected, cair)

**Alasan/JUSTIFIKASI**:
```
1. TRANSPARENCY & TRUST
   - Nasabah bisa simulasi dulu sebelum apply → Tidak ada surprise
   - Contoh: "Pinjam 5jt, bunga 2%/bulan, angsuran harian Rp 18.000"
   → Nasabah paham kewajibannya sebelum tanda tangan
   
2. PROCESS STANDARDIZATION
   - Tanpa sistem: Setiap petugas beda cara hitung bunga
   - Dengan sistem: Sistem auto-calculate → Standar semua cabang
   
3. FRAUD PREVENTION
   - Approval workflow → Petugas tidak bisa "cairkan" pinjaman fiktif sendiri
   - Harus ada approval dari Manager
   
4. SPEED & EFFICIENCY
   - Tanpa sistem: Pengajuan manual 3-5 hari
   - Dengan sistem: Kalau data lengkap, bisa same-day approval
   → Keunggulan kompetitif (bisa kalahkan koperasi lain)
```

#### B. Perhitungan Bunga Dinamis (Dynamic Interest)
**Fitur Detail**:
- 3 Metode: Flat, Efektif, Anuitas
- Parameter bunga dinamis: jenis pinjaman, tenor, profil risiko, jaminan
- Setting bunga per cabang (bisa beda antar cabang)
- Auto-generate jadwal angsuran
- Preview perhitungan sebelum approve

**Alasan/JUSTIFIKASI**:
```
1. RISK-BASED PRICING
   - Nasabah dengan history telat bayar → Bunga lebih tinggi
   - Nasabah dengan agunan SHM → Bunga lebih rendah
   → Harga pinjaman sesuai risiko (fair & profitable)
   
2. COMPETITIVE ADVANTAGE
   - Koperasi tradisional: Bunga flat 3% untuk semua
   - Anda: Bunga 2% untuk nasabah baik, 4% untuk nasabah bermasalah
   → Bisa tarik nasabah berkualitas dengan bunga rendah
   
3. ACCURACY & AUTOMATION
   - Tanpa sistem: Hitung manual → Risiko salah (bunga 2% jadi 3%)
   - Dengan sistem: Auto-calculate → Akurat 100%
   → Tidak ada sengketa dengan nasabah soal perhitungan
   
4. FLEXIBILITY
   - Bisa ganti metode bunga tanpa ribet
   - Contoh: Dari Flat ke Efektif → Hanya ubah setting, tidak ubah prosedur manual
```

#### C. Monitoring Pinjaman & Kolektibilitas
**Fitur Detail**:
- Status pinjaman: Aktif, Lunas, Macet
- Kolektibilitas otomatis: Lancar, Kurang Lancar, Diragukan, Macet
- Aging analysis (umur tunggakan: 1-30, 31-60, 61-90, >90 hari)
- NPL (Non Performing Loan) ratio per cabang/konsolidasi
- Alert pinjaman akan jatuh tempo, sudah telat

**Alasan/JUSTIFIKASI**:
```
1. EARLY WARNING SYSTEM
   - Pinjaman masih Lancar tapi sudah 2x telat bayar → Alert "Watchlist"
   - Bisa tindak lanjut sebelum jadi Macet
   → Minimize kerugian
   
2. REGULATORY COMPLIANCE
   - OJK/Kemenkop mensyaratkan laporan kolektibilitas berkala
   - Sistem auto-generate → Tidak perlu hitung manual
   
3. PORTFOLIO MANAGEMENT
   - Dashboard: Total outstanding Rp 1M, NPL 5%
   - Jika NPL naik jadi 10% → Alert untuk ketatkan approval
   → Bisnis tetap sehat & profitable
   
4. PREDICTIVE ANALYTICS (Phase 3)
   - Data history bisa dipakai untuk ML (Machine Learning)
   - Prediksi: "Nasabah dengan pola X berpotensi macet 80%"
   → Prevent bad loan sebelum terjadi
```

---

### 14.4 FITUR ANGSURAN & KOLEKSI

#### A. Input Pembayaran Angsuran
**Fitur Detail**:
- Input via mobile (petugas di lapangan) atau desktop
- Auto-recognize angsuran (sistem tahu ini angsuran ke-5 dari pinjaman X)
- Multi-payment method: Cash, Transfer, E-Wallet
- Upload bukti transfer (foto/screenshot)
- Real-time update sisa pinjaman

**Alasan/JUSTIFIKASI**:
```
1. REAL-TIME ACCURACY
   - Nasabah bayar Rp 100.000 → Sistem langsung update sisa pinjaman
   - Tidak ada delay seperti pencatatan manual (bisa error jika lupa catat)
   
2. RECEIPT & PROOF
   - Digital kwitansi otomatis terkirim ke nasabah (WhatsApp/Email)
   - Bukti bayar tidak bisa dipalsukan/ditolak
   → Jika ada sengketa "saya sudah bayar", ada bukti digital
   
3. CASH MANAGEMENT
   - Petugas input "Terima cash Rp 500rb dari 5 nasabah"
   → Sistem generate "Expected Kas" vs "Actual Kas"
   → Jika selisih, alert kemungkinan skimming
   
4. CONVENIENCE
   - Nasabah bisa bayar di cabang mana saja (tidak harus cabang asal)
   - Contoh: Anggota Cabang A pindah ke kota lain, bayar di Cabang B
```

#### B. Smart Collection System
**Fitur Detail**:
- Auto-generate daftar kunjungan harian berdasarkan jatuh tempo
- Prioritaskan nasabah dengan tunggakan
- Optimasi rute kunjungan (shortest path)
- Status kunjungan: Sudah dikunjungi, Janji bayar, Tidak ketemu, Tolak bayar
- Catatan hasil kunjungan (voice-to-text opsional)

**Alasan/JUSTIFIKASI**:
```
1. EFFICIENCY & PRODUCTIVITY
   - Tanpa sistem: Petugas hafalan mana yang harus dikunjungi → Bisa lupa
   - Dengan sistem: Aplikasi kasih daftar & rute optimal
   → Petugas bisa kunjungi lebih banyak nasabah per hari
   
2. SYSTEMATIC COLLECTION
   - Prioritas: Yang telat 7 hari didahulukan vs yang telat 1 hari
   - Strategi beda: Yang "lupa" di-reminder, yang "enggan" di-escalasi
   → Collection rate meningkat
   
3. PERFORMANCE TRACKING
   - Petugas A: Target 20 kunjungan, realisasi 15, collection rate 75%
   - Petugas B: Target 20 kunjungan, realisasi 20, collection rate 90%
   → Bisa evaluasi & reward yang berkinerja baik
   
4. DOCUMENTATION
   - Hasil kunjungan tercatat: "Nasabah janji bayar besok", "Nasabah pindah rumah"
   → Jika petugas ganti shift, petugas lain bisa lanjutkan
```

#### C. Auto-Reminder & Escalation
**Fitur Detail**:
- WhatsApp/SMS otomatis H-1 jatuh tempo
- Reminder berulang untuk tunggakan
- Escalation matrix: Petugas → Manager → Super Admin → Legal
- Notifikasi in-app untuk petugas ("Ada 5 nasabah jatuh tempo hari ini")

**Alasan/JUSTIFIKASI**:
```
1. REDUCE DELINQUENCY
   - Banyak nasabah telat bukan karena tidak mampu, tapi "lupa"
   - Reminder H-1 → Mengurangi telat bayar 30-50%
   → Collection rate meningkat
   
2. SYSTEMATIC ESCALATION
   - Tunggakan 3 hari: Petugas handle
   - Tunggakan 30 hari: Manager turun tangan
   - Tunggakan 90 hari: Tim khusus/legal
   → Tidak ada tunggakan yang "ditinggal"
   
3. COST SAVING
   - Reminder otomatis via WhatsApp (biaya Rp 0)
   - Dibandingkan petugas harus kunjungi satu per satu (biaya transport + waktu)
   → Operational cost turun
```

---

### 14.5 FITUR SIMPANAN (TABUNGAN)

#### A. Manajemen Simpanan
**Fitur Detail**:
- Jenis simpanan: Pokok, Wajib, Sukarela, Sibuhar (harian)
- Setor & tarik simpanan
- Bunga simpanan (jika ada)
- History transaksi simpanan
- Saldo simpanan real-time

**Alasan/JUSTIFIKASI**:
```
1. LIQUIDITY MANAGEMENT
   - Dana simpanan adalah sumber pinjaman
   - Dashboard: Total simpanan Rp 2M, Total pinjaman Rp 1.5M
   → Rasio likuiditas 75% (masih sehat)
   → Jika rasio < 60%, stop pinjaman baru
   
2. MEMBER RETENTION
   - Anggota dengan simpanan besar lebih "loyal"
   - Fitur simpanan → Incentivize anggota untuk menabung
   → Dana koperasi lebih stabil
   
3. BACK-TO-BACK LOAN
   - Anggota dengan simpanan Rp 5jt bisa pinjam Rp 4jt (jaminan simpanan)
   - Risiko minimal (kalau default, potong simpanan)
   → Produk pinjaman dengan risiko rendah
```

---

### 14.6 FITUR LAPORAN & ANALISIS

#### A. Laporan Keuangan Otomatis
**Fitur Detail**:
- Neraca, Laba Rugi, Arus Kas
- Periode: Harian, Mingguan, Bulanan, Tahunan
- Per cabang & konsolidasi
- Export PDF/Excel
- Auto-schedule (email laporan setiap tanggal 1)

**Alasan/JUSTIFIKASI**:
```
1. TIME SAVING
   - Tanpa sistem: Buat laporan manual 2-3 hari
   - Dengan sistem: Click → Generate dalam 5 detik
   → Manager bisa fokus ke strategi, bukan administrasi
   
2. ACCURACY & COMPLIANCE
   - Jurnal akuntansi otomatis (double entry)
   - Tidak ada "kebocoran" transaksi yang tidak tercatat
   → Siap untuk audit kapan saja
   
3. DECISION MAKING
   - Laporan real-time → Bisa ambil keputusan cepat
   - Contoh: "NPL naik 5% bulan ini" → Langsung ketatkan approval pinjaman
```

#### B. Dashboard & Analytics
**Fitur Detail**:
- Dashboard real-time (total anggota, outstanding, NPL)
- Grafik trend (pertumbuhan pinjaman, kolektibilitas)
- Heat map (wilayah dengan tunggakan tinggi)
- Perbandingan performa cabang
- Top/Bottom performer (petugas, nasabah, produk)

**Alasan/JUSTIFIKASI**:
```
1. VISUALIZATION & INSIGHT
   - Data angka ribuan sulit diinterpretasi
   - Grafik & heat map → Cepat identifikasi masalah
   → "Warna merah di Pasar A, ada masalah tunggakan"
   
2. COMPETITIVE BENCHMARKING
   - Cabang A NPL 3%, Cabang B NPL 8%
   → Apa yang dilakukan Cabang A yang bisa ditiru Cabang B?
   → Best practice sharing
   
3. PREDICTIVE (Phase 3)
   - Machine learning untuk prediksi risiko
   - "Berdasarkan data 2 tahun, bulan Desember selalu ada peningkatan tunggakan 20%"
   → Bisa prepare resource sebelumnya
```

---

### 14.7 FITUR NASABAH SELF-SERVICE (TRANSPARENCY PORTAL)

#### A. Nasabah Mobile/Web Portal
**Fitur Detail**:
- Login dengan KTP + OTP
- Lihat sisa pinjaman & jadwal angsuran
- Riwayat pembayaran (semua transaksi)
- Download kwitansi digital
- Pengajuan pinjaman baru
- Komplain & tracking status

**Alasan/JUSTIFIKASI**:
```
1. TRANSPARENCY & TRUST
   - Nasabah bisa cek sendiri "saya sudah bayar 10x, sisa 5x lagi"
   - Tidak ada curiga "apakah petugas benar-benar catat pembayaran saya?"
   → Kepercayaan meningkat → Loyalitas meningkat
   
2. COST REDUCTION
   - Nasabah tanya "sisa pinjaman saya berapa?" → Bisa cek sendiri
   - Tidak perlu CS/pegawai jawab telepon/WA terus
   → Operational cost turun
   
3. DIGITAL ENGAGEMENT
   - Nasabah yang pakai aplikasi lebih "engaged"
   - Bisa kirim promo/pengumuman via notifikasi
   → Channel marketing baru
```

---

### 14.8 FITUR KEAMANAN & COMPLIANCE

#### A. Security Features
**Fitur Detail**:
- Enkripsi password (bcrypt)
- SQL injection prevention
- XSS & CSRF protection
- Session timeout
- IP whitelist (opsional untuk admin)
- Backup otomatis (daily/weekly)

**Alasan/JUSTIFIKASI**:
```
1. DATA PROTECTION
   - Data nasabah (KTP, alamat, pinjaman) adalah data sensitif
   - Jika bocor → Dampak reputasi & legal (UU PDP)
   → Security tidak bisa ditawar
   
2. BUSINESS CONTINUITY
   - Jika server crash/database corrupt
   - Backup otomatis → Restore dalam hitungan jam (bukan hari)
   → Bisnis tidak berhenti lama
```

#### B. Audit Trail & Immutable Log
**Fitur Detail**:
- Log semua transaksi (who, when, what)
- Soft delete (tidak ada data benar-benar terhapus)
- History perubahan (sebelum/sesudah)
- Export log untuk audit eksternal

**Alasan/JUSTIFIKASI**:
```
1. FRAUD DETECTION
   - Jika ada uang hilang → Bisa trace siapa yang akses sistem terakhir
   - Contoh kasus: Petugas ubah "angsuran lunas" jadi "belum lunas"
     → Log tunjukkan perubahan, bisa dikembalikan
   
2. COMPLIANCE & AUDIT
   - Jika diaudit Kemenkop/OJK
   → Ada bukti semua transaksi lengkap & tidak bisa dimanipulasi
   
3. DISPUTE RESOLUTION
   - Nasabah klaim "saya sudah bayar Rp 1jt"
   - Log tunjukkan hanya ada pembayaran Rp 500rb
   → Bukti objektif untuk menyelesaikan sengketa
```

---

### 14.9 FITUR INTEGRASI & EKSPANSI (PHASE 2-3)

#### A. WhatsApp/SMS Gateway Integration
**Fitur Detail**:
- Auto-reminder via WhatsApp Business API
- Notifikasi pembayaran diterima
- Blast info/pengumuman ke semua nasabah
- Two-way communication (nasabah bisa reply)

**Alasan/JUSTIFIKASI**:
```
1. HIGH OPEN RATE
   - Email open rate 20%, WhatsApp open rate 90%+
   → Reminder lebih efektif
   
2. LOW COST
   - Dibandingkan kirim SMS (Rp 100-200/SMS)
   - WhatsApp API lebih murah untuk volume besar
   
3. CONVENIENCE
   - Nasabah Indonesia lebih familiar WhatsApp daripada email
   → Higher engagement
```

#### B. Payment Gateway Integration
**Fitur Detail**:
- Virtual Account (bCA, Mandiri, BNI, dll)
- E-Wallet (GoPay, OVO, DANA, LinkAja)
- QRIS untuk pembayaran di merchant
- Auto-reconcile (pembayaran auto-match dengan angsuran)

**Alasan/JUSTIFIKASI**:
```
1. CONVENIENCE
   - Nasabah bisa bayar angsuran via transfer bank/e-wallet
   - Tidak harus tunggu petugas datang (24/7 payment)
   → Collection rate meningkat
   
2. AUTO-RECONCILE
   - Bayar via VA → Sistem auto-update status angsuran
   - Tidak perlu petugas cek mutasi bank manual
   → Operational cost turun, accuracy naik
```

#### C. Machine Learning / AI (Phase 3)
**Fitur Detail**:
- Credit scoring otomatis berdasarkan history & data
- Prediksi risiko default sebelum approve pinjaman
- Anomaly detection (transaksi mencurigakan)
- Chatbot untuk FAQ nasabah

**Alasan/JUSTIFIKASI**:
```
1. RISK MITIGATION
   - ML prediksi: "Nasabah ini 80% berpotensi macet"
   → Bisa tolak pinjaman atau kasih bunga lebih tinggi
   → NPL turun
   
2. AUTOMATION
   - Credit scoring yang sekarang manual (perlu analis)
   → Auto-scoring dalam hitungan detik
   → Speed up approval process
   
3. 24/7 SERVICE
   - Chatbot jawab pertanyaan nasabah kapan saja
   → CS tidak perlu jawab pertanyaan repetitif
```

---

### 14.10 RINGKASAN PRIORITAS FITUR

#### MUST HAVE (Phase 1 - MVP)
| No | Fitur | Alasan Kritis |
|----|-------|---------------|
| 1 | RBAC & Autentikasi | Isolasi data, prevent fraud |
| 2 | Master Data Anggota | Basis semua transaksi |
| 3 | Pinjaman & Bunga | Core business |
| 4 | Angsuran & Koleksi | Cash flow management |
| 5 | Multi-Cabang | Anda punya beberapa cabang |
| 6 | Audit Trail | Compliance & fraud detection |
| 7 | Dashboard Basic | Monitoring bisnis |

#### SHOULD HAVE (Phase 2 - Enhancement)
| No | Fitur | Alasan Penting |
|----|-------|----------------|
| 1 | Simpanan/Tabungan | Liquidity management |
| 2 | Laporan Otomatis | Time saving & compliance |
| 3 | Smart Collection | Efficiency & collection rate |
| 4 | WhatsApp Gateway | Communication & reminder |
| 5 | Nasabah Portal | Transparency & trust |

#### NICE TO HAVE (Phase 3 - Advanced)
| No | Fitur | Alasan Value-Add |
|----|-------|------------------|
| 1 | Payment Gateway | Convenience & auto-reconcile |
| 2 | ML/AI Scoring | Advanced risk management |
| 3 | Mobile App Native | Better UX |
| 4 | Business Intelligence | Strategic insights |

---

## 15. STRATEGI MIGRASI DATA: DARI MANUAL KE DIGITAL

> **Pertanyaan Kritis**: "Apakah harus input semua data lama dulu sebelum aplikasi jalan, atau bisa sambil berjalan?"

**Jawaban**: Gunakan pendekatan **PHASED MIGRATION** (Migrasi Bertahap) - tidak perlu tunggu semua data selesai, tapi juga tidak langsung cut-off total.

---

### 15.1 PILIHAN STRATEGI MIGRASI

| Strategi | Deskripsi | Pros | Cons | Cocok untuk |
|----------|-----------|------|------|-------------|
| **BIG BANG** | Input SEMUA data lama dulu, baru go live | Bersih, konsisten | Lama, risky, operasi berhenti saat input | Data kecil (< 1000 anggota) |
| **PARALLEL** | Manual & digital berjalan bersamaan, lalu switch | Aman, bisa verifikasi | Double effort, resource intensive | Transisi aman |
| **PHASED** (Rekomendasi) | Migrasi per batch/cabang/waktu | Fleksibel, minimal disruption | Perlu koordinasi | Koperasi dengan banyak cabang |
| **CUT-OFF** | Data lama tetap manual, data baru masuk digital | Cepat deploy | Data tidak utuh | Emergency deployment |

**Rekomendasi untuk Anda**: **HYBRID PH + CUT-OFF**
- **Data Master** (Anggota, Petugas): Migrasi dulu (Phased)
- **Pinjaman Aktif**: Migrasi bertahap
- **Pinjaman Lunas Lama**: Cut-off (tidak dimigrasi, hanya arsip PDF)
- **Transaksi Baru**: Langsung digital

---

### 15.2 STRATEGI REKOMENDASI: "3 FASE MIGRASI"

```
┌─────────────────────────────────────────────────────────────────────┐
│                    TIMELINE MIGRASI (12-16 Minggu)                  │
├─────────────────────────────────────────────────────────────────────┤
│                                                                     │
│  FASE 1: PREPARASI (Minggu 1-4)                                    │
│  ├── Audit & Cleaning Data Manual                                  │
│  ├── Setup Aplikasi & Database                                     │
│  ├── Training Petugas                                              │
│  └── UAT (User Acceptance Testing)                                 │
│                                                                     │
│  FASE 2: PILOT & PARALLEL (Minggu 5-8)                             │
│  ├── Pilih 1 Cabang untuk Pilot                                    │
│  ├── Input Data Master (Anggota aktif)                             │
│  ├── Parallel Operation: Manual + Digital                          │
│  └── Validasi & Koreksi                                            │
│                                                                     │
│  FASE 3: ROLLOUT & OPTIMIZATION (Minggu 9-16)                      │
│  ├── Rollout ke Cabang lain (1 per minggu)                         │
│  ├── Bulk Import data historis (optional)                          │
│  └── Full Digital Operation                                        │
│                                                                     │
└─────────────────────────────────────────────────────────────────────┘
```

---

### 15.3 DETAIL FASE MIGRASI

#### FASE 1: PREPARASI (Minggu 1-4)

**A. Data Audit & Cleaning**
```
1. INVENTORY DATA MANUAL
   ├── List semua buku/catatan yang ada:
   │   - Buku Daftar Anggota
   │   - Buku Pinjaman (aktif & lunas)
   │   - Buku Angsuran/Kas
   │   - Buku Simpanan
   │   └── Arsip perjanjian/garansi
   │
   └── Kategorikan data:
       - DATA AKTIF (anggota masih pinjam/tabung)
       - DATA LUNAS < 1 tahun (riwayat penting)
       - DATA LUNAS > 1 tahun (arsip saja)
       - DATA BERMasalah (anggota macet/blacklist)
```

**B. Data Cleaning (KRITIS!)**
```
Masalah Umum Data Manual:
├── Duplikasi: Satu anggota tercatat 2x dengan nama beda
├── Incomplete: Data KTP tidak lengkap, telepon tidak ada
├── Inkonsisten: Format tanggal beda-beda (01/02/2024 vs 2024-02-01)
├── Missing: Data anggota lama hilang/rusak
└── Error: Saldo pinjaman tidak match dengan buku kas

Solusi Cleaning:
1. Buat Excel template standar (sesuai struktur database)
2. Isi data ke Excel (1 sheet = 1 tabel database)
3. Validasi dengan formula Excel:
   - KTP harus 16 digit angka
   - Telepon harus 10-13 digit
   - Tanggal dalam format YYYY-MM-DD
   - Saldo pinjaman = Pokok - Total Angsuran Terbayar
4. Cross-check dengan buku kas (harus match)
```

**C. Setup Aplikasi**
```
1. Install aplikasi di server hosting/lokal
2. Setup database & struktur tabel
3. Konfigurasi cabang (sesuai jumlah cabang Anda)
4. Buat user account untuk masing-masing petugas
5. Setting parameter bunga & produk pinjaman
```

---

#### FASE 2: PILOT & PARALLEL (Minggu 5-8)

**A. Pilih Cabang Pilot**
```
Kriteria Cabang Pilot:
- Cabang dengan data paling "bersih" (tercatat rapi)
- Cabang dengan jumlah anggota sedang (50-100 anggota)
- Manager cabang kooperatif & tech-savvy
- Dekat dengan kantor pusat (mudah monitoring)

Contoh: Pilih "Cabang Pasar A" untuk pilot
```

**B. Input Data Master (Anggota Aktif)**
```
Urutan Input Data:
1. DATA ANGGOTA (Tabel: anggota)
   ├── Input semua anggota AKTIF (masih ada pinjaman/tabungan)
   ├── Prioritaskan: Yang sering transaksi
   └── Skip sementara: Anggota tidak aktif > 1 tahun

2. DATA PETUGAS (Tabel: petugas)
   ├── Input data petugas lapangan
   ├── Assign ke cabang masing-masing
   └── Buat user login untuk masing-masing

3. DATA PINJAMAN AKTIF (Tabel: pinjaman & angsuran)
   ├── List pinjaman yang masih berjalan
   ├── Input: Plafon, tenor, bunga, sisa pokok
   ├── Generate jadwal angsuran (sampai lunas)
   └── Input pembayaran yang sudah terjadi (historis)
   
   Format Input:
   - Pinjaman ID: Auto-generate sistem
   - Anggota ID: Pilih dari dropdown (sudah input di langkah 1)
   - Tanggal akad: Sesuai perjanjian
   - Sisa pokok: Hitung dari buku manual
```

**C. Parallel Operation (Operasi Ganda)**
```
Skenario Parallel:
┌────────────────────────────────────────────────────────────┐
│  Transaksi  │  Catatan Manual  │  Input ke Digital │ Sync  │
├────────────────────────────────────────────────────────────┤
│  Senin      │  Buku kas        │  Aplikasi         │ √     │
│  Bayar A    │  Ditulis         │  Diinput          │ Match │
│  Bayar B    │  Ditulis         │  Diinput          │ Match │
└────────────────────────────────────────────────────────────┘

Prosedur Parallel (4 Minggu):
- Petugas tetap catat di buku manual (seperti biasa)
- Petugas juga input ke aplikasi (double input)
- Setiap sore: Supervisor cek "apakah manual = digital?"
- Jika ada selisih: Koreksi & cari penyebab

Tujuan Parallel:
- Validasi akurasi sistem
- Training petugas dengan data nyata
- Build confidence sebelum go-live penuh
```

---

#### FASE 3: ROLLOUT (Minggu 9-16)

**A. Rollout bertahap per Cabang**
```
Jadwal Rollout (contoh 5 cabang):
Minggu 9  : Cabang Pilot (sudah stabil, full digital)
Minggu 10 : Cabang 2 (training + input data + parallel 1 minggu)
Minggu 11 : Cabang 3 (training + input data + parallel 1 minggu)
Minggu 12 : Cabang 4 (training + input data + parallel 1 minggu)
Minggu 13 : Cabang 5 (training + input data + parallel 1 minggu)
Minggu 14-16: Optimization & fix issues
```

**B. Bulk Import Data Historis (Opsional)**
```
Data yang bisa di-import bulk (via Excel/CSV):
├── Anggota (gunakan Excel template)
├── Petugas (jika banyak)
└── Pinjaman aktif (1 file per cabang)

Cara Bulk Import:
1. Siapkan file Excel sesuai template
2. Validasi data (cek duplicate, format)
3. Import via PHP script atau phpMyAdmin
4. Verifikasi hasil import (spot check)

Contoh Script PHP untuk Import:
```php
// Upload file Excel
// Parse dengan library (PhpSpreadsheet)
// Validasi setiap row
// Insert ke database
// Generate log hasil import
```

Data yang TIDAK perlu di-import:
├── Pinjaman lunas > 1 tahun (hanya arsip PDF saja)
├── Anggota tidak aktif > 2 tahun
└── Transaksi angsuran historis detail (cukup saldo akhir)
```

---

### 15.4 TEMPLATE EXCEL UNTUK MIGRASI

#### Template 1: Data Anggota
```
Sheet: anggota_import.xlsx

| No | kode_anggota | cabang_id | nama       | no_ktp          | alamat      | telepon    | status |
|----|--------------|-----------|------------|-----------------|-------------|------------|--------|
| 1  | AGG001       | 1         | Siti Aminah| 3201012345678901| Jl. Mawar 1 | 0812345678 | aktif  |
| 2  | AGG002       | 1         | Budi Santoso| 3201012345678902| Jl. Melati 2| 0812345679 | aktif  |

Validasi:
- kode_anggota: Unique, tidak boleh duplikat
- cabang_id: Harus sesuai dengan ID cabang di sistem
- no_ktp: 16 digit angka
- telepon: 10-13 digit, diawali 08
- status: aktif/nonaktif/blacklist
```

#### Template 2: Data Pinjaman Aktif
```
Sheet: pinjaman_import.xlsx

| No | kode_pinjaman | cabang_id | anggota_kode | plafon    | tenor | bunga | tanggal_akad | sisa_pokok |
|----|---------------|-----------|--------------|-----------|-------|-------|--------------|------------|
| 1  | PJ001         | 1         | AGG001       | 5000000   | 12    | 2.5   | 2024-01-15   | 2500000    |
| 2  | PJ002         | 1         | AGG002       | 3000000   | 6     | 3     | 2024-02-20   | 1500000    |

Catatan:
- sisa_pokok: Hitung dari buku manual (Pokok - sudah dibayar)
- tenor: Dalam bulan (atau hari untuk pinjaman harian)
- bunga: Persen per bulan/per hari (sesuai produk)
```

---

### 15.5 MENGHANDLE TRANSAKSI SAAT MIGRASI

#### Skenario: Ada Transaksi Baru saat Input Data Lama
```
SOLUSI: "CUT-OFF DATE"

1. Tentukan Tanggal Cut-Off: 
   Contoh: "1 Juni 2026"

2. Data Lama (sebelum 1 Juni):
   ├── Pinjaman aktif yang sudah berjalan → Input ke sistem
   ├── Sisa pokok per 31 Mei 2026 → Input sebagai "sisa awal"
   └── Angsuran yang sudah terjadi sebelum 1 Juni → TIDAK diinput detail
       (cukup catat: "Sudah dibayar X kali, sisa Y kali")

3. Transaksi Baru (mulai 1 Juni):
   ├── Pinjaman baru → Langsung input ke sistem
   ├── Angsuran baru → Langsung input ke sistem
   └── Semua transaksi mulai 1 Juni 100% digital

4. Parallel Period (1-30 Juni):
   ├── Sistem: Catat transaksi baru
   ├── Manual: Tetap catat transaksi baru (backup)
   └── Akhir Juni: Stop manual, 100% digital
```

#### Contoh Kasus Real
```
Kasus: Pak Ahmad punya pinjaman sejak Januari 2024
- Plafon awal: Rp 5.000.000
- Total angsuran: 20x (Rp 250.000/bulan)
- Sudah dibayar: 12x (Rp 3.000.000)
- Sisa: 8x (Rp 2.000.000)

Cara Input ke Sistem:
1. Buat pinjaman dengan:
   - Plafon: Rp 5.000.000
   - Tenor: 20 bulan
   - Tanggal akad: 15 Januari 2024
   
2. Generate jadwal angsuran 20x

3. Input pembayaran historis 12x:
   - Atau gunakan shortcut: "Sudah bayar 12x, sisa 8x"
   - Sistem otomatis tandai 12 angsuran pertama sebagai LUNAS
   - Sisa 8 angsuran sebagai BELUM LUNAS

4. Mulai 1 Juni: Input angsuran ke-13 langsung ke sistem
```

---

### 15.6 BACKUP & ROLLBACK STRATEGY

```
1. BACKUP DATA MANUAL
   ├── Scan/foto semua buku kas (jadi PDF)
   ├── Simpan di cloud (Google Drive/Dropbox)
   └── Jangan buang buku manual sebelum 6 bulan stabil

2. BACKUP DIGITAL
   ├── Setup auto-backup database (harian)
   ├── Simpan backup di lokasi terpisah
   └── Test restore procedure sebelum go-live

3. ROLLBACK PLAN (Kalau gagal)
   ├── Jika sistem bermasalah di minggu pertama:
       → Kembali ke manual sementara
       → Fix issue
       → Retry migrasi
   ├── Jika 1 cabang gagal:
       → Cabang lain tetap jalan
       → Fix cabang bermasalah terpisah
   └── Jangan panic: Data aman di backup
```

---

### 15.7 CHECKLIST MIGRASI

#### Sebelum Mulai (Pre-Migration)
- [ ] Audit data manual selesai
- [ ] Excel template siap & validasi formula
- [ ] Data master (anggota aktif) sudah diisi di Excel
- [ ] Aplikasi terinstall & konfigurasi selesai
- [ ] Training material siap
- [ ] Petugas pilot sudah training
- [ ] Backup data manual tersimpan

#### Selama Migrasi (Migration)
- [ ] Input data anggota (done)
- [ ] Input data pinjaman aktif (done)
- [ ] Parallel operation berjalan lancar
- [ ] Spot check: Manual = Digital (match 100%)
- [ ] Petugas nyaman dengan aplikasi
- [ ] Issue log documented & resolved

#### Setelah Go-Live (Post-Migration)
- [ ] Stop pencatatan manual
- [ ] 100% operasi via aplikasi
- [ ] Buku manual diarsipkan (tetap disimpan)
- [ ] Backup otomatis berjalan
- [ ] Laporan pertama berhasil generate
- [ ] Semua petugas bisa akses & input

---

### 15.8 TIPS SUKSES MIGRASI

```
1. JANGAN PERFEKSIONIS
   - Tidak perlu 100% data lama masuk sistem
   - Prioritaskan: Data aktif & strategis
   - Data lama bisa diarsip PDF saja

2. START SMALL
   - Mulai dari 1 cabang, jangan semua cabang sekaligus
   - Validasi dulu, baru rollout

3. DATA CLEANING ADALAH 80% PEKERJAAN
   - Lebih baik lambat tapi data bersih
   - Jangan bawa "sampah" dari manual ke digital

4. TRAINING & CHANGE MANAGEMENT
   - Petugas butuh waktu adaptasi
   - Jangan marah jika lambat di awal
   - Beri insentif untuk adopsi cepat

5. SUPPORT SYSTEM
   - Siapkan tim support (WA/telepon) untuk bantu petugas
   - Response time harus cepat (< 15 menit)
   - Buat FAQ & video tutorial

6. CELEBRATE SMALL WINS
   - Cabang pertama sukses? Beri reward tim
   - Share good news ke cabang lain (build excitement)
```

---

### 15.9 KESIMPULAN

**Jawaban untuk Pertanyaan Anda**:
> "Apakah harus input semua dulu, atau bisa sambil berjalan?"

**Gunakan Strategi: "PHASED MIGRATION dengan CUT-OFF DATE"**

1. **Tidak perlu input SEMUA data lama** - hanya data aktif & penting saja
2. **Bisa sambil berjalan** - mulai dengan 1 cabang pilot
3. **Set tanggal cut-off** - data baru langsung digital, data lama dimigrasi bertahap
4. **Parallel operation** - manual & digital berjalan bersamaan 1-2 minggu untuk validasi
5. **Data historis detail** - boleh skip, cukup saldo akhir yang penting

**Timeline Rekomendasi**: **3-4 bulan** untuk migrasi lengkap semua cabang
- Minggu 1-4: Preparasi & cleaning data
- Minggu 5-8: Pilot 1 cabang + parallel
- Minggu 9-16: Rollout ke cabang lain
- Setelahnya: Full digital operation

**Dibuat**: April 2026  
**Tujuan**: Panduan digitalisasi usaha koperasi pasar dengan pinjaman bunga dinamis  
**Platform**: PHP, Bootstrap, jQuery, AJAX, MySQL, API

---

## 16. IMPLEMENTASI PRAKTIS (CODE SIAP PAKAI)

> **Tujuan**: Memberikan contoh code PHP yang bisa langsung Anda gunakan untuk membangun aplikasi. Code ini adalah boilerplate yang bisa dikembangkan sesuai kebutuhan.

---

### 16.1 SETUP DATABASE

#### A. SQL Schema (Copy-Paste ke phpMyAdmin)
```sql
-- Buat database
CREATE DATABASE kewer_koperasi;
USE kewer_koperasi;

-- Tabel Cabang
CREATE TABLE cabang (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kode_cabang VARCHAR(10) UNIQUE NOT NULL,
    nama_cabang VARCHAR(100) NOT NULL,
    alamat TEXT,
    telp VARCHAR(20),
    manager_id INT,
    tanggal_buka DATE,
    status ENUM('aktif', 'nonaktif') DEFAULT 'aktif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel Users (Login)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cabang_id INT NULL,
    nama VARCHAR(100) NOT NULL,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('superadmin', 'manager', 'petugas') NOT NULL,
    status ENUM('aktif', 'nonaktif') DEFAULT 'aktif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cabang_id) REFERENCES cabang(id)
);

-- Tabel Nasabah
CREATE TABLE nasabah (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cabang_id INT NOT NULL,
    kode_nasabah VARCHAR(20) UNIQUE NOT NULL,
    nama VARCHAR(100) NOT NULL,
    alamat TEXT,
    ktp VARCHAR(16) UNIQUE NOT NULL,
    telp VARCHAR(15),
    jenis_usaha VARCHAR(50),
    lokasi_pasar VARCHAR(100),
    status ENUM('aktif', 'nonaktif', 'blacklist') DEFAULT 'aktif',
    foto_ktp VARCHAR(255),
    foto_selfie VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cabang_id) REFERENCES cabang(id)
);

-- Tabel Petugas
CREATE TABLE petugas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cabang_id INT NOT NULL,
    kode_petugas VARCHAR(20) UNIQUE NOT NULL,
    nama VARCHAR(100) NOT NULL,
    telp VARCHAR(15),
    area_wilayah VARCHAR(100),
    target_harian DECIMAL(10,0) DEFAULT 500000,
    status ENUM('aktif', 'nonaktif') DEFAULT 'aktif',
    user_id INT UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cabang_id) REFERENCES cabang(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Tabel Pinjaman
CREATE TABLE pinjaman (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cabang_id INT NOT NULL,
    kode_pinjaman VARCHAR(20) UNIQUE NOT NULL,
    nasabah_id INT NOT NULL,
    jenis_pinjaman VARCHAR(50),
    plafon DECIMAL(12,0) NOT NULL,
    tenor INT NOT NULL,
    bunga_per_bulan DECIMAL(5,2) NOT NULL,
    total_bunga DECIMAL(12,0) NOT NULL,
    total_angsuran DECIMAL(12,0) NOT NULL,
    status ENUM('pengajuan', 'disetujui', 'aktif', 'lunas', 'macet') DEFAULT 'pengajuan',
    tanggal_akad DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cabang_id) REFERENCES cabang(id),
    FOREIGN KEY (nasabah_id) REFERENCES nasabah(id)
);

-- Tabel Angsuran
CREATE TABLE angsuran (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cabang_id INT NOT NULL,
    pinjaman_id INT NOT NULL,
    no_angsuran INT NOT NULL,
    jatuh_tempo DATE NOT NULL,
    pokok DECIMAL(12,0) NOT NULL,
    bunga DECIMAL(12,0) NOT NULL,
    total_angsuran DECIMAL(12,0) NOT NULL,
    status ENUM('belum', 'lunas', 'telat') DEFAULT 'belum',
    tanggal_bayar DATE NULL,
    petugas_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cabang_id) REFERENCES cabang(id),
    FOREIGN KEY (pinjaman_id) REFERENCES pinjaman(id),
    FOREIGN KEY (petugas_id) REFERENCES petugas(id)
);

-- Tabel Pembayaran
CREATE TABLE pembayaran (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cabang_id INT NOT NULL,
    angsuran_id INT NOT NULL,
    tanggal_bayar DATE NOT NULL,
    jumlah DECIMAL(12,0) NOT NULL,
    metode ENUM('cash', 'transfer', 'ewallet') DEFAULT 'cash',
    petugas_id INT NOT NULL,
    bukti_transfer VARCHAR(255),
    catatan TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cabang_id) REFERENCES cabang(id),
    FOREIGN KEY (angsuran_id) REFERENCES angsuran(id),
    FOREIGN KEY (petugas_id) REFERENCES petugas(id)
);

-- Tabel Tabungan (Opsional)
CREATE TABLE tabungan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cabang_id INT NOT NULL,
    nasabah_id INT NOT NULL,
    jenis ENUM('umum', 'berjangka', 'autodebet') DEFAULT 'umum',
    jumlah DECIMAL(12,0) NOT NULL,
    tipe ENUM('setor', 'tarik') NOT NULL,
    petugas_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cabang_id) REFERENCES cabang(id),
    FOREIGN KEY (nasabah_id) REFERENCES nasabah(id),
    FOREIGN KEY (petugas_id) REFERENCES petugas(id)
);

-- Insert data awal
INSERT INTO cabang (kode_cabang, nama_cabang, alamat, telp) VALUES 
('KB001', 'Cabang Pasar A', 'Jl. Pasar A No. 1', '0812345678'),
('KB002', 'Cabang Pasar B', 'Jl. Pasar B No. 2', '0812345679');

-- Insert user superadmin (password: admin123)
INSERT INTO users (nama, username, password, role) VALUES 
('Super Admin', 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'superadmin');

-- Insert manager cabang (password: manager123)
INSERT INTO users (cabang_id, nama, username, password, role) VALUES 
(1, 'Manager Cabang A', 'mgr_a', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'manager'),
(2, 'Manager Cabang B', 'mgr_b', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'manager');
```

---

### 16.2 CONFIGURATION FILES

#### A. config/database.php
```php
<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'kewer_koperasi');
define('DB_USER', 'root');
define('DB_PASS', '');

// Create connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset
$conn->set_charset("utf8mb4");

// Global function for query
function query($sql, $params = []) {
    global $conn;
    
    $stmt = $conn->prepare($sql);
    
    if ($params) {
        $types = str_repeat('s', count($params));
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    
    if (strpos($sql, 'SELECT') === 0) {
        $result = $stmt->get_result();
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        return $data;
    }
    
    return $stmt->affected_rows;
}
?>
```

#### B. config/session.php
```php
<?php
session_start();

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Get current user data
function getCurrentUser() {
    if (!isLoggedIn()) return null;
    
    $user = query("SELECT * FROM users WHERE id = ?", [$_SESSION['user_id']]);
    return $user[0] ?? null;
}

// Check user role
function hasRole($role) {
    $user = getCurrentUser();
    return $user && $user['role'] === $role;
}

// Redirect if not logged in
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }
}

// Redirect if not authorized
function requireRole($role) {
    requireLogin();
    if (!hasRole($role)) {
        header('Location: dashboard.php');
        exit();
    }
}

// Get current cabang for non-superadmin
function getCurrentCabang() {
    $user = getCurrentUser();
    if (!$user) return null;
    
    if ($user['role'] === 'superadmin') {
        return $_GET['cabang_id'] ?? $_SESSION['cabang_id'] ?? null;
    }
    
    return $user['cabang_id'];
}
?>
```

---

### 16.3 CORE FUNCTIONS

#### A. includes/functions.php
```php
<?php
require_once 'config/database.php';
require_once 'config/session.php';

// Generate unique code
function generateKode($prefix, $table, $field) {
    $result = query("SELECT MAX(CAST(SUBSTRING($field, 4) AS UNSIGNED)) as max_num FROM $table WHERE $field LIKE ?", ["$prefix%"]);
    $next_num = ($result[0]['max_num'] ?? 0) + 1;
    return $prefix . str_pad($next_num, 3, '0', STR_PAD_LEFT);
}

// Calculate loan interest (Flat Rate)
function calculateLoan($plafon, $tenor, $bunga_per_bulan) {
    $total_bunga = $plafon * ($bunga_per_bulan / 100) * $tenor;
    $total_pembayaran = $plafon + $total_bunga;
    $angsuran_pokok = $plafon / $tenor;
    $angsuran_bunga = $total_bunga / $tenor;
    $angsuran_total = $angsuran_pokok + $angsuran_bunga;
    
    return [
        'total_bunga' => $total_bunga,
        'total_pembayaran' => $total_pembayaran,
        'angsuran_pokok' => $angsuran_pokok,
        'angsuran_bunga' => $angsuran_bunga,
        'angsuran_total' => $angsuran_total
    ];
}

// Create loan schedule
function createLoanSchedule($pinjaman_id, $plafon, $tenor, $bunga_per_bulan, $tanggal_akad) {
    $calc = calculateLoan($plafon, $tenor, $bunga_per_bulan);
    $cabang_id = getCurrentCabang();
    
    for ($i = 1; $i <= $tenor; $i++) {
        $jatuh_tempo = date('Y-m-d', strtotime("+$i month", strtotime($tanggal_akad)));
        
        query("INSERT INTO angsuran (cabang_id, pinjaman_id, no_angsuran, jatuh_tempo, pokok, bunga, total_angsuran) VALUES (?, ?, ?, ?, ?, ?, ?)", [
            $cabang_id,
            $pinjaman_id,
            $i,
            $jatuh_tempo,
            $calc['angsuran_pokok'],
            $calc['angsuran_bunga'],
            $calc['angsuran_total']
        ]);
    }
}

// Check late payments
function checkLatePayments() {
    $cabang_id = getCurrentCabang();
    
    // Update status to 'telat' for payments past due date
    query("UPDATE angsuran SET status = 'telat' WHERE cabang_id = ? AND status = 'belum' AND jatuh_tempo < CURDATE()", [$cabang_id]);
    
    // Get list of late payments
    return query("SELECT a.*, n.nama, n.telp, p.kode_pinjaman 
                  FROM angsuran a 
                  JOIN pinjaman p ON a.pinjaman_id = p.id 
                  JOIN nasabah n ON p.nasabah_id = n.id 
                  WHERE a.cabang_id = ? AND a.status = 'telat' 
                  ORDER BY a.jatuh_tempo", [$cabang_id]);
}

// Format currency
function formatRupiah($amount) {
    return 'Rp ' . number_format($amount, 0, ',', '.');
}

// Format date
function formatDate($date, $format = 'd M Y') {
    return date($format, strtotime($date));
}

// Validate KTP
function validateKTP($ktp) {
    return preg_match('/^[0-9]{16}$/', $ktp);
}

// Validate phone
function validatePhone($phone) {
    return preg_match('/^08[0-9]{9,12}$/', $phone);
}

// Send WhatsApp notification (placeholder)
function sendWhatsApp($phone, $message) {
    // Implement WhatsApp API integration here
    // For now, just log the message
    error_log("WA to $phone: $message");
    return true;
}
?>
```

---

### 16.4 LOGIN SYSTEM

#### A. login.php
```php
<?php
require_once 'includes/functions.php';

if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit();
}

$error = '';

if ($_POST) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    $user = query("SELECT * FROM users WHERE username = ? AND status = 'aktif'", [$username]);
    
    if ($user && password_verify($password, $user[0]['password'])) {
        $_SESSION['user_id'] = $user[0]['id'];
        $_SESSION['cabang_id'] = $user[0]['cabang_id'];
        
        header('Location: dashboard.php');
        exit();
    } else {
        $error = 'Username atau password salah';
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Kewer Koperasi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            padding: 40px;
            width: 100%;
            max-width: 400px;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="text-center mb-4">
            <h2>Kewer Koperasi</h2>
            <p class="text-muted">Sistem Pinjaman Modal Pedagang</p>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Login</button>
        </form>
    </div>
</body>
</html>
```

---

### 16.5 DASHBOARD

#### A. dashboard.php
```php
<?php
require_once 'includes/functions.php';
requireLogin();

$user = getCurrentUser();
$cabang_id = getCurrentCabang();

// Get dashboard stats
$total_nasabah = query("SELECT COUNT(*) as total FROM nasabah WHERE cabang_id = ? AND status = 'aktif'", [$cabang_id])[0]['total'];
$total_pinjaman = query("SELECT COUNT(*) as total FROM pinjaman WHERE cabang_id = ? AND status = 'aktif'", [$cabang_id])[0]['total'];
$outstanding = query("SELECT SUM(plafon) as total FROM pinjaman WHERE cabang_id = ? AND status = 'aktif'", [$cabang_id])[0]['total'];
$late_payments = count(checkLatePayments());

// Get recent activities
$recent_activities = query("
    SELECT 
        CASE 
            WHEN p.id IS NOT NULL THEN CONCAT('Pinjaman ', p.kode_pinjaman, ' untuk ', n.nama)
            WHEN pemb.id IS NOT NULL THEN CONCAT('Pembayaran ', pemb.jumlah, ' dari ', n.nama)
            ELSE 'Aktivitas lain'
        END as activity,
        created_at
    FROM (
        SELECT id, kode_pinjaman, nasabah_id, created_at FROM pinjaman WHERE cabang_id = ?
        UNION ALL
        SELECT id, NULL as kode_pinjaman, angsuran_id as nasabah_id, created_at FROM pembayaran WHERE cabang_id = ?
    ) recent
    LEFT JOIN pinjaman p ON recent.id = p.id AND recent.kode_pinjaman IS NOT NULL
    LEFT JOIN pembayaran pemb ON recent.id = pemb.id AND pemb.kode_pinjaman IS NULL
    LEFT JOIN nasabah n ON 
        (p.nasabah_id = n.id OR pemb.angsuran_id IN (SELECT id FROM angsuran WHERE pinjaman_id = p.id))
    ORDER BY created_at DESC
    LIMIT 5
", [$cabang_id, $cabang_id]);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Kewer Koperasi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include 'includes/sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Dashboard</h1>
                    <?php if ($user['role'] === 'superadmin'): ?>
                        <select class="form-select" id="cabangSelector" style="width: 200px;">
                            <option value="">Semua Cabang</option>
                            <?php
                            $cabangs = query("SELECT * FROM cabang WHERE status = 'aktif'");
                            foreach ($cabangs as $cabang):
                            ?>
                                <option value="<?php echo $cabang['id']; ?>" <?php echo $cabang_id == $cabang['id'] ? 'selected' : ''; ?>>
                                    <?php echo $cabang['nama_cabang']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    <?php endif; ?>
                </div>
                
                <!-- Stats Cards -->
                <div class="row mb-4">
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-primary shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                            Total Nasabah
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            <?php echo $total_nasabah; ?>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-users fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-success shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                            Pinjaman Aktif
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            <?php echo $total_pinjaman; ?>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-info shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                            Outstanding
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            <?php echo formatRupiah($outstanding); ?>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-credit-card fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-warning shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                            Tunggakan
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            <?php echo $late_payments; ?>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Recent Activities -->
                <div class="card">
                    <div class="card-header">
                        <h5>Aktivitas Terkini</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($recent_activities)): ?>
                            <p class="text-muted">Belum ada aktivitas</p>
                        <?php else: ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($recent_activities as $activity): ?>
                                    <div class="list-group-item d-flex justify-content-between">
                                        <div>
                                            <?php echo $activity['activity']; ?>
                                        </div>
                                        <small class="text-muted">
                                            <?php echo formatDate($activity['created_at'], 'd M Y H:i'); ?>
                                        </small>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Cabang selector
        document.getElementById('cabangSelector')?.addEventListener('change', function() {
            const url = new URL(window.location);
            url.searchParams.set('cabang_id', this.value);
            window.location = url;
        });
    </script>
</body>
</html>
```

---

### 16.6 PINJAMAN MODULE

#### A. nasabah/tambah.php (Tambah Nasabah)
```php
<?php
require_once '../../includes/functions.php';
requireLogin();

$error = '';
$success = '';

if ($_POST) {
    $nama = $_POST['nama'] ?? '';
    $alamat = $_POST['alamat'] ?? '';
    $ktp = $_POST['ktp'] ?? '';
    $telp = $_POST['telp'] ?? '';
    $jenis_usaha = $_POST['jenis_usaha'] ?? '';
    $lokasi_pasar = $_POST['lokasi_pasar'] ?? '';
    $cabang_id = getCurrentCabang();
    
    // Validation
    if (!validateKTP($ktp)) {
        $error = 'Format KTP tidak valid (16 digit angka)';
    } elseif (!validatePhone($telp)) {
        $error = 'Format telepon tidak valid (08xxxxxxxxxx)';
    } else {
        // Check duplicate KTP
        $check = query("SELECT id FROM nasabah WHERE ktp = ?", [$ktp]);
        if ($check) {
            $error = 'KTP sudah terdaftar';
        } else {
            // Generate kode nasabah
            $kode_nasabah = generateKode('NSB', 'nasabah', 'kode_nasabah');
            
            // Handle file uploads
            $foto_ktp = '';
            $foto_selfie = '';
            
            if (isset($_FILES['foto_ktp']) && $_FILES['foto_ktp']['error'] === 0) {
                $foto_ktp = 'uploads/ktp_' . $kode_nasabah . '_' . time() . '.jpg';
                move_uploaded_file($_FILES['foto_ktp']['tmp_name'], '../../' . $foto_ktp);
            }
            
            if (isset($_FILES['foto_selfie']) && $_FILES['foto_selfie']['error'] === 0) {
                $foto_selfie = 'uploads/selfie_' . $kode_nasabah . '_' . time() . '.jpg';
                move_uploaded_file($_FILES['foto_selfie']['tmp_name'], '../../' . $foto_selfie);
            }
            
            // Insert nasabah
            $result = query("INSERT INTO nasabah (cabang_id, kode_nasabah, nama, alamat, ktp, telp, jenis_usaha, lokasi_pasar, foto_ktp, foto_selfie) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)", [
                $cabang_id, $kode_nasabah, $nama, $alamat, $ktp, $telp, $jenis_usaha, $lokasi_pasar, $foto_ktp, $foto_selfie
            ]);
            
            if ($result) {
                $success = 'Nasabah berhasil ditambahkan';
                // Clear form
                $_POST = [];
            } else {
                $error = 'Gagal menambahkan nasabah';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Nasabah - Kewer Koperasi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include '../includes/sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Tambah Nasabah</h1>
                    <a href="index.php" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Kembali
                    </a>
                </div>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <div class="card">
                    <div class="card-body">
                        <form method="POST" enctype="multipart/form-data">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Nama Lengkap *</label>
                                        <input type="text" name="nama" class="form-control" value="<?php echo $_POST['nama'] ?? ''; ?>" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">No. KTP *</label>
                                        <input type="text" name="ktp" class="form-control" value="<?php echo $_POST['ktp'] ?? ''; ?>" maxlength="16" required>
                                        <small class="form-text">16 digit angka</small>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">No. Telepon *</label>
                                        <input type="tel" name="telp" class="form-control" value="<?php echo $_POST['telp'] ?? ''; ?>" required>
                                        <small class="form-text">08xxxxxxxxxx</small>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Jenis Usaha</label>
                                        <select name="jenis_usaha" class="form-select">
                                            <option value="">Pilih Jenis Usaha</option>
                                            <option value="Pedagang Sayur">Pedagang Sayur</option>
                                            <option value="Pedagang Buah">Pedagang Buah</option>
                                            <option value="Warung Makan">Warung Makan</option>
                                            <option value="Warung Kelontong">Warung Kelontong</option>
                                            <option value="Toko Baju">Toko Baju</option>
                                            <option value="Lainnya">Lainnya</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Alamat</label>
                                        <textarea name="alamat" class="form-control" rows="3"><?php echo $_POST['alamat'] ?? ''; ?></textarea>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Lokasi Pasar/Warung</label>
                                        <input type="text" name="lokasi_pasar" class="form-control" value="<?php echo $_POST['lokasi_pasar'] ?? ''; ?>">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Foto KTP</label>
                                        <input type="file" name="foto_ktp" class="form-control" accept="image/*">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Foto Selfie + KTP</label>
                                        <input type="file" name="foto_selfie" class="form-control" accept="image/*">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="text-end">
                                <a href="index.php" class="btn btn-secondary me-2">Batal</a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save"></i> Simpan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
```

---

### 16.7 API ENDPOINTS

#### A. api/nasabah.php
```php
<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

require_once '../includes/functions.php';

// Get request method
$method = $_SERVER['REQUEST_METHOD'];

// Get request URI
$request = explode('/', trim($_SERVER['REQUEST_URI'], '/'));
$endpoint = $request[count($request) - 1];

// Authentication (simplified)
$headers = getallheaders();
$token = $headers['Authorization'] ?? '';
if (!$token) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

// Get user from token (simplified - implement proper JWT)
$user_id = 1; // Temporary
$cabang_id = getCurrentCabang();

switch ($method) {
    case 'GET':
        if ($endpoint === 'nasabah' || $endpoint === 'api.php') {
            // List nasabah
            $search = $_GET['search'] ?? '';
            $sql = "SELECT * FROM nasabah WHERE cabang_id = ?";
            $params = [$cabang_id];
            
            if ($search) {
                $sql .= " AND (nama LIKE ? OR kode_nasabah LIKE ? OR ktp LIKE ?)";
                $params[] = "%$search%";
                $params[] = "%$search%";
                $params[] = "%$search%";
            }
            
            $sql .= " ORDER BY created_at DESC";
            $nasabah = query($sql, $params);
            
            echo json_encode([
                'success' => true,
                'data' => $nasabah
            ]);
        }
        break;
        
    case 'POST':
        if ($endpoint === 'nasabah' || $endpoint === 'api.php') {
            // Add nasabah
            $input = json_decode(file_get_contents('php://input'), true);
            
            $nama = $input['nama'] ?? '';
            $alamat = $input['alamat'] ?? '';
            $ktp = $input['ktp'] ?? '';
            $telp = $input['telp'] ?? '';
            $jenis_usaha = $input['jenis_usaha'] ?? '';
            $lokasi_pasar = $input['lokasi_pasar'] ?? '';
            
            // Validation
            if (!validateKTP($ktp)) {
                http_response_code(400);
                echo json_encode(['error' => 'Format KTP tidak valid']);
                exit();
            }
            
            // Check duplicate
            $check = query("SELECT id FROM nasabah WHERE ktp = ?", [$ktp]);
            if ($check) {
                http_response_code(400);
                echo json_encode(['error' => 'KTP sudah terdaftar']);
                exit();
            }
            
            // Generate kode
            $kode_nasabah = generateKode('NSB', 'nasabah', 'kode_nasabah');
            
            // Insert
            $result = query("INSERT INTO nasabah (cabang_id, kode_nasabah, nama, alamat, ktp, telp, jenis_usaha, lokasi_pasar) VALUES (?, ?, ?, ?, ?, ?, ?, ?)", [
                $cabang_id, $kode_nasabah, $nama, $alamat, $ktp, $telp, $jenis_usaha, $lokasi_pasar
            ]);
            
            if ($result) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Nasabah berhasil ditambahkan',
                    'data' => ['kode_nasabah' => $kode_nasabah]
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Gagal menambahkan nasabah']);
            }
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
}
?>
```

---

### 16.8 DEPLOYMENT GUIDE

#### A. .htaccess
```apache
# Enable URL rewriting
RewriteEngine On

# Redirect to index.php
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]

# Security headers
Header always set X-Frame-Options DENY
Header always set X-Content-Type-Options nosniff
Header always set X-XSS-Protection "1; mode=block"

# Hide .htaccess
<Files .htaccess>
    Order allow,deny
    Deny from all
</Files>

# Protect config files
<Files "config/*">
    Order allow,deny
    Deny from all
</Files>
```

#### B. composer.json (Dependencies)
```json
{
    "name": "kewer/koperasi-digital",
    "description": "Sistem pinjaman modal pedagang digital",
    "require": {
        "php": ">=8.0",
        "ext-mysqli": "*",
        "ext-gd": "*",
        "phpmailer/phpmailer": "^6.8"
    },
    "autoload": {
        "psr-4": {
            "Kewer\\": "src/"
        }
    }
}
```

#### C. Installation Steps
```bash
# 1. Clone/download project files
# 2. Create database and import schema.sql
# 3. Update database credentials in config/database.php
# 4. Set permissions
chmod 755 -R .
chmod 777 uploads/

# 5. Install dependencies (if using composer)
composer install

# 6. Configure Apache/Nginx
# Enable mod_rewrite for Apache
# Set document root to project folder

# 7. Test login
# URL: http://localhost/kewer/login.php
# Default admin: admin / admin123
```

---

### 16.9 NEXT STEPS

Setelah boilerplate ini berjalan, Anda bisa:

1. **Customize UI/UX** sesuai brand Anda
2. **Add more features**:
   - WhatsApp integration
   - Payment gateway
   - Advanced reporting
   - Mobile app
3. **Enhance security**:
   - JWT authentication
   - Rate limiting
   - Input sanitization
4. **Optimize performance**:
   - Database indexing
   - Caching
   - Image optimization

---

**Dibuat**: April 2026  
**Tujuan**: Panduan digitalisasi usaha pinjaman modal pedagang (perorangan)  
**Platform**: PHP, Bootstrap, jQuery, AJAX, MySQL, API

---
