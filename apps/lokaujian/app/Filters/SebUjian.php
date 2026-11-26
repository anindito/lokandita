<?php

declare(strict_types=1);

namespace App\Filters;

use App\Libraries\Pengerjaan;
use App\Libraries\SebUjian as Konfigurasi;
use App\Policy\Akses;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Lokandita\Auth\Aplikasi;
use Lokandita\Auth\Current;
use Lokandita\Auth\Log;
use Lokandita\Auth\Seb\Seb;
use Lokandita\Auth\Sisipan;

/**
 * Filter `seb` LokaUjian (SPEC-LOKAUJIAN.md §9.1; KONTRAK.md §8, §23). Selalu SESUDAH `masuk`.
 *
 * Berlaku bagi MAHASISWA saja, menurut peran pengguna yang sudah masuk — bukan menurut alamat.
 * Staf lewat begitu saja. User-Agent tidak pernah dilihat: kedua header SEB diperiksa secara
 * kriptografis oleh paket bersama (Seb::periksa), dengan rumus yang sama dengan LokaKode.
 *
 * Yang khas LokaUjian adalah konfigurasi MANA yang dihitung: konfigurasi SEB di sini per ujian.
 *   - alamat yang memuat sesi (/ujian-saya/{s}/…, /api/sesi/{s}/…): HANYA konfigurasi ujian sesi
 *     itu. Di dalam SEB yang dibuka dengan berkas ujian lain, permintaan ini ditolak.
 *   - alamat lain di belakang filter ini (denyut, gambar): konfigurasi salah satu ujian mahasiswa itu.
 *
 * SEB_ENFORCEMENT=disabled: tidak ada yang diperiksa dan halaman mahasiswa diberi pita merah.
 */
final class SebUjian implements FilterInterface
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
        $jalur   = '/' . ltrim($request->getUri()->getPath(), '/');
        $kerja   = new Pengerjaan(new Akses($identitas));
        $sesiId  = preg_match('#^/(?:ujian-saya|api/sesi)/(\d{1,18})(?:/|$)#', $jalur, $m) === 1 ? (int) $m[1] : null;
        $sesi    = $sesiId === null ? null : $kerja->sesi($sesiId);
        $ujian   = $sesi !== null ? [(int) $sesi['exam_id']] : array_map(static fn (array $s): int => (int) $s['exam_id'], $kerja->daftar());
        $hasil   = Seb::periksa($request, Konfigurasi::kandidat($ujian));
        if ($hasil['lolos']) {
            Seb::perpanjangSewa($identitas->userId);
            Sisipan::skrip('/js/seb.js');

            return null;
        }

        // Tadinya di dalam SEB yang sah (sewa masih hidup) = diakhiri; selain itu = ditolak.
        $jenis = Seb::akhiriSewa($identitas->userId) ? 'seb_terminated' : 'seb_rejected';
        Log::event('seb.' . ($jenis === 'seb_terminated' ? 'diakhiri' : 'ditolak'), ['user' => $identitas->userId, 'reason' => $hasil['alasan'], 'path' => $jalur]);
        if ($jenis === 'seb_terminated' || $this->bolehCatat($identitas->userId)) {
            Konfigurasi::catatPeristiwa(
                $identitas->userId,
                $jenis,
                ['alasan' => $hasil['alasan'], 'jalur' => $jalur],
                $sesi === null ? null : (int) $sesi['sesi_id'],
                $sesi === null || $sesi['attempt_id'] === null ? null : (int) $sesi['attempt_id'],
                $request,
            );
        }

        $response = service('response')->setStatusCode(403)->setHeader('Cache-Control', 'no-store');
        if (str_starts_with($jalur, '/api/')) {
            return $response->setJSON(['error' => 'seb_wajib', 'message' => lang('Seb.wajib.judul', [Aplikasi::nama()])]);
        }

        return $response->setBody(view('seb_wajib', ['judul' => lang('SebUjian.wajib.judul'), 'alasan' => $hasil['alasan']]));
    }

    /**
     * Penolakan dicatat paling banyak sekali per menit per pengguna, supaya peramban biasa yang
     * terus mencoba tidak membanjiri tabel. Tanpa Redis penolakan tetap dicatat.
     */
    private function bolehCatat(string $userId): bool
    {
        try {
            return Seb::sumber()->redis()->set(Aplikasi::kode() . ':laju:seb:' . $userId, '1', ['nx', 'ex' => 60]) !== false;
        } catch (\Throwable $e) {
            Log::event('ratelimit.unavailable', ['reason' => $e->getMessage()]);

            return true;
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return $response;
    }
}
