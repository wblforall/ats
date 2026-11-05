<?php $this->setVar('title', 'Masuk') ?>
<?php $this->setVar('lead', 'Gunakan akun HRD atau pewawancara Anda.') ?>
<?= $this->extend('layouts/auth') ?>

<?= $this->section('form') ?>
<form action="<?= site_url('login') ?>" method="post" autocomplete="on">
    <?= csrf_field() ?>

    <div class="form-row">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" class="form-control"
               value="<?= esc(old('email')) ?>" required autofocus autocomplete="username" placeholder="nama@wbl.co.id">
    </div>

    <div class="form-row">
        <label for="password">Kata sandi</label>
        <input type="password" id="password" name="password" class="form-control" required autocomplete="current-password" placeholder="••••••••">
    </div>

    <button type="submit" class="btn btn-primary">Masuk</button>
</form>

<p class="auth-alt"><a href="<?= site_url('lupa-sandi') ?>">Lupa kata sandi?</a></p>
<?= $this->endSection() ?>
