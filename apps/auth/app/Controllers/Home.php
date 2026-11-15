<?php

declare(strict_types=1);

namespace App\Controllers;

use CodeIgniter\HTTP\ResponseInterface;
use Lokandita\Auth\Current;

/**
 * Halaman akun di LokaAuth.
 */
final class Home extends BaseController
{
    private const PESAN = ['sandi-diganti' => 'Auth.sandiDiganti'];

    public function index(): ResponseInterface
    {
        $kunci = (string) $this->request->getGet('pesan');
        $pesan = isset(self::PESAN[$kunci]) ? [['jenis' => 'sukses', 'teks' => lang(self::PESAN[$kunci])]] : [];

        return $this->halaman('beranda', [
            'judul'     => lang('Auth.akunSaya'),
            'identitas' => Current::identity(),
            'pesan'     => $pesan,
        ]);
    }
}
