<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($title ?? 'Masuk') ?> · WBL ATS</title>
    <meta name="theme-color" content="#0B1F45">
    <meta name="robots" content="noindex">
    <link rel="icon" href="<?= base_url('favicon.ico') ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Archivo:wdth,wght@100..125,600..800&family=Plus+Jakarta+Sans:wght@400..800&display=swap">
    <link rel="stylesheet" href="<?= base_url('assets/css/app.css') ?>?v=<?= APP_ASSET_VERSION ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/career.css') ?>?v=<?= APP_ASSET_VERSION ?>">
</head>
<body class="auth">

<!-- Halaman masuk staf: panel kanopi (sama dengan portal karir) + formulir. -->
<aside class="auth-side">
    <?= view('partials/canopy', ['variant' => 'hero']) ?>
    <a href="<?= site_url('karir') ?>" class="auth-back">← Portal karir</a>
    <div class="auth-side-copy rise">
        <span class="hero-eyebrow">WBL ATS</span>
        <h2>Ruang kerja rekrutmen.</h2>
        <p>Lowongan, pelamar, dan jadwal interview seluruh unit usaha WBL dikelola dalam satu sistem.</p>
    </div>
</aside>

<main class="auth-main">
    <div class="auth-card rise" style="--d:.08s">
        <img class="auth-logo" src="<?= base_url('assets/img/logo-wbl.png') ?>" alt="PT. Wulandari Bangun Laksana Tbk">
        <h1><?= esc($heading ?? $title ?? 'Masuk') ?></h1>
        <?php if (! empty($lead)): ?>
            <p class="lead"><?= esc($lead) ?></p>
        <?php endif; ?>

        <?php foreach (['error' => 'alert', 'info' => 'info', 'success' => 'check'] as $jenis => $ikon): ?>
            <?php if (session()->getFlashdata($jenis)): ?>
                <div class="alert alert-<?= $jenis ?>"><?= icon($ikon) ?><span><?= esc(session()->getFlashdata($jenis)) ?></span></div>
            <?php endif; ?>
        <?php endforeach; ?>

        <?= $this->renderSection('form') ?>
    </div>
    <p class="auth-foot">&copy; <?= date('Y') ?> PT. Wulandari Bangun Laksana Tbk · Khusus staf HRD &amp; pewawancara</p>
</main>

</body>
</html>
