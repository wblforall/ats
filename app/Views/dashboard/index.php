<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="page-head">
    <div>
        <h1 class="page-title">
            <span class="chip"><?= icon('dashboard') ?></span>
            Dashboard
        </h1>
        <p class="page-sub">Ringkasan rekrutmen <?= esc($auth['unit_name']) ?> · <?= tgl(date('Y-m-d')) ?></p>
    </div>
    <div class="spacer"></div>
    <?php if ($auth['role'] !== 'interviewer'): ?>
        <a href="<?= site_url('pelamar/baru') ?>" class="btn btn-ghost"><?= icon('plus') ?> Input Pelamar</a>
        <a href="<?= site_url('lowongan/baru') ?>" class="btn btn-primary"><?= icon('briefcase') ?> Lowongan Baru</a>
    <?php endif; ?>
</div>

<div class="section-title">Ringkasan</div>

<div class="grid grid-4">
    <div class="card is-blue">
        <div class="stat-label">Pelamar Aktif</div>
        <div class="stat-value"><?= number_format($activeTotal, 0, ',', '.') ?></div>
        <div class="stat-hint">Sedang berjalan di pipeline</div>
    </div>

    <div class="card">
        <div class="stat-label">Lamaran Bulan Ini</div>
        <div class="stat-value"><?= number_format($thisMonth, 0, ',', '.') ?></div>
        <div class="stat-hint">
            <?php $delta = $thisMonth - $lastMonth; ?>
            <?php if ($lastMonth === 0 && $thisMonth === 0): ?>
                Belum ada lamaran masuk
            <?php else: ?>
                <?= $delta >= 0 ? '+' : '' ?><?= $delta ?> dibanding bulan lalu (<?= $lastMonth ?>)
            <?php endif; ?>
        </div>
    </div>

    <div class="card">
        <div class="stat-label">Lowongan Dibuka</div>
        <div class="stat-value"><?= number_format($openJobCount, 0, ',', '.') ?></div>
        <div class="stat-hint">Tayang di portal karir</div>
    </div>

    <div class="card">
        <div class="stat-label">Diterima Tahun Ini</div>
        <div class="stat-value" style="color: var(--green);"><?= number_format($hiredCount, 0, ',', '.') ?></div>
        <div class="stat-hint">Kandidat resmi bergabung</div>
    </div>
</div>

<div class="section-title">Pipeline</div>

<div class="card" style="padding: 8px 4px;">
    <div class="stage-strip">
        <?php foreach ($stageList as $stage): ?>
            <a href="<?= site_url('pipeline?stage=' . $stage['slug']) ?>" class="stage-chip"
               style="border-top-color: <?= esc($stage['color']) ?>;" title="<?= esc($stage['name']) ?>">
                <div class="label"><?= esc($stage['name']) ?></div>
                <div class="count"><?= (int) ($perStage[$stage['id']] ?? 0) ?></div>
            </a>
        <?php endforeach; ?>
    </div>
</div>

<div class="grid grid-wide" style="margin-top: 22px; align-items: start;">
    <div>
        <div class="section-title" style="margin-top:0;">Pelamar Terbaru</div>
        <div class="table-wrap">
            <?php if ($recent === []): ?>
                <div class="empty">
                    <?= icon('users') ?>
                    <div>Belum ada lamaran masuk.</div>
                </div>
            <?php else: ?>
                <table class="data">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Posisi</th>
                            <th>Tahap</th>
                            <th>Masuk</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent as $row): ?>
                            <tr onclick="location.href='<?= site_url('pelamar/' . $row['id']) ?>'" style="cursor:pointer;">
                                <td>
                                    <div style="display:flex; align-items:center; gap:10px;">
                                        <span class="avatar" style="width:30px;height:30px;flex:0 0 30px;font-size:11px;"><?= esc(initials($row['full_name'])) ?></span>
                                        <span>
                                            <b><?= esc($row['full_name']) ?></b><br>
                                            <small style="color:var(--ink-faint);"><?= esc($row['email']) ?></small>
                                        </span>
                                    </div>
                                </td>
                                <td>
                                    <?= esc($row['job_title']) ?><br>
                                    <small style="color:var(--ink-faint);"><?= esc($row['unit_name']) ?></small>
                                </td>
                                <td>
                                    <span class="badge" style="background: <?= esc($row['stage_color']) ?>1F; color: <?= esc($row['stage_color']) ?>;">
                                        <span class="dot"></span><?= esc($row['stage_name']) ?>
                                    </span>
                                </td>
                                <td><small><?= sejak($row['applied_at']) ?></small></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

    <div>
        <div class="section-title" style="margin-top:0;">Agenda Interview 7 Hari</div>
        <div class="card" style="padding: 6px;">
            <?php if ($agenda === []): ?>
                <div class="empty">
                    <?= icon('calendar') ?>
                    <div>Tidak ada jadwal interview minggu ini.</div>
                </div>
            <?php else: ?>
                <?php foreach ($agenda as $iv): ?>
                    <a href="<?= site_url('pelamar/' . $iv['application_id']) ?>" class="list-row">
                        <span class="day">
                            <b><?= date('d', strtotime($iv['scheduled_at'])) ?></b>
                            <small><?= strtoupper(substr(hari($iv['scheduled_at']), 0, 3)) ?></small>
                        </span>
                        <span class="body">
                            <b><?= esc($iv['full_name']) ?></b>
                            <small><?= esc($iv['job_title']) ?> · <?= date('H:i', strtotime($iv['scheduled_at'])) ?> WITA</small>
                        </span>
                        <span class="badge badge-blue"><?= esc(\App\Models\InterviewModel::MODES[$iv['mode']] ?? $iv['mode']) ?></span>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div class="section-title">Lowongan Paling Ramai</div>
        <div class="card" style="padding: 6px;">
            <?php if ($openJobs === []): ?>
                <div class="empty"><?= icon('briefcase') ?><div>Belum ada lowongan dibuka.</div></div>
            <?php else: ?>
                <?php foreach ($openJobs as $job): ?>
                    <a href="<?= site_url('lowongan/' . $job['id']) ?>" class="list-row">
                        <span class="body">
                            <b><?= esc($job['title']) ?></b>
                            <small><?= esc($job['unit_name']) ?> · butuh <?= (int) $job['openings'] ?> orang</small>
                        </span>
                        <span class="tail">
                            <b><?= (int) $job['applicant_count'] ?></b>
                            <small>pelamar</small>
                        </span>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
