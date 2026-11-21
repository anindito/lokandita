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
}
