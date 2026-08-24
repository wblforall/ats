<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($title ?? 'Karir') ?> · <?= esc($settings['company_name'] ?? 'PT. Wulandari Bangun Laksana Tbk') ?></title>
    <meta name="description" content="<?= esc($settings['career_intro'] ?? 'Lowongan kerja terbaru.') ?>">
    <link rel="icon" href="<?= base_url('favicon.ico') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/app.css') ?>?v=<?= APP_ASSET_VERSION ?>">
</head>
<body class="public">

<header class="pub-header">
    <div class="bar">
        <a href="<?= site_url('karir') ?>" class="pub-brand">
            <span class="brand-plate">
                <img src="<?= base_url('assets/img/logo-wbl.png') ?>" alt="PT. Wulandari Bangun Laksana Tbk">
            </span>
            <span>
                <b><?= esc($settings['company_name'] ?? 'PT. Wulandari Bangun Laksana Tbk') ?></b>
                <small>Karir</small>
            </span>
        </a>
        <nav class="pub-nav">
            <a href="<?= site_url('karir') ?>">Lowongan</a>
            <a href="<?= site_url('lacak') ?>" class="solid">Lacak Lamaran</a>
        </nav>
    </div>

    <?= $this->renderSection('hero') ?>
</header>

<main class="pub-main">
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-error"><?= icon('alert') ?><span><?= esc(session()->getFlashdata('error')) ?></span></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('info')): ?>
        <div class="alert alert-info"><?= icon('info') ?><span><?= esc(session()->getFlashdata('info')) ?></span></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success"><?= icon('check') ?><span><?= esc(session()->getFlashdata('success')) ?></span></div>
    <?php endif; ?>

    <?= $this->renderSection('content') ?>
</main>

<footer class="pub-foot">
    &copy; <?= date('Y') ?> <?= esc($settings['company_name'] ?? 'PT. Wulandari Bangun Laksana Tbk') ?>.
    <?php if (! empty($settings['hr_email'])): ?>
        Pertanyaan seputar rekrutmen: <a href="mailto:<?= esc($settings['hr_email']) ?>"><?= esc($settings['hr_email']) ?></a>.
    <?php endif; ?>
    <br>
    Kami tidak pernah memungut biaya apa pun dalam proses rekrutmen.
</footer>

<script src="<?= base_url('assets/js/app.js') ?>?v=<?= APP_ASSET_VERSION ?>"></script>
</body>
</html>
