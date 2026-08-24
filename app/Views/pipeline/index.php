<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php $scorer = new \App\Libraries\PotentialScore(); ?>

<div class="page-head">
    <div>
        <h1 class="page-title">
            <span class="chip"><?= icon('pipeline') ?></span>
            Pipeline
        </h1>
        <p class="page-sub"><?= $total ?> pelamar aktif · <?= esc($auth['unit_name']) ?></p>
    </div>
    <div class="spacer"></div>
    <?php if ($jobs !== []): ?>
        <form method="get" action="<?= site_url('pipeline') ?>">
            <select name="job" class="form-control" onchange="this.form.submit()" style="min-width:220px;">
                <option value="">Semua lowongan</option>
                <?php foreach ($jobs as $job): ?>
                    <option value="<?= $job['id'] ?>" <?= $jobId === (int) $job['id'] ? 'selected' : '' ?>>
                        <?= esc($job['title']) ?> (<?= (int) $job['applicant_count'] ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
    <?php endif; ?>
</div>

<?php if ($total === 0): ?>
    <!-- Papan kosong sepenuhnya: satu ajakan yang jelas, bukan 8 kolom mati. -->
    <div class="table-wrap">
        <?php if ($jobs === []): ?>
            <?= empty_state('pipeline', 'Pipeline masih kosong',
                'Buat lowongan dulu — begitu ada yang melamar, kartu pelamar muncul di papan ini dan bisa digeser antar tahap.',
                'Buat Lowongan', site_url('lowongan/baru')) ?>
        <?php else: ?>
            <?= empty_state('pipeline', 'Belum ada pelamar aktif',
                'Kartu pelamar akan muncul di sini begitu ada lamaran yang berjalan. Anda bisa menggesernya antar tahap.') ?>
        <?php endif; ?>
    </div>
<?php else: ?>
    <div class="board-scroll">
        <div class="board-hint" aria-hidden="true"><?= icon('arrow-right') ?> Geser untuk lihat tahap lain</div>
        <button type="button" class="board-nav prev" aria-label="Geser kiri" style="transform:translateY(-50%) scaleX(-1);"><?= icon('arrow-right') ?></button>
        <button type="button" class="board-nav next" aria-label="Geser kanan"><?= icon('arrow-right') ?></button>
        <div class="board">
        <?php foreach ($stages as $stage): ?>
            <?php $kartu = $byStage[$stage['id']] ?? []; ?>
            <div class="board-col">
                <div class="board-head" style="border-top-color: <?= esc($stage['color']) ?>;">
                    <span><?= esc($stage['name']) ?></span>
                    <span class="board-count"><?= count($kartu) ?></span>
                </div>
                <div class="board-body">
                    <?php if ($kartu === []): ?>
                        <div class="board-empty">Belum ada</div>
                    <?php else: ?>
                        <?php foreach ($kartu as $row): ?>
                            <a href="<?= site_url('pelamar/' . $row['id']) ?>" class="board-card">
                                <div style="display:flex; align-items:center; gap:8px;">
                                    <span class="avatar" style="width:26px;height:26px;flex:0 0 26px;font-size:10px;"><?= esc(initials($row['full_name'])) ?></span>
                                    <b style="font-size:13px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;"><?= esc($row['full_name']) ?></b>
                                </div>
                                <div style="font-size:11.5px; color:var(--ink-soft); margin-top:6px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                                    <?= esc($row['job_title']) ?>
                                </div>
                                <div style="display:flex; align-items:center; justify-content:space-between; margin-top:8px; gap:6px;">
                                    <small style="color:var(--ink-faint);"><?= sejak($row['applied_at']) ?></small>
                                    <?php $ps = $scorer->forApplication($row); ?>
                                    <?php if ($ps['score'] !== null): ?>
                                        <?= potential_badge($ps['score'], $ps['band'], true) ?>
                                    <?php endif; ?>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>

<?= $this->endSection() ?>
