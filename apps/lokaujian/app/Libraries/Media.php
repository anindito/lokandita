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
     * Gambar yang boleh dilihat pengguna ini, menurut kuncinya (SPEC-LOKAUJIAN.md §5.3):
     *   - yang mengelola mata kuliah gambar itu;
     *   - staf yang boleh menjalankan sesi sebuah ujian terbit yang memuat gambar itu (pengajar
     *     melihat pratinjau ujian yang diberikan ke kelasnya);
     *   - mahasiswa, HANYA selama ia punya pengerjaan yang sedang berjalan pada ujian yang memuat
     *     gambar itu, atau (tahap U5) pengerjaan di kelas yang hasilnya dirilis dengan kunci —
     *     untuk tinjauannya.
     * Selain itu null — sama dengan kunci yang tidak ada.
     *
     * @return array<string, mixed>|null
     */
    public function dariKunci(string $kunci): ?array
    {
        $db    = db_connect();
        $milik = $db->query("SELECT * FROM images WHERE kunci = ? AND {$this->akses->diKelola()}", [$kunci])->getRowArray();
        if ($milik !== null) {
            return $milik;
        }
        // Barisnya dibaca dulu tanpa syarat hak HANYA untuk mendapat id-nya; ia baru dikembalikan
        // bila salah satu pemeriksaan di bawah lolos.
        $g = $db->query('SELECT * FROM images WHERE kunci = ?', [$kunci])->getRowArray();
        if ($g === null) {
            return null;
        }
        $id    = (int) $g['id'];
        $ujian = "(SELECT q.exam_id FROM exam_questions q WHERE q.image_id = {$id}
                   UNION SELECT k.exam_id FROM exam_groups k WHERE k.image_id = {$id}
                   UNION SELECT q2.exam_id FROM exam_options o JOIN exam_questions q2 ON q2.id = o.exam_question_id WHERE o.image_id = {$id})";
        if ($this->akses->mahasiswa()) {
            $boleh = $db->query(
                "SELECT 1 FROM attempts a JOIN sessions s ON s.id = a.session_id JOIN exams x ON x.id = a.exam_id
                 WHERE a.user_id = {$this->akses->idSql()} AND (" . Tenggat::berjalan() . " OR s.release_level = 'nilai_dan_kunci') AND a.exam_id IN {$ujian} LIMIT 1",
            )->getRowArray();
        } else {
            $boleh = $db->query(
                "SELECT 1 FROM sessions s JOIN exams x ON x.id = s.exam_id
                 WHERE x.status <> 'draf' AND {$this->akses->diJalankan('s.class_id')} AND s.exam_id IN {$ujian} LIMIT 1",
            )->getRowArray();
        }

        return $boleh === null ? null : $g;
    }

    /**
     * @param array<string, mixed> $gambar baris images
     */
    public static function jalur(array $gambar): string
    {
        return self::DIREKTORI . '/' . $gambar['kunci'] . ($gambar['jenis'] === 'png' ? '.png' : '.jpg');
    }
}
