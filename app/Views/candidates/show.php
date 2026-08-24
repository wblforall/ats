<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-head">
    <div>
        <a href="<?= site_url('kandidat') ?>" style="font-size:13px; color:var(--ink-soft);">← Bank talenta</a>
        <h1 class="page-title" style="margin-top:8px;">
            <span class="avatar" style="width:38px;height:38px;flex:0 0 38px;font-size:14px;"><?= esc(initials($candidate['full_name'])) ?></span>
            <?= esc($candidate['full_name']) ?>
        </h1>
        <p class="page-sub"><?= esc($candidate['email']) ?> · <?= esc($candidate['phone']) ?></p>
    </div>
    <div class="spacer"></div>
    <?php if (in_array($auth['role'], ['superadmin','hr_admin'], true)): ?>
        <?php if ($candidate['is_blacklisted']): ?>
            <form method="post" action="<?= site_url('kandidat/' . $candidate['id'] . '/blacklist') ?>">
                <?= csrf_field() ?>
                <button class="btn btn-ghost" data-confirm="Keluarkan dari daftar hitam?"><?= icon('check') ?> Lepas Blacklist</button>
            </form>
        <?php else: ?>
            <form method="post" action="<?= site_url('kandidat/' . $candidate['id'] . '/blacklist') ?>" style="display:flex; gap:8px;">
                <?= csrf_field() ?>
                <input type="text" name="reason" class="form-control" placeholder="Alasan blacklist" style="width:200px;">
                <button class="btn btn-danger"><?= icon('alert') ?> Blacklist</button>
            </form>
        <?php endif; ?>
    <?php endif; ?>
</div>
<?php if ($candidate['is_blacklisted']): ?>
    <div class="alert alert-error"><?= icon('alert') ?><span><b>Daftar hitam.</b> <?= esc($candidate['blacklist_reason'] ?: '') ?></span></div>
<?php endif; ?>
<div class="section-title" style="margin-top:0;">Riwayat Lamaran</div>
<div class="table-wrap">
    <?php if ($applications === []): ?>
        <div class="empty"><?= icon('briefcase') ?><div>Tidak ada lamaran (di unit yang bisa Anda lihat).</div></div>
    <?php else: ?>
        <table class="data roomy">
            <thead><tr><th>Lowongan</th><th>Tahap</th><th>Status</th><th>Dilamar</th></tr></thead>
            <tbody>
                <?php foreach ($applications as $a): ?>
                    <tr onclick="location.href='<?= site_url('pelamar/' . $a['id']) ?>'" style="cursor:pointer;">
                        <td><?= esc($a['job_title']) ?><br><small><?= esc($a['unit_name']) ?></small></td>
                        <td><span class="badge" style="background:<?= esc($a['stage_color']) ?>1F; color:<?= esc($a['stage_color']) ?>;"><span class="dot"></span><?= esc($a['stage_name']) ?></span></td>
                        <td><?= \App\Models\ApplicationModel::STATUSES[$a['status']] ?></td>
                        <td><small><?= tgl($a['applied_at']) ?></small></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
<?= $this->endSection() ?>
