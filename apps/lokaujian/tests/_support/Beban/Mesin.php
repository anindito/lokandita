<?php

declare(strict_types=1);

namespace Tests\Support\Beban;

/**
 * Mesin uji beban: satu proses, banyak permintaan HTTP sungguhan sekaligus (curl_multi), dan
 * pewaktu seperti window.setTimeout. Halaman ujian tiruan (Murid) dan dosen tiruan (Dosen)
 * berjalan di atasnya.
 *
 * Mesin ini juga mencatat setiap permintaan: label, kode status, lamanya. Kode 0 berarti
 * permintaan tidak mendapat jawaban (sambungan gagal atau melewati batas waktu).
 */
final class Mesin
{
    private \CurlMultiHandle $multi;

    /** @var \SplPriorityQueue<float, int> */
    private \SplPriorityQueue $antre;

    /** @var array<int, callable> pewaktu yang masih berlaku */
    private array $pewaktu = [];

    private int $idPewaktu = 0;

    /** @var array<int, array{0: callable, 1: string, 2: float, 3: array<string, list<string>>}> */
    private array $terbang = [];

    /** @var array<string, array{n: int, kode: array<int, int>, ms: list<float>}> */
    public array $catatan = [];

    /** @var list<array{0: float, 1: string, 2: int}> jawaban yang bukan 2xx/3xx: detik sejak mulai, label, kode */
    public array $galat = [];

    public readonly float $mulai;

    public function __construct(private readonly int $batasWaktuDetik = 30)
    {
        $this->multi = curl_multi_init();
        curl_multi_setopt($this->multi, CURLMOPT_MAXCONNECTS, 1024);
        $this->antre = new \SplPriorityQueue();
        $this->antre->setExtractFlags(\SplPriorityQueue::EXTR_BOTH);
        $this->mulai = microtime(true);
    }

    public function kini(): float
    {
        return microtime(true) - $this->mulai;
    }

    /** Menjalankan $fn sesudah $detik detik. Mengembalikan id untuk batal(). */
    public function nanti(float $detik, callable $fn): int
    {
        $id                 = ++$this->idPewaktu;
        $this->pewaktu[$id] = $fn;
        $this->antre->insert($id, -(microtime(true) + max(0.0, $detik)));

        return $id;
    }

    public function batal(?int $id): void
    {
        if ($id !== null) {
            unset($this->pewaktu[$id]);
        }
    }

    /**
     * Mengirim satu permintaan. $selesai(int $kode, string $isi, array $header) dipanggil ketika
     * jawabannya tiba; $kode 0 bila tidak ada jawaban.
     *
     * @param list<string> $header
     */
    public function minta(string $label, string $metode, string $url, ?string $badan, array $header, callable $selesai): void
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_CUSTOMREQUEST  => $metode,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_TIMEOUT        => $this->batasWaktuDetik,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_HTTPHEADER     => $header,
            CURLOPT_HEADERFUNCTION => function ($ch, string $baris): int {
                if (str_contains($baris, ':')) {
                    [$nama, $nilai]                                           = explode(':', $baris, 2);
                    $this->terbang[(int) $ch][3][strtolower(trim($nama))][] = trim($nilai);
                }

                return strlen($baris);
            },
        ]);
        if ($badan !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $badan);
        }
        $this->terbang[(int) $ch] = [$selesai, $label, microtime(true), []];
        curl_multi_add_handle($this->multi, $ch);
    }

    /** Berjalan sampai $berhenti() benar, atau tidak ada lagi pewaktu dan permintaan. */
    public function jalan(callable $berhenti): void
    {
        while (! $berhenti()) {
            // Pewaktu yang sudah jatuh tempo.
            while (! $this->antre->isEmpty() && -$this->antre->top()['priority'] <= microtime(true)) {
                $id = $this->antre->extract()['data'];
                if (isset($this->pewaktu[$id])) {
                    $fn = $this->pewaktu[$id];
                    unset($this->pewaktu[$id]);
                    $fn();
                }
            }
            do {
                $status = curl_multi_exec($this->multi, $aktif);
            } while ($status === CURLM_CALL_MULTI_PERFORM);
            while (($info = curl_multi_info_read($this->multi)) !== false) {
                $ch                               = $info['handle'];
                [$selesai, $label, $saat, $header] = $this->terbang[(int) $ch];
                unset($this->terbang[(int) $ch]);
                $kode = $info['result'] === CURLE_OK ? (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE) : 0;
                $isi  = $kode === 0 ? '' : (string) curl_multi_getcontent($ch);
                curl_multi_remove_handle($this->multi, $ch);
                curl_close($ch);
                $this->catat($label, $kode, (microtime(true) - $saat) * 1000);
                $selesai($kode, $isi, $header);
            }
            if ($this->terbang === [] && $this->pewaktu === []) {
                return;
            }
            $tunggu = 0.2;
            if (! $this->antre->isEmpty()) {
                $tunggu = min($tunggu, max(0.0, -$this->antre->top()['priority'] - microtime(true)));
            }
            if ($this->terbang !== []) {
                // -1: curl belum punya soket untuk ditunggu; tanpa jeda ini mesin berputar kosong.
                if (curl_multi_select($this->multi, max(0.002, $tunggu)) === -1) {
                    usleep(2000);
                }
            } else {
                usleep((int) (max(0.001, $tunggu) * 1_000_000));
            }
        }
    }

    private function catat(string $label, int $kode, float $ms): void
    {
        $c                   = &$this->catatan[$label];
        $c ??= ['n' => 0, 'kode' => [], 'ms' => []];
        $c['n']++;
        $c['kode'][$kode] = ($c['kode'][$kode] ?? 0) + 1;
        $c['ms'][]        = $ms;
        if ($kode === 0 || $kode >= 400) {
            $this->galat[] = [round($this->kini(), 1), $label, $kode];
        }
    }

    /**
     * Ringkasan per label: jumlah, kode status, dan lamanya (median, persentil 95 dan 99, terlama).
     *
     * @return array<string, array<string, mixed>>
     */
    public function ringkas(): array
    {
        $hasil = [];
        ksort($this->catatan);
        foreach ($this->catatan as $label => $c) {
            $ms = $c['ms'];
            sort($ms);
            $p  = static fn (float $q): int => (int) round($ms[(int) min(count($ms) - 1, floor($q * count($ms)))]);
            ksort($c['kode']);
            $hasil[$label] = ['jumlah' => $c['n'], 'kode' => $c['kode'], 'ms_p50' => $p(0.50), 'ms_p95' => $p(0.95), 'ms_p99' => $p(0.99), 'ms_maks' => (int) round((float) end($ms))];
        }

        return $hasil;
    }
}
