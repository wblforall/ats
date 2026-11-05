<?= $this->extend('layouts/public') ?>

<?= $this->section('hero') ?>
<div class="pub-hero">
    <h1><?= $iv ? 'Jadwal Interview Anda' : 'Tautan Tidak Berlaku' ?></h1>
    <p>
        <?php if ($iv): ?>
            <?= esc($iv['job_title']) ?> · <?= esc($iv['unit_name'] ?? '') ?>
        <?php else: ?>
            Tautan ini salah atau jadwalnya sudah diubah.
        <?php endif; ?>
    </p>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<?php if (! $iv): ?>
    <div class="pub-panel" style="max-width:560px; margin:0 auto; font-size:14px; line-height:1.7;">
        Bila jadwal interview Anda diubah, kami mengirim email undangan baru — gunakan tombol pada
        email yang <b>terbaru</b>. Butuh bantuan? Hubungi
        <a href="mailto:<?= esc($settings['hr_email'] ?? '') ?>"><?= esc($settings['hr_email'] ?? 'HRD') ?></a>.
    </div>
<?php else: ?>
<?php
$at       = $iv['scheduled_at'];
$aktif    = $iv['status'] === 'scheduled' && strtotime($at) > time();
$status   = $iv['response_status'];
$besok    = date('Y-m-d', strtotime('+1 day'));
?>

<div style="max-width:560px; margin:0 auto; display:flex; flex-direction:column; gap:16px;">
    <div class="pub-panel">
        <div class="pub-body-title" style="margin-top:0;">Halo, <?= esc($iv['full_name']) ?></div>
        <table class="data" style="font-size:14px;">
            <tr><td style="width:120px; color:var(--ink-soft);">Hari/Tanggal</td><td><b><?= hari($at) ?>, <?= tgl($at) ?></b></td></tr>
            <tr><td style="color:var(--ink-soft);">Waktu</td><td><b><?= date('H:i', strtotime($at)) ?> WITA</b> (<?= (int) $iv['duration_min'] ?> menit)</td></tr>
            <tr><td style="color:var(--ink-soft);">Bentuk</td><td><?= esc(\App\Models\InterviewModel::MODES[$iv['mode']] ?? $iv['mode']) ?></td></tr>
            <?php if ($iv['location']): ?>
                <tr><td style="color:var(--ink-soft);">Tempat</td><td><?= esc($iv['location']) ?></td></tr>
            <?php endif; ?>
            <?php if ($iv['meeting_link']): ?>
                <tr><td style="color:var(--ink-soft);">Tautan</td><td style="word-break:break-all;"><a href="<?= esc($iv['meeting_link'], 'attr') ?>" target="_blank" rel="noopener"><?= esc($iv['meeting_link']) ?></a></td></tr>
            <?php endif; ?>
        </table>

        <?php if ($status === 'canceled_by_candidate'): ?>
            <div class="alert alert-info" style="margin:14px 0 0;"><?= icon('info') ?><span>Anda membatalkan interview ini pada <?= tgl($iv['responded_at'], true) ?>. Tim HRD sudah diberi tahu.</span></div>
        <?php elseif (! $aktif): ?>
            <div class="alert alert-info" style="margin:14px 0 0;"><?= icon('info') ?><span>Jadwal ini sudah tidak aktif, jadi tidak perlu dijawab lagi.</span></div>
        <?php elseif ($status === 'confirmed'): ?>
            <div class="alert alert-success" style="margin:14px 0 0;"><?= icon('check') ?><span>Anda sudah mengonfirmasi hadir pada <?= tgl($iv['responded_at'], true) ?>.</span></div>
        <?php elseif ($status === 'reschedule_requested'): ?>
            <div class="alert alert-info" style="margin:14px 0 0;"><?= icon('clock') ?><span>
                Permintaan jadwal ulang Anda ke <b><?= hari($iv['reschedule_proposed_at']) ?>, <?= tgl($iv['reschedule_proposed_at'], true) ?> WITA</b>
                sedang ditinjau HRD. Sampai ada email undangan baru, jadwal di atas masih berlaku.
            </span></div>
        <?php endif; ?>
    </div>

    <?php if ($bolehHadir && $status !== 'confirmed'): ?>
        <form method="post" action="<?= $action ?>/hadir" class="pub-panel" id="hadir"
              style="<?= $aksi === 'hadir' ? 'outline:2px solid var(--green); outline-offset:-2px;' : '' ?>">
            <?= csrf_field() ?>
            <div class="pub-body-title" style="margin-top:0;">Bisa hadir?</div>
            <p style="font-size:13.5px; color:var(--ink-soft); margin:0 0 14px;">
                <?= $status === 'reschedule_requested'
                    ? 'Bila ternyata bisa hadir sesuai jadwal di atas, konfirmasi di sini — permintaan jadwal ulang Anda akan dibatalkan.'
                    : 'Tekan tombol di bawah untuk mengonfirmasi kehadiran Anda sesuai jadwal di atas.' ?>
            </p>
            <button type="submit" class="btn btn-primary" style="width:100%; justify-content:center; background:var(--green); border-color:var(--green);">
                <?= icon('check') ?> Saya Hadir Sesuai Jadwal
            </button>
        </form>
    <?php endif; ?>

    <?php if ($bolehUlang): ?>
        <form method="post" action="<?= $action ?>/ulang" class="pub-panel" id="ulang"
              style="<?= $aksi === 'ulang' ? 'outline:2px solid var(--blue); outline-offset:-2px;' : '' ?>">
            <?= csrf_field() ?>
            <div class="pub-body-title" style="margin-top:0;"><?= $status === 'reschedule_requested' ? 'Ubah usulan jadwal' : 'Berhalangan? Minta jadwal ulang' ?></div>
            <p style="font-size:13.5px; color:var(--ink-soft); margin:0 0 14px;">
                Usulkan waktu lain. HRD akan meninjau dan mengirim undangan baru bila disetujui.
                Permintaan bisa diajukan paling lambat H-1.
            </p>
            <div class="grid grid-2">
                <div class="form-row">
                    <label for="tanggal">Tanggal usulan <span class="req">*</span></label>
                    <input type="date" id="tanggal" name="tanggal" class="form-control" required min="<?= $besok ?>"
                           value="<?= esc(old('tanggal')) ?>">
                </div>
                <div class="form-row">
                    <label for="jam">Jam usulan (WITA) <span class="req">*</span></label>
                    <input type="time" id="jam" name="jam" class="form-control" required value="<?= esc(old('jam')) ?>">
                </div>
            </div>
            <div class="form-row">
                <label for="alasan">Alasan <span class="req">*</span></label>
                <textarea id="alasan" name="alasan" class="form-control" required maxlength="500"
                          placeholder="mis. Bentrok dengan jadwal ujian di kampus"><?= esc(old('alasan')) ?></textarea>
            </div>
            <button type="submit" class="btn btn-ghost" style="width:100%; justify-content:center;">
                <?= icon('calendar') ?> Kirim Permintaan Jadwal Ulang
            </button>
        </form>
    <?php elseif ($aktif): ?>
        <div class="pub-panel" style="font-size:13px; color:var(--ink-soft); line-height:1.7;">
            Permintaan jadwal ulang hanya bisa diajukan paling lambat H-1. Bila tidak bisa hadir, batalkan interview di bawah ini
            atau hubungi <a href="mailto:<?= esc($settings['hr_email'] ?? '') ?>"><?= esc($settings['hr_email'] ?? 'HRD') ?></a><?= ! empty($settings['hr_phone']) ? ' / ' . esc($settings['hr_phone']) : '' ?>.
        </div>
    <?php endif; ?>

    <?php if ($bolehHadir): ?>
        <details class="pub-panel" id="batal" <?= $aksi === 'batal' ? 'open' : '' ?>
                 style="<?= $aksi === 'batal' ? 'outline:2px solid #DC2626; outline-offset:-2px;' : '' ?>">
            <summary style="cursor:pointer; font-size:13.5px; color:var(--ink-soft);">
                Tidak bisa hadir sama sekali? <b style="color:#B91C1C;">Batalkan interview</b>
            </summary>
            <form method="post" action="<?= $action ?>/batal" style="margin-top:14px;">
                <?= csrf_field() ?>
                <p style="font-size:13px; color:var(--ink-soft); margin:0 0 12px;">
                    Jadwal akan langsung dibatalkan dan tim HRD diberi tahu, jadi pewawancara tidak menunggu Anda.
                </p>
                <div class="form-row">
                    <label for="alasan_batal">Alasan <span class="req">*</span></label>
                    <textarea id="alasan_batal" name="alasan_batal" class="form-control" required maxlength="500" style="min-height:80px;"
                              placeholder="mis. Sudah menerima tawaran kerja lain"><?= esc(old('alasan_batal')) ?></textarea>
                </div>
                <button type="submit" class="btn btn-ghost" style="width:100%; justify-content:center; color:#B91C1C;"
                        data-confirm="Batalkan interview ini? Tindakan ini tidak bisa diurungkan dari halaman ini.">
                    <?= icon('x') ?> Batalkan Interview
                </button>
            </form>
        </details>
    <?php endif; ?>
</div>
<?php endif; ?>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<?php if (! empty($aksi)): ?>
<script>
(function () {
    var el = document.getElementById(<?= json_encode(in_array($aksi, ['ulang', 'batal'], true) ? $aksi : 'hadir') ?>);
    if (el) el.scrollIntoView({ block: 'center' });
})();
</script>
<?php endif; ?>
<?= $this->endSection() ?>
