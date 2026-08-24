const puppeteer = require('puppeteer-core');
const path = require('path');
const fs = require('fs');
const sleep = ms => new Promise(r => setTimeout(r, ms));

const BASE = 'http://localhost:8080';
const OUT = path.join(__dirname, 'screenshots');
const CHROME = '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome';
const DESK = { width: 1280, height: 800 };
const MOB  = { width: 390, height: 844, isMobile: true, deviceScaleFactor: 2 };
if (!fs.existsSync(OUT)) fs.mkdirSync(OUT, { recursive: true });

async function goto(page, url) {
  await page.goto(`${BASE}/${url}`, { waitUntil: 'networkidle2', timeout: 25000 });
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
async function login(page) {
  const client = await page.createCDPSession();
  await client.send('Network.clearBrowserCookies');
  await page.goto(`${BASE}/login`, { waitUntil: 'domcontentloaded', timeout: 20000 });
  await sleep(400);
  await page.type('#email', 'admin@wbl.co.id');
  await page.type('#password', 'wbl12345');
  await Promise.all([
    page.waitForNavigation({ waitUntil: 'networkidle2', timeout: 20000 }),
    page.click('button[type="submit"]'),
  ]);
  await sleep(900);
}

(async () => {
  const browser = await puppeteer.launch({ executablePath: CHROME, headless: true, args: ['--no-sandbox', '--disable-setuid-sandbox'] });

  // ===== PUBLIK + LOGIN (tanpa sesi) =====
  const pub = await browser.newPage();
  await pub.setViewport(DESK);
  const sp = shot(pub);
  console.log('Publik & login:');
  await goto(pub, 'karir');                 await sp('ats_karir');
  await goto(pub, 'karir/supervisor-housekeeping-ptc-2608-01'); await sp('ats_lowongan_detail_publik');
  await goto(pub, 'karir/supervisor-housekeeping-ptc-2608-01/lamar'); await sp('ats_lamar');
  await goto(pub, 'lacak');                  await sp('ats_lacak_form');
  // lacak hasil: isi form & submit
  await pub.type('#ref', 'WBL-2026-000012');
  await pub.type('#email', 'rizky.ramadhan@example.com');
  await Promise.all([ pub.waitForNavigation({ waitUntil: 'networkidle2', timeout: 20000 }), pub.click('button[type="submit"]') ]);
  await sleep(800); await sp('ats_lacak_hasil');
  await goto(pub, 'login');                  await sp('ats_login');
  await pub.close();

  // ===== HR (login) =====
  const pg = await browser.newPage();
  await pg.setViewport(DESK);
  await login(pg);
  const s = shot(pg);
  console.log('Area HR:');
  await goto(pg, 'dashboard');               await s('ats_dashboard');
  await goto(pg, 'lowongan');                await s('ats_lowongan_list');
  await goto(pg, 'lowongan/baru');           await s('ats_lowongan_form');
  await goto(pg, 'lowongan/6');              await s('ats_lowongan_show');
  await goto(pg, 'pipeline');                await s('ats_pipeline');
  await goto(pg, 'unggulan');                await s('ats_unggulan');
  await goto(pg, 'pelamar');                 await s('ats_pelamar_list');
  await goto(pg, 'pelamar/17');              await s('ats_pelamar_detail');
  await goto(pg, 'interview/baru/17');       await s('ats_interview_form');
  await goto(pg, 'interview');               await s('ats_interview_list');
  await goto(pg, 'interview/1/nilai');       await s('ats_penilaian');
  await goto(pg, 'kandidat');                await s('ats_kandidat');
  await goto(pg, 'email');                   await s('ats_email');
  await goto(pg, 'email/template');          await s('ats_email_template');
  await goto(pg, 'laporan');                 await s('ats_laporan');
  await goto(pg, 'master/unit');             await s('ats_master_unit');
  await goto(pg, 'master/departemen');       await s('ats_master_departemen');
  await goto(pg, 'master/tahapan');          await s('ats_master_tahapan');
  await goto(pg, 'master/pengguna');         await s('ats_master_pengguna');
  await goto(pg, 'pengaturan');              await s('ats_pengaturan');
  const cookies = await pg.cookies();
  await pg.close();

  // ===== MOBILE (login page saja, tanpa login) =====
  const pgm = await browser.newPage();
  await pgm.setViewport(MOB);
  const sm = shot(pgm);
  console.log('Mobile:');
  await goto(pgm, 'login');                  await sm('mob_ats_login');
  await pgm.close();

  await browser.close();
  console.log('\n✅ Screenshot selesai');
})();
