<?php

declare(strict_types=1);

namespace Lokandita\Auth;

use CodeIgniter\HTTP\RequestInterface;

/**
 * Bahasa antarmuka: milik pengguna bila sudah masuk, kalau belum dari cookie
 * `__Host-bahasa`, dan bawaannya Indonesia.
 */
final class Locale
{
    public const DAFTAR = ['id', 'en', 'ar'];

    public const COOKIE = '__Host-bahasa';

    public static function apply(RequestInterface $request, ?string $userLocale = null): string
    {
        $locale = $userLocale ?? (string) ($_COOKIE[self::COOKIE] ?? '');
        if (! in_array($locale, self::DAFTAR, true)) {
            $locale = 'id';
        }
        $request->setLocale($locale);
        // Angka selalu Latin, juga dalam bahasa Arab (SPEC.md §11).
        service('language')->setLocale($locale === 'ar' ? 'ar-u-nu-latn' : $locale);

        return $locale;
    }

    public static function arah(string $locale): string
    {
        return $locale === 'ar' ? 'rtl' : 'ltr';
    }
}
