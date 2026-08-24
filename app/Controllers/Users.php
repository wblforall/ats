<?php

namespace App\Controllers;

use App\Models\UnitModel;
use App\Models\UserModel;

/**
 * Master pengguna internal. hr_admin mengelola user di unitnya; superadmin
 * semua unit dan satu-satunya yang boleh membuat superadmin lain.
 */
class Users extends BaseController
{
    public function index()
    {
        $unitId = $this->scopeUnitId();

        return $this->render('users/index', [
            'title'  => 'Pengguna',
            'active' => 'users',
            'users'  => model(UserModel::class)->withUnit($unitId)->orderBy('users.name', 'ASC')->findAll(),
            'units'  => $this->allowedUnits(),
            'roles'  => $this->allowedRoles(),
        ]);
    }

    public function create()
    {
        return $this->render('users/form', [
            'title'  => 'Pengguna Baru',
            'active' => 'users',
            'user'   => null,
            'units'  => $this->allowedUnits(),
            'roles'  => $this->allowedRoles(),
        ]);
    }

    public function edit(int $id)
    {
        $user = $this->findScoped($id);
        if (! $user) {
            return redirect()->to(site_url('master/pengguna'))->with('error', 'Pengguna tidak ditemukan.');
        }

        return $this->render('users/form', [
            'title'  => 'Ubah ' . $user['name'],
            'active' => 'users',
            'user'   => $user,
            'units'  => $this->allowedUnits(),
            'roles'  => $this->allowedRoles(),
        ]);
    }

    public function store()
    {
        return $this->save(null);
    }

    public function update(int $id)
    {
        if (! $this->findScoped($id)) {
            return redirect()->to(site_url('master/pengguna'))->with('error', 'Pengguna tidak ditemukan.');
        }

        return $this->save($id);
    }

    private function save(?int $id)
    {
        $users = model(UserModel::class);

        $role   = (string) $this->request->getPost('role');
        $unitId = $this->request->getPost('unit_id') !== '' ? (int) $this->request->getPost('unit_id') : null;

        /* Batasi peran & unit yang boleh diberikan sesuai wewenang pembuat. */
        if (! array_key_exists($role, $this->allowedRoles())) {
            return redirect()->back()->withInput()->with('error', 'Peran itu di luar wewenang Anda.');
        }
        if ($this->scopeUnitId() !== null) {
            $unitId = $this->scopeUnitId(); // hr_admin unit: user selalu di unitnya
        }

        $data = [
            'unit_id'  => $role === 'superadmin' ? null : $unitId,
            'name'     => trim((string) $this->request->getPost('name')),
            'email'    => strtolower(trim((string) $this->request->getPost('email'))),
            'role'     => $role,
            'position' => $this->request->getPost('position') ?: null,
            'phone'    => $this->request->getPost('phone') ?: null,
            'is_active' => $this->request->getPost('is_active') ? 1 : 0,
        ];

        $password = (string) $this->request->getPost('password');
        if ($id === null && $password === '') {
            return redirect()->back()->withInput()->with('error', 'Kata sandi wajib diisi untuk pengguna baru.');
        }
        if ($password !== '') {
            $data['password'] = $password; // di-hash otomatis oleh model
        }

        $saved = $id ? $users->update($id, $data) : $users->insert($data);
        if (! $saved) {
            return redirect()->back()->withInput()->with('errors', $users->errors());
        }

        return redirect()->to(site_url('master/pengguna'))
            ->with('success', $id ? 'Pengguna diperbarui.' : 'Pengguna ditambahkan.');
    }

    public function destroy(int $id)
    {
        $user = $this->findScoped($id);
        if (! $user) {
            return redirect()->to(site_url('master/pengguna'))->with('error', 'Pengguna tidak ditemukan.');
        }
        if ((int) $id === (int) $this->auth['id']) {
            return redirect()->back()->with('error', 'Anda tidak bisa menghapus akun Anda sendiri.');
        }

        /* Soft delete: jejak (penggeser tahap, penjadwal interview) tetap utuh. */
        model(UserModel::class)->delete($id);

        return redirect()->to(site_url('master/pengguna'))->with('success', 'Pengguna dinonaktifkan.');
    }

    /** Peran yang boleh diberikan: superadmin bisa semua; hr_admin tak bisa buat superadmin. */
    private function allowedRoles(): array
    {
        $all = [
            'hr_staff'    => 'Staf HRD',
            'interviewer' => 'Pewawancara',
            'hr_admin'    => 'Admin HRD',
            'superadmin'  => 'Super Admin',
        ];

        return $this->isRole('superadmin') ? $all
            : array_intersect_key($all, array_flip(['hr_staff', 'interviewer', 'hr_admin']));
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

    private function findScoped(int $id): ?array
    {
        $user = model(UserModel::class)->find($id);
        if (! $user) {
            return null;
        }
        $scope = $this->scopeUnitId();

        return ($scope === null || (int) $user['unit_id'] === $scope) ? $user : null;
    }
}
