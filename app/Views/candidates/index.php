<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-head">
    <div><h1 class="page-title"><span class="chip"><?= icon('user') ?></span> Bank Talenta</h1>
    <p class="page-sub"><?= count($candidates) ?> kandidat pernah melamar</p></div>
</div>
<div class="card" style="margin-bottom:18px;">
    <form method="get" action="<?= site_url('kandidat') ?>" style="display:flex; gap:10px;">
        <input type="search" name="q" class="form-control" value="<?= esc($q) ?>" placeholder="Cari nama, email, telepon, sekolah, atau posisi">
        <button type="submit" class="btn btn-primary"><?= icon('search') ?> Cari</button>
        <?php if ($q !== ''): ?><a href="<?= site_url('kandidat') ?>" class="btn btn-ghost">Reset</a><?php endif; ?>
    </form>
</div>
<div class="table-wrap">
    <?php if ($candidates === []): ?>
        <?= empty_state('user', 'Belum ada kandidat', $q !== '' ? 'Tidak ada yang cocok dengan pencarian.' : 'Bank talenta terisi otomatis dari setiap orang yang melamar.') ?>
    <?php else: ?>
        <table class="data roomy">
            <thead><tr><th>Nama</th><th>Kontak</th><th>Pendidikan</th><th class="num">Lamaran</th><th>Status</th></tr></thead>
            <tbody>
                <?php foreach ($candidates as $c): ?>
                    <tr data-href="<?= site_url('kandidat/' . $c['id']) ?>" tabindex="0" role="link">
                        <td>
                            <div style="display:flex; align-items:center; gap:10px;">
                                <span class="avatar" style="width:32px;height:32px;flex:0 0 32px;font-size:11px;"><?= esc(initials($c['full_name'])) ?></span>
                                <span><b><?= esc($c['full_name']) ?></b><br><small><?= esc($c['current_position'] ?: '—') ?></small></span>
                            </div>
                        </td>
                        <td><small><?= esc($c['email']) ?><br><?= esc($c['phone']) ?></small></td>
                        <td><small><?= esc(trim(($c['education_level'] ?? '') . ' ' . ($c['school'] ?? ''))) ?: '—' ?></small></td>
                        <td class="num"><?= (int) $c['application_count'] ?></td>
                        <td><?= $c['is_blacklisted'] ? '<span class="badge badge-red">Blacklist</span>' : '<span class="badge badge-green">Normal</span>' ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
<?= $this->endSection() ?>
