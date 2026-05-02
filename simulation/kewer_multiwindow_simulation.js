/**
 * ============================================================
 * KEWER REAL-WORLD SIMULATION — MULTI-WINDOW, REAL DATA
 * ============================================================
 * Setiap role membuka BROWSER WINDOW TERPISAH dan melakukan
 * AKSI NYATA yang mengubah data di database:
 *
 * • Admin Cabang   → tambah nasabah baru via form
 * • Admin Pusat    → input pengajuan pinjaman via form
 * • Manager Cabang → approve/review pinjaman via API
 * • Petugas        → catat aktivitas lapangan + bayar angsuran via API
 * • Karyawan       → rekonsiliasi kas via form
 * • Manager Pusat  → monitor & approve kas petugas via halaman
 * • Bos            → review laporan & dashboard
 * • Superadmin     → audit log & permissions
 *
 * Setiap browser window tetap terbuka agar bisa dilihat di layar.
 * Window baru dibuka untuk hari berikutnya (window lama ditutup).
 */

const puppeteer = require('puppeteer');
const fs   = require('fs');
const path = require('path');

// ─── CONFIG ───────────────────────────────────────────────────
const CFG = {
  base:       'http://localhost/kewer',
  days:       90,
  dayDelay:   4000,   // jeda antar hari (ms)
  stepDelay:  800,    // jeda antar aksi dalam satu role (ms)
  slowMo:     40,
  logDir:     path.join(__dirname, 'logs'),
  ssDir:      path.join(__dirname, 'screenshots'),
};

// ─── USERS ────────────────────────────────────────────────────
const USERS = {
  superadmin:     { u: 'patri',             p: 'password',    label: 'Superadmin' },
  bos:            { u: 'bos_simulasi',      p: 'password123', label: 'Bos' },
  manager_pusat:  { u: 'manager_pusat_sim', p: 'password123', label: 'Manager Pusat' },
  manager_cabang: { u: 'manager_cabang1',   p: 'password123', label: 'Manager Cabang' },
  admin_pusat:    { u: 'admin_pusat_sim',   p: 'password',    label: 'Admin Pusat' },
  admin_cabang:   { u: 'admin_cabang_sim',  p: 'password',    label: 'Admin Cabang' },
  petugas:        { u: 'petugas1_sim',      p: 'password123', label: 'Petugas' },
  karyawan:       { u: 'karyawan1',         p: 'password123', label: 'Karyawan' },
};

// ─── STATE ────────────────────────────────────────────────────
const S = {
  day: 0, date: null,
  nasabahIds: [],
  pinjamanIds: [],
  angsuranIds: [],
  petugasUserId: 33,  // petugas1_sim id di DB
  cabangId: 16,       // Kantor Pusat HQ001
  errors: 0, acts: 0,
  stats: Object.fromEntries(Object.keys(USERS).map(r => [r, { ok: 0, fail: 0, acts: 0 }])),
};

// ─── LOGGING ──────────────────────────────────────────────────
[CFG.logDir, CFG.ssDir].forEach(d => fs.existsSync(d) || fs.mkdirSync(d, { recursive: true }));
const logPath = path.join(CFG.logDir, `realworld_${new Date().toISOString().slice(0,10)}.log`);

function L(msg, t = 'INFO') {
  const line = `[${new Date().toISOString()}] [${t.padEnd(8)}] ${msg}`;
  console.log(line);
  fs.appendFileSync(logPath, line + '\n');
}
function LA(role, act) {
  S.stats[role].acts++; S.acts++;
  L(`[Day ${S.day}] ${USERS[role].label} ➤ ${act}`, 'ACT');
}
function LE(role, msg) {
  S.stats[role] && S.stats[role].fail++;
  S.errors++;
  L(`[Day ${S.day}] [${role}] ERROR: ${msg}`, 'ERROR');
}

const sleep = ms => new Promise(r => setTimeout(r, ms));

// ─── LAUNCH BROWSER (1 per role per hari) ────────────────────
async function launchBrowser(title) {
  return puppeteer.launch({
    headless: false,
    slowMo: CFG.slowMo,
    args: [
      '--no-sandbox',
      '--disable-setuid-sandbox',
      `--window-size=1100,750`,
      `--window-position=${(Math.floor(Math.random()*3))*370},${(Math.floor(Math.random()*2))*400}`,
    ],
    defaultViewport: null,
  });
}

// ─── LOGIN ────────────────────────────────────────────────────
async function login(browser, roleKey) {
  const u = USERS[roleKey];
  const page = await browser.newPage();
  try {
    await page.goto(
      `${CFG.base}/login.php?test_login=true&username=${u.u}&password=${u.p}`,
      { waitUntil: 'networkidle2', timeout: 20000 }
    );
    await sleep(700);
    const url = page.url();
    if (url.includes('dashboard.php') || url.endsWith('/kewer/') || url.endsWith('/kewer')) {
      S.stats[roleKey].ok++;
      L(`[Day ${S.day}] LOGIN ✓ ${u.label}`, 'LOGIN');
      return page;
    }
    // fallback
    await page.goto(`${CFG.base}/dashboard.php`, { waitUntil: 'networkidle2', timeout: 15000 });
    await sleep(500);
    if (page.url().includes('dashboard.php')) {
      S.stats[roleKey].ok++;
      L(`[Day ${S.day}] LOGIN ✓ ${u.label} (fallback)`, 'LOGIN');
      return page;
    }
    throw new Error(`URL setelah login: ${page.url()}`);
  } catch (e) {
    S.stats[roleKey].fail++;
    LE(roleKey, `Login gagal: ${e.message}`);
    await page.close();
    return null;
  }
}

async function go(page, url, role, desc) {
  try {
    await page.goto(`${CFG.base}${url}`, { waitUntil: 'networkidle2', timeout: 15000 });
    await sleep(CFG.stepDelay);
    return true;
  } catch (e) {
    LE(role, `Navigasi gagal ${url} (${desc}): ${e.message}`);
    return false;
  }
}

async function ss(page, name) {
  try { await page.screenshot({ path: path.join(CFG.ssDir, `${name}_d${S.day}_${Date.now()}.png`), fullPage: false }); }
  catch {}
}

// ─────────────────────────────────────────────────────────────
// AKSI NYATA PER ROLE
// ─────────────────────────────────────────────────────────────

// ── ADMIN CABANG: Tambah nasabah baru & input angsuran ────────
async function actAdminCabang(page) {
  // 1. Tambah nasabah baru via form
  const ts = Date.now();
  const ktpBase = 1200000000000000;
  const ktp = String(ktpBase + (S.day * 1000) + Math.floor(Math.random() * 999)).slice(0, 16);
  const telp = '0812' + String(Math.floor(10000000 + Math.random() * 89999999));
  const namaList = ['Siti Rahayu', 'Budi Santoso', 'Dewi Kusuma', 'Agus Prayitno',
                    'Rina Marlina', 'Hendra Gunawan', 'Yuni Lestari', 'Dodi Prasetyo',
                    'Fitri Handayani', 'Wahyu Nugroho', 'Sri Mulyani', 'Rudi Hartono',
                    'Lestari Wulandari', 'Bambang Sutrisno', 'Indah Permata'];
  const namaFull = namaList[S.day % namaList.length] + ' ' + (S.day * 3);
  LA('admin_cabang', `Siapkan pendaftaran nasabah baru: ${namaFull}`);

  await go(page, '/pages/nasabah/tambah.php', 'admin_cabang', 'form nasabah');
  await sleep(1000);

  try {
    // Cek apakah halaman berhasil (ada form, bukan redirect)
    const formEl = await page.$('form[method="POST"], form[method="post"]');
    if (!formEl) {
      LA('admin_cabang', 'Halaman tambah nasabah dibuka (form tidak terdeteksi - cek permission)');
    } else {
      // Isi form nasabah
      await page.$eval('input[name="nama"]', el => el.value = '');
      await page.type('input[name="nama"]', namaFull, { delay: 40 });
      await sleep(200);
      await page.$eval('input[name="ktp"]', el => el.value = '');
      await page.type('input[name="ktp"]', ktp, { delay: 30 });
      await sleep(200);
      await page.$eval('input[name="telp"]', el => el.value = '');
      await page.type('input[name="telp"]', telp, { delay: 30 });
      await sleep(200);

      // Isi alamat jika ada
      const alamatEl = await page.$('textarea[name="alamat"]');
      if (alamatEl) {
        await page.$eval('textarea[name="alamat"]', el => el.value = '');
        await page.type('textarea[name="alamat"]', `Jl. Pasar Lama No. ${S.day}`, { delay: 25 });
      }
      // Lokasi pasar
      const lokasiEl = await page.$('input[name="lokasi_pasar"]');
      if (lokasiEl) {
        await page.$eval('input[name="lokasi_pasar"]', el => el.value = '');
        await page.type('input[name="lokasi_pasar"]', 'Pasar Sentral', { delay: 25 });
      }

      await ss(page, 'admin_cabang_form_nasabah_isi');
      await sleep(400);

      // Submit form
      await page.click('button[type="submit"]');
      await page.waitForNavigation({ waitUntil: 'networkidle2', timeout: 12000 }).catch(() => {});
      await sleep(500);

      // Cek sukses atau error
      const alertSuccess = await page.$('.alert-success');
      const alertDanger  = await page.$('.alert-danger');
      if (alertSuccess) {
        const msg = await page.$eval('.alert-success', el => el.textContent.trim());
        LA('admin_cabang', `✓ Nasabah baru berhasil didaftarkan: ${namaFull} | ${msg}`);
      } else if (alertDanger) {
        const msg = await page.$eval('.alert-danger', el => el.textContent.trim());
        LE('admin_cabang', `Form nasabah: ${msg}`);
      } else {
        LA('admin_cabang', `Nasabah ${namaFull} diproses`);
      }
      await ss(page, 'admin_cabang_nasabah_result');
    }
  } catch (e) {
    LE('admin_cabang', `Form nasabah exception: ${e.message}`);
  }

  // 2. Lihat daftar nasabah
  await go(page, '/pages/nasabah/index.php', 'admin_cabang', 'daftar nasabah');
  LA('admin_cabang', 'Cek daftar nasabah terbaru cabang');
  await ss(page, 'admin_cabang_nasabah_list');

  // 3. Lihat daftar angsuran
  await go(page, '/pages/angsuran/index.php', 'admin_cabang', 'angsuran');
  LA('admin_cabang', 'Cek & proses pembayaran angsuran hari ini');
  await ss(page, 'admin_cabang_angsuran');

  // 4. Rekonsiliasi kas (akhir hari)
  await go(page, '/pages/cash_reconciliation/index.php', 'admin_cabang', 'rekonsiliasi');
  LA('admin_cabang', 'Buka rekonsiliasi kas harian cabang');
  await ss(page, 'admin_cabang_rekonsiliasi');
}

// ── ADMIN PUSAT: Input pengajuan pinjaman ─────────────────────
async function actAdminPusat(page) {
  LA('admin_pusat', 'Review data nasabah — siapkan pengajuan pinjaman');
  await go(page, '/pages/nasabah/index.php', 'admin_pusat', 'nasabah');
  await ss(page, 'admin_pusat_nasabah');

  // Tambah pinjaman baru setiap 3 hari (ada nasabah baru yang apply)
  if (S.day % 3 === 1) {
    await go(page, '/pages/pinjaman/tambah.php', 'admin_pusat', 'tambah pinjaman');
    await sleep(1200);
    try {
      // Cek ada form
      const formEl = await page.$('form#loanForm, form[method="POST"]');
      if (!formEl) {
        LA('admin_pusat', 'Halaman form pinjaman dibuka');
      } else {
        // Pilih nasabah dari dropdown
        const opts = await page.$$eval('select[name="nasabah_id"] option',
          els => els.filter(o => o.value && o.value !== '').map(o => ({ v: o.value, t: o.textContent.trim() }))
        );
        if (opts.length > 0) {
          // Pilih nasabah yang tidak punya pinjaman aktif (jika ada, coba max 3 opsi)
          const tryOpts = [...opts].sort(() => Math.random() - 0.5).slice(0, 3);
          const pick = tryOpts[0];
          await page.select('select[name="nasabah_id"]', pick.v);
          await sleep(400);
          LA('admin_pusat', `Pilih nasabah: ${pick.t}`);

          // Plafon (gunakan id="plafon", format angka saja karena PHP strip non-digit)
          const plafon = (Math.floor(Math.random() * 8) + 2) * 1000000;
          await page.$eval('#plafon', el => el.value = '');
          await page.type('#plafon', String(plafon), { delay: 30 });

          // Frekuensi
          await page.select('select[name="frekuensi"], #frekuensi', 'bulanan').catch(() => {});
          await sleep(300);

          // Tenor
          const tenor = [3, 6, 12][Math.floor(Math.random() * 3)];
          await page.$eval('#tenor', el => el.value = '');
          await page.type('#tenor', String(tenor), { delay: 30 });

          // Bunga (id="bunga")
          await page.$eval('#bunga', el => el.value = '').catch(async () => {
            await page.$eval('input[name="bunga_per_bulan"]', el => el.value = '').catch(() => {});
          });
          await page.type('#bunga', '2', { delay: 30 }).catch(async () => {
            await page.type('input[name="bunga_per_bulan"]', '2', { delay: 30 }).catch(() => {});
          });

          // Tanggal akad — set langsung via JS karena flatpickr
          await page.evaluate((tgl) => {
            const el = document.querySelector('input[name="tanggal_akad"]');
            if (el) { el.value = tgl; el.dispatchEvent(new Event('change')); }
          }, S.date);
          await sleep(300);

          // Tujuan & jaminan
          const tujEl = await page.$('textarea[name="tujuan_pinjaman"]');
          if (tujEl) { await page.$eval('textarea[name="tujuan_pinjaman"]', el => el.value = ''); await page.type('textarea[name="tujuan_pinjaman"]', 'Modal usaha harian', { delay: 20 }); }
          const jamEl = await page.$('textarea[name="jaminan"]');
          if (jamEl) { await page.$eval('textarea[name="jaminan"]', el => el.value = ''); await page.type('textarea[name="jaminan"]', 'BPKB Sepeda Motor', { delay: 20 }); }

          await ss(page, 'admin_pusat_form_pinjaman_isi');
          await sleep(500);
          await page.click('button[type="submit"]');
          await page.waitForNavigation({ waitUntil: 'networkidle2', timeout: 12000 }).catch(() => {});
          await sleep(500);

          // Cek hasil
          const ok  = await page.$('.alert-success');
          const err = await page.$('.alert-danger');
          if (ok) {
            const msg = await page.$eval('.alert-success', el => el.textContent.trim());
            LA('admin_pusat', `✓ Pinjaman Rp${plafon.toLocaleString()} tenor ${tenor}bln — ${msg}`);
          } else if (err) {
            const msg = await page.$eval('.alert-danger', el => el.textContent.trim());
            LE('admin_pusat', `Form pinjaman: ${msg}`);
          } else {
            LA('admin_pusat', `Pengajuan pinjaman Rp${plafon.toLocaleString()} diproses`);
          }
          await ss(page, 'admin_pusat_pinjaman_result');
        } else {
          LA('admin_pusat', 'Tidak ada nasabah aktif untuk pinjaman — tambah nasabah dulu');
        }
      }
    } catch (e) {
      LE('admin_pusat', `Form pinjaman: ${e.message}`);
    }
  }

  // Review daftar pinjaman
  await go(page, '/pages/pinjaman/index.php', 'admin_pusat', 'daftar pinjaman');
  LA('admin_pusat', 'Review status & portofolio pinjaman');
  await ss(page, 'admin_pusat_pinjaman_list');
}

// ── MANAGER CABANG: Approve pinjaman + monitor petugas ────────
async function actManagerCabang(page) {
  LA('manager_cabang', 'Buka dashboard & review notifikasi pinjaman pending');
  await ss(page, 'manager_cabang_dashboard');

  // Approve pinjaman via halaman (klik tombol approve jika ada)
  await go(page, '/pages/pinjaman/index.php', 'manager_cabang', 'pinjaman');
  LA('manager_cabang', 'Cek pinjaman pengajuan — proses approval');
  try {
    // Ambil ID pinjaman berstatus pengajuan via fetch dengan session
    const pendingIds = await page.evaluate(async (base) => {
      const r = await fetch(`${base}/api/pinjaman.php?cabang_id=16&status=pengajuan`);
      const text = await r.text();
      try {
        const data = JSON.parse(text);
        return (data.data || []).slice(0, 3).map(p => p.id);
      } catch { return []; }
    }, CFG.base);

    for (const pid of pendingIds) {
      const result = await page.evaluate(async (base, id) => {
        const r = await fetch(`${base}/api/pinjaman.php`, {
          method: 'PUT',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ id, action: 'approve', status: 'disetujui' })
        });
        const text = await r.text();
        try { return JSON.parse(text); } catch { return { raw: text.substring(0,80) }; }
      }, CFG.base, pid);
      LA('manager_cabang', `Approve pinjaman ID ${pid} → ${result.success ? '✓ DISETUJUI' : (result.message || result.error || result.raw || 'via halaman')}`);
      await sleep(500);
    }
    if (pendingIds.length === 0) LA('manager_cabang', 'Tidak ada pinjaman pending hari ini — semua sudah diproses');
  } catch (e) {
    LE('manager_cabang', `Approve pinjaman: ${e.message}`);
  }

  // Review aktivitas lapangan petugas
  await go(page, '/pages/field_activities/index.php', 'manager_cabang', 'field activities');
  LA('manager_cabang', 'Monitor laporan aktivitas lapangan petugas');
  await ss(page, 'manager_cabang_field');

  // Kas petugas
  await go(page, '/pages/kas_petugas/index.php', 'manager_cabang', 'kas petugas');
  LA('manager_cabang', 'Review setoran kas petugas');
  await ss(page, 'manager_cabang_kas');
}

// ── PETUGAS: Catat aktivitas lapangan + bayar angsuran ────────
async function actPetugas(page) {
  LA('petugas', 'Mulai hari — cek rute & daftar tagihan');

  // 1. Catat aktivitas lapangan via API (session sudah login)
  try {
    const actTypes = ['survey_nasabah', 'kutip_angsuran', 'follow_up', 'promosi', 'edukasi', 'lainnya'];
    const actType  = actTypes[S.day % actTypes.length];
    const descs    = {
      survey_nasabah: 'Survey calon nasabah baru di kawasan pasar',
      kutip_angsuran: 'Kumpul angsuran dari nasabah di lapangan',
      follow_up:      'Follow up nasabah yang telat bayar',
      promosi:        'Promosi produk pinjaman di area pasar',
      edukasi:        'Edukasi nasabah tentang manajemen keuangan usaha',
      lainnya:        'Kunjungan rutin & pengecekan kondisi usaha nasabah',
    };
    const result = await page.evaluate(async (base, data) => {
      const r = await fetch(`${base}/api/field_officer_activities.php`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
      });
      const text = await r.text();
      try { return JSON.parse(text); } catch { return { raw: text.substring(0, 100) }; }
    }, CFG.base, {
      activity_type: actType,
      description: descs[actType],
      location: 'Pasar Sentral - Kab. Samosir',
      activity_date: S.date,
      activity_time: `0${8 + (S.day % 3)}:${String(S.day * 7 % 60).padStart(2,'0')}:00`,
      status: 'completed'
    });
    LA('petugas', `Aktivitas lapangan [${actType}]: ${result.success ? '✓ tersimpan di DB' : result.error || result.raw || 'via halaman'}`);
  } catch (e) {
    LE('petugas', `API field activity: ${e.message}`);
  }

  // 2. Buka halaman aktivitas lapangan
  await go(page, '/pages/field_activities/index.php', 'petugas', 'aktivitas lapangan');
  LA('petugas', 'Lihat & review aktivitas lapangan hari ini');
  await ss(page, 'petugas_field_activities');

  // 3. Bayar angsuran via API (pakai session yang sudah login)
  try {
    const angsuranList = await page.evaluate(async (base) => {
      const r = await fetch(`${base}/api/angsuran.php?cabang_id=16&status=belum&limit=3`);
      const text = await r.text();
      try {
        const data = JSON.parse(text);
        return Array.isArray(data.data) ? data.data.slice(0, 2) : [];
      } catch { return []; }
    }, CFG.base);

    if (Array.isArray(angsuranList) && angsuranList.length > 0) {
      for (const ang of angsuranList) {
        if (!ang.id) continue;
        const result = await page.evaluate(async (base, id, jumlah) => {
          const r = await fetch(`${base}/api/pembayaran.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
              angsuran_id: id,
              jumlah_bayar: jumlah,
              cara_bayar: 'tunai',
              keterangan: 'Bayar via petugas lapangan'
            })
          });
          const text = await r.text();
          try { return JSON.parse(text); } catch { return { raw: text.substring(0,80) }; }
        }, CFG.base, ang.id, ang.total_angsuran || ang.total_bayar || 0);
        LA('petugas', `Bayar angsuran ID ${ang.id} Rp${Number(ang.total_angsuran||0).toLocaleString()} → ${result.success ? '✓ LUNAS' : result.message || result.raw || 'diproses'}`);
        await sleep(400);
      }
    } else {
      LA('petugas', 'Tidak ada angsuran pending — semua sudah terbayar');
    }
  } catch (e) {
    LE('petugas', `Bayar angsuran: ${e.message}`);
  }

  // 4. Buka halaman angsuran
  await go(page, '/pages/angsuran/index.php', 'petugas', 'angsuran');
  LA('petugas', 'Cek status & rekap angsuran cabang');
  await ss(page, 'petugas_angsuran');

  // 5. Setoran kas petugas via API
  try {
    const kasFisik = Math.floor(Math.random() * 5 + 1) * 500000;
    const result = await page.evaluate(async (base, data) => {
      const r = await fetch(`${base}/api/kas_petugas_setoran.php`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
      });
      const text = await r.text();
      try { return JSON.parse(text); } catch { return { raw: text.substring(0,80) }; }
    }, CFG.base, {
      tanggal: S.date,
      total_kas_petugas: kasFisik,
      total_setoran: kasFisik,
      keterangan: `Setoran hari ke-${S.day} - total angsuran terkumpul`
    });
    LA('petugas', `Setoran kas Rp${kasFisik.toLocaleString()} → ${result.success ? '✓ tersimpan' : result.error || result.raw || 'via halaman'}`);
  } catch (e) {
    LE('petugas', `Setoran kas: ${e.message}`);
  }

  await go(page, '/pages/kas_petugas/index.php', 'petugas', 'kas petugas');
  LA('petugas', 'Lihat riwayat & saldo kas petugas');
  await ss(page, 'petugas_kas');
}

// ── KARYAWAN: Rekonsiliasi kas harian ─────────────────────────
async function actKaryawan(page) {
  LA('karyawan', 'Mulai hari — cek notifikasi & tugas');

  // 1. Lihat angsuran (read only)
  await go(page, '/pages/angsuran/index.php', 'karyawan', 'angsuran');
  LA('karyawan', 'Rekap status angsuran untuk laporan harian');
  await ss(page, 'karyawan_angsuran');

  // 2. Rekonsiliasi kas via form
  await go(page, '/pages/cash_reconciliation/index.php', 'karyawan', 'rekonsiliasi');
  LA('karyawan', 'Buka halaman rekonsiliasi kas');
  try {
    // Coba isi form rekonsiliasi jika ada
    const formExists = await page.$('input[name="total_penerimaan"], input[name="kas_fisik"]');
    if (formExists) {
      const totalPenerimaan = Math.floor(Math.random() * 10 + 5) * 500000;
      const totalPengeluaran = Math.floor(Math.random() * 3 + 1) * 200000;
      await page.evaluate(() => {
        document.querySelectorAll('input[name="total_penerimaan"]').forEach(el => el.value = '');
        document.querySelectorAll('input[name="total_pengeluaran"]').forEach(el => el.value = '');
      });
      await page.type('input[name="total_penerimaan"]', String(totalPenerimaan), { delay: 20 }).catch(() => {});
      await page.type('input[name="total_pengeluaran"]', String(totalPengeluaran), { delay: 20 }).catch(() => {});
      const tglEl = await page.$('input[name="tanggal"]');
      if (tglEl) { await page.$eval('input[name="tanggal"]', el => el.value = ''); await page.type('input[name="tanggal"]', S.date, { delay: 10 }); }
      const ketEl = await page.$('textarea[name="keterangan"]');
      if (ketEl) await page.type('textarea[name="keterangan"]', `Rekonsiliasi hari ke-${S.day} simulasi`, { delay: 15 });
      await ss(page, 'karyawan_rekonsiliasi_isi');
      const submitBtn = await page.$('button[type="submit"]');
      if (submitBtn) {
        await submitBtn.click();
        await page.waitForNavigation({ waitUntil: 'networkidle2', timeout: 8000 }).catch(() => {});
        LA('karyawan', `Rekonsiliasi kas disimpan: penerimaan Rp${totalPenerimaan.toLocaleString()}`);
      } else {
        LA('karyawan', 'Halaman rekonsiliasi dibuka (tombol submit tidak ditemukan)');
      }
    } else {
      LA('karyawan', 'Halaman rekonsiliasi dibuka — cek data harian');
      await ss(page, 'karyawan_rekonsiliasi');
    }
  } catch (e) {
    LE('karyawan', `Rekonsiliasi: ${e.message}`);
  }

  // 3. View pengeluaran
  await go(page, '/pages/pengeluaran/index.php', 'karyawan', 'pengeluaran');
  LA('karyawan', 'Catat & review pengeluaran operasional');
  await ss(page, 'karyawan_pengeluaran');
}

// ── MANAGER PUSAT: Monitor & approve kas, review cabang ───────
async function actManagerPusat(page) {
  LA('manager_pusat', 'Review dashboard operasional lintas cabang');
  await ss(page, 'manager_pusat_dashboard');

  await go(page, '/pages/pinjaman/index.php', 'manager_pusat', 'pinjaman');
  LA('manager_pusat', 'Monitor portofolio pinjaman semua cabang');
  await ss(page, 'manager_pusat_pinjaman');

  await go(page, '/pages/kas_petugas/index.php', 'manager_pusat', 'kas petugas');
  LA('manager_pusat', 'Review & approve setoran kas petugas');
  try {
    // Approve setoran yang pending via API
    const setoran = await page.evaluate(async (base) => {
      const r = await fetch(`${base}/api/kas_petugas.php?status=pending&limit=5`);
      const d = await r.json().catch(() => ({}));
      return (d.data || []).map(s => s.id);
    }, CFG.base);
    for (const sid of setoran.slice(0, 3)) {
      const res = await page.evaluate(async (base, id) => {
        const r = await fetch(`${base}/api/kas_petugas.php/${id}/approve`, { method: 'PUT' });
        return r.json().catch(() => ({}));
      }, CFG.base, sid);
      LA('manager_pusat', `Approve setoran kas ${sid} → ${res.success ? 'OK' : 'cek halaman'}`);
    }
  } catch (e) {
    LE('manager_pusat', `Approve kas: ${e.message}`);
  }
  await ss(page, 'manager_pusat_kas');

  if (S.day % 7 === 0) {
    await go(page, '/pages/laporan/index.php', 'manager_pusat', 'laporan');
    LA('manager_pusat', 'Buat laporan kinerja mingguan');
    await ss(page, 'manager_pusat_laporan');
  }
}

// ── BOS: Review laporan & dashboard ───────────────────────────
async function actBos(page) {
  LA('bos', 'Review dashboard konsolidasi bisnis');
  await ss(page, 'bos_dashboard');

  await go(page, '/pages/laporan/index.php', 'bos', 'laporan');
  LA('bos', 'Baca laporan keuangan & performa cabang');
  await ss(page, 'bos_laporan');

  await go(page, '/pages/nasabah/index.php', 'bos', 'nasabah');
  LA('bos', 'Pantau total & pertumbuhan nasabah');
  await ss(page, 'bos_nasabah');

  if (S.day % 7 === 0) {
    await go(page, '/pages/family_risk/index.php', 'bos', 'family risk');
    LA('bos', 'Analisis risiko keluarga nasabah mingguan');
    await ss(page, 'bos_family_risk');
  }
  if (S.day % 30 === 2) {
    await go(page, '/pages/setting_bunga/index.php', 'bos', 'setting bunga');
    LA('bos', 'Evaluasi & setting bunga pinjaman bulanan');
    await ss(page, 'bos_setting_bunga');
  }
}

// ── SUPERADMIN: Audit + permissions ───────────────────────────
async function actSuperadmin(page) {
  LA('superadmin', 'Review audit trail sistem');
  await go(page, '/pages/audit/index.php', 'superadmin', 'audit');
  await ss(page, 'superadmin_audit');

  if (S.day % 7 === 1) {
    await go(page, '/pages/permissions/index.php', 'superadmin', 'permissions');
    LA('superadmin', 'Kelola permissions user mingguan');
    await ss(page, 'superadmin_permissions');
  }
  if (S.day % 14 === 0) {
    await go(page, '/pages/users/index.php', 'superadmin', 'users');
    LA('superadmin', 'Review daftar & aktivitas user');
    await ss(page, 'superadmin_users');
  }
}

// ─────────────────────────────────────────────────────────────
// SIMULATE ONE DAY — setiap role di BROWSER TERPISAH
// ─────────────────────────────────────────────────────────────
async function simulateDay(dayNum) {
  S.day = dayNum;
  const d = new Date('2026-05-01');
  d.setDate(d.getDate() + dayNum - 1);
  S.date = d.toISOString().slice(0, 10);
  const isWeekend = d.getDay() === 0 || d.getDay() === 6;
  const isSunday  = d.getDay() === 0;

  const dateStr = d.toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
  L('');
  L(`${'═'.repeat(62)}`, 'DAY');
  L(`  HARI ${dayNum}/90 — ${dateStr}${isWeekend ? ' [WEEKEND]' : ''}`, 'DAY');
  L(`${'═'.repeat(62)}`, 'DAY');

  // Daftar role yang aktif hari ini
  const schedule = [
    // role,           aktif di weekend?, fungsi aksi
    ['superadmin',     !isWeekend,        actSuperadmin],
    ['bos',            true,              actBos],
    ['manager_pusat',  !isWeekend,        actManagerPusat],
    ['manager_cabang', !isWeekend,        actManagerCabang],
    ['admin_pusat',    !isWeekend,        actAdminPusat],
    ['admin_cabang',   !isWeekend,        actAdminCabang],
    ['petugas',        !isSunday,         actPetugas],
    ['karyawan',       !isWeekend,        actKaryawan],
  ];

  for (const [role, active, fn] of schedule) {
    if (!active) {
      L(`[Day ${dayNum}] ${USERS[role].label} — libur`, 'SKIP');
      continue;
    }

    // Buka browser window baru untuk role ini
    let browser = null;
    try {
      browser = await launchBrowser(`${USERS[role].label} - Hari ${dayNum}`);
      const page = await login(browser, role);
      if (page) {
        await fn(page);
        await sleep(500);
      }
    } catch (e) {
      LE(role, `Window error: ${e.message}`);
    } finally {
      if (browser) {
        await sleep(800); // beri waktu user melihat window sebelum ditutup
        await browser.close().catch(() => {});
      }
    }

    await sleep(500); // jeda antar role
  }

  L(`[Day ${dayNum}] ✓ Selesai | Kumulatif aktivitas: ${S.acts} | Error: ${S.errors}`, 'SUCCESS');
}

// ─────────────────────────────────────────────────────────────
// MAIN
// ─────────────────────────────────────────────────────────────
async function main() {
  L('');
  L('╔══════════════════════════════════════════════════════════════╗');
  L('║  KEWER MULTI-WINDOW REAL-WORLD SIMULATION                   ║');
  L('║  8 Role × 90 Hari = Aktivitas Nyata di Setiap Window        ║');
  L('╚══════════════════════════════════════════════════════════════╝');
  L('');

  for (let day = 1; day <= CFG.days; day++) {
    await simulateDay(day);

    // Progress report setiap 7 hari
    if (day % 7 === 0) {
      L('');
      L(`── PROGRESS MINGGU KE-${Math.ceil(day/7)} ───────────────────────────────`);
      L(`Total aktivitas: ${S.acts} | Total error: ${S.errors}`);
      for (const [r, st] of Object.entries(S.stats)) {
        L(`  ${USERS[r].label.padEnd(20)} login ${st.ok}✓/${st.fail}✗  aktivitas: ${st.acts}`);
      }
      L('');
    }

    if (day < CFG.days) await sleep(CFG.dayDelay);
  }

  L('');
  L('╔══════════════════════════════════════════════════════════════╗');
  L('║  SIMULASI 90 HARI SELESAI — FINAL REPORT                   ║');
  L('╠══════════════════════════════════════════════════════════════╣');
  L(`║  Total Aktivitas: ${String(S.acts).padEnd(6)}                               ║`);
  L(`║  Total Error    : ${String(S.errors).padEnd(6)}                               ║`);
  L('╠══════════════════════════════════════════════════════════════╣');
  for (const [r, st] of Object.entries(S.stats)) {
    const pct = st.ok + st.fail > 0 ? Math.round(st.ok / (st.ok + st.fail) * 100) : 0;
    L(`║  ${USERS[r].label.padEnd(18)} ${String(pct+'%').padEnd(5)} login  akt: ${String(st.acts).padEnd(5)}         ║`);
  }
  L('╚══════════════════════════════════════════════════════════════╝');

  fs.writeFileSync(
    path.join(CFG.logDir, `report_${new Date().toISOString().slice(0,10)}.json`),
    JSON.stringify({ date: new Date().toISOString(), days: CFG.days, acts: S.acts, errors: S.errors, stats: S.stats }, null, 2)
  );
}

main().catch(e => { L(`FATAL: ${e.message}\n${e.stack}`, 'ERROR'); process.exit(1); });
