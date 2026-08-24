<?php

namespace App\Models;

use CodeIgniter\Model;

class DepartmentModel extends Model
{
    protected $table         = 'departments';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['unit_id', 'name', 'is_active', 'sort_order'];
    protected $useTimestamps = true;

    protected $validationRules = [
        'unit_id' => 'required|is_natural_no_zero',
        /* Sengaja TANPA is_unique: keunikannya berlaku per unit, bukan global.
           Pemeriksaannya ada di isDuplicate(). */
        'name'    => 'required|max_length[120]',
    ];
    protected $validationMessages = [
        'unit_id' => ['required' => 'Unit usaha wajib dipilih.'],
        'name'    => ['required' => 'Nama departemen wajib diisi.'],
    ];

    /**
     * Nama departemen hanya perlu unik DI DALAM satu unit — "Marketing" boleh
     * ada di Pentacity maupun E-Walk. Aturan is_unique bawaan tidak bisa
     * menyaring per kolom lain, jadi pemeriksaannya dilakukan di sini.
     */
    public function isDuplicate(int $unitId, string $name, ?int $exceptId = null): bool
    {
        $builder = $this->where('unit_id', $unitId)->where('name', trim($name));

        if ($exceptId !== null) {
            $builder->where('id !=', $exceptId);
        }

        return $builder->countAllResults() > 0;
    }

    /** Departemen aktif milik satu unit, untuk dropdown formulir lowongan. */
    public function forUnit(int $unitId): array
    {
        return $this->where('unit_id', $unitId)
            ->where('is_active', 1)
            ->orderBy('sort_order', 'ASC')
            ->orderBy('name', 'ASC')
            ->findAll();
    }

    /**
     * Seluruh departemen aktif beserta unitnya. Dipakai formulir lowongan:
     * semua opsi dirender sekaligus, lalu disaring di sisi browser mengikuti
     * unit yang dipilih — tanpa perlu memuat ulang halaman.
     */
    public function allActive(?int $unitId = null): array
    {
        $builder = $this->select('departments.*, units.name AS unit_name, units.code AS unit_code')
            ->join('units', 'units.id = departments.unit_id')
            ->where('departments.is_active', 1);

        if ($unitId !== null) {
            $builder->where('departments.unit_id', $unitId);
        }

        return $builder->orderBy('units.sort_order', 'ASC')
            ->orderBy('departments.sort_order', 'ASC')
            ->orderBy('departments.name', 'ASC')
            ->findAll();
    }

    /**
     * Benarkah departemen ini milik unit tersebut? Inilah penguncian yang
     * mencegah lowongan satu unit memakai departemen unit lain — pemeriksaan
     * di sisi browser saja tidak cukup, karena POST bisa dikirim langsung.
     */
    public function belongsToUnit(int $departmentId, int $unitId): bool
    {
        return $this->where('id', $departmentId)->where('unit_id', $unitId)->countAllResults() > 0;
    }

    /** Daftar departemen + jumlah lowongan yang memakainya, untuk master data. */
    public function withUsage(?int $unitId = null)
    {
        $builder = $this->select('departments.*, units.name AS unit_name, units.code AS unit_code,
                (SELECT COUNT(*) FROM jobs j WHERE j.department_id = departments.id AND j.deleted_at IS NULL) AS job_count')
            ->join('units', 'units.id = departments.unit_id');

        if ($unitId !== null) {
            $builder->where('departments.unit_id', $unitId);
        }

        return $builder->orderBy('units.sort_order', 'ASC')->orderBy('departments.name', 'ASC');
    }
}
