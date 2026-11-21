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
    $routes->post('sesi/perpanjang', 'Akun::perpanjang');
    $routes->post('preferensi', 'Akun::preferensi');
    // Ekspor data sendiri (KONTRAK.md §14).
    $routes->get('data-saya', 'DataSaya::index');
    $routes->post('data-saya/unduh', 'DataSaya::unduh');
});

// Superadmin saja (SPEC.md §3). Peran diperiksa oleh filter, bukan oleh tampilan.
$routes->group('admin', ['filter' => 'sesi:superadmin'], static function (RouteCollection $routes): void {
    $routes->get('pengguna', 'Admin\Pengguna::index');
    $routes->get('pengguna/ekspor', 'Admin\Pengguna::ekspor');
    $routes->get('pengguna/baru', 'Admin\Pengguna::baru');
    $routes->post('pengguna', 'Admin\Pengguna::simpan');
    $routes->post('pengguna/massal', 'Admin\Pengguna::massal');
    $routes->get('pengguna/(:segment)', 'Admin\Pengguna::sunting/$1');
    $routes->post('pengguna/(:segment)', 'Admin\Pengguna::perbarui/$1');
    $routes->post('pengguna/(:segment)/reset-sandi', 'Admin\Pengguna::resetSandi/$1');
    $routes->post('pengguna/(:segment)/hapus-mfa', 'Admin\Pengguna::hapusMfa/$1');
    $routes->get('impor', 'Admin\Impor::form');
    $routes->post('impor', 'Admin\Impor::unggah');
    $routes->post('impor/jalankan', 'Admin\Impor::jalankan');
    $routes->get('kredensial/(:segment)', 'Admin\Kredensial::unduh/$1');
    $routes->get('audit', 'Admin\AuditLog::index');
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
