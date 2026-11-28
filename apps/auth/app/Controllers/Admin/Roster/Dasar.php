<?php

declare(strict_types=1);

namespace App\Controllers\Admin\Roster;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use Lokandita\Auth\Current;

/**
 * Dasar halaman roster di Akun Lokandita (SPEC.md §5, KONTRAK.md §18). Semua rutenya berada
 * di belakang filter `sesi:superadmin` (Routes.php): peran diperiksa di server sebelum
 * pengendali mana pun di sini berjalan.
 */
abstract class Dasar extends BaseController
{
    protected function pelaku(): string
    {
        return Current::identity()->userId;
    }

    /**
     * Pesan sesudah pengalihan: ?pesan=<kunci>. Hanya kunci yang terdaftar yang ditampilkan;
     * ?n=<angka> mengisi {0} pada pesan yang menyebut jumlah.
     *
     * @param array<string, array{0: string, 1: string}> $peta kunci => [jenis, kunci bahasa]
     *
     * @return list<array{jenis: string, teks: string}>
     */
    protected function pesanDariQuery(array $peta): array
    {
        $k = (string) $this->request->getGet('pesan');
        $n = max(0, (int) $this->request->getGet('n'));

        return isset($peta[$k]) ? [['jenis' => $peta[$k][0], 'teks' => lang($peta[$k][1], [$n])]] : [];
    }

    /**
     * @return list<array{jenis: string, teks: string}>
     */
    protected function periksaLagi(): array
    {
        return $this->galat(lang('Roster.pesan.periksa'));
    }

    protected function tidakAda(): ResponseInterface
    {
        return $this->halaman('galat', ['judul' => lang('Lokandita.tidakDitemukan'), 'teks' => lang('Roster.tidakDitemukan')], 404);
    }
}
