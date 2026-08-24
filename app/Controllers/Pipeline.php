<?php

namespace App\Controllers;

use App\Models\ApplicationModel;
use App\Models\JobModel;
use App\Models\StageModel;

/**
 * Papan pipeline: lamaran aktif tersusun per kolom tahap. Hanya menampilkan
 * yang berstatus active — yang sudah diterima/gugur keluar dari papan kerja.
 */
class Pipeline extends BaseController
{
    public function index()
    {
        $unitId = $this->scopeUnitId();
        $jobId  = (int) $this->request->getGet('job');

        $stages = model(StageModel::class)->active();

        $builder = model(ApplicationModel::class)->detailed()
            ->where('applications.status', 'active');
        $builder = model(ApplicationModel::class)->scopeUnit($builder, $unitId);
        if ($jobId) {
            $builder->where('applications.job_id', $jobId);
        }
        $rows = $builder->orderBy('applications.applied_at', 'DESC')->findAll();

        /* Kelompokkan per stage_id supaya tampilan tinggal menata kolom. */
        $byStage = [];
        foreach ($stages as $stage) {
            $byStage[$stage['id']] = [];
        }
        foreach ($rows as $row) {
            $byStage[$row['stage_id']][] = $row;
        }

        return $this->render('pipeline/index', [
            'title'   => 'Pipeline',
            'active'  => 'pipeline',
            'stages'  => $stages,
            'byStage' => $byStage,
            'total'   => count($rows),
            'jobs'    => model(JobModel::class)->withStats($unitId)->orderBy('jobs.title', 'ASC')->findAll(),
            'jobId'   => $jobId,
        ]);
    }
}
