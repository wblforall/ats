<?php

namespace App\Controllers;

use App\Libraries\EmailQueue;
use App\Models\ApplicationModel;
use App\Models\InterviewModel;
use App\Models\EvaluationModel;
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

        /* Undangan ke pelamar. Diminta: langsung dikirim begitu jadwal
           disimpan, bukan sekadar diantrekan. Tetap dicatat di antrean lebih
           dulu (jejak & bisa kirim ulang), lalu dikirim seketika. */
        $pesanEmail = '';
        if ($this->request->getPost('kirim_undangan')) {
            $queue = new EmailQueue();
            $outboxId = $queue->queueForApplication('interview_invitation', $applicationId, [
                'tanggal_interview' => $scheduledAt ? tgl($scheduledAt) : '-',
                'jam_interview'     => $scheduledAt ? date('H:i', strtotime($scheduledAt)) : '-',
                'lokasi'            => $data['location'] ?: '-',
                'link_meeting'      => $data['meeting_link'] ?: '-',
            ], $this->auth['id']);

            if ($outboxId === null) {
                $pesanEmail = ' Namun template undangan tidak aktif, jadi email tidak dibuat.';
            } elseif (! $queue->smtpReady()) {
                $pesanEmail = ' Undangan tersimpan di antrean, tapi SMTP belum diatur — belum terkirim.';
            } elseif ($queue->sendNow($outboxId)) {
                $pesanEmail = ' Undangan langsung dikirim ke pelamar.';
            } else {
                $pesanEmail = ' Undangan gagal terkirim dan menunggu di antrean untuk dikirim ulang.';
            }
        }

        return redirect()->to(site_url('pelamar/' . $applicationId) . '#interview')
            ->with('success', 'Interview dijadwalkan.' . $pesanEmail);
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

    /**
     * Form penilaian untuk satu jadwal. Boleh diisi oleh pewawancara yang
     * ditugaskan pada jadwal itu, atau manajer HR. Nilai lama (bila sudah
     * pernah mengisi) ditampilkan untuk diperbarui.
     */
    public function evaluate(int $interviewId)
    {
        $interview = model(InterviewModel::class)->detailed()->where('interviews.id', $interviewId)->first();
        if (! $interview || ! $this->reachable($interview)) {
            return redirect()->to(site_url('interview'))->with('error', 'Jadwal tidak ditemukan.');
        }

        if (! $this->canEvaluate($interviewId)) {
            return redirect()->to(site_url('pelamar/' . $interview['application_id']))
                ->with('error', 'Anda tidak ditugaskan menilai interview ini.');
        }

        return $this->render('interviews/evaluate', [
            'title'     => 'Penilaian Interview',
            'active'    => 'interviews',
            'interview' => $interview,
            'app'       => model(ApplicationModel::class)->findDetailed((int) $interview['application_id']),
            'mine'      => model(EvaluationModel::class)->findMine($interviewId, $this->auth['id']),
        ]);
    }

    public function saveEvaluation(int $interviewId)
    {
        $interview = model(InterviewModel::class)->detailed()->where('interviews.id', $interviewId)->first();
        if (! $interview || ! $this->reachable($interview) || ! $this->canEvaluate($interviewId)) {
            return redirect()->to(site_url('interview'))->with('error', 'Anda tidak berhak menilai interview ini.');
        }

        $data = [
            'score_technical'     => $this->score('score_technical'),
            'score_communication' => $this->score('score_communication'),
            'score_attitude'      => $this->score('score_attitude'),
            'score_experience'    => $this->score('score_experience'),
            'score_culture'       => $this->score('score_culture'),
            'recommendation'      => $this->request->getPost('recommendation') ?: 'neutral',
            'strengths'           => $this->request->getPost('strengths') ?: null,
            'weaknesses'          => $this->request->getPost('weaknesses') ?: null,
            'notes'               => $this->request->getPost('notes') ?: null,
        ];

        /* submit() menghitung overall & menyegarkan rata-rata kandidat, lalu
           menandai jadwal ini selesai bila belum. */
        model(EvaluationModel::class)->submit($interviewId, $this->auth['id'], $data, (int) $interview['application_id']);
        if ($interview['status'] === 'scheduled') {
            model(InterviewModel::class)->update($interviewId, ['status' => 'completed']);
        }

        return redirect()->to(site_url('pelamar/' . $interview['application_id']) . '#interview')
            ->with('success', 'Penilaian disimpan.');
    }

    /** Skala 1–5; kosong berarti aspek tak dinilai. */
    private function score(string $field): ?int
    {
        $v = $this->request->getPost($field);

        return ($v === null || $v === '') ? null : max(1, min(5, (int) $v));
    }

    /** Boleh menilai bila ditugaskan pada jadwal, atau manajer HR. */
    private function canEvaluate(int $interviewId): bool
    {
        return $this->isRole('superadmin', 'hr_admin')
            || model(InterviewParticipantModel::class)->isAssigned($interviewId, (int) $this->auth['id']);
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
