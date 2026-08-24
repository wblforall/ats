<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Antrean email. Semua email direkam dulu di sini lalu dikirim — sehingga
 * gagal SMTP tidak pernah menggagalkan aksi HR, riwayat kiriman ke pelamar
 * bisa ditelusuri, dan yang gagal bisa dikirim ulang.
 */
class CreateEmailOutbox extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'             => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'application_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'template_code'  => ['type' => 'VARCHAR', 'constraint' => 60, 'null' => true],
            'to_email'       => ['type' => 'VARCHAR', 'constraint' => 160],
            'to_name'        => ['type' => 'VARCHAR', 'constraint' => 160, 'null' => true],
            'subject'        => ['type' => 'VARCHAR', 'constraint' => 255],
            'body'           => ['type' => 'TEXT'],
            'status'         => ['type' => 'ENUM', 'constraint' => ['queued', 'sent', 'failed'], 'default' => 'queued'],
            'attempts'       => ['type' => 'TINYINT', 'unsigned' => true, 'default' => 0],
            'last_error'     => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
            'created_by'     => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'sent_at'        => ['type' => 'DATETIME', 'null' => true],
            'created_at'     => ['type' => 'DATETIME', 'null' => true],
            'updated_at'     => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('status');
        $this->forge->addKey('application_id');
        $this->forge->addForeignKey('application_id', 'applications', 'id', '', 'SET NULL');
        $this->forge->addForeignKey('created_by', 'users', 'id', '', 'SET NULL');
        $this->forge->createTable('email_outbox');
    }

    public function down(): void
    {
        $this->forge->dropTable('email_outbox');
    }
}
