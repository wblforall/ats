<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * Akun awal. Superadmin tidak terikat unit (unit_id null) sehingga melihat
 * seluruh unit; akun HR contoh dipasang di Pentacity untuk uji hak akses.
 */
class UserSeeder extends Seeder
{
    public function run(): void
    {
        $now    = date('Y-m-d H:i:s');
        $ptcId  = $this->db->table('units')->where('code', 'PTC')->get()->getRow('id');

        $users = [
            [null,   'Super Admin',   'admin@wbl.co.id',       'superadmin', 'IT Administrator'],
            [null,   'HRD Pusat',     'hrd@wbl.co.id',         'hr_admin',   'Manager HRD'],
            [$ptcId, 'Staf HR PTC',   'hr.pentacity@wbl.co.id','hr_staff',   'Staff HRD'],
        ];

        foreach ($users as [$unitId, $name, $email, $role, $position]) {
            $this->db->table('users')->ignore(true)->insert([
                'unit_id'       => $unitId,
                'name'          => $name,
                'email'         => $email,
                'password_hash' => password_hash('wbl12345', PASSWORD_DEFAULT),
                'role'          => $role,
                'position'      => $position,
                'is_active'     => 1,
                'created_at'    => $now,
                'updated_at'    => $now,
            ]);
        }
    }
}
