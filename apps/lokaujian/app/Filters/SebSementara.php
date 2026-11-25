<?php

declare(strict_types=1);

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Lokandita\Auth\Current;
use Lokandita\Auth\Log;
use Lokandita\Auth\Seb\Seb;
use Lokandita\Auth\Sisipan;

/**
 * Filter `seb` LokaUjian UNTUK TAHAP U3 SAJA (SPEC-LOKAUJIAN.md §15: "Runs with
 * SEB_ENFORCEMENT=disabled until U4").
 *
 * LokaUjian belum punya konfigurasi SEB (tabel dan panelnya tahap U4), jadi belum ada yang bisa
 * diperiksa. Karena itu:
 *   SEB_ENFORCEMENT=disabled  halaman mahasiswa dibuka dengan pita merah "MODE UJI";
 *   SEB_ENFORCEMENT=enabled   mahasiswa DITOLAK (403). Tidak ada jalan diam-diam di mana
 *                             penegakan "aktif" tetapi tidak ada yang diperiksa.
 * Di tahap U4 alias `seb` diganti dengan Lokandita\Auth\Filters\WajibSeb dari paket bersama.
 * Staf lewat begitu saja, seperti di sana.
 */
final class SebSementara implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $identitas = Current::identity();
        if ($identitas === null || $identitas->globalRole !== 'mahasiswa') {
            return null;
        }
        if (! Seb::diberlakukan()) {
            Sisipan::pita(lang('Seb.modeUji'));

            return null;
        }
        $jalur = '/' . ltrim($request->getUri()->getPath(), '/');
        Log::event('seb.belum_tersedia', ['user' => $identitas->userId, 'path' => $jalur]);
        $response = service('response')->setStatusCode(403)->setHeader('Cache-Control', 'no-store');
        if (str_starts_with($jalur, '/api/')) {
            return $response->setJSON(['error' => 'seb_wajib']);
        }

        return $response->setBody(view('Lokandita\Auth\Views\pesan', ['judul' => lang('Kerja.seb.judul'), 'teks' => lang('Kerja.seb.belum')]));
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return $response;
    }
}
