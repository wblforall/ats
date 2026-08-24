<?php

namespace App\Models;

use CodeIgniter\Model;

class ApplicationModel extends Model
{
    protected $table          = 'applications';
    protected $primaryKey     = 'id';
    protected $returnType     = 'array';
    protected $useSoftDeletes = true;
    protected $allowedFields  = [
        'candidate_id', 'job_id', 'stage_id', 'status', 'source', 'referral_name',
        'cv_path', 'cv_original', 'cover_letter', 'expected_salary', 'available_from',
        'rating', 'avg_score', 'reject_reason', 'applied_at', 'closed_at',
    ];
    protected $useTimestamps = true;

    protected $validationRules = [
        'candidate_id' => 'required|is_natural_no_zero',
        'job_id'       => 'required|is_natural_no_zero',
        'stage_id'     => 'required|is_natural_no_zero',
        'status'       => 'permit_empty|in_list[active,hired,rejected,withdrawn]',
        'rating'       => 'permit_empty|greater_than_equal_to[1]|less_than_equal_to[5]',
    ];

    public const SOURCES = [
        'career_portal' => 'Portal Karir',
        'referral'      => 'Referensi Karyawan',
        'walk_in'       => 'Datang Langsung',
        'jobstreet'     => 'Jobstreet',
        'linkedin'      => 'LinkedIn',
        'instagram'     => 'Instagram',
        'other'         => 'Lainnya',
    ];

    public const STATUSES = [
        'active'    => 'Berjalan',
        'hired'     => 'Diterima',
        'rejected'  => 'Tidak Lolos',
        'withdrawn' => 'Mengundurkan Diri',
    ];

    /** Baris lamaran lengkap dengan data kandidat, lowongan, unit, dan tahap. */
    public function detailed()
    {
        return $this->select('applications.*,
                candidates.full_name, candidates.email, candidates.phone, candidates.city,
                candidates.gender, candidates.birth_date, candidates.address,
                candidates.education_level, candidates.school, candidates.major,
                candidates.years_experience, candidates.current_position, candidates.current_company,
                candidates.instagram, candidates.linkedin_url, candidates.portfolio_url,
                candidates.is_blacklisted, candidates.blacklist_reason,
                jobs.title AS job_title, jobs.code AS job_code, jobs.slug AS job_slug, jobs.unit_id,
                units.name AS unit_name, units.code AS unit_code,
                stages.name AS stage_name, stages.slug AS stage_slug, stages.color AS stage_color,
                stages.kind AS stage_kind, stages.sort_order AS stage_order')
            ->join('candidates', 'candidates.id = applications.candidate_id')
            ->join('jobs', 'jobs.id = applications.job_id')
            ->join('units', 'units.id = jobs.unit_id', 'left')
            ->join('stages', 'stages.id = applications.stage_id');
    }

    /** Batasi ke unit tertentu — dipanggil untuk user non-superadmin. */
    public function scopeUnit($builder, ?int $unitId)
    {
        return $unitId === null ? $builder : $builder->where('jobs.unit_id', $unitId);
    }

    public function findDetailed(int $id): ?array
    {
        return $this->detailed()->where('applications.id', $id)->first();
    }

    public function existsFor(int $candidateId, int $jobId): bool
    {
        return $this->withDeleted()
            ->where('candidate_id', $candidateId)
            ->where('job_id', $jobId)
            ->countAllResults() > 0;
    }

    /** Nomor lamaran yang ditampilkan ke pelamar, mis. WBL-2026-000123. */
    public function referenceCode(int $id, ?string $appliedAt = null): string
    {
        $year = $appliedAt ? date('Y', strtotime($appliedAt)) : date('Y');

        return 'WBL-' . $year . '-' . str_pad((string) $id, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Pindahkan lamaran ke tahap lain: status ikut kind tahap tujuan, riwayat
     * dicatat, dan closed_at diisi saat proses berakhir. Dibungkus transaksi
     * supaya riwayat tidak pernah terpisah dari posisi tahap yang tersimpan.
     */
    public function moveStage(int $applicationId, int $toStageId, ?int $userId = null, ?string $note = null, ?string $rejectReason = null): bool
    {
        $application = $this->find($applicationId);
        if (! $application) {
            return false;
        }

        $stage = model(StageModel::class)->find($toStageId);
        if (! $stage) {
            return false;
        }

        $fromStageId = (int) $application['stage_id'];
        if ($fromStageId === $toStageId && $rejectReason === null) {
            return true;
        }

        $status = model(StageModel::class)->statusForKind($stage['kind']);
        $now    = date('Y-m-d H:i:s');

        $this->db->transStart();

        $this->update($applicationId, [
            'stage_id'      => $toStageId,
            'status'        => $status,
            'reject_reason' => $status === 'rejected' ? $rejectReason : null,
            'closed_at'     => in_array($status, ['hired', 'rejected'], true) ? $now : null,
        ]);

        $this->db->table('application_history')->insert([
            'application_id' => $applicationId,
            'from_stage_id'  => $fromStageId ?: null,
            'to_stage_id'    => $toStageId,
            'user_id'        => $userId,
            'note'           => $note,
            'created_at'     => $now,
        ]);

        $this->db->transComplete();

        return $this->db->transStatus();
    }

    /** Rekap jumlah lamaran per tahap — sumber angka kolom papan pipeline. */
    public function countByStage(?int $unitId = null, ?int $jobId = null): array
    {
        $builder = $this->builder()
            ->select('stage_id, COUNT(*) AS total')
            ->join('jobs', 'jobs.id = applications.job_id')
            ->where('applications.deleted_at', null)
            ->where('applications.status', 'active')
            ->groupBy('stage_id');

        if ($unitId !== null) {
            $builder->where('jobs.unit_id', $unitId);
        }
        if ($jobId !== null) {
            $builder->where('applications.job_id', $jobId);
        }

        return array_column($builder->get()->getResultArray(), 'total', 'stage_id');
    }

    /** Hitung ulang rata-rata nilai interview kandidat dari seluruh evaluasi. */
    public function refreshAvgScore(int $applicationId): void
    {
        $row = $this->db->table('evaluations e')
            ->select('AVG(e.overall) AS avg_score')
            ->join('interviews i', 'i.id = e.interview_id')
            ->where('i.application_id', $applicationId)
            ->where('i.deleted_at', null)
            ->get()->getRowArray();

        $this->update($applicationId, ['avg_score' => $row['avg_score'] ?? null]);
    }
}
