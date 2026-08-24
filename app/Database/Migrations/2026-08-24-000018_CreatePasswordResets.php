<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Token reset kata sandi. Yang disimpan HANYA hash token (bukan token asli),
 * jadi bocornya tabel ini tidak memberi siapa pun tautan reset yang valid.
 * Token berlaku singkat dan sekali pakai.
 */
class CreatePasswordResets extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'         => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'user_id'    => ['type' => 'INT', 'unsigned' => true],
            'token_hash' => ['type' => 'VARCHAR', 'constraint' => 64],
            'expires_at' => ['type' => 'DATETIME'],
            'used_at'    => ['type' => 'DATETIME', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('token_hash');
        $this->forge->addForeignKey('user_id', 'users', 'id', '', 'CASCADE');
        $this->forge->createTable('password_resets');
    }

    public function down(): void
    {
        $this->forge->dropTable('password_resets');
    }
}
