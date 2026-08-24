function atsContent() {
  return `
<h2><div class="num">1</div> Pendahuluan</h2>
<div class="rule"></div>
<p style="text-align:justify">
<b>WBL ATS</b> (Applicant Tracking System) adalah sistem rekrutmen PT. Wulandari Bangun Laksana Tbk.
Aplikasi ini terdiri dari dua sisi: <b>Portal Karir</b> yang terbuka untuk umum tempat pelamar
mengirim lamaran tanpa membuat akun, dan <b>Area HR</b> tempat tim HRD mengelola lowongan,
memproses pelamar melalui tahapan seleksi, menjadwalkan interview, hingga penerimaan.
</p>
<p style="text-align:justify">
Alur besarnya: HR membuat lowongan &rarr; lowongan tayang di portal karir &rarr; pelamar mengirim
data dan CV &rarr; setiap lamaran bergerak di papan pipeline dari tahap ke tahap &rarr; HR
menjadwalkan interview dan mencatat penilaian &rarr; kandidat diterima atau dinyatakan tidak lolos.
Semua email ke pelamar tercatat dan terkirim melalui antrean.
</p>

<h3>Peran Pengguna</h3>
<table>
<thead><tr><th>Peran</th><th>Wewenang</th></tr></thead>
<tbody>
<tr><td><b>Super Admin</b></td><td>Akses seluruh unit dan semua master data. Satu-satunya yang bisa mengelola Unit Usaha dan membuat akun Super Admin lain.</td></tr>
<tr><td><b>Admin HRD</b></td><td>Kendali penuh dalam unitnya: kelola lowongan, pelamar, email, laporan, dan master data unitnya.</td></tr>
<tr><td><b>Staf HRD</b></td><td>Input dan geser tahap pelamar, jadwalkan interview. Tidak bisa menghapus lowongan atau mengelola master data.</td></tr>
<tr><td><b>Pewawancara</b></td><td>Hanya melihat agenda interview yang ditugaskan kepadanya dan mengisi penilaian.</td></tr>
</tbody>
</table>
<div class="callout">Setiap data disaring per <b>unit usaha</b>. HR sebuah unit tidak pernah bisa melihat atau mengubah data unit lain.</div>

<h2><div class="num">2</div> Portal Karir (Publik)</h2>
<div class="rule"></div>
<p style="text-align:justify">
Halaman depan portal karir menampilkan seluruh lowongan yang sedang dibuka. Pelamar dapat
menyaring berdasarkan kata kunci, unit usaha, atau jenis pekerjaan. Dropdown penyaring hanya
menampilkan unit dan jenis yang benar-benar memiliki lowongan tayang.
</p>
${ssOnly('ats_karir', 'Halaman depan portal karir')}
<p style="text-align:justify">
Tombol <span class="btn-ref">Masuk</span> di kanan atas hanya untuk staf HRD; pelamar umum tidak perlu login.
Klik satu lowongan untuk melihat detailnya: deskripsi pekerjaan, kualifikasi, dan yang ditawarkan.
</p>
${ssOnly('ats_lowongan_detail_publik', 'Detail lowongan di portal karir')}

<h3>Mengirim Lamaran</h3>
<p style="text-align:justify">
Dari halaman detail lowongan, klik <span class="btn-ref">Lamar Posisi Ini</span>. Pelamar mengisi
formulir berikut. Field bertanda wajib harus diisi.
</p>
<table>
<thead><tr><th>Field</th><th>Wajib</th><th>Keterangan</th></tr></thead>
<tbody>
<tr><td><b>Nama Lengkap</b></td><td>Ya</td><td>Sesuai KTP.</td></tr>
<tr><td><b>Email</b></td><td>Ya</td><td>Nomor lamaran dan semua pemberitahuan dikirim ke sini.</td></tr>
<tr><td><b>Nomor HP / WhatsApp</b></td><td>Ya</td><td>Kontak aktif pelamar.</td></tr>
<tr><td><b>Jenis Kelamin</b></td><td>Ya</td><td>Laki-laki / Perempuan.</td></tr>
<tr><td><b>Tanggal Lahir</b></td><td>Ya</td><td>Dipakai menghitung umur.</td></tr>
<tr><td><b>Kota Domisili</b></td><td>Ya</td><td>Tempat tinggal saat ini.</td></tr>
<tr><td><b>Akun Instagram</b></td><td>Ya</td><td>Bahan referensi bagi HR; cukup nama akun tanpa tanda @.</td></tr>
<tr><td><b>LinkedIn</b></td><td>Tidak</td><td>Opsional, bila punya.</td></tr>
<tr><td><b>Pendidikan Terakhir</b></td><td>Ya</td><td>SMA/SMK sampai S3.</td></tr>
<tr><td><b>Nama Sekolah / Universitas</b></td><td>Ya</td><td>Institusi pendidikan terakhir.</td></tr>
<tr><td><b>Gaji yang Diharapkan</b></td><td>Ya</td><td>Angka; pemisah ribuan terisi otomatis.</td></tr>
<tr><td><b>Unggah CV</b></td><td>Ya</td><td>Format PDF/DOC/DOCX, maksimal 5 MB.</td></tr>
<tr><td>Jurusan, Pengalaman, Posisi & Perusahaan Kini, Surat Lamaran</td><td>Tidak</td><td>Melengkapi profil pelamar.</td></tr>
</tbody>
</table>
${ssOnly('ats_lamar', 'Formulir lamaran')}
<div class="callout ok">Setelah mengirim, pelamar menerima <b>Nomor Lamaran</b> (mis. WBL-2026-000123) yang juga dikirim ke emailnya. Nomor ini adalah kunci untuk melacak status.</div>

<h3>Melacak Status Lamaran</h3>
<p style="text-align:justify">
Pelamar memantau prosesnya lewat menu <b>Lacak Lamaran</b> dengan memasukkan <b>nomor lamaran</b>
dan <b>email</b>. Keduanya diminta bersama agar status lamaran seseorang tidak bisa dilihat orang lain.
Halaman ini menampilkan perjalanan lamaran dari tahap ke tahap.
</p>
${ss2('ats_lacak_form', 'ats_lacak_hasil', 'Form pelacakan', 'Hasil pelacakan status')}

<h2><div class="num">3</div> Masuk ke Area HR</h2>
<div class="rule"></div>
<p style="text-align:justify">
Staf HRD masuk melalui halaman <b>Masuk</b> memakai email dan kata sandi akun masing-masing.
Bila lupa sandi, gunakan tautan <b>Lupa kata sandi?</b> — tautan reset akan dikirim ke email terdaftar.
</p>
${ssPair('ats_login', 'mob_ats_login', 'Halaman masuk (desktop)', 'Halaman masuk (mobile)')}

<h2><div class="num">4</div> Dashboard</h2>
<div class="rule"></div>
<p style="text-align:justify">
Dashboard menyajikan ringkasan rekrutmen: jumlah pelamar aktif, lamaran bulan ini, lowongan
dibuka, dan kandidat yang diterima tahun ini. Di bawahnya terdapat papan ringkas pipeline,
daftar pelamar terbaru, agenda interview 7 hari ke depan, dan lowongan paling ramai.
</p>
${ssOnly('ats_dashboard', 'Dashboard ringkasan rekrutmen')}
<div class="callout">Saat sistem masih kosong, dashboard menampilkan panduan langkah awal untuk memulai.</div>

<h2><div class="num">5</div> Lowongan</h2>
<div class="rule"></div>
<p style="text-align:justify">
Menu <b>Lowongan</b> mendaftar semua lowongan beserta jumlah pelamarnya. Klik <span class="btn-ref">Lowongan Baru</span>
untuk membuat lowongan. Kode dan tautan publik dibuat otomatis dan tidak berubah setelah dibuat.
</p>
${ssOnly('ats_lowongan_list', 'Daftar lowongan')}
<table>
<thead><tr><th>Field</th><th>Wajib</th><th>Keterangan</th></tr></thead>
<tbody>
<tr><td><b>Nama Posisi</b></td><td>Ya</td><td>Judul lowongan.</td></tr>
<tr><td><b>Unit Usaha</b></td><td>Ya</td><td>Tidak bisa dipindah setelah lowongan dibuat.</td></tr>
<tr><td>Departemen</td><td>Tidak</td><td>Dropdown otomatis tersaring mengikuti unit yang dipilih.</td></tr>
<tr><td><b>Jenis Hubungan Kerja</b></td><td>Ya</td><td>Karyawan Tetap, Kontrak, Paruh Waktu, Magang, Harian.</td></tr>
<tr><td><b>Jumlah Kebutuhan</b></td><td>Ya</td><td>Berapa orang yang dicari.</td></tr>
<tr><td>Deskripsi, Kualifikasi, Yang Ditawarkan</td><td>Tidak</td><td>Tampil apa adanya di portal karir.</td></tr>
<tr><td><b>Status</b></td><td>Ya</td><td>Draf, Dibuka, atau Ditutup. Hanya <b>Dibuka</b> yang tayang di portal.</td></tr>
<tr><td>Gaji Min/Max & Tampilkan Gaji</td><td>Tidak</td><td>Kisaran gaji; centang bila ingin tampil ke publik.</td></tr>
</tbody>
</table>
${ssOnly('ats_lowongan_form', 'Formulir lowongan')}
<p style="text-align:justify">
Halaman detail lowongan menampilkan ringkasan, sebaran pelamar per tahap, dan daftar pelamarnya.
</p>
${ssOnly('ats_lowongan_show', 'Detail lowongan dengan sebaran pelamar')}
<div class="callout warn">Lowongan yang sudah punya pelamar tidak bisa dihapus — hanya <b>ditutup</b> — agar riwayat lamaran tetap utuh.</div>

<h2><div class="num">6</div> Pipeline</h2>
<div class="rule"></div>
<p style="text-align:justify">
Papan <b>Pipeline</b> menampilkan pelamar aktif tersusun per kolom tahap, dari Lamaran Masuk
hingga Penawaran. Setiap kartu pelamar menampilkan nama, lowongan, dan skor potensinya. Klik kartu
untuk membuka detail pelamar. Papan dapat difokuskan ke satu lowongan lewat dropdown di kanan atas.
</p>
${ssOnly('ats_pipeline', 'Papan pipeline kanban')}
<div class="callout">Papan dapat <b>digeser ke samping</b> untuk melihat tahap lainnya — gunakan tombol panah, roda mouse, atau seret. Petunjuk "Geser untuk lihat tahap lain" muncul di atas papan.</div>

<h2><div class="num">7</div> Kandidat Unggulan &amp; Skor Potensi</h2>
<div class="rule"></div>
<p style="text-align:justify">
Menu <b>Unggulan</b> mengurutkan pelamar aktif menurut <b>Skor Potensi</b> — angka 0&ndash;100 yang
menggabungkan sinyal yang sudah terkumpul: performa interview, rekomendasi pewawancara, rating HR,
pengalaman kerja, dan pendidikan. Bobot tiap sinyal dapat disetel di Pengaturan.
</p>
<table>
<thead><tr><th>Komponen</th><th>Bobot Default</th></tr></thead>
<tbody>
<tr><td>Performa Interview</td><td>40</td></tr>
<tr><td>Rekomendasi Pewawancara</td><td>25</td></tr>
<tr><td>Rating HR</td><td>20</td></tr>
<tr><td>Pengalaman Kerja</td><td>10</td></tr>
<tr><td>Pendidikan</td><td>5</td></tr>
</tbody>
</table>
<p style="text-align:justify">
Skor dihitung hanya dari sinyal yang tersedia lalu dinormalkan, jadi pelamar baru dinilai adil
berdasar profilnya. Sebelum ada penilaian nyata (interview/rating/rekomendasi), statusnya
ditandai <span class="b b-b">Awal</span> — agar profil semata tidak langsung tampil sebagai Tinggi.
Band lainnya: <span class="b b-g">Tinggi</span> (&ge;75), <span class="b b-y">Sedang</span> (50&ndash;74), <span class="b b-gray">Rendah</span> (&lt;50).
</p>
${ssOnly('ats_unggulan', 'Halaman kandidat unggulan')}

<h2><div class="num">8</div> Pelamar</h2>
<div class="rule"></div>
<p style="text-align:justify">
Menu <b>Pelamar</b> mendaftar semua lamaran dengan penyaring tahap, lowongan, status, dan pencarian.
Klik satu baris untuk membuka detail pelamar.
</p>
${ssOnly('ats_pelamar_list', 'Daftar pelamar')}
<p style="text-align:justify">
Halaman detail pelamar adalah pusat kerja HR. Di sini terdapat kartu <b>Skor Potensi</b> dengan
rincian komponennya, data diri lengkap termasuk Instagram dan LinkedIn (sebagai tautan), riwayat
tahap, catatan internal, jadwal interview, penilaian pewawancara, dan riwayat email.
</p>
${ssOnly('ats_pelamar_detail', 'Detail pelamar dengan skor potensi')}

<h3>Menggeser Tahap</h3>
<p style="text-align:justify">
Pada kartu <b>Tahap Saat Ini</b>, pilih tahap tujuan lalu klik <span class="btn-ref">Simpan Perpindahan</span>.
Setiap perpindahan mencatat siapa yang menggeser dan kapan. Status lamaran mengikuti jenis tahap
tujuan: tahap <b>Diterima</b> menutup sebagai diterima, <b>Tidak Lolos</b> sebagai gugur. Saat pindah ke
tahap penutup atau Penawaran, email pemberitahuan yang sesuai disiapkan otomatis.
</p>

<h3>CV, Catatan, dan Rating</h3>
<p style="text-align:justify">
Tombol <span class="btn-ref">Lihat CV</span> membuka CV pelamar langsung di tab peramban (preview,
bukan unduh). Tombol <span class="btn-ref">Kirim Email</span> mengirim template email ke pelamar, dan
<span class="btn-ref">Mundur</span> menandai pelamar mengundurkan diri. HR juga bisa memberi rating
bintang pada kartu Skor Potensi. Catatan internal dan riwayat tahap tercatat lengkap di bawah halaman.
</p>

<h2><div class="num">9</div> Interview</h2>
<div class="rule"></div>
<p style="text-align:justify">
Dari halaman pelamar, klik <span class="btn-ref">Jadwalkan</span> untuk membuat jadwal interview.
Setiap jadwal mencatat siapa yang menjadwalkan, kapan diproses, dan untuk kapan.
</p>
<table>
<thead><tr><th>Field</th><th>Wajib</th><th>Keterangan</th></tr></thead>
<tbody>
<tr><td>Putaran</td><td>Tidak</td><td>Interview bisa lebih dari satu putaran.</td></tr>
<tr><td><b>Tanggal &amp; Jam</b></td><td>Ya</td><td>Waktu pelaksanaan.</td></tr>
<tr><td>Mode</td><td>Tidak</td><td>Tatap Muka, Daring, atau Telepon. Lokasi/tautan menyesuaikan.</td></tr>
<tr><td>Pewawancara</td><td>Tidak</td><td>Satu atau lebih; hanya yang berhak menilai di unit itu.</td></tr>
<tr><td>Kirim undangan</td><td>Tidak</td><td>Bila dicentang, undangan langsung dikirim ke pelamar setelah disimpan.</td></tr>
</tbody>
</table>
${ssOnly('ats_interview_form', 'Formulir jadwal interview')}
<p style="text-align:justify">
Menu <b>Interview</b> menampilkan agenda seluruh jadwal dalam rentang tanggal. Jadwal yang sudah dibuat
dapat diubah, dibatalkan, atau ditandai selesai.
</p>
${ssOnly('ats_interview_list', 'Agenda interview')}

<h3>Penilaian Pewawancara</h3>
<p style="text-align:justify">
Pewawancara mengisi penilaian lewat tombol <span class="btn-ref">Isi Penilaian</span> pada kartu interview.
Penilaian mencakup lima aspek skala 1&ndash;5 (Teknis, Komunikasi, Sikap, Pengalaman, Budaya),
rekomendasi, serta catatan kelebihan dan kekurangan. Nilai akhir dihitung otomatis dari aspek yang
diisi dan langsung menyegarkan rata-rata skor kandidat.
</p>
${ssOnly('ats_penilaian', 'Formulir penilaian pewawancara')}

<h2><div class="num">10</div> Kandidat (Bank Talenta)</h2>
<div class="rule"></div>
<p style="text-align:justify">
Menu <b>Kandidat</b> adalah bank talenta — kumpulan semua orang yang pernah melamar, lintas lowongan.
Berguna menemukan kembali kandidat lama saat ada lowongan baru yang cocok. Pencarian mencakup nama,
email, telepon, sekolah, dan posisi. Admin HRD dapat menandai kandidat ke daftar hitam beserta alasannya.
</p>
${ssOnly('ats_kandidat', 'Bank talenta kandidat')}

<h2><div class="num">11</div> Email</h2>
<div class="rule"></div>
<p style="text-align:justify">
Semua email ke pelamar — baik otomatis maupun manual — melewati <b>antrean</b> lebih dulu sebelum
dikirim. Desain ini membuat gangguan server email tidak pernah menggagalkan aksi HR, setiap kiriman
punya jejak, dan yang gagal bisa dikirim ulang. Menu <b>Email</b> menampilkan antrean dengan status
<span class="b b-y">Antre</span>, <span class="b b-g">Terkirim</span>, atau <span class="b b-r">Gagal</span>.
</p>
${ssOnly('ats_email', 'Antrean email')}
<p style="text-align:justify">
Submenu <b>Template</b> memuat template email standar (konfirmasi lamaran, undangan interview,
penawaran, dsb.) yang dapat diubah. Isi memakai placeholder seperti <b>{{nama_kandidat}}</b>,
<b>{{posisi}}</b>, dan <b>{{tanggal_interview}}</b> yang terisi otomatis saat dikirim.
</p>
${ssOnly('ats_email_template', 'Daftar template email')}
<div class="callout">Pengiriman nyata memerlukan konfigurasi SMTP di berkas <b>.env</b>. Sebelum diatur, email tetap tersimpan aman di antrean.</div>

<h2><div class="num">12</div> Laporan</h2>
<div class="rule"></div>
<p style="text-align:justify">
Menu <b>Laporan</b> menyajikan corong rekrutmen (berapa lamaran yang pernah mencapai tiap tahap),
rata-rata lama proses per tahap, rekap pelamar dan diterima per lowongan, serta ringkasan hasil
tahun berjalan.
</p>
${ssOnly('ats_laporan', 'Laporan rekrutmen')}

<h2><div class="num">13</div> Master Data</h2>
<div class="rule"></div>
<p style="text-align:justify">
Master data adalah pengaturan dasar yang dipakai seluruh aplikasi. Hanya Admin HRD dan Super Admin
yang dapat mengubahnya.
</p>

<h3>Unit Usaha</h3>
<p style="text-align:justify">
Daftar unit usaha WBL. Unit adalah akar pembatasan akses — data setiap unit terpisah. Kode unit
tidak bisa diubah setelah dibuat karena menjadi awalan kode lowongan. Hanya Super Admin yang dapat
mengelolanya.
</p>
${ssOnly('ats_master_unit', 'Master unit usaha')}

<h3>Departemen</h3>
<p style="text-align:justify">
Departemen terikat pada satu unit dan tidak bisa berpindah. Saat membuat lowongan, pilihan
departemen otomatis tersaring mengikuti unit. Klik ikon pensil untuk mengubah satu baris.
</p>
${ssOnly('ats_master_departemen', 'Master departemen')}

<h3>Tahapan Pipeline</h3>
<p style="text-align:justify">
Tahapan menentukan susunan papan pipeline. Urutan diatur dengan <b>menyeret</b> baris lewat pegangan
di kiri — tidak perlu mengetik nomor. Setiap tahap punya jenis (Berjalan, Diterima, Tidak Lolos) yang
menentukan status lamaran, serta warna untuk penanda visual.
</p>
${ssOnly('ats_master_tahapan', 'Master tahapan dengan drag-and-drop')}
<div class="callout">Sebaiknya ada tepat satu tahap berjenis <b>Diterima</b> dan satu <b>Tidak Lolos</b>, karena status lamaran diturunkan dari jenis tahap.</div>

<h3>Pengguna</h3>
<p style="text-align:justify">
Kelola akun staf HRD beserta peran dan unitnya. Kata sandi baru bisa diatur di sini; kosongkan bila
tidak ingin mengubah. Menghapus pengguna menonaktifkannya (soft delete) agar jejaknya tetap utuh.
</p>
${ssOnly('ats_master_pengguna', 'Master pengguna')}

<h3>Pengaturan</h3>
<p style="text-align:justify">
Pengaturan aplikasi yang bisa diubah tanpa menyentuh berkas konfigurasi: identitas perusahaan,
tagline dan pengantar portal karir, batas ukuran dan format CV, pengingat interview, serta
<b>bobot skor potensi</b> kandidat.
</p>
${ssOnly('ats_pengaturan', 'Halaman pengaturan')}
`;
}
module.exports = { atsContent };
