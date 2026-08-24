<?= $this->extend('layouts/public') ?>

<?= $this->section('hero') ?>
<div class="pub-hero">
    <h1>Lamaran Anda Terkirim</h1>
    <p>Simpan nomor lamaran di bawah ini — inilah kunci untuk memantau prosesnya.</p>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="pub-panel" style="max-width:620px; margin:0 auto; text-align:center;">
    <div style="width:56px; height:56px; margin:4px auto 16px; border-radius:50%; background:rgba(22,163,74,.12); color:var(--green); display:grid; place-items:center;">
        <?= icon('check', 'ico-lg') ?>
    </div>

    <div class="stat-label">Nomor Lamaran Anda</div>
    <div style="font-size:27px; font-weight:700; color:var(--blue); letter-spacing:.5px; margin:6px 0 4px;">
        <?= esc($ref) ?>
    </div>
    <p style="color:var(--ink-soft); font-size:13.5px; margin:0 0 20px;">
        Salinan nomor ini juga kami kirim ke <b><?= esc($email) ?></b>.
        Bila tidak ada di kotak masuk, periksa folder spam.
    </p>

    <div class="alert alert-info" style="text-align:left;">
        <?= icon('info') ?>
        <span>
            Untuk melacak status, Anda perlu <b>nomor lamaran dan email</b> yang sama.
            Keduanya diminta bersama agar status lamaran Anda tidak bisa dilihat orang lain.
        </span>
    </div>

    <div style="display:flex; gap:10px; justify-content:center; flex-wrap:wrap; margin-top:6px;">
        <a href="<?= site_url('lacak') ?>" class="btn btn-primary"><?= icon('search') ?> Lacak Lamaran</a>
        <a href="<?= site_url('karir') ?>" class="btn btn-ghost"><?= icon('briefcase') ?> Lihat Lowongan Lain</a>
    </div>
</div>

<?= $this->endSection() ?>
