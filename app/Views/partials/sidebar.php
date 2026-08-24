<?php
/**
 * Sidebar biru di dalam kartu putih (mengikuti referensi dashboard).
 * Menu disaring per peran: hr_staff tak melihat master data, interviewer
 * hanya melihat agenda & penilaiannya sendiri.
 */
$role   = $auth['role'] ?? 'hr_staff';
$isBoss = in_array($role, ['superadmin', 'hr_admin'], true);
$active = $active ?? '';

$menu = [
    ['dashboard',  'Dashboard',  'dashboard', site_url('dashboard'),   true],
    ['jobs',       'Lowongan',   'briefcase', site_url('lowongan'),    $role !== 'interviewer'],
    ['pipeline',   'Pipeline',   'pipeline',  site_url('pipeline'),    $role !== 'interviewer'],
    ['shortlist',  'Unggulan',   'star',      site_url('unggulan'),    $role !== 'interviewer'],
    ['apps',       'Pelamar',    'users',     site_url('pelamar'),     $role !== 'interviewer'],
    ['interviews', 'Interview',  'calendar',  site_url('interview'),   true],
    ['candidates', 'Kandidat',   'user',      site_url('kandidat'),    $role !== 'interviewer'],
    ['emails',     'Email',      'mail',      site_url('email'),       $isBoss],
    ['reports',    'Laporan',    'chart',     site_url('laporan'),     $isBoss],
];

$master = [
    ['units',       'Unit Usaha', 'building', site_url('master/unit'),       $role === 'superadmin'],
    ['departments', 'Departemen', 'layers',   site_url('master/departemen'), $isBoss],
    ['stages',      'Tahapan',    'layers',   site_url('master/tahapan'),    $isBoss],
    ['users',       'Pengguna',   'shield',   site_url('master/pengguna'),   $isBoss],
    ['settings',    'Pengaturan', 'settings', site_url('pengaturan'),        $isBoss],
];
?>
<aside class="sidebar">
    <?php if ($role !== 'interviewer'): ?>
        <a href="<?= site_url('lowongan/baru') ?>" class="btn-cta">
            <?= icon('plus') ?> Lowongan Baru
        </a>
    <?php endif; ?>

    <nav class="nav">
        <?php foreach ($menu as [$key, $label, $ico, $url, $show]): ?>
            <?php if ($show): ?>
                <a href="<?= $url ?>" class="nav-item<?= $active === $key ? ' is-active' : '' ?>">
                    <?= icon($ico) ?> <span><?= $label ?></span>
                    <?php if ($key === 'apps' && ! empty($newApplicantCount)): ?>
                        <span class="badge-count"><?= $newApplicantCount ?></span>
                    <?php endif; ?>
                </a>
            <?php endif; ?>
        <?php endforeach; ?>

        <?php if (array_filter(array_column($master, 4))): ?>
            <div class="nav-label">Master Data</div>
            <?php foreach ($master as [$key, $label, $ico, $url, $show]): ?>
                <?php if ($show): ?>
                    <a href="<?= $url ?>" class="nav-item<?= $active === $key ? ' is-active' : '' ?>">
                        <?= icon($ico) ?> <span><?= $label ?></span>
                    </a>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php endif; ?>
    </nav>

    <div class="sidebar-foot">
        <div><?= esc($auth['unit_name'] ?? 'Semua Unit') ?></div>
        <div class="meter"><span style="width: <?= (int) ($openJobPercent ?? 0) ?>%"></span></div>
        <div><?= (int) ($openJobCount ?? 0) ?> lowongan aktif</div>
    </div>
</aside>
