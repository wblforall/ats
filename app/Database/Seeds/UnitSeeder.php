<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * Unit usaha WBL. Kode disamakan dengan master unit yang sudah dipakai
 * aplikasi internal lain (pamsign) supaya laporan lintas sistem gampang
 * dicocokkan. HO dipakai untuk lowongan kantor pusat.
 */
class UnitSeeder extends Seeder
{
    public function run(): void
    {
        $now = date('Y-m-d H:i:s');

        $units = [
            ['HO',  'Kantor Pusat PT. Wulandari Bangun Laksana Tbk', 'Balikpapan', 1],
            ['PTC', 'Pentacity Shopping Venue',            'Balikpapan', 2],
            ['EWM', 'E-Walk Simply FUNtastic',             'Balikpapan', 3],
            ['MIC', 'Mall Intelligence Center',            'Balikpapan', 4],
            ['DEV', 'WBL Developer',                       'Balikpapan', 5],
            ['MKT', 'Marketing Grup',                      'Balikpapan', 6],
            ['EST', 'WBL Estate',                          'Samarinda',  7],
        ];

        foreach ($units as [$code, $name, $address, $sort]) {
            $this->db->table('units')->ignore(true)->insert([
                'code'       => $code,
                'name'       => $name,
                'address'    => $address,
                'is_active'  => 1,
                'sort_order' => $sort,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
