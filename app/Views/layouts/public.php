<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($title ?? 'Karir') ?> · <?= esc($settings['company_name'] ?? 'PT. Wulandari Bangun Laksana Tbk') ?></title>
    <meta name="description" content="<?= esc($settings['career_intro'] ?? 'Lowongan kerja terbaru.') ?>">
    <meta name="theme-color" content="#0B1F45">
    <link rel="icon" href="<?= base_url('favicon.ico') ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Archivo:wdth,wght@100..125,600..800&family=Plus+Jakarta+Sans:wght@400..800&display=swap">
    <link rel="stylesheet" href="<?= base_url('assets/css/app.css') ?>?v=<?= APP_ASSET_VERSION ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/career.css') ?>?v=<?= APP_ASSET_VERSION ?>">
    <script>
        /* Animasi loading hanya sekali per kunjungan; halaman berikutnya langsung tampil. */
        try {
            if (!sessionStorage.getItem('wblLoader') && !matchMedia('(prefers-reduced-motion: reduce)').matches) {
                document.documentElement.classList.add('loader-on');
                sessionStorage.setItem('wblLoader', '1');
            }
        } catch (e) {}
    </script>
</head>
<body class="public">

<div class="pub-loader" aria-hidden="true">
    <div class="pub-loader-mark">
        <img src="<?= base_url('assets/img/logo-wbl.png') ?>" alt="">
        <?= view('partials/canopy', ['variant' => 'logo']) ?>
    </div>
    <div class="pub-loader-bar"></div>
</div>

<header class="pub-header">
    <?= view('partials/canopy', ['variant' => 'hero']) ?>
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
            <?php if (! session()->has('user_id')): ?>
                <a href="<?= site_url('login') ?>" class="pub-login" title="Masuk untuk staf HRD"><?= icon('user') ?> Masuk</a>
            <?php else: ?>
                <a href="<?= site_url('dashboard') ?>" class="pub-login" title="Buka dashboard HRD"><?= icon('dashboard') ?> Dashboard</a>
            <?php endif; ?>
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
    <div style="max-width:1100px; margin:0 auto;">
        &copy; <?= date('Y') ?> <?= esc($settings['company_name'] ?? 'PT. Wulandari Bangun Laksana Tbk') ?>.
        <?php if (! empty($settings['hr_email'])): ?>
            Pertanyaan seputar rekrutmen: <a href="mailto:<?= esc($settings['hr_email']) ?>"><?= esc($settings['hr_email']) ?></a>.
        <?php endif; ?>
        <br>
        <span class="nofee"><?= icon('shield') ?> Kami tidak pernah memungut biaya apa pun dalam proses rekrutmen.</span>
    </div>
</footer>

<script src="<?= base_url('assets/js/app.js') ?>?v=<?= APP_ASSET_VERSION ?>"></script>
<?= $this->renderSection('scripts') ?>
</body>
</html>
