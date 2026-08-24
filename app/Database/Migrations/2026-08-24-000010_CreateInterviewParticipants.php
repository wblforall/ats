<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/** Pewawancara yang ditugaskan pada satu jadwal (bisa lebih dari satu orang). */
class CreateInterviewParticipants extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'           => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'interview_id' => ['type' => 'INT', 'unsigned' => true],
            'user_id'      => ['type' => 'INT', 'unsigned' => true],
            'created_at'   => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['interview_id', 'user_id']);
        $this->forge->addKey('user_id');
        $this->forge->addForeignKey('interview_id', 'interviews', 'id', '', 'CASCADE');
        $this->forge->addForeignKey('user_id', 'users', 'id', '', 'CASCADE');
        $this->forge->createTable('interview_participants');
    }

    public function down(): void
    {
        $this->forge->dropTable('interview_participants');
    }
}
