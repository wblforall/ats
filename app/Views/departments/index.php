<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<?php $bolehUbah = in_array($auth['role'], ['superadmin', 'hr_admin'], true); ?>

<div class="page-head">
    <div>
        <h1 class="page-title">
            <span class="chip"><?= icon('layers') ?></span>
            Departemen
        </h1>
        <p class="page-sub">
            <?= count($departments) ?> departemen · setiap departemen terikat pada satu unit usaha
        </p>
    </div>
</div>

<div class="alert alert-info">
    <?= icon('info') ?>
    <span>
        Departemen tidak bisa berpindah unit setelah dibuat. Saat membuat lowongan,
        pilihan departemen otomatis tersaring mengikuti unit yang dipilih.
    </span>
</div>

<div class="grid" style="grid-template-columns: minmax(0, 1fr) minmax(0, 320px); align-items:start;">
    <div class="table-wrap">
        <?php if ($departments === []): ?>
            <div class="empty">
                <?= icon('layers') ?>
                <div><b>Belum ada departemen.</b></div>
                <div style="margin-top:4px;">Tambahkan lewat formulir di samping.</div>
            </div>
        <?php else: ?>
            <table class="data roomy">
                <thead>
                    <tr>
                        <th>Departemen</th>
                        <th>Unit Usaha</th>
                        <th class="num">Lowongan</th>
                        <th>Status</th>
                        <?php if ($bolehUbah): ?><th></th><?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($departments as $dept): ?>
                        <tr>
                            <td>
                                <?php if ($bolehUbah): ?>
                                    <form method="post" action="<?= site_url('master/departemen/' . $dept['id'] . '/ubah') ?>"
                                          class="row-form">
                                        <?= csrf_field() ?>
                                        <input type="text" name="name" class="form-control" required
                                               value="<?= esc($dept['name']) ?>">
                                        <input type="hidden" name="is_active" value="0">
                                        <label style="display:flex; gap:5px; align-items:center; font-size:12px; white-space:nowrap;">
                                            <input type="checkbox" name="is_active" value="1" <?= $dept['is_active'] ? 'checked' : '' ?>>
                                            aktif
                                        </label>
                                        <button type="submit" class="btn btn-ghost btn-sm"><?= icon('check') ?> Simpan</button>
                                    </form>
                                <?php else: ?>
                                    <b><?= esc($dept['name']) ?></b>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge badge-blue"><?= esc($dept['unit_code']) ?></span>
                                <small><?= esc($dept['unit_name']) ?></small>
                            </td>
                            <td class="num"><?= (int) $dept['job_count'] ?></td>
                            <td>
                                <?php if ($dept['is_active']): ?>
                                    <span class="badge badge-green">Aktif</span>
                                <?php else: ?>
                                    <span class="badge badge-gray">Nonaktif</span>
                                <?php endif; ?>
                            </td>
                            <?php if ($bolehUbah): ?>
                                <td style="text-align:right;">
                                    <form method="post" action="<?= site_url('master/departemen/' . $dept['id'] . '/hapus') ?>">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-ghost btn-sm"
                                                data-confirm="Hapus departemen &quot;<?= esc($dept['name'], 'attr') ?>&quot;?">
                                            <?= icon('trash') ?>
                                        </button>
                                    </form>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <?php if ($bolehUbah): ?>
        <div class="card">
            <div class="section-title" style="margin-top:0;">Tambah Departemen</div>

            <form method="post" action="<?= site_url('master/departemen') ?>">
                <?= csrf_field() ?>

                <div class="form-row">
                    <label for="unit_id">Unit Usaha <span class="req">*</span></label>
                    <select id="unit_id" name="unit_id" class="form-control" required>
                        <?php foreach ($units as $unit): ?>
                            <option value="<?= $unit['id'] ?>" <?= (int) old('unit_id') === (int) $unit['id'] ? 'selected' : '' ?>>
                                <?= esc($unit['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="hint">Tidak bisa diubah setelah departemen dibuat.</div>
                </div>

                <div class="form-row">
                    <label for="name">Nama Departemen <span class="req">*</span></label>
                    <input type="text" id="name" name="name" class="form-control" required
                           value="<?= esc(old('name')) ?>" placeholder="mis. Operasional">
                    <div class="hint">Nama yang sama boleh dipakai di unit berbeda.</div>
                </div>

                <button type="submit" class="btn btn-primary" style="width:100%; justify-content:center;">
                    <?= icon('plus') ?> Tambah
                </button>
            </form>
        </div>
    <?php endif; ?>
</div>

<?= $this->endSection() ?>
