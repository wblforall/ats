<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="page-head">
    <div>
        <h1 class="page-title">
            <span class="chip"><?= icon('briefcase') ?></span>
            Lowongan
        </h1>
        <p class="page-sub"><?= count($jobs) ?> lowongan · <?= esc($auth['unit_name']) ?></p>
    </div>
    <div class="spacer"></div>
    <a href="<?= site_url('lowongan/baru') ?>" class="btn btn-primary"><?= icon('plus') ?> Lowongan Baru</a>
</div>

<div class="card" style="margin-bottom:18px;">
    <form method="get" action="<?= site_url('lowongan') ?>" style="display:flex; gap:10px; flex-wrap:wrap; align-items:flex-end;">
        <div class="form-row" style="margin:0; flex:1 1 240px;">
            <label for="q">Cari</label>
            <input type="search" id="q" name="q" class="form-control" value="<?= esc($filters['q']) ?>"
                   placeholder="Nama posisi, kode, atau departemen">
        </div>
        <div class="form-row" style="margin:0; flex:0 1 180px;">
            <label for="status">Status</label>
            <select id="status" name="status" class="form-control">
                <option value="">Semua status</option>
                <?php foreach (\App\Models\JobModel::STATUSES as $key => $label): ?>
                    <option value="<?= $key ?>" <?= $filters['status'] === $key ? 'selected' : '' ?>><?= $label ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="btn btn-primary"><?= icon('search') ?> Terapkan</button>
        <?php if ($filters['q'] !== '' || $filters['status'] !== ''): ?>
            <a href="<?= site_url('lowongan') ?>" class="btn btn-ghost">Reset</a>
        <?php endif; ?>
    </form>
</div>

<div class="table-wrap">
    <?php if ($jobs === []): ?>
        <?= empty_state('briefcase', 'Belum ada lowongan',
            $filters['q'] !== '' || $filters['status'] !== '' ? 'Tidak ada yang cocok dengan filter. Coba reset.' : 'Buat lowongan pertama Anda — nanti tayang di portal karir.',
            $filters['q'] === '' && $filters['status'] === '' ? 'Lowongan Baru' : '',
            site_url('lowongan/baru')) ?>
    <?php else: ?>
        <table class="data roomy">
            <thead>
                <tr>
                    <th>Posisi</th>
                    <th>Unit</th>
                    <th>Jenis</th>
                    <th class="num">Butuh</th>
                    <th class="num">Pelamar</th>
                    <th>Status</th>
                    <th>Ditutup</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($jobs as $job): ?>
                    <tr>
                        <td>
                            <a href="<?= site_url('lowongan/' . $job['id']) ?>"><b><?= esc($job['title']) ?></b></a><br>
                            <small><?= esc($job['code']) ?><?= $job['department'] ? ' · ' . esc($job['department']) : '' ?></small>
                        </td>
                        <td><small><?= esc($job['unit_name']) ?></small></td>
                        <td><small><?= \App\Models\JobModel::EMPLOYMENT_TYPES[$job['employment_type']] ?></small></td>
                        <td class="num"><?= (int) $job['openings'] ?></td>
                        <td class="num"><b><?= (int) $job['applicant_count'] ?></b></td>
                        <td>
                            <?php
                            $badge = match ($job['status']) {
                                'open'   => 'badge-green',
                                'closed' => 'badge-gray',
                                default  => 'badge-amber',
                            };
                            ?>
                            <span class="badge <?= $badge ?>"><?= \App\Models\JobModel::STATUSES[$job['status']] ?></span>
                        </td>
                        <td><small><?= $job['closing_date'] ? tgl($job['closing_date']) : '—' ?></small></td>
                        <td style="text-align:right; white-space:nowrap;">
                            <a href="<?= site_url('lowongan/' . $job['id'] . '/ubah') ?>" class="btn btn-ghost btn-sm"><?= icon('edit') ?> Ubah</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?= $this->endSection() ?>
