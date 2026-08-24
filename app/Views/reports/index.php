<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-head">
    <div><h1 class="page-title"><span class="chip"><?= icon('chart') ?></span> Laporan</h1>
    <p class="page-sub">Tahun <?= date('Y') ?> · <?= esc($auth['unit_name']) ?></p></div>
</div>

<div class="grid grid-4" style="margin-bottom:22px;">
    <div class="card is-blue"><div class="stat-label">Lamaran Masuk</div><div class="stat-value"><?= $summary['masuk'] ?></div><div class="stat-hint">tahun berjalan</div></div>
    <div class="card"><div class="stat-label">Sedang Aktif</div><div class="stat-value"><?= $summary['aktif'] ?></div><div class="stat-hint">di pipeline</div></div>
    <div class="card"><div class="stat-label">Diterima</div><div class="stat-value" style="color:var(--green);"><?= $summary['diterima'] ?></div><div class="stat-hint">tahun berjalan</div></div>
    <div class="card"><div class="stat-label">Tidak Lolos</div><div class="stat-value" style="color:var(--red);"><?= $summary['ditolak'] ?></div><div class="stat-hint">tahun berjalan</div></div>
</div>

<div class="section-title">Corong Rekrutmen</div>
<div class="card" style="margin-bottom:22px;">
    <?php
    $maks = max(1, max(array_map('intval', $funnel ?: [0])));
    foreach ($stages as $st):
        $n = (int) ($funnel[$st['id']] ?? 0);
        $pct = round($n / $maks * 100);
    ?>
        <div style="display:flex; align-items:center; gap:12px; margin-bottom:10px;">
            <div style="flex:0 0 150px; font-size:13px; text-align:right; color:var(--ink-soft);"><?= esc($st['name']) ?></div>
            <div style="flex:1; background:var(--muted); border-radius:8px; overflow:hidden; height:26px; position:relative;">
                <div style="width:<?= max($pct, $n > 0 ? 4 : 0) ?>%; height:100%; background:<?= esc($st['color']) ?>; border-radius:8px;"></div>
            </div>
            <div style="flex:0 0 44px; font-weight:700; font-size:14px;"><?= $n ?></div>
        </div>
    <?php endforeach; ?>
    <p style="font-size:11.5px; color:var(--ink-faint); margin:8px 0 0;">Jumlah lamaran yang pernah mencapai tiap tahap.</p>
</div>

<div class="grid" style="grid-template-columns: minmax(0,1fr) minmax(0,1fr); align-items:start;">
    <div>
        <div class="section-title" style="margin-top:0;">Rata-rata Lama di Tahap</div>
        <div class="table-wrap">
            <?php if ($avgDays === []): ?>
                <div class="empty"><?= icon('clock') ?><div>Belum cukup data perpindahan.</div></div>
            <?php else: ?>
                <table class="data"><thead><tr><th>Tahap</th><th class="num">Rata-rata</th><th class="num">Perpindahan</th></tr></thead>
                <tbody>
                    <?php foreach ($avgDays as $r): ?>
                        <tr><td><?= esc($r['stage_name']) ?></td>
                            <td class="num"><?= $r['avg_days'] !== null ? number_format((float) $r['avg_days'], 1) . ' hari' : '—' ?></td>
                            <td class="num"><?= (int) $r['moves'] ?></td></tr>
                    <?php endforeach; ?>
                </tbody></table>
            <?php endif; ?>
        </div>
    </div>
    <div>
        <div class="section-title" style="margin-top:0;">Per Lowongan</div>
        <div class="table-wrap">
            <table class="data"><thead><tr><th>Lowongan</th><th class="num">Butuh</th><th class="num">Pelamar</th><th class="num">Diterima</th></tr></thead>
            <tbody>
                <?php foreach ($perJob as $j): ?>
                    <tr>
                        <td><?= esc($j['title']) ?><br><small><?= esc($j['unit_name']) ?></small></td>
                        <td class="num"><?= (int) $j['openings'] ?></td>
                        <td class="num"><?= (int) $j['pelamar'] ?></td>
                        <td class="num"><?= (int) $j['diterima'] ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody></table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
