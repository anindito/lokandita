<?php

declare(strict_types=1);

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Safe Exam Browser (KONTRAK.md §8). Pemeriksaannya ada di packages/lokandita-auth
 * (Lokandita\Auth\Seb); yang khas LokaKode — dari mana mata kuliah dan pendaftaran dibaca,
 * ke mana peristiwa dan audit ditulis — ada di kelas yang disebut di sini.
 */
class Seb extends BaseConfig
{
    /** @var class-string<\Lokandita\Auth\Seb\Sumber> */
    public string $sumber = \App\Libraries\SumberSeb::class;
}
