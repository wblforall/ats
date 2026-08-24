<!DOCTYPE html>
<html lang="id"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Reset Sandi · WBL ATS</title>
<link rel="stylesheet" href="<?= base_url('assets/css/app.css') ?>?v=<?= APP_ASSET_VERSION ?>">
</head><body class="auth">
<div class="auth-card">
    <div class="brand"><img src="<?= base_url('assets/img/logo-wbl.png') ?>" alt="WBL"><span><h1 style="margin:0;font-size:17px;">Sandi Baru</h1><small style="color:var(--ink-faint);">Buat kata sandi baru</small></span></div>
    <?php if (session()->getFlashdata('error')): ?><div class="alert alert-error"><?= icon('alert') ?><span><?= esc(session()->getFlashdata('error')) ?></span></div><?php endif; ?>
    <form action="<?= site_url('reset/' . esc($token, 'url')) ?>" method="post">
        <?= csrf_field() ?>
        <div class="form-row"><label for="password">Kata Sandi Baru</label><input type="password" id="password" name="password" class="form-control" required minlength="6" autocomplete="new-password"></div>
        <div class="form-row"><label for="password_confirm">Ulangi Sandi</label><input type="password" id="password_confirm" name="password_confirm" class="form-control" required autocomplete="new-password"></div>
        <button type="submit" class="btn btn-primary"><?= icon('check') ?> Simpan Sandi Baru</button>
    </form>
</div>
</body></html>
