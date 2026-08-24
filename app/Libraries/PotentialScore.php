<?php

namespace App\Libraries;

use App\Models\EvaluationModel;
use App\Models\SettingModel;

/**
 * Skor potensi kandidat — transparan & bisa ditelusuri, bukan black-box.
 *
 * Menggabungkan sinyal yang sudah dikumpulkan sistem menjadi angka 0–100:
 *   - performa interview (rata-rata skor evaluasi)
 *   - rekomendasi pewawancara (strong_yes … strong_no)
 *   - rating bintang dari HR
 *   - lama pengalaman kerja
 *   - jenjang pendidikan
 *
 * Yang penting: skor dihitung HANYA dari sinyal yang tersedia, lalu
 * dinormalkan. Pelamar baru yang belum diinterview dinilai adil berdasar
 * profilnya saja, tidak "jatuh" hanya karena belum ada nilai interview.
 * Bobot tiap komponen bisa diubah HR di Pengaturan.
 */
class PotentialScore
{
    /** Bobot default (jumlah 100). Ditimpa oleh pengaturan bila ada. */
    private const DEFAULT_WEIGHTS = [
        'interview'      => 40,
        'recommendation' => 25,
        'rating'         => 20,
        'experience'     => 10,
        'education'      => 5,
    ];

    private const EDU_RANK = [
        'SMA/SMK' => 1, 'D1' => 2, 'D2' => 2, 'D3' => 3,
        'D4' => 4, 'S1' => 4, 'S2' => 5, 'S3' => 5,
    ];

    private const REC_VALUE = [
        'strong_yes' => 5, 'yes' => 4, 'neutral' => 3, 'no' => 2, 'strong_no' => 1,
    ];

    /**
     * Hitung skor untuk satu lamaran (baris dari ApplicationModel::detailed()).
     *
     * @return array{score:int|null, band:string, label:string, signals:int,
     *               total:int, breakdown:array}
     */
    public function forApplication(array $app): array
    {
        $weights = $this->weights();

        /* Tiap komponen: [nilai 0..1 atau null bila tak ada sinyal]. */
        $components = [
            'interview'      => $this->normInterview($app),
            'recommendation' => $this->normRecommendation($app),
            'rating'         => $this->norm5($app['rating'] ?? null),
            'experience'     => $this->normExperience($app['years_experience'] ?? null),
            'education'      => $this->normEducation($app['education_level'] ?? null),
        ];

        $earned = 0.0;
        $maxAvail = 0.0;
        $breakdown = [];

        foreach ($weights as $key => $bobot) {
            $val = $components[$key];
            $poin = $val === null ? null : round($val * $bobot, 1);

            if ($val !== null) {
                $earned  += $val * $bobot;
                $maxAvail += $bobot;
            }

            $breakdown[$key] = ['weight' => $bobot, 'value' => $val, 'points' => $poin];
        }

        $signals = count(array_filter($components, static fn ($v) => $v !== null));

        if ($maxAvail <= 0) {
            return ['score' => null, 'band' => 'none', 'label' => 'Belum dinilai', 'assessed' => false,
                    'signals' => 0, 'total' => count($components), 'breakdown' => $breakdown];
        }

        $score = (int) round($earned / $maxAvail * 100);

        /* Band Tinggi/Sedang/Rendah hanya sahih bila ADA sinyal penilaian nyata
           (interview / rekomendasi / rating). Profil semata (pendidikan +
           pengalaman) prediktor lemah — jangan sampai "S1 saja" tampil Tinggi.
           Sebelum ada penilaian, statusnya "Awal". */
        $assessed = $components['interview'] !== null
            || $components['recommendation'] !== null
            || $components['rating'] !== null;

        $band = $assessed ? $this->band($score) : 'prelim';

        return [
            'score'     => $score,
            'band'      => $band,
            'label'     => $this->bandLabel($band),
            'assessed'  => $assessed,
            'signals'   => $signals,
            'total'     => count($components),
            'breakdown' => $breakdown,
        ];
    }

    public function band(int $score): string
    {
        return match (true) {
            $score >= 75 => 'high',
            $score >= 50 => 'mid',
            default      => 'low',
        };
    }

    public function bandLabel(string $band): string
    {
        return ['high' => 'Tinggi', 'mid' => 'Sedang', 'low' => 'Rendah',
                'prelim' => 'Awal', 'none' => 'Belum dinilai'][$band] ?? '-';
    }

    /** Label per komponen untuk ditampilkan di rincian. */
    public function componentLabels(): array
    {
        return [
            'interview'      => 'Performa Interview',
            'recommendation' => 'Rekomendasi Pewawancara',
            'rating'         => 'Rating HR',
            'experience'     => 'Pengalaman Kerja',
            'education'      => 'Pendidikan',
        ];
    }

    // ---- Normalisasi tiap sinyal ke rentang 0..1 (null = tak ada data) -----

    private function normInterview(array $app): ?float
    {
        $avg = $app['avg_score'] ?? null;

        return $avg === null || $avg === '' ? null : $this->clamp(((float) $avg) / 5);
    }

    /**
     * Rata-rata rekomendasi. Bila baris lamaran sudah memuat `rec_avg`
     * (subquery dari detailed()), pakai itu — hemat satu query per baris di
     * daftar/pipeline. Jika tidak, hitung langsung dari evaluasi.
     */
    private function normRecommendation(array $app): ?float
    {
        if (array_key_exists('rec_avg', $app)) {
            return $app['rec_avg'] === null ? null : $this->clamp(((float) $app['rec_avg']) / 5);
        }

        $rows = model(EvaluationModel::class)->forApplication((int) $app['id']);
        if ($rows === []) {
            return null;
        }

        $sum = 0; $n = 0;
        foreach ($rows as $r) {
            $sum += self::REC_VALUE[$r['recommendation']] ?? 3;
            $n++;
        }

        return $n ? $this->clamp(($sum / $n) / 5) : null;
    }

    private function norm5($value): ?float
    {
        return ($value === null || $value === '') ? null : $this->clamp(((float) $value) / 5);
    }

    private function normExperience($years): ?float
    {
        if ($years === null || $years === '') {
            return null;
        }
        /* 5 tahun atau lebih dianggap penuh. */
        return $this->clamp(((float) $years) / 5);
    }

    private function normEducation(?string $level): ?float
    {
        if (! $level || ! isset(self::EDU_RANK[$level])) {
            return null;
        }

        return $this->clamp(self::EDU_RANK[$level] / 5);
    }

    private function clamp(float $v): float
    {
        return max(0.0, min(1.0, $v));
    }

    /** Bobot efektif: pengaturan HR bila ada, jika tidak pakai default. */
    private function weights(): array
    {
        $settings = model(SettingModel::class);
        $w = [];
        foreach (self::DEFAULT_WEIGHTS as $key => $default) {
            $val = $settings->value('potential_w_' . $key);
            $w[$key] = ($val === null || $val === '') ? $default : max(0, (int) $val);
        }
        /* Bila HR menyetel semua ke 0, jatuh kembali ke default agar tak bagi 0. */
        return array_sum($w) > 0 ? $w : self::DEFAULT_WEIGHTS;
    }
}
