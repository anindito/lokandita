<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Libraries\Aplikasi;
use App\Libraries\Teks;
use CodeIgniter\HTTP\ResponseInterface;
use Lokandita\Auth\Current;
use Lokandita\Auth\SessionReader;

/**
 * Portal setelah masuk (SPEC.md §15): nama, peran, dan aplikasi yang diizinkan
 * `app_access`. Hak akses dibaca dari LokaAuth pada setiap permintaan; Portal
 * tidak menyimpan apa pun tentang pengguna.
 */
final class Beranda extends BaseController
{
    public function index(): ResponseInterface
    {
        $identitas = Current::identity();
        $kartu     = [];
        foreach (Aplikasi::daftar() as $kode => $a) {
            if (SessionReader::hasAccess($identitas->userId, $kode)) {
                $kartu[$kode] = $a;
            }
        }

        return $this->response->setBody(view('beranda', [
            'judul'     => lang('Lokandita.beranda'),
            'identitas' => $identitas,
            'kartu'     => $kartu,
            'ket'       => Teks::ambil('aplikasi', $this->request->getLocale()),
            // Penyuntingan teks halaman publik (KONTRAK.md §15): superadmin saja; rutenya memeriksa lagi.
            'sunting'   => $identitas->globalRole === 'superadmin',
            // Pengelolaan pengguna ada di LokaAuth dan hanya untuk superadmin; LokaAuth memeriksanya lagi.
            'admin'     => $identitas->globalRole === 'superadmin' ? rtrim((string) getenv('AUTH_PUBLIC_URL'), '/') . '/admin/pengguna' : null,
            'kontak'    => (string) getenv('CONTACT_EMAIL'),
        ]));
    }

    /**
     * GET /data-saya — hanya tautan: setiap aplikasi mengekspor data yang disimpannya sendiri (KONTRAK.md §14).
     */
    public function dataSaya(): ResponseInterface
    {
        $identitas = Current::identity();
        $tautan    = ['LokaAuth' => rtrim((string) getenv('AUTH_PUBLIC_URL'), '/') . '/data-saya'];
        if (SessionReader::hasAccess($identitas->userId, 'lokakode')) {
            $tautan['LokaKode'] = Aplikasi::daftar()['lokakode']['url'] . '/data-saya';
        }

        return $this->response->setBody(view('data_saya', [
            'judul'  => lang('Portal.data.judul'),
            'tautan' => $tautan,
        ]));
    }
}
