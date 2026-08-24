<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-head">
    <div>
        <a href="<?= site_url('email') ?>" style="font-size:13px; color:var(--ink-soft);">← Semua email</a>
        <h1 class="page-title" style="margin-top:8px;"><span class="chip"><?= icon('mail') ?></span> Email</h1>
    </div>
</div>
<div class="card" style="max-width:760px;">
    <table style="width:100%; font-size:13px; margin-bottom:16px;">
        <tr><td style="color:var(--ink-soft); padding:4px 0; width:110px;">Kepada</td><td><?= esc($mail['to_name']) ?> &lt;<?= esc($mail['to_email']) ?>&gt;</td></tr>
        <tr><td style="color:var(--ink-soft); padding:4px 0;">Subjek</td><td><b><?= esc($mail['subject']) ?></b></td></tr>
        <tr><td style="color:var(--ink-soft); padding:4px 0;">Status</td><td>
            <?php $b = ['sent'=>'badge-green','failed'=>'badge-red','queued'=>'badge-amber'][$mail['status']];
            $l = ['sent'=>'Terkirim','failed'=>'Gagal','queued'=>'Antre'][$mail['status']]; ?>
            <span class="badge <?= $b ?>"><?= $l ?></span>
            <?= $mail['sent_at'] ? ' pada ' . tgl($mail['sent_at'], true) : '' ?>
        </td></tr>
        <tr><td style="color:var(--ink-soft); padding:4px 0;">Dibuat oleh</td><td><?= esc($mail['sender_name'] ?: 'sistem') ?> · <?= tgl($mail['created_at'], true) ?></td></tr>
        <?php if ($mail['last_error']): ?>
            <tr><td style="color:var(--ink-soft); padding:4px 0;">Galat</td><td style="color:var(--red);"><?= esc($mail['last_error']) ?></td></tr>
        <?php endif; ?>
    </table>
    <div class="section-title" style="margin-top:0;">Isi</div>
    <div style="border:1px solid var(--line); border-radius:10px; padding:16px; font-size:13.5px; line-height:1.6;">
        <?= $mail['body'] ?>
    </div>
</div>
<?= $this->endSection() ?>
