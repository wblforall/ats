<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Lamaran = pasangan kandidat x lowongan. Inilah baris yang bergerak di
 * pipeline. Unik per pasangan supaya satu orang tidak melamar lowongan yang
 * sama dua kali (form publik memblokirnya lebih dulu dengan pesan ramah).
 */
class CreateApplications extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'              => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'candidate_id'    => ['type' => 'INT', 'unsigned' => true],
            'job_id'          => ['type' => 'INT', 'unsigned' => true],
            'stage_id'        => ['type' => 'INT', 'unsigned' => true],
            'status'          => ['type' => 'ENUM', 'constraint' => ['active', 'hired', 'rejected', 'withdrawn'], 'default' => 'active'],
            'source'          => ['type' => 'ENUM', 'constraint' => ['career_portal', 'referral', 'walk_in', 'jobstreet', 'linkedin', 'instagram', 'other'], 'default' => 'career_portal'],
            'referral_name'   => ['type' => 'VARCHAR', 'constraint' => 160, 'null' => true],
            'cv_path'         => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'cv_original'     => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'cover_letter'    => ['type' => 'TEXT', 'null' => true],
            'expected_salary' => ['type' => 'DECIMAL', 'constraint' => '14,2', 'null' => true],
            'available_from'  => ['type' => 'DATE', 'null' => true],
            'rating'          => ['type' => 'TINYINT', 'unsigned' => true, 'null' => true],
            'avg_score'       => ['type' => 'DECIMAL', 'constraint' => '4,2', 'null' => true],
            'reject_reason'   => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'applied_at'      => ['type' => 'DATETIME', 'null' => true],
            'closed_at'       => ['type' => 'DATETIME', 'null' => true],
            'created_at'      => ['type' => 'DATETIME', 'null' => true],
            'updated_at'      => ['type' => 'DATETIME', 'null' => true],
            'deleted_at'      => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['candidate_id', 'job_id']);
        $this->forge->addKey(['job_id', 'stage_id']);
        $this->forge->addKey('status');
        $this->forge->addForeignKey('candidate_id', 'candidates', 'id', '', 'CASCADE');
        $this->forge->addForeignKey('job_id', 'jobs', 'id', '', 'CASCADE');
        $this->forge->addForeignKey('stage_id', 'stages', 'id', '', 'RESTRICT');
        $this->forge->createTable('applications');
    }

    public function down(): void
    {
        $this->forge->dropTable('applications');
    }
}
