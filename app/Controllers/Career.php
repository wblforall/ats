<?php

namespace App\Controllers;

use App\Libraries\EmailQueue;
use App\Models\ApplicationModel;
use App\Models\CandidateModel;
use App\Models\JobModel;
use App\Models\SettingModel;
use App\Models\StageModel;
use App\Models\UnitModel;

/**
 * Portal karir publik. Pelamar TIDAK membuat akun: cukup mengirim data sekali,
 * lalu memantau prosesnya di halaman pelacakan memakai nomor lamaran + email
 * yang dikirimkan ke inbox mereka.
 */
class Career extends BaseController
{
    /** Daftar lowongan yang sedang tayang, dengan penyaring unit & jenis kerja. */
    public function index()
    {
        $jobs = model(JobModel::class)->published();

        $unitId = (int) $this->request->getGet('unit');
        $type   = (string) $this->request->getGet('tipe');
        $q      = trim((string) $this->request->getGet('q'));

        if ($unitId) {
            $jobs->where('jobs.unit_id', $unitId);
        }
        if ($type !== '' && isset(JobModel::EMPLOYMENT_TYPES[$type])) {
            $jobs->where('jobs.employment_type', $type);
        }
        if ($q !== '') {
            $jobs->groupStart()
                ->like('jobs.title', $q)
                ->orLike('departments.name', $q)
                ->orLike('jobs.work_location', $q)
                ->groupEnd();
        }

        $settings = model(SettingModel::class);

        /* Alur yang ditampilkan ke pelamar diambil dari master tahapan, bukan
           ditulis mati di tampilan — jadi kalau HR mengubah pipeline, halaman
           depan ikut menyesuaikan. Hanya tahap berjalan yang relevan bagi
           pelamar; tahap "diterima"/"tidak lolos" adalah hasil akhir. */
        $flow = array_values(array_filter(
            model(StageModel::class)->active(),
            static fn (array $stage) => $stage['kind'] === 'open'
        ));

        /* Dropdown filter hanya memuat pilihan yang benar-benar ada lowongan
           dibukanya — tak perlu tampilkan unit/jenis yang kosong. Dihitung lewat
           query independen (bukan model bersama yang sedang dipakai $jobs) atas
           SEMUA lowongan tayang tanpa filter, agar pilihan tetap lengkap. */
        $now = date('Y-m-d H:i:s');
        $tayang = db_connect()->table('jobs')
            ->select('unit_id, employment_type')
            ->where('status', 'open')
            ->where('deleted_at', null)
            ->where('published_at IS NOT NULL')
            ->where('published_at <=', $now)
            ->groupStart()
                ->where('closing_date IS NULL')
                ->orWhere('closing_date >=', date('Y-m-d'))
            ->groupEnd()
            ->get()->getResultArray();
        $unitAda  = array_unique(array_column($tayang, 'unit_id'));
        $jenisAda = array_unique(array_column($tayang, 'employment_type'));

        $units = array_values(array_filter(
            model(UnitModel::class)->active(),
            static fn (array $u) => in_array((int) $u['id'], array_map('intval', $unitAda), true)
        ));

        return view('career/index', [
            'title'    => 'Karir',
            'jobs'     => $jobs->orderBy('jobs.published_at', 'DESC')->findAll(),
            'units'    => $units,
            'jenisAda' => $jenisAda,
            'flow'     => $flow,
            'filters'  => ['unit' => $unitId, 'tipe' => $type, 'q' => $q],
            'settings' => $settings->all(),
        ]);
    }

    /** Detail satu lowongan. */
    public function show(string $slug)
    {
        $job = model(JobModel::class)->findPublishedBySlug($slug);

        if (! $job) {
            return redirect()->to(site_url('karir'))
                ->with('error', 'Lowongan tidak ditemukan atau sudah ditutup.');
        }

        return view('career/show', [
            'title'    => $job['title'],
            'job'      => $job,
            'settings' => model(SettingModel::class)->all(),
        ]);
    }

    /** Formulir lamaran. */
    public function apply(string $slug)
    {
        $job = model(JobModel::class)->findPublishedBySlug($slug);

        if (! $job) {
            return redirect()->to(site_url('karir'))
                ->with('error', 'Lowongan tidak ditemukan atau sudah ditutup.');
        }

        return view('career/apply', [
            'title'    => 'Lamar ' . $job['title'],
            'job'      => $job,
            'settings' => model(SettingModel::class)->all(),
        ]);
    }

    /**
     * Simpan lamaran. Kandidat dicocokkan lewat email supaya satu orang yang
     * melamar beberapa posisi tidak terpecah jadi banyak baris kandidat.
     */
    public function submit(string $slug)
    {
        $job = model(JobModel::class)->findPublishedBySlug($slug);

        if (! $job) {
            return redirect()->to(site_url('karir'))
                ->with('error', 'Lowongan tidak ditemukan atau sudah ditutup.');
        }

        $settings = model(SettingModel::class);
        $maxMb    = (int) $settings->value('cv_max_mb', '5');
        $exts     = $settings->value('cv_allowed_ext', 'pdf,doc,docx');

        /* Nominal diketik dengan pemisah ribuan (5.500.000). Titik dibuang di
           sini, sebelum validasi, supaya aturan `numeric` melihat angka murni.
           Sisi browser sudah membersihkannya juga, tapi ini jaring pengaman
           bila JavaScript mati.

           `validate()` membaca datanya lewat getVar() — yaitu superglobal
           `request`, bukan `post` — jadi keduanya harus ikut diperbarui.
           Menyetel `post` saja membuat pembersihan ini tidak pernah terlihat
           oleh validator. */
        $post = $this->request->getPost();
        if (isset($post['expected_salary'])) {
            $post['expected_salary'] = $this->normalizeAmount((string) $post['expected_salary']);

            $this->request->setGlobal('post', $post);
            $this->request->setGlobal('request', array_merge(
                (array) $this->request->getGet(),
                $post
            ));
        }

        /* Daftar wajib mengikuti kebutuhan penyaringan HRD: identitas & kontak,
           data yang dipakai menyaring cepat tanpa membuka CV (usia, pendidikan,
           domisili), ekspektasi gaji agar proses tidak buntu di tahap penawaran,
           serta Instagram sebagai bahan referensi. LinkedIn tetap opsional
           karena banyak pelamar non-kantoran tidak memilikinya. */
        $rules = [
            'full_name'       => 'required|max_length[160]',
            'email'           => 'required|valid_email|max_length[160]',
            'phone'           => 'required|max_length[30]',
            'gender'          => 'required|in_list[L,P]',
            'birth_date'      => 'required|valid_date[Y-m-d]',
            'city'            => 'required|max_length[100]',
            'education_level' => 'required|max_length[40]',
            'school'          => 'required|max_length[160]',
            'instagram'       => 'required|max_length[120]',
            'expected_salary' => 'required|numeric|greater_than[0]',
            'linkedin_url'    => 'permit_empty|max_length[255]',
            'cv'              => "uploaded[cv]|max_size[cv," . ($maxMb * 1024) . "]|ext_in[cv,{$exts}]",
        ];

        $messages = [
            'full_name' => ['required' => 'Nama lengkap wajib diisi.'],
            'email'     => [
                'required'    => 'Email wajib diisi — nomor lamaran dikirim ke sini.',
                'valid_email' => 'Format email tidak valid.',
            ],
            'phone'      => ['required' => 'Nomor HP wajib diisi.'],
            'gender'     => [
                'required' => 'Jenis kelamin wajib dipilih.',
                'in_list'  => 'Jenis kelamin tidak dikenal.',
            ],
            'birth_date' => [
                'required'   => 'Tanggal lahir wajib diisi.',
                'valid_date' => 'Format tanggal lahir tidak valid.',
            ],
            'city'            => ['required' => 'Kota domisili wajib diisi.'],
            'education_level' => ['required' => 'Pendidikan terakhir wajib dipilih.'],
            'school'          => ['required' => 'Nama sekolah/universitas wajib diisi.'],
            'instagram'       => ['required' => 'Akun Instagram wajib diisi.'],
            'expected_salary' => [
                'required'     => 'Gaji yang diharapkan wajib diisi.',
                'numeric'      => 'Gaji yang diharapkan harus berupa angka.',
                'greater_than' => 'Gaji yang diharapkan harus lebih dari 0.',
            ],
            'cv' => [
                'uploaded' => 'Berkas CV wajib diunggah.',
                'max_size' => "Ukuran CV maksimal {$maxMb} MB.",
                'ext_in'   => "Format CV harus salah satu dari: {$exts}.",
            ],
        ];

        if (! $this->validate($rules, $messages)) {
            return redirect()->back()->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $candidates = model(CandidateModel::class);
        $email      = strtolower(trim((string) $this->request->getPost('email')));

        $candidateId = $candidates->findOrCreate([
            'full_name'        => trim((string) $this->request->getPost('full_name')),
            'email'            => $email,
            'phone'            => trim((string) $this->request->getPost('phone')),
            'gender'           => $this->request->getPost('gender') ?: null,
            'birth_date'       => $this->request->getPost('birth_date') ?: null,
            'city'             => $this->request->getPost('city') ?: null,
            'education_level'  => $this->request->getPost('education_level') ?: null,
            'school'           => $this->request->getPost('school') ?: null,
            'major'            => $this->request->getPost('major') ?: null,
            'years_experience' => $this->request->getPost('years_experience') !== '' ? $this->request->getPost('years_experience') : null,
            'current_position' => $this->request->getPost('current_position') ?: null,
            'current_company'  => $this->request->getPost('current_company') ?: null,
            'instagram'        => $this->normalizeInstagram((string) $this->request->getPost('instagram')),
            'linkedin_url'     => $this->request->getPost('linkedin_url') ?: null,
            'portfolio_url'    => $this->request->getPost('portfolio_url') ?: null,
        ]);

        if ($candidateId === null) {
            return redirect()->back()->withInput()
                ->with('error', 'Data Anda gagal disimpan. Silakan coba lagi, atau hubungi HRD bila terus berulang.');
        }

        $apps = model(ApplicationModel::class);

        /* Cegah lamaran ganda ke lowongan yang sama, dengan pesan yang ramah
           dan mengarahkan ke halaman pelacakan alih-alih error mentah. */
        if ($apps->existsFor($candidateId, (int) $job['id'])) {
            return redirect()->to(site_url('lacak'))
                ->with('info', 'Anda sudah pernah melamar posisi ini. Silakan lacak status lamaran Anda di halaman ini.');
        }

        $file = $this->request->getFile('cv');
        $dir  = WRITEPATH . 'uploads/cv/' . date('Y/m');
        if (! is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        $newName = $file->getRandomName();
        $file->move($dir, $newName);

        $stageId = model(StageModel::class)->firstStage()['id'] ?? null;
        $now     = date('Y-m-d H:i:s');

        $applicationId = $apps->insert([
            'candidate_id'    => $candidateId,
            'job_id'          => (int) $job['id'],
            'stage_id'        => $stageId,
            'status'          => 'active',
            'source'          => $this->request->getPost('source') ?: 'career_portal',
            'referral_name'   => $this->request->getPost('referral_name') ?: null,
            'cv_path'         => 'uploads/cv/' . date('Y/m') . '/' . $newName,
            'cv_original'     => $file->getClientName(),
            'cover_letter'    => $this->request->getPost('cover_letter') ?: null,
            'expected_salary' => $this->request->getPost('expected_salary') !== '' ? $this->request->getPost('expected_salary') : null,
            'available_from'  => $this->request->getPost('available_from') ?: null,
            'applied_at'      => $now,
        ], true);

        /* Tanpa pemeriksaan ini, kegagalan simpan akan lolos sebagai id 0:
           riwayat tahap gagal disisipkan diam-diam dan pelamar menerima nomor
           WBL-2026-000000 untuk lamaran yang sebenarnya tidak pernah ada. */
        if ($applicationId === false) {
            @unlink($dir . '/' . $newName);

            return redirect()->back()->withInput()
                ->with('error', 'Lamaran Anda gagal disimpan. Silakan coba lagi, atau hubungi HRD bila terus berulang.');
        }

        $applicationId = (int) $applicationId;

        db_connect()->table('application_history')->insert([
            'application_id' => $applicationId,
            'from_stage_id'  => null,
            'to_stage_id'    => $stageId,
            'user_id'        => null,
            'note'           => 'Lamaran masuk melalui portal karir.',
            'created_at'     => $now,
        ]);

        if ($settings->value('auto_email_on_apply', '1') === '1') {
            (new EmailQueue())->queueForApplication('application_received', $applicationId);
        }

        $this->session->setFlashdata('application_ref', $apps->referenceCode($applicationId, $now));
        $this->session->setFlashdata('application_email', $email);

        return redirect()->to(site_url('karir/terkirim'));
    }

    /** Buang pemisah ribuan dari nominal yang diketik pengguna. */
    private function normalizeAmount(string $raw): string
    {
        return preg_replace('/[^0-9]/', '', $raw) ?? '';
    }

    /**
     * Seragamkan isian Instagram menjadi username tanpa "@" maupun URL.
     * Pelamar mengisinya bermacam-macam (@nama, instagram.com/nama, nama),
     * dan HR perlu bentuk yang konsisten agar mudah dibuka & dicari.
     */
    private function normalizeInstagram(string $raw): ?string
    {
        $value = trim($raw);
        if ($value === '') {
            return null;
        }

        $value = preg_replace('#^https?://#i', '', $value);
        $value = preg_replace('#^(www\.)?instagram\.com/#i', '', $value);
        $value = ltrim($value, '@');
        $value = explode('/', $value)[0];
        $value = explode('?', $value)[0];

        return $value !== '' ? $value : null;
    }

    /** Halaman terima kasih yang menampilkan nomor lamaran. */
    public function submitted()
    {
        $ref = $this->session->getFlashdata('application_ref');

        if (! $ref) {
            return redirect()->to(site_url('karir'));
        }

        return view('career/submitted', [
            'title'    => 'Lamaran Terkirim',
            'ref'      => $ref,
            'email'    => $this->session->getFlashdata('application_email'),
            'settings' => model(SettingModel::class)->all(),
        ]);
    }
}
