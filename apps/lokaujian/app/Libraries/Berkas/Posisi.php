<?php

declare(strict_types=1);

namespace App\Libraries\Berkas;

/**
 * Nomor baris di dalam berkas JSON ujian (KONTRAK.md §20: pesan impor menyebut jalur dan baris).
 * json_decode() tidak memberi posisi dan diam-diam memakai nilai terakhir bila sebuah kunci
 * ditulis dua kali, jadi berkas dipindai sekali lagi di sini — untuk mencatat di baris mana tiap
 * nilai dimulai, dan kunci mana yang ganda.
 *
 * Jalur ditulis seperti di pesan: "title", "items[3].options[1].text", "images.g1.alt".
 * Dipanggil hanya untuk teks yang sudah lolos json_decode().
 */
final class Posisi
{
    private int $i = 0;

    private int $baris = 1;

    private readonly int $n;

    /** @var array<string, int> jalur => baris */
    public array $peta = [];

    /** @var list<string> jalur kunci yang ditulis lebih dari sekali dalam obyek yang sama */
    public array $ganda = [];

    private function __construct(private readonly string $s)
    {
        $this->n = strlen($s);
    }

    public static function petakan(string $json): self
    {
        $p = new self($json);
        $p->nilai('');

        return $p;
    }

    /**
     * Baris sebuah jalur; bila jalur itu tidak ada di berkas (kunci yang seharusnya ada tetapi
     * tidak ditulis), baris induknya yang terdekat.
     */
    public function baris(string $jalur): int
    {
        while ($jalur !== '') {
            if (isset($this->peta[$jalur])) {
                return $this->peta[$jalur];
            }
            $potong = max((int) strrpos($jalur, '.'), (int) strrpos($jalur, '['));
            $jalur  = substr($jalur, 0, $potong);
        }

        return 1;
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

    private function nilai(string $jalur): void
    {
        $this->lewatiSpasi();
        if ($this->i >= $this->n) {
            return;
        }
        if ($jalur !== '') {
            $this->peta[$jalur] = $this->baris;
        }
        $c = $this->s[$this->i];
        if ($c === '"') {
            $this->teks();
        } elseif ($c === '{' || $c === '[') {
            $tutup = $c === '{' ? '}' : ']';
            $ada   = [];
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
                if ($c === '{') {
                    // Nama kunci di berkas yang sah hanya huruf, angka, garis bawah dan tanda hubung;
                    // kunci lain akan ditolak sebagai kunci asing, jadi escape tidak perlu diurai.
                    $kunci = $this->teks();
                    $anak  = $jalur === '' ? $kunci : $jalur . '.' . $kunci;
                    if (isset($ada[$kunci])) {
                        $this->ganda[] = $anak;
                    }
                    $ada[$kunci] = true;
                    $this->lewatiSpasi();
                    $this->i++; // titik dua
                } else {
                    $anak = "{$jalur}[{$indeks}]";
                }
                $this->nilai($anak);
            }
        } else {
            // angka, true, false, null
            while ($this->i < $this->n && ! str_contains(",}] \t\r\n", $this->s[$this->i])) {
                $this->i++;
            }
        }
    }
}
