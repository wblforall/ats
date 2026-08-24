<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Form penilaian pewawancara. Lima aspek skala 1–5; `overall` disimpan hasil
 * hitung (bukan dihitung ulang di tiap laporan) agar rata-rata kandidat murah.
 * Satu pewawancara satu penilaian per jadwal.
 */
class CreateEvaluations extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'                  => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'interview_id'        => ['type' => 'INT', 'unsigned' => true],
            'user_id'             => ['type' => 'INT', 'unsigned' => true],
            'score_technical'     => ['type' => 'TINYINT', 'unsigned' => true, 'null' => true],
            'score_communication' => ['type' => 'TINYINT', 'unsigned' => true, 'null' => true],
            'score_attitude'      => ['type' => 'TINYINT', 'unsigned' => true, 'null' => true],
            'score_experience'    => ['type' => 'TINYINT', 'unsigned' => true, 'null' => true],
            'score_culture'       => ['type' => 'TINYINT', 'unsigned' => true, 'null' => true],
            'overall'             => ['type' => 'DECIMAL', 'constraint' => '4,2', 'null' => true],
            'recommendation'      => ['type' => 'ENUM', 'constraint' => ['strong_yes', 'yes', 'neutral', 'no', 'strong_no'], 'default' => 'neutral'],
            'strengths'           => ['type' => 'TEXT', 'null' => true],
            'weaknesses'          => ['type' => 'TEXT', 'null' => true],
            'notes'               => ['type' => 'TEXT', 'null' => true],
            'created_at'          => ['type' => 'DATETIME', 'null' => true],
            'updated_at'          => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['interview_id', 'user_id']);
        $this->forge->addForeignKey('interview_id', 'interviews', 'id', '', 'CASCADE');
        $this->forge->addForeignKey('user_id', 'users', 'id', '', 'CASCADE');
        $this->forge->createTable('evaluations');
    }

    public function down(): void
    {
        $this->forge->dropTable('evaluations');
    }
}
