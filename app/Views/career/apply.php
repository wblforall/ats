<?= $this->extend('layouts/public') ?>

<?= $this->section('hero') ?>
<div class="pub-hero" style="text-align:left; padding-bottom: 56px;">
    <div style="max-width:1100px; margin:0 auto; padding: 0 22px;">
        <a href="<?= site_url('karir/' . $job['slug']) ?>" style="color:#fff; opacity:.85; font-size:13px;">← Kembali ke detail lowongan</a>
        <h1 style="margin-top:10px;">Formulir Lamaran</h1>
        <p style="margin:0;"><?= esc($job['title']) ?> · <?= esc($job['unit_name']) ?></p>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<?php
$errors = session()->getFlashdata('errors') ?? [];

/* Penanda field wajib, ditulis sekali agar bentuknya seragam di seluruh form. */
$wajib = '<span class="req" title="Wajib diisi">*</span>';
?>

<p style="color:var(--ink-soft); font-size:13px; margin:0 0 16px;">
    Field bertanda <span class="req">*</span> wajib diisi.
</p>

<?php if ($errors): ?>
    <div class="alert alert-error">
        <?= icon('alert') ?>
        <span>
            <b>Periksa kembali isian berikut:</b>
            <ul style="margin:6px 0 0; padding-left:18px;">
                <?php foreach ($errors as $message): ?><li><?= esc($message) ?></li><?php endforeach; ?>
            </ul>
        </span>
    </div>
<?php endif; ?>

<form action="<?= site_url('karir/' . $job['slug'] . '/lamar') ?>" method="post" enctype="multipart/form-data">
    <?= csrf_field() ?>

    <div class="pub-panel">
        <div class="pub-body-title" style="margin-top:0;">Data Diri</div>

        <div class="grid grid-2">
            <div class="form-row">
                <label for="full_name">Nama Lengkap <?= $wajib ?></label>
                <input type="text" id="full_name" name="full_name" class="form-control" required
                       value="<?= esc(old('full_name')) ?>" placeholder="Sesuai KTP">
            </div>

            <div class="form-row">
                <label for="email">Email <?= $wajib ?></label>
                <input type="email" id="email" name="email" class="form-control" required
                       value="<?= esc(old('email')) ?>" placeholder="nama@email.com">
                <div class="hint">Nomor lamaran dan semua pemberitahuan dikirim ke alamat ini.</div>
            </div>

            <div class="form-row">
                <label for="phone">Nomor HP / WhatsApp <?= $wajib ?></label>
                <input type="text" id="phone" name="phone" class="form-control" required
                       value="<?= esc(old('phone')) ?>" placeholder="08xxxxxxxxxx">
            </div>

            <div class="form-row">
                <label for="city">Kota Domisili <?= $wajib ?></label>
                <input type="text" id="city" name="city" class="form-control" required value="<?= esc(old('city')) ?>">
            </div>

            <div class="form-row">
                <label for="gender">Jenis Kelamin <?= $wajib ?></label>
                <select id="gender" name="gender" class="form-control" required>
                    <option value="">Pilih…</option>
                    <option value="L" <?= old('gender') === 'L' ? 'selected' : '' ?>>Laki-laki</option>
                    <option value="P" <?= old('gender') === 'P' ? 'selected' : '' ?>>Perempuan</option>
                </select>
            </div>

            <div class="form-row">
                <label for="birth_date">Tanggal Lahir <?= $wajib ?></label>
                <input type="date" id="birth_date" name="birth_date" class="form-control" required
                       max="<?= date('Y-m-d', strtotime('-15 years')) ?>" value="<?= esc(old('birth_date')) ?>">
            </div>

            <div class="form-row">
                <label for="instagram">Akun Instagram <?= $wajib ?></label>
                <input type="text" id="instagram" name="instagram" class="form-control" required
                       value="<?= esc(old('instagram')) ?>" placeholder="namaakun">
                <div class="hint">Cukup nama akunnya saja, tanpa tanda @. Kami pakai sebagai bahan referensi.</div>
            </div>

            <div class="form-row">
                <label for="linkedin_url">LinkedIn</label>
                <input type="text" id="linkedin_url" name="linkedin_url" class="form-control"
                       value="<?= esc(old('linkedin_url')) ?>" placeholder="linkedin.com/in/namaanda">
                <div class="hint">Opsional — isi bila Anda punya.</div>
            </div>
        </div>
    </div>

    <div class="pub-panel" style="margin-top:18px;">
        <div class="pub-body-title" style="margin-top:0;">Pendidikan & Pengalaman</div>

        <div class="grid grid-2">
            <div class="form-row">
                <label for="education_level">Pendidikan Terakhir <?= $wajib ?></label>
                <select id="education_level" name="education_level" class="form-control" required>
                    <option value="">Pilih…</option>
                    <?php foreach (\App\Models\CandidateModel::EDUCATION_LEVELS as $level): ?>
                        <option value="<?= $level ?>" <?= old('education_level') === $level ? 'selected' : '' ?>><?= $level ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-row">
                <label for="school">Nama Sekolah / Universitas <?= $wajib ?></label>
                <input type="text" id="school" name="school" class="form-control" required value="<?= esc(old('school')) ?>">
            </div>

            <div class="form-row">
                <label for="major">Jurusan</label>
                <input type="text" id="major" name="major" class="form-control" value="<?= esc(old('major')) ?>">
            </div>

            <div class="form-row">
                <label for="years_experience">Lama Pengalaman Kerja (tahun)</label>
                <input type="number" step="0.5" min="0" id="years_experience" name="years_experience"
                       class="form-control" value="<?= esc(old('years_experience')) ?>" placeholder="0">
            </div>

            <div class="form-row">
                <label for="current_position">Posisi Saat Ini</label>
                <input type="text" id="current_position" name="current_position" class="form-control" value="<?= esc(old('current_position')) ?>">
            </div>

            <div class="form-row">
                <label for="current_company">Perusahaan Saat Ini</label>
                <input type="text" id="current_company" name="current_company" class="form-control" value="<?= esc(old('current_company')) ?>">
            </div>
        </div>
    </div>

    <div class="pub-panel" style="margin-top:18px;">
        <div class="pub-body-title" style="margin-top:0;">Berkas & Keterangan</div>

        <div class="form-row">
            <label for="cv">Unggah CV <?= $wajib ?></label>
            <input type="file" id="cv" name="cv" class="form-control" required
                   accept=".<?= str_replace(',', ',.', $settings['cv_allowed_ext'] ?? 'pdf,doc,docx') ?>">
            <div class="hint">
                Format <?= strtoupper(str_replace(',', ', ', $settings['cv_allowed_ext'] ?? 'pdf,doc,docx')) ?>,
                maksimal <?= (int) ($settings['cv_max_mb'] ?? 5) ?> MB.
            </div>
        </div>

        <div class="grid grid-2">
            <div class="form-row">
                <label for="expected_salary">Gaji yang Diharapkan (Rp) <?= $wajib ?></label>
                <input type="text" inputmode="numeric" data-money id="expected_salary" name="expected_salary"
                       class="form-control" required value="<?= esc(nominal(old('expected_salary'))) ?>" placeholder="5.000.000">
                <div class="hint">Cukup angkanya, pemisah ribuan terisi otomatis.</div>
            </div>

            <div class="form-row">
                <label for="available_from">Siap Mulai Bekerja</label>
                <input type="date" id="available_from" name="available_from" class="form-control" value="<?= esc(old('available_from')) ?>">
            </div>

            <div class="form-row">
                <label for="source">Tahu lowongan ini dari</label>
                <select id="source" name="source" class="form-control">
                    <?php foreach (\App\Models\ApplicationModel::SOURCES as $key => $label): ?>
                        <option value="<?= $key ?>" <?= old('source') === $key ? 'selected' : '' ?>><?= $label ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-row">
                <label for="referral_name">Nama Perekomendasi (bila ada)</label>
                <input type="text" id="referral_name" name="referral_name" class="form-control" value="<?= esc(old('referral_name')) ?>">
            </div>
        </div>

        <div class="form-row">
            <label for="cover_letter">Surat Lamaran Singkat</label>
            <textarea id="cover_letter" name="cover_letter" class="form-control"
                      placeholder="Ceritakan singkat mengapa Anda cocok untuk posisi ini."><?= esc(old('cover_letter')) ?></textarea>
        </div>

        <div style="display:flex; gap:10px; align-items:center; margin-top:18px; flex-wrap:wrap;">
            <button type="submit" class="btn btn-primary"><?= icon('check') ?> Kirim Lamaran</button>
            <a href="<?= site_url('karir/' . $job['slug']) ?>" class="btn btn-ghost">Batal</a>
            <span style="font-size:12px; color:var(--ink-faint);">
                Dengan mengirim, Anda setuju data ini diproses untuk keperluan rekrutmen.
            </span>
        </div>
    </div>
</form>

<?= $this->endSection() ?>
