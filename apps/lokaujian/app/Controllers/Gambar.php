<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Libraries\Media;
use CodeIgniter\HTTP\ResponseInterface;
use Lokandita\Auth\Log;

/**
 * Satu-satunya jalan keluar gambar soal (SPEC-LOKAUJIAN.md §5.3): GET /gambar/<kunci>, dijawab
 * aplikasi SESUDAH hak akses diperiksa. Gambar soal bukan berkas statis; di bawah /media/ nama
 * berkasnya selalu dijawab 403 oleh Apache (KONTRAK.md §20).
 *
 * Tahap U2: hanya yang mengelola mata kuliah gambar itu (superadmin, pengampu). Mahasiswa
 * mendapat gambar mulai tahap U3, selama pengerjaannya berlangsung. Kunci yang tidak ada dan
 * kunci milik mata kuliah lain dijawab sama: 404.
 */
final class Gambar extends BaseController
{
    public function tampil(string $kunci): ResponseInterface
    {
        $gambar = (new Media($this->akses()))->dariKunci($kunci);
        if ($gambar === null) {
            return $this->tidakDitemukan('image');
        }
        $isi = @file_get_contents(Media::jalur($gambar));
        if ($isi === false) {
            // Baris ada tetapi berkasnya tidak: volume tidak terpasang atau belum dipulihkan.
            Log::event('gambar.berkas_hilang', ['kunci' => $kunci]);

            return $this->tidakDitemukan('image_file');
        }

        return $this->response
            ->setStatusCode(200)
            ->setHeader('Content-Type', $gambar['jenis'] === 'png' ? 'image/png' : 'image/jpeg')
            ->setHeader('Content-Length', (string) strlen($isi))
            ->setHeader('Cache-Control', 'private, no-store')
            ->setHeader('X-Content-Type-Options', 'nosniff')
            ->setBody($isi);
    }
}
