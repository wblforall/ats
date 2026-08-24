<?= $this->extend('layouts/public') ?>

<?= $this->section('hero') ?>
<div class="pub-hero" style="text-align:left; padding-bottom: 56px;">
    <div style="max-width:1100px; margin:0 auto; padding: 0 22px;">
        <a href="<?= site_url('karir') ?>" style="color:#fff; opacity:.85; font-size:13px;">← Semua lowongan</a>
        <h1 style="margin-top:10px;"><?= esc($job['title']) ?></h1>
        <p style="margin:0;"><?= esc($job['unit_name']) ?><?= $job['department'] ? ' · ' . esc($job['department']) : '' ?></p>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="grid" style="grid-template-columns: minmax(0, 1.7fr) minmax(0, 1fr); align-items:start;">
    <div class="pub-panel">
        <div class="meta" style="display:flex; flex-wrap:wrap; gap:7px;">
            <span class="badge badge-blue"><?= \App\Models\JobModel::EMPLOYMENT_TYPES[$job['employment_type']] ?></span>
            <?php if ($job['level']): ?><span class="badge badge-gray"><?= esc($job['level']) ?></span><?php endif; ?>
            <?php if ($job['work_location']): ?><span class="badge badge-gray"><?= esc($job['work_location']) ?></span><?php endif; ?>
            <span class="badge badge-gray">Kode <?= esc($job['code']) ?></span>
        </div>

        <?php if ($job['description']): ?>
            <div class="pub-body-title">Deskripsi Pekerjaan</div>
            <div class="pub-body"><?= esc($job['description']) ?></div>
        <?php endif; ?>

        <?php if ($job['requirements']): ?>
            <div class="pub-body-title">Kualifikasi</div>
            <div class="pub-body"><?= esc($job['requirements']) ?></div>
        <?php endif; ?>

        <?php if ($job['benefits']): ?>
            <div class="pub-body-title">Yang Kami Tawarkan</div>
            <div class="pub-body"><?= esc($job['benefits']) ?></div>
        <?php endif; ?>
    </div>

    <div class="pub-panel" style="position: sticky; top: 18px;">
        <div class="stat-label">Ringkasan</div>

        <table style="width:100%; font-size:13.5px; margin: 12px 0 18px; border-collapse:collapse;">
            <tr>
                <td style="color:var(--ink-soft); padding:5px 0;">Unit</td>
                <td style="text-align:right; font-weight:600;"><?= esc($job['unit_name']) ?></td>
            </tr>
            <tr>
                <td style="color:var(--ink-soft); padding:5px 0;">Kebutuhan</td>
                <td style="text-align:right; font-weight:600;"><?= (int) $job['openings'] ?> orang</td>
            </tr>
            <?php if ($job['salary_visible'] && $job['salary_min']): ?>
                <tr>
                    <td style="color:var(--ink-soft); padding:5px 0;">Kisaran gaji</td>
                    <td style="text-align:right; font-weight:600;">
                        <?= rupiah((float) $job['salary_min']) ?><?= $job['salary_max'] ? ' – ' . rupiah((float) $job['salary_max'], false) : '' ?>
                    </td>
                </tr>
            <?php endif; ?>
            <?php if ($job['closing_date']): ?>
                <tr>
                    <td style="color:var(--ink-soft); padding:5px 0;">Lamaran ditutup</td>
                    <td style="text-align:right; font-weight:600;"><?= tgl($job['closing_date']) ?></td>
                </tr>
            <?php endif; ?>
        </table>

        <a href="<?= site_url('karir/' . $job['slug'] . '/lamar') ?>" class="btn btn-primary" style="width:100%; justify-content:center;">
            <?= icon('arrow-right') ?> Lamar Posisi Ini
        </a>

        <p style="font-size:12px; color:var(--ink-faint); margin: 12px 0 0; line-height:1.6;">
            Anda tidak perlu membuat akun. Setelah mengirim lamaran, nomor lamaran akan dikirim ke email Anda
            dan bisa dipakai untuk <a href="<?= site_url('lacak') ?>">melacak status</a> kapan saja.
        </p>
    </div>
</div>

<?= $this->endSection() ?>
