<?php

declare(strict_types=1);

namespace App\Controllers;

use CodeIgniter\HTTP\ResponseInterface;
use Lokandita\Auth\Locale;

/**
 * Pilihan bahasa sebelum masuk, disimpan di cookie `__Host-bahasa`.
 * Setelah masuk, bahasa adalah milik akun (menu akun).
 */
final class Language extends BaseController
{
    public function set(string $locale): ResponseInterface
    {
        $jawab = redirect()->to('/login');
        if (in_array($locale, Locale::DAFTAR, true)) {
            $jawab->setCookie(Locale::COOKIE, $locale, 31536000, '', '/', '', true, true, 'Lax');
        }

        return $jawab;
    }
}
