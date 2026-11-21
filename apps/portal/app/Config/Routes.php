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
