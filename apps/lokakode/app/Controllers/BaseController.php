<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Libraries\Akademik;
use App\Policy\Akses;
use CodeIgniter\Controller;
use CodeIgniter\HTTP\ResponseInterface;
use Lokandita\Auth\Current;
use Lokandita\Auth\Log;

/**
 * Dasar semua halaman LokaKode. Setiap rute di sini berada di belakang filter
 * `masuk` (KONTRAK.md §4), jadi identitas selalu ada.
 */
abstract class BaseController extends Controller
{
    protected $helpers = ['form'];

    private ?Akses $akses = null;

    protected function akses(): Akses
    {
        if ($this->akses === null) {
            $identitas = Current::identity();
            if ($identitas === null) {
                throw new \LogicException('Rute ini tidak dipasangi filter masuk.');
            }
            $this->akses = new Akses($identitas);
        }

        return $this->akses;
    }

    protected function akademik(): Akademik
    {
        return new Akademik($this->akses());
    }

    /**
     * @param array<string, mixed> $data
     */
    protected function halaman(string $view, array $data = [], int $status = 200): ResponseInterface
    {
        $data['akses'] = $this->akses();
        $data['pesan'] ??= [];

        return $this->response->setStatusCode($status)->setBody(view($view, $data));
    }

    /**
     * Jawaban untuk baris yang tidak ada ATAU di luar hak pengguna. Keduanya
     * sengaja tidak dibedakan, supaya nomor di URL tidak bisa dipakai menebak
     * kelas atau mata kuliah mana yang ada.
     */
    protected function tidakDitemukan(string $apa): ResponseInterface
    {
        Log::event('access.denied', [
            'user'   => $this->akses()->identitas()->userId,
            'reason' => 'outside_scope_or_missing',
            'what'   => $apa,
            'path'   => $this->request->getUri()->getPath(),
        ]);

        return $this->response->setStatusCode(404)->setBody(view('Lokandita\Auth\Views\pesan', [
            'judul' => lang('Lokandita.tidakDitemukan'),
            'teks'  => lang('Kode.tidakDitemukan'),
        ]));
    }

    /**
     * Peran ini tidak pernah boleh melakukan tindakan ini.
     */
    protected function terlarang(): ResponseInterface
    {
        Log::event('access.denied', [
            'user'   => $this->akses()->identitas()->userId,
            'reason' => 'role',
            'path'   => $this->request->getUri()->getPath(),
        ]);

        return $this->response->setStatusCode(403)->setBody(view('Lokandita\Auth\Views\pesan', [
            'judul' => lang('Lokandita.tidakBerhak'),
            'teks'  => lang('Lokandita.tidakBerhak'),
        ]));
    }
}
