# User Guide WBL ATS

Sumber & builder Panduan Pengguna PDF. Konfigurasi lengkap di `config.json`.

## Isi
- `ats.js` — konten user guide (fungsi `atsContent()` mengembalikan HTML per bab).
- `retake_ats.js` — ambil screenshot desktop + publik + login.
- `retake_mob_login.js` — ambil screenshot mobile login (cookie dibersihkan).
- `build_ug.js` — render cover (full-bleed) + isi, gabung jadi PDF.
- `config.json` — BASE_URL, akun login, path, ID record, tema cover.
- `screenshots/` — hasil screenshot (di-generate, boleh diabaikan git).

## Cara build ulang
```bash
# 0. Pastikan aplikasi jalan: php spark serve --port 8080
# 1. Isi data demo agar screenshot representatif
php spark db:seed DemoSeeder          # dari root project
# 2. Pasang dependensi (sekali saja)
cd userguide && npm install
# 3. Ambil screenshot. Skrip ini MENGUBAH data lokal: mengirim undangan
#    interview dan menjalankan jawaban pelamar (jadwal ulang, hadir) lewat
#    alur sebenarnya. Cadangkan DB dulu, pulihkan sesudahnya.
mysqldump -uroot wbl_ats > /tmp/wbl_ats.sql
npm run shots
mysql -uroot wbl_ats < /tmp/wbl_ats.sql
# 4. Build PDF -> ../docs/Panduan_Pengguna_WBL_ATS.pdf
npm run build
# 5. (opsional) kembalikan DB ke kondisi produksi
```

## Update konten
Edit `ats.js` (per bab). Tiap bab: `<h2>` (mulai halaman baru), paragraf,
tabel field, callout, dan helper screenshot `ssOnly()/ss2()/ssPair()`.
Setelah edit, jalankan `npm run build` (screenshot tak perlu diulang bila
tampilan tak berubah).

## Stroke logo cover
Logo cover pakai `public/assets/img/logo-wbl-stroke.png` = logo asli + halo putih.
Untuk regenerasi (mis. menebalkan stroke):
```bash
cd userguide
# ubah GROW di make_logo_stroke.py (5=tipis, 9=sekarang)
python3 make_logo_stroke.py      # butuh Pillow: pip install Pillow
node build_ug.js                 # rebuild PDF
```

## Catatan
- Chrome dipakai lewat `puppeteer-core` (path di `build_ug.js`/`config.json`).
- Screenshot butuh data; ID record ada di `config.json`.
- Cover bertema Human Capital; logo `public/assets/img/logo-wbl-stroke.png`
  (warna asli + stroke putih) di-generate dari `logo-wbl.png`.
