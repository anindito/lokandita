<?php

declare(strict_types=1);

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Daftar aplikasi keluarga Lokandita yang ditampilkan Portal (docs/branding/LOKANDITA-BRAND.md),
 * dalam urutan tampil. MENAMBAH APLIKASI = satu butir di sini, ditambah satu baris keterangan
 * `aplikasi.<kode>` di app/Language/{id,en,ar}/Portal.php. Tampilan tidak perlu diubah.
 *
 * Kunci   kode aplikasi, huruf kecil; untuk aplikasi yang memakai hak akses sama dengan nilai
 *         `app_access.app` (KONTRAK.md §3).
 * nama    nama merek, ditulis sama di semua bahasa.
 * status  'aktif'  = sudah berjalan: kartunya tautan ke alamat aplikasi itu;
 *         'segera' = belum ada: kartunya bukan tautan dan bertanda "Segera".
 * alamat  NAMA variabel lingkungan yang memuat alamat aplikasi (wajib untuk 'aktif'), atau null.
 * ikon    nama berkas ikon di public/merek/aplikasi/ tanpa akhiran (dibuat infra/buat-merek.sh
 *         dari docs/branding/baru/), atau null. Tanpa ikon, atau bila berkasnya tidak ada,
 *         kartu mendapat lingkaran netral — tidak ada logo yang dikarang.
 */
class Aplikasi extends BaseConfig
{
    /** @var array<string, array{nama: string, status: string, alamat: ?string, ikon: ?string}> */
    public array $daftar = [
        'lokakode'  => ['nama' => 'LokaKode',  'status' => 'aktif',  'alamat' => 'LOKAKODE_URL', 'ikon' => 'lokakode'],
        'lokaujian' => ['nama' => 'LokaUjian', 'status' => 'segera', 'alamat' => null,           'ikon' => 'lokaujian'],
        'lokakelas' => ['nama' => 'LokaKelas', 'status' => 'segera', 'alamat' => null,           'ikon' => 'lokakelas'],
        'lokasiber' => ['nama' => 'LokaSiber', 'status' => 'segera', 'alamat' => null,           'ikon' => 'lokasiber'],
        'lokaretas' => ['nama' => 'LokaRetas', 'status' => 'segera', 'alamat' => null,           'ikon' => 'lokaretas'],
        'lokadata'  => ['nama' => 'LokaData',  'status' => 'segera', 'alamat' => null,           'ikon' => 'lokadata'],
    ];
}
