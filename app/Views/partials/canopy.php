<?php
/**
 * Kanopi atap dari logo WBL, digambar ulang sebagai garis SVG.
 *
 * Geometrinya diukur dari public/assets/img/logo-wbl.png (768 × 480): puncak di
 * (396, 10), sebelas garis memancar dengan kemiringan di bawah, empat pita
 * melintang, dan lis atap. Karena koordinatnya sama dengan PNG, versi 'logo'
 * bisa ditumpuk tepat di atas gambar logo (animasi loading).
 *
 * Setiap garis dipecah dari puncak/tengah ke luar dan diberi pathLength="1",
 * sehingga CSS bisa menggambar semuanya bersamaan dalam satu sapuan.
 *
 * @var string $variant 'logo' (kotak sama dengan PNG) | 'mini' (atapnya saja) | 'hero' (dibentangkan ke bawah)
 */
$variant = $variant ?? 'logo';
$ax = 396;
$ay = 10;
$slopes = [-3.118, -2.173, -1.518, -0.936, -0.391, 0, 0.409, 0.918, 1.4545, 2.0, 2.918];
$xAt = static fn (float $s, float $y): float => round($ax + $s * ($y - $ay), 1);

$lines = [];
if ($variant !== 'hero') {
    foreach ($slopes as $i => $s) {
        if ($i === 0) {
            $lines[] = [$ax, $ay, 0, 137];
        } elseif ($i === count($slopes) - 1) {
            $lines[] = [$ax, $ay, 767, 137.1];
        } else {
            $lines[] = [$ax, $ay, $xAt($s, 128.5), 128.5];
        }
    }
    $bands = [20, 37.5, 67, 96];
    // Lis atap: datar di pinggir, naik miring, lalu datar di tengah.
    $eaves = [
        [[396, 128.5], [136, 128.5], [117, 140], [0, 140]],
        [[396, 128.5], [636, 128.5], [655, 140], [767, 140]],
    ];
    $viewBox = $variant === 'mini' ? '0 0 768 145' : '0 0 768 480';
} else {
    $bottom = 360;
    foreach ($slopes as $s) {
        $lines[] = [$ax, $ay, $xAt($s, $bottom), $bottom];
    }
    // Jarak pita membesar ke bawah, meneruskan perspektif di logo.
    $bands = [20, 37.5, 67, 96, 128.5, 166, 208, 256, 310];
    $eaves = [];
    $viewBox = '36 0 720 330';
}

$segments = '';
foreach ($lines as [$x1, $y1, $x2, $y2]) {
    $segments .= "<line class=\"cl-ray\" x1=\"$x1\" y1=\"$y1\" x2=\"$x2\" y2=\"$y2\" pathLength=\"1\"/>";
}
foreach ($bands as $y) {
    $l = $xAt($slopes[0], $y);
    $r = $xAt($slopes[count($slopes) - 1], $y);
    $segments .= "<line class=\"cl-band\" x1=\"$ax\" y1=\"$y\" x2=\"$l\" y2=\"$y\" pathLength=\"1\"/>"
        . "<line class=\"cl-band\" x1=\"$ax\" y1=\"$y\" x2=\"$r\" y2=\"$y\" pathLength=\"1\"/>";
}
foreach ($eaves as $pts) {
    $segments .= '<polyline class="cl-band" pathLength="1" points="'
        . implode(' ', array_map(static fn ($p) => $p[0] . ',' . $p[1], $pts)) . '"/>';
}
?>
<svg class="canopy canopy-<?= $variant ?>" viewBox="<?= $viewBox ?>" preserveAspectRatio="<?= $variant === 'hero' ? 'xMidYMin slice' : 'xMidYMid meet' ?>" aria-hidden="true" focusable="false">
    <?php if ($variant === 'hero'): ?>
        <defs>
            <!-- Cahaya emas yang berdenyut dari puncak atap ke tepi. -->
            <radialGradient id="canopyGlow" gradientUnits="userSpaceOnUse" cx="<?= $ax ?>" cy="<?= $ay ?>" r="420">
                <stop offset="0" stop-color="#fff" stop-opacity="0"/>
                <stop offset=".55" stop-color="#fff" stop-opacity="0">
                    <animate attributeName="offset" values="0;.92" dur="6s" repeatCount="indefinite"/>
                </stop>
                <stop offset=".62" stop-color="#fff" stop-opacity="1">
                    <animate attributeName="offset" values=".07;.99" dur="6s" repeatCount="indefinite"/>
                </stop>
                <stop offset=".7" stop-color="#fff" stop-opacity="0">
                    <animate attributeName="offset" values=".14;1" dur="6s" repeatCount="indefinite"/>
                </stop>
            </radialGradient>
            <mask id="canopyMask" maskUnits="userSpaceOnUse" x="-2000" y="-100" width="5000" height="800">
                <rect x="-2000" y="-100" width="5000" height="800" fill="url(#canopyGlow)"/>
            </mask>
        </defs>
        <g class="cl-base"><?= $segments ?></g>
        <g class="cl-glow" mask="url(#canopyMask)"><?= $segments ?></g>
    <?php else: ?>
        <g class="cl-base"><?= $segments ?></g>
    <?php endif; ?>
</svg>
