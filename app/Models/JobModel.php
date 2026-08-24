<?php

namespace App\Models;

use CodeIgniter\Model;

class JobModel extends Model
{
    protected $table          = 'jobs';
    protected $primaryKey     = 'id';
    protected $returnType     = 'array';
    protected $useSoftDeletes = true;
    protected $allowedFields  = [
        'unit_id', 'code', 'slug', 'title', 'department', 'employment_type', 'level',
        'work_location', 'openings', 'description', 'requirements', 'benefits',
        'salary_min', 'salary_max', 'salary_visible', 'status', 'published_at',
        'closing_date', 'created_by',
    ];
    protected $useTimestamps = true;

    protected $validationRules = [
        'unit_id'         => 'required|is_natural_no_zero',
        'title'           => 'required|max_length[160]',
        'code'            => 'required|max_length[40]|is_unique[jobs.code,id,{id}]',
        'slug'            => 'required|max_length[180]|is_unique[jobs.slug,id,{id}]',
        'employment_type' => 'required|in_list[full_time,contract,part_time,internship,daily]',
        'openings'        => 'required|is_natural_no_zero',
        'status'          => 'required|in_list[draft,open,closed]',
    ];
    protected $validationMessages = [
        'unit_id'  => ['required' => 'Unit wajib dipilih.'],
        'title'    => ['required' => 'Nama posisi wajib diisi.'],
        'code'     => ['is_unique' => 'Kode lowongan sudah dipakai.'],
        'slug'     => ['is_unique' => 'Slug lowongan sudah dipakai.'],
        'openings' => ['is_natural_no_zero' => 'Jumlah kebutuhan minimal 1.'],
    ];

    public const EMPLOYMENT_TYPES = [
        'full_time'  => 'Karyawan Tetap',
        'contract'   => 'Kontrak',
        'part_time'  => 'Paruh Waktu',
        'internship' => 'Magang',
        'daily'      => 'Harian',
    ];

    public const STATUSES = [
        'draft'  => 'Draf',
        'open'   => 'Dibuka',
        'closed' => 'Ditutup',
    ];

    /** Query dasar + nama unit + jumlah pelamar; dipakai daftar lowongan admin. */
    public function withStats(?int $unitId = null)
    {
        $builder = $this->select('jobs.*, units.name AS unit_name, units.code AS unit_code,
                (SELECT COUNT(*) FROM applications a WHERE a.job_id = jobs.id AND a.deleted_at IS NULL) AS applicant_count')
            ->join('units', 'units.id = jobs.unit_id', 'left');

        if ($unitId !== null) {
            $builder->where('jobs.unit_id', $unitId);
        }

        return $builder;
    }

    /**
     * Lowongan yang tayang di portal karir: berstatus open, sudah terbit, dan
     * belum lewat tanggal tutup (tanggal tutup kosong berarti tanpa batas).
     */
    public function published()
    {
        return $this->select('jobs.*, units.name AS unit_name')
            ->join('units', 'units.id = jobs.unit_id', 'left')
            ->where('jobs.status', 'open')
            ->where('jobs.published_at IS NOT NULL')
            ->where('jobs.published_at <=', date('Y-m-d H:i:s'))
            ->groupStart()
                ->where('jobs.closing_date IS NULL')
                ->orWhere('jobs.closing_date >=', date('Y-m-d'))
            ->groupEnd();
    }

    public function findPublishedBySlug(string $slug): ?array
    {
        return $this->published()->where('jobs.slug', $slug)->first();
    }

    /**
     * Kode otomatis, mis. PTC-2608-001 — unik per unit per bulan.
     * Urutannya tiga digit: dua digit habis di lowongan ke-100 dan mulai
     * menghasilkan kode kembar yang ditolak kunci unik.
     */
    public function generateCode(string $unitCode): string
    {
        $prefix = strtoupper($unitCode) . '-' . date('ym') . '-';
        $last   = $this->withDeleted()->like('code', $prefix, 'after')
            ->orderBy('code', 'DESC')->first();

        $next = $last ? ((int) substr($last['code'], strlen($prefix))) + 1 : 1;

        return $prefix . str_pad((string) $next, 3, '0', STR_PAD_LEFT);
    }

    /** Slug unik dari judul + kode, aman dipakai sebagai URL publik. */
    public function makeSlug(string $title, string $code): string
    {
        return url_title($title . ' ' . $code, '-', true);
    }
}
