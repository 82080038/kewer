'use strict';
/**
 * FASE 0 — SETUP: Superadmin + Bos + Cabang + Semua User
 * Mengikuti flow aplikasi yang benar:
 * 1. Bos daftar via pages/bos/register.php
 * 2. Superadmin approve via pages/superadmin/bos_approvals.php
 * 3. Bos login → buat cabang via pages/cabang/tambah.php
 * 4. Bos tambah semua staff via pages/petugas/tambah.php
 * 5. Update login.php quick login buttons
 */

const path = require('path');
const fs   = require('fs');

const {
  BASE, STATE, WILAYAH, NAMA_BATAK,
  log, LA, LE, LS, sleep, randItem, fmtDate,
  ss, newBrowser, loginUser, fillWilayah, checkAlert,
} = require('./sim_helpers');

// ─── DEFINISI SEMUA USER ────────────────────────────────────────
const ALL_USERS = [
  // bos
  { username: 'bos_kewer',      password: 'password', nama: 'Hendrik Simanjuntak', role: 'bos',            cabang: null,        email: 'bos@kewer.id',        telp: '081234560001' },
  // manager pusat
  { username: 'mgr_pusat',      password: 'password', nama: 'Sondang Br Silaban',  role: 'manager_pusat',  cabang: 'pangururan', email: 'mgr.pusat@kewer.id',  telp: '081234560002' },
  // manager cabang
  { username: 'mgr_pangururan', password: 'password', nama: 'Torus Napitupulu',    role: 'manager_cabang', cabang: 'pangururan', email: 'mgr.cb1@kewer.id',    telp: '081234560003' },
  { username: 'mgr_balige',     password: 'password', nama: 'Roswita Nainggolan',  role: 'manager_cabang', cabang: 'balige',     email: 'mgr.cb2@kewer.id',    telp: '081234560004' },
  // admin pusat
  { username: 'adm_pusat',      password: 'password', nama: 'Melvina Hutabarat',   role: 'admin_pusat',    cabang: 'pangururan', email: 'adm.pusat@kewer.id',  telp: '081234560005' },
  // admin cabang
  { username: 'adm_pangururan', password: 'password', nama: 'Lestari Tambunan',    role: 'admin_cabang',   cabang: 'pangururan', email: 'adm.cb1@kewer.id',    telp: '081234560006' },
  { username: 'adm_balige',     password: 'password', nama: 'Ruli Sirait',         role: 'admin_cabang',   cabang: 'balige',     email: 'adm.cb2@kewer.id',    telp: '081234560007' },
  // petugas (role di form = petugas_cabang)
  { username: 'ptr_pngr1',      password: 'password', nama: 'Darwin Sinaga',       role: 'petugas_cabang', cabang: 'pangururan', email: 'ptr1@kewer.id',       telp: '081234560008' },
  { username: 'ptr_pngr2',      password: 'password', nama: 'Nico Purba',          role: 'petugas_cabang', cabang: 'pangururan', email: 'ptr2@kewer.id',       telp: '081234560009' },
  { username: 'ptr_blg1',       password: 'password', nama: 'Markus Situmorang',   role: 'petugas_cabang', cabang: 'balige',     email: 'ptr3@kewer.id',       telp: '081234560010' },
  // karyawan
  { username: 'krw_pngr',       password: 'password', nama: 'Susi Aritonang',      role: 'karyawan',       cabang: 'pangururan', email: 'krw1@kewer.id',       telp: '081234560011' },
  { username: 'krw_blg',        password: 'password', nama: 'Petrus Hutagalung',   role: 'karyawan',       cabang: 'balige',     email: 'krw2@kewer.id',       telp: '081234560012' },
];

// ─── STEP 1: BOS DAFTAR ─────────────────────────────────────────
async function registerBos(browser) {
  log('═══ STEP 1: Bos mendaftar via /pages/bos/register.php ═══');
  const page = await browser.newPage();
  try {
    await page.goto(`${BASE}/pages/bos/register.php`, { waitUntil: 'networkidle2', timeout: 15000 });
    await sleep(800);

    // Username & Password
    await page.type('input[name="username"]', 'bos_kewer', { delay: 60 });
    await page.type('input[name="password"]', 'password', { delay: 60 });
    await page.type('input[name="confirm_password"]', 'password', { delay: 60 });
    await page.type('input[name="nama"]', 'Hendrik Simanjuntak', { delay: 50 });
    await page.type('input[name="nama_perusahaan"]', 'KSP Kewer Samosir', { delay: 50 });
    await page.type('input[name="email"]', 'bos@kewer.id', { delay: 40 });
    await page.type('input[name="telp"]', '081234560001', { delay: 40 });

    // Alamat wilayah — Provinsi Sumut → Samosir → Pangururan → Rianiate
    await fillWilayah(page,
      WILAYAH.province_id,
      WILAYAH.regency_samosir,
      WILAYAH.district_pangururan,
      WILAYAH.village_pangururan
    );

    await page.type('textarea[name="alamat"]', 'Jl. Gereja No. 12 Pangururan', { delay: 40 });

    await ss(page, 'bos_register_form');
    await page.click('button[type="submit"]');
    await page.waitForNavigation({ waitUntil: 'networkidle2', timeout: 12000 }).catch(() => {});
    await sleep(800);

    const result = await checkAlert(page, 'bos_kewer');
    if (result.success) {
      LA('bos_kewer', 'Pendaftaran bos terkirim — menunggu approval superadmin');
    }
    await ss(page, 'bos_register_result');
  } catch(e) {
    LE('bos_kewer', `Daftar bos: ${e.message}`);
  } finally {
    await page.close();
  }
}

// ─── STEP 2: SUPERADMIN APPROVE BOS ─────────────────────────────
async function superadminApproveBos(browser) {
  log('═══ STEP 2: Superadmin approve pendaftaran bos ═══');
  const page = await browser.newPage();
  try {
    const ok = await loginUser(page, 'patri');
    if (!ok) return;

    await page.goto(`${BASE}/pages/superadmin/bos_approvals.php`, { waitUntil: 'networkidle2', timeout: 12000 });
    await sleep(1000);
    await ss(page, 'superadmin_bos_approvals');

    // Extract CSRF token dan registration_id, lalu submit form langsung
    // (bypass SweetAlert2 karena lebih reliable untuk Puppeteer)
    const approved = await page.evaluate(() => {
      // CSRF ada di inline script: const csrfToken = '...'
      const scripts = Array.from(document.querySelectorAll('script'));
      let csrf = null;
      for (const s of scripts) {
        const m = s.textContent.match(/csrfToken\s*=\s*'([^']+)'/);
        if (m) { csrf = m[1]; break; }
      }
      // registration_id dari onclick="approveRegistration(8)"
      const btn = document.querySelector('button.btn-success');
      const regId = btn ? btn.getAttribute('onclick').match(/\d+/)?.[0] : null;
      if (!csrf || !regId) return { ok: false, reason: `csrf=${csrf} regId=${regId}` };
      const form = document.createElement('form');
      form.method = 'POST';
      form.innerHTML =
        `<input name="csrf_token" value="${csrf}">` +
        `<input name="action" value="approve">` +
        `<input name="registration_id" value="${regId}">`;
      document.body.appendChild(form);
      form.submit();
      return { ok: true, regId };
    });

    if (approved.ok) {
      await page.waitForNavigation({ waitUntil: 'networkidle2', timeout: 15000 }).catch(() => {});
      await sleep(800);
      LS('superadmin', `Pendaftaran bos id=${approved.regId} DISETUJUI ✓`);
    } else {
      LE('superadmin', `Tidak bisa submit form approve: ${approved.reason}`);
    }
    await ss(page, 'superadmin_after_approve');
  } catch(e) {
    LE('superadmin', `Approve bos: ${e.message}`);
  } finally {
    await page.close();
  }
}

// ─── STEP 3: BOS BUAT CABANG ──────────────────────────────────
async function bosCreateCabang(browser) {
  log('═══ STEP 3: Bos buat cabang (HQ + Cabang Balige) ═══');
  const page = await browser.newPage();
  try {
    const ok = await loginUser(page, 'bos_kewer');
    if (!ok) return;

    // Setelah login bos pertama kali, diarahkan ke setup_headquarters
    // Jika tidak otomatis, navigasi manual
    if (!page.url().includes('setup_headquarters') && !page.url().includes('dashboard')) {
      await page.goto(`${BASE}/pages/bos/setup_headquarters.php`, { waitUntil: 'networkidle2', timeout: 12000 });
    }
    // Jika sudah di dashboard (HQ sudah ada), skip ke buat cabang biasa
    if (page.url().includes('dashboard')) {
      log('Bos sudah punya HQ, lanjut buat cabang tambahan');
    } else {
      // ── SETUP HEADQUARTERS ──
      const hqData = {
        kode: 'HQ001', nama: 'Kantor Pusat Pangururan',
        alamat: 'Jl. Sisingamangaraja No. 5 Pangururan',
        prov: WILAYAH.province_id, kab: WILAYAH.regency_samosir,
        kec: WILAYAH.district_pangururan, desa: WILAYAH.village_pangururan,
        telp: '0626-21001', email: 'hq@kewer.id', is_hq: '1', key: 'pangururan',
      };
      LA('bos_kewer', `Setup kantor pusat: ${hqData.nama}`);

      // Pastikan di halaman setup_headquarters
      if (!page.url().includes('setup_headquarters')) {
        await page.goto(`${BASE}/pages/bos/setup_headquarters.php`, { waitUntil: 'networkidle2', timeout: 12000 });
      }
      await sleep(800);

      await fillCabangForm(page, hqData);
      await ss(page, 'bos_hq_form');
      await page.click('button[type="submit"]');
      await page.waitForNavigation({ waitUntil: 'networkidle2', timeout: 15000 }).catch(() => {});
      await sleep(1500); // tunggu redirect 3 detik

      // Tunggu redirect ke dashboard
      if (!page.url().includes('dashboard')) {
        await page.goto(`${BASE}/dashboard.php`, { waitUntil: 'networkidle2', timeout: 12000 });
      }
      await sleep(600);

      const hqId = await getCabangId(page, hqData.nama);
      if (hqId) {
        STATE.cabang.push({ id: hqId, nama: hqData.nama, key: 'pangururan' });
        LS('bos_kewer', `HQ ${hqData.nama} berhasil dibuat (id=${hqId})`);
      }
      await ss(page, 'bos_hq_result');
    }

    // ── BUAT CABANG BALIGE ──
    const cbBalige = {
      kode: 'CB001', nama: 'Cabang Balige',
      alamat: 'Jl. Sutomo No. 18 Balige',
      prov: WILAYAH.province_id, kab: WILAYAH.regency_toba,
      kec: WILAYAH.district_balige, desa: WILAYAH.village_balige,
      telp: '0632-21002', email: 'balige@kewer.id', is_hq: '0', key: 'balige',
    };
    LA('bos_kewer', `Buat cabang: ${cbBalige.nama}`);
    await page.goto(`${BASE}/pages/cabang/tambah.php`, { waitUntil: 'networkidle2', timeout: 12000 });
    await sleep(800);

    await fillCabangForm(page, cbBalige);
    await ss(page, 'bos_cabang_balige_form');
    await page.click('button[type="submit"]');
    await page.waitForNavigation({ waitUntil: 'networkidle2', timeout: 15000 }).catch(() => {});
    await sleep(800);

    const result = await checkAlert(page, 'bos_kewer');
    if (result.success || result.success === null) {
      const blgId = await getCabangId(page, cbBalige.nama);
      if (blgId) {
        STATE.cabang.push({ id: blgId, nama: cbBalige.nama, key: 'balige' });
        LS('bos_kewer', `Cabang ${cbBalige.nama} berhasil (id=${blgId})`);
      }
    }
    await ss(page, 'bos_cabang_balige_result');

    log(`Cabang terdaftar: ${STATE.cabang.map(c => c.nama).join(', ')}`);
  } catch(e) {
    LE('bos_kewer', `Buat cabang: ${e.message}`);
  } finally {
    await page.close();
  }
}

async function fillCabangForm(page, cb) {
  // Tunggu form tersedia
  const formEl = await page.$('form[method="POST"]').catch(() => null);
  if (!formEl) { LE('bos_kewer', 'Form cabang tidak ditemukan'); return; }

  const kodeEl = await page.$('input[name="kode_cabang"]');
  if (kodeEl) { await page.$eval('input[name="kode_cabang"]', e => e.value = ''); await page.type('input[name="kode_cabang"]', cb.kode, { delay: 50 }); }

  await page.$eval('input[name="nama_cabang"]', e => e.value = '');
  await page.type('input[name="nama_cabang"]', cb.nama, { delay: 50 });

  const telpEl = await page.$('input[name="telp"]');
  if (telpEl) await page.type('input[name="telp"]', cb.telp, { delay: 40 });

  const emailEl = await page.$('input[name="email"]');
  if (emailEl) await page.type('input[name="email"]', cb.email, { delay: 40 });

  // Checkbox/radio is_headquarters
  const hqEl = await page.$('input[name="is_headquarters"]');
  if (hqEl && cb.is_hq === '1') await page.evaluate(() => {
    const el = document.querySelector('input[name="is_headquarters"]');
    if (el && !el.checked) el.click();
  });

  // Wilayah
  await fillWilayah(page, cb.prov, cb.kab, cb.kec, cb.desa);

  const alamatEl = await page.$('textarea[name="alamat"]');
  if (alamatEl) await page.type('textarea[name="alamat"]', cb.alamat, { delay: 40 });
}

async function getCabangId(page, namaCabang) {
  try {
    const id = await page.evaluate(async (base, nama) => {
      const r = await fetch(`${base}/api/cabang.php`);
      const data = await r.json();
      const list = data.data || data || [];
      const found = list.find(c => c.nama_cabang && c.nama_cabang.includes(nama.split(' ').pop()));
      return found ? found.id : null;
    }, BASE, namaCabang);
    return id;
  } catch { return null; }
}

// ─── STEP 4: BOS TAMBAH SEMUA STAFF ─────────────────────────────
async function bosCreateAllStaff(browser) {
  log('═══ STEP 4: Bos tambah semua staff via /pages/petugas/tambah.php ═══');
  const page = await browser.newPage();
  try {
    const ok = await loginUser(page, 'bos_kewer');
    if (!ok) return;

    // Ambil daftar cabang dari DB langsung (reliable, tidak bergantung API session)
    const { execSync } = require('child_process');
    const cabangRaw = execSync(
      `/opt/lampp/bin/mysql -u root -proot kewer -e "SELECT id, nama_cabang FROM cabang;" --batch --skip-column-names 2>/dev/null`
    ).toString().trim();

    const cabangMap = {};
    for (const line of cabangRaw.split('\n')) {
      const [id, ...nameParts] = line.split('\t');
      const nama = nameParts.join(' ').trim().toLowerCase();
      if (!id) continue;
      if (nama.includes('balige'))               cabangMap['balige']     = parseInt(id);
      if (nama.includes('pangururan') || nama.includes('pusat')) cabangMap['pangururan'] = parseInt(id);
      // Isi STATE.cabang juga
      if (!STATE.cabang.find(c => c.id === parseInt(id))) {
        STATE.cabang.push({ id: parseInt(id), nama: nameParts.join(' ').trim(), key: nama.includes('balige') ? 'balige' : 'pangururan' });
      }
    }
    log(`CabangMap dari DB: ${JSON.stringify(cabangMap)}`);

    // Tambah setiap user kecuali bos (sudah ada)
    const staffList = ALL_USERS.filter(u => u.role !== 'bos');

    for (const user of staffList) {
      LA('bos_kewer', `Tambah staff: ${user.nama} (${user.role})`);
      await page.goto(`${BASE}/pages/petugas/tambah.php`, { waitUntil: 'networkidle2', timeout: 12000 });
      await sleep(700);

      const formEl = await page.$('form[method="POST"]').catch(() => null);
      if (!formEl) { LE('bos_kewer', `Form tambah petugas tidak tersedia untuk ${user.nama}`); continue; }

      await page.type('input[name="username"]', user.username, { delay: 50 });
      await page.type('input[name="password"]', user.password, { delay: 50 });
      const confirmEl = await page.$('input[name="confirm_password"]');
      if (confirmEl) await page.type('input[name="confirm_password"]', user.password, { delay: 50 });
      await page.type('input[name="nama"]', user.nama, { delay: 50 });
      const emailEl = await page.$('input[name="email"]');
      if (emailEl) await page.type('input[name="email"]', user.email, { delay: 40 });
      const telpEl = await page.$('input[name="telp"]');
      if (telpEl) await page.type('input[name="telp"]', user.telp, { delay: 40 });

      // Pilih role
      await page.select('select[name="role"]', user.role).catch(() => {});
      await sleep(400);

      // Pilih cabang
      const cabangId = user.cabang ? cabangMap[user.cabang] : null;
      if (cabangId) {
        await page.select('select[name="cabang_id"]', String(cabangId)).catch(() => {});
      }

      // Tanggal masuk
      const tglMasukEl = await page.$('input[name="tanggal_masuk"]');
      if (tglMasukEl) {
        await page.evaluate(() => {
          const el = document.querySelector('input[name="tanggal_masuk"]');
          if (el) el.value = '2026-05-01';
        });
      }

      await ss(page, `bos_tambah_${user.username}`);
      await page.click('button[type="submit"]');
      await page.waitForNavigation({ waitUntil: 'networkidle2', timeout: 12000 }).catch(() => {});
      await sleep(700);

      const result = await checkAlert(page, 'bos_kewer');
      if (result.success) {
        STATE.users.push({ ...user, cabang_id: cabangId });
        LS('bos_kewer', `Staff ${user.nama} (${user.role}) berhasil ditambahkan`);
      } else if (result.success === false && result.msg.includes('Username sudah')) {
        STATE.users.push({ ...user, cabang_id: cabangId });
        LA('bos_kewer', `Staff ${user.username} sudah ada, lanjut`);
      }
      await ss(page, `bos_staff_${user.username}_result`);
    }

    LS('bos_kewer', `Total staff berhasil: ${STATE.users.length}`);
  } catch(e) {
    LE('bos_kewer', `Tambah staff: ${e.message}`);
  } finally {
    await page.close();
  }
}

// ─── STEP 5: UPDATE QUICK LOGIN DI HALAMAN LOGIN ──────────────
function updateLoginQuickButtons() {
  log('═══ STEP 5: Update tombol quick login di halaman login ═══');
  const loginFile = path.join(__dirname, '../login.php');
  let content = fs.readFileSync(loginFile, 'utf8');

  // Bangun HTML tombol
  const roleColors = {
    superadmin:    'btn-danger',
    bos:           'btn-dark',
    manager_pusat: 'btn-primary',
    manager_cabang:'btn-info',
    admin_pusat:   'btn-secondary',
    admin_cabang:  'btn-success',
    petugas:       'btn-warning',
    karyawan:      'btn-light border',
  };

  const allUsersForBtn = [
    { username: 'patri',        nama: 'PATRI SIHALOHO',    role: 'superadmin' },
    ...ALL_USERS,
  ];

  const buttons = allUsersForBtn.map(u => {
    const color = roleColors[u.role] || 'btn-secondary';
    const label = u.role.replace(/_/g,' ').toUpperCase();
    return `                <button type="button" class="btn ${color} btn-sm" onclick="quickLogin('${u.username}','password')">`
         + `<strong>${label}</strong>: ${u.nama}</button>`;
  }).join('\n');

  const newBlock = `        <div class="mt-3">
            <p class="text-center text-muted mb-2"><small>⚡ Quick Login Simulasi:</small></p>
            <div class="d-grid gap-1">
${buttons}
            </div>
        </div>`;

  // Ganti blok quick login lama
  content = content.replace(
    /<div class="mt-3">\s*<p class="text-center text-muted mb-2">Quick Login[\s\S]*?<\/div>\s*<\/div>/,
    newBlock
  );

  fs.writeFileSync(loginFile, content, 'utf8');
  LS('system', 'Tombol quick login berhasil diupdate di login.php');
}

// ─── MAIN SETUP ─────────────────────────────────────────────────
async function runSetup() {
  log('');
  log('╔══════════════════════════════════════════════════════╗');
  log('║  KEWER SIMULATION — FASE SETUP                      ║');
  log('║  Ikuti flow aplikasi: Daftar → Approve → Cabang → Staff ║');
  log('╚══════════════════════════════════════════════════════╝');
  log('');

  // Window superadmin di kiri atas
  const browserSA = await newBrowser(0, 0, 960, 680);
  // Window bos di kanan atas
  const browserBos = await newBrowser(960, 0, 960, 680);

  try {
    // 1. Bos daftar
    await registerBos(browserBos);
    await sleep(1000);

    // 2. Superadmin approve
    await superadminApproveBos(browserSA);
    await sleep(1000);

    // 3. Bos login & buat cabang
    await bosCreateCabang(browserBos);
    await sleep(1000);

    // 4. Bos tambah semua staff
    await bosCreateAllStaff(browserBos);
    await sleep(1000);

    // 5. Update quick login buttons
    updateLoginQuickButtons();

    log('');
    log('╔══════════════════════════════════════════════════════╗');
    log('║  SETUP SELESAI — Siap untuk simulasi 2 minggu       ║');
    log('╚══════════════════════════════════════════════════════╝');
    log(`Users dibuat: ${STATE.users.length}`);
    log(`Cabang: ${STATE.cabang.map(c => c.nama).join(', ')}`);

    // Tampilkan dashboard superadmin & bos untuk konfirmasi
    const pgSA  = await browserSA.newPage();
    const pgBos = await browserBos.newPage();
    await loginUser(pgSA, 'patri');
    await loginUser(pgBos, 'bos_kewer');
    await sleep(3000);

  } finally {
    await sleep(5000);
    await browserSA.close();
    await browserBos.close();
  }
}

module.exports = { runSetup, ALL_USERS };

if (require.main === module) {
  runSetup().catch(e => { console.error(e); process.exit(1); });
}
