<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Akun media sosial pelamar sebagai bahan referensi tambahan bagi HR.
 * Instagram wajib diisi di formulir lamaran; LinkedIn sudah ada sejak awal
 * dan tetap opsional. Keduanya tetap `null` di basis data karena data lama
 * (dan lamaran yang diinput manual oleh HR) belum tentu punya.
 */
class AddSocialToCandidates extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('candidates', [
            'instagram' => [
                'type'       => 'VARCHAR',
                'constraint' => 120,
                'null'       => true,
                'after'      => 'linkedin_url',
            ],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('candidates', 'instagram');
    }
}
