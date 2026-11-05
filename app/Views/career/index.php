<?= $this->extend('layouts/public') ?>

<?= $this->section('hero') ?>
<div class="pub-hero hero-home">
    <span class="hero-eyebrow rise"><?= esc($settings['career_tagline'] ?? 'Bergabunglah bersama kami') ?></span>
    <h1 class="rise" style="--d:.08s" lang="en">Your future <em>starts here</em>.</h1>
    <?php if (! empty($settings['career_intro'])): ?>
        <p class="rise" style="--d:.16s"><?= esc($settings['career_intro']) ?></p>
    <?php endif; ?>
    <?php if ($allUnits !== []): ?>
        <ul class="hero-units rise" style="--d:.24s" aria-label="Unit usaha">
            <?php foreach ($allUnits as $unit): ?>
                <li><b><?= esc($unit['code']) ?></b><?= esc($unit['name']) ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<?php $adaFilter = $filters['q'] !== '' || $filters['unit'] || $filters['tipe'] !== ''; ?>

<form method="get" action="<?= site_url('karir') ?>" class="search-card rise" style="--d:.32s" role="search">
    <label>
        <span>Posisi</span>
        <input type="search" name="q" value="<?= esc($filters['q']) ?>" placeholder="mis. teknisi, akuntansi">
    </label>
    <label>
        <span>Unit usaha</span>
        <select name="unit">
            <option value="">Semua unit</option>
            <?php foreach ($units as $unit): ?>
                <option value="<?= $unit['id'] ?>" <?= $filters['unit'] === (int) $unit['id'] ? 'selected' : '' ?>><?= esc($unit['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <label>
        <span>Jenis</span>
        <select name="tipe">
            <option value="">Semua jenis</option>
            <?php foreach (\App\Models\JobModel::EMPLOYMENT_TYPES as $key => $label): ?>
                <?php if (in_array($key, $jenisAda, true)): ?>
                    <option value="<?= $key ?>" <?= $filters['tipe'] === $key ? 'selected' : '' ?>><?= $label ?></option>
                <?php endif; ?>
            <?php endforeach; ?>
        </select>
    </label>
    <button type="submit"><?= icon('search') ?> Cari</button>
</form>

<div class="section-head rise" style="--d:.4s">
    <h2>Lowongan dibuka</h2>
    <?php if ($jobs !== []): ?>
        <span class="count"><?= count($jobs) ?> posisi</span>
    <?php endif; ?>
    <?php if ($adaFilter): ?>
        <a href="<?= site_url('karir') ?>" class="reset">Hapus saringan</a>
    <?php endif; ?>
</div>

<div class="job-list rise" style="--d:.46s">
    <?php if ($jobs === []): ?>
        <div class="job-empty">
            <?= view('partials/canopy', ['variant' => 'mini']) ?>
            <?php if ($adaFilter): ?>
                <b>Tidak ada lowongan yang cocok.</b>
                <p>Coba kata kunci lain, atau tampilkan semua unit dan jenis pekerjaan.</p>
                <a href="<?= site_url('karir') ?>">Tampilkan semua lowongan →</a>
            <?php else: ?>
                <b>Belum ada lowongan dibuka saat ini.</b>
                <p>Lowongan baru dari seluruh unit usaha WBL diumumkan di halaman ini begitu dibuka.</p>
                <a href="<?= site_url('lacak') ?>">Sudah melamar? Lacak lamaran →</a>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <?php foreach ($jobs as $job): ?>
            <a href="<?= site_url('karir/' . $job['slug']) ?>" class="job-row">
                <span class="plate" title="<?= esc($job['unit_name']) ?>"><?= esc($job['unit_code'] ?: 'WBL') ?></span>
                <span>
                    <span class="jr-title"><?= esc($job['title']) ?></span>
                    <span class="jr-sub"><?= esc($job['unit_name']) ?><?= $job['department'] ? ' · ' . esc($job['department']) : '' ?></span>
                    <span class="jr-chips">
                        <span><?= \App\Models\JobModel::EMPLOYMENT_TYPES[$job['employment_type']] ?></span>
                        <?php if ($job['work_location']): ?><span><?= esc($job['work_location']) ?></span><?php endif; ?>
                        <?php if ((int) $job['openings'] > 1): ?><span><?= (int) $job['openings'] ?> orang</span><?php endif; ?>
                        <?php if ($job['salary_visible'] && $job['salary_min']): ?><span class="pay"><?= rupiah((float) $job['salary_min']) ?>+</span><?php endif; ?>
                    </span>
                </span>
                <span class="jr-end">
                    <?php if ($job['closing_date']): ?>
                        <small>Ditutup</small><b><?= tgl($job['closing_date']) ?></b>
                    <?php else: ?>
                        <small>Dibuka</small><b><?= sejak($job['published_at']) ?></b>
                    <?php endif; ?>
                </span>
                <span class="jr-arrow" aria-hidden="true">→</span>
            </a>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php if ($flow !== []): ?>
    <div class="section-head">
        <h2>Tahapan seleksi</h2>
    </div>
    <div class="pub-panel">
        <ol class="stages">
            <?php foreach ($flow as $stage): ?>
                <li><b><?= esc($stage['name']) ?></b></li>
            <?php endforeach; ?>
            <li class="end"><b>Bergabung</b></li>
        </ol>
    </div>
    <p class="stages-note">Setelah melamar, Anda bisa memantau tahap yang sedang berjalan kapan saja lewat Lacak Lamaran.</p>
<?php endif; ?>

<div class="cta-band">
    <div>
        <h2>Sudah melamar?</h2>
        <p>Masukkan nomor lamaran dan email Anda untuk melihat tahap seleksi saat ini. Tidak perlu membuat akun.</p>
    </div>
    <a href="<?= site_url('lacak') ?>" class="btn"><?= icon('search') ?> Lacak lamaran</a>
</div>

<?= $this->endSection() ?>
