<?php

declare(strict_types=1);

namespace App\Libraries\Berkas;

use App\Libraries\Media;

/**
 * Menulis satu ujian sebagai berkas JSON berbentuk KONTRAK.md §20.
 *
 * Hasilnya fungsi murni dari isi ujian: urutan kunci dan tata letaknya tetap (indentasi dua
 * spasi, satu baris baru di akhir), gambar diberi nama g1, g2, … menurut urutan pertama kali
 * dipakai, dan yang ditulis adalah berkas gambar yang TERSIMPAN. Karena itu mengekspor,
 * mengimpor, lalu mengekspor lagi menghasilkan berkas yang sama byte demi byte.
 *
 * Berkas ini memuat SEMUA kunci jawaban. Tidak pernah memuat kelas, jadwal, mahasiswa, jawaban
 * atau id basis data.
 */
final class Ekspor
{
    public const FORMAT = 'lokaujian-ujian/1';

    /** @var array<string, string> "id gambar|alt" => nama di berkas */
    private array $nama = [];

    /** @var array<string, array<string, string>> */
    private array $gambar = [];

    /**
     * @param array<string, mixed> $ujian hasil App\Libraries\Ujian::lihat()
     */
    public static function berkas(array $ujian, string $kodeMatkul): string
    {
        $e     = new self();
        $butir = [];
        foreach ($ujian['butir'] as $b) {
            if ($b['jenis'] === 'soal') {
                $butir[] = $e->soal($b['soal']);

                continue;
            }
            $k    = $b['kelompok'];
            $satu = ['type' => 'kelompok', 'text' => $k['text']];
            // Gambar kelompok dinamai sebelum gambar soal-soalnya: urutan pertama kali dipakai.
            $g                 = $e->gambar($k['image']);
            $satu['questions'] = array_map($e->soal(...), $k['questions']);
            if ($g !== null) {
                $satu['image'] = $g;
            }
            $butir[] = $satu;
        }
        $data = [
            'format'       => self::FORMAT,
            'course'       => $kodeMatkul,
            'title'        => (string) $ujian['title'],
            'kind'         => (string) $ujian['kind'],
            'instructions' => (string) $ujian['instructions'],
            'settings'     => [
                'mode'              => (string) $ujian['mode'],
                'duration_minutes'  => $ujian['duration_minutes'] === null ? null : (int) $ujian['duration_minutes'],
                'shuffle_questions' => (bool) $ujian['shuffle_questions'],
                'shuffle_options'   => (bool) $ujian['shuffle_options'],
            ],
            'items'  => $butir,
            'images' => $e->gambar === [] ? new \stdClass() : $e->gambar,
        ];
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);

        // PHP mengindentasi dengan empat spasi; kontrak menetapkan dua. Di dalam JSON, baris baru
        // hanya ada di antara nilai (di dalam teks ia tertulis \n), jadi spasi di awal baris selalu indentasi.
        return preg_replace_callback('/^ +/m', static fn (array $m): string => str_repeat(' ', intdiv(strlen($m[0]), 2)), $json) . "\n";
    }

    /**
     * @param array<string, mixed> $s
     *
     * @return array<string, mixed>
     */
    private function soal(array $s): array
    {
        $poin = (float) $s['points'];
        $satu = ['type' => $s['type'], 'text' => $s['text'], 'points' => floor($poin) === $poin ? (int) $poin : $poin];
        if ($s['type'] === 'benar_salah') {
            $satu['answer'] = (bool) $s['answer'];
        }
        $g = $this->gambar($s['image']);
        if ($s['type'] === 'pilihan_ganda') {
            $satu['options'] = [];
            foreach ($s['options'] as $o) {
                $opsi = ['text' => $o['text']];
                if ($o['correct']) {
                    $opsi['correct'] = true;
                }
                $og = $this->gambar($o['image']);
                if ($og !== null) {
                    $opsi['image'] = $og;
                }
                $satu['options'][] = $opsi;
            }
        }
        if ($g !== null) {
            $satu['image'] = $g;
        }

        return $satu;
    }

    /**
     * Nama gambar di berkas. Satu berkas gambar dengan dua teks alternatif berbeda menjadi dua
     * entri: teks alternatif adalah milik pemakaiannya.
     *
     * @param array<string, mixed>|null $g
     */
    private function gambar(?array $g): ?string
    {
        if ($g === null) {
            return null;
        }
        $kunci = $g['id'] . '|' . $g['alt'];
        if (! isset($this->nama[$kunci])) {
            $isi = @file_get_contents(Media::jalur($g));
            if ($isi === false || hash('sha256', $isi) !== $g['sha256']) {
                throw new \RuntimeException('gambar.berkas_hilang: berkas gambar ' . $g['kunci'] . ' tidak ada di volume atau isinya berubah');
            }
            $nama                = 'g' . (count($this->nama) + 1);
            $this->nama[$kunci]  = $nama;
            $this->gambar[$nama] = ['type' => (string) $g['jenis'], 'alt' => (string) $g['alt'], 'sha256' => (string) $g['sha256'], 'data' => base64_encode($isi)];
        }

        return $this->nama[$kunci];
    }
}
