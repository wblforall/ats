<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-head">
    <div><h1 class="page-title"><span class="chip"><?= icon('help') ?></span> Bantuan</h1>
    <p class="page-sub">Panduan singkat memakai ATS WBL</p></div>
</div>
<div class="grid" style="grid-template-columns: repeat(auto-fit, minmax(280px,1fr)); align-items:start;">
    <?php
    $topik = [
        ['briefcase', 'Membuat Lowongan', 'Menu Lowongan → Lowongan Baru. Kode & tautan publik dibuat otomatis. Ubah status ke "Dibuka" agar tayang di portal karir.'],
        ['users', 'Memproses Pelamar', 'Buka Pelamar → pilih orang → geser tahap, tambah catatan, unduh CV, dan lihat skor potensinya. Perpindahan tahap tercatat siapa & kapan.'],
        ['pipeline', 'Papan Pipeline', 'Menu Pipeline menampilkan pelamar aktif per tahap. Klik kartu untuk membuka detailnya.'],
        ['calendar', 'Menjadwalkan Interview', 'Dari halaman pelamar → Jadwalkan. Pilih waktu, mode, pewawancara. Undangan bisa langsung dikirim ke pelamar.'],
        ['star', 'Kandidat Unggulan', 'Menu Unggulan mengurutkan pelamar menurut skor potensi. Bobot sinyal bisa disetel di Pengaturan.'],
        ['mail', 'Email', 'Semua email masuk antrean lebih dulu. Kelola, kirim ulang, dan ubah template di menu Email.'],
        ['chart', 'Laporan', 'Corong rekrutmen, rata-rata lama tiap tahap, dan rekap per lowongan.'],
        ['shield', 'Peran Pengguna', 'Superadmin: semua unit & master. Admin HRD: penuh dalam unitnya. Staf HRD: kelola pelamar. Pewawancara: hanya agenda & penilaiannya.'],
    ];
    foreach ($topik as [$ico, $judul, $isi]): ?>
        <div class="card">
            <div style="display:flex; align-items:center; gap:10px; margin-bottom:8px;">
                <span style="width:32px;height:32px;border-radius:9px;background:var(--blue-hover);color:var(--blue-deep);display:grid;place-items:center;"><?= icon($ico) ?></span>
                <b style="font-size:14px;"><?= esc($judul) ?></b>
            </div>
            <p style="font-size:13px; color:var(--ink-soft); margin:0; line-height:1.6;"><?= esc($isi) ?></p>
        </div>
    <?php endforeach; ?>
</div>
<div class="card" style="margin-top:18px;">
    <p style="font-size:13px; margin:0; color:var(--ink-soft);">Butuh bantuan lebih lanjut? Hubungi administrator sistem atau tim IT WBL.</p>
</div>
<?= $this->endSection() ?>
