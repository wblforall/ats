<?php

namespace App\Controllers;

use App\Models\ApplicationModel;
use App\Models\CandidateModel;

/**
 * Bank talenta: seluruh orang yang pernah melamar, lintas lowongan. Berguna
 * mencari kembali kandidat lama saat ada lowongan baru yang cocok.
 */
class Candidates extends BaseController
{
    public function index()
    {
        $q = trim((string) $this->request->getGet('q'));

        return $this->render('candidates/index', [
            'title'      => 'Kandidat',
            'active'     => 'candidates',
            'candidates' => model(CandidateModel::class)->search($q)
                ->orderBy('candidates.full_name', 'ASC')->findAll(200),
            'q'          => $q,
        ]);
    }

    public function show(int $id)
    {
        $candidate = model(CandidateModel::class)->find($id);
        if (! $candidate) {
            return redirect()->to(site_url('kandidat'))->with('error', 'Kandidat tidak ditemukan.');
        }

        /* Semua lamaran orang ini; disaring ke unit yang boleh dilihat user. */
        $apps = model(ApplicationModel::class)->detailed()->where('applications.candidate_id', $id);
        $apps = model(ApplicationModel::class)->scopeUnit($apps, $this->scopeUnitId());

        return $this->render('candidates/show', [
            'title'        => $candidate['full_name'],
            'active'       => 'candidates',
            'candidate'    => $candidate,
            'applications' => $apps->orderBy('applications.applied_at', 'DESC')->findAll(),
        ]);
    }

    /** Tandai / lepas daftar hitam. */
    public function blacklist(int $id)
    {
        if (! $this->isManager()) {
            return redirect()->back()->with('error', 'Hanya Admin HRD yang boleh mengubah daftar hitam.');
        }

        $candidate = model(CandidateModel::class)->find($id);
        if (! $candidate) {
            return redirect()->to(site_url('kandidat'))->with('error', 'Kandidat tidak ditemukan.');
        }

        $jadikan = ! $candidate['is_blacklisted'];
        model(CandidateModel::class)->update($id, [
            'is_blacklisted'   => $jadikan ? 1 : 0,
            'blacklist_reason' => $jadikan ? (trim((string) $this->request->getPost('reason')) ?: null) : null,
        ]);

        return redirect()->to(site_url('kandidat/' . $id))
            ->with('success', $jadikan ? 'Kandidat masuk daftar hitam.' : 'Kandidat dikeluarkan dari daftar hitam.');
    }
}
