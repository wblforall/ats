<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/** Nilai awal pengaturan yang bisa diubah HR dari menu Pengaturan. */
class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $now = date('Y-m-d H:i:s');

        $settings = [
            'company_name'       => 'PT. Wulandari Bangun Laksana Tbk',
            'company_address'    => 'Balikpapan, Kalimantan Timur',
            'hr_email'           => 'hrd@wbl.co.id',
            'hr_phone'           => '',
            'career_tagline'     => 'Bergabunglah bersama kami',
            'career_intro'       => 'Kami mengundang talenta terbaik untuk tumbuh dan berkarya bersama PT. Wulandari Bangun Laksana Tbk.',
            'cv_max_mb'          => '5',
            'cv_allowed_ext'     => 'pdf,doc,docx',
            'auto_email_on_apply' => '1',
            'interview_reminder_hours' => '24',
        ];

        foreach ($settings as $key => $value) {
            $this->db->table('settings')->ignore(true)->insert([
                'key'        => $key,
                'value'      => $value,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
