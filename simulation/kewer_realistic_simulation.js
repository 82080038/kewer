/**
 * ============================================================
 * KEWER REALISTIC 3-MONTH SIMULATION - ALL ROLES
 * ============================================================
 * 
 * Simulasi realistis kehidupan nyata 3 bulan (90 hari) untuk
 * aplikasi koperasi pinjaman Kewer, mencakup SEMUA role:
 * 
 * HIERARKI ROLE:
 * 1. superadmin  (patri)          - Kelola sistem, permissions
 * 2. bos         (bos_simulasi)   - Pantau semua cabang, laporan
 * 3. manager_pusat (manager_pusat_sim) - Operasional lintas cabang
 * 4. manager_cabang (manager_cabang1) - Operasional 1 cabang
 * 5. admin_pusat  (admin_pusat_sim) - Admin pusat, data nasabah
 * 6. admin_cabang (admin_cabang_sim) - Admin cabang harian
 * 7. petugas      (petugas1_sim)  - Lapangan, kumpul angsuran
 * 8. karyawan     (karyawan1)     - Admin support, rekonsiliasi
 * 
 * AKTIVITAS NYATA PER ROLE:
 * - Superadmin: audit log, manage users, system check
 * - Bos: dashboard semua cabang, laporan keuangan, approval
 * - Manager Pusat: monitor cabang, approve pinjaman besar, laporan
 * - Manager Cabang: review pinjaman, approve, monitor petugas
 * - Admin Pusat: tambah nasabah, input pinjaman, manage data
 * - Admin Cabang: input angsuran, kelola nasabah cabang
 * - Petugas: aktivitas lapangan, kumpul angsuran, kas petugas
 * - Karyawan: rekonsiliasi kas, input data, support admin
 */

const puppeteer = require('puppeteer');
const fs = require('fs');
const path = require('path');

// ============================================================
// KONFIGURASI
// ============================================================
const config = {
  baseUrl: 'http://localhost/kewer',
  simulationDays: 90,
  dayDuration: 3000,      // 3 detik per hari simulasi
  headless: false,
  slowMo: 30,
  screenshotsDir: path.join(__dirname, 'screenshots'),
  logsDir: path.join(__dirname, 'logs'),
};

// ============================================================
// CREDENTIALS SEMUA ROLE
// ============================================================
const USERS = {
  superadmin:      { username: 'patri',             password: 'password',     role: 'superadmin',     label: 'Superadmin (Patri)' },
  bos:             { username: 'bos_simulasi',       password: 'password123',  role: 'bos',            label: 'Bos (Bos Simulasi)' },
  manager_pusat:   { username: 'manager_pusat_sim',  password: 'password123',  role: 'manager_pusat',  label: 'Manager Pusat' },
  manager_cabang:  { username: 'manager_cabang1',    password: 'password123',  role: 'manager_cabang', label: 'Manager Cabang' },
  admin_pusat:     { username: 'admin_pusat_sim',    password: 'password',     role: 'admin_pusat',    label: 'Admin Pusat' },
  admin_cabang:    { username: 'admin_cabang_sim',   password: 'password',     role: 'admin_cabang',   label: 'Admin Cabang' },
  petugas:         { username: 'petugas1_sim',       password: 'password123',  role: 'petugas',        label: 'Petugas Lapangan' },
  karyawan:        { username: 'karyawan1',          password: 'password123',  role: 'karyawan',       label: 'Karyawan' },
};

// ============================================================
// STATE SIMULASI
// ============================================================
const state = {
  currentDay: 0,
  currentDate: new Date(),
  nasabahIds: [],
  pinjamanIds: [],
  totalActivities: 0,
  totalErrors: 0,
  roleStats: {},
};

// Init role stats
Object.keys(USERS).forEach(r => {
  state.roleStats[r] = { loginSuccess: 0, loginFail: 0, activities: 0, errors: 0 };
});

// ============================================================
// SETUP DIREKTORI
// ============================================================
[config.screenshotsDir, config.logsDir].forEach(dir => {
  if (!fs.existsSync(dir)) fs.mkdirSync(dir, { recursive: true });
});

const logFile = path.join(config.logsDir, `sim_realworld_${new Date().toISOString().split('T')[0]}.log`);

// ============================================================
// LOGGING
// ============================================================
function log(msg, type = 'INFO') {
  const ts = new Date().toISOString();
  const line = `[${ts}] [${type.padEnd(8)}] ${msg}`;
  console.log(line);
  fs.appendFileSync(logFile, line + '\n');
}

function logRole(role, activity, detail = '') {
  const user = USERS[role];
  state.roleStats[role].activities++;
  state.totalActivities++;
  log(`[Day ${state.currentDay}] ${user.label} → ${activity}${detail ? ' | ' + detail : ''}`, 'ACTIVITY');
}

function logError(role, msg, err = null) {
  state.roleStats[role].errors++;
  state.totalErrors++;
  log(`[Day ${state.currentDay}] ERROR [${USERS[role].label}] ${msg}${err ? ': ' + err.message : ''}`, 'ERROR');
}

function delay(ms) {
  return new Promise(r => setTimeout(r, ms));
}

// ============================================================
// LOGIN FUNCTION
// ============================================================
async function loginAs(browser, roleKey) {
  const user = USERS[roleKey];
  const page = await browser.newPage();

  try {
    const url = `${config.baseUrl}/login.php?test_login=true&username=${user.username}&password=${user.password}`;
    await page.goto(url, { waitUntil: 'networkidle2', timeout: 20000 });
    await delay(800);

    const currentUrl = page.url();
    if (currentUrl.includes('dashboard.php') || currentUrl.endsWith('/kewer/')) {
      state.roleStats[roleKey].loginSuccess++;
      log(`[Day ${state.currentDay}] LOGIN OK: ${user.label} (${user.username})`, 'LOGIN');
      return page;
    }

    // Jika belum redirect, coba navigasi manual ke dashboard
    await page.goto(`${config.baseUrl}/dashboard.php`, { waitUntil: 'networkidle2', timeout: 15000 });
    await delay(500);
    const afterUrl = page.url();
    if (afterUrl.includes('dashboard.php')) {
      state.roleStats[roleKey].loginSuccess++;
      log(`[Day ${state.currentDay}] LOGIN OK (redirect manual): ${user.label}`, 'LOGIN');
      return page;
    }

    state.roleStats[roleKey].loginFail++;
    log(`[Day ${state.currentDay}] LOGIN FAIL: ${user.label} → URL: ${afterUrl}`, 'ERROR');
    await page.close();
    return null;
  } catch (err) {
    state.roleStats[roleKey].loginFail++;
    logError(roleKey, 'Login exception', err);
    try { await page.close(); } catch {}
    return null;
  }
}

// ============================================================
// HELPER: navigasi aman + screenshot pada error
// ============================================================
async function safeGoto(page, url, roleKey, desc) {
  try {
    await page.goto(`${config.baseUrl}${url}`, { waitUntil: 'networkidle2', timeout: 15000 });
    await delay(400);
    return true;
  } catch (err) {
    logError(roleKey, `Gagal navigasi ke ${url} (${desc})`, err);
    return false;
  }
}

async function screenshot(page, name) {
  try {
    const file = path.join(config.screenshotsDir, `${name}_${Date.now()}.png`);
    await page.screenshot({ path: file, fullPage: false });
  } catch {}
}

// ============================================================
// AKTIVITAS PER ROLE
// ============================================================

// --- SUPERADMIN: Kelola sistem, audit, permissions ---
async function runSuperadmin(browser, dayNum) {
  const page = await loginAs(browser, 'superadmin');
  if (!page) return;
  try {
    // Dashboard
    logRole('superadmin', 'Review dashboard sistem');
    await screenshot(page, `superadmin_dashboard_day${dayNum}`);

    // Audit log
    if (await safeGoto(page, '/pages/audit/index.php', 'superadmin', 'Audit Log')) {
      logRole('superadmin', 'Cek audit trail aktivitas sistem');
    }

    // Kelola permissions (setiap 7 hari)
    if (dayNum % 7 === 1) {
      if (await safeGoto(page, '/pages/permissions/index.php', 'superadmin', 'Permissions')) {
        logRole('superadmin', 'Review & kelola permissions user');
      }
    }

    // Kelola users (setiap 14 hari)
    if (dayNum % 14 === 0) {
      if (await safeGoto(page, '/pages/users/index.php', 'superadmin', 'Users')) {
        logRole('superadmin', 'Review daftar user sistem');
      }
    }

    // Auto confirm settings (setiap 30 hari)
    if (dayNum % 30 === 1) {
      if (await safeGoto(page, '/pages/auto_confirm/index.php', 'superadmin', 'Auto Confirm')) {
        logRole('superadmin', 'Cek & update setting auto-confirm pinjaman');
      }
    }

  } finally {
    await page.close();
  }
}

// --- BOS: Pantau bisnis, laporan, approval ---
async function runBos(browser, dayNum) {
  const page = await loginAs(browser, 'bos');
  if (!page) return;
  try {
    logRole('bos', 'Review dashboard konsolidasi semua cabang');
    await screenshot(page, `bos_dashboard_day${dayNum}`);

    // Laporan keuangan (harian)
    if (await safeGoto(page, '/pages/laporan/index.php', 'bos', 'Laporan')) {
      logRole('bos', 'Baca laporan keuangan harian');
    }

    // Monitor nasabah
    if (await safeGoto(page, '/pages/nasabah/index.php', 'bos', 'Nasabah')) {
      logRole('bos', 'Pantau total & pertumbuhan nasabah');
    }

    // Review pinjaman aktif
    if (await safeGoto(page, '/pages/pinjaman/index.php', 'bos', 'Pinjaman')) {
      logRole('bos', 'Pantau portofolio pinjaman semua cabang');
    }

    // Family risk analysis (setiap minggu)
    if (dayNum % 7 === 0) {
      if (await safeGoto(page, '/pages/family_risk/index.php', 'bos', 'Family Risk')) {
        logRole('bos', 'Analisis risiko keluarga nasabah');
      }
    }

    // Setting bunga (setiap bulan)
    if (dayNum % 30 === 2) {
      if (await safeGoto(page, '/pages/setting_bunga/index.php', 'bos', 'Setting Bunga')) {
        logRole('bos', 'Review & evaluasi setting bunga pinjaman');
      }
    }

    // Pengeluaran (review setiap minggu)
    if (dayNum % 7 === 3) {
      if (await safeGoto(page, '/pages/pengeluaran/index.php', 'bos', 'Pengeluaran')) {
        logRole('bos', 'Review pengeluaran operasional');
      }
    }

    // Rekonsiliasi kas (approval, setiap 3 hari)
    if (dayNum % 3 === 0) {
      if (await safeGoto(page, '/pages/cash_reconciliation/index.php', 'bos', 'Rekonsiliasi')) {
        logRole('bos', 'Review rekonsiliasi kas cabang');
      }
    }

    await screenshot(page, `bos_end_day${dayNum}`);
  } finally {
    await page.close();
  }
}

// --- MANAGER PUSAT: Operasional pusat, approve pinjaman besar ---
async function runManagerPusat(browser, dayNum) {
  const page = await loginAs(browser, 'manager_pusat');
  if (!page) return;
  try {
    logRole('manager_pusat', 'Review dashboard operasional pusat');

    // Pantau pinjaman
    if (await safeGoto(page, '/pages/pinjaman/index.php', 'manager_pusat', 'Pinjaman')) {
      logRole('manager_pusat', 'Monitor pinjaman & approval status lintas cabang');
    }

    // Monitor angsuran
    if (await safeGoto(page, '/pages/angsuran/index.php', 'manager_pusat', 'Angsuran')) {
      logRole('manager_pusat', 'Cek status pembayaran angsuran semua cabang');
    }

    // Kas petugas approval (setiap 2 hari)
    if (dayNum % 2 === 0) {
      if (await safeGoto(page, '/pages/kas_petugas/index.php', 'manager_pusat', 'Kas Petugas')) {
        logRole('manager_pusat', 'Approve/review setoran kas petugas');
      }
    }

    // Laporan (setiap minggu)
    if (dayNum % 7 === 2) {
      if (await safeGoto(page, '/pages/laporan/index.php', 'manager_pusat', 'Laporan')) {
        logRole('manager_pusat', 'Buat laporan kinerja mingguan lintas cabang');
      }
    }

    // Manage users (setiap 2 minggu)
    if (dayNum % 14 === 3) {
      if (await safeGoto(page, '/pages/users/index.php', 'manager_pusat', 'Users')) {
        logRole('manager_pusat', 'Kelola user & staf operasional');
      }
    }

    // Aktivitas lapangan monitor
    if (await safeGoto(page, '/pages/field_activities/index.php', 'manager_pusat', 'Field Activities')) {
      logRole('manager_pusat', 'Monitor aktivitas lapangan seluruh petugas');
    }

  } finally {
    await page.close();
  }
}

// --- MANAGER CABANG: Operasional harian cabang ---
async function runManagerCabang(browser, dayNum) {
  const page = await loginAs(browser, 'manager_cabang');
  if (!page) return;
  try {
    logRole('manager_cabang', 'Buka dashboard cabang & review target harian');

    // Review nasabah cabang
    if (await safeGoto(page, '/pages/nasabah/index.php', 'manager_cabang', 'Nasabah')) {
      logRole('manager_cabang', 'Cek daftar & status nasabah cabang');
    }

    // Review & approve pinjaman
    if (await safeGoto(page, '/pages/pinjaman/index.php', 'manager_cabang', 'Pinjaman')) {
      logRole('manager_cabang', 'Review pinjaman pending & proses approval');
    }

    // Monitor angsuran harian
    if (await safeGoto(page, '/pages/angsuran/index.php', 'manager_cabang', 'Angsuran')) {
      logRole('manager_cabang', 'Monitor pembayaran angsuran hari ini');
    }

    // Aktivitas lapangan (setiap hari)
    if (await safeGoto(page, '/pages/field_activities/index.php', 'manager_cabang', 'Field')) {
      logRole('manager_cabang', 'Review laporan aktivitas petugas lapangan');
    }

    // Rekonsiliasi kas (setiap hari)
    if (await safeGoto(page, '/pages/cash_reconciliation/index.php', 'manager_cabang', 'Rekonsiliasi')) {
      logRole('manager_cabang', 'Cek rekonsiliasi kas harian cabang');
    }

    // Laporan kinerja (setiap minggu)
    if (dayNum % 7 === 4) {
      if (await safeGoto(page, '/pages/laporan/index.php', 'manager_cabang', 'Laporan')) {
        logRole('manager_cabang', 'Buat laporan kinerja mingguan cabang');
      }
    }

    // Kas bon (setiap 5 hari)
    if (dayNum % 5 === 0) {
      if (await safeGoto(page, '/pages/kas_bon/index.php', 'manager_cabang', 'Kas Bon')) {
        logRole('manager_cabang', 'Kelola kas bon operasional cabang');
      }
    }

  } finally {
    await page.close();
  }
}

// --- ADMIN PUSAT: Tambah nasabah, input pinjaman, manage data ---
async function runAdminPusat(browser, dayNum) {
  const page = await loginAs(browser, 'admin_pusat');
  if (!page) return;
  try {
    logRole('admin_pusat', 'Buka dashboard & cek tugas admin hari ini');

    // Kelola nasabah (setiap hari)
    if (await safeGoto(page, '/pages/nasabah/index.php', 'admin_pusat', 'Nasabah')) {
      logRole('admin_pusat', 'Review & update data nasabah pusat');
    }

    // Input pinjaman baru (setiap 3 hari simulasi - ada nasabah baru apply)
    if (dayNum % 3 === 1) {
      if (await safeGoto(page, '/pages/pinjaman/tambah.php', 'admin_pusat', 'Tambah Pinjaman')) {
        logRole('admin_pusat', 'Input pengajuan pinjaman baru dari nasabah');
        // Isi form pinjaman dengan data dummy
        try {
          await page.waitForSelector('select[name="nasabah_id"], input[name="nasabah_id"]', { timeout: 5000 });
          logRole('admin_pusat', 'Form tambah pinjaman berhasil diakses');
        } catch {
          logRole('admin_pusat', 'Form pinjaman - halaman terbuka');
        }
      }
    }

    // Review pinjaman yang ada
    if (await safeGoto(page, '/pages/pinjaman/index.php', 'admin_pusat', 'Pinjaman')) {
      logRole('admin_pusat', 'Review status pinjaman & dokumen');
    }

    // Kelola jaminan (setiap 2 hari)
    if (dayNum % 2 === 0) {
      if (await safeGoto(page, '/pages/jaminan/index.php', 'admin_pusat', 'Jaminan')) {
        logRole('admin_pusat', 'Update status jaminan nasabah');
      }
    }

    // Data petugas (setiap minggu)
    if (dayNum % 7 === 5) {
      if (await safeGoto(page, '/pages/petugas/index.php', 'admin_pusat', 'Petugas')) {
        logRole('admin_pusat', 'Update data & rute petugas');
      }
    }

    // Rute harian
    if (dayNum % 3 === 0) {
      if (await safeGoto(page, '/pages/rute_harian/index.php', 'admin_pusat', 'Rute Harian')) {
        logRole('admin_pusat', 'Atur rute kunjungan harian petugas');
      }
    }

  } finally {
    await page.close();
  }
}

// --- ADMIN CABANG: Input angsuran, data nasabah cabang ---
async function runAdminCabang(browser, dayNum) {
  const page = await loginAs(browser, 'admin_cabang');
  if (!page) return;
  try {
    logRole('admin_cabang', 'Mulai kerja - cek dashboard & notifikasi cabang');

    // Nasabah cabang
    if (await safeGoto(page, '/pages/nasabah/index.php', 'admin_cabang', 'Nasabah')) {
      logRole('admin_cabang', 'Cek & update data nasabah cabang hari ini');
    }

    // Input angsuran (aktivitas utama harian)
    if (await safeGoto(page, '/pages/angsuran/index.php', 'admin_cabang', 'Angsuran')) {
      logRole('admin_cabang', 'Input pembayaran angsuran dari nasabah');
    }

    // Pembayaran (setiap hari)
    if (await safeGoto(page, '/pages/pembayaran/index.php', 'admin_cabang', 'Pembayaran')) {
      logRole('admin_cabang', 'Rekap pembayaran masuk hari ini');
    }

    // Pinjaman
    if (await safeGoto(page, '/pages/pinjaman/index.php', 'admin_cabang', 'Pinjaman')) {
      logRole('admin_cabang', 'Update status pinjaman cabang');
    }

    // Rekonsiliasi kas (akhir hari)
    if (await safeGoto(page, '/pages/cash_reconciliation/index.php', 'admin_cabang', 'Rekonsiliasi')) {
      logRole('admin_cabang', 'Rekonsiliasi kas akhir hari');
    }

    await screenshot(page, `admin_cabang_eod_day${dayNum}`);
  } finally {
    await page.close();
  }
}

// --- PETUGAS: Aktivitas lapangan, kumpul angsuran, kas petugas ---
async function runPetugas(browser, dayNum) {
  const page = await loginAs(browser, 'petugas');
  if (!page) return;
  try {
    logRole('petugas', 'Mulai hari - cek daftar tagihan & rute hari ini');

    // Cek angsuran yang perlu dikumpulkan
    if (await safeGoto(page, '/pages/angsuran/index.php', 'petugas', 'Angsuran')) {
      logRole('petugas', 'Review daftar angsuran yang harus dikumpulkan hari ini');
    }

    // Input aktivitas lapangan (wajib setiap hari)
    if (await safeGoto(page, '/pages/field_activities/index.php', 'petugas', 'Aktivitas Lapangan')) {
      logRole('petugas', 'Catat aktivitas lapangan: kunjungan nasabah, survey');
    }

    // Cek rute harian
    if (await safeGoto(page, '/pages/rute_harian/index.php', 'petugas', 'Rute Harian')) {
      logRole('petugas', 'Cek rute & jadwal kunjungan nasabah hari ini');
    }

    // Input pembayaran yang dikumpulkan (setiap hari)
    if (await safeGoto(page, '/pages/pembayaran/index.php', 'petugas', 'Pembayaran')) {
      logRole('petugas', 'Input pembayaran angsuran yang dikumpulkan di lapangan');
    }

    // Setoran ke kas petugas (setiap hari setelah kumpul uang)
    if (await safeGoto(page, '/pages/kas_petugas/index.php', 'petugas', 'Kas Petugas')) {
      logRole('petugas', 'Setoran kas petugas - uang angsuran terkumpul');
    }

    // Kinerja (review setiap minggu)
    if (dayNum % 7 === 6) {
      if (await safeGoto(page, '/pages/kinerja/index.php', 'petugas', 'Kinerja')) {
        logRole('petugas', 'Review kinerja mingguan pribadi');
      }
    }

    // View nasabah assigned
    if (await safeGoto(page, '/pages/nasabah/index.php', 'petugas', 'Nasabah')) {
      logRole('petugas', 'Cek data nasabah yang di-handle');
    }

    await screenshot(page, `petugas_eod_day${dayNum}`);
  } finally {
    await page.close();
  }
}

// --- KARYAWAN: Rekonsiliasi kas, data entry, admin support ---
async function runKaryawan(browser, dayNum) {
  const page = await loginAs(browser, 'karyawan');
  if (!page) return;
  try {
    logRole('karyawan', 'Mulai tugas - cek dashboard & agenda hari ini');

    // View nasabah (read only)
    if (await safeGoto(page, '/pages/nasabah/index.php', 'karyawan', 'Nasabah')) {
      logRole('karyawan', 'Cek data nasabah untuk keperluan administrasi');
    }

    // View pinjaman (read only)
    if (await safeGoto(page, '/pages/pinjaman/index.php', 'karyawan', 'Pinjaman')) {
      logRole('karyawan', 'Cek status pinjaman untuk rekap administrasi');
    }

    // View angsuran (read only)
    if (await safeGoto(page, '/pages/angsuran/index.php', 'karyawan', 'Angsuran')) {
      logRole('karyawan', 'Rekap status angsuran untuk laporan harian');
    }

    // Rekonsiliasi kas (tugas utama karyawan - setiap hari)
    if (await safeGoto(page, '/pages/cash_reconciliation/index.php', 'karyawan', 'Rekonsiliasi')) {
      logRole('karyawan', 'Lakukan rekonsiliasi kas harian - cocokkan fisik vs sistem');
    }

    // View pengeluaran
    if (await safeGoto(page, '/pages/pengeluaran/index.php', 'karyawan', 'Pengeluaran')) {
      logRole('karyawan', 'Catat & review pengeluaran operasional cabang');
    }

    await screenshot(page, `karyawan_eod_day${dayNum}`);
  } finally {
    await page.close();
  }
}

// ============================================================
// SIMULASI PER HARI
// ============================================================
async function simulateDay(browser, dayNum) {
  state.currentDay = dayNum;
  // Hitung tanggal simulasi (mulai 1 Mei 2026)
  const simDate = new Date('2026-05-01');
  simDate.setDate(simDate.getDate() + dayNum - 1);
  state.currentDate = simDate;

  const dateStr = simDate.toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
  log(`\n${'='.repeat(60)}`, 'DAY');
  log(`HARI ${dayNum}/90 — ${dateStr}`, 'DAY');
  log(`${'='.repeat(60)}`, 'DAY');

  const isWeekend = simDate.getDay() === 0 || simDate.getDay() === 6;
  const isMonday  = simDate.getDay() === 1;
  const isFriday  = simDate.getDay() === 5;

  try {
    // ---- SUPERADMIN: Cek sistem setiap hari kerja ----
    if (!isWeekend || dayNum % 7 === 0) {
      await runSuperadmin(browser, dayNum);
    }

    // ---- BOS: Pantau bisnis setiap hari kerja ----
    if (!isWeekend) {
      await runBos(browser, dayNum);
    } else {
      // Weekend: bos tetap cek laporan (bisnis tidak tidur)
      log(`[Day ${dayNum}] Bos: Weekend - review laporan ringkas`, 'ACTIVITY');
    }

    // ---- MANAGER PUSAT: Hari kerja ----
    if (!isWeekend) {
      await runManagerPusat(browser, dayNum);
    }

    // ---- MANAGER CABANG: Hari kerja ----
    if (!isWeekend) {
      await runManagerCabang(browser, dayNum);
    }

    // ---- ADMIN PUSAT: Hari kerja ----
    if (!isWeekend) {
      await runAdminPusat(browser, dayNum);
    }

    // ---- ADMIN CABANG: Hari kerja ----
    if (!isWeekend) {
      await runAdminCabang(browser, dayNum);
    }

    // ---- PETUGAS: Hari kerja (Senin-Sabtu lapangan) ----
    if (simDate.getDay() !== 0) { // tidak hari Minggu
      await runPetugas(browser, dayNum);
    }

    // ---- KARYAWAN: Hari kerja ----
    if (!isWeekend) {
      await runKaryawan(browser, dayNum);
    }

    log(`[Day ${dayNum}] ✓ Semua role selesai | Total aktivitas hari ini: ${state.totalActivities}`, 'SUCCESS');

  } catch (err) {
    log(`[Day ${dayNum}] CRITICAL ERROR: ${err.message}`, 'ERROR');
  }
}

// ============================================================
// MAIN
// ============================================================
async function runSimulation() {
  log('');
  log('╔══════════════════════════════════════════════════════════╗');
  log('║   KEWER REALISTIC 3-MONTH SIMULATION - ALL ROLES        ║');
  log('╠══════════════════════════════════════════════════════════╣');
  log(`║  Duration : ${config.simulationDays} hari (3 bulan)                       ║`);
  log(`║  Roles    : 8 role (superadmin, bos, mgr pusat/cabang,  ║`);
  log(`║             admin pusat/cabang, petugas, karyawan)       ║`);
  log(`║  Mode     : Headed (headless: false)                     ║`);
  log('╚══════════════════════════════════════════════════════════╝');
  log('');

  const browser = await puppeteer.launch({
    headless: config.headless,
    slowMo: config.slowMo,
    args: [
      '--no-sandbox',
      '--disable-setuid-sandbox',
      '--start-maximized',
      '--window-size=1280,800',
    ],
    defaultViewport: null,
  });

  try {
    for (let day = 1; day <= config.simulationDays; day++) {
      await simulateDay(browser, day);

      if (day < config.simulationDays) {
        await delay(config.dayDuration);
      }

      // Progress report setiap 7 hari
      if (day % 7 === 0 || day === config.simulationDays) {
        log('');
        log(`── PROGRESS WEEK ${Math.ceil(day/7)} ──────────────────────────────────`);
        log(`Total aktivitas: ${state.totalActivities} | Total error: ${state.totalErrors}`);
        Object.entries(state.roleStats).forEach(([role, stats]) => {
          log(`  ${USERS[role].label.padEnd(25)} login: ${stats.loginSuccess}✓ ${stats.loginFail}✗  aktivitas: ${stats.activities}  error: ${stats.errors}`);
        });
        log('');
      }
    }

    // ---- FINAL REPORT ----
    log('');
    log('╔══════════════════════════════════════════════════════════╗');
    log('║              SIMULASI SELESAI - FINAL REPORT            ║');
    log('╠══════════════════════════════════════════════════════════╣');
    log(`║  Total Hari    : ${config.simulationDays}                                  ║`);
    log(`║  Total Aktivitas: ${String(state.totalActivities).padEnd(6)}                              ║`);
    log(`║  Total Error   : ${String(state.totalErrors).padEnd(6)}                              ║`);
    log('╠══════════════════════════════════════════════════════════╣');
    log('║  STATISTIK PER ROLE:                                    ║');
    Object.entries(state.roleStats).forEach(([role, stats]) => {
      const successRate = stats.loginSuccess + stats.loginFail > 0
        ? Math.round((stats.loginSuccess / (stats.loginSuccess + stats.loginFail)) * 100)
        : 0;
      log(`║  ${USERS[role].label.padEnd(22)} ${String(successRate + '%').padEnd(5)} login OK  akt:${String(stats.activities).padEnd(5)}║`);
    });
    log('╚══════════════════════════════════════════════════════════╝');

    // Simpan report JSON
    const report = {
      simulationDate: new Date().toISOString(),
      totalDays: config.simulationDays,
      totalActivities: state.totalActivities,
      totalErrors: state.totalErrors,
      roleStats: state.roleStats,
    };
    const reportPath = path.join(config.logsDir, `sim_report_${new Date().toISOString().split('T')[0]}.json`);
    fs.writeFileSync(reportPath, JSON.stringify(report, null, 2));
    log(`Report disimpan: ${reportPath}`, 'SUCCESS');

  } catch (err) {
    log(`FATAL: ${err.message}`, 'ERROR');
    log(err.stack, 'ERROR');
  } finally {
    await browser.close();
  }
}

runSimulation().catch(err => {
  log(`UNHANDLED: ${err.message}`, 'ERROR');
  process.exit(1);
});
