<?php

declare(strict_types=1);

namespace App\Libraries\Soal;

/**
 * Nomor baris di dalam berkas JSON (SPEC.md §8.3: laporan impor menyebut baris
 * JSON). json_decode() tidak memberi posisi, jadi berkas dipindai sekali lagi
 * di sini — hanya untuk mencatat di baris mana tiap nilai dimulai.
 *
 * Hasil: jalur => baris, misalnya "meeting" => 3, "problems/0" => 6,
 * "problems/0/generator" => 21. Hanya tiga tingkat pertama yang dicatat.
 * Dipanggil hanya untuk teks yang sudah lolos json_decode().
 */
final class PosisiJson
{
    private int $i = 0;
    private int $baris = 1;
    private readonly int $n;

    /** @var array<string, int> */
    private array $peta = [];

    private function __construct(private readonly string $s)
    {
        $this->n = strlen($s);
    }

    /**
     * @return array<string, int>
     */
    public static function petakan(string $json): array
    {
        $p = new self($json);
        $p->lewatiSpasi();
        // Pembungkus larik berisi satu obyek (KONTRAK.md §7) tidak menambah tingkat jalur.
        if ($p->i < $p->n && $json[$p->i] === '[') {
            $p->i++;
            $p->lewatiSpasi();
        }
        $p->nilai('', 0);

        return $p->peta;
    }

    private function lewatiSpasi(): void
    {
        while ($this->i < $this->n) {
            $c = $this->s[$this->i];
            if ($c === "\n") {
                $this->baris++;
            } elseif ($c !== ' ' && $c !== "\t" && $c !== "\r") {
                return;
            }
            $this->i++;
        }
    }

    private function teks(): string
    {
        $awal = ++$this->i; // lewati tanda kutip pembuka
        while ($this->i < $this->n && $this->s[$this->i] !== '"') {
            $this->i += $this->s[$this->i] === '\\' ? 2 : 1;
        }
        $isi = substr($this->s, $awal, $this->i - $awal);
        $this->i++;

        return $isi;
    }

    private function nilai(string $jalur, int $tingkat): void
    {
        $this->lewatiSpasi();
        if ($this->i >= $this->n) {
            return;
        }
        if ($jalur !== '' && $tingkat <= 3) {
            $this->peta[$jalur] = $this->baris;
        }
        $c = $this->s[$this->i];
        if ($c === '"') {
            $this->teks();
        } elseif ($c === '{' || $c === '[') {
            $tutup = $c === '{' ? '}' : ']';
            $this->i++;
            for ($indeks = 0; ; $indeks++) {
                $this->lewatiSpasi();
                if ($this->i >= $this->n || $this->s[$this->i] === $tutup) {
                    $this->i++;

                    return;
                }
                if ($this->s[$this->i] === ',') {
                    $this->i++;
                    $indeks--;

                    continue;
                }
                $kunci = (string) $indeks;
                if ($c === '{') {
                    $kunci = $this->teks();
                    $this->lewatiSpasi();
                    $this->i++; // titik dua
                }
                $this->nilai($jalur === '' ? $kunci : $jalur . '/' . $kunci, $tingkat + 1);
            }
        } else {
            // angka, true, false, null
            while ($this->i < $this->n && ! str_contains(",}] \t\r\n", $this->s[$this->i])) {
                $this->i++;
            }
        }
    }
}
