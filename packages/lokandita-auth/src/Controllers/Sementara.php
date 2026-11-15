<?php

declare(strict_types=1);

namespace Lokandita\Auth\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\ResponseInterface;
use Lokandita\Auth\Current;

/**
 * Halaman sementara di LokaKode dan Portal (tahap 5): hanya membuktikan bahwa
 * sesi, menu akun dan Keluar bekerja antar-aplikasi. Diganti halaman sungguhan
 * di tahap 6 (LokaKode) dan tahap 11 (Portal).
 */
final class Sementara extends Controller
{
    public function index(): ResponseInterface
    {
        return $this->response->setBody(view('Lokandita\Auth\Views\sementara', [
            'judul'     => lang('Lokandita.beranda'),
            'identitas' => Current::identity(),
        ]));
    }

    public function keBeranda(): ResponseInterface
    {
        return redirect()->to('/beranda');
    }
}
