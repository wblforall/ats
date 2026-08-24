<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/** Template email otomatis; isi memakai placeholder {{nama_kandidat}} dst. */
class CreateEmailTemplates extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'          => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'code'        => ['type' => 'VARCHAR', 'constraint' => 60],
            'name'        => ['type' => 'VARCHAR', 'constraint' => 120],
            'subject'     => ['type' => 'VARCHAR', 'constraint' => 255],
            'body'        => ['type' => 'TEXT'],
            'description' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'auto_send'   => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'is_active'   => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at'  => ['type' => 'DATETIME', 'null' => true],
            'updated_at'  => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey('code');
        $this->forge->createTable('email_templates');
    }

    public function down(): void
    {
        $this->forge->dropTable('email_templates');
    }
}
