<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * Induk seluruh controller. Menyediakan dua hal yang dipakai di mana-mana:
 * identitas user yang login ($this->auth) dan render() yang selalu mengirim
 * data shell (identitas, hitungan untuk sidebar) ke layout.
 */
abstract class BaseController extends Controller
{
    protected $helpers = ['url', 'form', 'ui'];

    /** Data user yang login; null bila belum login (halaman publik). */
    protected ?array $auth = null;

    protected $session;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        $this->session = service('session');

        if ($this->session->has('user_id')) {
            $this->auth = [
                'id'         => (int) $this->session->get('user_id'),
                'name'       => $this->session->get('user_name'),
                'email'      => $this->session->get('user_email'),
                'role'       => $this->session->get('user_role'),
                'role_label' => self::ROLE_LABELS[$this->session->get('user_role')] ?? '',
                'unit_id'    => $this->session->get('unit_id') !== null ? (int) $this->session->get('unit_id') : null,
                'unit_name'  => $this->session->get('unit_name') ?? 'Semua Unit',
            ];
        }
    }

    public const ROLE_LABELS = [
        'superadmin'  => 'Super Admin',
        'hr_admin'    => 'Admin HRD',
        'hr_staff'    => 'Staf HRD',
        'interviewer' => 'Pewawancara',
    ];

    /**
     * Unit yang boleh dilihat user ini. null = seluruh unit (superadmin, atau
     * hr_admin pusat yang memang tidak diikat ke satu unit).
     */
    protected function scopeUnitId(): ?int
    {
        return $this->auth['unit_id'] ?? null;
    }

    protected function isRole(string ...$roles): bool
    {
        return in_array($this->auth['role'] ?? '', $roles, true);
    }

    /** true bila user boleh mengubah master data & kirim email. */
    protected function isManager(): bool
    {
        return $this->isRole('superadmin', 'hr_admin');
    }

    /**
     * Render halaman admin lengkap dengan data shell. Semua controller yang
     * memakai layout utama harus lewat sini supaya sidebar & topbar konsisten.
     */
    protected function render(string $view, array $data = []): string
    {
        return view($view, array_merge($this->shellData(), $data));
    }

    /** Angka kecil yang tampil di sidebar/topbar pada setiap halaman. */
    protected function shellData(): array
    {
        $unitId = $this->scopeUnitId();

        $jobs = model(\App\Models\JobModel::class);
        $open = $jobs->where('status', 'open');
        if ($unitId !== null) {
            $open->where('unit_id', $unitId);
        }
        $openJobCount = $open->countAllResults();

        $total = $jobs->withDeleted(false);
        if ($unitId !== null) {
            $total->where('unit_id', $unitId);
        }
        $totalJobs = max(1, $total->countAllResults());

        $apps     = model(\App\Models\ApplicationModel::class);
        $firstId  = model(\App\Models\StageModel::class)->firstStage()['id'] ?? 0;
        $newCount = 0;

        if ($firstId) {
            $b = $apps->join('jobs', 'jobs.id = applications.job_id')
                ->where('applications.stage_id', $firstId)
                ->where('applications.status', 'active');
            if ($unitId !== null) {
                $b->where('jobs.unit_id', $unitId);
            }
            $newCount = $b->countAllResults();
        }

        $interviews = model(\App\Models\InterviewModel::class)->between(
            date('Y-m-d H:i:s'),
            date('Y-m-d 23:59:59', strtotime('+7 days')),
            $unitId,
            $this->isRole('interviewer') ? ($this->auth['id'] ?? null) : null
        );

        return [
            'auth'                    => $this->auth,
            'openJobCount'            => $openJobCount,
            'openJobPercent'          => (int) round($openJobCount / $totalJobs * 100),
            'newApplicantCount'       => $newCount,
            'upcomingInterviewCount'  => count($interviews),
        ];
    }
}
