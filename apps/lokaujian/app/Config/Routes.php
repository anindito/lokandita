<?php

use CodeIgniter\Router\RouteCollection;
use Lokandita\Auth\Routes as RuteBersama;

/** @var RouteCollection $routes */

// LokaUjian tahap U1 (SPEC-LOKAUJIAN.md §15): jalan masuk dan lingkup. Belum ada soal, ujian
// atau sesi; itu tahap U2 dan seterusnya.

// KONTRAK.md §11: dipakai infra/deploy.sh untuk memastikan commit yang berjalan.
$routes->get('healthz', '\Lokandita\Auth\Controllers\Healthz::index');

// Menu akun: keluar, perpanjang sesi, preferensi (KONTRAK.md §4).
RuteBersama::akun($routes);

// Setiap rute baru WAJIB didaftarkan juga di tests/Policy/MatriksAksesTest.php;
// tes itu gagal bila ada rute di sini yang tidak ada di matriksnya.

// Tanpa sesi (KONTRAK.md §4 "Public routes"): halaman depan singkat dan pilihan bahasa.
// Tidak membaca basis data apa pun. /robots.txt adalah berkas statis di public/.
$routes->get('/', 'Depan::index');
RuteBersama::publik($routes);

// Semua pengguna yang masuk. Beranda mahasiswa sengaja di luar SEB (SPEC-LOKAUJIAN.md §9.1):
// daftarnya hanya untuk dibaca.
$routes->group('', ['filter' => 'masuk'], static function (RouteCollection $routes): void {
    $routes->get('beranda', 'Beranda::index');
});

// Staf (SPEC-LOKAUJIAN.md §3): mata kuliah dan kelas yang boleh dilihatnya. Lingkupnya — pengampu
// mata kuliahnya, pengajar kelasnya — diperiksa App\Policy\Akses di dalam kueri roster; di luar
// lingkup dijawab 404, sama dengan nomor yang tidak ada. Mahasiswa ditolak filter (403).
$routes->group('', ['filter' => 'masuk:superadmin,dosen'], static function (RouteCollection $routes): void {
    $routes->get('mata-kuliah', 'Matkul::daftar');
    $routes->get('mata-kuliah/(:num)', 'Matkul::lihat/$1');
    $routes->get('kelas/(:num)', 'Kelas::lihat/$1');
});
