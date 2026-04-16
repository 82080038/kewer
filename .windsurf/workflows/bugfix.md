---
description: Workflow untuk memperbaiki error secara menyeluruh di aplikasi Kewer
---

# Workflow Perbaikan Error

## Prinsip Utama
1. **Periksa error serupa di seluruh aplikasi** - Ketika menemukan error, cari pola yang sama di file lain dan perbaiki secara konsisten
2. **Periksa dampak perbaikan** - Setelah memperbaiki error, periksa bagian lain yang mungkin terdampak oleh perbaikan tersebut

## Langkah-langkah

### 1. Identifikasi Error
- Analisis error yang dilaporkan
- Pahami root cause dan pola error
- Catat tipe error (array access, foreach, CORS, dll)

### 2. Cari Error Serupa
- Gunakan grep untuk mencari pola kode yang sama di seluruh aplikasi
- Fokus pada:
  - Pola query()[0] yang langsung mengakses array
  - Variabel yang digunakan dalam foreach tanpa pengecekan array
  - URL eksternal DataTables language
  - Error handling yang tidak lengkap
- Prioritaskan file dengan pola yang sama

### 3. Perbaiki Secara Menyeluruh
- Terapkan perbaikan yang konsisten di semua file yang terdampak
- Gunakan pola yang sama untuk semua perbaikan
- Tambahkan pengecekan array sebelum akses
- Tambahkan fallback values yang sesuai

### 4. Periksa Dampak Perbaikan
- Cari file lain yang menggunakan fungsi/variabel yang diperbaiki
- Periksa apakah perbaikan mempengaruhi fungsionalitas lain
- Pastikan tidak ada error baru yang muncul akibat perbaikan
- Test area yang terkait dengan perbaikan

### 5. Update Todo List
- Buat todo list untuk melacak semua perbaikan yang diperlukan
- Update status todo saat setiap perbaikan selesai
- Pastikan semua item dalam todo selesai sebelum menyelesaikan task

## Contoh Penerapan

### Error: Array offset pada query()[0]
1. Cari semua instance `query(...)[0]` dengan grep
2. Ganti dengan pola:
   ```php
   $result = query("SELECT ...", [$params]);
   $value = is_array($result) && isset($result[0]) ? $result[0] : ['default' => 0];
   ```

### Error: Foreach pada non-array
1. Cari variabel yang digunakan dalam foreach
2. Tambahkan pengecekan sebelum foreach:
   ```php
   if (!is_array($data)) {
       $data = [];
   }
   foreach ($data as $item) { ... }
   ```

### Error: CORS DataTables language
1. Cari semua external language URL
2. Ganti dengan inline Indonesian translations

## Catatan Penting
- Selalu gunakan pendekatan yang konsisten
- Dokumentasikan perubahan yang dilakukan
- Pastikan perbaikan tidak merusak fungsionalitas yang sudah ada
- Test secara menyeluruh setelah perbaikan besar
