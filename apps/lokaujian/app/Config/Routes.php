<?php

use CodeIgniter\Router\RouteCollection;
use Lokandita\Auth\Routes as RuteBersama;

/** @var RouteCollection $routes */

// LokaUjian tahap U0 (SPEC-LOKAUJIAN.md §15): kerangka kosong di belakang halaman pemeliharaan.
// Belum ada halaman, belum ada login; itu tahap U1.

// KONTRAK.md §11: dipakai infra/deploy.sh untuk memastikan commit yang berjalan.
$routes->get('healthz', '\Lokandita\Auth\Controllers\Healthz::index');

// Tanpa sesi: pilihan bahasa (KONTRAK.md §4 "Public routes").
RuteBersama::publik($routes);

// /robots.txt adalah berkas statis di public/: seluruh LokaUjian tidak untuk diindeks.
