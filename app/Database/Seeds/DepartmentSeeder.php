<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * Departemen awal per unit usaha. Sengaja berbeda-beda antar unit: mal punya
 * Operasional & Tenant Relation, kantor pusat punya Keuangan & Human Capital.
 */
class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $units = array_column($this->db->table('units')->get()->getResultArray(), 'id', 'code');
        $now   = date('Y-m-d H:i:s');

        $peta = [
            'HO'  => ['Human Capital', 'Keuangan', 'Legal', 'IT', 'Umum'],
            'PTC' => ['Operasional', 'Tenant Relation', 'Marketing', 'Engineering', 'Security & Parking'],
            'EWM' => ['Operasional', 'Tenant Relation', 'Marketing', 'Engineering', 'Security & Parking'],
            'MIC' => ['IT', 'Data & Analitik'],
            'DEV' => ['Proyek', 'Perencanaan'],
            'MKT' => ['Penjualan', 'Promosi'],
            'EST' => ['Operasional', 'Pertanahan'],
        ];

        foreach ($peta as $unitCode => $namaDepartemen) {
            if (! isset($units[$unitCode])) {
                continue;
            }

            foreach ($namaDepartemen as $urutan => $nama) {
                $this->db->table('departments')->ignore(true)->insert([
                    'unit_id'    => $units[$unitCode],
                    'name'       => $nama,
                    'is_active'  => 1,
                    'sort_order' => $urutan + 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }
}
