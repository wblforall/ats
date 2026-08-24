<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-head">
    <div>
        <h1 class="page-title"><span class="chip"><?= icon('search') ?></span> Pencarian</h1>
        <p class="page-sub"><?= $q === '' ? 'Ketik kata kunci di kotak pencarian.' : 'Hasil untuk "' . esc($q) . '"' ?></p>
    </div>
</div>

<?php if ($q !== '' && mb_strlen($q) < 2): ?>
    <div class="alert alert-info"><?= icon('info') ?><span>Kata kunci minimal 2 huruf.</span></div>
<?php elseif ($q !== '' && $pelamar === [] && $lowongan === [] && $kandidat === []): ?>
    <div class="empty" style="background:#fff; border:1px solid var(--line); border-radius:var(--r-panel);">
        <?= icon('search') ?><div><b>Tidak ada hasil.</b></div>
    </div>
<?php endif; ?>

<?php if ($pelamar !== []): ?>
    <div class="section-title" style="margin-top:0;">Pelamar (<?= count($pelamar) ?>)</div>
    <div class="table-wrap" style="margin-bottom:22px;">
        <table class="data roomy"><thead><tr><th>Nama</th><th>Lowongan</th><th>Tahap</th></tr></thead><tbody>
            <?php foreach ($pelamar as $r): ?>
                <tr data-href="<?= site_url('pelamar/' . $r['id']) ?>" tabindex="0" role="link">
                    <td><b><?= esc($r['full_name']) ?></b><br><small><?= esc($r['email']) ?></small></td>
                    <td><?= esc($r['job_title']) ?><br><small><?= esc($r['unit_name']) ?></small></td>
                    <td><span class="badge" style="background:<?= esc($r['stage_color']) ?>1F; color:<?= esc($r['stage_color']) ?>;"><span class="dot"></span><?= esc($r['stage_name']) ?></span></td>
                </tr>
            <?php endforeach; ?>
        </tbody></table>
    </div>
<?php endif; ?>

<?php if ($lowongan !== []): ?>
    <div class="section-title">Lowongan (<?= count($lowongan) ?>)</div>
    <div class="table-wrap" style="margin-bottom:22px;">
        <table class="data roomy"><thead><tr><th>Posisi</th><th>Unit</th><th class="num">Pelamar</th></tr></thead><tbody>
            <?php foreach ($lowongan as $j): ?>
                <tr data-href="<?= site_url('lowongan/' . $j['id']) ?>" tabindex="0" role="link">
                    <td><b><?= esc($j['title']) ?></b><br><small><?= esc($j['code']) ?></small></td>
                    <td><small><?= esc($j['unit_name']) ?></small></td>
                    <td class="num"><?= (int) $j['applicant_count'] ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody></table>
    </div>
<?php endif; ?>

<?php if ($kandidat !== []): ?>
    <div class="section-title">Kandidat (<?= count($kandidat) ?>)</div>
    <div class="table-wrap">
        <table class="data roomy"><thead><tr><th>Nama</th><th>Kontak</th><th class="num">Lamaran</th></tr></thead><tbody>
            <?php foreach ($kandidat as $c): ?>
                <tr data-href="<?= site_url('kandidat/' . $c['id']) ?>" tabindex="0" role="link">
                    <td><b><?= esc($c['full_name']) ?></b></td>
                    <td><small><?= esc($c['email']) ?> · <?= esc($c['phone']) ?></small></td>
                    <td class="num"><?= (int) $c['application_count'] ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody></table>
    </div>
<?php endif; ?>
<?= $this->endSection() ?>
