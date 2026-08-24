<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="page-head">
    <div>
        <a href="<?= site_url('pelamar/' . $app['id']) ?>" style="font-size:13px; color:var(--ink-soft);">← Kembali ke pelamar</a>
        <h1 class="page-title" style="margin-top:8px;">
            <span class="chip"><?= icon('calendar') ?></span>
            Jadwalkan Interview
        </h1>
        <p class="page-sub"><?= esc($app['full_name']) ?> · <?= esc($app['job_title']) ?> (<?= esc($app['unit_name']) ?>)</p>
    </div>
</div>

<form method="post" action="<?= site_url('interview/baru/' . $app['id']) ?>">
    <?= csrf_field() ?>

    <div class="grid" style="grid-template-columns: minmax(0, 1.5fr) minmax(0, 1fr); align-items:start;">
        <div class="card">
            <div class="section-title" style="margin-top:0;">Waktu & Tempat</div>

            <div class="grid grid-2">
                <div class="form-row">
                    <label for="round">Putaran</label>
                    <input type="number" id="round" name="round" class="form-control" min="1" value="<?= (int) $nextRound ?>">
                </div>
                <div class="form-row">
                    <label for="title">Judul (opsional)</label>
                    <input type="text" id="title" name="title" class="form-control" value="<?= esc(old('title')) ?>" placeholder="mis. Wawancara HRD">
                </div>
                <div class="form-row">
                    <label for="tanggal">Tanggal <span class="req">*</span></label>
                    <input type="date" id="tanggal" name="tanggal" class="form-control" required
                           min="<?= date('Y-m-d') ?>" value="<?= esc(old('tanggal')) ?>">
                </div>
                <div class="form-row">
                    <label for="jam">Jam <span class="req">*</span></label>
                    <input type="time" id="jam" name="jam" class="form-control" required value="<?= esc(old('jam', '10:00')) ?>">
                </div>
                <div class="form-row">
                    <label for="duration_min">Durasi (menit)</label>
                    <input type="number" id="duration_min" name="duration_min" class="form-control" min="15" step="15" value="<?= esc(old('duration_min', 60)) ?>">
                </div>
                <div class="form-row">
                    <label for="mode">Mode</label>
                    <select id="mode" name="mode" class="form-control" data-mode>
                        <?php foreach (\App\Models\InterviewModel::MODES as $key => $label): ?>
                            <option value="<?= $key ?>" <?= old('mode') === $key ? 'selected' : '' ?>><?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-row" data-when="onsite,phone">
                <label for="location">Lokasi</label>
                <input type="text" id="location" name="location" class="form-control" value="<?= esc(old('location')) ?>" placeholder="mis. Ruang Rapat HRD, Lantai 3">
            </div>
            <div class="form-row" data-when="online">
                <label for="meeting_link">Tautan Meeting</label>
                <input type="url" id="meeting_link" name="meeting_link" class="form-control" value="<?= esc(old('meeting_link')) ?>" placeholder="https://meet.google.com/...">
            </div>
        </div>

        <div>
            <div class="card">
                <div class="section-title" style="margin-top:0;">Pewawancara</div>
                <?php if ($interviewers === []): ?>
                    <p style="color:var(--ink-soft); font-size:13px;">Belum ada pengguna yang bisa ditugaskan di unit ini.</p>
                <?php else: ?>
                    <div style="display:flex; flex-direction:column; gap:8px;">
                        <?php foreach ($interviewers as $uid => $nama): ?>
                            <label style="display:flex; gap:9px; align-items:center; font-size:13.5px;">
                                <input type="checkbox" name="participants[]" value="<?= $uid ?>">
                                <?= esc($nama) ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="card" style="margin-top:18px;">
                <label style="display:flex; gap:9px; align-items:flex-start; font-size:13px;">
                    <input type="checkbox" name="kirim_undangan" value="1" style="margin-top:3px;" checked>
                    <span>Kirim email undangan ke pelamar begitu jadwal disimpan.</span>
                </label>
                <p style="font-size:11.5px; color:var(--ink-faint); margin:8px 0 0;">
                    Undangan tercatat di riwayat email pelamar. Bila server email belum diatur,
                    undangan menunggu di antrean untuk dikirim ulang.
                </p>
            </div>

            <button type="submit" class="btn btn-primary" style="width:100%; justify-content:center; margin-top:18px;">
                <?= icon('check') ?> Simpan Jadwal
            </button>
        </div>
    </div>
</form>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
/* Tampilkan lokasi atau tautan sesuai mode interview. */
(function () {
    var mode = document.querySelector('[data-mode]');
    if (!mode) return;
    function sync() {
        document.querySelectorAll('[data-when]').forEach(function (el) {
            el.style.display = el.getAttribute('data-when').split(',').indexOf(mode.value) >= 0 ? '' : 'none';
        });
    }
    mode.addEventListener('change', sync);
    sync();
})();
</script>
<?= $this->endSection() ?>
