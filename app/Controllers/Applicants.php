<?php

namespace App\Controllers;

use App\Libraries\EmailQueue;
use App\Models\ApplicationHistoryModel;
use App\Models\ApplicationModel;
use App\Models\ApplicationNoteModel;
use App\Models\EvaluationModel;
use App\Models\InterviewModel;
use App\Models\JobModel;
use App\Models\StageModel;

/**
 * Pelamar (lamaran) di sisi HR: daftar, detail, geser tahap, catatan, unduh CV.
 *
 * Semua akses disaring per unit lewat scopeUnitId() — HR sebuah unit tidak
 * pernah bisa menyentuh lamaran unit lain, bahkan dengan menebak id di URL.
 */
class Applicants extends BaseController
{
    public function index()
    {
        $unitId = $this->scopeUnitId();

        $apps  = model(ApplicationModel::class)->detailed();
        $apps  = model(ApplicationModel::class)->scopeUnit($apps, $unitId);

        $stageSlug = (string) $this->request->getGet('stage');
        $jobId     = (int) $this->request->getGet('job');
        $status    = (string) $this->request->getGet('status');
        $q         = trim((string) $this->request->getGet('q'));

        if ($stageSlug !== '') {
            $apps->where('stages.slug', $stageSlug);
        }
        if ($jobId) {
            $apps->where('applications.job_id', $jobId);
        }
        if ($status !== '' && isset(ApplicationModel::STATUSES[$status])) {
            $apps->where('applications.status', $status);
        }
        if ($q !== '') {
            $apps->groupStart()
                ->like('candidates.full_name', $q)
                ->orLike('candidates.email', $q)
                ->orLike('candidates.phone', $q)
                ->groupEnd();
        }

        return $this->render('applicants/index', [
            'title'    => 'Pelamar',
            'active'   => 'apps',
            'rows'     => $apps->orderBy('applications.applied_at', 'DESC')->findAll(),
            'stages'   => model(StageModel::class)->active(),
            'jobs'     => model(JobModel::class)->withStats($unitId)->orderBy('jobs.title', 'ASC')->findAll(),
            'filters'  => ['stage' => $stageSlug, 'job' => $jobId, 'status' => $status, 'q' => $q],
        ]);
    }

    public function show(int $id)
    {
        $application = $this->findScoped($id);
        if (! $application) {
            return redirect()->to(site_url('pelamar'))->with('error', 'Pelamar tidak ditemukan.');
        }

        $application['reference'] = model(ApplicationModel::class)
            ->referenceCode($id, $application['applied_at']);

        return $this->render('applicants/show', [
            'title'       => $application['full_name'],
            'active'      => 'apps',
            'app'         => $application,
            'stages'      => model(StageModel::class)->active(),
            'timeline'    => model(ApplicationHistoryModel::class)->timeline($id),
            'notes'       => model(ApplicationNoteModel::class)->forApplication($id),
            'interviews'  => model(InterviewModel::class)->forApplication($id),
            'evaluations' => model(EvaluationModel::class)->forApplication($id),
            'emailLog'    => model(\App\Models\EmailOutboxModel::class)->forApplication($id),
            'templates'   => model(\App\Models\EmailTemplateModel::class)->activeOptions(),
        ]);
    }

    /**
     * Geser tahap. Status lamaran mengikuti kind tahap tujuan; riwayat dicatat
     * dengan id user yang menggeser. Bila tahap tujuan menutup proses
     * (diterima/tidak lolos), email pemberitahuan yang sesuai diantrekan.
     */
    public function move(int $id)
    {
        $application = $this->findScoped($id);
        if (! $application) {
            return redirect()->to(site_url('pelamar'))->with('error', 'Pelamar tidak ditemukan.');
        }

        if (! $this->isRole('superadmin', 'hr_admin', 'hr_staff')) {
            return redirect()->back()->with('error', 'Anda tidak berhak menggeser tahap.');
        }

        $toStageId = (int) $this->request->getPost('stage_id');
        $stage     = model(StageModel::class)->find($toStageId);
        if (! $stage) {
            return redirect()->back()->with('error', 'Tahap tujuan tidak dikenal.');
        }

        $note   = trim((string) $this->request->getPost('note')) ?: null;
        $reason = $stage['kind'] === 'rejected'
            ? (trim((string) $this->request->getPost('reject_reason')) ?: null)
            : null;

        $ok = model(ApplicationModel::class)
            ->moveStage($id, $toStageId, $this->auth['id'], $note, $reason);

        if (! $ok) {
            return redirect()->back()->with('error', 'Gagal menggeser tahap.');
        }

        /* Email otomatis untuk tahap penutup — hanya bila template-nya aktif.
           Sengaja diantrekan (bukan dikirim langsung) supaya HR sempat memeriksa
           di menu Email sebelum sampai ke pelamar. */
        $autoTemplate = match ($stage['kind']) {
            'hired'    => 'hired_welcome',
            'rejected' => 'rejection',
            default    => null,
        };
        if ($autoTemplate) {
            (new EmailQueue())->queueForApplication($autoTemplate, $id, [], $this->auth['id']);
        }

        return redirect()->to(site_url('pelamar/' . $id))
            ->with('success', 'Tahap dipindah ke "' . $stage['name'] . '".'
                . ($autoTemplate ? ' Email pemberitahuan disiapkan di antrean.' : ''));
    }

    public function addNote(int $id)
    {
        $application = $this->findScoped($id);
        if (! $application) {
            return redirect()->to(site_url('pelamar'))->with('error', 'Pelamar tidak ditemukan.');
        }

        $note = trim((string) $this->request->getPost('note'));
        if ($note === '') {
            return redirect()->back()->with('error', 'Catatan tidak boleh kosong.');
        }

        model(ApplicationNoteModel::class)->insert([
            'application_id' => $id,
            'user_id'        => $this->auth['id'],
            'note'           => $note,
        ]);

        return redirect()->to(site_url('pelamar/' . $id) . '#catatan')
            ->with('success', 'Catatan ditambahkan.');
    }

    public function rate(int $id)
    {
        $application = $this->findScoped($id);
        if (! $application) {
            return redirect()->to(site_url('pelamar'))->with('error', 'Pelamar tidak ditemukan.');
        }

        $rating = (int) $this->request->getPost('rating');
        $rating = max(0, min(5, $rating));

        model(ApplicationModel::class)->update($id, ['rating' => $rating ?: null]);

        return redirect()->to(site_url('pelamar/' . $id))->with('success', 'Penilaian bintang disimpan.');
    }

    /**
     * Unduh CV. CV disimpan di writable/ (di luar docroot) dan tidak bisa
     * diakses langsung lewat web; satu-satunya jalan adalah lewat sini, yang
     * memaksa cek unit lebih dulu. Nama unduhan memakai nama asli berkas.
     */
    public function downloadCv(int $id)
    {
        $application = $this->findScoped($id);
        if (! $application || ! $application['cv_path']) {
            return redirect()->to(site_url('pelamar'))->with('error', 'Berkas CV tidak ditemukan.');
        }

        $path = WRITEPATH . $application['cv_path'];
        if (! is_file($path)) {
            return redirect()->to(site_url('pelamar/' . $id))->with('error', 'Berkas CV sudah tidak ada di server.');
        }

        $namaUnduh = $application['cv_original']
            ?: ('CV-' . url_title($application['full_name'], '-', true) . '.' . pathinfo($path, PATHINFO_EXTENSION));

        return $this->response->download($path, null)->setFileName($namaUnduh);
    }

    /** Ambil lamaran lengkap yang boleh diakses user ini; null bila di luar unitnya. */
    private function findScoped(int $id): ?array
    {
        $builder = model(ApplicationModel::class)->detailed()->where('applications.id', $id);
        $builder = model(ApplicationModel::class)->scopeUnit($builder, $this->scopeUnitId());

        return $builder->first();
    }
}
