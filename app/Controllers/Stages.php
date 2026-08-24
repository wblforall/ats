<?php

namespace App\Controllers;

use App\Models\ApplicationModel;
use App\Models\StageModel;

/**
 * Master tahapan pipeline. Status lamaran diturunkan dari `kind` tahap, jadi
 * idealnya tetap ada tepat satu tahap ber-kind `hired` dan satu `rejected`.
 */
class Stages extends BaseController
{
    public function index()
    {
        $stages = model(StageModel::class)->orderBy('sort_order', 'ASC')->findAll();
        $counts = model(ApplicationModel::class)->countByStage($this->scopeUnitId());

        return $this->render('stages/index', [
            'title'  => 'Tahapan',
            'active' => 'stages',
            'stages' => $stages,
            'counts' => $counts,
        ]);
    }

    public function store()
    {
        $name = trim((string) $this->request->getPost('name'));
        $kind = (string) $this->request->getPost('kind');

        $stages = model(StageModel::class);
        $slug   = url_title($name, '-', true);

        /* Slug unik: bila bentrok, imbuhi angka. */
        $base = $slug; $i = 2;
        while ($stages->where('slug', $slug)->countAllResults() > 0) {
            $slug = $base . '-' . $i++;
        }

        $maxOrder = (int) ($stages->selectMax('sort_order')->first()['sort_order'] ?? 0);

        if (! $stages->insert([
            'name'       => $name,
            'slug'       => $slug,
            'kind'       => in_array($kind, ['open', 'hired', 'rejected'], true) ? $kind : 'open',
            'color'      => $this->request->getPost('color') ?: '#64748b',
            'sort_order' => $maxOrder + 1,
            'is_active'  => 1,
        ])) {
            return redirect()->back()->withInput()->with('errors', $stages->errors());
        }

        return redirect()->to(site_url('master/tahapan'))->with('success', "Tahap \"{$name}\" ditambahkan.");
    }

    public function update(int $id)
    {
        $stages = model(StageModel::class);
        if (! $stages->find($id)) {
            return redirect()->to(site_url('master/tahapan'))->with('error', 'Tahap tidak ditemukan.');
        }

        $data = [
            'name'       => trim((string) $this->request->getPost('name')),
            'kind'       => $this->request->getPost('kind'),
            'color'      => $this->request->getPost('color') ?: '#64748b',
            'sort_order' => (int) $this->request->getPost('sort_order'),
            'is_active'  => $this->request->getPost('is_active') ? 1 : 0,
        ];
        if (! in_array($data['kind'], ['open', 'hired', 'rejected'], true)) {
            $data['kind'] = 'open';
        }

        if (! $stages->update($id, $data)) {
            return redirect()->back()->withInput()->with('errors', $stages->errors());
        }

        return redirect()->to(site_url('master/tahapan'))->with('success', 'Tahap diperbarui.');
    }

    /**
     * Simpan urutan baru hasil geser (drag-and-drop). Menerima daftar id tahap
     * sesuai urutan tampilan, lalu menulis ulang sort_order 1..n. Dipanggil
     * lewat fetch dari halaman; membalas JSON.
     */
    public function reorder()
    {
        $ids = $this->request->getPost('order');
        if (! is_array($ids) || $ids === []) {
            return $this->response->setJSON(['ok' => false, 'msg' => 'Urutan kosong.', 'csrf' => csrf_hash()]);
        }

        $stages = model(StageModel::class);
        $urut   = 1;
        foreach ($ids as $id) {
            $id = (int) $id;
            if ($id > 0) {
                $stages->update($id, ['sort_order' => $urut++]);
            }
        }

        return $this->response->setJSON(['ok' => true, 'csrf' => csrf_hash()]);
    }

    public function destroy(int $id)
    {
        $stage = model(StageModel::class)->find($id);
        if (! $stage) {
            return redirect()->to(site_url('master/tahapan'))->with('error', 'Tahap tidak ditemukan.');
        }

        /* Tahap yang masih memuat lamaran tidak dihapus — lamaran akan
           kehilangan tahapnya (FK RESTRICT juga akan menolak). Dinonaktifkan
           saja agar tak lagi jadi pilihan tapi data lama tetap utuh. */
        $terpakai = model(ApplicationModel::class)->where('stage_id', $id)->countAllResults();
        if ($terpakai > 0) {
            model(StageModel::class)->update($id, ['is_active' => 0]);

            return redirect()->to(site_url('master/tahapan'))
                ->with('info', "Tahap ini dipakai {$terpakai} lamaran, jadi dinonaktifkan (bukan dihapus).");
        }

        model(StageModel::class)->delete($id);

        return redirect()->to(site_url('master/tahapan'))->with('success', 'Tahap dihapus.');
    }
}
