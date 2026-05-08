# Development Plan - Kewer Application

> **Created**: 8 Mei 2026
> **Terakhir Diperbarui**: 8 Mei 2026 (Batch Implementation Selesai)
> **Versi Aplikasi**: v2.4.0
> **Status**: Prioritized development roadmap

> **Catatan**: Dokumen ini menggantikan ROADMAP.md dan menyediakan rencana pengembangan yang lebih detail dan actionable dengan task breakdown, timeline, resource requirements, dan success metrics.

## Executive Summary

Aplikasi Kewer saat ini berada di versi v2.4.0 dengan fitur-fitur inti sudah lengkap (multi-database, RBAC, feature flags, nasabah portal, penagihan system). Development plan ini berfokus pada penyelesaian fitur yang sudah ada sebagian (partial implementation) dan implementasi fitur prioritas tinggi dari ROADMAP.md.

## Current State Assessment

### ✅ Completed Features (v2.4.0)
- Multi-database architecture (kewer, db_alamat, db_orang) - 67 tables + 1 view
- Role-Based Access Control (RBAC) dengan 9 roles + appOwner + nasabah
- Feature Flags System (12 feature flags)
- Frekuensi Angsuran (migrasi ke frekuensi_id)
- Nasabah Portal (7 pages: dashboard, profil, pengajuan pinjaman/simpanan, etc.)
- Penagihan System dengan pengingat otomatis
- Cron Jobs untuk daily tasks
- Koperasi Isolation untuk multi-tenant support
- Province Activation untuk rollout
- Enhanced Reference Tables (ref_frekuensi_angsuran, ref_produk_pinjaman)
- Jaminan Management (pinjaman_jaminan)
- **NEW: Advanced Dashboard Analytics** (API endpoint, Chart.js integration, caching)
- **NEW: Credit Scoring System** (ScoringEngine, auto-approval, database columns)
- **NEW: GPS Tracking** (GPSTracker service, visits page, geofencing)
- **NEW: Audit Trail UI** (Audit log viewer, API endpoint, export)
- **NEW: Geographic Analysis** (Radius search, demographic analysis, heatmap, area classification)
- **NEW: Multi-branch Sync** (DataSyncService, sync logs, conflict detection)
- **NEW: Third-party API / Webhooks** (WebhookService, configuration page, triggers in pinjaman/pembayaran)

### 🔄 Partial Implementation (Need Completion)
1. **WA Notifikasi** - API exists (api/wa_notifikasi.php), feature flag ready, but not fully integrated
2. **Export/Import Data** - Basic export exists (api/export.php, src/Export/Exporter.php), need full Excel/CSV import
3. **Payment Gateway** - Basic pembayaran_elektronik.php exists, need full QRIS/VA/e-wallet integration

### ❌ Not Implemented (Planned in ROADMAP.md)
1. PWA Support
2. 2FA TOTP (feature flag exists but not fully implemented)
3. Advanced Reporting (PDF, scheduled reports)

---

## Prioritized Development Plan

### Phase 1: Complete Partial Implementations (1-2 months)

#### 1.1 WA Notifikasi Full Integration 📱
**Priority**: 🔴 HIGH
**Effort**: 2-3 weeks
**Dependencies**: None

**Tasks**:
- [ ] Integrate api/wa_notifikasi.php dengan penagihan system
- [ ] Implement pengingat jatuh tempo otomatis (H-3, H-1, hari H)
- [ ] Add notifikasi pembayaran berhasil
- [ ] Add notifikasi blacklist nasabah
- [ ] Add notifikasi approval pinjaman
- [ ] Implement queue system untuk rate limiting
- [ ] Activate feature flag `wa_notifikasi` dan `wa_pengingat_auto`
- [ ] Test dengan real WA gateway (Fonnte)
- [ ] Update documentation

**Database Changes**:
- `kewer.notification_logs` - Log semua notifikasi (baru)
- `kewer.nasabah` - Pastikan no WA terisi

**Files to Create/Modify**:
- `src/Notification/WhatsAppService.php` - Service class
- `api/notifications.php` - Notification API endpoint
- Update `api/penagihan.php` - Integrate WA notifikasi
- Update `api/pengingat_penagihan.php` - Use WA service

---

#### 1.2 Export/Import Data Full Implementation 📥📤
**Priority**: 🔴 HIGH
**Effort**: 2-3 weeks
**Dependencies**: None

**Tasks**:
- [ ] Install PhpSpreadsheet via Composer
- [ ] Implement Excel export untuk:
  - [ ] Data nasabah
  - [ ] Data pinjaman
  - [ ] Laporan angsuran
  - [ ] Laporan pembayaran
- [ ] Implement CSV import untuk bulk:
  - [ ] Nasabah baru
  - [ ] Pinjaman baru
- [ ] Template validation dan error reporting
- [ ] Progress bar untuk import besar
- [ ] Add export buttons di semua halaman CRUD
- [ ] Activate feature flag `export_laporan`
- [ ] Test dengan large datasets

**Database Changes**:
- None (semua tabel sudah ada)

**Files to Create/Modify**:
- `src/Export/ExcelExporter.php` - Excel export service
- `src/Import/CsvImporter.php` - CSV import service
- Update `api/export.php` - Add Excel export
- Create `api/import.php` - CSV import API
- Update semua halaman CRUD - Add export buttons

---

#### 1.3 Advanced Dashboard Analytics 📊 ✅ COMPLETED
**Priority**: 🔴 HIGH
**Effort**: 3-4 weeks
**Dependencies**: None

**Tasks**:
- [x] Integrasikan Chart.js atau D3.js
- [x] Implement real-time metrics:
  - [x] Total pinjaman aktif
  - [x] Collection rate (persentase pembayaran tepat waktu)
  - [x] NPL ratio (Non-Performing Loan)
  - [x] Total angsuran bulan ini
  - [x] Per-cabang performance comparison
  - [x] Trend analysis (mingguan/bulanan/tahunan)
  - [x] Top 10 nasabah tertinggi/terendah
- [x] Implement caching untuk performance (Redis atau file cache)
- [x] Update dashboard.php dengan widgets baru
- [x] Create API endpoint: api/dashboard_analytics.php

**Database Changes**:
- [x] `kewer.dashboard_cache` - Untuk caching data (baru)

**Files to Create/Modify**:
- [x] `api/dashboard_analytics.php` - Analytics API
- [x] Update `pages/dashboard.php` - Add charts
- [ ] Update `pages/app_owner/dashboard.php` - Add platform-level analytics
- [x] `src/Cache/CacheManager.php` - Cache service

---

### Phase 2: Medium Priority Features (2-3 months)

#### 2.1 Credit Scoring System 🤖 ✅ COMPLETED
**Priority**: 🟡 MEDIUM
**Effort**: 4-6 weeks
**Dependencies**: Phase 1 complete

**Tasks**:
- [x] Collect historical data untuk training
- [x] Implement simple scoring model (rule-based first):
  - [x] Riwayat pembayaran (weight: 40%)
  - [x] Demografi (usia, pekerjaan, pendapatan) (weight: 20%)
  - [x] Lokasi (area classification) (weight: 15%)
  - [x] Relasi keluarga (family risk) (weight: 15%)
  - [x] Durasi menjadi nasabah (weight: 10%)
- [x] Auto-approval untuk low-risk nasabah
- [x] Risk score (0-100) untuk setiap nasabah
- [x] Update form pengajuan pinjaman dengan risk indicator
- [ ] Evolution ke ML model (scikit-learn atau PHP-ML)

**Database Changes**:
- [x] `kewer.nasabah` - Tambah kolom credit_score, risk_level
- [x] `kewer.pinjaman` - Tambah kolom auto_approved, approval_reason
- [x] `kewer.credit_scoring_logs` - Audit trail (baru)

**Files to Create/Modify**:
- [x] `src/CreditScoring/ScoringEngine.php` - Scoring logic
- [x] `api/credit_scoring.php` - Scoring API
- [ ] Update `pages/pinjaman/tambah.php` - Show risk indicator
- [ ] Update `pages/pinjaman/proses.php` - Auto-approval logic

---

#### 2.2 Payment Gateway Integration 💳
**Priority**: 🟡 MEDIUM
**Effort**: 3-4 weeks
**Dependencies**: None

**Tasks**:
- [ ] Pilih payment gateway (Midtrans, Xendit, atau lainnya)
- [ ] Implement QRIS (Quick Response Code Indonesian Standard)
- [ ] Implement Virtual Account (VA)
- [ ] Implement e-wallet (GoPay, OVO, Dana, ShopeePay)
- [ ] Auto-reconciliation pembayaran
- [ ] Webhook handlers untuk payment notifications
- [ ] Payment instruction page untuk nasabah

**Database Changes**:
- `kewer.pembayaran` - Tambah kolom payment_method, payment_gateway, transaction_id
- `kewer.payment_logs` - Log semua transaksi payment gateway (baru)
- `kewer.payment_reconciliation` - Untuk auto-reconciliation (baru)

**Files to Create/Modify**:
- `src/Payment/PaymentGatewayService.php` - Payment service
- `api/webhook_payment.php` - Webhook handler
- Update `api/pembayaran_elektronik.php` - Full implementation
- Create `pages/nasabah/payment_instruction.php` - Payment instructions

---

#### 2.3 GPS Tracking for Field Officers 📍 ✅ COMPLETED
**Priority**: 🟡 MEDIUM
**Effort**: 3-4 weeks
**Dependencies**: None

**Tasks**:
- [x] Implement GPS tracking saat pembayaran lapangan
- [x] Geofencing untuk validasi lokasi
- [ ] Route optimization untuk petugas
- [x] Visit logging dengan foto
- [x] Dashboard kunjungan petugas
- [ ] Activate feature flag `gps_pembayaran`

**Database Changes**:
- [x] `kewer.visits` - Log kunjungan petugas dengan GPS (baru)
- [x] `kewer.mobile_devices` - Register device (baru)
- [x] `kewer.pembayaran` - latitude, longitude, gps_accuracy, captured_at (baru)
- [x] `kewer.cabang` - latitude, longitude, geofence_radius (baru)

**Files to Create/Modify**:
- [x] `src/Geo/GPSTracker.php` - GPS tracking service
- [x] Update `api/pembayaran.php` - Add GPS capture
- [x] Create `pages/petugas/kunjungan.php` - Visit log
- [x] Create `api/visits.php` - Visits API
- [ ] Update `pages/rute_harian/index.php` - Show GPS data

---

#### 2.4 SMS Gateway 📩
**Priority**: 🟡 MEDIUM
**Effort**: 2-3 weeks
**Dependencies**: WA Notifikasi complete (reuse queue system)

**Tasks**:
- [ ] Notifikasi SMS untuk nasabah tanpa WhatsApp
- [ ] OTP untuk authentication (opsional)
- [ ] Notifikasi penting (blacklist, approval besar)
- [ ] Pilih SMS gateway (Twilio, Nexmo, atau lokal)
- [ ] Reuse notification queue system dari WhatsApp

**Database Changes**:
- `kewer.nasabah` - Tambah kolom no_hp, notification_preference
- `kewer.notification_logs` - Reuse dari WhatsApp

**Files to Create/Modify**:
- `src/Notification/SmsService.php` - SMS service
- Update notification preference di nasabah

---

#### 2.5 Audit Trail UI 🔍 ✅ COMPLETED
**Priority**: 🟡 MEDIUM
**Effort**: 2-3 weeks
**Dependencies**: None

**Tasks**:
- [x] Create UI untuk audit_log table
- [x] Filterable log viewer (by user, action, table, date range)
- [x] Export audit log
- [x] Search functionality
- [ ] Rollback capability (opsional)

**Database Changes**:
- None (audit_log table sudah ada)

**Files to Create/Modify**:
- [x] Create `pages/audit/index.php` - Audit log viewer
- [x] Create `api/audit_log.php` - Audit log API

---

### Phase 3: Low Priority Features (3-4 months)

#### 3.1 PWA Support 📲
**Priority**: 🟢 LOW
**Effort**: 4-5 weeks
**Dependencies**: None

**Tasks**:
- [ ] Create service worker
- [ ] Create manifest file
- [ ] Implement IndexedDB untuk offline storage
- [ ] Offline-first architecture dengan sync queue
- [ ] Background sync saat koneksi tersedia
- [ ] Activate feature flag `pwa`

**Database Changes**:
- `kewer.sync_queue` - Queue untuk offline sync (baru)

**Files to Create/Modify**:
- Create `service-worker.js` - Service worker
- Create `manifest.json` - PWA manifest
- Create `src/PWA/SyncManager.php` - Sync service
- Update semua pages - Add PWA support

---

#### 3.2 2FA TOTP Full Implementation 🔐
**Priority**: 🟢 LOW
**Effort**: 2-3 weeks
**Dependencies**: None

**Tasks**:
- [ ] Implement TOTP generation
- [ ] QR code untuk setup
- [ ] Verification process
- [ ] Backup codes
- [ ] Activate feature flag `two_factor_auth`

**Database Changes**:
- `kewer.users` - TOTP fields sudah ada (totp_secret, totp_enabled, totp_verified_at, phone_2fa)

**Files to Create/Modify**:
- Update `pages/users/settings_2fa.php` - Full implementation
- Update `api/auth_2fa.php` - Full verification
- `src/Auth/TOTPService.php` - TOTP service

---

#### 3.3 Advanced Reporting 📈
**Priority**: 🟢 LOW
**Effort**: 4-5 weeks
**Dependencies**: Phase 1.3 complete

**Tasks**:
- [ ] Install TCPDF atau DomPDF
- [ ] PDF generation dengan header/footer
- [ ] Scheduled reports (daily/weekly/monthly)
- [ ] Email reports otomatis
- [ ] Custom report builder
- [ ] Drill-down reports

**Database Changes**:
- `kewer.scheduled_reports` - Konfigurasi report (baru)
- `kewer.report_history` - Log report yang sudah di-generate (baru)
- `kewer.email_queue` - Queue untuk email reports (baru)

**Files to Create/Modify**:
- `src/Report/PdfGenerator.php` - PDF generation
- `src/Report/ReportScheduler.php` - Report scheduler
- Create `pages/laporan/builder.php` - Report builder
- Create `api/reports.php` - Reports API

---

### Phase 4: Future Considerations (6+ months)

#### 4.1 Geographic Analysis 🗺️ ✅ COMPLETED
**Priority**: 🔵 FUTURE
**Effort**: 6-8 weeks
**Dependencies**: GPS tracking complete

**Tasks**:
- [x] Radius-based nasabah search (cari nasabah dalam radius X km dari cabang)
- [x] Demographic analysis per area (pendapatan, kepadatan, klasifikasi urban/rural)
- [x] Risk scoring berdasarkan lokasi (area high-risk vs low-risk)
- [x] Heatmap nasabah per kecamatan/desa
- [x] Haversine formula untuk distance calculation

**Database Changes**:
- None (latitude/longitude columns already exist)

**Files to Create/Modify**:
- [x] `api/geographic_analysis.php` - Geographic analysis API
- [ ] Update halaman nasabah - Show location on map

---

#### 4.2 Mobile App / PWA (Native) 📱
**Priority**: 🔵 FUTURE
**Effort**: 12-16 weeks
**Dependencies**: PWA support complete

**Tasks**:
- [ ] Native Android/iOS app (React Native atau Flutter)
- [ ] API authentication dengan JWT
- [ ] Offline-first architecture dengan sync queue
- [ ] Push notifications
- [ ] Camera integration untuk dokumentasi

**Database Changes**:
- `kewer.mobile_devices` - Register device
- `kewer.sync_queue` - Queue untuk offline sync
- `kewer.visits` - Log kunjungan petugas dengan GPS

---

#### 4.3 Cloud Deployment ☁️
**Priority**: 🔵 FUTURE
**Effort**: 8-10 weeks
**Dependencies**: All features complete

**Tasks**:
- [ ] Containerization dengan Docker
- [ ] CI/CD pipeline (GitHub Actions/GitLab CI)
- [ ] Auto-scaling untuk high traffic
- [ ] Load balancing
- [ ] Database clustering

**Database Changes**:
- Tidak ada perubahan schema
- Perlu environment variables untuk cloud config

---

#### 4.4 Multi-branch Synchronization 🔄 ✅ COMPLETED
**Priority**: 🔵 FUTURE
**Effort**: 6-8 weeks
**Dependencies**: Cloud deployment complete

**Tasks**:
- [x] Real-time sync antar cabang (jika multi-database)
- [x] Conflict resolution untuk data yang sama
- [ ] Offline-first architecture
- [ ] Centralized dashboard untuk semua cabang
- [ ] Implementasi event sourcing atau change data capture

**Database Changes**:
- [x] `kewer.sync_logs` - Log sync operations (baru)
- [x] `kewer.sync_conflicts` - Conflict resolution (baru)
- [ ] Mungkin perlu database per-cabang

**Files to Create/Modify**:
- [x] `src/Sync/DataSyncService.php` - Sync service
- [ ] Monitoring dashboard untuk sync status

---

#### 4.5 Third-party API Integration 🔌 ✅ COMPLETED
**Priority**: 🔵 FUTURE
**Effort**: 4-6 weeks
**Dependencies**: Cloud deployment complete

**Tasks**:
- [ ] BI checking (SLIK OJK, Indonesia Credit Bureau)
- [ ] Integration dengan sistem eksternal
- [ ] Open API untuk partner
- [x] Webhook system untuk event notifications
- [x] API client class untuk external services
- [x] Rate limiting dan caching
- [ ] OpenAPI documentation (Swagger)
- [x] API key management

**Database Changes**:
- [x] `kewer.external_api_logs` - Log external API calls (baru)
- [x] `kewer.api_keys` - Management untuk partner API (baru)
- [x] `kewer.webhooks` - Konfigurasi webhook (baru)
- [x] `kewer.webhook_logs` - Log webhook deliveries (baru)

**Files to Create/Modify**:
- [x] API client classes untuk external services
- [x] Webhook system implementation

---

## Implementation Timeline

### Q3 2026 (Juli - September)
- **July**: WA Notifikasi Full Integration
- **August**: Export/Import Data
- **September**: Advanced Dashboard Analytics

### Q4 2026 (Oktober - Desember)
- **October**: Credit Scoring System
- **November**: Payment Gateway Integration
- **December**: GPS Tracking + SMS Gateway

### Q1 2027 (Januari - Maret)
- **January**: Audit Trail UI + PWA Support
- **February**: 2FA TOTP
- **March**: Advanced Reporting

### Q2 2027 (April - Juni)
- **April**: Geographic Analysis
- **May**: Multi-branch Synchronization
- **June**: Third-party API Integration + Cloud Deployment

---

## Resource Requirements

### Development Team
- **Senior PHP Developer**: 1 FTE (backend logic, database)
- **Frontend Developer**: 1 FTE (UI/UX, JavaScript, charts)
- **DevOps Engineer**: 0.5 FTE (deployment, CI/CD, cloud)

### Tools & Services
- **WA Gateway**: Fonnte (Rp500k/month) atau Twilio
- **Payment Gateway**: Midtrans/Xendit (transaction fees)
- **Email Service**: SendGrid/Mailgun (untuk reports)
- **Cloud Provider**: AWS/GCP/Azure (jika cloud deployment)
- **Monitoring**: Sentry (error tracking), New Relic (APM)

### Budget Estimate
- **Q3 2026**: Rp15-20 juta (WA gateway, development tools)
- **Q4 2026**: Rp20-25 juta (payment gateway setup, GPS services)
- **Q1 2027**: Rp10-15 juta (PWA testing, email service)
- **Q2 2027**: Rp30-40 juta (cloud deployment, geographic data services)

---

## Risk Mitigation

### Technical Risks
1. **WA Gateway Rate Limiting** - Implement queue system, use multiple gateways
2. **Payment Gateway Integration Complexity** - Start with simple QRIS, expand gradually
3. **GPS Accuracy Issues** - Add geofencing tolerance, manual override option
4. **Performance with Large Datasets** - Implement caching, pagination, lazy loading

### Business Risks
1. **User Adoption** - Provide training, documentation, gradual rollout
2. **Cost Overruns** - Prioritize high-value features, use open-source alternatives
3. **Data Security** - Regular security audits, penetration testing

---

## Success Metrics

### Phase 1 Success Criteria
- WA notifikasi sent untuk 90% penagihan jatuh tempo
- Export/import data < 30 detik untuk 10,000 records
- Dashboard load time < 3 detik dengan real-time charts

### Phase 2 Success Criteria
- Credit scoring accuracy > 80% (compared to manual approval)
- Payment gateway success rate > 95%
- GPS tracking accuracy < 50 meter radius
- Audit log searchable < 5 detik

### Phase 3 Success Criteria
- PWA offline mode works untuk 24+ hours
- 2FA adoption rate > 70% untuk admin users
- PDF report generation < 10 detik
- Scheduled reports delivered 99% on time

---

## Cara Menggunakan Development Plan Ini

### Untuk Developer Baru
1. Baca file ini untuk memahami arah pengembangan aplikasi
2. Diskusikan dengan tim prioritas mana yang paling urgent
3. Pilih satu fitur untuk diimplementasi
4. Ikuti workflow di `.windsurf/workflows/` sesuai jenis task
5. Update DEVELOPMENT_PLAN.md ini setelah fitur selesai (tandai dengan ✅)

### Untuk Product Owner / Manager
1. Review prioritas dan sesuaikan dengan business needs
2. Tentukan timeline berdasarkan resource yang tersedia
3. Assign task ke developer
4. Monitor progress dengan checklist di file ini

### Untuk Maintainer
1. Update status fitur (🔄 In Progress, ✅ Done, ⏸️ On Hold)
2. Tambahkan catatan jika ada perubahan prioritas
3. Update tanggal terakhir diperbarui
4. Archive fitur yang sudah tidak relevan

---

## Status Legend

- 🆕 **New** - Fitur belum mulai
- 🔄 **In Progress** - Sedang dikembangkan
- ✅ **Done** - Sudah selesai
- ⏸️ **On Hold** - Ditunda sementara
- ❌ **Cancelled** - Tidak jadi dikembangkan

---

## Catatan Penting

1. **Feature Flags**: Semua fitur baru harus menggunakan feature flags system (lihat `.windsurf/rules.md`)
2. **Testing**: Setiap fitur harus di-test sesuai workflow `.windsurf/workflows/testing.md`
3. **Documentation**: Update documentation terkait setelah fitur selesai
4. **Database Changes**: Ikuti workflow `.windsurf/workflows/database.md` untuk perubahan database
5. **Security**: Ikuti workflow `.windsurf/workflows/security.md` untuk security considerations

---

## Referensi Terkait

- **Development Rules**: `.windsurf/rules.md`
- **Application Analysis**: `.windsurf/analysis.md`
- **Developer Setup**: `DEVELOPMENT.md`
- **Workflows**: `.windsurf/workflows/`
- **Database Schema**: `database/` folder

---

## Next Steps

1. **Immediate (This Week)**:
   - Review and approve this development plan
   - Assign development team
   - Set up development environment for Phase 1

2. **Short-term (Next Month)**:
   - Start WA Notifikasi Full Integration
   - Set up WA gateway account
   - Create notification service architecture

3. **Medium-term (Next Quarter)**:
   - Complete Phase 1 features
   - User acceptance testing
   - Production deployment

---

*This development plan will be updated quarterly based on progress and changing priorities.*
