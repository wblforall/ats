<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($title ?? 'Dashboard') ?> · WBL ATS</title>
    <link rel="icon" href="<?= base_url('favicon.ico') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/app.css') ?>?v=<?= APP_ASSET_VERSION ?>">
    <?= $this->renderSection('head') ?>
</head>
<body>
<div class="app">
    <div class="app-shell">
        <?= $this->include('partials/topbar') ?>

        <div class="shell-body">
            <?= $this->include('partials/sidebar') ?>

            <main class="content">
                <?php if (session()->getFlashdata('success')): ?>
                    <div class="alert alert-success"><?= icon('check') ?><span><?= esc(session()->getFlashdata('success')) ?></span></div>
                <?php endif; ?>
                <?php if (session()->getFlashdata('error')): ?>
                    <div class="alert alert-error"><?= icon('alert') ?><span><?= esc(session()->getFlashdata('error')) ?></span></div>
                <?php endif; ?>
                <?php if (session()->getFlashdata('info')): ?>
                    <div class="alert alert-info"><?= icon('info') ?><span><?= esc(session()->getFlashdata('info')) ?></span></div>
                <?php endif; ?>

                <?php
                /* Daftar galat validasi. Ditaruh di layout, bukan di tiap
                   halaman, supaya kegagalan simpan tidak pernah lolos tanpa
                   pesan hanya karena satu tampilan lupa menampilkannya. */
                $galatValidasi = session()->getFlashdata('errors');
                ?>
                <?php if (! empty($galatValidasi)): ?>
                    <div class="alert alert-error">
                        <?= icon('alert') ?>
                        <span>
                            <b>Periksa kembali isian berikut:</b>
                            <ul style="margin:6px 0 0; padding-left:18px;">
                                <?php foreach ((array) $galatValidasi as $pesan): ?>
                                    <li><?= esc($pesan) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </span>
                    </div>
                <?php endif; ?>

                <?= $this->renderSection('content') ?>
            </main>
        </div>
    </div>
</div>

<script src="<?= base_url('assets/js/app.js') ?>?v=<?= APP_ASSET_VERSION ?>"></script>
<?= $this->renderSection('scripts') ?>
</body>
</html>
