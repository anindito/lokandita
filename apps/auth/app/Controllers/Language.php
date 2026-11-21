<?php

declare(strict_types=1);

namespace App\Controllers;

use CodeIgniter\HTTP\ResponseInterface;
use Lokandita\Auth\Controllers\Akun as AkunKlien;
use Lokandita\Auth\Controllers\Bahasa;

/**
 * Pilihan bahasa sebelum masuk, disimpan di cookie `__Host-bahasa`.
 * Setelah masuk, bahasa adalah milik akun (menu akun).
 */
final class Language extends BaseController
{
    public function set(string $locale): ResponseInterface
    {
        // Sama dengan aplikasi klien (KONTRAK.md §4); tanpa ?kembali tujuannya halaman login.
        $kembali = (string) $this->request->getGet('kembali');

        return Bahasa::jawab($locale, $kembali === '' ? '/login' : AkunKlien::kembali($kembali));
    }
}
