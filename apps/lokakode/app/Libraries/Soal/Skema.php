<?php

declare(strict_types=1);

namespace App\Libraries\Soal;

/**
 * Pemeriksaan bentuk berkas impor soal (KONTRAK.md §7, SPEC.md §8.3).
 *
 * Ketat pada tipe dan kode, tetapi setiap galat mengatakan apa yang harus
 * diubah. Sebuah galat adalah ['field', 'kode', 'argumen']; kalimatnya ada di
 * berkas bahasa (Soal.skema.<kode>), jadi sama di web dan di CLI.
 */
final class Skema
{
    public const TINGKAT = ['mudah_sekali', 'mudah', 'mudah_sedang', 'sedang', 'sulit'];
    public const MODE    = ['exact', 'trimmed', 'token', 'float'];
    public const BAHASA  = ['c', 'cpp', 'python'];
    public const WAKTU_BAWAAN = ['c' => 2000, 'cpp' => 2000, 'python' => 5000];

    public const KUNCI_BERKAS = ['course', 'meeting', 'language', 'problems'];
    public const KUNCI_SOAL   = ['slug', 'difficulty', 'comparison_mode', 'time_limit_ms', 'samples_count', 'hidden_count',
        'required_concepts_in_submission', 'params', 'translations', 'generator', 'reference_solution'];
    public const KUNCI_TEKS = ['title', 'statement', 'input_format', 'output_format', 'constraints'];

    public const MAKS_SOAL   = 200;
    public const MAKS_SUMBER = 262144; // 256 KB, sama dengan batas judge
    public const MAKS_TEKS   = 65536;

    /**
     * Nilai seperti yang ditulis di berkas, dipendekkan, untuk ditampilkan di pesan.
     */
    public static function tampil(mixed $nilai): string
    {
        $teks = json_encode($nilai, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($teks === false) {
            return '?';
        }

        return mb_strlen($teks) > 60 ? mb_substr($teks, 0, 57) . '…' : $teks;
    }

    /**
     * @return array{field: string, kode: string, argumen: list<string>}
     */
    private static function galat(string $field, string $kode, string ...$argumen): array
    {
        return ['field' => $field, 'kode' => $kode, 'argumen' => array_values($argumen)];
    }

    /**
     * Kunci yang paling mirip, untuk "maksud Anda …?".
     *
     * @param list<string> $sah
     */
    private static function mirip(string $kunci, array $sah): string
    {
        $terbaik = '';
        $jarak   = 4;
        foreach ($sah as $s) {
            $j = levenshtein(strtolower($kunci), $s);
            if ($j < $jarak) {
                [$terbaik, $jarak] = [$s, $j];
            }
        }

        return $terbaik;
    }

    /**
     * @param array<mixed> $obyek
     * @param list<string> $sah
     *
     * @return list<array{field: string, kode: string, argumen: list<string>}>
     */
    private static function kunciAsing(array $obyek, array $sah, string $awalan = ''): array
    {
        $galat = [];
        foreach (array_keys($obyek) as $kunci) {
            $kunci = (string) $kunci;
            if (! in_array($kunci, $sah, true)) {
                $mirip   = self::mirip($kunci, $sah);
                $galat[] = $mirip !== ''
                    ? self::galat($awalan . $kunci, 'kunciMirip', $awalan . $kunci, $mirip)
                    : self::galat($awalan . $kunci, 'kunciAsing', $awalan . $kunci, implode(', ', $sah));
            }
        }

        return $galat;
    }

    private static function obyek(mixed $x): bool
    {
        return is_array($x) && ($x === [] || ! array_is_list($x));
    }

    /**
     * Bagian atas berkas: course, meeting, language, problems. Belum melihat basis data.
     *
     * @return list<array{field: string, kode: string, argumen: list<string>}>
     */
    public static function periksaBerkas(mixed $data): array
    {
        if (! self::obyek($data) || $data === []) {
            return [self::galat('', 'bukanObyek')];
        }
        $galat = self::kunciAsing($data, self::KUNCI_BERKAS);

        if (! isset($data['course']) || ! is_string($data['course']) || preg_match('/^[A-Z0-9][A-Z0-9-]{1,19}$/', $data['course']) !== 1) {
            $galat[] = self::galat('course', 'course', self::tampil($data['course'] ?? null));
        }
        $m = $data['meeting'] ?? null;
        if (! is_int($m)) {
            // "4", "4 — Percabangan", 4.0: sebut angka yang dimaksud bila bisa ditebak.
            $tebak = is_string($m) && preg_match('/^\s*(\d{1,2})\b/', $m, $c) === 1 ? $c[1] : (is_float($m) && $m == (int) $m ? (string) (int) $m : '');
            $galat[] = $tebak !== '' && (int) $tebak >= 1 && (int) $tebak <= 16
                ? self::galat('meeting', 'meetingBulat', $tebak, self::tampil($m))
                : self::galat('meeting', 'meeting', self::tampil($m));
        } elseif ($m < 1 || $m > 16) {
            $galat[] = self::galat('meeting', 'meeting', self::tampil($m));
        }
        if (! in_array($data['language'] ?? null, self::BAHASA, true)) {
            $galat[] = self::galat('language', 'language', self::tampil($data['language'] ?? null));
        }
        $p = $data['problems'] ?? null;
        if (! is_array($p) || ! array_is_list($p) || $p === []) {
            $galat[] = self::galat('problems', 'problems');
        } elseif (count($p) > self::MAKS_SOAL) {
            $galat[] = self::galat('problems', 'problemsBanyak', (string) self::MAKS_SOAL, (string) count($p));
        }

        return $galat;
    }

    /**
     * Satu soal. Mengembalikan soal yang sudah dinormalkan (nilai bawaan terisi)
     * dan daftar galatnya; soal dipakai hanya bila daftar galat kosong.
     *
     * @param list<string> $konsepBahasa kode konsep katalog yang berlaku untuk bahasa pertemuan
     *
     * @return array{0: array<string, mixed>, 1: list<array{field: string, kode: string, argumen: list<string>}>}
     */
    public static function periksaSoal(mixed $s, string $bahasa, array $konsepBahasa): array
    {
        if (! self::obyek($s) || $s === []) {
            return [[], [self::galat('', 'soalBukanObyek')]];
        }
        $galat = self::kunciAsing($s, self::KUNCI_SOAL);

        if (! is_string($s['slug'] ?? null) || preg_match('/^[a-z0-9-]{3,80}$/', $s['slug']) !== 1) {
            $galat[] = self::galat('slug', 'slug', self::tampil($s['slug'] ?? null));
        }
        $tingkat = $s['difficulty'] ?? null;
        if (! in_array($tingkat, self::TINGKAT, true)) {
            $baku    = is_string($tingkat) ? str_replace([' ', '-'], '_', strtolower(trim($tingkat))) : '';
            $galat[] = in_array($baku, self::TINGKAT, true)
                ? self::galat('difficulty', 'difficultyTulis', $baku, self::tampil($tingkat))
                : self::galat('difficulty', 'difficulty', implode(', ', self::TINGKAT), self::tampil($tingkat));
        }
        $mode = $s['comparison_mode'] ?? 'trimmed';
        if (! in_array($mode, self::MODE, true)) {
            $galat[] = self::galat('comparison_mode', 'pilihan', 'comparison_mode', implode(', ', self::MODE), self::tampil($mode));
        }
        $bulat = static function (string $kunci, int $min, int $maks, ?int $bawaan) use ($s, &$galat): int {
            $v = $s[$kunci] ?? $bawaan;
            if (! is_int($v) || $v < $min || $v > $maks) {
                $galat[] = self::galat($kunci, 'bulat', $kunci, (string) $min, (string) $maks, self::tampil($v));

                return $min;
            }

            return $v;
        };
        $waktu       = $bulat('time_limit_ms', 100, 10000, self::WAKTU_BAWAAN[$bahasa]);
        $contoh      = $bulat('samples_count', 2, 3, null);
        $tersembunyi = $bulat('hidden_count', 10, 50, null);

        $syarat = $s['required_concepts_in_submission'] ?? [];
        if (! is_array($syarat) || ! array_is_list($syarat)) {
            $galat[] = self::galat('required_concepts_in_submission', 'daftarKonsep', self::tampil($syarat));
            $syarat  = [];
        }
        foreach ($syarat as $kode) {
            if (! is_string($kode) || ! in_array($kode, $konsepBahasa, true)) {
                $galat[] = self::galat('required_concepts_in_submission', 'konsepAsing', self::tampil($kode), $bahasa);
            }
        }

        $params = $s['params'] ?? [];
        if (! self::obyek($params)) {
            $galat[] = self::galat('params', 'params', self::tampil($params));
            $params  = [];
        }
        foreach ($params as $nama => $nilai) {
            if (preg_match('/^[a-z_][a-z0-9_]{0,39}$/', (string) $nama) !== 1 || ! (is_string($nilai) || is_int($nilai) || is_float($nilai))) {
                $galat[] = self::galat('params', 'paramBentuk', (string) $nama);
            }
        }

        [$terjemahan, $galatTeks] = self::periksaTerjemahan($s['translations'] ?? null, array_map('strval', array_keys($params)));
        $galat = array_merge($galat, $galatTeks);

        foreach (['generator', 'reference_solution'] as $kunci) {
            $v = $s[$kunci] ?? null;
            if (! is_string($v) || trim($v) === '') {
                $galat[] = self::galat($kunci, 'sumberKosong', $kunci);
            } elseif (strlen($v) > self::MAKS_SUMBER) {
                $galat[] = self::galat($kunci, 'sumberBesar', $kunci);
            }
        }

        $normal = [
            'slug'                            => $s['slug'] ?? '',
            'difficulty'                      => is_string($tingkat) ? strtoupper($tingkat) : '',
            'comparison_mode'                 => $mode,
            'time_limit_ms'                   => $waktu,
            'samples_count'                   => $contoh,
            'hidden_count'                    => $tersembunyi,
            'required_concepts_in_submission' => array_values(array_unique(array_filter($syarat, 'is_string'))),
            'params'                          => $params,
            'translations'                    => $terjemahan,
            'generator'                       => is_string($s['generator'] ?? null) ? $s['generator'] : '',
            'reference_solution'              => is_string($s['reference_solution'] ?? null) ? $s['reference_solution'] : '',
        ];

        return [$normal, $galat];
    }

    /**
     * @param list<string> $namaParam
     *
     * @return array{0: array<string, array<string, mixed>>, 1: list<array{field: string, kode: string, argumen: list<string>}>}
     */
    private static function periksaTerjemahan(mixed $t, array $namaParam): array
    {
        if (! self::obyek($t) || ! isset($t['id'])) {
            return [[], [self::galat('translations', 'terjemahanId')]];
        }
        $galat = self::kunciAsing($t, ['id', 'en', 'ar'], 'translations.');
        $hasil = [];
        foreach (['id', 'en', 'ar'] as $bahasa) {
            $isi = $t[$bahasa] ?? [];
            if (! self::obyek($isi)) {
                $galat[] = self::galat('translations', 'terjemahanBentuk', $bahasa);

                continue;
            }
            // {} untuk en atau ar berarti "belum diterjemahkan".
            if ($isi === [] && $bahasa !== 'id') {
                continue;
            }
            $galat = array_merge($galat, self::kunciAsing($isi, [...self::KUNCI_TEKS, ...($bahasa === 'id' ? [] : ['machine'])], "translations.{$bahasa}."));
            $satu  = [];
            foreach (self::KUNCI_TEKS as $kunci) {
                $v     = $isi[$kunci] ?? '';
                $wajib = in_array($kunci, ['title', 'statement'], true);
                if (! is_string($v) || ($wajib && trim($v) === '')) {
                    $galat[] = self::galat('translations', 'teksWajib', "translations.{$bahasa}.{$kunci}");
                    $v       = '';
                } elseif (strlen($v) > ($kunci === 'title' ? 190 : self::MAKS_TEKS)) {
                    $galat[] = self::galat('translations', 'teksPanjang', "translations.{$bahasa}.{$kunci}");
                }
                // {{nama}} di dalam teks harus ada di params (keputusan pemilik, tahap 8).
                if (preg_match_all('/\{\{\s*([^}]*?)\s*\}\}/', $v, $cocok) > 0) {
                    foreach (array_unique($cocok[1]) as $nama) {
                        if (! in_array($nama, $namaParam, true)) {
                            $galat[] = self::galat('translations', 'paramHilang', "translations.{$bahasa}.{$kunci}", (string) $nama);
                        }
                    }
                }
                $satu[$kunci] = $v;
            }
            if ($bahasa !== 'id') {
                $mesin = $isi['machine'] ?? false;
                if (! is_bool($mesin)) {
                    $galat[] = self::galat('translations', 'machine', "translations.{$bahasa}.machine");
                }
                $satu['machine'] = $mesin === true;
            }
            $hasil[$bahasa] = $satu;
        }

        return [$hasil, $galat];
    }

    /**
     * Sidik isi soal: sama persis berarti impor ulang tidak membuat versi baru.
     *
     * @param array<string, mixed> $normal hasil periksaSoal()
     */
    public static function sidik(array $normal, string $bahasa): string
    {
        unset($normal['slug']);
        $normal['language'] = $bahasa;
        $urut = static function (array &$a) use (&$urut): void {
            if (! array_is_list($a)) {
                ksort($a);
            }
            foreach ($a as &$v) {
                if (is_array($v)) {
                    $urut($v);
                }
            }
        };
        $urut($normal);

        return hash('sha256', json_encode($normal, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }
}
