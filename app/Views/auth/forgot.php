<!DOCTYPE html>
<html lang="id"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Lupa Sandi · WBL ATS</title>
<link rel="stylesheet" href="<?= base_url('assets/css/app.css') ?>?v=<?= APP_ASSET_VERSION ?>">
</head><body class="auth">
<div class="auth-card">
    <div class="brand"><img src="<?= base_url('assets/img/logo-wbl.png') ?>" alt="WBL"><span><h1 style="margin:0;font-size:17px;">Lupa Sandi</h1><small style="color:var(--ink-faint);">Reset akun HRD</small></span></div>
    <p class="lead">Masukkan email akun Anda. Kami kirim tautan untuk membuat sandi baru.</p>
    <?php if (session()->getFlashdata('error')): ?><div class="alert alert-error"><?= icon('alert') ?><span><?= esc(session()->getFlashdata('error')) ?></span></div><?php endif; ?>
    <form action="<?= site_url('lupa-sandi') ?>" method="post">
        <?= csrf_field() ?>
        <div class="form-row"><label for="email">Email</label><input type="email" id="email" name="email" class="form-control" required autofocus placeholder="nama@wbl.co.id"></div>
        <button type="submit" class="btn btn-primary"><?= icon('mail') ?> Kirim Tautan Reset</button>
    </form>
    <p style="font-size:12.5px; margin-top:16px;"><a href="<?= site_url('login') ?>">← Kembali ke halaman masuk</a></p>
</div>
</body></html>
