<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="page-head">
    <div>
        <a href="<?= site_url('pelamar') ?>" style="font-size:13px; color:var(--ink-soft);">← Semua pelamar</a>
        <h1 class="page-title" style="margin-top:8px;">
            <span class="chip"><?= icon('plus') ?></span>
            Input Pelamar
        </h1>
        <p class="page-sub">Untuk pelamar yang datang langsung atau menyerahkan berkas ke HRD.</p>
    </div>
</div>

<div class="alert alert-info" style="margin-bottom:18px;"><?= icon('info') ?><span>
    Pilih lowongan, lalu isi formulir lamaran atas nama pelamar — sama dengan formulir di portal karir, termasuk unggah CV.
    Setelah dikirim, lamaran langsung masuk ke tahap pertama pipeline dan nomor lamaran tampil di layar
    (juga dikirim ke email pelamar bila konfirmasi otomatis menyala di Pengaturan).
</span></div>

<div class="table-wrap">
    <?php if ($jobs === []): ?>
        <?= empty_state('briefcase', 'Belum ada lowongan yang tayang',
            'Pelamar hanya bisa diinput ke lowongan berstatus Dibuka. Buka atau buat lowongan dulu.',
            'Buat Lowongan', site_url('lowongan/baru')) ?>
    <?php else: ?>
        <table class="data roomy">
            <thead>
                <tr>
                    <th>Posisi</th>
                    <th>Unit</th>
                    <th>Jenis</th>
                    <th>Ditutup</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($jobs as $job): ?>
                    <tr>
                        <td><b><?= esc($job['title']) ?></b><br><small><?= esc($job['code']) ?><?= $job['department'] ? ' · ' . esc($job['department']) : '' ?></small></td>
                        <td><?= esc($job['unit_name']) ?></td>
                        <td><?= \App\Models\JobModel::EMPLOYMENT_TYPES[$job['employment_type']] ?? esc($job['employment_type']) ?></td>
                        <td><?= $job['closing_date'] ? tgl($job['closing_date']) : '—' ?></td>
                        <td style="text-align:right;">
                            <a href="<?= site_url('karir/' . $job['slug'] . '/lamar') ?>" class="btn btn-primary btn-sm"><?= icon('edit') ?> Isi Formulir</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?= $this->endSection() ?>
