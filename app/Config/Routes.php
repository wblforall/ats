<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

/* ---------------------------------------------------------------------------
 | Portal karir (publik) — pelamar tidak membuat akun. Mereka mengirim lamaran
 | sekali, lalu memantau prosesnya lewat nomor lamaran + email di /lacak.
 |---------------------------------------------------------------------------*/
$routes->get('/', 'Career::index');
$routes->get('karir', 'Career::index');
$routes->get('karir/terkirim', 'Career::submitted');
$routes->get('karir/(:segment)', 'Career::show/$1');
$routes->get('karir/(:segment)/lamar', 'Career::apply/$1');
$routes->post('karir/(:segment)/lamar', 'Career::submit/$1');

$routes->get('lacak', 'Track::form');
$routes->post('lacak', 'Track::check');

// Jawaban pelamar atas undangan interview (tautan bertanda tangan dari email).
$routes->get('jadwal-interview/(:num)/(:alphanum)', 'InterviewReply::show/$1/$2');
$routes->post('jadwal-interview/(:num)/(:alphanum)/hadir', 'InterviewReply::confirm/$1/$2');
$routes->post('jadwal-interview/(:num)/(:alphanum)/ulang', 'InterviewReply::reschedule/$1/$2');
$routes->post('jadwal-interview/(:num)/(:alphanum)/batal', 'InterviewReply::cancel/$1/$2');

/* ---------------------------------------------------------------------------
 | Autentikasi
 |---------------------------------------------------------------------------*/
$routes->get('login', 'Auth::login', ['filter' => 'guest']);
$routes->post('login', 'Auth::attempt', ['filter' => 'guest']);
$routes->get('logout', 'Auth::logout');
$routes->get('lupa-sandi', 'Auth::forgot', ['filter' => 'guest']);
$routes->post('lupa-sandi', 'Auth::sendReset', ['filter' => 'guest']);
$routes->get('reset/(:segment)', 'Auth::resetForm/$1', ['filter' => 'guest']);
$routes->post('reset/(:segment)', 'Auth::resetSave/$1', ['filter' => 'guest']);

/* ---------------------------------------------------------------------------
 | Area internal HR — semua wajib login
 |---------------------------------------------------------------------------*/
$routes->group('', ['filter' => 'auth'], static function ($routes) {
    $routes->get('dashboard', 'Dashboard::index');

    // Area kerja HR penuh — tertutup bagi interviewer, yang hanya melihat
    // agenda & penilaiannya sendiri.
    $routes->group('', ['filter' => 'auth:superadmin,hr_admin,hr_staff'], static function ($routes) {

    // Lowongan. Rute statis didaftarkan sebelum yang ber-parameter supaya
    // "baru" tidak tertangkap sebagai id lowongan.
    $routes->get('lowongan', 'Jobs::index');
    $routes->get('lowongan/baru', 'Jobs::create');
    $routes->post('lowongan', 'Jobs::store');
    $routes->get('lowongan/(:num)', 'Jobs::show/$1');
    $routes->get('lowongan/(:num)/ubah', 'Jobs::edit/$1');
    $routes->post('lowongan/(:num)/ubah', 'Jobs::update/$1');
    $routes->post('lowongan/(:num)/hapus', 'Jobs::destroy/$1');

    // Pelamar (lamaran di sisi HR)
    $routes->get('pelamar', 'Applicants::index');
    $routes->get('pelamar/(:num)', 'Applicants::show/$1');
    $routes->post('pelamar/(:num)/geser', 'Applicants::move/$1');
    $routes->post('pelamar/(:num)/catatan', 'Applicants::addNote/$1');
    $routes->post('pelamar/(:num)/nilai', 'Applicants::rate/$1');
    $routes->post('pelamar/(:num)/email', 'Applicants::sendEmail/$1');
    $routes->post('pelamar/(:num)/mundur', 'Applicants::withdraw/$1');

    // Papan pipeline
    $routes->get('pipeline', 'Pipeline::index');

    // Kandidat unggulan (skor potensi)
    $routes->get('unggulan', 'Shortlist::index');

    // Kandidat (bank talenta)
    $routes->get('kandidat', 'Candidates::index');
    $routes->get('kandidat/(:num)', 'Candidates::show/$1');
    $routes->post('kandidat/(:num)/blacklist', 'Candidates::blacklist/$1');

    // Penjadwalan interview & ubah status = aksi HR.
    $routes->get('interview/baru/(:num)', 'Interviews::create/$1');
    $routes->post('interview/baru/(:num)', 'Interviews::store/$1');
    $routes->get('interview/(:num)/ubah', 'Interviews::edit/$1');
    $routes->post('interview/(:num)/ubah', 'Interviews::updateInterview/$1');
    $routes->post('interview/(:num)/status', 'Interviews::setStatus/$1');
    $routes->post('interview/(:num)/jadwal-ulang/setujui', 'Interviews::approveReschedule/$1');
    $routes->post('interview/(:num)/jadwal-ulang/tolak', 'Interviews::declineReschedule/$1');

    }); // akhir grup HR penuh

    // Agenda & pengisian penilaian — boleh interviewer (cek "ditugaskan" di controller).
    $routes->get('interview', 'Interviews::index');
    $routes->get('interview/(:num)/nilai', 'Interviews::evaluate/$1');
    $routes->post('interview/(:num)/nilai', 'Interviews::saveEvaluation/$1');

    // Unduh CV: di luar grup HR penuh supaya interviewer yang DITUGASKAN pada
    // interview pelamar itu juga bisa mengunduhnya; cek akses di controller.
    $routes->get('pelamar/(:num)/cv', 'Applicants::downloadCv/$1');

    // Email, laporan, dan master lanjutan — khusus manajer HR.
    $routes->group('', ['filter' => 'auth:superadmin,hr_admin'], static function ($routes) {
        $routes->get('email', 'Emails::index');
        $routes->post('email/kirim-semua', 'Emails::flush');
        $routes->get('email/template', 'Emails::templates');
        $routes->get('email/template/(:num)', 'Emails::editTemplate/$1');
        $routes->post('email/template/(:num)', 'Emails::updateTemplate/$1');
        $routes->get('email/(:num)', 'Emails::view/$1');
        $routes->post('email/(:num)/kirim', 'Emails::send/$1');

        $routes->get('laporan', 'Reports::index');

        $routes->get('master/tahapan', 'Stages::index');
        $routes->post('master/tahapan', 'Stages::store');
        $routes->post('master/tahapan/urutkan', 'Stages::reorder');
        $routes->post('master/tahapan/(:num)/ubah', 'Stages::update/$1');
        $routes->post('master/tahapan/(:num)/hapus', 'Stages::destroy/$1');

        $routes->get('master/pengguna', 'Users::index');
        $routes->get('master/pengguna/baru', 'Users::create');
        $routes->post('master/pengguna', 'Users::store');
        $routes->get('master/pengguna/(:num)/ubah', 'Users::edit/$1');
        $routes->post('master/pengguna/(:num)/ubah', 'Users::update/$1');
        $routes->post('master/pengguna/(:num)/hapus', 'Users::destroy/$1');

        $routes->get('pengaturan', 'Settings::index');
        $routes->post('pengaturan', 'Settings::update');
    });

    // Pencarian global & bantuan (semua peran)
    $routes->get('cari', 'Search::index');
    $routes->get('bantuan', 'Help::index');

    // Profil sendiri (semua peran yang login)
    $routes->get('profil', 'Profile::index');
    $routes->post('profil', 'Profile::update');

    // Master unit usaha — hanya superadmin, karena unit adalah akar
    // pembatasan akses seluruh aplikasi.
    $routes->group('master/unit', ['filter' => 'auth:superadmin'], static function ($routes) {
        $routes->get('/', 'Units::index');
        $routes->post('/', 'Units::store');
        $routes->post('(:num)/ubah', 'Units::update/$1');
        $routes->post('(:num)/hapus', 'Units::destroy/$1');
    });

    // Master departemen — terikat pada unit usaha, tidak bisa menyeberang.
    $routes->get('master/departemen', 'Departments::index');
    $routes->post('master/departemen', 'Departments::store');
    $routes->post('master/departemen/(:num)/ubah', 'Departments::update/$1');
    $routes->post('master/departemen/(:num)/hapus', 'Departments::destroy/$1');
});
