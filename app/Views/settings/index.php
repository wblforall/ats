<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-head">
    <div><h1 class="page-title"><span class="chip"><?= icon('settings') ?></span> Pengaturan</h1>
    <p class="page-sub">Nilai ini dipakai portal karir dan email. Berlaku ke seluruh unit.</p></div>
</div>
<form method="post" action="<?= site_url('pengaturan') ?>">
    <?= csrf_field() ?>
    <div class="grid" style="grid-template-columns: minmax(0,1fr) minmax(0,1fr); align-items:start;">
        <div class="card">
            <div class="section-title" style="margin-top:0;">Identitas Perusahaan</div>
            <?php
            $teks = [
                'company_name' => 'Nama Perusahaan', 'company_address' => 'Alamat',
                'hr_email' => 'Email HRD', 'hr_phone' => 'Telepon HRD',
            ];
            foreach ($teks as $k => $lbl): ?>
                <div class="form-row">
                    <label for="<?= $k ?>"><?= $lbl ?></label>
                    <input type="text" id="<?= $k ?>" name="<?= $k ?>" class="form-control" value="<?= esc($settings[$k] ?? '') ?>">
                </div>
            <?php endforeach; ?>
        </div>
        <div class="card">
            <div class="section-title" style="margin-top:0;">Portal Karir</div>
            <div class="form-row">
                <label for="career_tagline">Tagline</label>
                <input type="text" id="career_tagline" name="career_tagline" class="form-control" value="<?= esc($settings['career_tagline'] ?? '') ?>">
            </div>
            <div class="form-row">
                <label for="career_intro">Pengantar</label>
                <textarea id="career_intro" name="career_intro" class="form-control"><?= esc($settings['career_intro'] ?? '') ?></textarea>
            </div>
            <div class="section-title">Lamaran</div>
            <div class="grid grid-2">
                <div class="form-row">
                    <label for="cv_max_mb">Maks. CV (MB)</label>
                    <input type="number" id="cv_max_mb" name="cv_max_mb" class="form-control" value="<?= esc($settings['cv_max_mb'] ?? '5') ?>" min="1">
                </div>
                <div class="form-row">
                    <label for="cv_allowed_ext">Format CV</label>
                    <input type="text" id="cv_allowed_ext" name="cv_allowed_ext" class="form-control" value="<?= esc($settings['cv_allowed_ext'] ?? 'pdf,doc,docx') ?>">
                </div>
            </div>
            <div class="form-row">
                <label for="interview_reminder_hours">Pengingat interview (jam sebelum)</label>
                <input type="number" id="interview_reminder_hours" name="interview_reminder_hours" class="form-control" value="<?= esc($settings['interview_reminder_hours'] ?? '24') ?>" min="1">
            </div>
            <label style="display:flex; gap:9px; align-items:center; font-size:13px; margin-top:6px;">
                <input type="checkbox" name="auto_email_on_apply" value="1" <?= ($settings['auto_email_on_apply'] ?? '1') === '1' ? 'checked' : '' ?>>
                Kirim email konfirmasi otomatis saat lamaran masuk
            </label>
        </div>
    </div>
    <button type="submit" class="btn btn-primary" style="margin-top:18px;"><?= icon('check') ?> Simpan Pengaturan</button>
</form>
<?= $this->endSection() ?>
