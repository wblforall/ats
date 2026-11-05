<?php $this->setVar('title', 'Reset Sandi') ?>
<?php $this->setVar('heading', 'Kata sandi baru') ?>
<?php $this->setVar('lead', 'Buat kata sandi baru untuk akun Anda, minimal 6 karakter.') ?>
<?= $this->extend('layouts/auth') ?>

<?= $this->section('form') ?>
<form action="<?= site_url('reset/' . esc($token, 'url')) ?>" method="post">
    <?= csrf_field() ?>
    <div class="form-row">
        <label for="password">Kata sandi baru</label>
        <input type="password" id="password" name="password" class="form-control" required minlength="6" autocomplete="new-password">
    </div>
    <div class="form-row">
        <label for="password_confirm">Ulangi kata sandi</label>
        <input type="password" id="password_confirm" name="password_confirm" class="form-control" required autocomplete="new-password">
    </div>
    <button type="submit" class="btn btn-primary"><?= icon('check') ?> Simpan kata sandi baru</button>
</form>
<?= $this->endSection() ?>
