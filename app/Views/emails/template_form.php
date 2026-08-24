<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-head">
    <div>
        <a href="<?= site_url('email/template') ?>" style="font-size:13px; color:var(--ink-soft);">← Semua template</a>
        <h1 class="page-title" style="margin-top:8px;"><span class="chip"><?= icon('edit') ?></span> <?= esc($template['name']) ?></h1>
        <p class="page-sub">Kode: <?= esc($template['code']) ?></p>
    </div>
</div>
<form method="post" action="<?= site_url('email/template/' . $template['id']) ?>">
    <?= csrf_field() ?>
    <div class="grid" style="grid-template-columns: minmax(0,1.6fr) minmax(0,1fr); align-items:start;">
        <div class="card">
            <div class="form-row">
                <label for="name">Nama Template</label>
                <input type="text" id="name" name="name" class="form-control" required value="<?= esc(old('name', $template['name'])) ?>">
            </div>
            <div class="form-row">
                <label for="subject">Subjek</label>
                <input type="text" id="subject" name="subject" class="form-control" required value="<?= esc(old('subject', $template['subject'])) ?>">
            </div>
            <div class="form-row">
                <label for="body">Isi Email (boleh HTML)</label>
                <textarea id="body" name="body" class="form-control" required style="min-height:280px; font-family:ui-monospace, monospace; font-size:12.5px;"><?= esc(old('body', $template['body'])) ?></textarea>
            </div>
            <div style="display:flex; gap:20px; flex-wrap:wrap;">
                <label style="display:flex; gap:8px; align-items:center; font-size:13px;">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" <?= $template['is_active'] ? 'checked' : '' ?>> Aktif
                </label>
                <label style="display:flex; gap:8px; align-items:center; font-size:13px;">
                    <input type="hidden" name="auto_send" value="0">
                    <input type="checkbox" name="auto_send" value="1" <?= $template['auto_send'] ? 'checked' : '' ?>> Kirim otomatis
                </label>
            </div>
            <button type="submit" class="btn btn-primary" style="margin-top:16px;"><?= icon('check') ?> Simpan Template</button>
        </div>
        <div class="card">
            <div class="section-title" style="margin-top:0;">Placeholder Tersedia</div>
            <table style="width:100%; font-size:12px;">
                <?php foreach (\App\Models\EmailTemplateModel::PLACEHOLDERS as $ph => $ket): ?>
                    <tr>
                        <td style="padding:4px 0; font-family:ui-monospace,monospace; color:var(--blue-deep); white-space:nowrap;"><?= esc($ph) ?></td>
                        <td style="padding:4px 0 4px 10px; color:var(--ink-soft);"><?= esc($ket) ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>
    </div>
</form>
<?= $this->endSection() ?>
