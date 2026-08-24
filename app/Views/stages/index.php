<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php $kinds = ['open' => 'Berjalan', 'hired' => 'Diterima', 'rejected' => 'Tidak Lolos']; ?>

<div class="page-head">
    <div>
        <h1 class="page-title"><span class="chip"><?= icon('layers') ?></span> Tahapan Pipeline</h1>
        <p class="page-sub"><?= count($stages) ?> tahap · urutan menentukan susunan papan pipeline</p>
    </div>
</div>

<div class="alert alert-info">
    <?= icon('info') ?>
    <span>Status lamaran mengikuti jenis tahap: <b>Diterima</b> menutup sebagai diterima, <b>Tidak Lolos</b> sebagai gugur, sisanya tetap berjalan. Sebaiknya ada tepat satu tahap Diterima dan satu Tidak Lolos.</span>
</div>

<div class="grid" style="grid-template-columns: minmax(0,1fr) minmax(0,300px); align-items:start;">
    <div class="table-wrap">
        <table class="data roomy">
            <thead><tr><th>Tahap</th><th>Jenis</th><th class="num">Urut</th><th class="num">Aktif Kini</th><th>Status</th><th></th></tr></thead>
            <tbody>
                <?php foreach ($stages as $s): ?>
                    <tr>
                        <td>
                            <form method="post" action="<?= site_url('master/tahapan/' . $s['id'] . '/ubah') ?>" class="row-form">
                                <?= csrf_field() ?>
                                <input type="color" name="color" value="<?= esc($s['color']) ?>" style="width:34px; height:34px; border:1px solid var(--line); border-radius:8px; padding:2px; flex:0 0 34px;">
                                <input type="text" name="name" class="form-control" required value="<?= esc($s['name']) ?>">
                                <select name="kind" class="form-control" style="flex:0 0 120px;">
                                    <?php foreach ($kinds as $k => $lbl): ?>
                                        <option value="<?= $k ?>" <?= $s['kind'] === $k ? 'selected' : '' ?>><?= $lbl ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <input type="number" name="sort_order" class="form-control" style="flex:0 0 60px;" value="<?= (int) $s['sort_order'] ?>">
                                <input type="hidden" name="is_active" value="0">
                                <label><input type="checkbox" name="is_active" value="1" <?= $s['is_active'] ? 'checked' : '' ?>> aktif</label>
                                <button type="submit" class="btn btn-ghost btn-sm"><?= icon('check') ?></button>
                            </form>
                        </td>
                        <td><span class="badge badge-gray"><?= $kinds[$s['kind']] ?></span></td>
                        <td class="num"><?= (int) $s['sort_order'] ?></td>
                        <td class="num"><?= (int) ($counts[$s['id']] ?? 0) ?></td>
                        <td><?= $s['is_active'] ? '<span class="badge badge-green">Aktif</span>' : '<span class="badge badge-gray">Nonaktif</span>' ?></td>
                        <td style="text-align:right;">
                            <form method="post" action="<?= site_url('master/tahapan/' . $s['id'] . '/hapus') ?>">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-ghost btn-sm" data-confirm="Hapus tahap ini?"><?= icon('trash') ?></button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="card">
        <div class="section-title" style="margin-top:0;">Tambah Tahap</div>
        <form method="post" action="<?= site_url('master/tahapan') ?>">
            <?= csrf_field() ?>
            <div class="form-row">
                <label for="name">Nama Tahap <span class="req">*</span></label>
                <input type="text" id="name" name="name" class="form-control" required value="<?= esc(old('name')) ?>" placeholder="mis. Medical Check Up">
            </div>
            <div class="form-row">
                <label for="kind">Jenis</label>
                <select id="kind" name="kind" class="form-control">
                    <?php foreach ($kinds as $k => $lbl): ?><option value="<?= $k ?>"><?= $lbl ?></option><?php endforeach; ?>
                </select>
            </div>
            <div class="form-row">
                <label for="color">Warna</label>
                <input type="color" id="color" name="color" class="form-control" value="#64748b" style="height:42px;">
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%; justify-content:center;"><?= icon('plus') ?> Tambah</button>
        </form>
    </div>
</div>
<?= $this->endSection() ?>
