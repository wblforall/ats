<?= $this->extend('layouts/public') ?>

<?= $this->section('hero') ?>
<div class="pub-hero">
    <h1>Lacak Lamaran</h1>
    <p>Masukkan nomor lamaran dan email yang Anda pakai saat melamar.</p>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="pub-panel" style="max-width:520px; margin:0 auto;">
    <form action="<?= site_url('lacak') ?>" method="post">
        <?= csrf_field() ?>

        <div class="form-row">
            <label for="ref">Nomor Lamaran</label>
            <input type="text" id="ref" name="ref" class="form-control" required autofocus
                   value="<?= esc(old('ref')) ?>" placeholder="WBL-<?= date('Y') ?>-000123"
                   style="letter-spacing:.5px;">
            <div class="hint">Terdapat pada email konfirmasi lamaran Anda.</div>
        </div>

        <div class="form-row">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" class="form-control" required
                   value="<?= esc(old('email')) ?>" placeholder="nama@email.com">
            <div class="hint">Harus sama persis dengan email yang Anda isi di formulir lamaran.</div>
        </div>

        <button type="submit" class="btn btn-primary" style="width:100%; justify-content:center;">
            <?= icon('search') ?> Lihat Status
        </button>
    </form>

    <p style="font-size:12px; color:var(--ink-faint); margin:16px 0 0; line-height:1.6;">
        Kehilangan nomor lamaran? Hubungi
        <a href="mailto:<?= esc($settings['hr_email'] ?? '') ?>"><?= esc($settings['hr_email'] ?? 'HRD') ?></a>
        dengan menyebutkan nama lengkap dan posisi yang dilamar.
    </p>
</div>

<?= $this->endSection() ?>
