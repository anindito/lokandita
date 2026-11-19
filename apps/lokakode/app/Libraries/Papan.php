<?php

declare(strict_types=1);

namespace App\Libraries;

use Lokandita\Auth\Log;
use Lokandita\Auth\UserDirectory;

/**
 * Papan peringkat sebuah pertemuan (SPEC.md §12): per kelas, dan per mata
 * kuliah dalam satu tahun ajaran.
 *
 * Urutannya: nilai pertemuan (tinggi dulu), surplus (tinggi dulu), waktu nilai
 * itu tercapai (awal dulu), jumlah kiriman (sedikit dulu). Dua mahasiswa yang
 * sama di keempatnya mendapat peringkat yang sama. Hanya pendaftaran AKTIF yang
 * ikut (SPEC.md §5); mahasiswa tanpa kiriman yang dinilai tidak punya baris.
 * Kiriman latihan sesudah tutup tidak pernah masuk, karena tidak masuk meeting_scores.
 *
 * Fungsi di sini menerima baris kelas atau pertemuan yang SUDAH lolos
 * App\Policy\Akses di pemanggilnya (Jendela::satu, Kurikulum::pertemuan,
 * Ruang::pertemuan); tidak ada yang berangkat dari id di URL saja.
 *
 * Urutan disimpan di Redis (tanpa nama; hanya id dan angka) dan dibuang setiap
 * kali ada nilai yang dihitung ulang atau pendaftaran yang berubah. Bila Redis
 * tidak bisa dihubungi papan dihitung langsung dari MySQL.
 */
final class Papan
{
    private const AWALAN = 'lokakode:papan:';

    // Jaring pengaman bila ada pembuangan yang terlewat; pembuangan yang sebenarnya ada di lupakan().
    private const UMUR_DETIK = 300;

    private const URUT = 'ms.score DESC, ms.surplus DESC, ms.reached_at ASC, ms.submissions ASC, ms.user_id ASC';

    /**
     * Papan satu kelas.
     *
     * @return list<array{peringkat: int, user_id: string, nilai: float, total: float, surplus: float, kiriman: int, tercapai: ?string, kelas: string}>
     */
    public static function kelas(int $pertemuanId, int $kelasId): array
    {
        return self::ambil("{$pertemuanId}:kelas:{$kelasId}", '
            SELECT ms.user_id, ms.score, ms.total_points, ms.surplus, ms.submissions, ms.reached_at, k.name AS kelas
            FROM enrollments e
            JOIN classes k ON k.id = e.class_id
            JOIN meetings m ON m.course_id = k.course_id AND m.id = ?
            JOIN meeting_scores ms ON ms.user_id = e.user_id AND ms.meeting_id = m.id
            WHERE k.id = ? AND e.status = \'AKTIF\'
            ORDER BY ' . self::URUT, [$pertemuanId, $kelasId]);
    }

    /**
     * Papan seluruh kelas mata kuliah pertemuan itu dalam satu tahun ajaran.
     *
     * @return list<array<string, mixed>> seperti kelas()
     */
    public static function matkul(int $pertemuanId, int $tahunId): array
    {
        return self::ambil("{$pertemuanId}:tahun:{$tahunId}", '
            SELECT ms.user_id, ms.score, ms.total_points, ms.surplus, ms.submissions, ms.reached_at, k.name AS kelas
            FROM enrollments e
            JOIN classes k ON k.id = e.class_id
            JOIN meetings m ON m.course_id = k.course_id AND m.id = ?
            JOIN meeting_scores ms ON ms.user_id = e.user_id AND ms.meeting_id = m.id
            WHERE k.academic_year_id = ? AND e.status = \'AKTIF\'
            ORDER BY ' . self::URUT, [$pertemuanId, $tahunId]);
    }

    /**
     * @param list<int> $ikat
     *
     * @return list<array<string, mixed>>
     */
    private static function ambil(string $kunci, string $sql, array $ikat): array
    {
        $redis = null;
        try {
            $redis  = Antrean::redis();
            $simpan = $redis->get(self::AWALAN . $kunci);
            if (is_string($simpan)) {
                $papan = json_decode($simpan, true);
                if (is_array($papan)) {
                    return $papan;
                }
            }
        } catch (\RedisException $e) {
            Log::event('papan.cache_unavailable', ['reason' => $e->getMessage()]);
            $redis = null;
        }

        $papan   = [];
        $sebelum = null;
        foreach (db_connect()->query($sql, $ikat)->getResultArray() as $i => $b) {
            $kunciUrut = [$b['score'], $b['surplus'], $b['reached_at'], $b['submissions']];
            $papan[]   = [
                'peringkat' => $kunciUrut === $sebelum ? $papan[$i - 1]['peringkat'] : $i + 1,
                'user_id'   => (string) $b['user_id'],
                'nilai'     => (float) $b['score'],
                'total'     => (float) $b['total_points'],
                'surplus'   => (float) $b['surplus'],
                'kiriman'   => (int) $b['submissions'],
                'tercapai'  => $b['reached_at'],
                'kelas'     => (string) $b['kelas'],
            ];
            $sebelum = $kunciUrut;
        }

        if ($redis !== null) {
            try {
                $redis->setex(self::AWALAN . $kunci, self::UMUR_DETIK, json_encode($papan));
            } catch (\RedisException $e) {
                Log::event('papan.cache_unavailable', ['reason' => $e->getMessage()]);
            }
        }

        return $papan;
    }

    /**
     * Membuang papan yang tersimpan: milik satu pertemuan (ada nilai yang dihitung
     * ulang), atau semuanya (pendaftaran berubah: pindah kelas, mundur, lulus).
     * Dipanggil SESUDAH transaksinya selesai.
     */
    public static function lupakan(?int $pertemuanId = null): void
    {
        try {
            $redis = Antrean::redis();
            $kunci = $redis->keys(self::AWALAN . ($pertemuanId === null ? '' : $pertemuanId . ':') . '*');
            if ($kunci !== []) {
                $redis->del($kunci);
            }
        } catch (\RedisException $e) {
            // Tanpa pembuangan, papan lama paling lama bertahan UMUR_DETIK.
            Log::event('papan.cache_unavailable', ['reason' => $e->getMessage()]);
        }
    }

    /**
     * Papan untuk DOSEN: nama dan nomor induk selalu tampil.
     *
     * @param list<array<string, mixed>> $papan
     *
     * @return list<array<string, mixed>>
     */
    public static function bernama(array $papan): array
    {
        $orang = UserDirectory::byIds(array_column($papan, 'user_id'));

        return array_map(static fn (array $b): array => $b + [
            'nama'        => $orang[$b['user_id']]['nama'] ?? '—',
            'nomor_induk' => $orang[$b['user_id']]['nomor_induk'] ?? '—',
        ], $papan);
    }

    /**
     * Papan untuk MAHASISWA: nama saja, tanpa nomor induk, tanpa id. Bila pertemuannya
     * disamarkan, peserta lain tampil sebagai nomor urut dan hanya barisnya sendiri
     * yang bernama. Yang dikembalikan tidak memuat user_id siapa pun.
     *
     * @param list<array<string, mixed>> $papan
     *
     * @return list<array{peringkat: int, nama: ?string, urut: int, saya: bool, nilai: float, total: float}>
     */
    public static function untukMahasiswa(array $papan, string $sayaId, bool $samar): array
    {
        $orang = UserDirectory::byIds(array_column($papan, 'user_id'));
        $hasil = [];
        foreach ($papan as $i => $b) {
            $saya    = $b['user_id'] === $sayaId;
            $hasil[] = [
                'peringkat' => $b['peringkat'],
                'nama'      => $samar && ! $saya ? null : ($orang[$b['user_id']]['nama'] ?? '—'),
                'urut'      => $i + 1,
                'saya'      => $saya,
                'nilai'     => $b['nilai'],
                'total'     => $b['total'],
            ];
        }

        return $hasil;
    }
}
