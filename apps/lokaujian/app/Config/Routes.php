<?php

use CodeIgniter\Router\RouteCollection;
use Lokandita\Auth\Routes as RuteBersama;

/** @var RouteCollection $routes */

// LokaUjian (SPEC-LOKAUJIAN.md §15): jalan masuk dan lingkup (U1), bank soal dan draf ujian (U2).
// Belum ada sesi dan pengerjaan; itu tahap U3 dan seterusnya.

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

// Bank soal dan draf ujian satu mata kuliah (tahap U2, SPEC-LOKAUJIAN.md §5, §6.3; KONTRAK.md §20).
// Hanya superadmin dan pengampu mata kuliah itu: mata kuliah di luar lingkup dijawab 404, pengajar
// mata kuliah itu 403 (BaseController::matkulDikelola). Setiap rute memuat id mata kuliahnya dan
// setiap kueri diikat ke id itu, jadi soal, kelompok atau ujian mata kuliah lain dijawab 404.
$routes->group('mata-kuliah/(:num)', ['filter' => 'masuk:superadmin,dosen'], static function (RouteCollection $routes): void {
    $routes->get('bank', 'Bank::daftar/$1');

    $routes->get('bank/soal/baru', 'Soal::baru/$1');
    $routes->post('bank/soal', 'Soal::buat/$1');
    $routes->get('bank/soal/(:num)', 'Soal::lihat/$1/$2');
    $routes->get('bank/soal/(:num)/sunting', 'Soal::sunting/$1/$2');
    $routes->post('bank/soal/(:num)', 'Soal::simpan/$1/$2');
    $routes->post('bank/soal/(:num)/arsip', 'Soal::arsip/$1/$2');
    $routes->post('bank/soal/(:num)/hapus', 'Soal::hapus/$1/$2');
    $routes->post('bank/soal/(:num)/geser', 'Soal::geser/$1/$2');

    $routes->get('bank/kelompok/baru', 'Kelompok::baru/$1');
    $routes->post('bank/kelompok', 'Kelompok::buat/$1');
    $routes->get('bank/kelompok/(:num)', 'Kelompok::lihat/$1/$2');
    $routes->get('bank/kelompok/(:num)/sunting', 'Kelompok::sunting/$1/$2');
    $routes->post('bank/kelompok/(:num)', 'Kelompok::simpan/$1/$2');
    $routes->post('bank/kelompok/(:num)/arsip', 'Kelompok::arsip/$1/$2');
    $routes->post('bank/kelompok/(:num)/hapus', 'Kelompok::hapus/$1/$2');

    $routes->get('ujian', 'Draf::daftar/$1');
    $routes->post('ujian', 'Draf::buat/$1');
    $routes->post('ujian/impor', 'Draf::impor/$1');
    $routes->get('ujian/(:num)', 'Draf::lihat/$1/$2');
    $routes->get('ujian/(:num)/ekspor', 'Draf::eksporHalaman/$1/$2');
    $routes->post('ujian/(:num)/ekspor', 'Draf::ekspor/$1/$2');
    $routes->post('ujian/(:num)/hapus', 'Draf::hapus/$1/$2');
});

// Gambar soal (SPEC-LOKAUJIAN.md §5.3): dijawab aplikasi sesudah hak akses diperiksa, tidak pernah
// sebagai berkas statis. Semua peran lewat filter; siapa yang boleh melihat gambar mana diputuskan
// di kueri (App\Libraries\Media) — tahap U2: hanya pengelola mata kuliahnya.
$routes->get('gambar/([0-9a-f]{32})', 'Gambar::tampil/$1', ['filter' => 'masuk']);
