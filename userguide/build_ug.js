const puppeteer = require('puppeteer-core');
const { PDFDocument } = require('pdf-lib');
const path = require('path');
const fs = require('fs');

const CHROME = '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome';
const PROJ = path.join(__dirname, '..');
const SS = path.join(__dirname, 'screenshots');
const OUT_PDF = path.join(PROJ, 'docs', 'Panduan_Pengguna_WBL_ATS.pdf');
const LOGO_STROKE = path.join(PROJ, 'public/assets/img/logo-wbl-stroke.png');

function b64(file) {
  const p = path.join(SS, file + '.png');
  if (!fs.existsSync(p)) { console.warn('  ! screenshot hilang:', file); return ''; }
  return 'data:image/png;base64,' + fs.readFileSync(p).toString('base64');
}
global.ssOnly = (f, alt) => `<figure class="ss"><img src="${b64(f)}" alt="${alt||''}"><figcaption>${alt||''}</figcaption></figure>`;
global.ss2 = (a, b, aa, ab) => `<div class="ss-row"><figure class="ss"><img src="${b64(a)}" alt="${aa||''}"><figcaption>${aa||''}</figcaption></figure><figure class="ss"><img src="${b64(b)}" alt="${ab||''}"><figcaption>${ab||''}</figcaption></figure></div>`;
global.ssPair = (d, m, ad, am) => `<div class="ss-row"><figure class="ss ss-desk"><img src="${b64(d)}" alt="${ad||''}"><figcaption>${ad||''}</figcaption></figure><figure class="ss ss-mob"><img src="${b64(m)}" alt="${am||''}"><figcaption>${am||''}</figcaption></figure></div>`;

const { atsContent } = require(path.join(__dirname, 'ats.js'));
const logoStroke = 'data:image/png;base64,' + fs.readFileSync(LOGO_STROKE).toString('base64');
const tanggal = new Date().toLocaleDateString('id-ID', { day:'numeric', month:'long', year:'numeric' });

// ---------- COVER (full-bleed, halaman terpisah, margin 0) ----------
const COVER_HTML = `<!DOCTYPE html><html lang="id"><head><meta charset="utf-8"><style>
@page { size: A4; margin: 0; }
* { box-sizing: border-box; margin: 0; }
body { font-family: -apple-system, "Segoe UI", Roboto, Arial, sans-serif; }
.cover {
  position: relative; width: 210mm; height: 297mm; overflow: hidden; color: #fff;
  background:
    radial-gradient(120% 80% at 82% -8%, rgba(216,154,45,.16), transparent 58%),
    linear-gradient(160deg, #10233F 0%, #0B1A30 55%, #0A1526 100%);
}
.cover::before { content:""; position:absolute; inset:0;
  background-image: radial-gradient(rgba(157,182,214,.10) 1.3px, transparent 1.4px);
  background-size: 30px 30px; }

.net { position:absolute; top:-40px; right:-50px; width:540px; height:540px; z-index:1; opacity:.95; }
.inner { position:relative; z-index:2; height:100%; display:flex; flex-direction:column; padding: 30mm 24mm; }
.tag { font-size:10px; letter-spacing:3px; text-transform:uppercase; color:#E0A82E; font-weight:700; }
.tag .d { display:inline-block; width:26px; height:2px; background:#E0A82E; vertical-align:middle; margin-right:10px; }
.plate { width:230px; margin:38px 0 auto; }
.plate img { width:100%; display:block; }
.title { font-size:54px; line-height:1.02; font-weight:800; letter-spacing:-1px; }
.bar { width:92px; height:5px; background:#E0A82E; border-radius:3px; margin:20px 0; }
.sys { font-size:13px; letter-spacing:2px; text-transform:uppercase; color:#9DB6D6; font-weight:600; }
.foot { margin-top:34px; display:flex; justify-content:space-between; align-items:flex-end; border-top:1px solid rgba(157,182,214,.22); padding-top:16px; }
.foot .co { font-size:13px; font-weight:700; color:#fff; }
.foot .co small { display:block; font-weight:400; color:#8FA6C4; font-size:10.5px; margin-top:2px; }
.foot .ver { font-size:10.5px; color:#E0A82E; letter-spacing:1px; text-align:right; text-transform:uppercase; }
.credit { margin-top:14px; font-size:9.5px; color:#8FA6C4; line-height:1.7; }
.credit b { color:#C9D6E8; }
.credit .cr { color:#6E86A6; letter-spacing:.3px; }
.brace { position:absolute; width:34px; height:34px; border:2px solid rgba(216,154,45,.55); z-index:2; }
.brace.tl { top:15mm; left:15mm; border-right:0; border-bottom:0; }
.brace.br { bottom:15mm; right:15mm; border-left:0; border-top:0; }
</style></head><body>
<div class="cover">
  <span class="brace tl"></span><span class="brace br"></span>
    <svg class="net" viewBox="0 0 420 420" fill="none" xmlns="http://www.w3.org/2000/svg">
      <!-- garis jaringan -->
      <g stroke="#9DB6D6" stroke-opacity=".30" stroke-width="1.1">
        <line x1="95" y1="70" x2="205" y2="48"/><line x1="205" y1="48" x2="315" y2="92"/>
        <line x1="95" y1="70" x2="150" y2="165"/><line x1="205" y1="48" x2="255" y2="180"/>
        <line x1="315" y1="92" x2="360" y2="205"/><line x1="150" y1="165" x2="255" y2="180"/>
        <line x1="255" y1="180" x2="360" y2="205"/><line x1="150" y1="165" x2="135" y2="285"/>
        <line x1="255" y1="180" x2="290" y2="300"/><line x1="135" y1="285" x2="290" y2="300"/>
        <line x1="205" y1="48" x2="150" y2="165"/><line x1="60" y1="150" x2="150" y2="165"/>
        <line x1="60" y1="150" x2="95" y2="70"/><line x1="290" y1="300" x2="360" y2="205"/>
        <line x1="135" y1="285" x2="215" y2="335"/><line x1="215" y1="335" x2="290" y2="300"/>
      </g>
      <g stroke="#E0A82E" stroke-opacity=".45" stroke-width="1.4">
        <line x1="315" y1="92" x2="255" y2="180"/><line x1="150" y1="165" x2="95" y2="70"/>
        <line x1="255" y1="180" x2="215" y2="335"/>
      </g>
      <!-- node kecil -->
      <g fill="#9DB6D6" fill-opacity=".55">
        <circle cx="60" cy="150" r="4"/><circle cx="360" cy="205" r="4.5"/><circle cx="215" cy="335" r="4"/>
      </g>
      <g fill="#E0A82E" fill-opacity=".9"><circle cx="135" cy="285" r="5.5"/><circle cx="315" cy="92" r="5"/></g>
      <!-- figur orang (kepala + bahu) -->
      <g fill="#DCE6F4" fill-opacity=".95">
        <g transform="translate(205,48) scale(1.25)"><circle cx="0" cy="-4" r="7"/><path d="M-11 13 a11 11 0 0 1 22 0 z"/></g>
        <g transform="translate(255,180) scale(1.35)"><circle cx="0" cy="-4" r="7"/><path d="M-11 13 a11 11 0 0 1 22 0 z"/></g>
        <g transform="translate(150,165) scale(1.15)"><circle cx="0" cy="-4" r="7"/><path d="M-11 13 a11 11 0 0 1 22 0 z"/></g>
        <g transform="translate(95,70) scale(1.0)"><circle cx="0" cy="-4" r="7"/><path d="M-11 13 a11 11 0 0 1 22 0 z"/></g>
        <g transform="translate(290,300) scale(1.1)"><circle cx="0" cy="-4" r="7"/><path d="M-11 13 a11 11 0 0 1 22 0 z"/></g>
      </g>
      <!-- figur orang aksen emas -->
      <g fill="#E7B24A" fill-opacity=".92">
        <g transform="translate(255,180) scale(0.0)"></g>
      </g>
    </svg>
  <div class="inner">
    <div class="tag"><span class="d"></span>PT. Wulandari Bangun Laksana Tbk</div>
    <div class="plate"><img src="${logoStroke}" alt="WBL"></div>
    <h1 class="title">PANDUAN<br>PENGGUNA</h1>
    <div class="bar"></div>
    <div class="sys">WBL ATS &middot; Applicant Tracking System</div>
    <div class="foot">
      <div class="co">Sistem Rekrutmen<small>Dokumentasi resmi untuk tim HRD</small></div>
      <div class="ver">Versi 1.1<br>${tanggal}</div>
    </div>
    <div class="credit">
      <div>Dikembangkan oleh <b>Ahmad Affan Ridha</b> &middot; Deputy Business &amp; People Development</div>
      <div class="cr">Hak Cipta &copy; ${new Date().getFullYear()} PT. Nusantara Solusi Teknologi</div>
    </div>
  </div>
</div></body></html>`;

// ---------- ISI (bermargin, dengan footer) ----------
const CONTENT_CSS = `
* { box-sizing: border-box; }
body { font-family:-apple-system,"Segoe UI",Roboto,Arial,sans-serif; color:#1F2937; font-size:11.5px; line-height:1.6; margin:0; }
h2 { page-break-before: always; display:flex; align-items:center; gap:10px; font-size:18px; color:#153C7F; margin:0 0 4px; }
h2:first-of-type { page-break-before: avoid; }
h2 .num { width:30px; height:30px; flex:0 0 30px; background:#153C7F; color:#E5B652; border-radius:8px; display:flex; align-items:center; justify-content:center; font-size:15px; font-weight:700; }
h3 { font-size:13.5px; color:#0B1F45; margin:18px 0 6px; }
.rule { height:3px; background:linear-gradient(to right,#C89023,#F3E3BF); border-radius:3px; margin:4px 0 12px; }
p { margin:0 0 10px; }
table { width:100%; border-collapse:collapse; margin:10px 0 14px; font-size:10.8px; page-break-inside:avoid; }
th { background:#F3F6FB; color:#475569; text-align:left; padding:7px 10px; border:1px solid #E6E8F0; font-size:10px; text-transform:uppercase; letter-spacing:.4px; }
td { padding:7px 10px; border:1px solid #E6E8F0; vertical-align:top; }
.callout { background:#EEF2F9; border-left:3px solid #153C7F; border-radius:6px; padding:10px 13px; margin:12px 0; font-size:11px; }
.callout.warn { background:#FEF3E2; border-left-color:#F59E0B; } .callout.ok { background:#E7F6EC; border-left-color:#16A34A; }
.b { display:inline-block; padding:1px 8px; border-radius:999px; font-size:10px; font-weight:700; }
.b-g{background:#E7F6EC;color:#15803D}.b-y{background:#FEF3E2;color:#B45309}.b-r{background:#FDE7E7;color:#B91C1C}.b-b{background:#E8EDF7;color:#153C7F}.b-gray{background:#EEF0F4;color:#64748B}
.btn-ref{display:inline-block;background:#153C7F;color:#fff;padding:1px 9px;border-radius:6px;font-size:10.5px;font-weight:600}
.btn-ref-outline{display:inline-block;border:1px solid #153C7F;color:#153C7F;padding:1px 9px;border-radius:6px;font-size:10.5px;font-weight:600}
figure.ss{margin:12px 0;page-break-inside:avoid}
figure.ss img{width:100%;border:1px solid #E6E8F0;border-radius:8px;display:block}
figure.ss figcaption{font-size:9.5px;color:#9CA3AF;text-align:center;margin-top:5px;font-style:italic}
.ss-row{display:flex;gap:12px;margin:12px 0;page-break-inside:avoid}
.ss-row figure{flex:1;margin:0}.ss-row .ss-mob{flex:0 0 30%}
`;
const CONTENT_HTML = `<!DOCTYPE html><html lang="id"><head><meta charset="utf-8"><style>${CONTENT_CSS}</style></head><body>${atsContent()}</body></html>`;

(async () => {
  if (!fs.existsSync(path.dirname(OUT_PDF))) fs.mkdirSync(path.dirname(OUT_PDF), { recursive: true });
  const browser = await puppeteer.launch({ executablePath: CHROME, headless: true, args: ['--no-sandbox'] });

  console.log('Render cover (full-bleed)...');
  const pc = await browser.newPage();
  await pc.setContent(COVER_HTML, { waitUntil: 'networkidle0', timeout: 60000 });
  const coverPdf = await pc.pdf({ width: '210mm', height: '297mm', printBackground: true, pageRanges: '1' });
  await pc.close();

  console.log('Render isi (bermargin + footer)...');
  const pg = await browser.newPage();
  await pg.setContent(CONTENT_HTML, { waitUntil: 'networkidle0', timeout: 60000 });
  const contentPdf = await pg.pdf({
    format: 'A4', printBackground: true,
    margin: { top: '18mm', bottom: '16mm', left: '15mm', right: '15mm' },
    displayHeaderFooter: true, headerTemplate: '<div></div>',
    footerTemplate: '<div style="width:100%;font-size:8px;color:#9CA3AF;text-align:center;padding:0 15mm;">&copy; PT. Nusantara Solusi Teknologi &middot; Panduan Pengguna WBL ATS &middot; Halaman <span class="pageNumber"></span></div>',
  });
  await pg.close();
  await browser.close();

  console.log('Gabung cover + isi...');
  const out = await PDFDocument.create();
  const cov = await PDFDocument.load(coverPdf);
  const con = await PDFDocument.load(contentPdf);
  const [cp] = await out.copyPages(cov, [0]); out.addPage(cp);
  const conPages = await out.copyPages(con, con.getPageIndices());
  conPages.forEach(p => out.addPage(p));
  fs.writeFileSync(OUT_PDF, await out.save());

  const kb = Math.round(fs.statSync(OUT_PDF).size / 1024);
  console.log(`  ✓ saved → ${OUT_PDF} (${kb} KB, ${out.getPageCount()} halaman)`);
})();
