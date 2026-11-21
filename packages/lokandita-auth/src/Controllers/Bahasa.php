<?php

declare(strict_types=1);

namespace Lokandita\Auth\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\ResponseInterface;
use Lokandita\Auth\Locale;

/**
 * GET /bahasa/{id|en|ar}?kembali=<jalur lokal> — pilihan bahasa sebelum masuk,
 * disimpan di cookie `__Host-bahasa` (KONTRAK.md §2, §4). Setelah masuk, bahasa
 * adalah milik akun (menu akun) dan cookie ini tidak dibaca.
 */
final class Bahasa extends Controller
{
    public function atur(string $locale): ResponseInterface
    {
        return self::jawab($locale, Akun::kembali((string) $this->request->getGet('kembali')));
    }

    /**
     * Kode bahasa yang tidak dikenal tidak mengubah apa pun; pengalihannya tetap terjadi.
     */
    public static function jawab(string $locale, string $tujuan): ResponseInterface
    {
        $jawab = redirect()->to($tujuan);
        if (in_array($locale, Locale::DAFTAR, true)) {
            $jawab->setCookie(Locale::COOKIE, $locale, 31536000, '', '/', '', true, true, 'Lax');
        }

        return $jawab;
    }
}
