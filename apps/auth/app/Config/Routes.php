<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

// KONTRAK.md §11: dipakai infra/deploy.sh untuk memastikan commit yang berjalan.
$routes->get('healthz', '\Lokandita\Auth\Controllers\Healthz::index');

// Tanpa sesi.
$routes->get('login', 'Login::form');
$routes->post('login', 'Login::submit');
$routes->get('bahasa/(:segment)', 'Language::set/$1');
$routes->get('keluar-selesai', 'Logout::selesai');

// Dengan sesi. Filter `sesi` juga memaksa ganti kata sandi bila diwajibkan.
$routes->group('', ['filter' => 'sesi'], static function (RouteCollection $routes): void {
    $routes->get('/', 'Home::index');
    $routes->get('ganti-password', 'ChangePassword::form');
    $routes->post('ganti-password', 'ChangePassword::submit');
    $routes->post('keluar', 'Logout::keluar');
});
