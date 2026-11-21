<?php

declare(strict_types=1);

namespace App\Libraries;

use Lokandita\Auth\Locale;
use Lokandita\Auth\Log;

/**
 * Teks halaman publik Portal (KONTRAK.md §15): bawaannya berkas bahasa, dan superadmin
 * boleh menggantinya per halaman per bahasa lewat /admin/teks.
 *
 * Satu aturan menjaga semuanya tetap sederhana: teks pengganti selalu BERBENTUK SAMA
 * dengan bawaannya — kunci yang sama dan jumlah bagian yang sama. Karena itu tampilan
 * publik tidak perlu tahu dari mana sebuah teks berasal.
 */
final class Teks
{
    /**
     * Halaman yang bisa disunting. `aplikasi` = keterangan tiga kartu aplikasi. `kontak` tidak
     * ada lagi: sejak 2026-11-21 isinya adalah bagian-bagian terakhir halaman `tentang`.
     */
    public const HALAMAN = ['depan', 'aplikasi', 'tentang', 'privasi', 'ketentuan'];

    /** Halaman yang menampilkan "Terakhir diperbarui". */
    private const BERTANGGAL = ['privasi', 'ketentuan'];

    private const MAKS_BARIS = 400;    // judul, deskripsi dan teks satu baris lain

    private const MAKS_BUTIR = 3000;   // satu paragraf atau satu butir

    private const MAKS_JUMLAH = 40;    // paragraf atau butir dalam satu daftar

    /** @var array<string, array<string, array<string, mixed>>|null> bahasa => halaman => baris; null = basis data tidak terjangkau */
    private static array $muat = [];

    public static function dikenal(string $halaman, string $bahasa): bool
    {
        return in_array($halaman, self::HALAMAN, true) && in_array($bahasa, Locale::DAFTAR, true);
    }

    /**
     * Teks bawaan dari berkas bahasa.
     *
     * @return array<string, mixed>
     */
    public static function bawaan(string $halaman, string $bahasa): array
    {
        $semua = require APPPATH . "Language/{$bahasa}/Portal.php";

        return $semua[$halaman];
    }

    /**
     * Teks yang berlaku: pengganti bila ada, kalau tidak bawaan. Halaman publik tidak
     * boleh gagal karena tabel ini: bila basis data tidak terjangkau, bawaan yang dipakai.
     *
     * @return array<string, mixed>
     */
    public static function ambil(string $halaman, string $bahasa): array
    {
        $baris = self::baris($bahasa)[$halaman] ?? null;
        if ($baris === null) {
            return self::bawaan($halaman, $bahasa);
        }
        $isi = $baris['isi'];
        if (in_array($halaman, self::BERTANGGAL, true)) {
            $isi['diperbarui'] = lang('Portal.diperbaruiPada', [self::tanggal($baris['updated_at'], $bahasa)]);
        }

        return $isi;
    }

    /**
     * Baris pengganti untuk satu bahasa, dikunci dengan nama halaman.
     *
     * @return array<string, array{isi: array<string, mixed>, updated_by: string, updated_at: string}>
     */
    public static function baris(string $bahasa): array
    {
        if (! array_key_exists($bahasa, self::$muat)) {
            try {
                $hasil = [];
                foreach (db_connect()->query('SELECT halaman, isi, updated_by, updated_at FROM teks WHERE bahasa = ?', [$bahasa])->getResultArray() as $b) {
                    $isi = json_decode((string) $b['isi'], true, 16, JSON_THROW_ON_ERROR);
                    // Teks pengganti yang bentuknya tidak lagi sama dengan bawaan (halaman yang sudah tidak
                    // ada, atau jumlah bagiannya berubah oleh sebuah deploy) tidak dipakai: bawaan yang tampil,
                    // dan teks lama tetap ada di tabel serta di riwayat untuk disalin ke form.
                    if (! self::dikenal((string) $b['halaman'], $bahasa) || ! self::sebentuk($isi, self::bawaan((string) $b['halaman'], $bahasa))) {
                        Log::event('teks.bentuk_usang', ['halaman' => $b['halaman'], 'bahasa' => $bahasa]);

                        continue;
                    }
                    $hasil[$b['halaman']] = ['isi' => $isi, 'updated_by' => $b['updated_by'], 'updated_at' => $b['updated_at']];
                }
                self::$muat[$bahasa] = $hasil;
            } catch (\Throwable $e) {
                Log::event('teks.db_unavailable', ['error' => $e::class]);
                self::$muat[$bahasa] = null;
            }
        }

        return self::$muat[$bahasa] ?? [];
    }

    /**
     * Kunci yang sama dan jumlah bagian yang sama dengan bawaan.
     *
     * @param array<string, mixed> $bawaan
     */
    private static function sebentuk(mixed $isi, array $bawaan): bool
    {
        return is_array($isi)
            && array_keys($isi) === array_keys($bawaan)
            && count((array) ($isi['bagian'] ?? [])) === count((array) ($bawaan['bagian'] ?? []));
    }

    /**
     * Menyusun teks pengganti dari isian form, berbentuk persis seperti $bawaan.
     * Daftar (paragraf, butir, langkah) diisi satu per baris di sebuah kotak teks.
     *
     * @param array<string, mixed> $form   isian `t[...]`
     * @param array<string, mixed> $bawaan bentuk yang harus diikuti
     *
     * @return array{0: array<string, mixed>, 1: list<string>} teks dan daftar kunci yang salah (mis. "bagian.2.judul")
     */
    public static function dariForm(array $form, array $bawaan): array
    {
        $galat = [];
        $hasil = [];
        foreach ($bawaan as $kunci => $contoh) {
            if ($kunci === 'diperbarui') {
                // Tanggal diisi sendiri saat menyimpan; nilai bawaan dibawa supaya bentuknya tetap sama.
                $hasil[$kunci] = $contoh;
            } elseif ($kunci === 'bagian') {
                $hasil[$kunci] = [];
                foreach ($contoh as $i => $bagian) {
                    $isian = is_array($form['bagian'][$i] ?? null) ? $form['bagian'][$i] : [];
                    $satu  = ['judul' => self::baris1($isian['judul'] ?? null, "bagian.{$i}.judul", $galat)];
                    $satu['isi'] = self::daftar($isian['isi'] ?? null, "bagian.{$i}.isi", $galat, true);
                    if (array_key_exists('butir', $bagian)) {
                        $satu['butir'] = self::daftar($isian['butir'] ?? null, "bagian.{$i}.butir", $galat, true);
                    }
                    if ($satu['isi'] === [] && ($satu['butir'] ?? []) === []) {
                        $galat[] = "bagian.{$i}.isi";
                    }
                    $hasil[$kunci][] = $satu;
                }
            } elseif (is_array($contoh)) {
                $hasil[$kunci] = self::daftar($form[$kunci] ?? null, (string) $kunci, $galat, false);
            } else {
                $hasil[$kunci] = self::baris1($form[$kunci] ?? null, (string) $kunci, $galat);
            }
        }

        return [$hasil, array_values(array_unique($galat))];
    }

    /**
     * Menyimpan teks pengganti dan baris auditnya dalam satu transaksi. Simpanan yang
     * tidak mengubah apa pun tidak menulis apa pun. Mengembalikan true bila ada yang berubah.
     *
     * @param array<string, mixed> $isi hasil dariForm() tanpa galat
     */
    public static function simpan(string $halaman, string $bahasa, array $isi, string $pelaku): bool
    {
        $sebelum = self::baris($bahasa)[$halaman]['isi'] ?? null;
        if ($sebelum === $isi || ($sebelum === null && $isi === self::bawaan($halaman, $bahasa))) {
            return false;
        }
        $db   = db_connect();
        $kini = gmdate('Y-m-d H:i:s');
        $json = json_encode($isi, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        $db->transException(true)->transStart();
        $db->query(
            'INSERT INTO teks (halaman, bahasa, isi, updated_by, updated_at) VALUES (?, ?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE isi = VALUES(isi), updated_by = VALUES(updated_by), updated_at = VALUES(updated_at)',
            [$halaman, $bahasa, $json, $pelaku, $kini],
        );
        self::audit('teks.diubah', $halaman, $bahasa, $sebelum ?? self::bawaan($halaman, $bahasa), $isi, $pelaku, $kini);
        $db->transComplete();
        unset(self::$muat[$bahasa]);

        return true;
    }

    /**
     * Menghapus teks pengganti: halaman itu kembali ke berkas bahasa.
     */
    public static function kembalikan(string $halaman, string $bahasa, string $pelaku): bool
    {
        $sebelum = self::baris($bahasa)[$halaman]['isi'] ?? null;
        if ($sebelum === null) {
            return false;
        }
        $db = db_connect();
        $db->transException(true)->transStart();
        $db->query('DELETE FROM teks WHERE halaman = ? AND bahasa = ?', [$halaman, $bahasa]);
        self::audit('teks.dikembalikan', $halaman, $bahasa, $sebelum, null, $pelaku, gmdate('Y-m-d H:i:s'));
        $db->transComplete();
        unset(self::$muat[$bahasa]);

        return true;
    }

    /**
     * Tanggal (Asia/Jakarta) dari waktu UTC di basis data, ditulis dalam bahasa itu dengan angka Latin.
     */
    public static function tanggal(string $utc, string $bahasa): string
    {
        $format = new \IntlDateFormatter(['id' => 'id_ID', 'en' => 'en_GB', 'ar' => 'ar@numbers=latn'][$bahasa], \IntlDateFormatter::LONG, \IntlDateFormatter::NONE, 'Asia/Jakarta');

        return (string) $format->format(new \DateTimeImmutable($utc, new \DateTimeZone('UTC')));
    }

    /**
     * Satu baris log audit Portal (hanya ditambah). Dipakai juga oleh App\Libraries\Foto,
     * dengan $halaman = 'foto' dan tanpa bahasa. Panggil di dalam transaksi perubahannya.
     *
     * @param array<string, mixed>|null $sebelum
     * @param array<string, mixed>|null $sesudah
     */
    public static function audit(string $aksi, string $halaman, ?string $bahasa, ?array $sebelum, ?array $sesudah, string $pelaku, string $kini): void
    {
        $request = service('request');
        $json    = static fn (?array $a): ?string => $a === null ? null : json_encode($a, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        db_connect()->table('audit_log')->insert([
            'actor_id'   => $pelaku,
            'action'     => $aksi,
            'halaman'    => $halaman,
            'bahasa'     => $bahasa,
            'sebelum'    => $json($sebelum),
            'sesudah'    => $json($sesudah),
            'ip'         => method_exists($request, 'getIPAddress') ? $request->getIPAddress() : null,
            'user_agent' => method_exists($request, 'getUserAgent') ? substr((string) $request->getUserAgent(), 0, 255) : null,
            'created_at' => $kini,
        ]);
        Log::event($aksi, ['actor' => $pelaku, 'halaman' => $halaman, 'bahasa' => $bahasa]);
    }

    /**
     * Teks satu baris yang wajib diisi.
     *
     * @param list<string> $galat
     */
    private static function baris1(mixed $nilai, string $kunci, array &$galat): string
    {
        $teks = is_string($nilai) ? trim((string) preg_replace('/\s+/u', ' ', $nilai)) : '';
        if ($teks === '' || mb_strlen($teks) > self::MAKS_BARIS || ! self::bersih($teks)) {
            $galat[] = $kunci;
        }

        return $teks;
    }

    /**
     * Daftar dari kotak teks: satu paragraf atau butir per baris, baris kosong diabaikan.
     *
     * @param list<string> $galat
     *
     * @return list<string>
     */
    private static function daftar(mixed $nilai, string $kunci, array &$galat, bool $bolehKosong): array
    {
        $daftar = [];
        foreach (preg_split('/\R/u', is_string($nilai) ? $nilai : '') ?: [] as $baris) {
            $baris = trim((string) preg_replace('/[ \t]+/u', ' ', $baris));
            if ($baris !== '') {
                $daftar[] = $baris;
            }
        }
        $salah = count($daftar) > self::MAKS_JUMLAH || ($daftar === [] && ! $bolehKosong);
        foreach ($daftar as $baris) {
            $salah = $salah || mb_strlen($baris) > self::MAKS_BUTIR || ! self::bersih($baris);
        }
        if ($salah) {
            $galat[] = $kunci;
        }

        return $daftar;
    }

    /** UTF-8 yang sah dan tanpa karakter kendali. */
    private static function bersih(string $teks): bool
    {
        return mb_check_encoding($teks, 'UTF-8') && preg_match('/[\x00-\x08\x0B-\x1F\x7F]/', $teks) !== 1;
    }
}
