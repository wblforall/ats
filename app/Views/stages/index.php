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
                    <tr class="view-row" id="view-<?= $s['id'] ?>">
                        <td>
                            <span style="display:inline-flex; align-items:center; gap:9px;">
                                <span style="width:14px; height:14px; border-radius:4px; background:<?= esc($s['color']) ?>;"></span>
                                <b><?= esc($s['name']) ?></b>
                            </span>
                        </td>
                        <td><span class="badge badge-gray"><?= $kinds[$s['kind']] ?></span></td>
                        <td class="num"><?= (int) $s['sort_order'] ?></td>
                        <td class="num"><?= (int) ($counts[$s['id']] ?? 0) ?></td>
                        <td><?= $s['is_active'] ? '<span class="badge badge-green">Aktif</span>' : '<span class="badge badge-gray">Nonaktif</span>' ?></td>
                        <td style="text-align:right; white-space:nowrap;">
                            <button type="button" class="btn-icon-only" data-edit="<?= $s['id'] ?>" title="Ubah"><?= icon('edit') ?></button>
                            <form method="post" action="<?= site_url('master/tahapan/' . $s['id'] . '/hapus') ?>" style="display:inline;">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn-icon-only" data-confirm="Hapus tahap ini?" title="Hapus"><?= icon('trash') ?></button>
                            </form>
                        </td>
                    </tr>
                    <tr class="edit-row" id="edit-<?= $s['id'] ?>">
                        <td colspan="6">
                            <form method="post" action="<?= site_url('master/tahapan/' . $s['id'] . '/ubah') ?>" class="edit-panel">
                                <?= csrf_field() ?>
                                <div class="form-row"><label>Warna</label><input type="color" name="color" value="<?= esc($s['color']) ?>" style="width:44px; height:38px; border:1px solid var(--line); border-radius:8px; padding:2px;"></div>
                                <div class="form-row" style="flex:1 1 180px;"><label>Nama</label><input type="text" name="name" class="form-control" required value="<?= esc($s['name']) ?>" style="width:100%;"></div>
                                <div class="form-row"><label>Jenis</label>
                                    <select name="kind" class="form-control">
                                        <?php foreach ($kinds as $k => $lbl): ?><option value="<?= $k ?>" <?= $s['kind'] === $k ? 'selected' : '' ?>><?= $lbl ?></option><?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-row"><label>Urutan</label><input type="number" name="sort_order" class="form-control" style="width:70px;" value="<?= (int) $s['sort_order'] ?>"></div>
                                <input type="hidden" name="is_active" value="0">
                                <label style="display:flex; gap:6px; align-items:center; font-size:13px; padding-bottom:9px;"><input type="checkbox" name="is_active" value="1" <?= $s['is_active'] ? 'checked' : '' ?>> Aktif</label>
                                <button type="submit" class="btn btn-primary btn-sm"><?= icon('check') ?> Simpan</button>
                                <button type="button" class="btn btn-ghost btn-sm" data-cancel-edit="<?= $s['id'] ?>">Batal</button>
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
            <div class="form-row"><label for="name">Nama Tahap <span class="req">*</span></label><input type="text" id="name" name="name" class="form-control" required value="<?= esc(old('name')) ?>" placeholder="mis. Medical Check Up"></div>
            <div class="form-row"><label for="kind">Jenis</label><select id="kind" name="kind" class="form-control"><?php foreach ($kinds as $k => $lbl): ?><option value="<?= $k ?>"><?= $lbl ?></option><?php endforeach; ?></select></div>
            <div class="form-row"><label for="color">Warna</label><input type="color" id="color" name="color" class="form-control" value="#64748b" style="height:42px;"></div>
            <button type="submit" class="btn btn-primary" style="width:100%; justify-content:center;"><?= icon('plus') ?> Tambah</button>
        </form>
    </div>
</div>
<?= $this->endSection() ?>
