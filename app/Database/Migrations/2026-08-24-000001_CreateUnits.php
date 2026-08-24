<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Unit usaha WBL (Pentacity, E-Walk, PAM, dst). Semua lowongan & user HR
 * terikat ke satu unit; user tanpa unit_id (superadmin) melihat seluruh unit.
 */
class CreateUnits extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'         => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'code'       => ['type' => 'VARCHAR', 'constraint' => 20],
            'name'       => ['type' => 'VARCHAR', 'constraint' => 120],
            'address'    => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'is_active'  => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'sort_order' => ['type' => 'SMALLINT', 'unsigned' => true, 'default' => 0],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey('code');
        $this->forge->createTable('units');
    }

    public function down(): void
    {
        $this->forge->dropTable('units');
    }
}
