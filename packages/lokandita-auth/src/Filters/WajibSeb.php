<?php

declare(strict_types=1);

namespace Lokandita\Auth\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Lokandita\Auth\Aplikasi;
use Lokandita\Auth\Current;
use Lokandita\Auth\Log;
use Lokandita\Auth\Seb\Seb;
use Lokandita\Auth\Sisipan;

/**
 * Safe Exam Browser wajib bagi MAHASISWA (SPEC.md §13.1, KONTRAK.md §8). Dipasang sesudah
 * filter `masuk`, jadi keputusannya menurut peran pengguna yang sudah masuk — bukan menurut
 * alamat. Staf lewat begitu saja. Satu filter untuk semua aplikasi (alias `seb`); yang berbeda
 * per aplikasi datang dari Lokandita\Auth\Seb\Sumber.
 *
 * SEB_ENFORCEMENT=disabled: pemeriksaan dilewati dan setiap halaman mahasiswa diberi pita merah.
 */
final class WajibSeb implements FilterInterface
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

        $hasil = Seb::periksa($request, Seb::kandidat($identitas->userId));
        if ($hasil['lolos']) {
            Seb::perpanjangSewa($identitas->userId);
            Sisipan::skrip('/js/seb.js');

            return null;
        }

        // Tadinya di dalam SEB yang sah (sewa masih hidup) = sesi diakhiri; selain itu = ditolak.
        $diakhiri = Seb::akhiriSewa($identitas->userId);
        $jalur    = '/' . ltrim($request->getUri()->getPath(), '/');
        self::catat($identitas->userId, $diakhiri ? 'seb_terminated' : 'seb_rejected', $hasil['alasan'], $jalur, $request);

        $response = service('response')->setStatusCode(403)->setHeader('Cache-Control', 'no-store');
        if (str_starts_with($jalur, '/api/')) {
            return $response->setJSON(['error' => 'seb_wajib', 'message' => lang('Seb.wajib.judul', [Aplikasi::nama()])]);
        }

        return $response->setBody(view('Lokandita\Auth\Views\seb_wajib', ['judul' => lang('Seb.wajib.judul', [Aplikasi::nama()]), 'alasan' => $hasil['alasan']]));
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return $response;
    }

    /**
     * Penolakan dan pengakhiran adalah peristiwa integritas. Pengakhiran selalu dicatat;
     * penolakan paling banyak satu per menit per pengguna, supaya peramban biasa yang terus
     * mencoba tidak membanjiri tabel.
     */
    private static function catat(string $userId, string $jenis, string $alasan, string $jalur, RequestInterface $request): void
    {
        Log::event('seb.' . ($jenis === 'seb_terminated' ? 'diakhiri' : 'ditolak'), ['user' => $userId, 'reason' => $alasan, 'path' => $jalur]);
        if ($jenis === 'seb_rejected') {
            try {
                if (Seb::sumber()->redis()->set(Aplikasi::kode() . ':laju:seb:' . $userId, '1', ['nx', 'ex' => 60]) === false) {
                    return;
                }
            } catch (\RedisException $e) {
                Log::event('ratelimit.unavailable', ['reason' => $e->getMessage()]);
            }
        }
        Seb::sumber()->catatPeristiwa($userId, $jenis, ['alasan' => $alasan, 'jalur' => $jalur], $request);
    }
}
