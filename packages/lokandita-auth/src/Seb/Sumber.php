<?php

declare(strict_types=1);

namespace Lokandita\Auth\Seb;

use CodeIgniter\HTTP\RequestInterface;
use Lokandita\Auth\Identity;

/**
 * Yang disediakan SETIAP APLIKASI kepada pemeriksaan SEB bersama (KONTRAK.md §8).
 *
 * Paket ini tidak tahu dari mana mata kuliah dan pendaftaran berasal: LokaKode membaca
 * tabelnya sendiri, LokaUjian membaca view roster. Paket hanya menyentuh `seb_configs` dan
 * `seb_exam_keys` di skema aplikasi itu, lewat koneksi bawaannya.
 *
 * Kelas pelaksananya didaftarkan di app/Config/Seb.php (`public string $sumber`).
 */
interface Sumber
{
    /**
     * Mata kuliah yang konfigurasinya berlaku bagi mahasiswa ini: pendaftaran AKTIF pada
     * tahun ajaran aktif.
     *
     * @return list<int>
     */
    public function matkulMahasiswa(string $userId): array;

    /**
     * Kode dan nama mata kuliah, DIURUTKAN MENURUT KODE oleh basis data. Id yang tidak ada
     * dilewati.
     *
     * @param list<int> $ids
     *
     * @return list<array{id: int, code: string, name: string}>
     */
    public function matkul(array $ids): array;

    /**
     * Mata kuliah berkode itu (untuk unduhan berkas), atau null.
     *
     * @return array{id: int, code: string, name: string}|null
     */
    public function matkulDariKode(string $kode): ?array;

    /**
     * Mata kuliah untuk panel SEB, dilihat oleh pengguna ini. Tiga keadaan:
     *   null                   tidak ada, atau di luar yang boleh dilihatnya  -> 404
     *   boleh_kelola = false   terlihat, tetapi bukan pengelolanya            -> 403
     *   boleh_kelola = true    superadmin atau pengampunya
     *
     * @return array{id: int, code: string, name: string, boleh_kelola: bool}|null
     */
    public function matkulUntukPanel(Identity $siapa, int $matkulId): ?array;

    /**
     * HTML navigasi aplikasi itu untuk halaman panel (sudah aman untuk ditampilkan).
     */
    public function navigasi(Identity $siapa): string;

    /**
     * Koneksi Redis aplikasi itu (sewa SEB dan pembatas laju). Boleh melempar \RedisException.
     */
    public function redis(): \Redis;

    /**
     * Menulis peristiwa integritas `seb_rejected` atau `seb_terminated` ke tabel aplikasi itu.
     *
     * @param array{alasan: string, jalur: string} $detail
     */
    public function catatPeristiwa(string $userId, string $jenis, array $detail, RequestInterface $request): void;

    /**
     * Menulis `seb.dibuat`, `seb.dirotasi` atau `seb.kunci` ke log audit aplikasi itu. Dipanggil
     * DI DALAM transaksi perubahannya, pada koneksi bawaan aplikasi.
     *
     * @param array<string, mixed> $detail
     */
    public function catatAudit(string $aksi, int $matkulId, array $detail): void;
}
