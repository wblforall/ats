<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/** Lowongan. `slug` dipakai sebagai URL publik di portal karir. */
class CreateJobs extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'              => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'unit_id'         => ['type' => 'INT', 'unsigned' => true],
            'code'            => ['type' => 'VARCHAR', 'constraint' => 40],
            'slug'            => ['type' => 'VARCHAR', 'constraint' => 180],
            'title'           => ['type' => 'VARCHAR', 'constraint' => 160],
            'department'      => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true],
            'employment_type' => ['type' => 'ENUM', 'constraint' => ['full_time', 'contract', 'part_time', 'internship', 'daily'], 'default' => 'full_time'],
            'level'           => ['type' => 'VARCHAR', 'constraint' => 60, 'null' => true],
            'work_location'   => ['type' => 'VARCHAR', 'constraint' => 160, 'null' => true],
            'openings'        => ['type' => 'SMALLINT', 'unsigned' => true, 'default' => 1],
            'description'     => ['type' => 'TEXT', 'null' => true],
            'requirements'    => ['type' => 'TEXT', 'null' => true],
            'benefits'        => ['type' => 'TEXT', 'null' => true],
            'salary_min'      => ['type' => 'DECIMAL', 'constraint' => '14,2', 'null' => true],
            'salary_max'      => ['type' => 'DECIMAL', 'constraint' => '14,2', 'null' => true],
            'salary_visible'  => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'status'          => ['type' => 'ENUM', 'constraint' => ['draft', 'open', 'closed'], 'default' => 'draft'],
            'published_at'    => ['type' => 'DATETIME', 'null' => true],
            'closing_date'    => ['type' => 'DATE', 'null' => true],
            'created_by'      => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'created_at'      => ['type' => 'DATETIME', 'null' => true],
            'updated_at'      => ['type' => 'DATETIME', 'null' => true],
            'deleted_at'      => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey('code');
        $this->forge->addUniqueKey('slug');
        $this->forge->addKey(['status', 'unit_id']);
        $this->forge->addForeignKey('unit_id', 'units', 'id', '', 'CASCADE');
        $this->forge->addForeignKey('created_by', 'users', 'id', '', 'SET NULL');
        $this->forge->createTable('jobs');
    }

    public function down(): void
    {
        $this->forge->dropTable('jobs');
    }
}
