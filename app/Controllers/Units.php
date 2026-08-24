<?php

namespace App\Controllers;

use App\Models\DepartmentModel;
use App\Models\JobModel;
use App\Models\UnitModel;
use App\Models\UserModel;

/**
 * Master unit usaha. Hanya superadmin yang boleh menyentuhnya: unit adalah
 * akar dari pembatasan akses — mengubahnya berarti mengubah siapa melihat apa
 * di seluruh aplikasi.
 */
class Units extends BaseController
{
    public function index()
    {
        return $this->render('units/index', [
            'title'  => 'Unit Usaha',
            'active' => 'units',
            'units'  => model(UnitModel::class)->withUsage()->findAll(),
        ]);
    }

    public function store()
    {
        $code = strtoupper(trim((string) $this->request->getPost('code')));
        $name = trim((string) $this->request->getPost('name'));

        $units = model(UnitModel::class);

        if (! $units->insert([
            'code'       => $code,
            'name'       => $name,
            'address'    => $this->request->getPost('address') ?: null,
            'is_active'  => 1,
            'sort_order' => (int) $this->request->getPost('sort_order'),
        ])) {
            return redirect()->back()->withInput()->with('errors', $units->errors());
        }

        return redirect()->to(site_url('master/unit'))
            ->with('success', "Unit \"{$name}\" ditambahkan.");
    }

    public function update(int $id)
    {
        $units = model(UnitModel::class);

        if (! $units->find($id)) {
            return redirect()->to(site_url('master/unit'))->with('error', 'Unit tidak ditemukan.');
        }

        $data = [
            'name'       => trim((string) $this->request->getPost('name')),
            'address'    => $this->request->getPost('address') ?: null,
            'sort_order' => (int) $this->request->getPost('sort_order'),
            'is_active'  => $this->request->getPost('is_active') ? 1 : 0,
        ];

        /* Kode unit sengaja tidak ikut diubah: ia jadi awalan kode lowongan
           yang sudah beredar (mis. PTC-2608-001), jadi mengubahnya membuat
           kode lama tidak lagi cocok dengan unitnya. */
        if (! $units->update($id, $data)) {
            return redirect()->back()->withInput()->with('errors', $units->errors());
        }

        return redirect()->to(site_url('master/unit'))->with('success', 'Unit diperbarui.');
    }

    public function destroy(int $id)
    {
        $unit = model(UnitModel::class)->find($id);

        if (! $unit) {
            return redirect()->to(site_url('master/unit'))->with('error', 'Unit tidak ditemukan.');
        }

        /* Unit yang masih menaungi lowongan, departemen, atau user tidak
           dihapus — hanya dinonaktifkan. Menghapusnya akan menyeret ikut
           seluruh lowongan dan departemennya (relasi CASCADE). */
        $lowongan   = model(JobModel::class)->withDeleted()->where('unit_id', $id)->countAllResults();
        $departemen = model(DepartmentModel::class)->where('unit_id', $id)->countAllResults();
        $pengguna   = model(UserModel::class)->withDeleted()->where('unit_id', $id)->countAllResults();
        $total      = $lowongan + $departemen + $pengguna;

        if ($total > 0) {
            model(UnitModel::class)->update($id, ['is_active' => 0]);

            return redirect()->to(site_url('master/unit'))->with('info', sprintf(
                'Unit ini masih dipakai (%d lowongan, %d departemen, %d pengguna), jadi dinonaktifkan bukan dihapus.',
                $lowongan,
                $departemen,
                $pengguna
            ));
        }

        model(UnitModel::class)->delete($id);

        return redirect()->to(site_url('master/unit'))->with('success', 'Unit dihapus.');
    }
}
