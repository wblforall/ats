<?php

namespace App\Controllers;

use App\Models\ApplicationModel;
use App\Models\DepartmentModel;
use App\Models\JobModel;
use App\Models\StageModel;
use App\Models\UnitModel;

/** Kelola lowongan: daftar, buat, ubah, terbitkan, tutup. */
class Jobs extends BaseController
{
    public function index()
    {
        $unitId = $this->scopeUnitId();
        $status = (string) $this->request->getGet('status');
        $q      = trim((string) $this->request->getGet('q'));

        $jobs = model(JobModel::class)->withStats($unitId);

        if ($status !== '' && isset(JobModel::STATUSES[$status])) {
            $jobs->where('jobs.status', $status);
        }
        if ($q !== '') {
            $jobs->groupStart()
                ->like('jobs.title', $q)
                ->orLike('jobs.code', $q)
                ->orLike('departments.name', $q)
                ->groupEnd();
        }

        return $this->render('jobs/index', [
            'title'   => 'Lowongan',
            'active'  => 'jobs',
            'jobs'    => $jobs->orderBy('jobs.created_at', 'DESC')->findAll(),
            'filters' => ['status' => $status, 'q' => $q],
        ]);
    }

    /** Peran yang boleh membuat & mengubah lowongan. */
    private function canWrite(): bool
    {
        return $this->isRole('superadmin', 'hr_admin', 'hr_staff');
    }

    public function create()
    {
        if (! $this->canWrite()) {
            return redirect()->to(site_url('lowongan'))->with('error', 'Anda tidak berhak membuat lowongan.');
        }

        return $this->render('jobs/form', [
            'title'       => 'Lowongan Baru',
            'active'      => 'jobs',
            'job'         => null,
            'units'       => $this->allowedUnits(),
            'departments' => model(DepartmentModel::class)->allActive($this->scopeUnitId()),
        ]);
    }

    public function edit(int $id)
    {
        if (! $this->canWrite()) {
            return redirect()->to(site_url('lowongan'))->with('error', 'Anda tidak berhak mengubah lowongan.');
        }

        $job = $this->findScoped($id);
        if (! $job) {
            return redirect()->to(site_url('lowongan'))->with('error', 'Lowongan tidak ditemukan.');
        }

        return $this->render('jobs/form', [
            'title'       => 'Ubah ' . $job['title'],
            'active'      => 'jobs',
            'job'         => $job,
            'units'       => $this->allowedUnits(),
            'departments' => model(DepartmentModel::class)->allActive($this->scopeUnitId()),
        ]);
    }

    /*
     * Penjagaan peran diulang di store() dan update(), bukan hanya di halaman
     * formulirnya. Menyembunyikan tombol tidak menghalangi siapa pun mengirim
     * POST langsung ke alamat ini.
     */
    public function store()
    {
        if (! $this->canWrite()) {
            return redirect()->to(site_url('lowongan'))->with('error', 'Anda tidak berhak membuat lowongan.');
        }

        return $this->save(null);
    }

    public function update(int $id)
    {
        if (! $this->canWrite()) {
            return redirect()->to(site_url('lowongan'))->with('error', 'Anda tidak berhak mengubah lowongan.');
        }

        if (! $this->findScoped($id)) {
            return redirect()->to(site_url('lowongan'))->with('error', 'Lowongan tidak ditemukan.');
        }

        return $this->save($id);
    }

    /**
     * Simpan lowongan baru maupun perubahan. Kode & slug dibuat sekali saat
     * lowongan lahir dan tidak pernah berubah setelahnya — slug sudah dipakai
     * sebagai URL publik, jadi mengubahnya akan mematikan tautan yang beredar.
     */
    private function save(?int $id)
    {
        $jobs = model(JobModel::class);

        $unitId = (int) $this->request->getPost('unit_id');
        if (! $this->isRole('superadmin') && $this->scopeUnitId() !== null && $unitId !== $this->scopeUnitId()) {
            return redirect()->back()->withInput()->with('error', 'Anda hanya boleh membuat lowongan untuk unit Anda sendiri.');
        }

        /* Departemen terkunci pada unitnya. Penyaringan dropdown di sisi
           browser hanya membantu pengguna; yang benar-benar menahan adalah
           pemeriksaan ini, karena POST bisa dikirim langsung tanpa formulir. */
        $departmentId = (int) $this->request->getPost('department_id') ?: null;
        if ($departmentId !== null && ! model(DepartmentModel::class)->belongsToUnit($departmentId, $unitId)) {
            return redirect()->back()->withInput()
                ->with('error', 'Departemen yang dipilih bukan milik unit tersebut.');
        }

        $data = [
            'unit_id'         => $unitId,
            'title'           => trim((string) $this->request->getPost('title')),
            'department_id'   => $departmentId,
            'employment_type' => $this->request->getPost('employment_type'),
            'level'           => $this->request->getPost('level') ?: null,
            'work_location'   => $this->request->getPost('work_location') ?: null,
            'openings'        => (int) $this->request->getPost('openings') ?: 1,
            'description'     => $this->request->getPost('description') ?: null,
            'requirements'    => $this->request->getPost('requirements') ?: null,
            'benefits'        => $this->request->getPost('benefits') ?: null,
            'salary_min'      => $this->amount($this->request->getPost('salary_min')),
            'salary_max'      => $this->amount($this->request->getPost('salary_max')),
            'salary_visible'  => $this->request->getPost('salary_visible') ? 1 : 0,
            'status'          => $this->request->getPost('status') ?: 'draft',
            'closing_date'    => $this->request->getPost('closing_date') ?: null,
        ];

        if ($id === null) {
            $unitCode     = model(UnitModel::class)->find($unitId)['code'] ?? 'JOB';
            $data['code'] = $jobs->generateCode($unitCode);
            $data['slug'] = $jobs->makeSlug($data['title'], $data['code']);
            $data['created_by'] = $this->auth['id'];
        }

        /* published_at menandai kapan lowongan mulai tayang; diisi sekali saat
           status pertama kali menjadi "open" supaya urutan di portal stabil. */
        $existing = $id ? $jobs->find($id) : null;
        if ($data['status'] === 'open' && ($existing === null || $existing['published_at'] === null)) {
            $data['published_at'] = date('Y-m-d H:i:s');
        }

        if (! $jobs->save($id ? ['id' => $id] + $data : $data)) {
            return redirect()->back()->withInput()->with('errors', $jobs->errors());
        }

        $newId = $id ?? $jobs->getInsertID();

        return redirect()->to(site_url('lowongan/' . $newId))
            ->with('success', $id ? 'Lowongan diperbarui.' : 'Lowongan dibuat dengan kode ' . $data['code'] . '.');
    }

    /** Detail lowongan + ringkasan pelamarnya per tahap. */
    public function show(int $id)
    {
        $job = $this->findScoped($id);
        if (! $job) {
            return redirect()->to(site_url('lowongan'))->with('error', 'Lowongan tidak ditemukan.');
        }

        $apps = model(ApplicationModel::class);

        return $this->render('jobs/show', [
            'title'      => $job['title'],
            'active'     => 'jobs',
            'job'        => $job,
            'stageList'  => model(StageModel::class)->active(),
            'perStage'   => $apps->countByStage($this->scopeUnitId(), $id),
            'applicants' => $apps->detailed()->where('applications.job_id', $id)
                ->orderBy('applications.applied_at', 'DESC')->findAll(),
        ]);
    }

    public function destroy(int $id)
    {
        if (! $this->isManager()) {
            return redirect()->to(site_url('lowongan'))->with('error', 'Hanya Admin HRD yang boleh menghapus lowongan.');
        }

        $job = $this->findScoped($id);
        if (! $job) {
            return redirect()->to(site_url('lowongan'))->with('error', 'Lowongan tidak ditemukan.');
        }

        /* Lowongan yang sudah punya pelamar tidak dihapus, hanya ditutup —
           menghapusnya akan ikut membuang riwayat lamaran orang. */
        $count = model(ApplicationModel::class)->where('job_id', $id)->countAllResults();
        if ($count > 0) {
            model(JobModel::class)->update($id, ['status' => 'closed']);

            return redirect()->to(site_url('lowongan'))
                ->with('info', "Lowongan sudah punya {$count} pelamar, jadi ditutup (bukan dihapus) agar riwayat lamaran tetap utuh.");
        }

        model(JobModel::class)->delete($id);

        return redirect()->to(site_url('lowongan'))->with('success', 'Lowongan dihapus.');
    }

    /**
     * Nominal diketik dengan pemisah ribuan (4.000.000). Titiknya dibuang di
     * sini agar yang tersimpan tetap angka murni, sekaligus jaring pengaman
     * bila JavaScript pembersih di sisi browser tidak jalan.
     */
    private function amount($raw): ?string
    {
        $digits = preg_replace('/[^0-9]/', '', (string) $raw);

        return ($digits === '' || $digits === null) ? null : $digits;
    }

    /** Ambil lowongan yang boleh diakses user ini; null bila di luar unitnya. */
    private function findScoped(int $id): ?array
    {
        $job = model(JobModel::class)->withStats($this->scopeUnitId())->where('jobs.id', $id)->first();

        return $job ?: null;
    }

    /** Unit yang boleh dipilih: semua untuk superadmin, satu untuk lainnya. */
    private function allowedUnits(): array
    {
        $units = model(UnitModel::class);

        if ($this->scopeUnitId() === null) {
            return $units->active();
        }

        $unit = $units->find($this->scopeUnitId());

        return $unit ? [$unit] : [];
    }
}
