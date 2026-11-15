<?php

use CodeIgniter\Router\RouteCollection;
use Lokandita\Auth\Routes as RuteBersama;

/** @var RouteCollection $routes */

// KONTRAK.md §11: dipakai infra/deploy.sh untuk memastikan commit yang berjalan.
$routes->get('healthz', '\Lokandita\Auth\Controllers\Healthz::index');

// Menu akun: keluar, perpanjang sesi, preferensi (KONTRAK.md §4).
RuteBersama::akun($routes);

// Tahap 5: halaman sementara untuk membuktikan sesi antar-aplikasi.
RuteBersama::sementara($routes);
