<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="page-head">
    <div>
        <h1 class="page-title">
            <span class="chip"><?= icon('calendar') ?></span>
            Interview
        </h1>
        <p class="page-sub"><?= count($rows) ?> jadwal · <?= tgl($filters['from']) ?> – <?= tgl($filters['to']) ?></p>
    </div>
    <div class="spacer"></div>
    <form method="get" action="<?= site_url('interview') ?>" style="display:flex; gap:8px; align-items:flex-end;">
        <div class="form-row" style="margin:0;">
            <label for="from" style="font-size:11px;">Dari</label>
            <input type="date" id="from" name="from" class="form-control" value="<?= esc($filters['from']) ?>">
        </div>
        <div class="form-row" style="margin:0;">
            <label for="to" style="font-size:11px;">Sampai</label>
            <input type="date" id="to" name="to" class="form-control" value="<?= esc($filters['to']) ?>">
        </div>
        <button type="submit" class="btn btn-ghost btn-sm">Tampilkan</button>
    </form>
</div>

<div class="table-wrap">
    <?php if ($rows === []): ?>
        <?= empty_state('calendar', 'Tidak ada jadwal interview', 'Jadwalkan interview dari halaman detail pelamar.') ?>
    <?php else: ?>
        <table class="data roomy">
            <thead>
                <tr>
                    <th>Waktu</th>
                    <th>Pelamar</th>
                    <th>Lowongan</th>
                    <th>Mode</th>
                    <th>Status</th>
                    <th>Jawaban Pelamar</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $iv): ?>
                    <tr data-href="<?= site_url('pelamar/' . $iv['application_id']) ?>#interview" tabindex="0" role="link">
                        <td>
                            <b><?= hari($iv['scheduled_at']) ?>, <?= tgl($iv['scheduled_at']) ?></b><br>
                            <small><?= date('H:i', strtotime($iv['scheduled_at'])) ?> WITA · putaran <?= (int) $iv['round'] ?></small>
                        </td>
                        <td><?= esc($iv['full_name']) ?><br><small><?= esc($iv['phone']) ?></small></td>
                        <td><?= esc($iv['job_title']) ?><br><small><?= esc($iv['unit_name']) ?></small></td>
                        <td><span class="badge badge-gray"><?= \App\Models\InterviewModel::MODES[$iv['mode']] ?></span></td>
                        <td>
                            <?php
                            $b = match ($iv['status']) {
                                'completed' => 'badge-green', 'canceled' => 'badge-gray',
                                'no_show' => 'badge-red', default => 'badge-blue',
                            };
                            ?>
                            <span class="badge <?= $b ?>"><?= \App\Models\InterviewModel::STATUSES[$iv['status']] ?></span>
                        </td>
                        <td>
                            <?php if (\App\Models\InterviewModel::showsResponse($iv)): ?>
                                <span class="badge <?= \App\Models\InterviewModel::RESPONSE_BADGES[$iv['response_status']] ?>"><?= \App\Models\InterviewModel::RESPONSES[$iv['response_status']] ?></span>
                            <?php else: ?>
                                <small style="color:var(--ink-faint);">—</small>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?= $this->endSection() ?>
