<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<?php
$bolehGeser = in_array($auth['role'], ['superadmin', 'hr_admin', 'hr_staff'], true);
$umur = $app['birth_date'] ? (int) ((time() - strtotime($app['birth_date'])) / 31557600) : null;
?>

<div class="page-head">
    <div>
        <a href="<?= site_url('pelamar') ?>" style="font-size:13px; color:var(--ink-soft);">← Semua pelamar</a>
        <h1 class="page-title" style="margin-top:8px;">
            <span class="avatar" style="width:38px;height:38px;flex:0 0 38px;font-size:14px;"><?= esc(initials($app['full_name'])) ?></span>
            <?= esc($app['full_name']) ?>
        </h1>
        <p class="page-sub">
            <?= esc($app['reference']) ?> · melamar <?= esc($app['job_title']) ?> (<?= esc($app['unit_name']) ?>) · <?= tgl($app['applied_at']) ?>
        </p>
    </div>
    <div class="spacer"></div>
    <?php if ($app['cv_path']): ?>
        <a href="<?= site_url('pelamar/' . $app['id'] . '/cv') ?>" class="btn btn-ghost"><?= icon('download') ?> Unduh CV</a>
    <?php endif; ?>
</div>

<?php if ($app['is_blacklisted']): ?>
    <div class="alert alert-error">
        <?= icon('alert') ?>
        <span><b>Kandidat masuk daftar hitam.</b> <?= esc($app['blacklist_reason'] ?: 'Tanpa keterangan.') ?></span>
    </div>
<?php endif; ?>

<div class="grid" style="grid-template-columns: minmax(0, 1.55fr) minmax(0, 1fr); align-items:start;">
    <!-- ============ KOLOM KIRI ============ -->
    <div>
        <!-- Geser tahap -->
        <div class="card">
            <div class="section-title" style="margin-top:0;">Tahap Saat Ini</div>

            <div style="display:flex; align-items:center; gap:12px; flex-wrap:wrap; margin-bottom:14px;">
                <span class="badge" style="font-size:13px; padding:6px 14px; background: <?= esc($app['stage_color']) ?>1F; color: <?= esc($app['stage_color']) ?>;">
                    <span class="dot"></span><?= esc($app['stage_name']) ?>
                </span>
                <?php
                $badge = match ($app['status']) {
                    'hired' => 'badge-green', 'rejected' => 'badge-red',
                    'withdrawn' => 'badge-gray', default => 'badge-blue',
                };
                ?>
                <span class="badge <?= $badge ?>"><?= \App\Models\ApplicationModel::STATUSES[$app['status']] ?></span>
                <?php if ($app['reject_reason']): ?>
                    <small style="color:var(--ink-soft);">Alasan: <?= esc($app['reject_reason']) ?></small>
                <?php endif; ?>
            </div>

            <?php if ($bolehGeser): ?>
                <form method="post" action="<?= site_url('pelamar/' . $app['id'] . '/geser') ?>" id="formGeser">
                    <?= csrf_field() ?>
                    <div class="grid grid-2">
                        <div class="form-row" style="margin-bottom:10px;">
                            <label for="stage_id">Pindahkan ke tahap</label>
                            <select id="stage_id" name="stage_id" class="form-control" required>
                                <?php foreach ($stages as $stage): ?>
                                    <option value="<?= $stage['id'] ?>" data-kind="<?= $stage['kind'] ?>"
                                            <?= (int) $app['stage_id'] === (int) $stage['id'] ? 'selected' : '' ?>>
                                        <?= esc($stage['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-row" style="margin-bottom:10px;">
                            <label for="note">Catatan perpindahan (opsional)</label>
                            <input type="text" id="note" name="note" class="form-control" placeholder="mis. lolos tes tertulis">
                        </div>
                    </div>
                    <div class="form-row" id="rowAlasan" style="margin-bottom:10px; display:none;">
                        <label for="reject_reason">Alasan tidak lolos</label>
                        <input type="text" id="reject_reason" name="reject_reason" class="form-control"
                               placeholder="Ditampilkan di catatan internal, bukan ke pelamar">
                    </div>
                    <button type="submit" class="btn btn-primary"><?= icon('arrow-right') ?> Simpan Perpindahan</button>
                </form>
            <?php endif; ?>
        </div>

        <!-- Interview -->
        <div class="card" style="margin-top:18px;" id="interview">
            <div style="display:flex; align-items:center; gap:10px; margin-bottom:4px;">
                <div class="section-title" style="margin:0;">Jadwal Interview</div>
                <div class="spacer" style="flex:1;"></div>
                <?php if ($bolehGeser): ?>
                    <a href="<?= site_url('interview/baru/' . $app['id']) ?>" class="btn btn-ghost btn-sm"><?= icon('plus') ?> Jadwalkan</a>
                <?php endif; ?>
            </div>

            <?php if ($interviews === []): ?>
                <p style="color:var(--ink-soft); font-size:13px; margin:10px 0 0;">Belum ada jadwal interview.</p>
            <?php else: ?>
                <?php foreach ($interviews as $iv): ?>
                    <?php $peserta = model(\App\Models\InterviewParticipantModel::class)->forInterview((int) $iv['id']); ?>
                    <div style="border:1px solid var(--line); border-radius:12px; padding:14px; margin-top:12px;">
                        <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
                            <b>Putaran <?= (int) $iv['round'] ?><?= $iv['title'] ? ' · ' . esc($iv['title']) : '' ?></b>
                            <?php
                            $ivBadge = match ($iv['status']) {
                                'completed' => 'badge-green', 'canceled' => 'badge-gray',
                                'no_show' => 'badge-red', default => 'badge-blue',
                            };
                            ?>
                            <span class="badge <?= $ivBadge ?>"><?= \App\Models\InterviewModel::STATUSES[$iv['status']] ?></span>
                            <span class="badge badge-gray"><?= \App\Models\InterviewModel::MODES[$iv['mode']] ?></span>
                        </div>
                        <div style="font-size:13px; color:var(--ink); margin-top:8px;">
                            <?= icon('calendar') ?> <?= hari($iv['scheduled_at']) ?>, <?= tgl($iv['scheduled_at'], true) ?> WITA
                            (<?= (int) $iv['duration_min'] ?> menit)
                        </div>
                        <?php if ($iv['location']): ?>
                            <div style="font-size:13px; color:var(--ink-soft); margin-top:4px;">Lokasi: <?= esc($iv['location']) ?></div>
                        <?php endif; ?>
                        <?php if ($iv['meeting_link']): ?>
                            <div style="font-size:13px; color:var(--ink-soft); margin-top:4px;">Tautan: <?= esc($iv['meeting_link']) ?></div>
                        <?php endif; ?>
                        <?php if ($peserta !== []): ?>
                            <div style="font-size:12.5px; color:var(--ink-soft); margin-top:8px;">
                                Pewawancara: <?= esc(implode(', ', array_column($peserta, 'user_name'))) ?>
                            </div>
                        <?php endif; ?>
                        <div style="font-size:11.5px; color:var(--ink-faint); margin-top:8px; border-top:1px solid var(--line); padding-top:8px;">
                            Dijadwalkan oleh <b><?= esc($iv['created_by'] ? (model(\App\Models\UserModel::class)->find($iv['created_by'])['name'] ?? '—') : 'sistem') ?></b>
                            pada <?= tgl($iv['created_at'], true) ?>
                        </div>
                        <div style="display:flex; gap:8px; margin-top:10px; flex-wrap:wrap;">
                            <a href="<?= site_url('interview/' . $iv['id'] . '/nilai') ?>" class="btn btn-ghost btn-sm"><?= icon('star') ?> Isi Penilaian</a>
                            <?php if ($bolehGeser && $iv['status'] === 'scheduled'): ?>
                                <form method="post" action="<?= site_url('interview/' . $iv['id'] . '/status') ?>" style="display:inline;">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="status" value="completed">
                                    <button class="btn btn-ghost btn-sm"><?= icon('check') ?> Tandai Selesai</button>
                                </form>
                                <form method="post" action="<?= site_url('interview/' . $iv['id'] . '/status') ?>" style="display:inline;">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="status" value="canceled">
                                    <button class="btn btn-ghost btn-sm" data-confirm="Batalkan jadwal ini?"><?= icon('x') ?> Batalkan</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Catatan -->
        <div class="card" style="margin-top:18px;" id="catatan">
            <div class="section-title" style="margin-top:0;">Catatan Internal</div>

            <form method="post" action="<?= site_url('pelamar/' . $app['id'] . '/catatan') ?>" style="margin-bottom:14px;">
                <?= csrf_field() ?>
                <div class="form-row" style="margin-bottom:8px;">
                    <textarea name="note" class="form-control" required placeholder="Tulis catatan hasil screening, telepon, dll." style="min-height:70px;"></textarea>
                </div>
                <button type="submit" class="btn btn-ghost btn-sm"><?= icon('plus') ?> Tambah Catatan</button>
            </form>

            <?php if ($notes === []): ?>
                <p style="color:var(--ink-soft); font-size:13px;">Belum ada catatan.</p>
            <?php else: ?>
                <?php foreach ($notes as $n): ?>
                    <div style="border-top:1px solid var(--line); padding:11px 0;">
                        <div style="font-size:13.5px; white-space:pre-line;"><?= esc($n['note']) ?></div>
                        <div style="font-size:11.5px; color:var(--ink-faint); margin-top:4px;">
                            <?= esc($n['user_name'] ?: 'HR') ?> · <?= tgl($n['created_at'], true) ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Linimasa -->
        <div class="card" style="margin-top:18px;">
            <div class="section-title" style="margin-top:0;">Riwayat Tahap</div>
            <?php if ($timeline === []): ?>
                <p style="color:var(--ink-soft); font-size:13px;">Belum ada riwayat.</p>
            <?php else: ?>
                <?php foreach ($timeline as $h): ?>
                    <div style="display:flex; gap:12px; padding:9px 0; border-top:1px solid var(--line);">
                        <span style="flex:0 0 8px; width:8px; height:8px; border-radius:50%; margin-top:6px; background: <?= esc($h['to_color'] ?? '#64748b') ?>;"></span>
                        <div>
                            <div style="font-size:13.5px;">
                                <?= $h['from_stage'] ? esc($h['from_stage']) . ' → ' : '' ?><b><?= esc($h['to_stage']) ?></b>
                            </div>
                            <?php if ($h['note']): ?>
                                <div style="font-size:12.5px; color:var(--ink-soft);"><?= esc($h['note']) ?></div>
                            <?php endif; ?>
                            <div style="font-size:11.5px; color:var(--ink-faint); margin-top:2px;">
                                <?= esc($h['user_name'] ?: 'sistem') ?> · <?= tgl($h['created_at'], true) ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- ============ KOLOM KANAN: profil ============ -->
    <div>
        <div class="card">
            <div class="section-title" style="margin-top:0;">Data Diri</div>
            <table style="width:100%; font-size:13px; border-collapse:collapse;">
                <?php
                $baris = [
                    ['Email', $app['email']],
                    ['No HP', $app['phone']],
                    ['Instagram', $app['instagram'] ? '@' . $app['instagram'] : null, $app['instagram'] ? 'https://instagram.com/' . $app['instagram'] : null],
                    ['LinkedIn', $app['linkedin_url'] ?: null, $app['linkedin_url'] ?: null],
                    ['Jenis Kelamin', $app['gender'] === 'L' ? 'Laki-laki' : ($app['gender'] === 'P' ? 'Perempuan' : null)],
                    ['Umur', $umur ? $umur . ' tahun' : null],
                    ['Kota', $app['city']],
                    ['Pendidikan', trim(($app['education_level'] ?? '') . ' ' . ($app['major'] ? '· ' . $app['major'] : ''))],
                    ['Sekolah', $app['school']],
                    ['Pengalaman', $app['years_experience'] !== null ? rtrim(rtrim(number_format((float) $app['years_experience'], 1), '0'), ',.') . ' tahun' : null],
                    ['Posisi Kini', trim(($app['current_position'] ?? '') . ' ' . ($app['current_company'] ? '· ' . $app['current_company'] : ''))],
                    ['Gaji Diharapkan', $app['expected_salary'] !== null ? rupiah((float) $app['expected_salary']) : null],
                    ['Siap Mulai', $app['available_from'] ? tgl($app['available_from']) : null],
                    ['Sumber', \App\Models\ApplicationModel::SOURCES[$app['source']] ?? $app['source']],
                ];
                foreach ($baris as $b):
                    $val = $b[1];
                    if ($val === null || $val === '') continue;
                    $link = $b[2] ?? null;
                ?>
                    <tr>
                        <td style="color:var(--ink-soft); padding:6px 0; vertical-align:top; white-space:nowrap;"><?= esc($b[0]) ?></td>
                        <td style="padding:6px 0 6px 14px; text-align:right; font-weight:500;">
                            <?php if ($link): ?>
                                <a href="<?= esc($link) ?>" target="_blank" rel="noopener"><?= esc($val) ?></a>
                            <?php else: ?>
                                <?= esc($val) ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>

            <?php if ($app['cover_letter']): ?>
                <div class="section-title">Surat Lamaran</div>
                <div style="font-size:13px; color:var(--ink-soft); white-space:pre-line; line-height:1.6;"><?= esc($app['cover_letter']) ?></div>
            <?php endif; ?>
        </div>

        <!-- Penilaian interview -->
        <?php if ($evaluations !== []): ?>
            <div class="card" style="margin-top:18px;">
                <div class="section-title" style="margin-top:0;">Penilaian Pewawancara</div>
                <?php foreach ($evaluations as $ev): ?>
                    <div style="border-top:1px solid var(--line); padding:11px 0;">
                        <div style="display:flex; justify-content:space-between; align-items:center;">
                            <b style="font-size:13px;"><?= esc($ev['user_name']) ?></b>
                            <span style="font-size:16px; font-weight:700; color:var(--blue);"><?= number_format((float) $ev['overall'], 1) ?></span>
                        </div>
                        <div style="font-size:12px; color:var(--ink-soft);">
                            Putaran <?= (int) $ev['round'] ?> ·
                            <?= \App\Models\EvaluationModel::RECOMMENDATIONS[$ev['recommendation']] ?? $ev['recommendation'] ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Riwayat email -->
        <div class="card" style="margin-top:18px;">
            <div class="section-title" style="margin-top:0;">Riwayat Email</div>
            <?php if ($emailLog === []): ?>
                <p style="color:var(--ink-soft); font-size:13px;">Belum ada email ke pelamar ini.</p>
            <?php else: ?>
                <?php foreach ($emailLog as $mail): ?>
                    <div style="border-top:1px solid var(--line); padding:9px 0;">
                        <div style="font-size:13px;"><?= esc($mail['subject']) ?></div>
                        <div style="font-size:11.5px; color:var(--ink-faint);">
                            <?php
                            $mailBadge = match ($mail['status']) {
                                'sent' => 'badge-green', 'failed' => 'badge-red', default => 'badge-amber',
                            };
                            $mailLabel = ['queued' => 'Antre', 'sent' => 'Terkirim', 'failed' => 'Gagal'][$mail['status']] ?? $mail['status'];
                            ?>
                            <span class="badge <?= $mailBadge ?>"><?= $mailLabel ?></span>
                            <?= tgl($mail['created_at'], true) ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
/* Tampilkan kolom "alasan tidak lolos" hanya bila tahap tujuan ber-kind rejected. */
(function () {
    var sel = document.getElementById('stage_id');
    var row = document.getElementById('rowAlasan');
    if (!sel || !row) return;
    function sync() {
        var opt = sel.options[sel.selectedIndex];
        row.style.display = opt && opt.getAttribute('data-kind') === 'rejected' ? 'block' : 'none';
    }
    sel.addEventListener('change', sync);
    sync();
})();
</script>
<?= $this->endSection() ?>
