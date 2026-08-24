<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Tahapan pipeline rekrutmen. `kind` menentukan arti akhir sebuah tahap:
 * open = masih berjalan, hired = diterima, rejected = gugur. Status aplikasi
 * ikut otomatis dari kind tahap tujuan, jadi tidak ada dua sumber kebenaran.
 */
class CreateStages extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'         => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'name'       => ['type' => 'VARCHAR', 'constraint' => 80],
            'slug'       => ['type' => 'VARCHAR', 'constraint' => 60],
            'kind'       => ['type' => 'ENUM', 'constraint' => ['open', 'hired', 'rejected'], 'default' => 'open'],
            'color'      => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => '#64748b'],
            'sort_order' => ['type' => 'SMALLINT', 'unsigned' => true, 'default' => 0],
            'is_active'  => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey('slug');
        $this->forge->createTable('stages');
    }

    public function down(): void
    {
        $this->forge->dropTable('stages');
    }
}
