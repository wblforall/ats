<?php

namespace App\Models;

use CodeIgniter\Model;

class UnitModel extends Model
{
    protected $table         = 'units';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['code', 'name', 'address', 'is_active', 'sort_order'];
    protected $useTimestamps = true;

    protected $validationRules = [
        'code' => 'required|max_length[20]|is_unique[units.code,id,{id}]',
        'name' => 'required|max_length[120]',
    ];
    protected $validationMessages = [
        'code' => [
            'required'  => 'Kode unit wajib diisi.',
            'is_unique' => 'Kode unit sudah dipakai.',
        ],
        'name' => ['required' => 'Nama unit wajib diisi.'],
    ];

    /** Unit aktif, urut sesuai sort_order — dipakai di semua dropdown. */
    public function active(): array
    {
        return $this->where('is_active', 1)->orderBy('sort_order', 'ASC')->findAll();
    }

    /** Pasangan id => nama untuk helper form. */
    public function options(): array
    {
        return array_column($this->active(), 'name', 'id');
    }
}
