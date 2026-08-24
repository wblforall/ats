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
            'jobs'     => model(JobModel::class)->withStats($unitId)->where('(SELECT COUNT(*) FROM applications a WHERE a.job_id = jobs.id AND a.deleted_at IS NULL) > 0', null, false)->orderBy('jobs.title', 'ASC')->findAll(),
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
            'potential'   => (new \App\Libraries\PotentialScore())->forApplication($application),
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

        /* Email pemberitahuan yang cocok dengan tahap tujuan. Template yang
           ber-flag auto_send dikirim langsung; sisanya hanya diantrekan agar
           HR sempat meninjau di menu Email. Pemetaan tahap→template memakai
           kind (hired/rejected) dan slug (penawaran). */
        $templateCode = match (true) {
            $stage['kind'] === 'hired'      => 'hired_welcome',
            $stage['kind'] === 'rejected'   => 'rejection',
            $stage['slug'] === 'penawaran'  => 'offer_letter',
            default                         => null,
        };

        $emailMsg = '';
        if ($templateCode) {
            $tpl = model(\App\Models\EmailTemplateModel::class)->findByCode($templateCode);
            if ($tpl) {
                $queue    = new EmailQueue();
                $outboxId = $queue->queueForApplication($templateCode, $id, [], $this->auth['id']);

                if ($outboxId && $tpl['auto_send'] && $queue->smtpReady() && $queue->sendNow($outboxId)) {
                    $emailMsg = ' Email "' . $tpl['name'] . '" langsung dikirim.';
                } elseif ($outboxId) {
                    $emailMsg = ' Email "' . $tpl['name'] . '" disiapkan di antrean.';
                }
            }
        }

        return redirect()->to(site_url('pelamar/' . $id))
            ->with('success', 'Tahap dipindah ke "' . $stage['name'] . '".' . $emailMsg);
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
     * Kirim email ke pelamar dari template terpilih. Langsung dikirim bila
     * SMTP siap; kalau tidak, menunggu di antrean. HR memilih template dan
     * placeholder terisi otomatis dari data lamaran.
     */
    public function sendEmail(int $id)
    {
        $application = $this->findScoped($id);
        if (! $application) {
            return redirect()->to(site_url('pelamar'))->with('error', 'Pelamar tidak ditemukan.');
        }
        if (! $this->isManager()) {
            return redirect()->back()->with('error', 'Hanya Admin HRD yang boleh mengirim email.');
        }

        $code = (string) $this->request->getPost('template_code');
        if ($code === '') {
            return redirect()->back()->with('error', 'Pilih template email lebih dulu.');
        }

        $queue    = new EmailQueue();
        $outboxId = $queue->queueForApplication($code, $id, [], $this->auth['id']);

        if ($outboxId === null) {
            return redirect()->back()->with('error', 'Template tidak ditemukan atau nonaktif.');
        }

        $pesan = 'Email disiapkan.';
        if (! $queue->smtpReady()) {
            $pesan = 'Email masuk antrean; SMTP belum diatur jadi belum terkirim.';
        } elseif ($queue->sendNow($outboxId)) {
            $pesan = 'Email terkirim ke pelamar.';
        } else {
            $pesan = 'Email gagal terkirim dan menunggu di antrean.';
        }

        return redirect()->to(site_url('pelamar/' . $id))->with('success', $pesan);
    }

    /** Tandai pelamar mengundurkan diri. */
    public function withdraw(int $id)
    {
        $application = $this->findScoped($id);
        if (! $application) {
            return redirect()->to(site_url('pelamar'))->with('error', 'Pelamar tidak ditemukan.');
        }
        if (! $this->isRole('superadmin', 'hr_admin', 'hr_staff')) {
            return redirect()->back()->with('error', 'Anda tidak berhak mengubah status ini.');
        }

        $reason = trim((string) $this->request->getPost('reason')) ?: 'Mengundurkan diri';
        model(ApplicationModel::class)->update($id, [
            'status'    => 'withdrawn',
            'closed_at' => date('Y-m-d H:i:s'),
        ]);
        model(\App\Models\ApplicationNoteModel::class)->insert([
            'application_id' => $id,
            'user_id'        => $this->auth['id'],
            'note'           => 'Pelamar mengundurkan diri. ' . $reason,
        ]);

        return redirect()->to(site_url('pelamar/' . $id))->with('info', 'Pelamar ditandai mengundurkan diri.');
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

        $ext  = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $nama = $application['cv_original']
            ?: ('CV-' . url_title($application['full_name'], '-', true) . '.' . $ext);

        $mime = [
            'pdf'  => 'application/pdf',
            'doc'  => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ][$ext] ?? 'application/octet-stream';

        /* Sajikan `inline` supaya CV tampil langsung di tab peramban, bukan
           terunduh. PDF ter-preview; format yang tak bisa dirender peramban
           (doc/docx) akan tetap terunduh dengan nama yang benar. Nama file
           dibersihkan dari karakter yang bisa merusak header. */
        $namaAman = str_replace(['"', "\r", "\n"], '', $nama);

        return $this->response
            ->setHeader('Content-Type', $mime)
            ->setHeader('Content-Disposition', 'inline; filename="' . $namaAman . '"')
            ->setHeader('X-Content-Type-Options', 'nosniff')
            ->setBody(file_get_contents($path));
    }

    /**
     * Ambil lamaran lengkap yang boleh diakses user ini; null bila terlarang.
     *
     * Interviewer tidak punya unit kerja tetap, jadi tidak disaring per unit —
     * ia hanya boleh melihat lamaran yang benar-benar ia wawancarai. HR
     * disaring seperti biasa per unit.
     */
    private function findScoped(int $id): ?array
    {
        if ($this->isRole('interviewer')) {
            return $this->isAssignedToApplication($id)
                ? model(ApplicationModel::class)->findDetailed($id)
                : null;
        }

        $builder = model(ApplicationModel::class)->detailed()->where('applications.id', $id);
        $builder = model(ApplicationModel::class)->scopeUnit($builder, $this->scopeUnitId());

        return $builder->first();
    }

    /** Benarkah user (interviewer) ditugaskan pada suatu interview lamaran ini. */
    private function isAssignedToApplication(int $applicationId): bool
    {
        return model(\App\Models\InterviewParticipantModel::class)
            ->join('interviews', 'interviews.id = interview_participants.interview_id')
            ->where('interviews.application_id', $applicationId)
            ->where('interview_participants.user_id', (int) $this->auth['id'])
            ->countAllResults() > 0;
    }
}
