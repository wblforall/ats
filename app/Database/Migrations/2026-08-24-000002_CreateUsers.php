<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * User internal HR. Peran:
 *  - superadmin : semua unit, kelola user & master data
 *  - hr_admin   : penuh dalam unitnya (kelola lowongan, kandidat, kirim email)
 *  - hr_staff   : input & geser tahap kandidat, tidak bisa hapus lowongan
 *  - interviewer: hanya lihat kandidat yang diinterview + isi penilaian
 */
class CreateUsers extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'            => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'unit_id'       => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'name'          => ['type' => 'VARCHAR', 'constraint' => 120],
            'email'         => ['type' => 'VARCHAR', 'constraint' => 160],
            'password_hash' => ['type' => 'VARCHAR', 'constraint' => 255],
            'role'          => ['type' => 'ENUM', 'constraint' => ['superadmin', 'hr_admin', 'hr_staff', 'interviewer'], 'default' => 'hr_staff'],
            'position'      => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true],
            'phone'         => ['type' => 'VARCHAR', 'constraint' => 30, 'null' => true],
            'is_active'     => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'last_login_at' => ['type' => 'DATETIME', 'null' => true],
            'created_at'    => ['type' => 'DATETIME', 'null' => true],
            'updated_at'    => ['type' => 'DATETIME', 'null' => true],
            'deleted_at'    => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey('email');
        $this->forge->addKey('unit_id');
        $this->forge->addForeignKey('unit_id', 'units', 'id', '', 'SET NULL');
        $this->forge->createTable('users');
    }

    public function down(): void
    {
        $this->forge->dropTable('users');
    }
}
