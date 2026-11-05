<?php $this->setVar('title', 'Lupa Sandi') ?>
<?php $this->setVar('heading', 'Lupa kata sandi') ?>
<?php $this->setVar('lead', 'Masukkan email akun Anda. Kami kirim tautan untuk membuat kata sandi baru.') ?>
<?= $this->extend('layouts/auth') ?>

<?= $this->section('form') ?>
<form action="<?= site_url('lupa-sandi') ?>" method="post">
    <?= csrf_field() ?>
    <div class="form-row">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" class="form-control" required autofocus autocomplete="username" placeholder="nama@wbl.co.id">
    </div>
    <button type="submit" class="btn btn-primary"><?= icon('mail') ?> Kirim tautan reset</button>
</form>

<p class="auth-alt"><a href="<?= site_url('login') ?>">← Kembali ke halaman masuk</a></p>
<?= $this->endSection() ?>
