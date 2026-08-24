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

/* ---------------------------------------------------------------------------
 | Autentikasi
 |---------------------------------------------------------------------------*/
$routes->get('login', 'Auth::login', ['filter' => 'guest']);
$routes->post('login', 'Auth::attempt', ['filter' => 'guest']);
$routes->get('logout', 'Auth::logout');

/* ---------------------------------------------------------------------------
 | Area internal HR — semua wajib login
 |---------------------------------------------------------------------------*/
$routes->group('', ['filter' => 'auth'], static function ($routes) {
    $routes->get('dashboard', 'Dashboard::index');

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
    $routes->get('pelamar/(:num)/cv', 'Applicants::downloadCv/$1');
    $routes->post('pelamar/(:num)/geser', 'Applicants::move/$1');
    $routes->post('pelamar/(:num)/catatan', 'Applicants::addNote/$1');
    $routes->post('pelamar/(:num)/nilai', 'Applicants::rate/$1');

    // Papan pipeline
    $routes->get('pipeline', 'Pipeline::index');

    // Interview
    $routes->get('interview', 'Interviews::index');
    $routes->get('interview/baru/(:num)', 'Interviews::create/$1');
    $routes->post('interview/baru/(:num)', 'Interviews::store/$1');
    $routes->post('interview/(:num)/status', 'Interviews::setStatus/$1');

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
