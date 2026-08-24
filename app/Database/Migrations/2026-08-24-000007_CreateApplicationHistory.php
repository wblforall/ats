<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/** Jejak perpindahan tahap — dasar laporan funnel & lama proses per tahap. */
class CreateApplicationHistory extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'             => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'application_id' => ['type' => 'INT', 'unsigned' => true],
            'from_stage_id'  => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'to_stage_id'    => ['type' => 'INT', 'unsigned' => true],
            'user_id'        => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'note'           => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'created_at'     => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('application_id');
        $this->forge->addKey('created_at');
        $this->forge->addForeignKey('application_id', 'applications', 'id', '', 'CASCADE');
        $this->forge->addForeignKey('user_id', 'users', 'id', '', 'SET NULL');
        $this->forge->createTable('application_history');
    }

    public function down(): void
    {
        $this->forge->dropTable('application_history');
    }
}
