<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/** Seeder utama: `php spark db:seed DatabaseSeeder` mengisi seluruh master data. */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call('UnitSeeder');
        $this->call('StageSeeder');
        $this->call('UserSeeder');
        $this->call('EmailTemplateSeeder');
        $this->call('SettingSeeder');
    }
}
