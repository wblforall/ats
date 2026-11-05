<?php

namespace App\Controllers;

use App\Libraries\EmailQueue;
use App\Libraries\InterviewLink;
use App\Models\ApplicationNoteModel;
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
        ] + InterviewModel::freshResponse();

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
           disimpan, bukan sekadar diantrekan. */
        $pesanEmail = $this->request->getPost('kirim_undangan')
            ? $this->sendInvitation((int) $interviewId)
            : '';

        return redirect()->to(site_url('pelamar/' . $applicationId) . '#interview')
            ->with('success', 'Interview dijadwalkan.' . $pesanEmail);
    }

    /** Form ubah jadwal interview yang sudah ada. */
    public function edit(int $id)
    {
        $interview = model(InterviewModel::class)->detailed()->where('interviews.id', $id)->first();
        if (! $interview || ! $this->reachable($interview)) {
            return redirect()->to(site_url('interview'))->with('error', 'Jadwal tidak ditemukan.');
        }
        if (! $this->isRole('superadmin', 'hr_admin', 'hr_staff')) {
            return redirect()->to(site_url('pelamar/' . $interview['application_id']))->with('error', 'Anda tidak berhak mengubah jadwal.');
        }

        return $this->render('interviews/form', [
            'title'        => 'Ubah Jadwal Interview',
            'active'       => 'interviews',
            'app'          => $this->scopedApplication((int) $interview['application_id']),
            'interview'    => $interview,
            'nextRound'    => (int) $interview['round'],
            'interviewers' => model(UserModel::class)->interviewerOptions($interview['unit_id']),
            'chosen'       => model(InterviewParticipantModel::class)->idsFor($id),
            /* Dari tombol "Atur waktu lain" pada permintaan jadwal ulang:
               form terisi usulan pelamar dan undangan baru dicentang. */
            'pakaiUsulan'  => $this->request->getGet('usulan') && $interview['reschedule_proposed_at'],
        ]);
    }

    public function updateInterview(int $id)
    {
        $interview = model(InterviewModel::class)->detailed()->where('interviews.id', $id)->first();
        if (! $interview || ! $this->reachable($interview)) {
            return redirect()->to(site_url('interview'))->with('error', 'Jadwal tidak ditemukan.');
        }
        if (! $this->isRole('superadmin', 'hr_admin', 'hr_staff')) {
            return redirect()->to(site_url('pelamar/' . $interview['application_id']))->with('error', 'Anda tidak berhak mengubah jadwal.');
        }

        $tanggal = trim((string) $this->request->getPost('tanggal'));
        $jam     = trim((string) $this->request->getPost('jam'));
        $scheduledAt = $tanggal && $jam ? $tanggal . ' ' . $jam . ':00' : $interview['scheduled_at'];

        $data = [
            'round'        => (int) $this->request->getPost('round') ?: (int) $interview['round'],
            'title'        => $this->request->getPost('title') ?: null,
            'mode'         => $this->request->getPost('mode') ?: 'onsite',
            'scheduled_at' => $scheduledAt,
            'duration_min' => (int) $this->request->getPost('duration_min') ?: 60,
            'location'     => $this->request->getPost('location') ?: null,
            'meeting_link' => $this->request->getPost('meeting_link') ?: null,
        ];

        /* Waktu berubah: jawaban pelamar atas jadwal lama tak berlaku lagi dan
           tombol di email lama dimatikan. */
        if ($scheduledAt !== $interview['scheduled_at']) {
            $data += InterviewModel::freshResponse();
        }

        $interviews = model(InterviewModel::class);
        if (! $interviews->update($id, $data)) {
            return redirect()->back()->withInput()->with('errors', $interviews->errors());
        }

        $sah = array_keys(model(UserModel::class)->interviewerOptions($interview['unit_id']));
        $dipilih = array_intersect(array_map('intval', (array) $this->request->getPost('participants')), $sah);
        model(InterviewParticipantModel::class)->sync($id, $dipilih);

        $pesanEmail = $this->request->getPost('kirim_undangan') ? $this->sendInvitation($id) : '';

        return redirect()->to(site_url('pelamar/' . $interview['application_id']) . '#interview')
            ->with('success', 'Jadwal interview diperbarui.' . $pesanEmail);
    }

    /** Setujui usulan pelamar apa adanya: jadwal dipindah dan undangan baru dikirim. */
    public function approveReschedule(int $id)
    {
        $interview = $this->rescheduleRequest($id);
        if (is_string($interview)) {
            return redirect()->back()->with('error', $interview);
        }

        $usulan = $interview['reschedule_proposed_at'];
        if (strtotime($usulan) <= time()) {
            return redirect()->to(site_url('interview/' . $id . '/ubah?usulan=1'))
                ->with('error', 'Waktu usulan pelamar sudah lewat. Tentukan waktu lain.');
        }
        model(InterviewModel::class)->update($id, ['scheduled_at' => $usulan] + InterviewModel::freshResponse());
        $this->note($interview, 'Permintaan jadwal ulang disetujui. Jadwal dipindah ke '
            . hari($usulan) . ', ' . tgl($usulan, true) . ' WITA.');

        return redirect()->to(site_url('pelamar/' . $interview['application_id']) . '#interview')
            ->with('success', 'Jadwal dipindah sesuai usulan pelamar.' . $this->sendInvitation($id));
    }

    /** Tolak usulan: jadwal tetap, pelamar diberi tahu dan diminta konfirmasi lagi. */
    public function declineReschedule(int $id)
    {
        $interview = $this->rescheduleRequest($id);
        if (is_string($interview)) {
            return redirect()->back()->with('error', $interview);
        }

        $pesan = trim((string) $this->request->getPost('pesan'));
        model(InterviewModel::class)->update($id, InterviewModel::freshResponse(false));
        $this->note($interview, 'Permintaan jadwal ulang ditolak; jadwal tetap.' . ($pesan !== '' ? ' Pesan ke pelamar: ' . $pesan : ''));

        $fresh = model(InterviewModel::class)->find($id);
        $hasil = $this->queueAndSend('interview_reschedule_declined', $fresh, [
            'pesan_hr' => $pesan !== '' ? '<p>' . nl2br(esc($pesan)) . '</p>' : '',
        ]);

        return redirect()->to(site_url('pelamar/' . $interview['application_id']) . '#interview')
            ->with('success', 'Permintaan jadwal ulang ditolak, jadwal tetap.' . $hasil);
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

    /** Kirim undangan (dengan tombol jawaban) untuk jadwal ini; kembalikan kalimat hasil untuk flash. */
    private function sendInvitation(int $interviewId): string
    {
        $interview = model(InterviewModel::class)->find($interviewId);
        $hasil     = $this->queueAndSend('interview_invitation', $interview);
        model(InterviewModel::class)->update($interviewId, ['invited_at' => date('Y-m-d H:i:s')]);

        return $hasil;
    }

    /**
     * Susun email jadwal ke pelamar, catat di antrean, lalu kirim seketika.
     * Tetap lewat antrean lebih dulu supaya ada jejak & bisa dikirim ulang.
     */
    private function queueAndSend(string $template, array $interview, array $extra = []): string
    {
        $interview = model(InterviewModel::class)->withNonce($interview);
        $at        = $interview['scheduled_at'];
        $queue     = new EmailQueue();
        $outboxId = $queue->queueForApplication($template, (int) $interview['application_id'], $extra + [
            'tanggal_interview' => tgl($at),
            'jam_interview'     => date('H:i', strtotime($at)),
            'lokasi'            => $interview['location'] ?: '-',
            'link_meeting'      => $interview['meeting_link'] ?: '-',
            'tombol_jawaban'    => InterviewLink::buttonsHtml($interview),
        ], $this->auth['id']);

        if ($outboxId === null) {
            return ' Namun template email tidak aktif, jadi email tidak dibuat.';
        }
        if (! $queue->smtpReady()) {
            return ' Email tersimpan di antrean, tapi SMTP belum diatur — belum terkirim.';
        }

        return $queue->sendNow($outboxId)
            ? ' Email langsung dikirim ke pelamar.'
            : ' Email gagal terkirim dan menunggu di antrean untuk dikirim ulang.';
    }

    /** Jadwal dengan permintaan jadwal ulang yang masih menunggu, atau pesan kesalahan. */
    private function rescheduleRequest(int $id): array|string
    {
        $interview = model(InterviewModel::class)->detailed()->where('interviews.id', $id)->first();
        if (! $interview || ! $this->reachable($interview)) {
            return 'Jadwal tidak ditemukan.';
        }
        if (! $this->isRole('superadmin', 'hr_admin', 'hr_staff')) {
            return 'Anda tidak berhak mengubah jadwal.';
        }
        if ($interview['status'] !== 'scheduled' || $interview['response_status'] !== 'reschedule_requested') {
            return 'Tidak ada permintaan jadwal ulang yang menunggu untuk jadwal ini.';
        }

        return $interview;
    }

    private function note(array $interview, string $text): void
    {
        model(ApplicationNoteModel::class)->insert([
            'application_id' => (int) $interview['application_id'],
            'user_id'        => $this->auth['id'],
            'note'           => 'Interview putaran ' . (int) $interview['round'] . ': ' . $text,
        ]);
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
