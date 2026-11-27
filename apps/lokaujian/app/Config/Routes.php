<?php

use CodeIgniter\Router\RouteCollection;
use Lokandita\Auth\Routes as RuteBersama;

/** @var RouteCollection $routes */

// LokaUjian (SPEC-LOKAUJIAN.md §15): jalan masuk dan lingkup (U1), bank soal dan draf ujian (U2),
// penerbitan, sesi dan pengerjaan (U3), Safe Exam Browser per ujian dan peristiwa integritas (U4).
// Tampilan langsung, tambahan waktu, buka ulang, nilai, koreksi, rilis, CSV, log audit, data saya (U5).

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
    // Ekspor data sendiri (KONTRAK.md §14): di luar SEB, tanpa id di alamatnya.
    $routes->get('data-saya', 'DataSaya::index');
    $routes->post('data-saya/unduh', 'DataSaya::unduh');
});

// Log audit (SPEC-LOKAUJIAN.md §13): superadmin saja, hanya membaca.
$routes->get('audit', 'AuditLog::daftar', ['filter' => 'masuk:superadmin']);

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

    // Tahap U3 (SPEC-LOKAUJIAN.md §6; KONTRAK.md §22): menyusun dan mengatur draf, menerbitkan
    // (isi dibekukan), menarik, mengarsipkan, dan memberikan ujian terbit kepada satu kelas.
    $routes->get('ujian/(:num)/sunting', 'Draf::sunting/$1/$2');
    $routes->post('ujian/(:num)', 'Draf::simpan/$1/$2');
    $routes->post('ujian/(:num)/butir', 'Draf::tambahButir/$1/$2');
    $routes->post('ujian/(:num)/butir/(:num)/geser', 'Draf::geserButir/$1/$2/$3');
    $routes->post('ujian/(:num)/butir/(:num)/hapus', 'Draf::hapusButir/$1/$2/$3');
    $routes->post('ujian/(:num)/terbit', 'Draf::terbit/$1/$2');
    $routes->post('ujian/(:num)/tarik', 'Draf::tarik/$1/$2');
    $routes->post('ujian/(:num)/arsip', 'Draf::arsip/$1/$2');
    $routes->post('ujian/(:num)/sesi', 'SesiStaf::buat/$1/$2');

    // Tahap U4 (SPEC-LOKAUJIAN.md §9.1; KONTRAK.md §23): Safe Exam Browser PER UJIAN — membuat atau
    // merotasi konfigurasi, mengunduh berkas .seb, mendaftarkan Browser Exam Key.
    $routes->get('ujian/(:num)/seb', 'SebPanel::lihat/$1/$2');
    $routes->post('ujian/(:num)/seb', 'SebPanel::simpan/$1/$2');
    $routes->post('ujian/(:num)/seb/kunci', 'SebPanel::kunci/$1/$2');
    $routes->get('ujian/(:num)/seb/unduh', 'SebPanel::unduh/$1/$2');

    // Tahap U5 (SPEC-LOKAUJIAN.md §11; KONTRAK.md §24): koreksi kunci atau poin sesudah ujian.
    $routes->get('ujian/(:num)/koreksi', 'Koreksi::lihat/$1/$2');
    $routes->post('ujian/(:num)/koreksi/(:num)', 'Koreksi::ubah/$1/$2/$3');
});

// Sesi (tahap U3, SPEC-LOKAUJIAN.md §7; KONTRAK.md §22): siapa pun yang MELIHAT kelasnya boleh
// menjalankannya — superadmin, pengampu mata kuliahnya, pengajar kelas itu. Lingkupnya diperiksa di
// kueri (App\Libraries\Sesi + Akses::diJalankan); sesi kelas lain dijawab 404. Jadwal dan hapus
// hanya bagi yang mengelola mata kuliahnya (404 bagi pengajar: baginya tindakan itu tidak ada).
$routes->group('sesi/(:num)', ['filter' => 'masuk:superadmin,dosen'], static function (RouteCollection $routes): void {
    $routes->get('', 'SesiStaf::lihat/$1');
    $routes->get('pratinjau', 'SesiStaf::pratinjau/$1');
    $routes->get('peristiwa', 'SesiStaf::peristiwa/$1');
    $routes->post('jadwal', 'SesiStaf::jadwal/$1');
    $routes->post('buka', 'SesiStaf::buka/$1');
    $routes->post('tutup', 'SesiStaf::tutup/$1');
    $routes->post('mulai', 'SesiStaf::mulai/$1');
    $routes->post('akhir', 'SesiStaf::akhir/$1');
    $routes->post('akhiri', 'SesiStaf::akhiri/$1');
    $routes->post('hapus', 'SesiStaf::hapus/$1');
    // Tahap U5 (SPEC-LOKAUJIAN.md §7.3, §10, §12; KONTRAK.md §24): tampilan langsung, tambahan
    // waktu, buka ulang dan CSV bagi yang menjalankan sesi; rilis hanya bagi yang mengelola mata
    // kuliahnya (404 bagi pengajar, seperti jadwal).
    $routes->get('langsung', 'SesiStaf::langsung/$1');
    $routes->post('tambah-waktu', 'SesiStaf::tambahWaktu/$1');
    $routes->post('buka-ulang', 'SesiStaf::bukaUlang/$1');
    $routes->post('csv', 'SesiStaf::csv/$1');
    $routes->post('rilis', 'SesiStaf::rilis/$1');
    $routes->post('rilis/tarik', 'SesiStaf::tarikRilis/$1');
});
// CSV semua sesi satu ujian yang boleh DILIHAT pemintanya (pengajar: kelasnya sendiri saja).
$routes->post('ujian/(:num)/csv', 'SesiStaf::csvUjian/$1', ['filter' => 'masuk:superadmin,dosen']);

// Mahasiswa (SPEC-LOKAUJIAN.md §8, §9.1): mulai, menunggu, halaman ujian, simpan, kumpulkan.
// Filter `seb` (App\Filters\SebUjian, KONTRAK.md §23) selalu SESUDAH `masuk`: kedua header SEB
// harus cocok dengan konfigurasi UJIAN sesi itu. Alamatnya memuat id SESI saja: pengerjaan dicari
// dari pengguna yang masuk.
$routes->group('', ['filter' => ['masuk:mahasiswa', 'seb']], static function (RouteCollection $routes): void {
    $routes->get('ujian-saya/(:num)', 'Kerja::lihat/$1');
    $routes->post('ujian-saya/(:num)/mulai', 'Kerja::mulai/$1');
    $routes->get('ujian-saya/(:num)/kerjakan', 'Kerja::kerjakan/$1');
    $routes->post('ujian-saya/(:num)/kumpulkan', 'Kerja::kumpulkan/$1');
    // Tinjauan sesudah hasil dirilis dengan kunci (SPEC-LOKAUJIAN.md §11): di dalam SEB.
    $routes->get('ujian-saya/(:num)/tinjau', 'Kerja::tinjau/$1');
    $routes->get('api/sesi/(:num)/keadaan', 'Kerja::keadaan/$1');
    $routes->post('api/sesi/(:num)/jawaban', 'Kerja::jawaban/$1');
    $routes->post('api/sesi/(:num)/peristiwa', 'Kerja::peristiwa/$1');
    // Denyut SEB tiap 25 detik (KONTRAK.md §8): filter `seb` yang memeriksa dan memperpanjang sewa.
    $routes->post('api/seb/denyut', '\Lokandita\Auth\Controllers\Seb::denyut');
});

// Di LUAR filter `seb` (SPEC-LOKAUJIAN.md §9.1): berkas .seb sebuah ujian diunduh mahasiswanya di
// peramban biasa, dari beranda; dan alamat masuk (startURL berkas itu) hanya mengarahkan ke sesinya.
$routes->get('ujian-saya/(:num)/seb', 'Kerja::unduhSeb/$1', ['filter' => 'masuk:mahasiswa']);
$routes->get('ujian/(:num)/masuk', 'Kerja::masuk/$1', ['filter' => 'masuk']);

// Safe Exam Browser, tanpa sesi (KONTRAK.md §4, §23): petunjuk, periksa header, alamat keluar
// (quitURL). Tidak ada daftar atau unduhan publik: konfigurasi LokaUjian per ujian.
$routes->get('seb', 'SebPublik::petunjuk');
$routes->get('seb/periksa', 'SebPublik::periksa');
$routes->get('seb/keluar', '\Lokandita\Auth\Controllers\Seb::keluar');

// Gambar soal (SPEC-LOKAUJIAN.md §5.3): dijawab aplikasi sesudah hak akses diperiksa, tidak pernah
// sebagai berkas statis. Semua peran lewat filter; siapa yang boleh melihat gambar mana diputuskan
// di kueri (App\Libraries\Media): pengelola mata kuliahnya; staf yang menjalankan sesi ujian terbit
// yang memuatnya; mahasiswa hanya selama pengerjaannya atas ujian itu berjalan.
// Bagi mahasiswa gambar soal adalah bagian dari ujian, jadi juga di belakang `seb`.
$routes->get('gambar/([0-9a-f]{32})', 'Gambar::tampil/$1', ['filter' => ['masuk', 'seb']]);
