<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-head">
    <div><h1 class="page-title"><span class="chip"><?= icon('user') ?></span> Profil Saya</h1>
    <p class="page-sub"><?= esc(\App\Controllers\BaseController::ROLE_LABELS[$me['role']] ?? $me['role']) ?> · <?= esc($auth['unit_name']) ?></p></div>
</div>
<form method="post" action="<?= site_url('profil') ?>">
    <?= csrf_field() ?>
    <div class="card" style="max-width:560px;">
        <div class="form-row">
            <label for="name">Nama</label>
            <input type="text" id="name" name="name" class="form-control" required value="<?= esc($me['name']) ?>">
        </div>
        <div class="form-row">
            <label>Email</label>
            <input type="text" class="form-control" value="<?= esc($me['email']) ?>" disabled>
            <div class="hint">Email hanya bisa diubah oleh Admin HRD.</div>
        </div>
        <div class="grid grid-2">
            <div class="form-row">
                <label for="position">Jabatan</label>
                <input type="text" id="position" name="position" class="form-control" value="<?= esc($me['position']) ?>">
            </div>
            <div class="form-row">
                <label for="phone">Telepon</label>
                <input type="text" id="phone" name="phone" class="form-control" value="<?= esc($me['phone']) ?>">
            </div>
        </div>
        <div class="section-title">Ubah Kata Sandi</div>
        <div class="grid grid-2">
            <div class="form-row">
                <label for="password">Kata Sandi Baru</label>
                <input type="password" id="password" name="password" class="form-control" autocomplete="new-password">
            </div>
            <div class="form-row">
                <label for="password_confirm">Ulangi Sandi</label>
                <input type="password" id="password_confirm" name="password_confirm" class="form-control" autocomplete="new-password">
            </div>
        </div>
        <div class="hint" style="margin-bottom:12px;">Kosongkan bila tidak ingin mengubah sandi.</div>
        <button type="submit" class="btn btn-primary"><?= icon('check') ?> Simpan Profil</button>
    </div>
</form>
<?= $this->endSection() ?>
