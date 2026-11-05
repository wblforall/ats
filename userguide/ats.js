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
menjadwalkan interview, pelamar mengonfirmasi kehadiran lewat tombol di email &rarr; pewawancara
mencatat penilaian &rarr; kandidat diterima atau dinyatakan tidak lolos. Semua email ke pelamar
tercatat dan terkirim melalui antrean.
</p>

<h3>Peran Pengguna</h3>
<table>
<thead><tr><th>Peran</th><th>Wewenang</th></tr></thead>
<tbody>
<tr><td><b>Super Admin</b></td><td>Akses seluruh unit dan semua menu. Satu-satunya yang bisa mengelola Unit Usaha dan memberi peran Super Admin.</td></tr>
<tr><td><b>Admin HRD</b></td><td>Kelola lowongan, pelamar, interview, email, laporan, departemen, tahapan, pengguna (di unitnya), dan pengaturan. Tahapan dan Pengaturan berlaku untuk seluruh unit.</td></tr>
<tr><td><b>Staf HRD</b></td><td>Kelola lowongan dan pelamar, geser tahap, jadwalkan interview, tindak lanjuti jawaban pelamar. Tidak membuka menu Email, Laporan, dan Master Data; tidak bisa mengirim email manual atau memblacklist kandidat.</td></tr>
<tr><td><b>Pewawancara</b></td><td>Hanya melihat Dashboard dan agenda interview yang ditugaskan kepadanya.</td></tr>
</tbody>
</table>
<div class="callout">Setiap data disaring per <b>unit usaha</b>. HR sebuah unit tidak bisa melihat atau mengubah data unit lain. Super Admin melihat semua unit.</div>

<h2><div class="num">2</div> Portal Karir (Publik)</h2>
<div class="rule"></div>
<p style="text-align:justify">
Portal karir adalah wajah rekrutmen WBL bagi publik. Halaman depannya dibuka dengan judul
<b>Your future starts here.</b> beserta teks pengantar yang diatur di menu Pengaturan. Animasi
pembuka (kanopi atap logo WBL) hanya tampil sekali per kunjungan.
</p>
${ssPair('ats_karir', 'mob_ats_karir', 'Halaman depan portal karir (desktop)', 'Portal karir di ponsel')}
<p style="text-align:justify">
Kotak pencarian berisi tiga isian: <b>Posisi</b> (kata kunci yang dicocokkan ke nama posisi,
departemen, dan lokasi kerja), <b>Unit usaha</b>, dan <b>Jenis</b>, lalu tombol
<span class="btn-ref">Cari</span>. Dropdown hanya memuat unit dan jenis yang benar-benar punya
lowongan tayang. Bagian <b>Lowongan dibuka</b> mendaftar setiap posisi dengan pelat kode unit
(mis. <b>EWM</b>, <b>PTC</b>), jenis pekerjaan, lokasi, jumlah kebutuhan, dan tanggal ditutup. Saat
saringan aktif, muncul tautan <b>Hapus saringan</b>.
</p>
<p style="text-align:justify">
Di bawahnya, <b>Tahapan seleksi</b> menampilkan urutan tahap yang akan dilalui pelamar (diambil
dari master Tahapan) hingga <b>Bergabung</b>, dan pita <b>Sudah melamar?</b> mengarah ke Lacak
Lamaran. Tombol <span class="btn-ref">Masuk</span> di kanan atas hanya untuk staf HRD.
</p>
${ssOnly('ats_lowongan_detail_publik', 'Detail lowongan di portal karir')}

<h3>Mengirim Lamaran</h3>
<p style="text-align:justify">
Dari halaman detail lowongan, klik <span class="btn-ref">Lamar Posisi Ini</span>. Formulir terbagi
menjadi tiga bagian: <b>Data Diri</b>, <b>Pendidikan &amp; Pengalaman</b>, serta <b>Berkas &amp;
Keterangan</b>. Pelamar tidak perlu membuat akun.
</p>
<table>
<thead><tr><th>Nama Field</th><th>Wajib</th><th>Keterangan</th></tr></thead>
<tbody>
<tr><td><b>Nama Lengkap</b></td><td>Ya</td><td>Sesuai KTP.</td></tr>
<tr><td><b>Email</b></td><td>Ya</td><td>Nomor lamaran dan semua pemberitahuan dikirim ke sini.</td></tr>
<tr><td><b>Nomor HP / WhatsApp</b></td><td>Ya</td><td>Kontak aktif pelamar.</td></tr>
<tr><td><b>Kota Domisili</b></td><td>Ya</td><td>Tempat tinggal saat ini.</td></tr>
<tr><td><b>Jenis Kelamin</b></td><td>Ya</td><td>Laki-laki / Perempuan.</td></tr>
<tr><td><b>Tanggal Lahir</b></td><td>Ya</td><td>Dipakai menghitung umur.</td></tr>
<tr><td><b>Akun Instagram</b></td><td>Ya</td><td>Cukup nama akun tanpa tanda @; bahan referensi HR.</td></tr>
<tr><td>LinkedIn</td><td>Tidak</td><td>Opsional, bila punya.</td></tr>
<tr><td><b>Pendidikan Terakhir</b></td><td>Ya</td><td>SMA/SMK sampai S3.</td></tr>
<tr><td><b>Nama Sekolah / Universitas</b></td><td>Ya</td><td>Institusi pendidikan terakhir.</td></tr>
<tr><td>Jurusan</td><td>Tidak</td><td>Bidang studi.</td></tr>
<tr><td>Lama Pengalaman Kerja (tahun)</td><td>Tidak</td><td>Ikut dihitung dalam Skor Potensi.</td></tr>
<tr><td>Posisi Saat Ini / Perusahaan Saat Ini</td><td>Tidak</td><td>Pekerjaan sekarang, bila ada.</td></tr>
<tr><td><b>Gaji yang Diharapkan (Rp)</b></td><td>Ya</td><td>Angka; pemisah ribuan terisi otomatis.</td></tr>
<tr><td>Siap Mulai Bekerja</td><td>Tidak</td><td>Kapan pelamar bisa mulai.</td></tr>
<tr><td>Tahu lowongan ini dari</td><td>Tidak</td><td>Sumber informasi lowongan.</td></tr>
<tr><td>Nama Perekomendasi (bila ada)</td><td>Tidak</td><td>Nama orang yang merekomendasikan.</td></tr>
<tr><td><b>Unggah CV</b></td><td>Ya</td><td>Format dan ukuran maksimal mengikuti Pengaturan (bawaan PDF/DOC/DOCX, 5 MB).</td></tr>
<tr><td>Surat Lamaran Singkat</td><td>Tidak</td><td>Pesan singkat untuk HR.</td></tr>
</tbody>
</table>
${ssOnly('ats_lamar', 'Formulir lamaran')}
<p style="text-align:justify">
Klik <span class="btn-ref">Kirim Lamaran</span>. Halaman <b>Lamaran Anda Terkirim</b> menampilkan
<b>Nomor Lamaran</b> (mis. WBL-2026-000123) beserta tombol <span class="btn-ref">Lacak Lamaran</span>
dan <span class="btn-ref-outline">Lihat Lowongan Lain</span>.
</p>
<div class="callout ok">Nomor lamaran juga dikirim ke email pelamar bila opsi <b>Kirim email konfirmasi otomatis saat lamaran masuk</b> di Pengaturan menyala. Nomor ini adalah kunci untuk melacak status.</div>

<h3>Melacak Status Lamaran</h3>
<p style="text-align:justify">
Pelamar membuka menu <b>Lacak Lamaran</b>, mengisi <b>Nomor Lamaran</b> dan <b>Email</b>, lalu klik
<span class="btn-ref">Lihat Status</span>. Keduanya diminta bersama agar status lamaran seseorang
tidak bisa dilihat orang lain. Hasilnya menampilkan <b>Perjalanan Lamaran Anda</b> tahap demi tahap,
panel ringkasan, dan kotak <b>Anda punya jadwal wawancara</b> bila interview sudah dijadwalkan.
</p>
${ss2('ats_lacak_form', 'ats_lacak_hasil', 'Form pelacakan', 'Hasil pelacakan status')}

<h2><div class="num">3</div> Masuk ke Area HR</h2>
<div class="rule"></div>
<p style="text-align:justify">
Staf HRD dan pewawancara masuk lewat halaman <b>Masuk</b> (tombol Masuk di portal karir) memakai
<b>Email</b> dan <b>Kata sandi</b> akun masing-masing, lalu klik <span class="btn-ref">Masuk</span>.
Tautan <b>← Portal karir</b> di kiri atas kembali ke halaman publik.
</p>
${ssPair('ats_login', 'mob_ats_login', 'Halaman masuk (desktop)', 'Halaman masuk (ponsel)')}

<h3>Lupa Kata Sandi</h3>
<p style="text-align:justify">
Klik <b>Lupa kata sandi?</b>, isi email akun, lalu klik <span class="btn-ref">Kirim tautan reset</span>.
Bila email terdaftar, tautan untuk membuat kata sandi baru dikirim ke kotak masuk dan berlaku
<b>1 jam</b>. Di halaman <b>Kata sandi baru</b>, isi sandi minimal 6 karakter dua kali lalu klik
<span class="btn-ref">Simpan kata sandi baru</span>.
</p>
${ssOnly('ats_lupa_sandi', 'Halaman lupa kata sandi')}

<h3>Profil Saya, Pencarian, dan Bantuan</h3>
<p style="text-align:justify">
Klik nama Anda di kanan atas lalu <b>Profil Saya</b> untuk mengubah nama, jabatan, telepon, dan
<b>kata sandi sendiri</b> (isi <b>Kata Sandi Baru</b> dan <b>Ulangi Sandi</b>, lalu
<span class="btn-ref">Simpan Profil</span>). Email hanya bisa diubah oleh Admin HRD. Kolom
pencarian di topbar mencari pelamar, lowongan, atau email sekaligus; ikon tanda tanya membuka
halaman Bantuan.
</p>

<h2><div class="num">4</div> Dashboard</h2>
<div class="rule"></div>
<p style="text-align:justify">
Dashboard menyajikan ringkasan rekrutmen: <b>Pelamar Aktif</b>, <b>Lamaran Bulan Ini</b>,
<b>Lowongan Dibuka</b>, dan <b>Diterima Tahun Ini</b>. Di bawahnya terdapat deret <b>Pipeline</b>
(jumlah pelamar per tahap), <b>Pelamar Terbaru</b>, <b>Agenda Interview 7 Hari</b> (hingga 6 jadwal
terdekat), dan <b>Lowongan Paling Ramai</b>. Tombol <span class="btn-ref">Lowongan Baru</span> ada di
kanan atas dan di sidebar.
</p>
${ssPair('ats_dashboard', 'mob_ats_dashboard', 'Dashboard (desktop)', 'Dashboard di ponsel')}
<h3>Kartu Jawaban Pelamar</h3>
<p style="text-align:justify">
Bila ada pelamar yang <b>minta jadwal ulang</b> atau <b>membatalkan interview</b>, kartu
<b>Jawaban Pelamar</b> muncul di atas agenda beserta jumlahnya. Permintaan jadwal ulang tampil
sebagai "jadwal lama &rarr; usulan" dengan penanda <span class="b b-y">Tinjau</span>; pembatalan
untuk jadwal hari ini atau setelahnya tampil sebagai "Batal hadir" dengan penanda
<span class="b b-r">Dibatalkan</span>. Klik satu baris untuk langsung ke kartu interview pelamar.
Setiap jadwal di agenda juga menampilkan jawaban pelamar (lihat Bab 9).
</p>
${ss2('ats_dashboard_jawaban', 'ats_pelamar_jadwal_ulang', 'Kartu Jawaban Pelamar di dashboard', 'Permintaan jadwal ulang di halaman pelamar')}
<div class="callout">Saat sistem masih kosong, dashboard menampilkan panel <b>Selamat datang di ATS WBL</b> dengan tombol <span class="btn-ref">Buat Lowongan</span> dan <span class="btn-ref-outline">Atur Perusahaan</span>.</div>

<h2><div class="num">5</div> Lowongan</h2>
<div class="rule"></div>
<p style="text-align:justify">
Menu <b>Lowongan</b> mendaftar semua lowongan beserta jumlah pelamarnya, dengan pencarian dan
saringan status. Klik <span class="btn-ref">Lowongan Baru</span> untuk membuat lowongan. Kode dan
tautan publik dibuat otomatis saat disimpan dan tidak berubah sesudahnya.
</p>
${ssOnly('ats_lowongan_list', 'Daftar lowongan')}
<table>
<thead><tr><th>Nama Field</th><th>Wajib</th><th>Keterangan</th></tr></thead>
<tbody>
<tr><td><b>Nama Posisi</b></td><td>Ya</td><td>Judul lowongan.</td></tr>
<tr><td><b>Unit Usaha</b></td><td>Ya</td><td>Tidak bisa dipindah setelah lowongan dibuat.</td></tr>
<tr><td>Departemen</td><td>Tidak</td><td>Pilihan otomatis tersaring mengikuti unit yang dipilih.</td></tr>
<tr><td><b>Jenis Hubungan Kerja</b></td><td>Ya</td><td>Karyawan Tetap, Kontrak, Paruh Waktu, Magang, Harian.</td></tr>
<tr><td>Jenjang</td><td>Tidak</td><td>Mis. Staff, Supervisor.</td></tr>
<tr><td>Lokasi Kerja</td><td>Tidak</td><td>Mis. Balikpapan.</td></tr>
<tr><td><b>Jumlah Kebutuhan</b></td><td>Ya</td><td>Berapa orang yang dicari.</td></tr>
<tr><td>Deskripsi Pekerjaan, Kualifikasi, Yang Ditawarkan</td><td>Tidak</td><td>Tampil apa adanya di portal karir; tulis satu poin per baris.</td></tr>
<tr><td>Status</td><td>Tidak</td><td>Draf (bawaan), Dibuka, atau Ditutup. Hanya <b>Dibuka</b> yang tayang di portal.</td></tr>
<tr><td>Tanggal Tutup</td><td>Tidak</td><td>Kosongkan bila tanpa batas waktu. Lewat tanggal ini, lowongan ditutup otomatis setiap malam.</td></tr>
<tr><td>Gaji Minimum / Maksimum (Rp)</td><td>Tidak</td><td>Kisaran gaji; centang <b>Tampilkan kisaran gaji di portal karir</b> bila ingin tampil ke publik.</td></tr>
</tbody>
</table>
${ssOnly('ats_lowongan_form', 'Formulir lowongan')}
<p style="text-align:justify">
Klik <span class="btn-ref">Buat Lowongan</span> (atau <span class="btn-ref">Simpan Perubahan</span>
saat mengubah). Halaman detail lowongan menampilkan ringkasan, <b>Sebaran Pelamar per Tahap</b>, dan
<b>Daftar Pelamar</b>, dengan tombol <span class="btn-ref-outline">Lihat di Portal</span> dan
<span class="btn-ref">Ubah</span>.
</p>
${ssOnly('ats_lowongan_show', 'Detail lowongan dengan sebaran pelamar')}
<div class="callout warn">Untuk menghentikan lowongan, ubah <b>Status</b> menjadi <b>Ditutup</b>. Riwayat lamaran tetap utuh.</div>

<h2><div class="num">6</div> Pipeline</h2>
<div class="rule"></div>
<p style="text-align:justify">
Papan <b>Pipeline</b> menampilkan pelamar aktif tersusun per kolom tahap sesuai master Tahapan.
Setiap kartu menampilkan nama, lowongan, sudah berapa lama melamar, dan skor potensinya. Klik kartu
untuk membuka detail pelamar. Dropdown <b>Semua lowongan</b> di kanan atas memfokuskan papan ke satu
lowongan (beserta jumlah pelamarnya).
</p>
${ssOnly('ats_pipeline', 'Papan pipeline kanban')}
<div class="callout">Papan dapat <b>digeser ke samping</b> untuk melihat tahap lainnya — gunakan tombol panah, roda mouse, atau seret. Kolom Diterima dan Tidak Lolos selalu kosong karena papan hanya memuat lamaran yang masih berjalan.</div>

<h2><div class="num">7</div> Kandidat Unggulan &amp; Skor Potensi</h2>
<div class="rule"></div>
<p style="text-align:justify">
Menu <b>Unggulan</b> (halaman <b>Kandidat Unggulan</b>) mengurutkan pelamar aktif menurut
<b>Skor Potensi</b> — angka 0&ndash;100 yang menggabungkan sinyal yang sudah terkumpul. Saring dengan
<b>Lowongan</b> dan <b>Tingkat</b>, lalu klik <span class="btn-ref">Terapkan</span>. Bobot tiap sinyal
dapat disetel di Pengaturan.
</p>
<table>
<thead><tr><th>Komponen</th><th>Bobot Bawaan</th></tr></thead>
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
Tingkat lainnya: <span class="b b-g">Tinggi</span> (&ge;75), <span class="b b-y">Sedang</span> (50&ndash;74), <span class="b b-gray">Rendah</span> (&lt;50).
</p>
${ssOnly('ats_unggulan', 'Halaman kandidat unggulan')}

<h2><div class="num">8</div> Pelamar</h2>
<div class="rule"></div>
<p style="text-align:justify">
Menu <b>Pelamar</b> mendaftar semua lamaran. Saring dengan <b>Cari</b> (nama, email, atau nomor HP),
<b>Tahap</b>, <b>Lowongan</b>, dan <b>Status</b>, lalu klik <span class="btn-ref">Terapkan</span>.
Tombol <span class="btn-ref-outline">Lihat Papan Pipeline</span> membuka tampilan kanban. Klik satu
baris untuk membuka detail pelamar.
</p>
${ssOnly('ats_pelamar_list', 'Daftar pelamar')}
<p style="text-align:justify">
Halaman detail pelamar adalah pusat kerja HR. Isinya: <b>Tahap Saat Ini</b>, <b>Jadwal Interview</b>,
<b>Catatan Internal</b>, <b>Riwayat Tahap</b>, kartu <b>Skor Potensi</b> dengan rincian komponen dan
<b>Rating HR</b> bintang, <b>Data Diri</b> (termasuk Instagram dan LinkedIn sebagai tautan),
<b>Penilaian Pewawancara</b>, dan <b>Riwayat Email</b>.
</p>
${ssOnly('ats_pelamar_detail', 'Detail pelamar dengan skor potensi')}

<h3>Menggeser Tahap</h3>
<p style="text-align:justify">
Pada kartu <b>Tahap Saat Ini</b>, pilih <b>Pindahkan ke tahap</b>, isi <b>Catatan perpindahan
(opsional)</b>, lalu klik <span class="btn-ref">Simpan Perpindahan</span>. Bila tujuannya Tidak Lolos,
muncul isian <b>Alasan tidak lolos</b>. Setiap perpindahan mencatat siapa dan kapan. Status lamaran
mengikuti jenis tahap tujuan: <b>Diterima</b> menutup sebagai diterima, <b>Tidak Lolos</b> sebagai
gugur.
</p>
<div class="callout">Pindah ke <b>Penawaran</b>, <b>Diterima</b>, atau <b>Tidak Lolos</b> menyiapkan email Penawaran Kerja, Selamat Bergabung, atau Pemberitahuan Hasil Seleksi di antrean. Email itu langsung terkirim hanya bila templatenya dicentang <b>Kirim otomatis</b>; selain itu kirim dari menu Email.</div>

<h3>CV, Email, Mundur, Catatan, dan Rating</h3>
<p style="text-align:justify">
Tombol <span class="btn-ref">Lihat CV</span> membuka CV di tab peramban (pratinjau, bukan unduh).
<span class="btn-ref">Kirim Email</span> (khusus Admin HRD dan Super Admin) membuka pilihan
<b>Pilih template</b> dan tombol <span class="btn-ref">Kirim Sekarang</span>.
<span class="btn-ref-outline">Mundur</span> menandai pelamar mengundurkan diri dengan
<b>Alasan (opsional)</b> lalu <span class="btn-ref">Tandai Mundur</span>. Catatan ditambahkan lewat
<span class="btn-ref">Tambah Catatan</span>; jawaban pelamar atas undangan interview dan keputusan HR
atasnya tercatat di sini secara otomatis (penulis <b>Sistem</b> untuk aksi pelamar).
</p>

<h2><div class="num">9</div> Interview</h2>
<div class="rule"></div>
<p style="text-align:justify">
Dari kartu <b>Jadwal Interview</b> di halaman pelamar, klik <span class="btn-ref">Jadwalkan</span>.
Setiap jadwal mencatat siapa yang menjadwalkan, kapan diproses, dan untuk kapan.
</p>
<table>
<thead><tr><th>Nama Field</th><th>Wajib</th><th>Keterangan</th></tr></thead>
<tbody>
<tr><td>Putaran</td><td>Tidak</td><td>Terisi otomatis dengan putaran berikutnya.</td></tr>
<tr><td>Judul (opsional)</td><td>Tidak</td><td>Mis. Wawancara HRD.</td></tr>
<tr><td><b>Tanggal</b></td><td>Ya</td><td>Hari pelaksanaan.</td></tr>
<tr><td><b>Jam</b></td><td>Ya</td><td>Waktu mulai (WITA).</td></tr>
<tr><td>Durasi (menit)</td><td>Tidak</td><td>Bawaan 60 menit.</td></tr>
<tr><td>Mode</td><td>Tidak</td><td>Tatap Muka, Daring, atau Telepon.</td></tr>
<tr><td>Lokasi / Tautan Meeting</td><td>Tidak</td><td><b>Lokasi</b> untuk Tatap Muka/Telepon; <b>Tautan Meeting</b> (Google Meet, Zoom, dsb.) untuk Daring.</td></tr>
<tr><td>Pewawancara</td><td>Tidak</td><td>Centang satu atau lebih; hanya yang berhak menilai di unit itu.</td></tr>
<tr><td>Kirim email undangan</td><td>Tidak</td><td>Tercentang bawaan: undangan bertombol jawaban langsung dikirim setelah disimpan.</td></tr>
</tbody>
</table>
${ssOnly('ats_interview_form', 'Formulir jadwal interview')}
<p style="text-align:justify">
Klik <span class="btn-ref">Simpan Jadwal</span>. Pada kartu interview di halaman pelamar tersedia
tombol <span class="btn-ref-outline">Isi Penilaian</span>, <span class="btn-ref-outline">Ubah Jadwal</span>,
<span class="btn-ref-outline">Tandai Selesai</span>, dan <span class="btn-ref-outline">Batalkan</span>.
</p>

<h3>Agenda Interview</h3>
<p style="text-align:justify">
Menu <b>Interview</b> menampilkan agenda dalam rentang <b>Dari</b>&ndash;<b>Sampai</b> (bawaan hari ini
hingga 30 hari ke depan; ubah lalu klik <span class="btn-ref">Tampilkan</span>). Kolomnya: Waktu,
Pelamar, Lowongan, Mode, Status, dan <b>Jawaban Pelamar</b>. Klik satu baris untuk membuka pelamar.
</p>
${ssOnly('ats_interview_list', 'Agenda interview dengan kolom Jawaban Pelamar')}

<h3>Jawaban Pelamar atas Undangan</h3>
<p style="text-align:justify">
Email undangan (juga email pengingat) memuat tombol <b>Konfirmasi Hadir</b> dan <b>Minta Jadwal
Ulang</b>, serta tautan <b>Batalkan interview</b>. Tombol hanya membuka halaman <b>Jadwal Interview
Anda</b>; jawaban baru tercatat setelah pelamar menekan tombol di halaman itu, sehingga pemindai
tautan di layanan email tidak bisa menjawab atas nama pelamar. Pelamar tidak perlu login.
</p>
${ssPair('ats_jawab_interview', 'mob_ats_jawab_interview', 'Halaman jawaban pelamar (desktop)', 'Halaman jawaban di ponsel')}
<table>
<thead><tr><th>Pilihan Pelamar</th><th>Batas Waktu</th><th>Yang Terjadi</th></tr></thead>
<tbody>
<tr><td><b>Saya Hadir Sesuai Jadwal</b></td><td>Sampai jam interview</td><td>Tercatat <span class="b b-g">Konfirmasi Hadir</span>. Permintaan jadwal ulang yang masih menunggu ikut dibatalkan.</td></tr>
<tr><td><b>Kirim Permintaan Jadwal Ulang</b></td><td>Paling lambat <b>H-1</b></td><td>Pelamar mengisi <b>Tanggal usulan</b> (paling cepat besok), <b>Jam usulan (WITA)</b>, dan <b>Alasan</b>. Tercatat <span class="b b-y">Minta Jadwal Ulang</span>; jadwal lama tetap berlaku sampai ada undangan baru.</td></tr>
<tr><td><b>Batalkan Interview</b></td><td>Sampai jam interview</td><td>Pelamar mengisi <b>Alasan</b>. Jadwal langsung berstatus Dibatalkan dan tercatat <span class="b b-r">Dibatalkan Pelamar</span>, sehingga HR tidak menunggu.</td></tr>
</tbody>
</table>
${ss2('ats_jawab_ulang_form', 'ats_jawab_batal_form', 'Form minta jadwal ulang', 'Form batalkan interview')}
${ssOnly('ats_email_undangan', 'Email undangan interview dengan tombol jawaban')}
<div class="callout">Untuk permintaan jadwal ulang dan pembatalan, <b>HR yang menjadwalkan</b> menerima email pemberitahuan berisi tautan ke halaman pelamar. Bila akun itu tidak aktif, email dikirim ke <b>Email HRD</b> di Pengaturan.</div>

<h3>Menindaklanjuti Permintaan Jadwal Ulang</h3>
<p style="text-align:justify">
Di kartu interview halaman pelamar muncul kotak <b>Pelamar minta jadwal ulang</b> berisi usulan dan
alasannya, dengan tiga pilihan:
</p>
<table>
<thead><tr><th>Tombol</th><th>Hasil</th></tr></thead>
<tbody>
<tr><td><span class="btn-ref">Setujui &amp; Kirim Undangan</span></td><td>Jadwal dipindah ke usulan pelamar dan undangan baru langsung terkirim. Bila waktu usulan sudah lewat, HR diarahkan ke form ubah jadwal.</td></tr>
<tr><td><span class="btn-ref-outline">Atur Waktu Lain</span></td><td>Membuka form ubah jadwal yang sudah terisi usulan pelamar, dengan <b>Kirim undangan baru ke pelamar</b> tercentang. Ubah tanggal/jam lalu <span class="btn-ref">Simpan Perubahan</span>.</td></tr>
<tr><td><span class="btn-ref-outline">Tolak</span></td><td>Isi <b>Pesan ke pelamar (opsional)</b> lalu <span class="btn-ref">Tolak &amp; Beri Tahu Pelamar</span>. Jadwal tetap; pelamar menerima email "Jadwal Ulang Tidak Disetujui" dengan tombol jawaban untuk konfirmasi lagi.</td></tr>
</tbody>
</table>
${ssOnly('ats_interview_ubah_usulan', 'Form ubah jadwal terisi usulan pelamar')}
<div class="callout warn">Setiap kali <b>tanggal atau jam</b> diubah, tombol di email lama otomatis tidak berlaku dan jawaban pelamar kembali ke <span class="b b-gray">Belum Menjawab</span>. Centang <b>Kirim undangan baru ke pelamar</b> agar pelamar menerima tombol yang baru. Pelamar yang membuka tautan lama melihat halaman <b>Tautan Tidak Berlaku</b>.</div>

<h3>Penilaian Pewawancara</h3>
<p style="text-align:justify">
Penilaian diisi lewat tombol <span class="btn-ref-outline">Isi Penilaian</span> pada kartu interview.
Ada lima aspek skala 1&ndash;5 — <b>Kemampuan Teknis</b>, <b>Komunikasi</b>, <b>Sikap &amp;
Kedisiplinan</b>, <b>Pengalaman Relevan</b>, <b>Kecocokan Budaya</b> — lalu <b>Rekomendasi</b> (Sangat
Direkomendasikan hingga Sangat Tidak Direkomendasikan), kelebihan, kekurangan, dan <b>Catatan Lain</b>.
Klik <span class="btn-ref">Simpan Penilaian</span>: nilai akhir dihitung dari aspek yang diisi,
rata-rata skor kandidat diperbarui, dan jadwal otomatis ditandai <b>Selesai</b>.
</p>
${ssOnly('ats_penilaian', 'Formulir penilaian pewawancara')}

<h2><div class="num">10</div> Kandidat (Bank Talenta)</h2>
<div class="rule"></div>
<p style="text-align:justify">
Menu <b>Kandidat</b> (halaman <b>Bank Talenta</b>) memuat semua orang yang pernah melamar, lintas
lowongan — berguna menemukan kembali kandidat lama saat ada lowongan baru yang cocok. Ketik di kolom
pencarian (nama, email, telepon, sekolah, atau posisi) lalu klik <span class="btn-ref">Cari</span>.
Detail kandidat menampilkan <b>Riwayat Lamaran</b>. Admin HRD dapat mengisi <b>Alasan blacklist</b>
lalu <span class="btn-ref-outline">Blacklist</span>, atau mencabutnya dengan
<span class="btn-ref-outline">Lepas Blacklist</span>.
</p>
${ssOnly('ats_kandidat', 'Bank talenta kandidat')}

<h2><div class="num">11</div> Email</h2>
<div class="rule"></div>
<p style="text-align:justify">
Semua email ke pelamar — otomatis maupun manual — masuk <b>antrean</b> lebih dulu sebelum dikirim.
Gangguan server email tidak pernah menggagalkan aksi HR, setiap kiriman punya jejak, dan yang gagal
bisa dikirim ulang. Menu <b>Email</b> (khusus Admin HRD dan Super Admin) punya tab <b>Semua</b>,
<b>Antre</b>, <b>Terkirim</b>, dan <b>Gagal</b>, tombol <span class="btn-ref-outline">Kirim</span> per
baris, serta <span class="btn-ref">Kirim Semua Tertunda</span>.
</p>
${ssOnly('ats_email', 'Antrean email')}
<p style="text-align:justify">
Tombol <span class="btn-ref-outline">Template</span> membuka daftar template (Nama, Subjek, Kirim
Otomatis, Status). Saat mengubah template, isi <b>Nama Template</b>, <b>Subjek</b>, dan <b>Isi Email
(boleh HTML)</b>, atur centang <b>Aktif</b> dan <b>Kirim otomatis</b>, lalu klik
<span class="btn-ref">Simpan Template</span>. Panel <b>Placeholder Tersedia</b> mendaftar isian
seperti <b>{{nama_kandidat}}</b>, <b>{{posisi}}</b>, dan <b>{{tanggal_interview}}</b> yang terisi
otomatis saat dikirim.
</p>
${ssOnly('ats_email_template', 'Daftar template email')}
<table>
<thead><tr><th>Template Jawaban Interview</th><th>Penerima</th><th>Kapan</th></tr></thead>
<tbody>
<tr><td>Permintaan Jadwal Ulang (ke HR)</td><td>HR yang menjadwalkan</td><td>Pelamar minta jadwal ulang.</td></tr>
<tr><td>Interview Dibatalkan Pelamar (ke HR)</td><td>HR yang menjadwalkan</td><td>Pelamar membatalkan interview.</td></tr>
<tr><td>Jadwal Ulang Tidak Disetujui</td><td>Pelamar</td><td>HR menolak permintaan jadwal ulang.</td></tr>
</tbody>
</table>
<div class="callout">Placeholder <b>{{tombol_jawaban}}</b> menaruh tombol jawaban di template Undangan Interview, Pengingat Interview, dan Jadwal Ulang Tidak Disetujui. Bila terhapus, tombol tetap ditambahkan di akhir email agar pelamar selalu bisa menjawab.</div>
<div class="callout ok">Email dikirim dari <b>recruitment@wbl-bsb.com</b>. Antrean dikirim otomatis setiap 5 menit, dan pengingat interview dikirim menjelang jadwal. Setiap email yang terkirim juga tersalin ke folder <b>Sent</b> kotak surat pengirim, sehingga bisa dilihat dari webmail atau Outlook.</div>

<h2><div class="num">12</div> Laporan</h2>
<div class="rule"></div>
<p style="text-align:justify">
Menu <b>Laporan</b> (khusus Admin HRD dan Super Admin) menyajikan kartu <b>Lamaran Masuk</b>,
<b>Sedang Aktif</b>, <b>Diterima</b>, dan <b>Tidak Lolos</b>, lalu <b>Corong Rekrutmen</b> (berapa lamaran
yang pernah mencapai tiap tahap), <b>Rata-rata Lama di Tahap</b>, dan rekap <b>Per Lowongan</b>.
</p>
${ssOnly('ats_laporan', 'Laporan rekrutmen')}

<h2><div class="num">13</div> Master Data</h2>
<div class="rule"></div>
<p style="text-align:justify">
Master data adalah pengaturan dasar yang dipakai seluruh aplikasi. Hanya Admin HRD dan Super Admin
yang dapat mengubahnya; Unit Usaha khusus Super Admin.
</p>

<h3>Unit Usaha</h3>
<p style="text-align:justify">
Daftar unit usaha WBL dengan isian <b>Kode</b>, <b>Nama Unit</b>, <b>Kota / Alamat</b>, dan <b>Urutan
Tampil</b>. Unit adalah akar pembatasan akses — data setiap unit terpisah. Kode unit tidak bisa
diubah karena menjadi awalan kode lowongan dan tampil sebagai pelat kode di portal karir. Unit yang
masih dipakai tidak dihapus, melainkan dinonaktifkan.
</p>
${ssOnly('ats_master_unit', 'Master unit usaha')}

<h3>Departemen</h3>
<p style="text-align:justify">
Tambah departemen lewat panel <b>Tambah Departemen</b> (<b>Unit Usaha</b> dan <b>Nama Departemen</b>
wajib). Departemen terikat pada satu unit dan tidak bisa berpindah. Saat membuat lowongan, pilihan
departemen otomatis tersaring mengikuti unit. Klik ikon pensil untuk mengubah satu baris.
</p>
${ssOnly('ats_master_departemen', 'Master departemen')}

<h3>Tahapan Pipeline</h3>
<p style="text-align:justify">
Tahapan menentukan susunan papan pipeline dan bagian <b>Tahapan seleksi</b> di portal karir. Urutan
diatur dengan <b>menyeret</b> baris lewat pegangan di kiri. Setiap tahap punya jenis (Berjalan,
Diterima, Tidak Lolos) yang menentukan status lamaran, serta warna penanda. Tahap yang masih dipakai
lamaran dinonaktifkan, bukan dihapus.
</p>
${ssOnly('ats_master_tahapan', 'Master tahapan dengan drag-and-drop')}
<div class="callout">Sebaiknya ada tepat satu tahap berjenis <b>Diterima</b> dan satu <b>Tidak Lolos</b>, karena status lamaran diturunkan dari jenis tahap.</div>

<h3>Pengguna</h3>
<p style="text-align:justify">
Kelola akun staf HRD dan pewawancara: <b>Nama</b>, <b>Email</b>, <b>Peran</b>, <b>Unit</b>, Jabatan,
Telepon, <b>Kata Sandi</b>, dan <b>Akun aktif</b>. Kata sandi wajib untuk pengguna baru; saat mengubah
pengguna, kosongkan bila tidak ingin mengganti sandinya. Email harus unik antarakun. Ikon hapus
<b>menonaktifkan</b> pengguna agar jejaknya tetap utuh; akun sendiri tidak bisa dinonaktifkan. Admin
HRD hanya mengelola pengguna di unitnya dan tidak bisa memberi peran Super Admin.
</p>
${ssOnly('ats_master_pengguna', 'Master pengguna')}
<div class="callout">Pastikan email setiap akun HR adalah kotak surat yang benar-benar dibaca: pemberitahuan jadwal ulang dan pembatalan interview dikirim ke email HR yang menjadwalkan.</div>

<h3>Pengaturan</h3>
<p style="text-align:justify">
Pengaturan berlaku untuk seluruh unit dan terbagi menjadi <b>Identitas Perusahaan</b> (Nama
Perusahaan, Alamat, Email HRD, Telepon HRD), <b>Portal Karir</b> (Tagline dan Pengantar yang tampil di
halaman depan), <b>Lamaran</b> (Maks. CV, Format CV, pengingat interview berapa jam sebelumnya, dan
<b>Kirim email konfirmasi otomatis saat lamaran masuk</b>), serta <b>Bobot Skor Potensi Kandidat</b>
(bobot relatif, tidak harus berjumlah 100).
</p>
${ssOnly('ats_pengaturan', 'Halaman pengaturan')}
`;
}
module.exports = { atsContent };
