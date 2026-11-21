<?php

declare(strict_types=1);

namespace App\Filters;

use App\Libraries\Antrean;
use App\Libraries\Seb\Seb;
use App\Libraries\Waktu;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Lokandita\Auth\Current;
use Lokandita\Auth\Log;
use Lokandita\Auth\Sisipan;

/**
 * Safe Exam Browser wajib bagi MAHASISWA (SPEC.md §13.1, KONTRAK.md §8). Dipasang sesudah
 * filter `masuk`, jadi keputusannya menurut peran pengguna yang sudah masuk — bukan menurut
 * alamat. Staf lewat begitu saja.
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
            return $response->setJSON(['error' => 'seb_wajib', 'message' => lang('Seb.wajib.judul')]);
        }

        return $response->setBody(view('seb_wajib', ['judul' => lang('Seb.wajib.judul'), 'alasan' => $hasil['alasan']]));
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
                if (Antrean::redis()->set('lokakode:laju:seb:' . $userId, '1', ['nx', 'ex' => 60]) === false) {
                    return;
                }
            } catch (\RedisException $e) {
                Log::event('ratelimit.unavailable', ['reason' => $e->getMessage()]);
            }
        }
        db_connect()->table('integrity_events')->insert([
            'user_id' => $userId, 'kind' => $jenis, 'meeting_problem_id' => null,
            'detail' => json_encode(['alasan' => $alasan, 'jalur' => $jalur]),
            'ip' => method_exists($request, 'getIPAddress') ? $request->getIPAddress() : null,
            'user_agent' => method_exists($request, 'getUserAgent') ? substr((string) $request->getUserAgent(), 0, 255) : null,
            'created_at' => Waktu::kini(),
        ]);
    }
}
