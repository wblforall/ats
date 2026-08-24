<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-head">
    <div>
        <a href="<?= site_url('master/pengguna') ?>" style="font-size:13px; color:var(--ink-soft);">← Semua pengguna</a>
        <h1 class="page-title" style="margin-top:8px;"><span class="chip"><?= icon('shield') ?></span> <?= $user ? 'Ubah Pengguna' : 'Pengguna Baru' ?></h1>
    </div>
</div>
<form method="post" action="<?= $user ? site_url('master/pengguna/' . $user['id'] . '/ubah') : site_url('master/pengguna') ?>">
    <?= csrf_field() ?>
    <div class="card" style="max-width:620px;">
        <div class="grid grid-2">
            <div class="form-row">
                <label for="name">Nama <span class="req">*</span></label>
                <input type="text" id="name" name="name" class="form-control" required value="<?= esc(old('name', $user['name'] ?? '')) ?>">
            </div>
            <div class="form-row">
                <label for="email">Email <span class="req">*</span></label>
                <input type="email" id="email" name="email" class="form-control" required value="<?= esc(old('email', $user['email'] ?? '')) ?>">
            </div>
            <div class="form-row">
                <label for="role">Peran <span class="req">*</span></label>
                <select id="role" name="role" class="form-control" required>
                    <?php foreach ($roles as $key => $label): ?>
                        <option value="<?= $key ?>" <?= old('role', $user['role'] ?? 'hr_staff') === $key ? 'selected' : '' ?>><?= $label ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-row">
                <label for="unit_id">Unit</label>
                <select id="unit_id" name="unit_id" class="form-control">
                    <option value="">— Semua unit (khusus superadmin) —</option>
                    <?php foreach ($units as $unit): ?>
                        <option value="<?= $unit['id'] ?>" <?= (int) old('unit_id', $user['unit_id'] ?? 0) === (int) $unit['id'] ? 'selected' : '' ?>><?= esc($unit['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-row">
                <label for="position">Jabatan</label>
                <input type="text" id="position" name="position" class="form-control" value="<?= esc(old('position', $user['position'] ?? '')) ?>">
            </div>
            <div class="form-row">
                <label for="phone">Telepon</label>
                <input type="text" id="phone" name="phone" class="form-control" value="<?= esc(old('phone', $user['phone'] ?? '')) ?>">
            </div>
            <div class="form-row">
                <label for="password">Kata Sandi <?= $user ? '' : '<span class="req">*</span>' ?></label>
                <input type="password" id="password" name="password" class="form-control" <?= $user ? '' : 'required' ?> autocomplete="new-password">
                <?php if ($user): ?><div class="hint">Kosongkan bila tidak ingin mengubah sandi.</div><?php endif; ?>
            </div>
            <div class="form-row" style="display:flex; align-items:flex-end;">
                <label style="display:flex; gap:8px; align-items:center; font-size:13px;">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" <?= old('is_active', $user['is_active'] ?? 1) ? 'checked' : '' ?>> Akun aktif
                </label>
            </div>
        </div>
        <button type="submit" class="btn btn-primary" style="margin-top:8px;"><?= icon('check') ?> <?= $user ? 'Simpan' : 'Buat Pengguna' ?></button>
    </div>
</form>
<?= $this->endSection() ?>
