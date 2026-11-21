<?php

declare(strict_types=1);

namespace App\Libraries;

use Lokandita\Auth\Log;

/**
 * Foto pembuka halaman depan Portal (KONTRAK.md §16).
 *
 * Bawaannya foto di dalam image (docs/branding/foto/hero-portal.jpg, diolah saat build).
 * Superadmin boleh mengunggah foto lain lewat /admin/teks/foto, melihat pratinjaunya,
 * lalu menayangkannya. Foto lama tidak pernah dihapus dan bisa ditayangkan lagi.
 *
 * Yang disajikan hanyalah berkas buatan App\Libraries\Gambar, bernama acak, di
 * /srv/media/hero (volume). Berkas unggahan aslinya dibuang sesudah diolah.
 */
final class Foto
{
    public const SLOT = 'hero';

    /** Direktori berkas unggahan yang sudah diolah, dan alamatnya (Apache: Alias /media). */
    public const DIREKTORI = '/srv/media/hero';

    public const ALAMAT = '/media/hero';

    /** Sumber dan lisensi foto bawaan (docs/branding/LISENSI.md). */
    public const BAWAAN_SUMBER = 'Josh Sorenson / Pexels';

    public const BAWAAN_LISENSI = 'Pexels License — https://www.pexels.com/license/';

    private const BAHASA = ['id', 'en', 'ar'];

    /**
     * Foto bawaan, dalam bentuk yang dipakai tampilan publik/_foto.
     *
     * @return array{id: null, dasar: string, versi: string, lebar: int, tinggi: int, alt: string, sumber: string, lisensi: string}
     */
    public static function bawaan(string $bahasa): array
    {
        $ukuran = json_decode((string) @file_get_contents(FCPATH . 'merek/hero/hero.json'), true);

        return [
            'id'      => null,
            'dasar'   => '/merek/hero/hero',
            // Nama berkas bawaan tetap, jadi alamatnya diberi versi supaya foto bawaan baru tidak tertahan di cache.
            'versi'   => '?v=' . substr((string) getenv('LOKANDITA_COMMIT'), 0, 12),
            'lebar'   => (int) ($ukuran['lebar'] ?? 1600),
            'tinggi'  => (int) ($ukuran['tinggi'] ?? 1067),
            'alt'     => lang('Portal.foto.altBawaan', [], $bahasa),
            'sumber'  => self::BAWAAN_SUMBER,
            'lisensi' => self::BAWAAN_LISENSI,
        ];
    }

    /**
     * Foto yang sedang tayang; foto bawaan bila tidak ada yang ditayangkan, bila basis data
     * tidak terjangkau, atau bila berkasnya tidak ada. Halaman publik tidak boleh gagal karena foto.
     *
     * @return array<string, mixed>
     */
    public static function tayang(string $bahasa): array
    {
        try {
            $baris = db_connect()->query(
                'SELECT f.* FROM foto_tayang t JOIN foto f ON f.id = t.foto_id WHERE t.slot = ?',
                [self::SLOT],
            )->getRowArray();
        } catch (\Throwable $e) {
            Log::event('foto.db_unavailable', ['error' => $e::class]);
            $baris = null;
        }
        if ($baris === null) {
            return self::bawaan($bahasa);
        }
        if (! self::berkasAda((string) $baris['kunci'])) {
            Log::event('foto.berkas_hilang', ['foto' => (int) $baris['id']]);

            return self::bawaan($bahasa);
        }

        return self::untuk($baris, $bahasa);
    }

    /**
     * Baris `foto` dalam bentuk yang dipakai tampilan.
     *
     * @param array<string, mixed> $baris
     *
     * @return array<string, mixed>
     */
    public static function untuk(array $baris, string $bahasa): array
    {
        return [
            'id'      => (int) $baris['id'],
            'dasar'   => self::ALAMAT . '/' . $baris['kunci'],
            'versi'   => '',
            'lebar'   => (int) $baris['lebar'],
            'tinggi'  => (int) $baris['tinggi'],
            'alt'     => (string) $baris['alt_' . (in_array($bahasa, self::BAHASA, true) ? $bahasa : 'id')],
            'sumber'  => (string) $baris['sumber'],
            'lisensi' => (string) $baris['lisensi'],
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function cari(int $id): ?array
    {
        return db_connect()->query('SELECT * FROM foto WHERE id = ?', [$id])->getRowArray();
    }

    /**
     * Semua foto yang pernah diunggah, terbaru dulu.
     *
     * @return list<array<string, mixed>>
     */
    public static function semua(): array
    {
        return db_connect()->query('SELECT * FROM foto ORDER BY id DESC')->getResultArray();
    }

    /** Id foto yang sedang tayang; null = foto bawaan. */
    public static function idTayang(): ?int
    {
        $baris = db_connect()->query('SELECT foto_id FROM foto_tayang WHERE slot = ?', [self::SLOT])->getRowArray();

        return isset($baris['foto_id']) ? (int) $baris['foto_id'] : null;
    }

    /**
     * Memeriksa isian yang wajib menyertai sebuah unggahan.
     *
     * @param array<string, mixed> $isian
     *
     * @return array{0: array<string, string>, 1: list<string>} isian yang sudah dirapikan, dan nama isian yang salah
     */
    public static function isian(array $isian): array
    {
        $galat = [];
        $hasil = [];
        foreach (['alt_id' => 300, 'alt_en' => 300, 'alt_ar' => 300, 'sumber' => 200, 'lisensi' => 200] as $nama => $maks) {
            $teks = is_string($isian[$nama] ?? null) ? trim((string) preg_replace('/\s+/u', ' ', $isian[$nama])) : '';
            if ($teks === '' || mb_strlen($teks) > $maks || ! mb_check_encoding($teks, 'UTF-8') || preg_match('/[\x00-\x1F\x7F]/', $teks) === 1) {
                $galat[] = $nama;
            }
            $hasil[$nama] = $teks;
        }
        if (($isian['berhak'] ?? '') !== '1') {
            $galat[] = 'berhak';
        }

        return [$hasil, $galat];
    }

    /**
     * Mengolah dan menyimpan satu unggahan sebagai foto yang BELUM tayang.
     *
     * @param string               $berkas   berkas sementara dari PHP
     * @param string               $namaAsli nama berkas menurut peramban; hanya disimpan sebagai teks
     * @param array<string, string> $isian    hasil isian() tanpa galat
     *
     * @return int|string id foto, atau kode galat Gambar::periksa() / 'tidak_terbaca' / 'tidak_tersimpan'
     */
    public static function unggah(string $berkas, string $namaAsli, array $isian, string $pelaku): int|string
    {
        $periksa = Gambar::periksa($berkas);
        if (! is_array($periksa)) {
            return $periksa;
        }
        if (! is_dir(self::DIREKTORI) && ! @mkdir(self::DIREKTORI, 0755, true)) {
            Log::event('foto.direktori_gagal', []);

            return 'tidak_tersimpan';
        }
        // Nama berkas dibuat server: 128 bit acak. Nama dari pengunggah tidak pernah menyentuh sistem berkas.
        $kunci  = bin2hex(random_bytes(16));
        $ukuran = Gambar::buat($berkas, self::DIREKTORI . '/' . $kunci);
        if ($ukuran === null) {
            return 'tidak_terbaca';
        }

        $kini = gmdate('Y-m-d H:i:s');
        $nama = mb_substr(trim((string) preg_replace('/[\x00-\x1F\x7F\/\\\\]+/u', ' ', mb_scrub($namaAsli, 'UTF-8'))), 0, 255);
        $db   = db_connect();
        try {
            $db->transException(true)->transStart();
            $db->table('foto')->insert($isian + [
                'kunci'       => $kunci,
                'nama_asli'   => $nama === '' ? '(tanpa nama)' : $nama,
                'jenis'       => $periksa['jenis'],
                'lebar'       => $ukuran['lebar'],
                'tinggi'      => $ukuran['tinggi'],
                'berhak'      => 1,
                'uploaded_by' => $pelaku,
                'uploaded_at' => $kini,
            ]);
            $id = (int) $db->insertID();
            Teks::audit('foto.diunggah', 'foto', null, null, self::catatan(self::cari($id)), $pelaku, $kini);
            $db->transComplete();
        } catch (\Throwable $e) {
            foreach (Gambar::LEBAR as $l) {
                foreach (Gambar::FORMAT as $f) {
                    @unlink(self::DIREKTORI . "/{$kunci}-{$l}.{$f}");
                }
            }
            Log::event('foto.simpan_gagal', ['error' => $e::class]);

            return 'tidak_tersimpan';
        }

        return $id;
    }

    /**
     * Menayangkan foto $id. Foto yang sudah pernah tayang dicatat sebagai "dipulihkan".
     * Mengembalikan false bila foto itu memang sedang tayang atau berkasnya tidak ada.
     */
    public static function tayangkan(int $id, string $pelaku): bool
    {
        $baris = self::cari($id);
        $kini  = self::idTayang();
        if ($baris === null || $kini === $id || ! self::berkasAda((string) $baris['kunci'])) {
            return false;
        }
        $aksi = $baris['published_at'] === null ? 'foto.ditayangkan' : 'foto.dipulihkan';
        self::ganti($id, $aksi, self::catatan($kini === null ? null : self::cari($kini)), self::catatan($baris), $pelaku);

        return true;
    }

    /**
     * Kembali ke foto bawaan. Mengembalikan false bila memang foto bawaan yang sedang tayang.
     */
    public static function keBawaan(string $pelaku): bool
    {
        $kini = self::idTayang();
        if ($kini === null) {
            return false;
        }
        self::ganti(null, 'foto.bawaan', self::catatan(self::cari($kini)), self::catatan(null), $pelaku);

        return true;
    }

    /** Semua lebar dan format foto itu ada di volume. */
    public static function berkasAda(string $kunci): bool
    {
        foreach (Gambar::LEBAR as $l) {
            foreach (Gambar::FORMAT as $f) {
                if (! is_file(self::DIREKTORI . "/{$kunci}-{$l}.{$f}")) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * @param array<string, mixed> $sebelum
     * @param array<string, mixed> $sesudah
     */
    private static function ganti(?int $id, string $aksi, array $sebelum, array $sesudah, string $pelaku): void
    {
        $db   = db_connect();
        $kini = gmdate('Y-m-d H:i:s');
        $db->transException(true)->transStart();
        $db->query(
            'INSERT INTO foto_tayang (slot, foto_id, updated_by, updated_at) VALUES (?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE foto_id = VALUES(foto_id), updated_by = VALUES(updated_by), updated_at = VALUES(updated_at)',
            [self::SLOT, $id, $pelaku, $kini],
        );
        if ($id !== null) {
            $db->query('UPDATE foto SET published_at = COALESCE(published_at, ?) WHERE id = ?', [$kini, $id]);
        }
        Teks::audit($aksi, 'foto', null, $sebelum, $sesudah, $pelaku, $kini);
        $db->transComplete();
    }

    /**
     * Keterangan sebuah foto untuk log audit; null = foto bawaan.
     *
     * @param array<string, mixed>|null $baris
     *
     * @return array<string, mixed>
     */
    private static function catatan(?array $baris): array
    {
        if ($baris === null) {
            return ['foto' => 'bawaan (hero-portal.jpg)', 'sumber' => self::BAWAAN_SUMBER, 'lisensi' => self::BAWAAN_LISENSI];
        }

        return [
            'foto'    => '#' . $baris['id'],
            'berkas'  => $baris['nama_asli'],
            'ukuran'  => $baris['lebar'] . ' × ' . $baris['tinggi'],
            'sumber'  => $baris['sumber'],
            'lisensi' => $baris['lisensi'],
            'alt_id'  => $baris['alt_id'],
            'alt_en'  => $baris['alt_en'],
            'alt_ar'  => $baris['alt_ar'],
        ];
    }
}
