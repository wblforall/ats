<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Ganti kolom teks bebas `jobs.department` dengan rujukan ke master
 * departemen. Teks bebas membuat "Marketing", "marketing", dan "Mkt" hidup
 * berdampingan sebagai tiga hal berbeda, dan tidak ada yang mencegah HR unit
 * A menuliskan departemen milik unit B.
 *
 * Data lama tidak dibuang: tiap pasangan (unit, teks departemen) yang sudah
 * ada dijadikan baris master lebih dulu, baru lowongannya ditautkan.
 */
class LinkJobsToDepartments extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('jobs', [
            'department_id' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'title',
            ],
        ]);

        $now = date('Y-m-d H:i:s');

        $rows = $this->db->table('jobs')
            ->select('unit_id, department')
            ->where('department IS NOT NULL')
            ->where('department !=', '')
            ->distinct()
            ->get()->getResultArray();

        foreach ($rows as $row) {
            $name = trim($row['department']);
            if ($name === '') {
                continue;
            }

            $existing = $this->db->table('departments')
                ->where('unit_id', $row['unit_id'])
                ->where('name', $name)
                ->get()->getRowArray();

            if ($existing) {
                $departmentId = (int) $existing['id'];
            } else {
                $this->db->table('departments')->insert([
                    'unit_id'    => $row['unit_id'],
                    'name'       => $name,
                    'is_active'  => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
                $departmentId = (int) $this->db->insertID();
            }

            $this->db->table('jobs')
                ->where('unit_id', $row['unit_id'])
                ->where('department', $row['department'])
                ->update(['department_id' => $departmentId]);
        }

        /* Lewat SQL langsung: forge->addForeignKey() hanya berlaku saat tabel
           dibuat, bukan untuk kolom yang ditambahkan belakangan. */
        $this->db->query('ALTER TABLE `jobs` ADD CONSTRAINT `jobs_department_id_foreign`
            FOREIGN KEY (`department_id`) REFERENCES `departments`(`id`) ON DELETE SET NULL');

        $this->forge->dropColumn('jobs', 'department');
    }

    public function down(): void
    {
        $this->forge->addColumn('jobs', [
            'department' => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true, 'after' => 'title'],
        ]);

        $this->db->query('UPDATE jobs j JOIN departments d ON d.id = j.department_id SET j.department = d.name');

        $this->db->query('ALTER TABLE `jobs` DROP FOREIGN KEY `jobs_department_id_foreign`');
        $this->forge->dropColumn('jobs', 'department_id');
    }
}
