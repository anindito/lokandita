<?php

declare(strict_types=1);

namespace Lokandita\Auth;

use CodeIgniter\HTTP\ResponseInterface;

/**
 * Bagian yang sama dari ekspor data sendiri di setiap aplikasi (KONTRAK.md §14):
 * bentuk berkas, batas baris per daftar, dan jeda antar-unduhan.
 */
final class Ekspor
{
    /** Paling banyak sekian baris per daftar; daftar yang dipotong disebut dalam `terpotong`. */
    public const BATAS = 5000;

    /** Jeda antar-unduhan per pengguna per aplikasi, dalam detik. */
    public const JEDA = 60;

    /**
     * Memotong daftar ke BATAS baris. Pemanggil mengambil BATAS + 1 baris supaya
     * kelebihan itu terlihat.
     *
     * @param list<array<string, mixed>> $baris
     * @param list<string>               $terpotong diisi nama daftar yang dipotong
     *
     * @return list<array<string, mixed>>
     */
    public static function potong(string $nama, array $baris, array &$terpotong): array
    {
        if (count($baris) > self::BATAS) {
            $terpotong[] = $nama;

            return array_slice($baris, 0, self::BATAS);
        }

        return $baris;
    }

    /**
     * Waktu DATETIME MySQL (UTC) menjadi ISO 8601, mis. 2026-11-21T03:00:00Z.
     */
    public static function waktu(?string $utc): ?string
    {
        if ($utc === null || $utc === '') {
            return null;
        }

        return gmdate('Y-m-d\TH:i:s\Z', (int) strtotime($utc . ' UTC'));
    }

    /**
     * Boleh mengunduh sekarang? Paling banyak sekali per JEDA detik. Bila Redis tidak
     * bisa dihubungi, unduhan diizinkan dan kegagalannya dicatat: hak atas data sendiri
     * tidak boleh hilang karena cache mati.
     *
     * @param callable(): \Redis $redis koneksi Redis aplikasi itu; dipanggil di sini supaya
     *                                  kegagalan menyambung pun tertangkap
     */
    public static function bolehSekarang(callable $redis, string $userId): bool
    {
        try {
            return (bool) $redis()->set('ekspor:' . $userId, '1', ['nx', 'ex' => self::JEDA]);
        } catch (\Throwable $e) {
            Log::event('export.ratelimit_unavailable', ['user' => $userId, 'error' => $e::class]);

            return true;
        }
    }

    /**
     * Jawaban berkas JSON.
     *
     * @param array<string, mixed> $isi     kunci khusus aplikasi
     * @param list<string>         $terpotong
     */
    public static function berkas(ResponseInterface $response, string $userId, array $isi, array $terpotong): ResponseInterface
    {
        $app  = Aplikasi::kode();
        $data = ['aplikasi' => $app, 'dibuat' => gmdate('Y-m-d\TH:i:s\Z'), 'pengguna' => $userId, 'terpotong' => $terpotong] + $isi;

        return $response
            ->setHeader('Cache-Control', 'no-store')
            ->setHeader('Content-Disposition', 'attachment; filename="lokandita-' . $app . '-data-saya.json"')
            ->setContentType('application/json')
            ->setBody(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE | JSON_THROW_ON_ERROR));
    }

    /**
     * Jawaban 429: terlalu cepat mengunduh lagi.
     */
    public static function terlaluCepat(ResponseInterface $response): ResponseInterface
    {
        return $response->setStatusCode(429)->setHeader('Cache-Control', 'no-store')->setHeader('Retry-After', (string) self::JEDA)
            ->setBody(view('Lokandita\Auth\Views\pesan', [
                'judul' => lang('Lokandita.dataSaya.judul'),
                'teks'  => lang('Lokandita.dataSaya.terlaluCepat'),
            ]));
    }
}
