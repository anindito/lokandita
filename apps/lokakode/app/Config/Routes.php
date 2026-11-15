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

// Superadmin saja (SPEC.md §3): tahun ajaran dan mata kuliah.
$routes->group('', ['filter' => 'masuk:superadmin'], static function (RouteCollection $routes): void {
    $routes->get('tahun-ajaran', 'TahunAjaran::index');
    $routes->post('tahun-ajaran', 'TahunAjaran::simpan');
    $routes->get('tahun-ajaran/(:num)', 'TahunAjaran::sunting/$1');
    $routes->post('tahun-ajaran/(:num)', 'TahunAjaran::perbarui/$1');
    $routes->post('tahun-ajaran/(:num)/aktifkan', 'TahunAjaran::aktifkan/$1');
    $routes->get('mata-kuliah/baru', 'MataKuliah::baru');
    $routes->post('mata-kuliah', 'MataKuliah::simpan');
    $routes->get('mata-kuliah/(:num)/ubah', 'MataKuliah::sunting/$1');
    $routes->post('mata-kuliah/(:num)', 'MataKuliah::perbarui/$1');
});
