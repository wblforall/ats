const puppeteer = require('puppeteer-core');
const path = require('path');
const fs = require('fs');
const { execFileSync } = require('child_process');
const sleep = ms => new Promise(r => setTimeout(r, ms));

const cfg = require('./config.json');
const BASE = cfg.baseUrl;
const IDS = cfg.recordIds;
const OUT = path.join(__dirname, 'screenshots');
const CHROME = cfg.chromePath;
const DESK = { width: 1280, height: 800 };
const MOB  = { width: 390, height: 844, isMobile: true, deviceScaleFactor: 2 };
if (!fs.existsSync(OUT)) fs.mkdirSync(OUT, { recursive: true });

/* Baca database lokal (XAMPP) — dipakai untuk mengambil tautan jawaban
   interview dari email di antrean, persis seperti yang diterima pelamar. */
const sql = q => execFileSync(cfg.mysql.bin, ['-u' + cfg.mysql.user, cfg.mysql.db, '-N', '-e', q]).toString().trim();
const linkFromOutbox = interviewId => {
  const body = sql(`select body from email_outbox where template_code='interview_invitation' and body like '%jadwal-interview/${interviewId}/%' order by id desc limit 1`);
  const m = body.match(new RegExp(`${BASE.replace(/[.:/]/g, '\\$&')}/jadwal-interview/${interviewId}/[a-f0-9]+`));
  if (!m) throw new Error('Tautan jawaban interview #' + interviewId + ' tidak ditemukan di email_outbox');
  return m[0];
};

async function goto(page, url) {
  await page.goto(url.startsWith('http') ? url : `${BASE}/${url}`, { waitUntil: 'networkidle2', timeout: 25000 });
  // Ikon debug toolbar CodeIgniter hanya ada di lokal (development), bukan bagian aplikasi.
  await page.addStyleTag({ content: '#debug-icon, #debug-bar, #toolbarContainer { display: none !important; }' }).catch(() => {});
  await sleep(900);
}
function shot(page) {
  return async (name, scrollY) => {
    if (scrollY !== undefined) { await page.evaluate(y => window.scrollTo(0, y), scrollY); await sleep(500); }
    else { await page.evaluate(() => window.scrollTo(0, 0)); await sleep(200); }
    await page.screenshot({ path: path.join(OUT, `${name}.png`), fullPage: false });
    console.log('  ✓', name);
  };
}
async function shotEl(page, name, selector) {
  const el = await page.$(selector);
  await el.scrollIntoView(); await sleep(300);
  await el.screenshot({ path: path.join(OUT, `${name}.png`) });
  console.log('  ✓', name);
}
async function login(page) {
  const client = await page.createCDPSession();
  await client.send('Network.clearBrowserCookies');
  await page.goto(`${BASE}/login`, { waitUntil: 'domcontentloaded', timeout: 20000 });
  await sleep(400);
  await page.type(cfg.login.fieldEmail, cfg.login.email);
  await page.type(cfg.login.fieldPassword, cfg.login.password);
  await Promise.all([
    page.waitForNavigation({ waitUntil: 'networkidle2', timeout: 20000 }),
    page.click(cfg.login.submit),
  ]);
  await sleep(900);
}
async function submit(page, selector) {
  await Promise.all([page.waitForNavigation({ waitUntil: 'networkidle2', timeout: 20000 }), page.click(selector)]);
  await sleep(700);
}
/* Halaman publik: animasi loading hanya muncul sekali per sesi; lewati. */
async function noLoader(page) {
  await page.evaluateOnNewDocument(() => { try { sessionStorage.setItem('wblLoader', '1'); } catch (e) {} });
}

(async () => {
  const browser = await puppeteer.launch({ executablePath: CHROME, headless: true, args: ['--no-sandbox', '--disable-setuid-sandbox'] });

  // ===== PUBLIK + LOGIN (tanpa sesi) =====
  const pub = await browser.newPage();
  await pub.setViewport(DESK);
  await noLoader(pub);
  const sp = shot(pub);
  console.log('Publik & login:');
  await goto(pub, 'karir');                                   await sp('ats_karir');
  await goto(pub, `karir/${IDS.publicJobSlug}`);              await sp('ats_lowongan_detail_publik');
  await goto(pub, `karir/${IDS.publicJobSlug}/lamar`);        await sp('ats_lamar');
  await goto(pub, 'lacak');                                   await sp('ats_lacak_form');
  await pub.type('#ref', IDS.trackRef);
  await pub.type('#email', IDS.trackEmail);
  await submit(pub, 'button[type="submit"]');                 await sp('ats_lacak_hasil');
  await goto(pub, 'login');                                   await sp('ats_login');
  await goto(pub, 'lupa-sandi');                              await sp('ats_lupa_sandi');
  await pub.close();

  // ===== HR (login) =====
  const pg = await browser.newPage();
  await pg.setViewport(DESK);
  await login(pg);
  const s = shot(pg);
  console.log('Area HR:');

  /* Siapkan jawaban pelamar lewat alur sebenarnya: HR mengirim undangan
     (centang "Kirim undangan baru" di form ubah jadwal), lalu pelamar
     membuka tautan dari email itu. */
  for (const id of [IDS.interviewRescheduleId, IDS.interviewConfirmId]) {
    await goto(pg, `interview/${id}/ubah`);
    await pg.$eval('input[name="kirim_undangan"]', e => { e.checked = true; });
    await submit(pg, 'form[action*="/ubah"] button[type="submit"]');
  }
  const linkUlang = linkFromOutbox(IDS.interviewRescheduleId);
  const linkHadir = linkFromOutbox(IDS.interviewConfirmId);

  /* Pelamar tidak login: pakai konteks browser terpisah tanpa sesi HR,
     supaya header publik menampilkan "Masuk", bukan "Dashboard". */
  const tamu = await browser.createBrowserContext();
  const pel = await tamu.newPage();
  await pel.setViewport(DESK);
  await noLoader(pel);
  const spl = shot(pel);
  await goto(pel, linkUlang + '?aksi=hadir');                 await spl('ats_jawab_interview');
  await goto(pel, linkUlang + '?aksi=ulang');
  await shotEl(pel, 'ats_jawab_ulang_form', '#ulang');
  await pel.$eval('#tanggal', (e, v) => { e.value = v; }, IDS.rescheduleProposedDate);
  await pel.$eval('#jam', e => { e.value = '14:00'; });
  await pel.type('#alasan', 'Bentrok dengan ujian akhir semester di kampus.');
  await submit(pel, '#ulang button[type="submit"]');          await spl('ats_jawab_ulang_terkirim');
  await goto(pel, linkHadir);
  await submit(pel, '#hadir button[type="submit"]');          await spl('ats_jawab_hadir_terkirim');
  await goto(pel, linkHadir + '?aksi=batal');
  await shotEl(pel, 'ats_jawab_batal_form', '#batal');
  /* Email undangan bertombol, dirender dari isi email di antrean. */
  const body = sql(`select body from email_outbox where template_code='interview_invitation' order by id desc limit 1`).replace(/\\n/g, '\n');
  await pel.setViewport({ width: 720, height: 900 });
  await pel.setContent(`<div style="font-family:Arial,sans-serif;font-size:14px;line-height:1.5;padding:24px 28px;max-width:640px;color:#1f2937">${body}</div>`);
  await sleep(300);
  await pel.screenshot({ path: path.join(OUT, 'ats_email_undangan.png'), fullPage: true });
  console.log('  ✓ ats_email_undangan');
  await pel.close();

  await goto(pg, 'dashboard');                                await s('ats_dashboard');
  await shotEl(pg, 'ats_dashboard_jawaban', '.grid-wide > div:last-child');
  await goto(pg, 'lowongan');                                 await s('ats_lowongan_list');
  await goto(pg, 'lowongan/baru');                            await s('ats_lowongan_form');
  await goto(pg, `lowongan/${IDS.jobShowId}`);                await s('ats_lowongan_show');
  await goto(pg, 'pipeline');                                 await s('ats_pipeline');
  await goto(pg, 'unggulan');                                 await s('ats_unggulan');
  await goto(pg, 'pelamar');                                  await s('ats_pelamar_list');
  await goto(pg, `pelamar/${IDS.applicantDetailId}`);         await s('ats_pelamar_detail');
  await shotEl(pg, 'ats_pelamar_jadwal_ulang', '#interview');
  await goto(pg, `interview/baru/${IDS.applicantDetailId}`);  await s('ats_interview_form');
  await goto(pg, `interview/${IDS.interviewRescheduleId}/ubah?usulan=1`); await s('ats_interview_ubah_usulan');
  await goto(pg, 'interview?from=' + IDS.agendaFrom);         await s('ats_interview_list');
  await goto(pg, `interview/${IDS.interviewEvaluateId}/nilai`); await s('ats_penilaian');
  await goto(pg, 'kandidat');                                 await s('ats_kandidat');
  await goto(pg, 'email');                                    await s('ats_email');
  await goto(pg, 'email/template');                           await s('ats_email_template');
  await goto(pg, 'laporan');                                  await s('ats_laporan');
  await goto(pg, 'master/unit');                              await s('ats_master_unit');
  await goto(pg, 'master/departemen');                        await s('ats_master_departemen');
  await goto(pg, 'master/tahapan');                           await s('ats_master_tahapan');
  await goto(pg, 'master/pengguna');                          await s('ats_master_pengguna');
  await goto(pg, 'pengaturan');                               await s('ats_pengaturan');
  const cookies = await pg.cookies();
  await pg.close();

  // ===== MOBILE — cookie dari sesi desktop, tanpa login ulang =====
  const pgm = await browser.newPage();
  await pgm.setViewport(MOB);
  await pgm.setCookie(...cookies);
  const sm = shot(pgm);
  console.log('Mobile:');
  await goto(pgm, 'dashboard');                               await sm('mob_ats_dashboard');
  await goto(pgm, `pelamar/${IDS.applicantDetailId}`);
  await shotEl(pgm, 'mob_ats_pelamar_jadwal_ulang', '#interview');
  await pgm.close();

  const pubm = await tamu.newPage();
  await pubm.setViewport(MOB);
  await noLoader(pubm);
  const spm = shot(pubm);
  await goto(pubm, 'karir');                                  await spm('mob_ats_karir');
  await goto(pubm, linkHadir);                                await spm('mob_ats_jawab_interview');
  await pubm.close();

  await browser.close();
  console.log('\n✅ Screenshot selesai');
})();
