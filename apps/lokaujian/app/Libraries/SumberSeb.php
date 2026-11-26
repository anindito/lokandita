<?php

declare(strict_types=1);

namespace App\Libraries;

use CodeIgniter\HTTP\RequestInterface;
use Lokandita\Auth\Identity;
use Lokandita\Auth\Seb\Sumber;

/**
 * Yang disediakan LokaUjian kepada paket SEB bersama (KONTRAK.md §8).
 *
 * Paket itu memakainya untuk dua hal di LokaUjian: koneksi Redis (sewa 30 detik) dan — bila
 * suatu hari controller bersama dipasang — pencatatan. Empat fungsi tentang MATA KULIAH sengaja
 * mengembalikan "tidak ada": konfigurasi SEB LokaUjian adalah per ujian (App\Libraries\SebUjian)
 * dan panel, daftar serta unduhan per mata kuliah milik paket tidak punya rute di sini.
 */
final class SumberSeb implements Sumber
{
    public function matkulMahasiswa(string $userId): array
    {
        return [];
    }

    public function matkul(array $ids): array
    {
        return [];
    }

    public function matkulDariKode(string $kode): ?array
    {
        return null;
    }

    public function matkulUntukPanel(Identity $siapa, int $matkulId): ?array
    {
        return null;
    }

    public function navigasi(Identity $siapa): string
    {
        return '';
    }

    public function redis(): \Redis
    {
        return Jadwal::redis();
    }

    public function catatPeristiwa(string $userId, string $jenis, array $detail, RequestInterface $request): void
    {
        SebUjian::catatPeristiwa($userId, $jenis, $detail, null, null, $request);
    }

    public function catatAudit(string $aksi, int $matkulId, array $detail): void
    {
        Audit::catat($aksi, 'matkul', $matkulId, $detail, $matkulId);
    }
}
