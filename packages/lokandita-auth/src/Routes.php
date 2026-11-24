<?php

declare(strict_types=1);

namespace Lokandita\Auth;

use CodeIgniter\Router\RouteCollection;

/**
 * Rute yang sama di setiap aplikasi klien (KONTRAK.md §4). Dipanggil dari app/Config/Routes.php.
 */
final class Routes
{
    public static function akun(RouteCollection $routes): void
    {
        $routes->group('', ['filter' => 'masuk'], static function (RouteCollection $routes): void {
            $routes->post('keluar', '\Lokandita\Auth\Controllers\Akun::keluar');
            $routes->post('sesi/perpanjang', '\Lokandita\Auth\Controllers\Akun::perpanjang');
            $routes->post('preferensi', '\Lokandita\Auth\Controllers\Akun::preferensi');
        });
    }

    /**
     * Rute tanpa sesi yang sama di setiap aplikasi klien: pilihan bahasa sebelum masuk.
     */
    public static function publik(RouteCollection $routes): void
    {
        $routes->get('bahasa/(:segment)', '\Lokandita\Auth\Controllers\Bahasa::atur/$1');
    }

    /**
     * Safe Exam Browser (KONTRAK.md §8), jalur yang sama di setiap aplikasi yang memakainya.
     * Aplikasi itu harus punya alias filter `masuk` dan `seb`, dan app/Config/Seb.php.
     */
    public static function seb(RouteCollection $routes): void
    {
        // Tanpa sesi: petunjuk, berkas .seb per mata kuliah, halaman periksa header, alamat keluar (quitURL).
        $routes->get('seb', '\Lokandita\Auth\Controllers\Seb::petunjuk');
        $routes->get('seb/periksa', '\Lokandita\Auth\Controllers\Seb::periksa');
        $routes->get('seb/keluar', '\Lokandita\Auth\Controllers\Seb::keluar');
        $routes->get('seb/unduh/(:segment)', '\Lokandita\Auth\Controllers\Seb::unduh/$1');
        // Denyut tiap 25 detik: filter `seb` yang memeriksa header dan memperpanjang sewa.
        $routes->group('', ['filter' => ['masuk:mahasiswa', 'seb']], static function (RouteCollection $routes): void {
            $routes->post('api/seb/denyut', '\Lokandita\Auth\Controllers\Seb::denyut');
        });
        // Panel konfigurasi mata kuliah: superadmin dan pengampunya (Sumber::matkulUntukPanel); pengajar 403.
        $routes->group('', ['filter' => 'masuk:superadmin,dosen'], static function (RouteCollection $routes): void {
            $routes->get('mata-kuliah/(:num)/seb', '\Lokandita\Auth\Controllers\SebKelola::lihat/$1');
            $routes->post('mata-kuliah/(:num)/seb', '\Lokandita\Auth\Controllers\SebKelola::simpan/$1');
            $routes->post('mata-kuliah/(:num)/seb/kunci', '\Lokandita\Auth\Controllers\SebKelola::kunci/$1');
        });
    }
}
