<?php

declare(strict_types=1);

namespace App\Libraries;

/**
 * Aplikasi Lokandita yang ditampilkan Portal (SPEC.md §1, §15), dibaca dari Config\Aplikasi —
 * daftar itu data, bukan bagian tampilan. Aplikasi tanpa alamat belum dibangun: ditampilkan
 * sebagai "segera" dan tidak bisa diklik.
 */
final class Aplikasi
{
    /**
     * @return array<string, array{nama: string, url: ?string, ikon: ?string}> dikunci dengan kode
     *         aplikasi, dalam urutan tampil. `url` null = "segera". `ikon` null = lingkaran netral.
     */
    public static function daftar(): array
    {
        $daftar = [];
        foreach (config('Aplikasi')->daftar as $kode => $a) {
            $url = null;
            if ($a['status'] === 'aktif' && $a['alamat'] !== null) {
                $url = rtrim((string) getenv($a['alamat']), '/');
                // Alamat kosong tidak boleh menjadi tautan ke Portal sendiri.
                $url = $url === '' ? null : $url;
            }
            $ikon = $a['ikon'];
            if ($ikon !== null && (preg_match('/^[a-z0-9-]+$/', $ikon) !== 1 || ! is_file(FCPATH . "merek/aplikasi/{$ikon}-terang.png"))) {
                $ikon = null;
            }
            $daftar[$kode] = ['nama' => $a['nama'], 'url' => $url, 'ikon' => $ikon];
        }

        return $daftar;
    }
}
