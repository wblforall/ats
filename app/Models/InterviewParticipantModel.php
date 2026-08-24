<?php

namespace App\Models;

use CodeIgniter\Model;

class InterviewParticipantModel extends Model
{
    protected $table         = 'interview_participants';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['interview_id', 'user_id'];
    protected $useTimestamps = true;
    protected $updatedField  = '';

    public function forInterview(int $interviewId): array
    {
        return $this->select('interview_participants.*, users.name AS user_name, users.email, users.position')
            ->join('users', 'users.id = interview_participants.user_id')
            ->where('interview_id', $interviewId)
            ->orderBy('users.name', 'ASC')
            ->findAll();
    }

    public function idsFor(int $interviewId): array
    {
        return array_map('intval', array_column(
            $this->where('interview_id', $interviewId)->findAll(),
            'user_id'
        ));
    }

    /** Ganti seluruh daftar pewawancara satu jadwal dengan daftar baru. */
    public function sync(int $interviewId, array $userIds): void
    {
        $userIds = array_values(array_unique(array_map('intval', array_filter($userIds))));

        $this->where('interview_id', $interviewId)->delete();

        if ($userIds === []) {
            return;
        }

        $now = date('Y-m-d H:i:s');
        $this->insertBatch(array_map(
            static fn (int $uid) => ['interview_id' => $interviewId, 'user_id' => $uid, 'created_at' => $now],
            $userIds
        ));
    }

    public function isAssigned(int $interviewId, int $userId): bool
    {
        return $this->where('interview_id', $interviewId)->where('user_id', $userId)->countAllResults() > 0;
    }
}
