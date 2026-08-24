<?= $this->extend('layouts/public') ?>

<?= $this->section('hero') ?>
<div class="pub-hero">
    <h1><?= esc($settings['career_tagline'] ?? 'Bergabunglah bersama kami') ?></h1>
    <p><?= esc($settings['career_intro'] ?? '') ?></p>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<?php
/* Penjelasan tiap tahap dipetakan dari slug. Tahap buatan HR yang belum
   dikenali tetap tampil, hanya tanpa keterangan tambahan. */
$flowNotes = [
    'lamaran-masuk'  => 'Kirim data dan CV Anda lewat formulir. Nomor lamaran langsung dikirim ke email.',
    'seleksi-berkas' => 'Tim HRD memeriksa kesesuaian berkas dengan kebutuhan posisi.',
    'tes-asesmen'    => 'Uji kemampuan sesuai bidang, bisa daring maupun di kantor.',
    'interview-hr'   => 'Wawancara awal bersama tim HRD.',
    'interview-user' => 'Wawancara dengan calon atasan langsung Anda.',
    'penawaran'      => 'Pembahasan gaji, tunjangan, dan tanggal mulai bekerja.',
];
?>

<?php if ($flow !== []): ?>
    <div class="pub-panel" style="margin-bottom: 26px;">
        <h2 style="font-size:16px; margin:0 0 4px;">Alur Rekrutmen</h2>
        <p style="color:var(--ink-soft); font-size:13.5px; margin:0 0 16px;">
            Setiap tahap Anda lalui satu per satu. Statusnya bisa dipantau kapan saja lewat
            <a href="<?= site_url('lacak') ?>">halaman pelacakan</a>.
        </p>

        <div class="flow">
            <?php foreach ($flow as $i => $stage): ?>
                <div class="flow-step">
                    <div class="no"><?= $i + 1 ?></div>
                    <b><?= esc($stage['name']) ?></b>
                    <?php if (isset($flowNotes[$stage['slug']])): ?>
                        <small><?= esc($flowNotes[$stage['slug']]) ?></small>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>

<div class="pub-panel">
    <form method="get" action="<?= site_url('karir') ?>" class="pub-filters">
        <div class="form-row">
            <label for="q">Cari posisi</label>
            <input type="search" id="q" name="q" class="form-control"
                   value="<?= esc($filters['q']) ?>" placeholder="mis. teknisi, akuntansi">
        </div>

        <div class="form-row">
            <label for="unit">Unit usaha</label>
            <select id="unit" name="unit" class="form-control">
                <option value="">Semua unit</option>
                <?php foreach ($units as $unit): ?>
                    <option value="<?= $unit['id'] ?>" <?= $filters['unit'] === (int) $unit['id'] ? 'selected' : '' ?>>
                        <?= esc($unit['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-row">
            <label for="tipe">Jenis</label>
            <select id="tipe" name="tipe" class="form-control">
                <option value="">Semua jenis</option>
                <?php foreach (\App\Models\JobModel::EMPLOYMENT_TYPES as $key => $label): ?>
                    <option value="<?= $key ?>" <?= $filters['tipe'] === $key ? 'selected' : '' ?>><?= $label ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <button type="submit" class="btn btn-primary"><?= icon('search') ?> Cari</button>
        <?php if ($filters['q'] !== '' || $filters['unit'] || $filters['tipe'] !== ''): ?>
            <a href="<?= site_url('karir') ?>" class="btn btn-ghost">Reset</a>
        <?php endif; ?>
    </form>
</div>

<div style="display:flex; align-items:baseline; gap:10px; margin: 26px 2px 12px;">
    <h2 style="font-size:16px; margin:0; color:var(--ink);">
        <?= count($jobs) ?> lowongan tersedia
    </h2>
</div>

<?php if ($jobs === []): ?>
    <div class="pub-panel">
        <div class="empty">
            <?= icon('briefcase') ?>
            <div><b>Belum ada lowongan yang cocok.</b></div>
            <div style="margin-top:4px;">Coba ubah kata kunci atau saringan, dan cek kembali lain waktu.</div>
        </div>
    </div>
<?php else: ?>
    <div class="grid grid-2">
        <?php foreach ($jobs as $job): ?>
            <a href="<?= site_url('karir/' . $job['slug']) ?>" class="job-card">
                <h3><?= esc($job['title']) ?></h3>
                <div style="color:var(--ink-soft); font-size:13px;">
                    <?= esc($job['unit_name']) ?><?= $job['department'] ? ' · ' . esc($job['department']) : '' ?>
                </div>

                <div class="meta">
                    <span class="badge badge-blue"><?= \App\Models\JobModel::EMPLOYMENT_TYPES[$job['employment_type']] ?></span>
                    <?php if ($job['work_location']): ?>
                        <span class="badge badge-gray"><?= esc($job['work_location']) ?></span>
                    <?php endif; ?>
                    <?php if ((int) $job['openings'] > 1): ?>
                        <span class="badge badge-gray"><?= (int) $job['openings'] ?> posisi</span>
                    <?php endif; ?>
                    <?php if ($job['salary_visible'] && $job['salary_min']): ?>
                        <span class="badge badge-green"><?= rupiah((float) $job['salary_min']) ?>+</span>
                    <?php endif; ?>
                </div>

                <?php if ($job['description']): ?>
                    <p class="excerpt"><?= esc(mb_substr(strip_tags($job['description']), 0, 160)) ?></p>
                <?php endif; ?>

                <div style="display:flex; align-items:center; gap:6px; margin-top:12px; font-size:12px; color:var(--ink-faint);">
                    <?= icon('clock') ?>
                    <?php if ($job['closing_date']): ?>
                        Ditutup <?= tgl($job['closing_date']) ?>
                    <?php else: ?>
                        Dibuka <?= sejak($job['published_at']) ?>
                    <?php endif; ?>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<div class="cta-band">
    <div>
        <h2>Sudah pernah melamar?</h2>
        <p>
            Pantau sampai mana proses lamaran Anda memakai nomor lamaran dan email
            yang Anda terima. Tidak perlu membuat akun.
        </p>
    </div>
    <a href="<?= site_url('lacak') ?>" class="btn"><?= icon('search') ?> Lacak Lamaran</a>
</div>

<?= $this->endSection() ?>
