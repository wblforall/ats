<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/** Jadwal interview per lamaran. Satu lamaran bisa punya beberapa putaran. */
class CreateInterviews extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'             => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'application_id' => ['type' => 'INT', 'unsigned' => true],
            'round'          => ['type' => 'TINYINT', 'unsigned' => true, 'default' => 1],
            'title'          => ['type' => 'VARCHAR', 'constraint' => 160, 'null' => true],
            'mode'           => ['type' => 'ENUM', 'constraint' => ['onsite', 'online', 'phone'], 'default' => 'onsite'],
            'scheduled_at'   => ['type' => 'DATETIME'],
            'duration_min'   => ['type' => 'SMALLINT', 'unsigned' => true, 'default' => 60],
            'location'       => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'meeting_link'   => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'status'         => ['type' => 'ENUM', 'constraint' => ['scheduled', 'completed', 'canceled', 'no_show'], 'default' => 'scheduled'],
            'notes'          => ['type' => 'TEXT', 'null' => true],
            'created_by'     => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'created_at'     => ['type' => 'DATETIME', 'null' => true],
            'updated_at'     => ['type' => 'DATETIME', 'null' => true],
            'deleted_at'     => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('application_id');
        $this->forge->addKey('scheduled_at');
        $this->forge->addForeignKey('application_id', 'applications', 'id', '', 'CASCADE');
        $this->forge->addForeignKey('created_by', 'users', 'id', '', 'SET NULL');
        $this->forge->createTable('interviews');
    }

    public function down(): void
    {
        $this->forge->dropTable('interviews');
    }
}
