<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * Data contoh untuk uji tampilan & alur. TIDAK dipanggil DatabaseSeeder —
 * jalankan manual: `php spark db:seed DemoSeeder`. Hapus sebelum dipakai
 * produksi dengan `php spark db:seed DemoSeeder --clear` tidak tersedia,
 * jadi bersihkan lewat migrate:refresh + db:seed DatabaseSeeder.
 */
class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $units  = array_column($this->db->table('units')->get()->getResultArray(), 'id', 'code');
        /* Departemen kini master tersendiri; dicari per unit supaya lowongan
           contoh tidak pernah menunjuk departemen milik unit lain. */
        $deptOf = function (int $unitId, string $nama) {
            $row = $this->db->table('departments')
                ->where('unit_id', $unitId)->where('name', $nama)->get()->getRowArray();

            return $row ? (int) $row['id'] : null;
        };
        $stages = array_column($this->db->table('stages')->orderBy('sort_order')->get()->getResultArray(), 'id', 'slug');
        $admin  = $this->db->table('users')->where('email', 'hrd@wbl.co.id')->get()->getRow('id');
        $now    = date('Y-m-d H:i:s');

        $jobs = [
            ['PTC', 'Supervisor Housekeeping',  'Operasional',   'full_time', 'Supervisor', 2, 4500000, 6000000],
            ['PTC', 'Staf Tenant Relation',     'Marketing',     'full_time', 'Staff',      1, 4000000, 5200000],
            ['EWM', 'Teknisi ME',               'Engineering',   'contract',  'Staff',      3, 3800000, 4800000],
            ['EWM', 'Admin Parkir',             'Operasional',   'full_time', 'Staff',      1, 3500000, 4200000],
            ['HO',  'Staf Akuntansi',           'Keuangan',      'full_time', 'Staff',      1, 5000000, 6500000],
            ['MIC', 'Data Analyst',             'IT',            'full_time', 'Staff',      1, 6500000, 9000000],
        ];

        $jobIds = [];
        foreach ($jobs as $i => [$unit, $title, $dept, $type, $level, $openings, $min, $max]) {
            $code = $unit . '-' . date('ym') . '-' . str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT);
            $this->db->table('jobs')->ignore(true)->insert([
                'unit_id'         => $units[$unit],
                'code'            => $code,
                'slug'            => url_title($title . ' ' . $code, '-', true),
                'title'           => $title,
                'department_id'   => $deptOf($units[$unit], $dept),
                'employment_type' => $type,
                'level'           => $level,
                'work_location'   => $unit === 'EST' ? 'Samarinda' : 'Balikpapan',
                'openings'        => $openings,
                'description'     => "Bertanggung jawab atas kelancaran operasional harian pada bagian {$dept}.",
                'requirements'    => "- Pendidikan minimal D3/S1\n- Pengalaman minimal 1 tahun di bidang terkait\n- Bersedia bekerja dengan sistem shift",
                'benefits'        => "- BPJS Kesehatan & Ketenagakerjaan\n- Tunjangan kehadiran\n- Jenjang karier",
                'salary_min'      => $min,
                'salary_max'      => $max,
                'salary_visible'  => 0,
                'status'          => $i < 5 ? 'open' : 'draft',
                'published_at'    => $i < 5 ? date('Y-m-d H:i:s', strtotime("-" . (20 - $i * 3) . " days")) : null,
                'closing_date'    => date('Y-m-d', strtotime('+30 days')),
                'created_by'      => $admin,
                'created_at'      => $now,
                'updated_at'      => $now,
            ]);
            $jobIds[] = (int) $this->db->insertID() ?: (int) $this->db->table('jobs')->where('code', $code)->get()->getRow('id');
        }

        $people = [
            ['Rizky Ramadhan',   'rizky.ramadhan@example.com', '081250110001', 'L', 'S1', 'Universitas Mulawarman', 'Manajemen',      3.0],
            ['Siti Nurhaliza',   'siti.nurhaliza@example.com', '081250110002', 'P', 'D3', 'Politeknik Balikpapan',  'Akuntansi',      2.0],
            ['Andi Prasetyo',    'andi.prasetyo@example.com',  '081250110003', 'L', 'S1', 'Institut Teknologi Kalimantan', 'Teknik Elektro', 4.5],
            ['Dewi Lestari',     'dewi.lestari@example.com',   '081250110004', 'P', 'S1', 'Universitas Balikpapan', 'Ilmu Komunikasi', 1.5],
            ['Budi Santoso',     'budi.santoso@example.com',   '081250110005', 'L', 'SMA/SMK', 'SMKN 1 Balikpapan',  'Teknik Mesin',    6.0],
            ['Maya Anggraini',   'maya.anggraini@example.com', '081250110006', 'P', 'S1', 'Universitas Mulawarman', 'Sistem Informasi', 2.5],
            ['Fajar Nugroho',    'fajar.nugroho@example.com',  '081250110007', 'L', 'S1', 'Universitas Gadjah Mada','Statistika',      3.5],
            ['Rina Wulandari',   'rina.wulandari@example.com', '081250110008', 'P', 'D3', 'Politeknik Negeri Samarinda', 'Administrasi', 1.0],
            ['Hendra Gunawan',   'hendra.gunawan@example.com', '081250110009', 'L', 'S1', 'Universitas Hasanuddin', 'Teknik Industri', 5.0],
            ['Putri Amelia',     'putri.amelia@example.com',   '081250110010', 'P', 'S1', 'Universitas Airlangga',  'Psikologi',       2.0],
            ['Yoga Pratama',     'yoga.pratama@example.com',   '081250110011', 'L', 'D3', 'Politeknik Balikpapan',  'Teknik Sipil',    1.5],
            ['Nadia Safitri',    'nadia.safitri@example.com',  '081250110012', 'P', 'S1', 'Universitas Mulawarman', 'Akuntansi',       3.0],
        ];

        $stageFlow = ['lamaran-masuk', 'lamaran-masuk', 'seleksi-berkas', 'seleksi-berkas', 'tes-asesmen',
            'interview-hr', 'interview-hr', 'interview-user', 'penawaran', 'diterima', 'tidak-lolos', 'lamaran-masuk'];

        foreach ($people as $i => [$name, $email, $phone, $gender, $edu, $school, $major, $exp]) {
            $this->db->table('candidates')->ignore(true)->insert([
                'full_name'        => $name,
                'email'            => $email,
                'phone'            => $phone,
                'gender'           => $gender,
                'birth_date'       => date('Y-m-d', strtotime('-' . (22 + $i) . ' years')),
                'city'             => 'Balikpapan',
                'education_level'  => $edu,
                'school'           => $school,
                'major'            => $major,
                'years_experience' => $exp,
                'current_position' => 'Staf ' . $major,
                'created_at'       => $now,
                'updated_at'       => $now,
            ]);
            $candidateId = (int) ($this->db->insertID() ?: $this->db->table('candidates')->where('email', $email)->get()->getRow('id'));

            $stageSlug = $stageFlow[$i];
            $stageId   = $stages[$stageSlug];
            $status    = match ($stageSlug) {
                'diterima'    => 'hired',
                'tidak-lolos' => 'rejected',
                default       => 'active',
            };
            $appliedAt = date('Y-m-d H:i:s', strtotime('-' . (14 - $i) . ' days'));

            $this->db->table('applications')->ignore(true)->insert([
                'candidate_id'    => $candidateId,
                'job_id'          => $jobIds[$i % 5],
                'stage_id'        => $stageId,
                'status'          => $status,
                'source'          => ['career_portal', 'referral', 'walk_in', 'jobstreet'][$i % 4],
                'expected_salary' => 4000000 + $i * 250000,
                'applied_at'      => $appliedAt,
                'closed_at'       => $status === 'active' ? null : date('Y-m-d H:i:s', strtotime('-1 day')),
                'created_at'      => $appliedAt,
                'updated_at'      => $now,
            ]);
            $appId = (int) ($this->db->insertID()
                ?: $this->db->table('applications')->where('candidate_id', $candidateId)->get()->getRow('id'));

            $this->db->table('application_history')->insert([
                'application_id' => $appId,
                'from_stage_id'  => null,
                'to_stage_id'    => $stages['lamaran-masuk'],
                'user_id'        => $admin,
                'note'           => 'Lamaran masuk melalui portal karir.',
                'created_at'     => $appliedAt,
            ]);

            if ($stageId !== $stages['lamaran-masuk']) {
                $this->db->table('application_history')->insert([
                    'application_id' => $appId,
                    'from_stage_id'  => $stages['lamaran-masuk'],
                    'to_stage_id'    => $stageId,
                    'user_id'        => $admin,
                    'note'           => null,
                    'created_at'     => date('Y-m-d H:i:s', strtotime($appliedAt . ' +3 days')),
                ]);
            }

            /* Jadwal interview untuk yang sudah masuk tahap wawancara. */
            if (in_array($stageSlug, ['interview-hr', 'interview-user', 'penawaran'], true)) {
                $this->db->table('interviews')->insert([
                    'application_id' => $appId,
                    'round'          => 1,
                    'title'          => 'Wawancara HRD',
                    'mode'           => $i % 2 === 0 ? 'onsite' : 'online',
                    'scheduled_at'   => date('Y-m-d H:i:s', strtotime('+' . ($i % 5 + 1) . ' days 10:00')),
                    'duration_min'   => 60,
                    'location'       => 'Ruang Rapat HRD, Lantai 3',
                    'meeting_link'   => $i % 2 === 0 ? null : 'https://meet.google.com/contoh-demo',
                    'status'         => 'scheduled',
                    'created_by'     => $admin,
                    'created_at'     => $now,
                    'updated_at'     => $now,
                ]);
                $interviewId = (int) $this->db->insertID();
                $this->db->table('interview_participants')->ignore(true)->insert([
                    'interview_id' => $interviewId,
                    'user_id'      => $admin,
                    'created_at'   => $now,
                ]);
            }
        }
    }
}
