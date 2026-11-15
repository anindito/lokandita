<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

// KONTRAK.md §11: dipakai infra/deploy.sh untuk memastikan commit yang berjalan.
$routes->get('healthz', '\Lokandita\Auth\Controllers\Healthz::index');

// Tanpa sesi.
$routes->get('login', 'Login::form');
$routes->post('login', 'Login::submit');
// Langkah kedua login: hanya bisa dicapai setelah kata sandi benar (cookie __Host-tunda).
$routes->get('login/kode', 'MfaLogin::formKode');
$routes->post('login/kode', 'MfaLogin::kirimKode');
$routes->get('login/mfa-daftar', 'MfaLogin::formDaftar');
$routes->post('login/mfa-daftar', 'MfaLogin::kirimDaftar');
$routes->get('bahasa/(:segment)', 'Language::set/$1');
$routes->get('keluar-selesai', 'Logout::selesai');

// Dengan sesi. Filter `sesi` juga memaksa ganti kata sandi bila diwajibkan.
$routes->group('', ['filter' => 'sesi'], static function (RouteCollection $routes): void {
    $routes->get('/', 'Home::index');
    $routes->get('ganti-password', 'ChangePassword::form');
    $routes->post('ganti-password', 'ChangePassword::submit');
    $routes->post('keluar', 'Logout::keluar');
});

// API internal (KONTRAK.md §5). Dari internet Caddy menjawab 404 untuk /internal/*.
$routes->group('internal', ['filter' => 'layanan'], static function (RouteCollection $routes): void {
    $routes->post('session/touch', 'Internal::touch');
    $routes->post('session/revoke', 'Internal::revoke');
    $routes->post('session/prefs', 'Internal::prefs');
    $routes->get('users/(:segment)', 'Internal::user/$1');
    $routes->post('users/lookup', 'Internal::lookup');
    $routes->post('password-reset', 'Internal::passwordReset');
    // Rute internal yang tidak dikenal dijawab JSON, bukan halaman HTML.
    $routes->add('/', 'Internal::unknown');
    $routes->add('(:any)', 'Internal::unknown');
});
