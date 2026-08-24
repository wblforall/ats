<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="page-head">
    <div>
        <h1 class="page-title">
            <span class="chip"><?= icon('briefcase') ?></span>
            <?= esc($job['title']) ?>
        </h1>
        <p class="page-sub">
            <?= esc($job['code']) ?> · <?= esc($job['unit_name']) ?>
            <?= $job['department'] ? ' · ' . esc($job['department']) : '' ?>
        </p>
    </div>
    <div class="spacer"></div>
    <?php if ($job['status'] === 'open'): ?>
        <a href="<?= site_url('karir/' . $job['slug']) ?>" target="_blank" rel="noopener" class="btn btn-ghost">
            <?= icon('eye') ?> Lihat di Portal
        </a>
    <?php endif; ?>
    <a href="<?= site_url('lowongan/' . $job['id'] . '/ubah') ?>" class="btn btn-primary"><?= icon('edit') ?> Ubah</a>
</div>

<div class="grid grid-4" style="margin-bottom:22px;">
    <div class="card is-blue">
        <div class="stat-label">Total Pelamar</div>
        <div class="stat-value"><?= (int) $job['applicant_count'] ?></div>
        <div class="stat-hint">Sejak lowongan dibuka</div>
    </div>
    <div class="card">
        <div class="stat-label">Kebutuhan</div>
        <div class="stat-value"><?= (int) $job['openings'] ?></div>
        <div class="stat-hint">orang</div>
    </div>
    <div class="card">
        <div class="stat-label">Status</div>
        <div class="stat-value" style="font-size:20px; padding-top:6px;">
            <?php
            $badge = match ($job['status']) {
                'open'   => 'badge-green',
                'closed' => 'badge-gray',
                default  => 'badge-amber',
            };
            ?>
            <span class="badge <?= $badge ?>"><?= \App\Models\JobModel::STATUSES[$job['status']] ?></span>
        </div>
        <div class="stat-hint"><?= $job['published_at'] ? 'Terbit ' . tgl($job['published_at']) : 'Belum pernah terbit' ?></div>
    </div>
    <div class="card">
        <div class="stat-label">Ditutup</div>
        <div class="stat-value" style="font-size:19px; padding-top:8px;">
            <?= $job['closing_date'] ? tgl($job['closing_date']) : 'Tanpa batas' ?>
        </div>
        <div class="stat-hint"><?= \App\Models\JobModel::EMPLOYMENT_TYPES[$job['employment_type']] ?></div>
    </div>
</div>

<div class="section-title">Sebaran Pelamar per Tahap</div>
<div class="card" style="padding: 8px 4px; margin-bottom:22px;">
    <div class="stage-strip">
        <?php foreach ($stageList as $stage): ?>
            <div class="stage-chip" style="border-top-color: <?= esc($stage['color']) ?>;">
                <div class="label"><?= esc($stage['name']) ?></div>
                <div class="count"><?= (int) ($perStage[$stage['id']] ?? 0) ?></div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<div class="section-title">Daftar Pelamar</div>
<div class="table-wrap">
    <?php if ($applicants === []): ?>
        <div class="empty">
            <?= icon('users') ?>
            <div><b>Belum ada yang melamar posisi ini.</b></div>
            <?php if ($job['status'] !== 'open'): ?>
                <div style="margin-top:4px;">Lowongan belum tayang — ubah statusnya menjadi Dibuka agar muncul di portal karir.</div>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <table class="data roomy">
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Pendidikan</th>
                    <th>Tahap</th>
                    <th>Sumber</th>
                    <th>Masuk</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($applicants as $row): ?>
                    <tr data-href="<?= site_url('pelamar/' . $row['id']) ?>" tabindex="0" role="link">
                        <td>
                            <div style="display:flex; align-items:center; gap:10px;">
                                <span class="avatar" style="width:30px;height:30px;flex:0 0 30px;font-size:11px;"><?= esc(initials($row['full_name'])) ?></span>
                                <span>
                                    <b><?= esc($row['full_name']) ?></b><br>
                                    <small><?= esc($row['email']) ?></small>
                                </span>
                            </div>
                        </td>
                        <td>
                            <?= esc($row['education_level'] ?: '—') ?><br>
                            <small><?= esc($row['school'] ?: '') ?></small>
                        </td>
                        <td>
                            <span class="badge" style="background: <?= esc($row['stage_color']) ?>1F; color: <?= esc($row['stage_color']) ?>;">
                                <span class="dot"></span><?= esc($row['stage_name']) ?>
                            </span>
                        </td>
                        <td><small><?= \App\Models\ApplicationModel::SOURCES[$row['source']] ?? $row['source'] ?></small></td>
                        <td><small><?= sejak($row['applied_at']) ?></small></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?= $this->endSection() ?>
