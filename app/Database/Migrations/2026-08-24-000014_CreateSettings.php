<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/** Pengaturan aplikasi yang bisa diubah HR tanpa menyentuh .env. */
class CreateSettings extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'         => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'key'        => ['type' => 'VARCHAR', 'constraint' => 80],
            'value'      => ['type' => 'TEXT', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey('key');
        $this->forge->createTable('settings');
    }

    public function down(): void
    {
        $this->forge->dropTable('settings');
    }
}
