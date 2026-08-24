<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Data orang, terpisah dari lamaran. Satu orang bisa melamar beberapa
 * lowongan tanpa data dirinya terduplikasi — email jadi kunci identitas.
 */
class CreateCandidates extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'               => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'full_name'        => ['type' => 'VARCHAR', 'constraint' => 160],
            'email'            => ['type' => 'VARCHAR', 'constraint' => 160],
            'phone'            => ['type' => 'VARCHAR', 'constraint' => 30, 'null' => true],
            'gender'           => ['type' => 'ENUM', 'constraint' => ['L', 'P'], 'null' => true],
            'birth_date'       => ['type' => 'DATE', 'null' => true],
            'address'          => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'city'             => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'education_level'  => ['type' => 'VARCHAR', 'constraint' => 40, 'null' => true],
            'school'           => ['type' => 'VARCHAR', 'constraint' => 160, 'null' => true],
            'major'            => ['type' => 'VARCHAR', 'constraint' => 160, 'null' => true],
            'years_experience' => ['type' => 'DECIMAL', 'constraint' => '4,1', 'null' => true],
            'current_position' => ['type' => 'VARCHAR', 'constraint' => 160, 'null' => true],
            'current_company'  => ['type' => 'VARCHAR', 'constraint' => 160, 'null' => true],
            'linkedin_url'     => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'portfolio_url'    => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'photo_path'       => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'is_blacklisted'   => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'blacklist_reason' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'created_at'       => ['type' => 'DATETIME', 'null' => true],
            'updated_at'       => ['type' => 'DATETIME', 'null' => true],
            'deleted_at'       => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey('email');
        $this->forge->addKey('phone');
        $this->forge->addKey('full_name');
        $this->forge->createTable('candidates');
    }

    public function down(): void
    {
        $this->forge->dropTable('candidates');
    }
}
