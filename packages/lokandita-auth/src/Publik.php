<?php

declare(strict_types=1);

namespace Lokandita\Auth;

use CodeIgniter\HTTP\ResponseInterface;

/**
 * Halaman publik (KONTRAK.md §4 "Public routes"): tanpa sesi, tanpa basis data,
 * isinya sama untuk semua orang.
 */
final class Publik
{
    /**
     * Halaman depan: pengunjung yang membawa cookie sesi dibawa ke /beranda.
     * Hanya ADA-tidaknya cookie yang dilihat; nilainya tidak dibaca. Hidup atau
     * tidaknya sesi diputuskan filter `masuk` di /beranda.
     */
    public static function keBerandaBilaMembawaSesi(): ?ResponseInterface
    {
        if ((string) ($_COOKIE[SessionReader::COOKIE] ?? '') === '') {
            return null;
        }

        return redirect()->to('/beranda');
    }

    /**
     * Data tata letak untuk halaman publik: boleh diindeks, tombol Masuk, pilihan bahasa.
     *
     * @param list<array{href: string, teks: string}> $nav
     *
     * @return array<string, mixed>
     */
    public static function data(string $judul, string $deskripsi, array $nav = []): array
    {
        return ['judul' => $judul, 'deskripsi' => $deskripsi, 'publik' => true, 'navPublik' => $nav];
    }
}
