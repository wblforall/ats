<?php

namespace App\Models;

use CodeIgniter\Model;

class StageModel extends Model
{
    protected $table         = 'stages';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['name', 'slug', 'kind', 'color', 'sort_order', 'is_active'];
    protected $useTimestamps = true;

    protected $validationRules = [
        'name' => 'required|max_length[80]',
        'slug' => 'required|max_length[60]|is_unique[stages.slug,id,{id}]',
        'kind' => 'required|in_list[open,hired,rejected]',
    ];
    protected $validationMessages = [
        'name' => ['required' => 'Nama tahap wajib diisi.'],
        'slug' => ['is_unique' => 'Slug tahap sudah dipakai.'],
        'kind' => ['in_list' => 'Jenis tahap tidak dikenal.'],
    ];

    public function active(): array
    {
        return $this->where('is_active', 1)->orderBy('sort_order', 'ASC')->findAll();
    }

    /** Tahap pertama pipeline — tujuan default lamaran baru. */
    public function firstStage(): ?array
    {
        return $this->where('is_active', 1)->where('kind', 'open')
            ->orderBy('sort_order', 'ASC')->first();
    }

    public function bySlug(string $slug): ?array
    {
        return $this->where('slug', $slug)->first();
    }

    /**
     * Status lamaran yang seharusnya berlaku bila dipindah ke tahap ini.
     * Satu-satunya tempat pemetaan kind -> status ditulis.
     */
    public function statusForKind(string $kind): string
    {
        return match ($kind) {
            'hired'    => 'hired',
            'rejected' => 'rejected',
            default    => 'active',
        };
    }
}
