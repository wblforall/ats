<?php

namespace App\Controllers;

use App\Models\ApplicationModel;
use App\Models\InterviewModel;
use App\Models\JobModel;
use App\Models\StageModel;

/** Ringkasan harian tim HR: angka pipeline, pelamar baru, agenda terdekat. */
class Dashboard extends BaseController
{
    public function index()
    {
        $unitId = $this->scopeUnitId();

        $apps   = model(ApplicationModel::class);
        $jobs   = model(JobModel::class);
        $stages = model(StageModel::class);

        $perStage   = $apps->countByStage($unitId);
        $stageList  = $stages->active();
        $hiredStage = array_values(array_filter($stageList, static fn ($s) => $s['kind'] === 'hired'));

        /* Pelamar bulan ini vs bulan lalu — dipakai sebagai pembanding tren. */
        $thisMonth = $this->countApplications($unitId, date('Y-m-01 00:00:00'), date('Y-m-d 23:59:59'));
        $lastMonth = $this->countApplications(
            $unitId,
            date('Y-m-01 00:00:00', strtotime('first day of last month')),
            date('Y-m-t 23:59:59', strtotime('last day of last month'))
        );

        $hiredThisYear = $apps->join('jobs', 'jobs.id = applications.job_id')
            ->where('applications.status', 'hired')
            ->where('applications.closed_at >=', date('Y-01-01 00:00:00'));
        if ($unitId !== null) {
            $hiredThisYear->where('jobs.unit_id', $unitId);
        }
        $hiredCount = $hiredThisYear->countAllResults();

        $recent = $apps->detailed()
            ->where('applications.status', 'active');
        if ($unitId !== null) {
            $recent->where('jobs.unit_id', $unitId);
        }
        $recent = $recent->orderBy('applications.applied_at', 'DESC')
            ->orderBy('applications.id', 'DESC')
            ->findAll(8);

        $agenda = model(InterviewModel::class)->between(
            date('Y-m-d H:i:s'),
            date('Y-m-d 23:59:59', strtotime('+7 days')),
            $unitId,
            $this->isRole('interviewer') ? $this->auth['id'] : null
        );

        return $this->render('dashboard/index', [
            'title'       => 'Dashboard',
            'active'      => 'dashboard',
            'stageList'   => $stageList,
            'perStage'    => $perStage,
            'activeTotal' => array_sum($perStage),
            'thisMonth'   => $thisMonth,
            'lastMonth'   => $lastMonth,
            'hiredCount'  => $hiredCount,
            'hiredStage'  => $hiredStage[0] ?? null,
            'recent'      => $recent,
            'agenda'      => array_slice($agenda, 0, 6),
            'openJobs'    => $jobs->withStats($unitId)->where('jobs.status', 'open')
                ->orderBy('applicant_count', 'DESC')->findAll(5),
        ]);
    }

    private function countApplications(?int $unitId, string $from, string $to): int
    {
        $builder = model(ApplicationModel::class)
            ->join('jobs', 'jobs.id = applications.job_id')
            ->where('applications.applied_at >=', $from)
            ->where('applications.applied_at <=', $to);

        if ($unitId !== null) {
            $builder->where('jobs.unit_id', $unitId);
        }

        return $builder->countAllResults();
    }
}
