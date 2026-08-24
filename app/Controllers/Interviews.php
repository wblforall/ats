<?php

namespace App\Controllers;

use App\Libraries\EmailQueue;
use App\Models\ApplicationModel;
use App\Models\InterviewModel;
use App\Models\InterviewParticipantModel;
use App\Models\UserModel;

/**
 * Jadwal interview. Setiap jadwal mencatat SIAPA yang menjadwalkan (created_by),
 * KAPAN diproses (created_at, otomatis), dan UNTUK KAPAN (scheduled_at) — tiga
 * hal yang diminta agar penjadwalan bisa ditelusuri.
 */
class Interviews extends BaseController
{
    /** Agenda semua interview dalam rentang, untuk halaman daftar/kalender. */
    public function index()
    {
        $unitId = $this->scopeUnitId();
        $from   = $this->request->getGet('from') ?: date('Y-m-d');
        $to     = $this->request->getGet('to') ?: date('Y-m-d', strtotime($from . ' +30 days'));

        /* Interviewer hanya melihat jadwal yang menugaskan dirinya. */
        $onlyUser = $this->isRole('interviewer') ? $this->auth['id'] : null;

        return $this->render('interviews/index', [
            'title'   => 'Interview',
            'active'  => 'interviews',
            'rows'    => model(InterviewModel::class)->between($from . ' 00:00:00', $to . ' 23:59:59', $unitId, $onlyUser),
            'filters' => ['from' => $from, 'to' => $to],
        ]);
    }

    /** Formulir jadwal baru untuk satu lamaran. */
    public function create(int $applicationId)
    {
        $application = $this->scopedApplication($applicationId);
        if (! $application) {
            return redirect()->to(site_url('pelamar'))->with('error', 'Pelamar tidak ditemukan.');
        }

        if (! $this->isRole('superadmin', 'hr_admin', 'hr_staff')) {
            return redirect()->to(site_url('pelamar/' . $applicationId))->with('error', 'Anda tidak berhak menjadwalkan interview.');
        }

        return $this->render('interviews/form', [
            'title'         => 'Jadwalkan Interview',
            'active'        => 'interviews',
            'app'           => $application,
            'nextRound'     => model(InterviewModel::class)->nextRound($applicationId),
            'interviewers'  => model(UserModel::class)->interviewerOptions($application['unit_id']),
        ]);
    }

    public function store(int $applicationId)
    {
        $application = $this->scopedApplication($applicationId);
        if (! $application) {
            return redirect()->to(site_url('pelamar'))->with('error', 'Pelamar tidak ditemukan.');
        }

        if (! $this->isRole('superadmin', 'hr_admin', 'hr_staff')) {
            return redirect()->to(site_url('pelamar/' . $applicationId))->with('error', 'Anda tidak berhak menjadwalkan interview.');
        }

        $tanggal = trim((string) $this->request->getPost('tanggal'));
        $jam     = trim((string) $this->request->getPost('jam'));
        $scheduledAt = $tanggal && $jam ? $tanggal . ' ' . $jam . ':00' : null;

        $interviews = model(InterviewModel::class);
        $data = [
            'application_id' => $applicationId,
            'round'          => (int) $this->request->getPost('round') ?: 1,
            'title'          => $this->request->getPost('title') ?: null,
            'mode'           => $this->request->getPost('mode') ?: 'onsite',
            'scheduled_at'   => $scheduledAt,
            'duration_min'   => (int) $this->request->getPost('duration_min') ?: 60,
            'location'       => $this->request->getPost('location') ?: null,
            'meeting_link'   => $this->request->getPost('meeting_link') ?: null,
            'status'         => 'scheduled',
            'created_by'     => $this->auth['id'],   // siapa yang menjadwalkan
        ];

        if (! $interviews->insert($data)) {
            return redirect()->back()->withInput()->with('errors', $interviews->errors());
        }

        $interviewId = $interviews->getInsertID();

        /* Pewawancara yang ditugaskan. Disaring ke yang benar-benar boleh
           menilai di unit ini, supaya id sembarang dari form tidak menyusup. */
        $sah = array_keys(model(UserModel::class)->interviewerOptions($application['unit_id']));
        $dipilih = array_intersect(
            array_map('intval', (array) $this->request->getPost('participants')),
            $sah
        );
        model(InterviewParticipantModel::class)->sync($interviewId, $dipilih);

        /* Undangan ke pelamar disiapkan di antrean bila diminta. */
        if ($this->request->getPost('kirim_undangan')) {
            (new EmailQueue())->queueForApplication('interview_invitation', $applicationId, [
                'tanggal_interview' => $scheduledAt ? tgl($scheduledAt) : '-',
                'jam_interview'     => $scheduledAt ? date('H:i', strtotime($scheduledAt)) : '-',
                'lokasi'            => $data['location'] ?: '-',
                'link_meeting'      => $data['meeting_link'] ?: '-',
            ], $this->auth['id']);
        }

        return redirect()->to(site_url('pelamar/' . $applicationId) . '#interview')
            ->with('success', 'Interview dijadwalkan.'
                . ($this->request->getPost('kirim_undangan') ? ' Undangan disiapkan di antrean email.' : ''));
    }

    /** Ubah status jadwal (selesai / batal / tidak hadir). */
    public function setStatus(int $id)
    {
        $interview = model(InterviewModel::class)->detailed()->where('interviews.id', $id)->first();
        if (! $interview || ! $this->reachable($interview)) {
            return redirect()->to(site_url('interview'))->with('error', 'Jadwal tidak ditemukan.');
        }

        $status = (string) $this->request->getPost('status');
        if (! isset(InterviewModel::STATUSES[$status])) {
            return redirect()->back()->with('error', 'Status tidak dikenal.');
        }

        model(InterviewModel::class)->update($id, ['status' => $status]);

        return redirect()->to(site_url('pelamar/' . $interview['application_id']) . '#interview')
            ->with('success', 'Status interview diperbarui.');
    }

    private function scopedApplication(int $id): ?array
    {
        $builder = model(ApplicationModel::class)->detailed()->where('applications.id', $id);
        $builder = model(ApplicationModel::class)->scopeUnit($builder, $this->scopeUnitId());

        return $builder->first();
    }

    /** Apakah jadwal ini boleh disentuh user (dalam unitnya). */
    private function reachable(array $interview): bool
    {
        $unitId = $this->scopeUnitId();

        return $unitId === null || (int) $interview['unit_id'] === $unitId;
    }
}
