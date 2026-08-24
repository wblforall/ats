<?php

namespace App\Controllers;

use App\Models\ApplicationHistoryModel;
use App\Models\ApplicationModel;
use App\Models\JobModel;
use App\Models\StageModel;

/** Laporan rekrutmen: corong pipeline, lama proses per tahap, ringkasan hasil. */
class Reports extends BaseController
{
    public function index()
    {
        $unitId = $this->scopeUnitId();
        $apps   = model(ApplicationModel::class);

        /* Corong: berapa lamaran yang PERNAH menyentuh tiap tahap (dari
           riwayat), bukan hanya yang sedang di sana — itulah gambaran corong. */
        $stages = model(StageModel::class)->active();
        $funnel = $this->funnel($unitId);

        /* Ringkasan hasil sepanjang tahun berjalan. */
        $summary = $this->summary($unitId);

        return $this->render('reports/index', [
            'title'     => 'Laporan',
            'active'    => 'reports',
            'stages'    => $stages,
            'funnel'    => $funnel,
            'perStage'  => $apps->countByStage($unitId),
            'avgDays'   => model(ApplicationHistoryModel::class)->avgDaysPerStage($unitId),
            'summary'   => $summary,
            'perJob'    => $this->perJob($unitId),
        ]);
    }

    /** Jumlah lamaran unik yang pernah mencapai tiap tahap. */
    private function funnel(?int $unitId): array
    {
        $sql = 'SELECT h.to_stage_id AS stage_id, COUNT(DISTINCT h.application_id) AS total
                FROM application_history h
                JOIN applications a ON a.id = h.application_id
                JOIN jobs j ON j.id = a.job_id
                WHERE a.deleted_at IS NULL';
        $params = [];
        if ($unitId !== null) { $sql .= ' AND j.unit_id = ?'; $params[] = $unitId; }
        $sql .= ' GROUP BY h.to_stage_id';

        return array_column(db_connect()->query($sql, $params)->getResultArray(), 'total', 'stage_id');
    }

    private function summary(?int $unitId): array
    {
        $b = fn () => model(ApplicationModel::class)->join('jobs', 'jobs.id = applications.job_id');
        $scope = fn ($q) => $unitId === null ? $q : $q->where('jobs.unit_id', $unitId);

        $yearStart = date('Y-01-01 00:00:00');

        return [
            'masuk'    => $scope($b()->where('applications.applied_at >=', $yearStart))->countAllResults(),
            'aktif'    => $scope($b()->where('applications.status', 'active'))->countAllResults(),
            'diterima' => $scope($b()->where('applications.status', 'hired')->where('applications.closed_at >=', $yearStart))->countAllResults(),
            'ditolak'  => $scope($b()->where('applications.status', 'rejected')->where('applications.closed_at >=', $yearStart))->countAllResults(),
        ];
    }

    /** Rekap pelamar & diterima per lowongan. */
    private function perJob(?int $unitId): array
    {
        $sql = "SELECT j.title, u.name AS unit_name, j.openings,
                       COUNT(a.id) AS pelamar,
                       SUM(a.status = 'hired') AS diterima
                FROM jobs j
                LEFT JOIN applications a ON a.job_id = j.id AND a.deleted_at IS NULL
                LEFT JOIN units u ON u.id = j.unit_id
                WHERE j.deleted_at IS NULL";
        $params = [];
        if ($unitId !== null) { $sql .= ' AND j.unit_id = ?'; $params[] = $unitId; }
        $sql .= ' GROUP BY j.id, j.title, u.name, j.openings ORDER BY pelamar DESC';

        return db_connect()->query($sql, $params)->getResultArray();
    }
}
