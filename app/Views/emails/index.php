<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="page-head">
    <div>
        <h1 class="page-title"><span class="chip"><?= icon('mail') ?></span> Email</h1>
        <p class="page-sub">Antrean kiriman ke pelamar</p>
    </div>
    <div class="spacer"></div>
    <a href="<?= site_url('email/template') ?>" class="btn btn-ghost"><?= icon('edit') ?> Template</a>
    <?php if ($counts['queued'] + $counts['failed'] > 0): ?>
        <form method="post" action="<?= site_url('email/kirim-semua') ?>" style="display:inline;">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-primary" data-confirm="Kirim semua email yang tertunda sekarang?">
                <?= icon('mail') ?> Kirim Semua Tertunda
            </button>
        </form>
    <?php endif; ?>
</div>

<?php if (! $smtpReady): ?>
    <div class="alert alert-info">
        <?= icon('info') ?>
        <span>Server email (SMTP) belum diatur di <b>.env</b>. Email tetap tercatat di antrean dan bisa dikirim setelah SMTP dikonfigurasi.</span>
    </div>
<?php endif; ?>

<div style="display:flex; gap:8px; margin-bottom:16px; flex-wrap:wrap;">
    <?php
    $tabs = [
        ''       => 'Semua (' . $counts['all'] . ')',
        'queued' => 'Antre (' . $counts['queued'] . ')',
        'sent'   => 'Terkirim (' . $counts['sent'] . ')',
        'failed' => 'Gagal (' . $counts['failed'] . ')',
    ];
    foreach ($tabs as $key => $label): ?>
        <a href="<?= site_url('email') . ($key ? '?status=' . $key : '') ?>"
           class="btn <?= $filter === $key ? 'btn-primary' : 'btn-ghost' ?> btn-sm"><?= $label ?></a>
    <?php endforeach; ?>
</div>

<div class="table-wrap">
    <?php if ($rows === []): ?>
        <div class="empty"><?= icon('mail') ?><div><b>Tidak ada email.</b></div></div>
    <?php else: ?>
        <table class="data roomy">
            <thead>
                <tr><th>Penerima</th><th>Subjek</th><th>Status</th><th>Dibuat</th><th></th></tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $mail): ?>
                    <tr>
                        <td>
                            <b><?= esc($mail['to_name'] ?: $mail['to_email']) ?></b><br>
                            <small><?= esc($mail['to_email']) ?></small>
                        </td>
                        <td>
                            <a href="<?= site_url('email/' . $mail['id']) ?>"><?= esc($mail['subject']) ?></a>
                            <?php if ($mail['last_error']): ?>
                                <br><small style="color:var(--red);"><?= esc(mb_substr($mail['last_error'], 0, 60)) ?></small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php
                            $b = ['sent' => 'badge-green', 'failed' => 'badge-red', 'queued' => 'badge-amber'][$mail['status']];
                            $l = ['sent' => 'Terkirim', 'failed' => 'Gagal', 'queued' => 'Antre'][$mail['status']];
                            ?>
                            <span class="badge <?= $b ?>"><?= $l ?></span>
                            <?php if ((int) $mail['attempts'] > 0): ?><small style="color:var(--ink-faint);"> · <?= (int) $mail['attempts'] ?>x</small><?php endif; ?>
                        </td>
                        <td><small><?= sejak($mail['created_at']) ?></small></td>
                        <td style="text-align:right;">
                            <?php if ($mail['status'] !== 'sent'): ?>
                                <form method="post" action="<?= site_url('email/' . $mail['id'] . '/kirim') ?>" style="display:inline;">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-ghost btn-sm"><?= icon('mail') ?> Kirim</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?= $this->endSection() ?>
