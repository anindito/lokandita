<?php

declare(strict_types=1);

namespace App\Libraries;

/**
 * Antrean judge LokaKode, dibaca LokaUjian untuk SATU hal: peringatan di halaman sesi dosen
 * bahwa server sedang sibuk menilai kiriman LokaKode (SPEC-LOKAUJIAN.md §14; KONTRAK.md §6).
 *
 * LokaUjian hanya MEMBACA panjang kedua daftar antrean (LLEN) lewat JUDGE_QUEUE_REDIS_URL. Ia
 * tidak pernah menulis ke Redis milik judge, tidak membaca isi pekerjaan, dan tidak memutuskan
 * apa pun dari angka ini: bila Redis tidak terjangkau, peringatannya tidak muncul dan tidak ada
 * yang lain yang berubah.
 *
 * Sebuah pekerjaan keluar dari daftar begitu worker mengambilnya, jadi daftar yang berisi berarti
 * worker sedang penuh. Supaya peringatan tidak berkedip di antara dua pertanyaan, "ada antrean"
 * diingat 60 detik di Redis LokaUjian sendiri.
 */
final class AntreanJudge
{
    /** Nama daftar antrean: App\Libraries\Antrean::KUNCI di LokaKode (KONTRAK.md §6). */
    private const DAFTAR = ['lokandita:judge:normal', 'lokandita:judge:low'];

    private const KUNCI_INGAT = 'lokaujian:judge:antre';

    private const INGAT_DETIK = 60;

    /**
     * @return array{antre: int|null, sibuk: bool} antre = pekerjaan yang menunggu sekarang (null:
     *                                             tidak terbaca); sibuk = ada yang menunggu sekarang
     *                                             atau dalam 60 detik terakhir
     */
    public static function baca(): array
    {
        $antre = null;
        try {
            $u = parse_url((string) getenv('JUDGE_QUEUE_REDIS_URL'));
            $r = new \Redis();
            $r->connect((string) ($u['host'] ?? ''), (int) ($u['port'] ?? 6379), 1.0);
            if (($u['pass'] ?? '') !== '') {
                $r->auth($u['pass']);
            }
            $r->select((int) ltrim((string) ($u['path'] ?? '/0'), '/'));
            $antre = 0;
            foreach (self::DAFTAR as $kunci) {
                $antre += (int) $r->lLen($kunci);
            }
            $r->close();
        } catch (\Throwable) {
            $antre = null;
        }
        $sibuk = $antre !== null && $antre > 0;
        try {
            $milik = Jadwal::redis();
            if ($sibuk) {
                $milik->setex(self::KUNCI_INGAT, self::INGAT_DETIK, (string) time());
            } else {
                $sibuk = $milik->exists(self::KUNCI_INGAT) > 0;
            }
        } catch (\Throwable) {
            // Hanya tampilan; lihat keterangan kelas.
        }

        return ['antre' => $antre, 'sibuk' => $sibuk];
    }
}
