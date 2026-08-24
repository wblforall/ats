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
});
