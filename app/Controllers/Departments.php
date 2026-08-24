<?php

namespace App\Controllers;

use App\Models\DepartmentModel;
use App\Models\JobModel;
use App\Models\UnitModel;

/**
 * Master departemen. Setiap departemen terikat pada satu unit usaha dan tidak
 * bisa dipindah setelah dibuat — memindahkannya akan membuat lowongan lama
 * yang memakainya seolah milik unit lain.
 */
class Departments extends BaseController
{
    public function index()
    {
        $unitId = $this->scopeUnitId();

        return $this->render('departments/index', [
            'title'       => 'Departemen',
            'active'      => 'departments',
            'departments' => model(DepartmentModel::class)->withUsage($unitId)->findAll(),
            'units'       => $this->allowedUnits(),
        ]);
    }

    public function store()
    {
        if (! $this->isManager()) {
            return redirect()->to(site_url('master/departemen'))
                ->with('error', 'Hanya Admin HRD yang boleh mengubah master departemen.');
        }

        $unitId = (int) $this->request->getPost('unit_id');
        $name   = trim((string) $this->request->getPost('name'));

        if ($error = $this->guard($unitId, $name)) {
            return redirect()->back()->withInput()->with('error', $error);
        }

        $departments = model(DepartmentModel::class);

        if ($departments->isDuplicate($unitId, $name)) {
            return redirect()->back()->withInput()
                ->with('error', "Departemen \"{$name}\" sudah ada di unit tersebut.");
        }

        if (! $departments->insert(['unit_id' => $unitId, 'name' => $name, 'is_active' => 1])) {
            return redirect()->back()->withInput()->with('errors', $departments->errors());
        }

        return redirect()->to(site_url('master/departemen'))
            ->with('success', "Departemen \"{$name}\" ditambahkan.");
    }

    public function update(int $id)
    {
        if (! $this->isManager()) {
            return redirect()->to(site_url('master/departemen'))
                ->with('error', 'Hanya Admin HRD yang boleh mengubah master departemen.');
        }

        $departments = model(DepartmentModel::class);
        $department  = $this->findScoped($id);

        if (! $department) {
            return redirect()->to(site_url('master/departemen'))->with('error', 'Departemen tidak ditemukan.');
        }

        $name = trim((string) $this->request->getPost('name'));

        if ($name === '') {
            return redirect()->back()->with('error', 'Nama departemen wajib diisi.');
        }

        if ($departments->isDuplicate((int) $department['unit_id'], $name, $id)) {
            return redirect()->back()->with('error', "Departemen \"{$name}\" sudah ada di unit tersebut.");
        }

        /* unit_id sengaja tidak ikut diubah: memindahkan departemen antar unit
           akan menyeret lowongan lama yang memakainya ke unit yang salah. */
        $departments->update($id, [
            'name'      => $name,
            'is_active' => $this->request->getPost('is_active') ? 1 : 0,
        ]);

        return redirect()->to(site_url('master/departemen'))->with('success', 'Departemen diperbarui.');
    }

    public function destroy(int $id)
    {
        if (! $this->isManager()) {
            return redirect()->to(site_url('master/departemen'))
                ->with('error', 'Hanya Admin HRD yang boleh mengubah master departemen.');
        }

        $department = $this->findScoped($id);

        if (! $department) {
            return redirect()->to(site_url('master/departemen'))->with('error', 'Departemen tidak ditemukan.');
        }

        /* Departemen yang masih dipakai lowongan dinonaktifkan saja, bukan
           dihapus — menghapusnya membuat lowongan lama kehilangan keterangan
           departemennya tanpa jejak. */
        $terpakai = model(JobModel::class)->withDeleted()->where('department_id', $id)->countAllResults();

        if ($terpakai > 0) {
            model(DepartmentModel::class)->update($id, ['is_active' => 0]);

            return redirect()->to(site_url('master/departemen'))
                ->with('info', "Departemen ini dipakai {$terpakai} lowongan, jadi dinonaktifkan (bukan dihapus) agar data lowongan tetap utuh.");
        }

        model(DepartmentModel::class)->delete($id);

        return redirect()->to(site_url('master/departemen'))->with('success', 'Departemen dihapus.');
    }

    /** Pemeriksaan bersama untuk penambahan: unit sah dan boleh diakses. */
    private function guard(int $unitId, string $name): ?string
    {
        if ($unitId <= 0) {
            return 'Unit usaha wajib dipilih.';
        }

        if ($name === '') {
            return 'Nama departemen wajib diisi.';
        }

        $scope = $this->scopeUnitId();
        if ($scope !== null && $unitId !== $scope) {
            return 'Anda hanya boleh mengelola departemen unit Anda sendiri.';
        }

        if (! model(UnitModel::class)->find($unitId)) {
            return 'Unit usaha tidak ditemukan.';
        }

        return null;
    }

    private function findScoped(int $id): ?array
    {
        $department = model(DepartmentModel::class)->find($id);

        if (! $department) {
            return null;
        }

        $scope = $this->scopeUnitId();

        return ($scope === null || (int) $department['unit_id'] === $scope) ? $department : null;
    }

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
