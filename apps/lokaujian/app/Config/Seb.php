<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Safe Exam Browser (KONTRAK.md §8, §23). Dari paket bersama LokaUjian memakai pemeriksaan
 * header, pembuat setelan dan berkas .seb, enkripsi, dan sewa 30 detik. Konfigurasinya sendiri
 * per UJIAN (App\Libraries\SebUjian), jadi bagian paket yang berbicara tentang mata kuliah —
 * panel, daftar dan unduhan publik — tidak dipasang di rute LokaUjian.
 */
class Seb extends BaseConfig
{
    /** @var class-string<\Lokandita\Auth\Seb\Sumber> */
    public string $sumber = \App\Libraries\SumberSeb::class;
}
