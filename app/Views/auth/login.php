<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk · WBL ATS</title>
    <link rel="icon" href="<?= base_url('favicon.ico') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/app.css') ?>?v=<?= APP_ASSET_VERSION ?>">
</head>
<body class="auth">
    <div class="auth-card">
        <div class="brand">
            <img src="<?= base_url('assets/img/logo-wbl.png') ?>" alt="PT. Wulandari Bangun Laksana Tbk">
            <span>
                <h1 style="margin:0;font-size:17px;">ATS Rekrutmen</h1>
                <small style="color:var(--ink-faint);">Sistem penerimaan karyawan</small>
            </span>
        </div>

        <p class="lead">Masuk memakai akun HRD Anda.</p>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-error"><?= icon('alert') ?><span><?= esc(session()->getFlashdata('error')) ?></span></div>
        <?php endif; ?>
        <?php if (session()->getFlashdata('info')): ?>
            <div class="alert alert-info"><?= icon('info') ?><span><?= esc(session()->getFlashdata('info')) ?></span></div>
        <?php endif; ?>

        <form action="<?= site_url('login') ?>" method="post" autocomplete="on">
            <?= csrf_field() ?>

            <div class="form-row">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" class="form-control"
                       value="<?= esc(old('email')) ?>" required autofocus placeholder="nama@wbl.co.id">
            </div>

            <div class="form-row">
                <label for="password">Kata Sandi</label>
                <input type="password" id="password" name="password" class="form-control" required placeholder="••••••••">
            </div>

            <button type="submit" class="btn btn-primary"><?= icon('logout') ?> Masuk</button>
        </form>

        <p style="font-size:12.5px; text-align:center; margin-top:16px;">
            <a href="<?= site_url('lupa-sandi') ?>">Lupa kata sandi?</a>
        </p>
    </div>
</body>
</html>
