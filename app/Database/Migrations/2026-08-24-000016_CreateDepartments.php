<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Departemen selalu milik satu unit usaha. Tidak ada departemen "global":
 * Marketing di Pentacity dan Marketing di E-Walk adalah dua baris berbeda,
 * karena atasan, anggaran, dan proses rekrutmennya memang terpisah.
 *
 * Kunci unik (unit_id, name) menjaga agar satu unit tidak punya dua
 * departemen bernama sama, sekaligus tetap membolehkan nama yang sama
 * dipakai di unit lain.
 */
class CreateDepartments extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'         => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'unit_id'    => ['type' => 'INT', 'unsigned' => true],
            'name'       => ['type' => 'VARCHAR', 'constraint' => 120],
            'is_active'  => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'sort_order' => ['type' => 'SMALLINT', 'unsigned' => true, 'default' => 0],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['unit_id', 'name']);
        $this->forge->addForeignKey('unit_id', 'units', 'id', '', 'CASCADE');
        $this->forge->createTable('departments');
    }

    public function down(): void
    {
        $this->forge->dropTable('departments');
    }
}
