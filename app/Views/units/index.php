<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="page-head">
    <div>
        <h1 class="page-title">
            <span class="chip"><?= icon('building') ?></span>
            Unit Usaha
        </h1>
        <p class="page-sub"><?= count($units) ?> unit · akar pembatasan akses seluruh aplikasi</p>
    </div>
</div>

<div class="alert alert-info">
    <?= icon('info') ?>
    <span>
        Kode unit tidak bisa diubah setelah dibuat karena menjadi awalan kode lowongan
        yang sudah beredar (mis. <b>PTC-2608-001</b>). Unit yang masih dipakai hanya bisa
        dinonaktifkan, tidak dihapus.
    </span>
</div>

<div class="grid" style="grid-template-columns: minmax(0, 1fr) minmax(0, 320px); align-items:start;">
    <div class="table-wrap">
        <table class="data roomy">
            <thead>
                <tr>
                    <th>Kode</th>
                    <th>Nama Unit</th>
                    <th class="num">Dept</th>
                    <th class="num">Lowongan</th>
                    <th class="num">Pengguna</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($units as $unit): ?>
                    <tr>
                        <td><span class="badge badge-blue"><?= esc($unit['code']) ?></span></td>
                        <td>
                            <form method="post" action="<?= site_url('master/unit/' . $unit['id'] . '/ubah') ?>" class="row-form">
                                <?= csrf_field() ?>
                                <input type="text" name="name" class="form-control" required value="<?= esc($unit['name']) ?>">
                                <input type="text" name="address" class="form-control" style="flex:0 1 130px;"
                                       value="<?= esc($unit['address']) ?>" placeholder="Kota">
                                <input type="number" name="sort_order" class="form-control" style="flex:0 0 62px;"
                                       value="<?= (int) $unit['sort_order'] ?>" title="Urutan tampil">
                                <input type="hidden" name="is_active" value="0">
                                <label>
                                    <input type="checkbox" name="is_active" value="1" <?= $unit['is_active'] ? 'checked' : '' ?>>
                                    aktif
                                </label>
                                <button type="submit" class="btn btn-ghost btn-sm"><?= icon('check') ?> Simpan</button>
                            </form>
                        </td>
                        <td class="num"><?= (int) $unit['department_count'] ?></td>
                        <td class="num"><?= (int) $unit['job_count'] ?></td>
                        <td class="num"><?= (int) $unit['user_count'] ?></td>
                        <td>
                            <?php if ($unit['is_active']): ?>
                                <span class="badge badge-green">Aktif</span>
                            <?php else: ?>
                                <span class="badge badge-gray">Nonaktif</span>
                            <?php endif; ?>
                        </td>
                        <td style="text-align:right;">
                            <form method="post" action="<?= site_url('master/unit/' . $unit['id'] . '/hapus') ?>">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-ghost btn-sm"
                                        data-confirm="Hapus unit &quot;<?= esc($unit['name'], 'attr') ?>&quot;? Unit yang masih dipakai akan dinonaktifkan saja.">
                                    <?= icon('trash') ?>
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="card">
        <div class="section-title" style="margin-top:0;">Tambah Unit</div>

        <form method="post" action="<?= site_url('master/unit') ?>">
            <?= csrf_field() ?>

            <div class="form-row">
                <label for="code">Kode <span class="req">*</span></label>
                <input type="text" id="code" name="code" class="form-control" required maxlength="20"
                       value="<?= esc(old('code')) ?>" placeholder="mis. PTC" style="text-transform:uppercase;">
                <div class="hint">Dipakai sebagai awalan kode lowongan. Tidak bisa diubah nanti.</div>
            </div>

            <div class="form-row">
                <label for="name">Nama Unit <span class="req">*</span></label>
                <input type="text" id="name" name="name" class="form-control" required
                       value="<?= esc(old('name')) ?>" placeholder="mis. Pentacity Shopping Venue">
            </div>

            <div class="form-row">
                <label for="address">Kota / Alamat</label>
                <input type="text" id="address" name="address" class="form-control" value="<?= esc(old('address')) ?>">
            </div>

            <div class="form-row">
                <label for="sort_order">Urutan Tampil</label>
                <input type="number" id="sort_order" name="sort_order" class="form-control"
                       value="<?= esc(old('sort_order', count($units) + 1)) ?>" min="0">
            </div>

            <button type="submit" class="btn btn-primary" style="width:100%; justify-content:center;">
                <?= icon('plus') ?> Tambah
            </button>
        </form>
    </div>
</div>

<?= $this->endSection() ?>
