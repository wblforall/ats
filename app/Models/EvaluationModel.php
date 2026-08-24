<?php

namespace App\Models;

use CodeIgniter\Model;

class EvaluationModel extends Model
{
    protected $table         = 'evaluations';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = [
        'interview_id', 'user_id', 'score_technical', 'score_communication',
        'score_attitude', 'score_experience', 'score_culture', 'overall',
        'recommendation', 'strengths', 'weaknesses', 'notes',
    ];
    protected $useTimestamps = true;

    protected $validationRules = [
        'interview_id'   => 'required|is_natural_no_zero',
        'user_id'        => 'required|is_natural_no_zero',
        'recommendation' => 'required|in_list[strong_yes,yes,neutral,no,strong_no]',
    ];

    protected $beforeInsert = ['computeOverall'];
    protected $beforeUpdate = ['computeOverall'];

    public const ASPECTS = [
        'score_technical'     => 'Kemampuan Teknis',
        'score_communication' => 'Komunikasi',
        'score_attitude'      => 'Sikap & Kedisiplinan',
        'score_experience'    => 'Pengalaman Relevan',
        'score_culture'       => 'Kecocokan Budaya',
    ];

    public const RECOMMENDATIONS = [
        'strong_yes' => 'Sangat Direkomendasikan',
        'yes'        => 'Direkomendasikan',
        'neutral'    => 'Netral',
        'no'         => 'Tidak Direkomendasikan',
        'strong_no'  => 'Sangat Tidak Direkomendasikan',
    ];

    /** Nilai akhir = rata-rata aspek yang diisi; aspek kosong tidak menurunkan skor. */
    protected function computeOverall(array $data): array
    {
        $scores = [];
        foreach (array_keys(self::ASPECTS) as $aspect) {
            $value = $data['data'][$aspect] ?? null;
            if ($value !== null && $value !== '') {
                $scores[] = (float) $value;
            }
        }

        $data['data']['overall'] = $scores === [] ? null : round(array_sum($scores) / count($scores), 2);

        return $data;
    }

    public function forInterview(int $interviewId): array
    {
        return $this->select('evaluations.*, users.name AS user_name, users.position')
            ->join('users', 'users.id = evaluations.user_id')
            ->where('interview_id', $interviewId)
            ->orderBy('evaluations.created_at', 'ASC')
            ->findAll();
    }

    public function findMine(int $interviewId, int $userId): ?array
    {
        return $this->where('interview_id', $interviewId)->where('user_id', $userId)->first();
    }

    /** Semua penilaian sepanjang proses satu lamaran, untuk halaman detail. */
    public function forApplication(int $applicationId): array
    {
        return $this->select('evaluations.*, users.name AS user_name, users.position,
                interviews.round, interviews.title AS interview_title, interviews.scheduled_at')
            ->join('interviews', 'interviews.id = evaluations.interview_id')
            ->where('interviews.application_id', $applicationId)
            ->where('interviews.deleted_at', null)
            ->orderBy('interviews.round', 'ASC')
            ->findAll();
    }

    /** Simpan penilaian (satu pewawancara satu form per jadwal) lalu segarkan rata-rata kandidat. */
    public function submit(int $interviewId, int $userId, array $data, int $applicationId): bool
    {
        $data['interview_id'] = $interviewId;
        $data['user_id']      = $userId;

        $existing = $this->findMine($interviewId, $userId);
        $saved    = $existing ? $this->update($existing['id'], $data) : (bool) $this->insert($data);

        if ($saved) {
            model(ApplicationModel::class)->refreshAvgScore($applicationId);
        }

        return $saved;
    }
}
