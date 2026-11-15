<?php

use CodeIgniter\Router\RouteCollection;
use Lokandita\Auth\Routes as RuteBersama;

/** @var RouteCollection $routes */

// KONTRAK.md §11: dipakai infra/deploy.sh untuk memastikan commit yang berjalan.
$routes->get('healthz', '\Lokandita\Auth\Controllers\Healthz::index');

// Menu akun: keluar, perpanjang sesi, preferensi (KONTRAK.md §4).
RuteBersama::akun($routes);

// Setiap rute baru WAJIB didaftarkan juga di tests/Policy/MatriksAksesTest.php;
// tes itu gagal bila ada rute di sini yang tidak ada di matriksnya.

// Tanpa sesi: akar hanya mengalihkan ke beranda. Halaman depan publik LokaKode
// (SPEC.md §15) dibangun di tahap 11.
$routes->get('/', 'Beranda::akar');

// Semua pengguna yang masuk.
$routes->group('', ['filter' => 'masuk'], static function (RouteCollection $routes): void {
    $routes->get('beranda', 'Beranda::index');
});

// Staf (superadmin dan dosen). Peran global diperiksa filter; lingkupnya
// (mata kuliah dan kelas yang mana) diperiksa App\Policy\Akses di dalam kueri.
$routes->group('', ['filter' => 'masuk:superadmin,dosen'], static function (RouteCollection $routes): void {
    $routes->get('mata-kuliah', 'MataKuliah::index');
    $routes->get('mata-kuliah/(:num)', 'MataKuliah::lihat/$1');
    $routes->get('kelas/(:num)', 'Kelas::lihat/$1');
});
