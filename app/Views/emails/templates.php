<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-head">
    <div>
        <a href="<?= site_url('email') ?>" style="font-size:13px; color:var(--ink-soft);">← Antrean email</a>
        <h1 class="page-title" style="margin-top:8px;"><span class="chip"><?= icon('edit') ?></span> Template Email</h1>
        <p class="page-sub">Isi memakai placeholder seperti {{nama_kandidat}}, {{posisi}}, dll.</p>
    </div>
</div>
<div class="table-wrap">
    <table class="data roomy">
        <thead><tr><th>Nama</th><th>Subjek</th><th>Kirim Otomatis</th><th>Status</th><th></th></tr></thead>
        <tbody>
            <?php foreach ($templates as $t): ?>
                <tr>
                    <td><b><?= esc($t['name']) ?></b><br><small><?= esc($t['code']) ?></small></td>
                    <td><small><?= esc($t['subject']) ?></small></td>
                    <td><?= $t['auto_send'] ? '<span class="badge badge-blue">Ya</span>' : '<span class="badge badge-gray">Manual</span>' ?></td>
                    <td><?= $t['is_active'] ? '<span class="badge badge-green">Aktif</span>' : '<span class="badge badge-gray">Nonaktif</span>' ?></td>
                    <td style="text-align:right;"><a href="<?= site_url('email/template/' . $t['id']) ?>" class="btn btn-ghost btn-sm"><?= icon('edit') ?> Ubah</a></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?= $this->endSection() ?>
