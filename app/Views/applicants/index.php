<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php $scorer = new \App\Libraries\PotentialScore(); ?>

<div class="page-head">
    <div>
        <h1 class="page-title">
            <span class="chip"><?= icon('users') ?></span>
            Pelamar
        </h1>
        <p class="page-sub"><?= count($rows) ?> lamaran · <?= esc($auth['unit_name']) ?></p>
    </div>
    <div class="spacer"></div>
    <a href="<?= site_url('pipeline') ?>" class="btn btn-ghost"><?= icon('pipeline') ?> Lihat Papan Pipeline</a>
</div>

<div class="card" style="margin-bottom:18px;">
    <form method="get" action="<?= site_url('pelamar') ?>" style="display:flex; gap:10px; flex-wrap:wrap; align-items:flex-end;">
        <div class="form-row" style="margin:0; flex:1 1 220px;">
            <label for="q">Cari</label>
            <input type="search" id="q" name="q" class="form-control" value="<?= esc($filters['q']) ?>"
                   placeholder="Nama, email, atau nomor HP">
        </div>
        <div class="form-row" style="margin:0; flex:0 1 190px;">
            <label for="stage">Tahap</label>
            <select id="stage" name="stage" class="form-control">
                <option value="">Semua tahap</option>
                <?php foreach ($stages as $stage): ?>
                    <option value="<?= $stage['slug'] ?>" <?= $filters['stage'] === $stage['slug'] ? 'selected' : '' ?>>
                        <?= esc($stage['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-row" style="margin:0; flex:0 1 190px;">
            <label for="job">Lowongan</label>
            <select id="job" name="job" class="form-control">
                <option value="">Semua lowongan</option>
                <?php foreach ($jobs as $job): ?>
                    <option value="<?= $job['id'] ?>" <?= (int) $filters['job'] === (int) $job['id'] ? 'selected' : '' ?>>
                        <?= esc($job['title']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-row" style="margin:0; flex:0 1 150px;">
            <label for="status">Status</label>
            <select id="status" name="status" class="form-control">
                <option value="">Semua status</option>
                <?php foreach (\App\Models\ApplicationModel::STATUSES as $key => $label): ?>
                    <option value="<?= $key ?>" <?= $filters['status'] === $key ? 'selected' : '' ?>><?= $label ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="btn btn-primary"><?= icon('search') ?> Terapkan</button>
        <?php if (array_filter($filters)): ?>
            <a href="<?= site_url('pelamar') ?>" class="btn btn-ghost">Reset</a>
        <?php endif; ?>
    </form>
</div>

<div class="table-wrap">
    <?php if ($rows === []): ?>
        <?= empty_state('users', 'Belum ada pelamar',
            array_filter($filters) ? 'Tidak ada yang cocok dengan filter. Coba reset.' : 'Pelamar muncul di sini begitu ada yang mendaftar lewat portal karir.',
            array_filter($filters) ? '' : '', '') ?>
    <?php else: ?>
        <table class="data roomy">
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Lowongan</th>
                    <th>Tahap</th>
                    <th>Status</th>
                    <th>Potensi</th>
                    <th>Masuk</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $row): ?>
                    <tr data-href="<?= site_url('pelamar/' . $row['id']) ?>" tabindex="0" role="link">
                        <td>
                            <div style="display:flex; align-items:center; gap:10px;">
                                <span class="avatar" style="width:32px;height:32px;flex:0 0 32px;font-size:11px;"><?= esc(initials($row['full_name'])) ?></span>
                                <span>
                                    <b><?= esc($row['full_name']) ?></b>
                                    <?php if ($row['is_blacklisted']): ?>
                                        <span class="badge badge-red" style="margin-left:4px;">Blacklist</span>
                                    <?php endif; ?>
                                    <br><small><?= esc($row['email']) ?></small>
                                </span>
                            </div>
                        </td>
                        <td>
                            <?= esc($row['job_title']) ?><br>
                            <small><?= esc($row['unit_name']) ?></small>
                        </td>
                        <td>
                            <span class="badge" style="background: <?= esc($row['stage_color']) ?>1F; color: <?= esc($row['stage_color']) ?>;">
                                <span class="dot"></span><?= esc($row['stage_name']) ?>
                            </span>
                        </td>
                        <td>
                            <?php
                            $badge = match ($row['status']) {
                                'hired'     => 'badge-green',
                                'rejected'  => 'badge-red',
                                'withdrawn' => 'badge-gray',
                                default     => 'badge-blue',
                            };
                            ?>
                            <span class="badge <?= $badge ?>"><?= \App\Models\ApplicationModel::STATUSES[$row['status']] ?></span>
                        </td>
                        <td>
                            <?php $ps = $scorer->forApplication($row); ?>
                            <?= potential_badge($ps['score'], $ps['band'], true) ?>
                        </td>
                        <td><small><?= sejak($row['applied_at']) ?></small></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?= $this->endSection() ?>
