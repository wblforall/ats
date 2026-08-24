<?php

namespace App\Models;

use CodeIgniter\Model;

class EmailTemplateModel extends Model
{
    protected $table         = 'email_templates';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['code', 'name', 'subject', 'body', 'description', 'auto_send', 'is_active'];
    protected $useTimestamps = true;

    protected $validationRules = [
        'code'    => 'required|max_length[60]|is_unique[email_templates.code,id,{id}]',
        'name'    => 'required|max_length[120]',
        'subject' => 'required|max_length[255]',
        'body'    => 'required',
    ];
    protected $validationMessages = [
        'code'    => ['is_unique' => 'Kode template sudah dipakai.'],
        'subject' => ['required' => 'Subjek email wajib diisi.'],
        'body'    => ['required' => 'Isi email wajib diisi.'],
    ];

    /** Placeholder yang dikenali beserta penjelasannya, ditampilkan di form. */
    public const PLACEHOLDERS = [
        '{{nama_kandidat}}'     => 'Nama lengkap pelamar',
        '{{posisi}}'            => 'Nama posisi yang dilamar',
        '{{unit}}'              => 'Unit usaha lowongan',
        '{{perusahaan}}'        => 'Nama perusahaan',
        '{{kode_lamaran}}'      => 'Nomor lamaran',
        '{{tanggal_interview}}' => 'Tanggal jadwal interview',
        '{{jam_interview}}'     => 'Jam jadwal interview',
        '{{lokasi}}'            => 'Lokasi interview',
        '{{link_meeting}}'      => 'Tautan meeting daring',
        '{{nama_hr}}'           => 'Nama pengirim (user yang login)',
        '{{kontak_hr}}'         => 'Kontak HRD dari pengaturan',
    ];

    public function findByCode(string $code): ?array
    {
        return $this->where('code', $code)->where('is_active', 1)->first();
    }

    public function activeOptions(): array
    {
        return array_column(
            $this->where('is_active', 1)->orderBy('name', 'ASC')->findAll(),
            'name',
            'code'
        );
    }
}
