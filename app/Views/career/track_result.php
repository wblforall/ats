<?= $this->extend('layouts/public') ?>

<?= $this->section('hero') ?>
<div class="pub-hero">
    <h1>Status Lamaran</h1>
    <p><?= esc($ref) ?> · <?= esc($application['job_title']) ?></p>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<?php
/* Sebuah tahap ditandai selesai hanya bila benar-benar tercatat di riwayat,
   bukan sekadar karena urutannya lebih awal. Kandidat yang gugur di tahap
   pertama tidak boleh terlihat seolah sudah melewati wawancara. */
$isClosed = in_array($application['status'], ['hired', 'rejected', 'withdrawn'], true);
?>

<div class="grid" style="grid-template-columns: minmax(0, 1.4fr) minmax(0, 1fr); align-items:start;">
    <div class="pub-panel">
        <div class="pub-body-title" style="margin-top:0;">Perjalanan Lamaran Anda</div>

        <div class="step-list">
            <?php foreach ($stages as $stage): ?>
                <?php
                $order   = (int) $stage['sort_order'];
                $isHere  = (int) $stage['id'] === (int) $application['stage_id'];
                $wasHere = in_array((int) $stage['id'], $visited, true);

                /* Tahap "Tidak Lolos" hanya ditampilkan bila memang dilalui,
                   supaya pelamar yang masih berjalan tidak melihatnya. */
                if ($stage['kind'] === 'rejected' && ! $wasHere) {
                    continue;
                }
                if ($stage['kind'] === 'hired' && ! $wasHere && $application['status'] === 'rejected') {
                    continue;
                }

                $class = match (true) {
                    $isHere && $stage['kind'] === 'rejected' => 'fail',
                    $isHere                                  => 'now',
                    $wasHere                                 => 'done',
                    default                                  => '',
                };
                ?>
                <div class="step <?= $class ?>">
                    <span class="bullet">
                        <?= $class === 'done' ? '✓' : ($class === 'fail' ? '×' : $order) ?>
                    </span>
                    <span class="txt">
                        <b><?= esc($stage['name']) ?></b>
                        <?php if ($isHere): ?>
                            <small>
                                <?php if ($stage['kind'] === 'rejected'): ?>
                                    Proses lamaran ini telah berakhir.
                                <?php elseif ($stage['kind'] === 'hired'): ?>
                                    Selamat! Anda diterima pada posisi ini.
                                <?php else: ?>
                                    Tahap yang sedang berjalan saat ini.
                                <?php endif; ?>
                            </small>
                        <?php elseif ($class === 'done'): ?>
                            <small>Selesai</small>
                        <?php endif; ?>
                    </span>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if ($interview): ?>
            <div class="alert alert-info" style="margin-top:22px;">
                <?= icon('calendar') ?>
                <span>
                    <b>Anda punya jadwal wawancara.</b><br>
                    <?= hari($interview['scheduled_at']) ?>, <?= tgl($interview['scheduled_at']) ?>
                    pukul <?= date('H:i', strtotime($interview['scheduled_at'])) ?> WITA
                    (<?= \App\Models\InterviewModel::MODES[$interview['mode']] ?>).
                    <?php if ($interview['location']): ?><br>Lokasi: <?= esc($interview['location']) ?><?php endif; ?>
                    <?php if ($interview['meeting_link']): ?><br>Tautan: <?= esc($interview['meeting_link']) ?><?php endif; ?>
                </span>
            </div>
        <?php endif; ?>

        <?php if (! $isClosed): ?>
            <p style="font-size:12.5px; color:var(--ink-faint); margin:18px 0 0; line-height:1.6;">
                Kami menghubungi pelamar melalui email dan telepon. Mohon pastikan kontak Anda aktif.
                Proses seleksi biasanya memerlukan beberapa hari kerja di tiap tahap.
            </p>
        <?php endif; ?>
    </div>

    <div class="pub-panel">
        <div class="stat-label">Ringkasan</div>

        <table style="width:100%; font-size:13.5px; margin-top:12px; border-collapse:collapse;">
            <tr>
                <td style="color:var(--ink-soft); padding:6px 0;">Nomor lamaran</td>
                <td style="text-align:right; font-weight:600;"><?= esc($ref) ?></td>
            </tr>
            <tr>
                <td style="color:var(--ink-soft); padding:6px 0;">Nama</td>
                <td style="text-align:right; font-weight:600;"><?= esc($application['full_name']) ?></td>
            </tr>
            <tr>
                <td style="color:var(--ink-soft); padding:6px 0;">Posisi</td>
                <td style="text-align:right; font-weight:600;"><?= esc($application['job_title']) ?></td>
            </tr>
            <tr>
                <td style="color:var(--ink-soft); padding:6px 0;">Unit</td>
                <td style="text-align:right; font-weight:600;"><?= esc($application['unit_name']) ?></td>
            </tr>
            <tr>
                <td style="color:var(--ink-soft); padding:6px 0;">Dikirim</td>
                <td style="text-align:right; font-weight:600;"><?= tgl($application['applied_at']) ?></td>
            </tr>
            <tr>
                <td style="color:var(--ink-soft); padding:6px 0;">Status</td>
                <td style="text-align:right;">
                    <?php
                    $badge = match ($application['status']) {
                        'hired'     => 'badge-green',
                        'rejected'  => 'badge-red',
                        'withdrawn' => 'badge-gray',
                        default     => 'badge-blue',
                    };
                    ?>
                    <span class="badge <?= $badge ?>"><?= \App\Models\ApplicationModel::STATUSES[$application['status']] ?></span>
                </td>
            </tr>
        </table>

        <a href="<?= site_url('lacak') ?>" class="btn btn-ghost" style="width:100%; justify-content:center; margin-top:16px;">
            Lacak lamaran lain
        </a>
    </div>
</div>

<?= $this->endSection() ?>
