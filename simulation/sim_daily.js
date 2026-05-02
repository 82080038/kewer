'use strict';
/**
 * FASE 1 — AKTIVITAS HARIAN: 2 minggu simulasi
 * Setiap hari dibagi beberapa sesi jam (pagi/siang/sore)
 * Setiap role = browser window terpisah yang tampil bersamaan
 * Pinjaman: harian, mingguan, bulanan
 */

const path = require('path');
const {
  BASE, STATE, WILAYAH, NAMA_BATAK, LOKASI_PASAR, JENIS_USAHA_IDS,
  log, LA, LE, LS, sleep, randItem, fmtDate, nextSimDay, isWeekend,
  ss, newBrowser, loginUser, fillWilayah, checkAlert,
} = require('./sim_helpers');

// Posisi window per role (4 kolom x 3 baris)
const WIN_POS = {
  superadmin:    { x:   0, y:   0, w: 640, h: 520 },
  bos:           { x: 640, y:   0, w: 640, h: 520 },
  manager_pusat: { x:1280, y:   0, w: 640, h: 520 },
  mgr_pangururan:{ x:   0, y: 520, w: 640, h: 520 },
  mgr_balige:    { x: 640, y: 520, w: 640, h: 520 },
  adm_pusat:     { x:1280, y: 520, w: 640, h: 520 },
  adm_pangururan:{ x:   0, y:1040, w: 640, h: 520 },
  adm_balige:    { x: 640, y:1040, w: 640, h: 520 },
  ptr_pngr1:     { x:1280, y:1040, w: 640, h: 520 },
  ptr_pngr2:     { x:   0, y:1560, w: 640, h: 520 },
  ptr_blg1:      { x: 640, y:1560, w: 640, h: 520 },
  krw_pngr:      { x:1280, y:1560, w: 640, h: 520 },
  krw_blg:       { x:   0, y:2080, w: 640, h: 520 },
};

// KTP pool unik per nasabah (16 digit, mulai dari prefix berbeda per cabang)
let ktpCounter = 1200000000000001;
function nextKTP() { return String(ktpCounter++); }

// Pilih jenis_usaha value dari dropdown (tipe: J001→'warung', J002→'sembako', dsb strip J→strip0→angka)
// Dari DB: value di form = str_replace('J','',strtolower(kode)) misal J001→'001', tapi cek form:
// $kode = str_replace('J', '', strtolower($ju['jenis_kode'])); → '001','002',...
function randJenisUsaha() {
  const codes = ['001','002','003','004','005','006','007','008','009','010'];
  return randItem(codes);
}

// ─── AKTIVITAS PER ROLE ──────────────────────────────────────

// JAM 08:00 — Admin cabang daftar nasabah baru
async function actAdminCabangPagi(page, username, cabangKey, simDate) {
  const cabangId = getCabangIdByKey(cabangKey);
  const idx = Math.floor(Math.random() * NAMA_BATAK.length);
  const nama = NAMA_BATAK[idx];
  const ktp  = nextKTP();
  const telp = `0812${String(80000000 + ktpCounter % 20000000)}`;
  const pasar = randItem(LOKASI_PASAR);

  LA(username, `[08:00] Daftar nasabah baru: ${nama}`);
  await page.goto(`${BASE}/pages/nasabah/tambah.php`, { waitUntil: 'networkidle2', timeout: 15000 });
  await sleep(800);

  try {
    const formEl = await page.$('form[method="POST"]').catch(() => null);
    if (!formEl) { LA(username, 'Form nasabah tidak tersedia (cek permission)'); return null; }

    await page.type('input[name="nama"]', nama, { delay: 50 });
    await page.type('input[name="ktp"]', ktp, { delay: 30 });
    await page.type('input[name="telp"]', telp, { delay: 35 });

    // Wilayah: sesuai cabang
    const wil = cabangKey === 'balige'
      ? { kab: WILAYAH.regency_toba,    kec: WILAYAH.district_balige,      desa: WILAYAH.village_balige }
      : { kab: WILAYAH.regency_samosir, kec: WILAYAH.district_pangururan,   desa: WILAYAH.village_pangururan };

    await fillWilayah(page, WILAYAH.province_id, wil.kab, wil.kec, wil.desa);

    await page.type('textarea[name="alamat"]',
      `Jl. ${randItem(['Merdeka','Sudirman','Pemuda','Sisingamangaraja','Diponegoro'])} No.${Math.floor(Math.random()*100)+1}`,
      { delay: 35 });

    await page.type('input[name="lokasi_pasar"]', pasar, { delay: 35 });

    const jenisEl = await page.$('select[name="jenis_usaha"]');
    if (jenisEl) await page.select('select[name="jenis_usaha"]', randJenisUsaha()).catch(() => {});

    await ss(page, `${username}_nasabah_form`);
    await page.click('button[type="submit"]');
    await page.waitForNavigation({ waitUntil: 'networkidle2', timeout: 12000 }).catch(() => {});
    await sleep(600);

    const result = await checkAlert(page, username);
    if (result.success) {
      // Ambil id nasabah baru
      const nasabahId = await page.evaluate(async (base, cabId) => {
        const r = await fetch(`${base}/api/nasabah.php?cabang_id=${cabId}`);
        const data = await r.json().catch(() => ({}));
        const list = data.data || [];
        return list.length ? list[list.length - 1].id : null;
      }, BASE, cabangId);

      const nasabahData = { id: nasabahId, nama, cabang_id: cabangId, cabangKey, ktp };
      STATE.nasabah.push(nasabahData);
      LS(username, `Nasabah ${nama} (id=${nasabahId}) terdaftar di ${pasar}`);
      await ss(page, `${username}_nasabah_ok`);
      return nasabahData;
    }
    await ss(page, `${username}_nasabah_err`);
    return null;
  } catch(e) {
    LE(username, `Form nasabah: ${e.message}`);
    return null;
  }
}

// JAM 09:00 — Admin input pengajuan pinjaman (harian/mingguan/bulanan)
async function actAdminPusat(page, username, simDate) {
  // Pilih nasabah yang belum punya pinjaman aktif
  const nasabahAktif = STATE.nasabah.filter(n => !STATE.pinjaman.find(p =>
    p.nasabah_id === n.id && ['pengajuan','disetujui','aktif'].includes(p.status)
  ));

  if (nasabahAktif.length === 0) {
    LA(username, '[09:00] Tidak ada nasabah baru untuk pinjaman hari ini');
    // Tetap buka halaman pinjaman untuk review
    await page.goto(`${BASE}/pages/pinjaman/index.php`, { waitUntil: 'networkidle2', timeout: 12000 });
    await sleep(600);
    LA(username, '[09:30] Review portofolio pinjaman aktif');
    await ss(page, `${username}_pinjaman_review`);
    return;
  }

  // Pilih 1-2 nasabah untuk diajukan pinjaman
  const target = nasabahAktif.slice(0, Math.min(2, nasabahAktif.length));

  for (const nsb of target) {
    // Tentukan frekuensi pinjaman secara bergiliran
    const frekuensiList = ['harian', 'mingguan', 'bulanan'];
    const frekuensi = frekuensiList[STATE.simDay % frekuensiList.length];

    // Plafon dan tenor sesuai frekuensi
    const pinjamanConfig = {
      harian:   { plafon: randInt(500000, 3000000, 500000),  tenor: randInt(30, 180, 30),  satuanLabel: 'hari' },
      mingguan: { plafon: randInt(1000000, 5000000, 500000), tenor: randInt(4, 24, 4),     satuanLabel: 'minggu' },
      bulanan:  { plafon: randInt(2000000, 10000000, 500000),tenor: randInt(3, 24, 3),     satuanLabel: 'bulan' },
    };
    const cfg = pinjamanConfig[frekuensi];

    LA(username, `[09:00] Ajukan pinjaman ${frekuensi} untuk ${nsb.nama} — Rp${cfg.plafon.toLocaleString()} ${cfg.tenor} ${cfg.satuanLabel}`);

    await page.goto(`${BASE}/pages/pinjaman/tambah.php`, { waitUntil: 'networkidle2', timeout: 12000 });
    await sleep(1000);

    try {
      const formEl = await page.$('form#loanForm, form[method="POST"]').catch(() => null);
      if (!formEl) { LA(username, 'Form pinjaman tidak tersedia'); continue; }

      // Pilih nasabah
      const opts = await page.$$eval('select[name="nasabah_id"] option',
        els => els.filter(o => o.value && o.value !== '').map(o => ({ v: o.value, t: o.textContent }))
      );
      const found = opts.find(o => o.v == nsb.id || o.t.includes(nsb.nama));
      if (!found) { LA(username, `Nasabah ${nsb.nama} tidak ada di dropdown (cabang berbeda?)`); continue; }

      await page.select('select[name="nasabah_id"]', found.v);
      await sleep(400);

      // Frekuensi
      await page.select('select[name="frekuensi"], #frekuensi', frekuensi).catch(() => {});
      await sleep(300);

      // Plafon — set value langsung karena ada format rupiah
      await page.$eval('#plafon', (el, val) => { el.value = val; el.dispatchEvent(new Event('input')); }, String(cfg.plafon));
      await sleep(300);

      // Tenor
      await page.$eval('#tenor', (el, val) => { el.value = val; el.dispatchEvent(new Event('input')); }, String(cfg.tenor));

      // Bunga
      await page.$eval('#bunga, input[name="bunga_per_bulan"]', el => { el.value = '2'; el.dispatchEvent(new Event('input')); }).catch(() => {});

      // Tanggal akad (set via JS karena flatpickr)
      await page.evaluate((tgl) => {
        const el = document.querySelector('input[name="tanggal_akad"]');
        if (el) { el.value = tgl; el.dispatchEvent(new Event('change')); }
      }, fmtDate(simDate));
      await sleep(300);

      // Tujuan pinjaman
      const tujuan = randItem([
        'Modal usaha warung sembako',
        'Tambah modal dagangan pasar',
        'Beli stok barang jualan',
        'Renovasi kios pasar',
        'Beli alat usaha',
        'Modal awal usaha baru',
      ]);
      const tujEl = await page.$('textarea[name="tujuan_pinjaman"]');
      if (tujEl) await page.type('textarea[name="tujuan_pinjaman"]', tujuan, { delay: 30 });

      // Jaminan
      const jaminanTipe = randItem(['001','002','003']);
      const jaminanEl = await page.$('select[name="jaminan_tipe"]');
      if (jaminanEl) await page.select('select[name="jaminan_tipe"]', jaminanTipe).catch(() => {});
      const jaminanNilai = cfg.plafon * randInt(1, 3, 1);
      const jaminanNilaiEl = await page.$('input[name="jaminan_nilai"]');
      if (jaminanNilaiEl) await page.$eval('input[name="jaminan_nilai"]', (el,v) => el.value=v, String(jaminanNilai));
      const jaminanTxtEl = await page.$('textarea[name="jaminan"]');
      if (jaminanTxtEl) await page.type('textarea[name="jaminan"]', randItem(['BPKB Motor','BPKB Mobil','SHM Tanah','Tabungan']), { delay: 30 });

      await ss(page, `${username}_pinjaman_${frekuensi}_form`);
      await page.click('button[type="submit"]');
      await page.waitForNavigation({ waitUntil: 'networkidle2', timeout: 15000 }).catch(() => {});
      await sleep(700);

      const result = await checkAlert(page, username);
      if (result.success) {
        STATE.pinjaman.push({
          nasabah_id: nsb.id, frekuensi, plafon: cfg.plafon,
          tenor: cfg.tenor, status: 'pengajuan'
        });
        LS(username, `Pinjaman ${frekuensi} Rp${cfg.plafon.toLocaleString()} diajukan untuk ${nsb.nama}`);
      }
      await ss(page, `${username}_pinjaman_${frekuensi}_result`);
    } catch(e) {
      LE(username, `Form pinjaman ${frekuensi}: ${e.message}`);
    }
  }
}

// JAM 10:00 — Manager approve pinjaman pengajuan
async function actManagerApprovePinjaman(page, username) {
  LA(username, '[10:00] Cek & approve pinjaman pengajuan');

  await page.goto(`${BASE}/pages/pinjaman/index.php`, { waitUntil: 'networkidle2', timeout: 12000 });
  await sleep(800);

  try {
    // Cari link/tombol detail pinjaman status pengajuan
    const approveLinks = await page.$$('a[href*="approve"], a[href*="detail"], button[onclick*="approve"]');
    
    // Ambil pinjaman pengajuan via API
    const pendingLoans = await page.evaluate(async (base) => {
      const r = await fetch(`${base}/api/pinjaman.php?status=pengajuan`);
      const text = await r.text();
      try {
        const data = JSON.parse(text);
        return (data.data || []).slice(0, 3);
      } catch { return []; }
    }, BASE);

    if (pendingLoans.length === 0) {
      LA(username, 'Tidak ada pinjaman pengajuan saat ini');
      await ss(page, `${username}_pinjaman_review`);
      return;
    }

    for (const loan of pendingLoans) {
      // Buka halaman detail/approve
      await page.goto(`${BASE}/pages/pinjaman/detail.php?id=${loan.id}`, { waitUntil: 'networkidle2', timeout: 12000 }).catch(() => {});
      await sleep(600);
      await ss(page, `${username}_pinjaman_detail_${loan.id}`);

      // Cari tombol approve di halaman
      const approveBtns = await page.$$('button, a');
      let approved = false;
      for (const btn of approveBtns) {
        const txt = await page.evaluate(el => el.textContent.trim().toLowerCase(), btn);
        if (txt.includes('setuju') || txt.includes('approve') || txt.includes('acc')) {
          await btn.click();
          await page.waitForNavigation({ waitUntil: 'networkidle2', timeout: 10000 }).catch(() => {});
          await sleep(500);
          // Konfirmasi modal jika ada
          const modal = await page.$('.modal.show, .swal2-confirm');
          if (modal) await modal.click();
          await sleep(500);
          LS(username, `Pinjaman ID ${loan.id} (${loan.frekuensi||''} Rp${Number(loan.plafon||0).toLocaleString()}) DISETUJUI`);
          // Update state
          const sp = STATE.pinjaman.find(p => p.nasabah_id == loan.nasabah_id);
          if (sp) sp.status = 'disetujui';
          approved = true;
          break;
        }
      }

      if (!approved) {
        // Coba via API PUT
        const result = await page.evaluate(async (base, id) => {
          const r = await fetch(`${base}/api/pinjaman.php`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id, status: 'disetujui' })
          });
          const text = await r.text();
          try { return JSON.parse(text); } catch { return { raw: text.substring(0, 80) }; }
        }, BASE, loan.id);
        LA(username, `Pinjaman ${loan.id} approve via API → ${result.success ? '✓' : result.raw || result.error || 'cek manual'}`);
      }
    }
  } catch(e) {
    LE(username, `Approve pinjaman: ${e.message}`);
  }
  await ss(page, `${username}_after_approve`);
}

// JAM 08:00-16:00 — Petugas: aktivitas lapangan + kutip angsuran
async function actPetugasHarian(page, username, simDate) {
  const jamList = ['08:00', '09:30', '11:00', '13:00', '14:30', '16:00'];
  const actTypes = ['survey_nasabah', 'kutip_angsuran', 'follow_up', 'promosi', 'edukasi', 'lainnya'];

  for (let i = 0; i < 3; i++) {
    const jam  = jamList[i];
    const tipe = actTypes[(STATE.simDay + i) % actTypes.length];
    const desc = {
      survey_nasabah: 'Survey calon nasabah baru di kawasan pasar',
      kutip_angsuran: 'Kumpul angsuran harian nasabah di lapangan',
      follow_up:      'Follow up nasabah yang belum bayar angsuran',
      promosi:        'Promosi produk pinjaman kepada pedagang pasar',
      edukasi:        'Edukasi nasabah tentang manajemen keuangan usaha',
      lainnya:        'Kunjungan rutin & verifikasi kondisi usaha nasabah',
    }[tipe];

    LA(username, `[${jam}] Aktivitas lapangan: ${tipe}`);

    const result = await page.evaluate(async (base, data) => {
      const r = await fetch(`${base}/api/field_officer_activities.php`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
      });
      const text = await r.text();
      try { return JSON.parse(text); } catch { return { raw: text.substring(0, 100) }; }
    }, BASE, {
      activity_type: tipe,
      description: desc,
      location: randItem(LOKASI_PASAR),
      activity_date: fmtDate(simDate),
      activity_time: jam + ':00',
      status: 'completed'
    });

    if (result.success) {
      LS(username, `[${jam}] Aktivitas ${tipe} tersimpan di DB`);
    } else {
      LE(username, `[${jam}] Aktivitas: ${result.error || result.raw || 'error'}`);
    }
    await sleep(800);

    // Setelah kutip_angsuran, bayar lewat API pembayaran
    if (tipe === 'kutip_angsuran') {
      await actBayarAngsuran(page, username, simDate);
    }
  }

  // Tampilkan halaman aktivitas
  await page.goto(`${BASE}/pages/field_activities/index.php`, { waitUntil: 'networkidle2', timeout: 12000 });
  await sleep(600);
  LA(username, 'Review aktivitas lapangan hari ini');
  await ss(page, `${username}_field_act_hari${STATE.simDay}`);

  // JAM 15:30 — Setoran kas
  await actSetoranKas(page, username, simDate);
}

// Bayar angsuran yang jatuh tempo
async function actBayarAngsuran(page, username, simDate) {
  try {
    const dueList = await page.evaluate(async (base) => {
      const r = await fetch(`${base}/api/angsuran.php?status=belum&limit=3`);
      const text = await r.text();
      try {
        const data = JSON.parse(text);
        return (data.data || []).slice(0, 2);
      } catch { return []; }
    }, BASE);

    if (!dueList.length) {
      LA(username, 'Tidak ada angsuran jatuh tempo — semua current');
      return;
    }

    for (const ang of dueList) {
      if (!ang.id) continue;
      const bayar = Number(ang.total_angsuran) || Number(ang.total_bayar) || 0;
      const result = await page.evaluate(async (base, angId, jumlah) => {
        const r = await fetch(`${base}/api/pembayaran.php`, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ angsuran_id: angId, jumlah_bayar: jumlah, cara_bayar: 'tunai', keterangan: 'Bayar tunai via petugas lapangan' })
        });
        const text = await r.text();
        try { return JSON.parse(text); } catch { return { raw: text.substring(0, 80) }; }
      }, BASE, ang.id, bayar);
      LA(username, `Bayar angsuran ID ${ang.id} Rp${bayar.toLocaleString()} → ${result.success ? '✓ LUNAS' : result.message || result.raw || 'error'}`);
      await sleep(300);
    }
  } catch(e) {
    LE(username, `Bayar angsuran: ${e.message}`);
  }
}

// Setoran kas petugas sore hari
async function actSetoranKas(page, username, simDate) {
  const total = randInt(1, 6, 1) * 500000;
  LA(username, `[15:30] Setoran kas Rp${total.toLocaleString()}`);

  const result = await page.evaluate(async (base, data) => {
    const r = await fetch(`${base}/api/kas_petugas_setoran.php`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(data)
    });
    const text = await r.text();
    try { return JSON.parse(text); } catch { return { raw: text.substring(0, 80) }; }
  }, BASE, {
    tanggal: fmtDate(simDate),
    total_kas_petugas: total,
    total_setoran: total,
    keterangan: `Setoran hari ${STATE.simDay} — angsuran terkumpul dari lapangan`
  });

  if (result.success) {
    LS(username, `Setoran kas Rp${total.toLocaleString()} tersimpan`);
  } else {
    LE(username, `Setoran kas: ${result.error || result.raw}`);
  }

  await page.goto(`${BASE}/pages/kas_petugas/index.php`, { waitUntil: 'networkidle2', timeout: 12000 });
  await sleep(500);
  await ss(page, `${username}_kas_hari${STATE.simDay}`);
}

// JAM 16:30 — Karyawan rekonsiliasi kas harian
async function actKaryawanRekonsiliasi(page, username, simDate) {
  LA(username, '[16:30] Rekonsiliasi kas harian');

  await page.goto(`${BASE}/pages/angsuran/index.php`, { waitUntil: 'networkidle2', timeout: 12000 });
  await sleep(600);
  LA(username, 'Review status angsuran & rekap pembayaran');
  await ss(page, `${username}_angsuran_review_d${STATE.simDay}`);

  await page.goto(`${BASE}/pages/cash_reconciliation/index.php`, { waitUntil: 'networkidle2', timeout: 12000 });
  await sleep(800);
  LA(username, 'Buka halaman rekonsiliasi kas harian');
  await ss(page, `${username}_rekonsiliasi_d${STATE.simDay}`);

  // Coba submit rekonsiliasi jika ada form
  const formEl = await page.$('form[method="POST"]').catch(() => null);
  if (formEl) {
    const totalEl  = await page.$('input[name="total_kas"], input[name="saldo_akhir"]');
    const catatanEl = await page.$('textarea[name="catatan"], textarea[name="keterangan"]');
    if (totalEl) {
      const totalKas = randInt(1, 10, 1) * 1000000;
      await page.$eval('input[name="total_kas"], input[name="saldo_akhir"]', (el,v) => el.value=v, String(totalKas));
    }
    if (catatanEl) await page.type(catatanEl, `Rekonsiliasi hari ${STATE.simDay} — ${fmtDate(simDate)}`, { delay: 30 });
    await page.click('button[type="submit"]').catch(() => {});
    await sleep(500);
  }
  await ss(page, `${username}_rekonsiliasi_submit_d${STATE.simDay}`);
}

// JAM 08:00 — Superadmin: review & maintenance sistem
async function actSuperadmin(page, simDate) {
  LA('patri', `[08:00] Review sistem — ${fmtDate(simDate)}`);
  await page.goto(`${BASE}/dashboard.php`, { waitUntil: 'networkidle2', timeout: 12000 });
  await sleep(600);
  await ss(page, `superadmin_dashboard_d${STATE.simDay}`);

  // Review users
  await page.goto(`${BASE}/pages/petugas/index.php`, { waitUntil: 'networkidle2', timeout: 12000 });
  await sleep(500);
  LA('patri', '[09:00] Review daftar user & petugas aktif');

  // Mingguan: review bos approvals
  if (STATE.simDay % 7 === 0) {
    await page.goto(`${BASE}/pages/superadmin/bos_approvals.php`, { waitUntil: 'networkidle2', timeout: 12000 });
    await sleep(500);
    LA('patri', '[10:00] Review pendaftaran bos mingguan');
    await ss(page, `superadmin_bos_review_d${STATE.simDay}`);
  }
}

// JAM 08:00 — Bos: review bisnis harian
async function actBos(page, simDate) {
  LA('bos_kewer', `[08:00] Review dashboard bisnis — ${fmtDate(simDate)}`);
  await page.goto(`${BASE}/dashboard.php`, { waitUntil: 'networkidle2', timeout: 12000 });
  await sleep(600);
  await ss(page, `bos_dashboard_d${STATE.simDay}`);

  // Review cabang
  await page.goto(`${BASE}/pages/cabang/index.php`, { waitUntil: 'networkidle2', timeout: 12000 });
  await sleep(500);
  LA('bos_kewer', '[09:00] Review performa cabang');
  await ss(page, `bos_cabang_d${STATE.simDay}`);

  // Mingguan: review laporan keuangan
  await page.goto(`${BASE}/pages/pinjaman/index.php`, { waitUntil: 'networkidle2', timeout: 12000 });
  await sleep(500);
  LA('bos_kewer', '[10:00] Monitor portofolio pinjaman semua cabang');
}

// JAM 09:00 — Manager Pusat: monitor operasional lintas cabang
async function actManagerPusat(page, simDate) {
  LA('mgr_pusat', '[09:00] Monitor operasional lintas cabang');
  await page.goto(`${BASE}/pages/pinjaman/index.php`, { waitUntil: 'networkidle2', timeout: 12000 });
  await sleep(500);
  LA('mgr_pusat', 'Review portofolio pinjaman semua cabang');
  await ss(page, `mgr_pusat_pinjaman_d${STATE.simDay}`);

  await page.goto(`${BASE}/pages/kas_petugas/index.php`, { waitUntil: 'networkidle2', timeout: 12000 });
  await sleep(500);
  LA('mgr_pusat', '[10:30] Review & approve setoran kas petugas');
  await ss(page, `mgr_pusat_kas_d${STATE.simDay}`);
}

// ─── HELPERS ─────────────────────────────────────────────────
function getCabangIdByKey(key) {
  const c = STATE.cabang.find(x => x.key === key);
  return c ? c.id : null;
}

function randInt(min, max, step=1) {
  const steps = Math.floor((max - min) / step);
  return min + Math.floor(Math.random() * (steps + 1)) * step;
}

// ─── SIMULASI 1 HARI ─────────────────────────────────────────
async function simulateOneDay(browsers, simDate) {
  const dow = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
  log('');
  log(`╔══════════════════════════════════════════════╗`);
  log(`║  HARI ${STATE.simDay.toString().padStart(2,'0')}/14 — ${dow[simDate.getDay()]} ${fmtDate(simDate)}    ║`);
  log(`╚══════════════════════════════════════════════╝`);

  const weekend = isWeekend(simDate);
  if (weekend) log(`  [WEEKEND — aktivitas terbatas]`);

  // Jalankan semua role secara paralel (sudah login di window masing-masing)
  const tasks = [];

  // Superadmin — selalu aktif kecuali Minggu
  if (simDate.getDay() !== 0) {
    tasks.push(actSuperadmin(browsers.patri.page, simDate));
  }

  // Bos — Senin-Sabtu
  if (!weekend || simDate.getDay() === 6) {
    tasks.push(actBos(browsers.bos_kewer.page, simDate));
  }

  if (!weekend) {
    // Manager Pusat
    tasks.push(actManagerPusat(browsers.mgr_pusat.page, simDate));

    // Manager Cabang — approve pinjaman
    tasks.push(actManagerApprovePinjaman(browsers.mgr_pangururan.page, 'mgr_pangururan'));
    tasks.push(actManagerApprovePinjaman(browsers.mgr_balige.page, 'mgr_balige'));

    // Admin Cabang — daftar nasabah (pagi)
    tasks.push(actAdminCabangPagi(browsers.adm_pangururan.page, 'adm_pangururan', 'pangururan', simDate));
    tasks.push(actAdminCabangPagi(browsers.adm_balige.page, 'adm_balige', 'balige', simDate));

    // Admin Pusat — input pinjaman
    tasks.push(actAdminPusat(browsers.adm_pusat.page, 'adm_pusat', simDate));

    // Petugas — aktivitas lapangan seharian
    tasks.push(actPetugasHarian(browsers.ptr_pngr1.page, 'ptr_pngr1', simDate));
    tasks.push(actPetugasHarian(browsers.ptr_pngr2.page, 'ptr_pngr2', simDate));
    tasks.push(actPetugasHarian(browsers.ptr_blg1.page, 'ptr_blg1', simDate));

    // Karyawan — rekonsiliasi sore
    tasks.push(actKaryawanRekonsiliasi(browsers.krw_pngr.page, 'krw_pngr', simDate));
    tasks.push(actKaryawanRekonsiliasi(browsers.krw_blg.page, 'krw_blg', simDate));
  } else {
    // Sabtu: hanya petugas & bos
    if (simDate.getDay() === 6) {
      tasks.push(actPetugasHarian(browsers.ptr_pngr1.page, 'ptr_pngr1', simDate));
      tasks.push(actPetugasHarian(browsers.ptr_blg1.page, 'ptr_blg1', simDate));
    }
  }

  // Jalankan semua paralel, tapi jangan biarkan error satu role stop yang lain
  const results = await Promise.allSettled(tasks);
  const errors = results.filter(r => r.status === 'rejected');
  if (errors.length) {
    errors.forEach(e => LE('system', `Task error: ${e.reason?.message || e.reason}`));
  }

  log(`  Hari ${STATE.simDay} selesai | Nasabah: ${STATE.nasabah.length} | Pinjaman: ${STATE.pinjaman.length}`);
}

// ─── BUKA SEMUA BROWSER WINDOW ───────────────────────────────
async function openAllBrowsers() {
  log('Membuka browser window untuk semua role...');
  const browsers = {};
  const userList = [
    { key: 'patri',        ...WIN_POS.superadmin },
    { key: 'bos_kewer',    ...WIN_POS.bos },
    { key: 'mgr_pusat',    ...WIN_POS.manager_pusat },
    { key: 'mgr_pangururan', ...WIN_POS.mgr_pangururan },
    { key: 'mgr_balige',   ...WIN_POS.mgr_balige },
    { key: 'adm_pusat',    ...WIN_POS.adm_pusat },
    { key: 'adm_pangururan', ...WIN_POS.adm_pangururan },
    { key: 'adm_balige',   ...WIN_POS.adm_balige },
    { key: 'ptr_pngr1',    ...WIN_POS.ptr_pngr1 },
    { key: 'ptr_pngr2',    ...WIN_POS.ptr_pngr2 },
    { key: 'ptr_blg1',     ...WIN_POS.ptr_blg1 },
    { key: 'krw_pngr',     ...WIN_POS.krw_pngr },
    { key: 'krw_blg',      ...WIN_POS.krw_blg },
  ];

  for (const u of userList) {
    log(`  Buka window: ${u.key} @ [${u.x},${u.y}]`);
    const browser = await newBrowser(u.x, u.y, u.w, u.h);
    const page    = await browser.newPage();
    const ok      = await loginUser(page, u.key);
    if (ok) {
      browsers[u.key] = { browser, page };
      LA(u.key, 'Window terbuka & login berhasil');
    } else {
      LE(u.key, 'Login gagal — window tidak akan dipakai');
      await browser.close();
    }
    await sleep(500); // jangan spawn semua sekaligus
  }
  return browsers;
}

async function closeAllBrowsers(browsers) {
  for (const [key, { browser }] of Object.entries(browsers)) {
    await browser.close().catch(() => {});
  }
}

// ─── MAIN SIMULASI 2 MINGGU ──────────────────────────────────
async function runSimulation() {
  log('');
  log('╔═══════════════════════════════════════════════════════╗');
  log('║  KEWER SIMULATION — 2 MINGGU (14 HARI)               ║');
  log('║  13 role × 14 hari = aktivitas nyata terpisah        ║');
  log('╚═══════════════════════════════════════════════════════╝');
  log('');

  // Load state cabang dari DB
  await loadCabangFromDB();

  // Buka semua browser window
  const browsers = await openAllBrowsers();
  log(`Browser aktif: ${Object.keys(browsers).length}`);

  try {
    for (let day = 1; day <= 14; day++) {
      nextSimDay();
      await simulateOneDay(browsers, STATE.simDate);
      // Jeda antar hari
      await sleep(4000);
    }

    log('');
    log('╔═══════════════════════════════════════════════════════╗');
    log('║  SIMULASI 2 MINGGU SELESAI                           ║');
    log(`║  Total nasabah: ${String(STATE.nasabah.length).padEnd(4)} | Pinjaman: ${String(STATE.pinjaman.length).padEnd(4)}           ║`);
    log('╚═══════════════════════════════════════════════════════╝');

    // Tahan semua browser untuk verifikasi
    await sleep(15000);
  } finally {
    await closeAllBrowsers(browsers);
  }
}

async function loadCabangFromDB() {
  // Query DB untuk isi STATE.cabang
  const { execSync } = require('child_process');
  try {
    const out = execSync(`/opt/lampp/bin/mysql -u root -proot kewer -e "SELECT id, nama_cabang FROM cabang;" --batch --skip-column-names 2>/dev/null`).toString();
    for (const line of out.trim().split('\n')) {
      const parts = line.split('\t');
      if (parts.length >= 2) {
        const id = parseInt(parts[0]);
        const nama = parts[1].trim();
        const key = nama.toLowerCase().includes('balige') ? 'balige' : 'pangururan';
        if (!STATE.cabang.find(c => c.id === id)) {
          STATE.cabang.push({ id, nama, key });
        }
      }
    }
    log(`Cabang loaded dari DB: ${STATE.cabang.map(c => `${c.nama}(id=${c.id})`).join(', ')}`);
  } catch(e) {
    log(`WARNING: Tidak bisa load cabang dari DB: ${e.message}`, 'WARN');
  }
}

module.exports = { runSimulation };

if (require.main === module) {
  runSimulation().catch(e => { console.error(e); process.exit(1); });
}
