<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-head">
    <div>
        <h1 class="page-title"><span class="chip"><?= icon('star') ?></span> Kandidat Unggulan</h1>
        <p class="page-sub"><?= count($rows) ?> pelamar aktif, diurutkan menurut skor potensi</p>
    </div>
</div>

<div class="alert alert-info">
    <?= icon('info') ?>
    <span>Skor potensi menggabungkan performa interview, rekomendasi pewawancara, rating HR, pengalaman, dan pendidikan. Pelamar tanpa penilaian ditandai <b>Awal</b> dan diletakkan setelah yang sudah dinilai.</span>
</div>

<div class="card" style="margin-bottom:18px;">
    <form method="get" action="<?= site_url('unggulan') ?>" style="display:flex; gap:10px; flex-wrap:wrap; align-items:flex-end;">
        <div class="form-row" style="margin:0; flex:0 1 240px;">
            <label for="job">Lowongan</label>
            <select id="job" name="job" class="form-control">
                <option value="">Semua lowongan</option>
                <?php foreach ($jobs as $j): ?>
                    <option value="<?= $j['id'] ?>" <?= (int) $filters['job'] === (int) $j['id'] ? 'selected' : '' ?>><?= esc($j['title']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-row" style="margin:0; flex:0 1 170px;">
            <label for="band">Tingkat</label>
            <select id="band" name="band" class="form-control">
                <option value="">Semua</option>
                <option value="high"   <?= $filters['band']==='high'?'selected':'' ?>>Tinggi</option>
                <option value="mid"    <?= $filters['band']==='mid'?'selected':'' ?>>Sedang</option>
                <option value="low"    <?= $filters['band']==='low'?'selected':'' ?>>Rendah</option>
                <option value="prelim" <?= $filters['band']==='prelim'?'selected':'' ?>>Awal</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary"><?= icon('search') ?> Terapkan</button>
        <?php if (array_filter($filters)): ?><a href="<?= site_url('unggulan') ?>" class="btn btn-ghost">Reset</a><?php endif; ?>
    </form>
</div>

<div class="table-wrap">
    <?php if ($rows === []): ?>
        <?= empty_state('star', 'Belum ada pelamar aktif', 'Kandidat unggulan muncul begitu ada lamaran yang berjalan.') ?>
    <?php else: ?>
        <table class="data roomy">
            <thead><tr><th style="width:44px;">#</th><th>Potensi</th><th>Nama</th><th>Lowongan</th><th>Tahap</th><th class="num">Interview</th></tr></thead>
            <tbody>
                <?php foreach ($rows as $i => $row): $s = $row['_score']; ?>
                    <tr data-href="<?= site_url('pelamar/' . $row['id']) ?>" tabindex="0" role="link">
                        <td style="color:var(--ink-faint); font-weight:700;"><?= $i + 1 ?></td>
                        <td><?= potential_badge($s['score'], $s['band']) ?></td>
                        <td>
                            <div style="display:flex; align-items:center; gap:10px;">
                                <span class="avatar" style="width:30px;height:30px;flex:0 0 30px;font-size:11px;"><?= esc(initials($row['full_name'])) ?></span>
                                <span><b><?= esc($row['full_name']) ?></b><br><small><?= esc($row['education_level'] ?: '—') ?> · <?= $row['years_experience'] !== null ? rtrim(rtrim(number_format((float)$row['years_experience'],1),'0'),'.,').' th' : '—' ?></small></span>
                            </div>
                        </td>
                        <td><?= esc($row['job_title']) ?><br><small><?= esc($row['unit_name']) ?></small></td>
                        <td><span class="badge" style="background:<?= esc($row['stage_color']) ?>1F; color:<?= esc($row['stage_color']) ?>;"><span class="dot"></span><?= esc($row['stage_name']) ?></span></td>
                        <td class="num"><?= $row['avg_score'] !== null ? number_format((float)$row['avg_score'],1) : '—' ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
<?= $this->endSection() ?>
