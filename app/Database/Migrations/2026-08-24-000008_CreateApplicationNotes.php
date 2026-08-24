<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/** Catatan bebas tim HR pada satu lamaran (screening berkas, hasil telepon, dll). */
class CreateApplicationNotes extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'             => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'application_id' => ['type' => 'INT', 'unsigned' => true],
            'user_id'        => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'note'           => ['type' => 'TEXT'],
            'created_at'     => ['type' => 'DATETIME', 'null' => true],
            'updated_at'     => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('application_id');
        $this->forge->addForeignKey('application_id', 'applications', 'id', '', 'CASCADE');
        $this->forge->addForeignKey('user_id', 'users', 'id', '', 'SET NULL');
        $this->forge->createTable('application_notes');
    }

    public function down(): void
    {
        $this->forge->dropTable('application_notes');
    }
}
