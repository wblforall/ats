<?php

namespace App\Controllers;

use App\Libraries\PotentialScore;
use App\Models\ApplicationModel;
use App\Models\JobModel;

/**
 * Kandidat Unggulan: pelamar aktif diurutkan menurun berdasar skor potensi.
 * Membantu HR menemukan kandidat menjanjikan tanpa membuka satu per satu.
 */
class Shortlist extends BaseController
{
    public function index()
    {
        $unitId = $this->scopeUnitId();
        $jobId  = (int) $this->request->getGet('job');
        $band   = (string) $this->request->getGet('band');

        $builder = model(ApplicationModel::class)->detailed()->where('applications.status', 'active');
        $builder = model(ApplicationModel::class)->scopeUnit($builder, $unitId);
        if ($jobId) {
            $builder->where('applications.job_id', $jobId);
        }
        $rows = $builder->findAll();

        $scorer = new PotentialScore();
        $scored = [];
        foreach ($rows as $row) {
            $s = $scorer->forApplication($row);
            $row['_score'] = $s;
            $scored[] = $row;
        }

        /* Urut: skor tertinggi dulu; yang sudah dinilai di atas yang masih awal. */
        usort($scored, static function ($a, $b) {
            if ($a['_score']['assessed'] !== $b['_score']['assessed']) {
                return $b['_score']['assessed'] <=> $a['_score']['assessed'];
            }
            return ($b['_score']['score'] ?? -1) <=> ($a['_score']['score'] ?? -1);
        });

        if ($band !== '' && in_array($band, ['high', 'mid', 'low', 'prelim'], true)) {
            $scored = array_values(array_filter($scored, static fn ($r) => $r['_score']['band'] === $band));
        }

        return $this->render('shortlist/index', [
            'title'   => 'Kandidat Unggulan',
            'active'  => 'shortlist',
            'rows'    => $scored,
            'jobs'    => model(JobModel::class)->withStats($unitId)->orderBy('jobs.title', 'ASC')->findAll(),
            'filters' => ['job' => $jobId, 'band' => $band],
        ]);
    }
}
