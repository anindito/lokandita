<?php

use CodeIgniter\Router\RouteCollection;
use Lokandita\Auth\Routes as RuteBersama;

/** @var RouteCollection $routes */

// KONTRAK.md §11: dipakai infra/deploy.sh untuk memastikan commit yang berjalan.
$routes->get('healthz', '\Lokandita\Auth\Controllers\Healthz::index');

// Menu akun: keluar, perpanjang sesi, preferensi (KONTRAK.md §4).
RuteBersama::akun($routes);

// Tanpa sesi: pilihan bahasa (KONTRAK.md §4 "Public routes").
RuteBersama::publik($routes);

// Halaman publik (SPEC.md §15): tanpa sesi, tanpa basis data, boleh diindeks.
$routes->get('/', 'Publik::depan');
$routes->get('tentang', 'Publik::tentang');
$routes->get('kontak', 'Publik::kontak');
$routes->get('privasi', 'Publik::privasi');
$routes->get('ketentuan', 'Publik::ketentuan');
$routes->get('sitemap.xml', 'Publik::peta');
$routes->get('robots.txt', 'Publik::robot');

// Setelah masuk — setiap sesi yang hidup, peran apa pun (KONTRAK.md §4 langkah 4).
$routes->group('', ['filter' => 'masuk'], static function (RouteCollection $routes): void {
    $routes->get('beranda', 'Beranda::index');
    $routes->get('data-saya', 'Beranda::dataSaya');
});

// Penyuntingan teks dan foto halaman publik (KONTRAK.md §15, §16): superadmin saja. Peran diperiksa filter.
$routes->group('admin/teks', ['filter' => 'masuk:superadmin'], static function (RouteCollection $routes): void {
    $routes->get('/', 'Admin\Teks::index');
    $routes->get('riwayat', 'Admin\Teks::riwayat');
    // Foto pembuka (KONTRAK.md §16). Didaftarkan sebelum rute {halaman}/{bahasa} di bawah.
    $routes->get('foto', 'Admin\Foto::index');
    $routes->post('foto', 'Admin\Foto::unggah');
    $routes->post('foto/bawaan', 'Admin\Foto::bawaan');
    $routes->get('foto/(:num)', 'Admin\Foto::lihat/$1');
    $routes->post('foto/(:num)/tayang', 'Admin\Foto::tayang/$1');
    $routes->get('(:segment)/(:segment)', 'Admin\Teks::sunting/$1/$2');
    $routes->post('(:segment)/(:segment)', 'Admin\Teks::kirim/$1/$2');
    $routes->post('(:segment)/(:segment)/bawaan', 'Admin\Teks::bawaan/$1/$2');
});
