<?php

declare(strict_types=1);

namespace App\Libraries;

/**
 * Aturan isi soal, kelompok soal dan ujian (SPEC-LOKAUJIAN.md §5, KONTRAK.md §20), ditulis satu
 * kali. Form penyuntingan dan impor berkas JSON memakai fungsi yang sama, sehingga yang bisa
 * ditulis lewat form selalu bisa diekspor dan diimpor lagi, dan sebaliknya.
 *
 * Masukan berbentuk seperti di berkas (§20): kunci `type`, `text`, `points`, `answer`, `options`,
 * `image`. Jenis nilainya diperiksa ketat (teks harus string, poin harus angka, `correct` harus
 * boolean); form mengubah isiannya ke jenis itu sebelum memanggil kelas ini.
 *
 * `image` di sini hanya dilihat ADA atau TIDAK (null = tidak ada). Gambarnya sendiri diperiksa
 * oleh App\Libraries\Gambar; kelas ini meneruskan nilainya apa adanya.
 *
 * Galat dikumpulkan sebagai [jalur, kode, argumen]; kalimatnya ada di berkas bahasa
 * (Bank.galat.<kode>). Jalur ditulis seperti di JSON: items[3].options[1].text.
 */
final class Aturan
{
    public const TIPE = ['benar_salah', 'pilihan_ganda', 'isian', 'uraian'];

    public const TEKS_SOAL_MAKS     = 10_000;
    public const TEKS_KELOMPOK_MAKS = 20_000;
    public const TEKS_OPSI_MAKS     = 1_000;
    public const ALT_MAKS           = 300;
    public const OPSI_MIN           = 2;
    public const OPSI_MAKS          = 6;
    public const SOAL_KELOMPOK_MAKS = 20;
    public const SOAL_UJIAN_MAKS    = 200;
    public const JUDUL_MAKS         = 190;
    public const PETUNJUK_MAKS      = 5_000;
    public const DURASI_MIN         = 5;
    public const DURASI_MAKS        = 600;
    public const POIN_MIN           = 0.25;
    public const POIN_MAKS          = 100;

    /** @var list<array{0: string, 1: string, 2: list<int|string>}> */
    private array $galat = [];

    /**
     * @return list<array{0: string, 1: string, 2: list<int|string>}>
     */
    public function galat(): array
    {
        return $this->galat;
    }

    /**
     * @param list<int|string> $arg
     */
    public function tolak(string $jalur, string $kode, array $arg = []): void
    {
        $this->galat[] = [$jalur, $kode, $arg];
    }

    /**
     * Teks sebagaimana disimpan: akhir baris LF, tanpa spasi di awal dan akhir.
     */
    public static function rapikan(string $teks): string
    {
        return trim(str_replace(["\r\n", "\r"], "\n", $teks));
    }

    /**
     * Poin sebagai teks desimal "2.50", atau null bila bukan 0,25–100 dalam langkah 0,25.
     */
    public static function poin(mixed $nilai): ?string
    {
        if (! is_int($nilai) && ! is_float($nilai)) {
            return null;
        }
        $empat = $nilai * 4;
        if ($nilai < self::POIN_MIN || $nilai > self::POIN_MAKS || abs($empat - round($empat)) > 1e-9) {
            return null;
        }

        return number_format(round($empat) / 4, 2, '.', '');
    }

    /**
     * Kunci yang tidak dikenal ditolak di setiap tingkat: kunci yang salah eja tidak boleh
     * diam-diam diabaikan.
     *
     * @param array<mixed>  $obyek
     * @param list<string>  $dikenal
     */
    public function kunciDikenal(array $obyek, array $dikenal, string $jalur): void
    {
        foreach (array_keys($obyek) as $k) {
            if (! in_array((string) $k, $dikenal, true)) {
                $this->tolak($jalur === '' ? (string) $k : $jalur . '.' . $k, 'kunciAsing', [implode(', ', $dikenal)]);
            }
        }
    }

    /**
     * Teks wajib bertipe string. $min 0 berarti boleh kosong.
     */
    public function teks(mixed $nilai, string $jalur, int $min, int $maks): ?string
    {
        if (! is_string($nilai)) {
            $this->tolak($jalur, 'harusTeks');

            return null;
        }
        $teks    = self::rapikan($nilai);
        $panjang = mb_strlen($teks);
        if ($panjang < $min) {
            $this->tolak($jalur, 'kosong');

            return null;
        }
        if ($panjang > $maks) {
            $this->tolak($jalur, 'terlaluPanjang', [$maks, $panjang]);

            return null;
        }

        return $teks;
    }

    /**
     * Satu soal dari keempat tipe. Mengembalikan bentuk bakunya, atau null bila ada galat
     * (galatnya dicatat; pemeriksaan tetap dilanjutkan sejauh mungkin supaya semua kesalahan
     * dilaporkan sekaligus).
     *
     * @return array{type: string, text: string, points: string, answer: ?bool, image: mixed, options: list<array{text: string, correct: bool, image: mixed}>}|null
     */
    public function soal(mixed $s, string $jalur): ?array
    {
        if (! is_array($s) || array_is_list($s)) {
            $this->tolak($jalur, 'harusObyek');

            return null;
        }
        $awal = count($this->galat);
        $tipe = $s['type'] ?? null;
        if (! is_string($tipe) || ! in_array($tipe, self::TIPE, true)) {
            $this->tolak($jalur . '.type', 'tipe', [implode(', ', self::TIPE)]);

            return null;
        }
        $dikenal = ['type', 'text', 'points', 'image'];
        if ($tipe === 'benar_salah') {
            $dikenal[] = 'answer';
        }
        if ($tipe === 'pilihan_ganda') {
            $dikenal[] = 'options';
        }
        // Kunci yang bukan milik tipe ini (options pada isian, answer pada pilihan_ganda) ikut ditolak.
        $this->kunciDikenal($s, $dikenal, $jalur);

        $teks = $this->teks($s['text'] ?? null, $jalur . '.text', 1, self::TEKS_SOAL_MAKS);
        $poin = '1.00';
        if (array_key_exists('points', $s)) {
            $poin = self::poin($s['points']);
            if ($poin === null) {
                $this->tolak($jalur . '.points', 'poin');
            }
        }
        $kunci = null;
        if ($tipe === 'benar_salah') {
            if (! is_bool($s['answer'] ?? null)) {
                $this->tolak($jalur . '.answer', 'kunciBenarSalah');
            } else {
                $kunci = $s['answer'];
            }
        }
        $opsi = [];
        if ($tipe === 'pilihan_ganda') {
            $opsi = $this->opsi($s['options'] ?? null, $jalur . '.options');
        }
        if (count($this->galat) > $awal) {
            return null;
        }

        return ['type' => $tipe, 'text' => (string) $teks, 'points' => (string) $poin, 'answer' => $kunci, 'image' => $s['image'] ?? null, 'options' => $opsi];
    }

    /**
     * Opsi pilihan ganda: 2–6, tepat satu benar. Teks opsi boleh kosong bila opsi itu bergambar
     * (keputusan pemilik 2026-11-25).
     *
     * @return list<array{text: string, correct: bool, image: mixed}>
     */
    private function opsi(mixed $daftar, string $jalur): array
    {
        if (! is_array($daftar) || ! array_is_list($daftar)) {
            $this->tolak($jalur, 'harusLarik');

            return [];
        }
        if (count($daftar) < self::OPSI_MIN || count($daftar) > self::OPSI_MAKS) {
            $this->tolak($jalur, 'jumlahOpsi', [self::OPSI_MIN, self::OPSI_MAKS, count($daftar)]);
        }
        $hasil = [];
        $benar = 0;
        foreach ($daftar as $i => $o) {
            $j = "{$jalur}[{$i}]";
            if (! is_array($o) || array_is_list($o)) {
                $this->tolak($j, 'harusObyek');

                continue;
            }
            $this->kunciDikenal($o, ['text', 'correct', 'image'], $j);
            $gambar = $o['image'] ?? null;
            // Tanpa gambar, teks wajib ada. Dengan gambar, teks boleh tidak ditulis sama sekali.
            $teks = $gambar !== null && ! array_key_exists('text', $o)
                ? ''
                : $this->teks($o['text'] ?? null, $j . '.text', $gambar === null ? 1 : 0, self::TEKS_OPSI_MAKS);
            $tanda = $o['correct'] ?? false;
            if (! is_bool($tanda)) {
                $this->tolak($j . '.correct', 'harusBoolean');
                $tanda = false;
            }
            $benar  += $tanda ? 1 : 0;
            $hasil[] = ['text' => (string) $teks, 'correct' => $tanda, 'image' => $gambar];
        }
        if ($benar !== 1) {
            $this->tolak($jalur, 'satuBenar', [$benar]);
        }

        return $hasil;
    }

    /**
     * Kepala kelompok soal: teks bersama dan/atau gambar. Soal-soalnya diperiksa pemanggil
     * dengan soal(), karena form menyimpannya satu per satu dan impor sekaligus.
     *
     * @return array{text: string, image: mixed}|null
     */
    public function kelompok(mixed $k, string $jalur, bool $denganSoal): ?array
    {
        if (! is_array($k) || array_is_list($k)) {
            $this->tolak($jalur, 'harusObyek');

            return null;
        }
        $awal = count($this->galat);
        // `points` tidak boleh ada pada kelompok: poin milik tiap soalnya.
        $this->kunciDikenal($k, $denganSoal ? ['type', 'text', 'image', 'questions'] : ['type', 'text', 'image'], $jalur);
        $gambar = $k['image'] ?? null;
        $teks   = $gambar !== null && ! array_key_exists('text', $k)
            ? ''
            : $this->teks($k['text'] ?? null, $jalur . '.text', $gambar === null ? 1 : 0, self::TEKS_KELOMPOK_MAKS);

        return count($this->galat) > $awal ? null : ['text' => (string) $teks, 'image' => $gambar];
    }

    /**
     * Judul, jenis, petunjuk dan setelan ujian. Kunci yang tidak ditulis mendapat bawaannya.
     *
     * @param array<mixed> $u obyek tingkat atas berkas, atau isian form dalam bentuk yang sama
     *
     * @return array{title: string, kind: string, instructions: string, mode: string, duration_minutes: ?int, shuffle_questions: bool, shuffle_options: bool}|null
     */
    public function ujian(array $u): ?array
    {
        $awal  = count($this->galat);
        $judul = $this->teks($u['title'] ?? null, 'title', 1, self::JUDUL_MAKS);
        if (is_string($judul) && str_contains($judul, "\n")) {
            $this->tolak('title', 'satuBaris');
        }
        $jenis = $u['kind'] ?? 'ujian';
        if (! in_array($jenis, ['ujian', 'kuis'], true)) {
            $this->tolak('kind', 'jenis');
        }
        $petunjuk = array_key_exists('instructions', $u) ? $this->teks($u['instructions'], 'instructions', 0, self::PETUNJUK_MAKS) : '';

        $setelan = $u['settings'] ?? null;
        if (! is_array($setelan) || ($setelan !== [] && array_is_list($setelan))) {
            $this->tolak('settings', 'harusObyek');

            return null;
        }
        $this->kunciDikenal($setelan, ['mode', 'duration_minutes', 'shuffle_questions', 'shuffle_options'], 'settings');
        $mode = $setelan['mode'] ?? null;
        if (! in_array($mode, ['per_mahasiswa', 'dikendalikan_dosen'], true)) {
            $this->tolak('settings.mode', 'mode');
        }
        $durasi = $setelan['duration_minutes'] ?? null;
        if ($durasi !== null && (! is_int($durasi) || $durasi < self::DURASI_MIN || $durasi > self::DURASI_MAKS)) {
            $this->tolak('settings.duration_minutes', 'durasi', [self::DURASI_MIN, self::DURASI_MAKS]);
        } elseif ($durasi === null && $mode === 'per_mahasiswa') {
            $this->tolak('settings.duration_minutes', 'durasiWajib');
        }
        $acak = [];
        foreach (['shuffle_questions', 'shuffle_options'] as $k) {
            $acak[$k] = $setelan[$k] ?? false;
            if (! is_bool($acak[$k])) {
                $this->tolak('settings.' . $k, 'harusBoolean');
            }
        }
        if (count($this->galat) > $awal) {
            return null;
        }

        return [
            'title' => (string) $judul, 'kind' => (string) $jenis, 'instructions' => (string) $petunjuk, 'mode' => (string) $mode,
            'duration_minutes' => $durasi, 'shuffle_questions' => $acak['shuffle_questions'], 'shuffle_options' => $acak['shuffle_options'],
        ];
    }
}
