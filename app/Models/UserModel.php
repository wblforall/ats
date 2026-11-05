<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table          = 'users';
    protected $primaryKey     = 'id';
    protected $returnType     = 'array';
    protected $useSoftDeletes = true;
    protected $allowedFields  = [
        'unit_id', 'name', 'email', 'password_hash', 'role',
        'position', 'phone', 'is_active', 'last_login_at',
        // Field virtual: dilewatkan lolos proteksi agar hook hashPassword
        // sempat mengubahnya jadi password_hash. CI4 menyaring allowedFields
        // SEBELUM beforeInsert, jadi tanpa ini `password` keburu dibuang.
        'password',
    ];
    protected $useTimestamps = true;

    protected $validationRules = [
        // Wajib ada agar placeholder {id} di aturan email terisi saat ubah data.
        'id'    => 'permit_empty|is_natural_no_zero',
        'name'  => 'required|max_length[120]',
        'email' => 'required|valid_email|max_length[160]|is_unique[users.email,id,{id}]',
        'role'  => 'required|in_list[superadmin,hr_admin,hr_staff,interviewer]',
    ];
    protected $validationMessages = [
        'name'  => ['required' => 'Nama wajib diisi.'],
        'email' => [
            'required'    => 'Email wajib diisi.',
            'valid_email' => 'Format email tidak valid.',
            'is_unique'   => 'Email sudah terdaftar.',
        ],
        'role' => ['in_list' => 'Peran tidak dikenal.'],
    ];

    protected $beforeInsert = ['hashPassword'];
    protected $beforeUpdate = ['hashPassword'];

    /**
     * Menerima field virtual `password`; kalau kosong saat update, hash lama
     * dibiarkan apa adanya sehingga form edit tak perlu mengulang sandi.
     */
    protected function hashPassword(array $data): array
    {
        if (! isset($data['data']['password'])) {
            return $data;
        }

        $plain = (string) $data['data']['password'];
        unset($data['data']['password']);

        if ($plain !== '') {
            $data['data']['password_hash'] = password_hash($plain, PASSWORD_DEFAULT);
        }

        return $data;
    }

    public function findActiveByEmail(string $email): ?array
    {
        return $this->where('email', $email)->where('is_active', 1)->first();
    }

    public function touchLogin(int $id): void
    {
        $this->builder()->where('id', $id)->update(['last_login_at' => date('Y-m-d H:i:s')]);
    }

    /** Daftar user + nama unit; $unitId membatasi ke satu unit (non-superadmin). */
    public function withUnit(?int $unitId = null)
    {
        $builder = $this->select('users.*, units.name AS unit_name')
            ->join('units', 'units.id = users.unit_id', 'left');

        if ($unitId !== null) {
            $builder->where('users.unit_id', $unitId);
        }

        return $builder;
    }

    /** Kandidat pewawancara: siapa saja yang boleh mengisi penilaian. */
    public function interviewerOptions(?int $unitId = null): array
    {
        $builder = $this->where('is_active', 1)
            ->whereIn('role', ['hr_admin', 'hr_staff', 'interviewer', 'superadmin']);

        if ($unitId !== null) {
            $builder->groupStart()->where('unit_id', $unitId)->orWhere('unit_id', null)->groupEnd();
        }

        return array_column($builder->orderBy('name', 'ASC')->findAll(), 'name', 'id');
    }
}
