<?php

/**
 * Helper tampilan: ikon SVG sebaris dan format Indonesia.
 * Ikon ditanam langsung (bukan dari CDN) supaya aplikasi tetap tampil normal
 * di jaringan internal yang tidak punya akses keluar.
 */

if (! function_exists('icon')) {
    /** Ikon garis 24x24 (stroke mengikuti currentColor). */
    function icon(string $name, string $class = ''): string
    {
        $paths = [
            'dashboard'  => '<rect x="3" y="3" width="7" height="9" rx="1.5"/><rect x="14" y="3" width="7" height="5" rx="1.5"/><rect x="14" y="12" width="7" height="9" rx="1.5"/><rect x="3" y="16" width="7" height="5" rx="1.5"/>',
            'briefcase'  => '<rect x="2" y="7" width="20" height="14" rx="2"/><path d="M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><path d="M2 13h20"/>',
            'users'      => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
            'pipeline'   => '<rect x="3" y="4" width="5" height="16" rx="1.5"/><rect x="10" y="4" width="5" height="11" rx="1.5"/><rect x="17" y="4" width="4" height="7" rx="1.5"/>',
            'calendar'   => '<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M16 3v4M8 3v4M3 11h18"/>',
            'mail'       => '<rect x="2" y="4" width="20" height="16" rx="2"/><path d="m2 7 10 6 10-6"/>',
            'chart'      => '<path d="M3 3v18h18"/><path d="m7 14 3-4 3 3 5-7"/>',
            'building'   => '<rect x="4" y="3" width="16" height="18" rx="2"/><path d="M9 8h.01M15 8h.01M9 12h.01M15 12h.01M9 16h.01M15 16h.01"/>',
            'layers'     => '<path d="m12 2 9 5-9 5-9-5 9-5Z"/><path d="m3 12 9 5 9-5"/><path d="m3 17 9 5 9-5"/>',
            'shield'     => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"/>',
            'settings'   => '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 1 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.6 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 1 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.6h.09A1.65 1.65 0 0 0 10 3.09V3a2 2 0 1 1 4 0v.09A1.65 1.65 0 0 0 15 4.6a1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9v.09a1.65 1.65 0 0 0 1.51 1H21a2 2 0 1 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1Z"/>',
            'search'     => '<circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/>',
            'bell'       => '<path d="M18 8a6 6 0 1 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.7 21a2 2 0 0 1-3.4 0"/>',
            'help'       => '<circle cx="12" cy="12" r="9"/><path d="M9.1 9a3 3 0 0 1 5.8 1c0 2-3 2.5-3 4"/><path d="M12 17h.01"/>',
            'plus'       => '<path d="M12 5v14M5 12h14"/>',
            'logout'     => '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="m16 17 5-5-5-5"/><path d="M21 12H9"/>',
            'user'       => '<circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/>',
            'menu'       => '<path d="M4 6h16M4 12h16M4 18h16"/>',
            'check'      => '<path d="m4 12 5 5L20 6"/>',
            'x'          => '<path d="M18 6 6 18M6 6l12 12"/>',
            'alert'      => '<circle cx="12" cy="12" r="9"/><path d="M12 8v5M12 16h.01"/>',
            'info'       => '<circle cx="12" cy="12" r="9"/><path d="M12 11v5M12 8h.01"/>',
            'edit'       => '<path d="M11 4H5a2 2 0 0 0-2 2v13a2 2 0 0 0 2 2h13a2 2 0 0 0 2-2v-6"/><path d="M18.5 2.5a2.12 2.12 0 0 1 3 3L12 15l-4 1 1-4Z"/>',
            'trash'      => '<path d="M3 6h18M8 6V4a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>',
            'eye'        => '<path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/>',
            'download'   => '<path d="M12 3v12"/><path d="m7 11 5 5 5-5"/><path d="M4 21h16"/>',
            'file'       => '<path d="M14 2H7a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7Z"/><path d="M14 2v5h5"/>',
            'star'       => '<path d="m12 3 2.7 5.6 6.3.9-4.5 4.4 1 6.1-5.5-2.9-5.5 2.9 1-6.1L3 9.5l6.3-.9Z"/>',
            'clock'      => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
            'arrow-right'=> '<path d="M5 12h14"/><path d="m13 6 6 6-6 6"/>',
            'chevron-down'=> '<path d="m6 9 6 6 6-6"/>',
        ];

        $body = $paths[$name] ?? $paths['info'];

        /* Kelas `ico` memberi ukuran dasar. Tanpa itu SVG tanpa lebar eksplisit
           akan memuai memenuhi wadah flex-nya. Aturan yang lebih spesifik di
           CSS (mis. `.nav-item svg`) tetap menang bila perlu ukuran lain. */
        $cls = trim('ico ' . $class);

        return '<svg class="' . esc($cls, 'attr') . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" '
            . 'stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $body . '</svg>';
    }
}

if (! function_exists('potential_badge')) {
    /**
     * Badge skor potensi kandidat. $band: high|mid|low|none.
     * Dipakai di kartu pipeline, daftar pelamar, dan halaman kandidat unggulan.
     */
    function potential_badge(?int $score, string $band, bool $compact = false): string
    {
        $cls = ['high' => 'pot-high', 'mid' => 'pot-mid', 'low' => 'pot-low',
                'prelim' => 'pot-prelim', 'none' => 'pot-none'][$band] ?? 'pot-none';
        $text = $score === null ? '—' : (string) $score;
        $label = ['high' => 'Tinggi', 'mid' => 'Sedang', 'low' => 'Rendah',
                  'prelim' => 'Awal', 'none' => 'Belum dinilai'][$band] ?? '';

        if ($compact) {
            return '<span class="pot ' . $cls . '" title="Skor potensi: ' . esc($label, 'attr') . '">' . esc($text) . '</span>';
        }

        return '<span class="pot ' . $cls . '">' . esc($text) . ' · ' . esc($label) . '</span>';
    }
}

if (! function_exists('initials')) {
    /** Inisial nama untuk avatar, maksimal dua huruf. */
    function initials(?string $name): string
    {
        $parts = preg_split('/\s+/', trim((string) $name)) ?: [];
        $parts = array_values(array_filter($parts));

        if ($parts === []) {
            return '?';
        }

        $first = mb_strtoupper(mb_substr($parts[0], 0, 1));
        $last  = count($parts) > 1 ? mb_strtoupper(mb_substr(end($parts), 0, 1)) : '';

        return $first . $last;
    }
}

if (! function_exists('rupiah')) {
    function rupiah(?float $value, bool $withPrefix = true): string
    {
        if ($value === null) {
            return '-';
        }

        return ($withPrefix ? 'Rp ' : '') . number_format($value, 0, ',', '.');
    }
}

if (! function_exists('nominal')) {
    /**
     * Siapkan nominal untuk ditaruh di input berpemisah ribuan.
     *
     * Sumbernya dua macam dan bentuknya berbeda:
     *   - dari basis data  : "4500000.00"  (titik = pemisah desimal)
     *   - isian ulang user : "4.500.000"   (titik = pemisah ribuan)
     * Keduanya dibedakan dari polanya — desimal hanya punya satu titik dengan
     * satu sampai dua angka di belakangnya. Tanpa pembedaan ini, ".00" dari
     * basis data ikut terbaca sebagai angka dan nilainya membengkak 100 kali
     * setiap kali lowongan disimpan ulang.
     */
    function nominal($value): string
    {
        $raw = trim((string) $value);

        if ($raw === '') {
            return '';
        }

        $digits = preg_match('/^\d+\.\d{1,2}$/', $raw)
            ? (string) (int) round((float) $raw)
            : (string) preg_replace('/\D/', '', $raw);

        return $digits === '' ? '' : number_format((int) $digits, 0, ',', '.');
    }
}

if (! function_exists('tgl')) {
    /** Tanggal Indonesia: 24 Agustus 2026 (opsional dengan jam). */
    function tgl(?string $datetime, bool $withTime = false): string
    {
        if (! $datetime) {
            return '-';
        }

        $bulan = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

        $ts   = strtotime($datetime);
        $text = date('j', $ts) . ' ' . $bulan[(int) date('n', $ts)] . ' ' . date('Y', $ts);

        return $withTime ? $text . ', ' . date('H:i', $ts) : $text;
    }
}

if (! function_exists('hari')) {
    /** Nama hari Indonesia. */
    function hari(?string $datetime): string
    {
        if (! $datetime) {
            return '-';
        }

        $nama = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

        return $nama[(int) date('w', strtotime($datetime))];
    }
}

if (! function_exists('sejak')) {
    /** Jarak waktu ringkas: "3 hari lalu", "baru saja". */
    function sejak(?string $datetime): string
    {
        if (! $datetime) {
            return '-';
        }

        $diff = time() - strtotime($datetime);

        return match (true) {
            $diff < 60        => 'baru saja',
            $diff < 3600      => intdiv($diff, 60) . ' menit lalu',
            $diff < 86400     => intdiv($diff, 3600) . ' jam lalu',
            $diff < 2592000   => intdiv($diff, 86400) . ' hari lalu',
            $diff < 31536000  => intdiv($diff, 2592000) . ' bulan lalu',
            default           => intdiv($diff, 31536000) . ' tahun lalu',
        };
    }
}
