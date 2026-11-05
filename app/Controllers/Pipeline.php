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
        $slug   = (string) $this->request->getGet('stage');

        $stages = model(StageModel::class)->active();

        /* Dari kartu tahap di dashboard: papan hanya menampilkan tahap itu. */
        $stageFilter = null;
        foreach ($stages as $stage) {
            if ($slug !== '' && $stage['slug'] === $slug) {
                $stageFilter = $stage;
            }
        }

        $builder = model(ApplicationModel::class)->detailed()
            ->where('applications.status', 'active');
        $builder = model(ApplicationModel::class)->scopeUnit($builder, $unitId);
        if ($jobId) {
            $builder->where('applications.job_id', $jobId);
        }
        if ($stageFilter) {
            $builder->where('applications.stage_id', $stageFilter['id']);
            $stages = [$stageFilter];
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
            'jobs'    => model(JobModel::class)->withStats($unitId)->where('(SELECT COUNT(*) FROM applications a WHERE a.job_id = jobs.id AND a.deleted_at IS NULL) > 0', null, false)->orderBy('jobs.title', 'ASC')->findAll(),
            'jobId'   => $jobId,
            'stageFilter' => $stageFilter,
        ]);
    }
}
