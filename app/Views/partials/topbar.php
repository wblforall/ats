<?php
/** Topbar putih: tombol menu (mobile), pencarian global, notifikasi, chip user. */
$auth = $auth ?? [];
?>
<header class="topbar">
    <button type="button" class="icon-btn nav-toggle" id="navToggle" aria-label="Buka menu">
        <?= icon('menu') ?>
    </button>

    <a href="<?= site_url('dashboard') ?>" class="topbar-brand">
        <img src="<?= base_url('assets/img/logo-wbl.png') ?>" alt="PT. Wulandari Bangun Laksana Tbk">
        <span class="name">ATS Rekrutmen</span>
    </a>

    <form class="search" action="<?= site_url('cari') ?>" method="get" role="search">
        <?= icon('search') ?>
        <input type="search" name="q" value="<?= esc($searchQuery ?? '') ?>"
               placeholder="Cari pelamar, lowongan, atau email…" autocomplete="off">
    </form>

    <div class="topbar-actions">
        <a href="<?= site_url('interview') ?>" class="icon-btn" title="Jadwal interview terdekat">
            <?= icon('bell') ?>
            <?php if (! empty($upcomingInterviewCount)): ?><span class="dot"></span><?php endif; ?>
        </a>
        <a href="<?= site_url('bantuan') ?>" class="icon-btn" title="Bantuan"><?= icon('help') ?></a>

        <div class="dropdown" id="userDropdown">
            <a href="#" class="user-chip" id="userChipToggle">
                <span class="who">
                    <b><?= esc($auth['name'] ?? 'Pengguna') ?></b>
                    <small><?= esc($auth['role_label'] ?? '') ?></small>
                </span>
                <span class="avatar"><?= esc(initials($auth['name'] ?? null)) ?></span>
            </a>
            <div class="dropdown-menu">
                <a href="<?= site_url('profil') ?>"><?= icon('user') ?> Profil Saya</a>
                <?php if (in_array($auth['role'] ?? '', ['superadmin', 'hr_admin'], true)): ?>
                    <a href="<?= site_url('pengaturan') ?>"><?= icon('settings') ?> Pengaturan</a>
                <?php endif; ?>
                <hr>
                <a href="<?= site_url('logout') ?>"><?= icon('logout') ?> Keluar</a>
            </div>
        </div>
    </div>
</header>
