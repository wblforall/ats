<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * Tahapan default pipeline. Urutan boleh diubah HR lewat master data, tapi
 * tahap ber-kind `hired` dan `rejected` sebaiknya tetap ada satu-satu karena
 * status lamaran diturunkan dari kind tahap tujuan.
 */
class StageSeeder extends Seeder
{
    public function run(): void
    {
        $now = date('Y-m-d H:i:s');

        $stages = [
            ['Lamaran Masuk',   'lamaran-masuk',   'open',     '#64748b', 1],
            ['Seleksi Berkas',  'seleksi-berkas',  'open',     '#0ea5e9', 2],
            ['Tes / Asesmen',   'tes-asesmen',     'open',     '#8b5cf6', 3],
            ['Interview HR',    'interview-hr',    'open',     '#f59e0b', 4],
            ['Interview User',  'interview-user',  'open',     '#d97706', 5],
            ['Penawaran',       'penawaran',       'open',     '#14b8a6', 6],
            ['Diterima',        'diterima',        'hired',    '#16a34a', 7],
            ['Tidak Lolos',     'tidak-lolos',     'rejected', '#dc2626', 8],
        ];

        foreach ($stages as [$name, $slug, $kind, $color, $sort]) {
            $this->db->table('stages')->ignore(true)->insert([
                'name'       => $name,
                'slug'       => $slug,
                'kind'       => $kind,
                'color'      => $color,
                'sort_order' => $sort,
                'is_active'  => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
