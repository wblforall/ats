<?php

namespace App\Models;

use CodeIgniter\Model;

class CandidateModel extends Model
{
    protected $table          = 'candidates';
    protected $primaryKey     = 'id';
    protected $returnType     = 'array';
    protected $useSoftDeletes = true;
    protected $allowedFields  = [
        'full_name', 'email', 'phone', 'gender', 'birth_date', 'address', 'city',
        'education_level', 'school', 'major', 'years_experience', 'current_position',
        'current_company', 'linkedin_url', 'instagram', 'portfolio_url', 'photo_path',
        'is_blacklisted', 'blacklist_reason',
    ];
    protected $useTimestamps = true;

    protected $validationRules = [
        'full_name' => 'required|max_length[160]',
        'email'     => 'required|valid_email|max_length[160]|is_unique[candidates.email,id,{id}]',
        'phone'     => 'permit_empty|max_length[30]',
    ];
    protected $validationMessages = [
        'full_name' => ['required' => 'Nama lengkap wajib diisi.'],
        'email'     => [
            'required'    => 'Email wajib diisi.',
            'valid_email' => 'Format email tidak valid.',
            'is_unique'   => 'Email ini sudah terdaftar sebagai pelamar.',
        ],
    ];

    public const EDUCATION_LEVELS = ['SMA/SMK', 'D1', 'D2', 'D3', 'D4', 'S1', 'S2', 'S3'];

    public function findByEmail(string $email): ?array
    {
        return $this->where('email', $email)->first();
    }

    /**
     * Ambil kandidat yang emailnya sudah ada, atau buat baru. Dipakai form
     * lamaran publik supaya satu orang tidak jadi banyak baris kandidat.
     * Data profil yang sudah terisi tidak ditimpa nilai kosong.
     *
     * Pencariannya sengaja menyertakan baris yang terhapus lunak: kunci unik
     * email di basis data tidak peduli `deleted_at`, jadi kalau yang terhapus
     * diabaikan, pelamar lama yang datanya pernah dihapus akan selalu gagal
     * disimpan. Baris seperti itu dipulihkan saja ketika ia melamar lagi.
     *
     * @return int|null id kandidat, atau null bila penyimpanan gagal.
     */
    public function findOrCreate(array $data): ?int
    {
        $existing = $this->withDeleted()->where('email', $data['email'])->first();

        if (! $existing) {
            $id = $this->insert($data, true);

            return $id === false ? null : (int) $id;
        }

        $update = array_filter(
            $data,
            static fn ($v, $k) => $k !== 'email' && $v !== null && $v !== '' && ($existing[$k] ?? null) === null,
            ARRAY_FILTER_USE_BOTH
        );

        if (($existing['deleted_at'] ?? null) !== null) {
            $update['deleted_at'] = null;
        }

        if ($update !== []) {
            /* Lewat builder, bukan update() model, supaya baris yang terhapus
               lunak tetap terjangkau dan bisa dipulihkan. */
            $this->builder()->where('id', $existing['id'])->update($update);
        }

        return (int) $existing['id'];
    }

    /** Pencarian bebas untuk daftar kandidat & bank talenta. */
    public function search(?string $q)
    {
        $builder = $this->select('candidates.*,
                (SELECT COUNT(*) FROM applications a WHERE a.candidate_id = candidates.id AND a.deleted_at IS NULL) AS application_count');

        if ($q !== null && trim($q) !== '') {
            $q = trim($q);
            $builder->groupStart()
                ->like('full_name', $q)
                ->orLike('email', $q)
                ->orLike('phone', $q)
                ->orLike('school', $q)
                ->orLike('current_position', $q)
                ->groupEnd();
        }

        return $builder;
    }
}
