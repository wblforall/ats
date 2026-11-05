<?php

namespace App\Models;

use CodeIgniter\Model;

class InterviewModel extends Model
{
    protected $table          = 'interviews';
    protected $primaryKey     = 'id';
    protected $returnType     = 'array';
    protected $useSoftDeletes = true;
    protected $allowedFields  = [
        'application_id', 'round', 'title', 'mode', 'scheduled_at', 'duration_min',
        'location', 'meeting_link', 'status', 'notes', 'created_by',
        'response_status', 'response_nonce', 'invited_at', 'responded_at',
        'reschedule_proposed_at', 'response_reason',
    ];
    protected $useTimestamps = true;

    protected $validationRules = [
        'application_id' => 'required|is_natural_no_zero',
        'scheduled_at'   => 'required|valid_date[Y-m-d H:i:s]',
        'mode'           => 'required|in_list[onsite,online,phone]',
        'duration_min'   => 'required|is_natural_no_zero',
    ];
    protected $validationMessages = [
        'scheduled_at' => [
            'required'   => 'Tanggal & jam interview wajib diisi.',
            'valid_date' => 'Format tanggal interview tidak valid.',
        ],
    ];

    public const MODES = [
        'onsite' => 'Tatap Muka',
        'online' => 'Daring',
        'phone'  => 'Telepon',
    ];

    public const STATUSES = [
        'scheduled' => 'Terjadwal',
        'completed' => 'Selesai',
        'canceled'  => 'Dibatalkan',
        'no_show'   => 'Tidak Hadir',
    ];

    /** Jawaban pelamar atas undangan (lewat tombol di email). */
    public const RESPONSES = [
        'pending'              => 'Belum Menjawab',
        'confirmed'            => 'Konfirmasi Hadir',
        'reschedule_requested'  => 'Minta Jadwal Ulang',
        'canceled_by_candidate' => 'Dibatalkan Pelamar',
    ];

    public const RESPONSE_BADGES = [
        'pending'              => 'badge-gray',
        'confirmed'            => 'badge-green',
        'reschedule_requested'  => 'badge-amber',
        'canceled_by_candidate' => 'badge-red',
    ];

    /** Badge jawaban tampil bila pelamar sudah menjawab, atau undangan bertombol sudah terkirim. */
    public static function showsResponse(array $interview): bool
    {
        return $interview['response_status'] !== 'pending'
            ? $interview['status'] === 'scheduled' || $interview['response_status'] === 'canceled_by_candidate'
            : $interview['status'] === 'scheduled' && ! empty($interview['invited_at']);
    }

    /** Konfirmasi hadir — dan pembatalan oleh pelamar — dibuka sampai jam interview. */
    public static function canConfirm(array $interview): bool
    {
        return $interview['status'] === 'scheduled' && strtotime($interview['scheduled_at']) > time();
    }

    /** Minta jadwal ulang paling lambat H-1: hari ini harus sebelum hari interview. */
    public static function canReschedule(array $interview): bool
    {
        return $interview['status'] === 'scheduled'
            && date('Y-m-d') < date('Y-m-d', strtotime($interview['scheduled_at']));
    }

    /**
     * Kolom jawaban dikosongkan. Bila jadwal berubah ($newLink), tautan di email
     * lama ikut dimatikan; bila jadwal tetap (mis. jadwal ulang ditolak), tautan
     * lama tetap berlaku.
     */
    public static function freshResponse(bool $newLink = true): array
    {
        $data = [
            'response_status'        => 'pending',
            'responded_at'           => null,
            'reschedule_proposed_at' => null,
            'response_reason'        => null,
        ];

        return $newLink ? $data + ['response_nonce' => \App\Libraries\InterviewLink::newNonce()] : $data;
    }

    /** Pastikan jadwal punya nonce (jadwal lama dibuat sebelum fitur jawaban) sebelum tautannya dipakai. */
    public function withNonce(array $interview): array
    {
        if (empty($interview['response_nonce'])) {
            $interview['response_nonce'] = \App\Libraries\InterviewLink::newNonce();
            $this->update((int) $interview['id'], ['response_nonce' => $interview['response_nonce']]);
        }

        return $interview;
    }

    /**
     * Jawaban pelamar yang perlu dilihat HR di dashboard: permintaan jadwal
     * ulang yang menunggu keputusan, dan pembatalan untuk jadwal yang belum lewat.
     */
    public function needingAttention(?int $unitId = null): array
    {
        $builder = $this->detailed()
            ->groupStart()
                ->groupStart()
                    ->where('interviews.status', 'scheduled')
                    ->where('interviews.response_status', 'reschedule_requested')
                ->groupEnd()
                ->orGroupStart()
                    ->where('interviews.response_status', 'canceled_by_candidate')
                    ->where('interviews.scheduled_at >=', date('Y-m-d 00:00:00'))
                ->groupEnd()
            ->groupEnd()
            ->orderBy('interviews.responded_at', 'ASC');

        if ($unitId !== null) {
            $builder->where('jobs.unit_id', $unitId);
        }

        return $builder->findAll();
    }

    /** Jadwal + siapa yang diwawancara dan untuk lowongan apa. */
    public function detailed()
    {
        return $this->select('interviews.*,
                candidates.full_name, candidates.email, candidates.phone,
                jobs.title AS job_title, jobs.unit_id, units.name AS unit_name,
                applications.id AS application_id')
            ->join('applications', 'applications.id = interviews.application_id')
            ->join('candidates', 'candidates.id = applications.candidate_id')
            ->join('jobs', 'jobs.id = applications.job_id')
            ->join('units', 'units.id = jobs.unit_id', 'left');
    }

    public function forApplication(int $applicationId): array
    {
        return $this->where('application_id', $applicationId)
            ->orderBy('scheduled_at', 'ASC')->findAll();
    }

    /** Putaran berikutnya untuk lamaran ini (round 1, 2, 3, ...). */
    public function nextRound(int $applicationId): int
    {
        $last = $this->selectMax('round')->where('application_id', $applicationId)->first();

        return (int) ($last['round'] ?? 0) + 1;
    }

    /** Agenda rentang tanggal, untuk kalender & dashboard. */
    public function between(string $from, string $to, ?int $unitId = null, ?int $userId = null): array
    {
        $builder = $this->detailed()
            ->where('interviews.scheduled_at >=', $from)
            ->where('interviews.scheduled_at <=', $to)
            ->orderBy('interviews.scheduled_at', 'ASC');

        if ($unitId !== null) {
            $builder->where('jobs.unit_id', $unitId);
        }

        if ($userId !== null) {
            $builder->join('interview_participants ip', 'ip.interview_id = interviews.id')
                ->where('ip.user_id', $userId);
        }

        return $builder->findAll();
    }

    /** Jadwal yang perlu diingatkan: masih terjadwal dan jatuh dalam N jam ke depan. */
    public function dueForReminder(int $hours): array
    {
        return $this->detailed()
            ->where('interviews.status', 'scheduled')
            ->where('interviews.scheduled_at >=', date('Y-m-d H:i:s'))
            ->where('interviews.scheduled_at <=', date('Y-m-d H:i:s', strtotime("+{$hours} hours")))
            ->findAll();
    }
}
