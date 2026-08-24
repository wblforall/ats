<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-head">
    <div>
        <a href="<?= site_url('pelamar/' . $interview['application_id']) ?>#interview" style="font-size:13px; color:var(--ink-soft);">← Kembali ke pelamar</a>
        <h1 class="page-title" style="margin-top:8px;"><span class="chip"><?= icon('star') ?></span> Penilaian Interview</h1>
        <p class="page-sub"><?= esc($interview['full_name']) ?> · <?= esc($interview['job_title']) ?> · putaran <?= (int) $interview['round'] ?></p>
    </div>
</div>

<?php if (! empty($app)): ?>
    <div class="card" style="margin-bottom:18px;">
        <div class="section-title" style="margin-top:0;">Tentang Kandidat</div>
        <div style="display:flex; gap:24px; flex-wrap:wrap; font-size:13px;">
            <div><span style="color:var(--ink-soft);">Pendidikan:</span> <b><?= esc(trim(($app['education_level'] ?? '') . ' ' . ($app['major'] ? '· ' . $app['major'] : ''))) ?: '—' ?></b></div>
            <div><span style="color:var(--ink-soft);">Sekolah:</span> <b><?= esc($app['school'] ?: '—') ?></b></div>
            <div><span style="color:var(--ink-soft);">Pengalaman:</span> <b><?= $app['years_experience'] !== null ? rtrim(rtrim(number_format((float)$app['years_experience'],1),'0'),'.,') . ' th' : '—' ?></b></div>
            <div><span style="color:var(--ink-soft);">Posisi kini:</span> <b><?= esc($app['current_position'] ?: '—') ?></b></div>
            <?php if ($app['cv_path']): ?>
                <a href="<?= site_url('pelamar/' . $app['id'] . '/cv') ?>" class="btn btn-ghost btn-sm"><?= icon('download') ?> Unduh CV</a>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<form method="post" action="<?= site_url('interview/' . $interview['id'] . '/nilai') ?>">
    <?= csrf_field() ?>
    <div class="grid" style="grid-template-columns: minmax(0,1.4fr) minmax(0,1fr); align-items:start;">
        <div class="card">
            <div class="section-title" style="margin-top:0;">Skor Aspek (1–5)</div>
            <?php foreach (\App\Models\EvaluationModel::ASPECTS as $field => $label): ?>
                <?php $cur = (int) ($mine[$field] ?? 0); ?>
                <div style="display:flex; align-items:center; justify-content:space-between; padding:9px 0; border-bottom:1px solid var(--line);">
                    <span style="font-size:13.5px;"><?= $label ?></span>
                    <span style="display:flex; gap:4px;">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <label style="cursor:pointer;">
                                <input type="radio" name="<?= $field ?>" value="<?= $i ?>" <?= $cur === $i ? 'checked' : '' ?> style="display:none;" class="score-radio">
                                <span class="score-dot" style="width:30px; height:30px; display:grid; place-items:center; border:1px solid var(--line); border-radius:8px; font-size:13px; font-weight:600;<?= $cur === $i ? 'background:var(--blue); color:#fff; border-color:var(--blue);' : '' ?>"><?= $i ?></span>
                            </label>
                        <?php endfor; ?>
                    </span>
                </div>
            <?php endforeach; ?>
            <p style="font-size:11.5px; color:var(--ink-faint); margin-top:10px;">Aspek yang tidak dinilai boleh dilewati; nilai akhir dihitung dari yang terisi.</p>
        </div>

        <div>
            <div class="card">
                <div class="form-row">
                    <label for="recommendation">Rekomendasi</label>
                    <select id="recommendation" name="recommendation" class="form-control">
                        <?php foreach (\App\Models\EvaluationModel::RECOMMENDATIONS as $k => $lbl): ?>
                            <option value="<?= $k ?>" <?= ($mine['recommendation'] ?? 'neutral') === $k ? 'selected' : '' ?>><?= $lbl ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-row">
                    <label for="strengths">Kelebihan</label>
                    <textarea id="strengths" name="strengths" class="form-control" style="min-height:70px;"><?= esc($mine['strengths'] ?? '') ?></textarea>
                </div>
                <div class="form-row">
                    <label for="weaknesses">Kekurangan</label>
                    <textarea id="weaknesses" name="weaknesses" class="form-control" style="min-height:70px;"><?= esc($mine['weaknesses'] ?? '') ?></textarea>
                </div>
                <div class="form-row">
                    <label for="notes">Catatan Lain</label>
                    <textarea id="notes" name="notes" class="form-control" style="min-height:60px;"><?= esc($mine['notes'] ?? '') ?></textarea>
                </div>
                <button type="submit" class="btn btn-primary" style="width:100%; justify-content:center;"><?= icon('check') ?> Simpan Penilaian</button>
            </div>
        </div>
    </div>
</form>
<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
/* Sorot kotak skor terpilih. */
document.querySelectorAll('.score-radio').forEach(function (radio) {
    radio.addEventListener('change', function () {
        var group = radio.closest('span').querySelectorAll('.score-radio');
        group.forEach(function (r) {
            var dot = r.nextElementSibling;
            var on = r.checked;
            dot.style.background = on ? 'var(--blue)' : '';
            dot.style.color = on ? '#fff' : '';
            dot.style.borderColor = on ? 'var(--blue)' : 'var(--line)';
        });
    });
});
</script>
<?= $this->endSection() ?>
