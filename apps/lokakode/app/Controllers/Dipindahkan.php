<?php

declare(strict_types=1);

namespace App\Controllers;

use CodeIgniter\HTTP\ResponseInterface;

/**
 * Alamat lama pengelolaan roster (KONTRAK.md §18). Sejak tahap A2 hanya Akun Lokandita yang
 * menulis roster; LokaKode hanya membacanya. Pengendali ini TIDAK membaca atau menulis apa pun:
 * ia hanya mengalihkan superadmin (satu-satunya peran yang lolos filter rutenya) ke halaman
 * yang sama di Akun Lokandita.
 *
 *   GET  /kelas/7/ubah     -> {AUTH_PUBLIC_URL}/admin/kelas/7/ubah
 *   POST /kelas/7/pindah   -> {AUTH_PUBLIC_URL}/admin/kelas/7      (isian dibuang)
 *   POST /tahun-ajaran/baru/jalankan -> {AUTH_PUBLIC_URL}/admin/tahun-ajaran
 */
final class Dipindahkan extends BaseController
{
    public function alih(): ResponseInterface
    {
        $segmen = $this->request->getUri()->getSegments();
        if ($this->request->getMethod() !== 'GET') {
            // Halaman induk: "kelas/7/pindah" -> "kelas/7"; "tahun-ajaran/baru/jalankan" -> "tahun-ajaran".
            $segmen = array_slice($segmen, 0, ctype_digit((string) ($segmen[1] ?? '')) ? 2 : 1);
        }

        return redirect()->to(self::alamat(implode('/', $segmen)), 303);
    }

    /**
     * Alamat sebuah halaman roster di Akun Lokandita, mis. alamat('kelas/7').
     */
    public static function alamat(string $jalur): string
    {
        return rtrim((string) getenv('AUTH_PUBLIC_URL'), '/') . '/admin/' . ltrim($jalur, '/');
    }
}
