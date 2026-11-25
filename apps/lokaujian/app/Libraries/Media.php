<?php

declare(strict_types=1);

namespace App\Libraries;

use App\Policy\Akses;
use Lokandita\Auth\Waktu;

/**
 * Gambar soal: baris `images` dan berkasnya di volume lokandita_lokaujian_media
 * (/srv/media/soal, KONTRAK.md §20).
 *
 * Berkas di sini BUKAN berkas statis publik. Apache hanya menjawab nama berbentuk
 * "<32 heks>-<angka>.(avif|webp|jpg)" di bawah /media/ (KONTRAK.md §16); nama gambar soal adalah
 * "<32 heks>.png|jpg", tanpa tanda hubung, sehingga selalu dijawab 403 di sana. Satu-satunya
 * jalan keluarnya adalah GET /gambar/<kunci>, sesudah hak akses diperiksa.
 *
 * Satu baris per berkas per mata kuliah. Berkas tidak pernah dihapus atau diubah.
 */
final class Media
{
    public const DIREKTORI = '/srv/media/soal';

    public function __construct(private readonly Akses $akses)
    {
    }

    /**
     * Memeriksa dan mengolah satu berkas kiriman, lalu mencatatnya untuk mata kuliah itu.
     * Gambar yang hasil olahannya sama persis dengan gambar mata kuliah itu yang sudah tersimpan
     * tidak disimpan dua kali: baris yang ada dikembalikan (`baru` = false).
     *
     * Pemanggil sudah memastikan pengguna mengelola $matkulId.
     *
     * @return array<string, mixed>|string baris images ditambah `baru`, atau kode galat Gambar
     */
    public function simpan(int $matkulId, string $berkas, string $pelakuId): array|string
    {
        ini_set('memory_limit', '512M');
        $periksa = Gambar::periksa($berkas);
        if (is_string($periksa)) {
            return $periksa;
        }
        $hasil = Gambar::tulis($berkas, self::DIREKTORI);
        if ($hasil === null) {
            return 'tidak_terbaca';
        }
        // PNG sebuah foto bisa lebih besar daripada kirimannya. Berkas tersimpan pun paling banyak
        // 5 MB, supaya ekspor ujian tetap bisa diimpor lagi.
        if ($hasil['byte'] > Gambar::MAKS_BYTE) {
            @unlink($hasil['jalur']);

            return 'hasil_terlalu_besar';
        }
        $db  = db_connect();
        $ada = $this->dariSha($matkulId, $hasil['sha256']);
        if ($ada !== null) {
            @unlink($hasil['jalur']);

            return $ada + ['baru' => false];
        }
        $db->table('images')->insert([
            'course_id' => $matkulId, 'kunci' => $hasil['kunci'], 'jenis' => $hasil['jenis'], 'lebar' => $hasil['lebar'],
            'tinggi' => $hasil['tinggi'], 'byte' => $hasil['byte'], 'sha256' => $hasil['sha256'],
            'uploaded_by' => $pelakuId, 'uploaded_at' => Waktu::kini(),
        ]);

        return (array) $this->dariSha($matkulId, $hasil['sha256']) + ['baru' => true];
    }

    /**
     * @return array<string, mixed>|null
     */
    public function dariSha(int $matkulId, string $sha256): ?array
    {
        return db_connect()->query(
            "SELECT * FROM images WHERE course_id = ? AND sha256 = ? AND {$this->akses->diKelola()}",
            [$matkulId, $sha256],
        )->getRowArray();
    }

    /**
     * Gambar yang boleh dilihat pengguna ini, menurut kuncinya. Tahap U2: hanya pengelola mata
     * kuliahnya. Mahasiswa mendapat gambar mulai tahap U3, selama pengerjaannya berlangsung.
     *
     * @return array<string, mixed>|null
     */
    public function dariKunci(string $kunci): ?array
    {
        return db_connect()->query("SELECT * FROM images WHERE kunci = ? AND {$this->akses->diKelola()}", [$kunci])->getRowArray();
    }

    /**
     * @param array<string, mixed> $gambar baris images
     */
    public static function jalur(array $gambar): string
    {
        return self::DIREKTORI . '/' . $gambar['kunci'] . ($gambar['jenis'] === 'png' ? '.png' : '.jpg');
    }
}
