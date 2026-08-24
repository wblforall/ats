<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php $roleLabels = \App\Controllers\BaseController::ROLE_LABELS; ?>
<div class="page-head">
    <div>
        <h1 class="page-title"><span class="chip"><?= icon('shield') ?></span> Pengguna</h1>
        <p class="page-sub"><?= count($users) ?> pengguna · <?= esc($auth['unit_name']) ?></p>
    </div>
    <div class="spacer"></div>
    <a href="<?= site_url('master/pengguna/baru') ?>" class="btn btn-primary"><?= icon('plus') ?> Pengguna Baru</a>
</div>
<div class="table-wrap">
    <table class="data roomy">
        <thead><tr><th>Nama</th><th>Email</th><th>Peran</th><th>Unit</th><th>Status</th><th></th></tr></thead>
        <tbody>
            <?php foreach ($users as $u): ?>
                <tr>
                    <td>
                        <div style="display:flex; align-items:center; gap:10px;">
                            <span class="avatar" style="width:32px;height:32px;flex:0 0 32px;font-size:11px;"><?= esc(initials($u['name'])) ?></span>
                            <span><b><?= esc($u['name']) ?></b><?php if ($u['position']): ?><br><small><?= esc($u['position']) ?></small><?php endif; ?></span>
                        </div>
                    </td>
                    <td><small><?= esc($u['email']) ?></small></td>
                    <td><span class="badge badge-blue"><?= $roleLabels[$u['role']] ?? $u['role'] ?></span></td>
                    <td><small><?= esc($u['unit_name'] ?: 'Semua unit') ?></small></td>
                    <td><?= $u['is_active'] ? '<span class="badge badge-green">Aktif</span>' : '<span class="badge badge-gray">Nonaktif</span>' ?></td>
                    <td style="text-align:right; white-space:nowrap;">
                        <a href="<?= site_url('master/pengguna/' . $u['id'] . '/ubah') ?>" class="btn btn-ghost btn-sm"><?= icon('edit') ?> Ubah</a>
                        <?php if ((int) $u['id'] !== (int) $auth['id']): ?>
                            <form method="post" action="<?= site_url('master/pengguna/' . $u['id'] . '/hapus') ?>" style="display:inline;">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-ghost btn-sm" data-confirm="Nonaktifkan pengguna &quot;<?= esc($u['name'], 'attr') ?>&quot;?"><?= icon('trash') ?></button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?= $this->endSection() ?>
