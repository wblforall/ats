<?php

namespace App\Controllers;

use App\Models\ApplicationModel;
use App\Models\CandidateModel;
use App\Models\JobModel;

/** Pencarian global dari kotak di topbar: pelamar, lowongan, kandidat. */
class Search extends BaseController
{
    public function index()
    {
        $q      = trim((string) $this->request->getGet('q'));
        $unitId = $this->scopeUnitId();

        $pelamar = $lowongan = $kandidat = [];

        if ($q !== '' && mb_strlen($q) >= 2) {
            /* Pelamar (lamaran) — disaring per unit. */
            $ab = model(ApplicationModel::class)->detailed()
                ->groupStart()
                    ->like('candidates.full_name', $q)
                    ->orLike('candidates.email', $q)
                    ->orLike('candidates.phone', $q)
                ->groupEnd();
            $ab = model(ApplicationModel::class)->scopeUnit($ab, $unitId);
            $pelamar = $ab->orderBy('applications.applied_at', 'DESC')->findAll(20);

            /* Lowongan — hanya untuk peran yang boleh melihat lowongan. */
            if (! $this->isRole('interviewer')) {
                $lowongan = model(JobModel::class)->withStats($unitId)
                    ->groupStart()->like('jobs.title', $q)->orLike('jobs.code', $q)->groupEnd()
                    ->orderBy('jobs.created_at', 'DESC')->findAll(20);

                $kandidat = model(CandidateModel::class)->search($q)
                    ->orderBy('candidates.full_name', 'ASC')->findAll(20);
            }
        }

        return $this->render('search/index', [
            'title'    => 'Pencarian',
            'active'   => '',
            'q'        => $q,
            'pelamar'  => $pelamar,
            'lowongan' => $lowongan,
            'kandidat' => $kandidat,
        ]);
    }
}
