<?php

declare(strict_types=1);

namespace Tests\Support\Beban;

/**
 * Satu mahasiswa tiruan untuk uji beban: masuk lewat form LokaAuth, menunggu dosen, menekan
 * Mulai, lalu berperilaku seperti /js/ujian.js — bukan lebih ringan:
 *
 *   - bertanya keadaan tiap 10 detik dan mengirim denyut SEB tiap 25 detik;
 *   - pilihan disimpan seketika; teks 2 detik sesudah ketikan berhenti dan paling lama 10 detik
 *     sejak perubahan pertama yang belum tersimpan;
 *   - satu kiriman per soal pada satu saat, dengan pencacah naik satu tiap kiriman;
 *   - kiriman yang gagal (tanpa jawaban, 5xx, 429) diulang sampai diterima;
 *   - berhenti hanya karena jawaban server (409, 403, atau keadaan "tidak berjalan").
 *
 * Yang dicatat untuk pembuktian "tidak ada jawaban hilang":
 *   $terkirim[soal][n]  nilai dan tanda ragu setiap kiriman, diterima atau tidak;
 *   $diterima[soal]     pencacah kiriman terakhir yang dijawab server 200 ok.
 * Sesudah ujian, baris `answers` harus memuat pencacah >= $diterima dan PERSIS nilai kiriman
 * berpencacah itu.
 *
 * Header SEB dihitung dengan rumus KONTRAK.md §8, seperti SebUjianTest: ini bukan klien SEB.
 */
final class Murid
{
    /** @var array<string, string> cookie di host LokaAuth */
    private array $kueAuth = [];

    /** @var array<string, string> cookie di host LokaUjian */
    private array $kueApl = [];

    private string $csrf = '';

    private string $token = '';

    /** @var list<array<string, mixed>> */
    public array $soal = [];

    /** @var array<int, array<int, array{0: mixed, 1: bool}>> */
    public array $terkirim = [];

    /** @var array<int, int> */
    public array $diterima = [];

    public string $tahap = 'baru';

    public bool $berhenti = false;

    public string $alasanBerhenti = '';

    /** @var list<string> hal yang tidak boleh terjadi (jawaban 422/404, halaman tak terbaca, pencacah tak cocok saat lanjut) */
    public array $janggal = [];

    public int $ulang = 0;        // kiriman simpan yang harus diulang

    public int $simpanOk = 0;

    public int $lanjut = 0;       // berapa kali halaman dibuka ulang ("menutup peramban")

    private int $gagalBeruntun = 0;

    private int $generasi = 0;    // naik setiap halaman dibuka; pewaktu halaman lama berhenti sendiri

    private float $diamSampai = 0.0; // "sambungan putus": tidak ada permintaan sampai saat ini

    private int $kini = 0;

    /**
     * @param array<string, mixed> $akun    hasil Fixtures::user (id, nomor_induk, password)
     * @param array<string, mixed> $rencana kumpul_pada, putus_pada, lanjut_pada (detik sejak ujian mulai; null = tidak)
     */
    public function __construct(
        private readonly Mesin $mesin,
        public readonly array $akun,
        private readonly string $urlAuth,
        private readonly string $urlApl,
        private readonly string $asalApl,     // APP_URL: dasar rumus header SEB
        private readonly int $ujian,
        private readonly int $sesi,
        private readonly string $kunciKonfig,
        private readonly string $bek,
        private readonly string $ip,
        private readonly array $rencana,
    ) {
    }

    // ------------------------------------------------------------------ permintaan

    /**
     * @param array<string, string> $header
     */
    private function minta(string $label, string $host, string $metode, string $jalur, ?string $badan, array $header, callable $selesai): void
    {
        $apl   = $host === 'apl';
        $baris = ['X-Forwarded-Proto: https', 'X-Forwarded-For: ' . $this->ip, 'Accept-Encoding: identity'];
        foreach ($header as $n => $v) {
            $baris[] = "{$n}: {$v}";
        }
        if ($apl) {
            $url     = $this->asalApl . $jalur;
            $baris[] = 'X-SafeExamBrowser-ConfigKeyHash: ' . hash('sha256', $url . $this->kunciKonfig);
            $baris[] = 'X-SafeExamBrowser-RequestHash: ' . hash('sha256', $url . $this->bek);
        }
        $kue = $apl ? $this->kueApl : $this->kueAuth;
        if ($kue !== []) {
            $pasang = [];
            foreach ($kue as $n => $v) {
                $pasang[] = "{$n}={$v}";
            }
            $baris[] = 'Cookie: ' . implode('; ', $pasang);
        }
        $this->mesin->minta($label, $metode, ($apl ? $this->urlApl : $this->urlAuth) . $jalur, $badan, $baris, function (int $kode, string $isi, array $h) use ($apl, $selesai): void {
            foreach ($h['set-cookie'] ?? [] as $baris) {
                [$n, $v] = array_pad(explode('=', trim(explode(';', $baris)[0]), 2), 2, '');
                $hapus   = $v === '' || $v === 'deleted' || preg_match('/max-age=(0|-\d+)\b/i', $baris) === 1;
                if ($apl) {
                    if ($hapus) { unset($this->kueApl[$n]); } else { $this->kueApl[$n] = $v; }
                } else {
                    if ($hapus) { unset($this->kueAuth[$n]); } else { $this->kueAuth[$n] = $v; }
                }
            }
            $selesai($kode, $isi, $h);
        });
    }

    /**
     * @param array<string, string> $isian
     */
    private function form(string $label, string $host, string $jalur, array $isian, callable $selesai): void
    {
        $this->minta($label, $host, 'POST', $jalur, http_build_query($isian), ['Content-Type' => 'application/x-www-form-urlencoded'], $selesai);
    }

    private function janggal(string $apa): void
    {
        $this->janggal[] = sprintf('%.0f dtk %s: %s', $this->mesin->kini(), $this->tahap, $apa);
    }

    /** Mengulang langkah sebelum ujian (masuk, halaman mulai) sesudah jeda: seperti orang menekan lagi. */
    private function cobaLagi(callable $langkah, string $apa, int $kode): void
    {
        if ($kode !== 0 && $kode < 500 && $kode !== 429) {
            $this->janggal("{$apa} dijawab {$kode}");
        }
        $this->mesin->nanti(3 + mt_rand(0, 3000) / 1000, $langkah);
    }

    // ------------------------------------------------------------------ masuk dan menunggu

    public function masuk(): void
    {
        $this->tahap = 'masuk';
        $this->minta('auth GET /login', 'auth', 'GET', '/login', null, [], function (int $kode, string $isi): void {
            if ($kode !== 200 || preg_match('/name="csrf" value="([^"]+)"/', $isi, $m) !== 1) {
                $this->cobaLagi($this->masuk(...), 'GET /login', $kode);

                return;
            }
            $this->form('auth POST /login', 'auth', '/login', ['csrf' => $m[1], 'nomor_induk' => (string) $this->akun['nomor_induk'], 'password' => (string) $this->akun['password']], function (int $kode): void {
                if (! in_array($kode, [302, 303], true) || ! isset($this->kueAuth['lokandita_sid'])) {
                    $this->cobaLagi($this->masuk(...), 'POST /login', $kode);

                    return;
                }
                // Cookie sesi berlaku untuk .lokandita.com: peramban membawanya ke LokaUjian.
                $this->kueApl['lokandita_sid'] = $this->kueAuth['lokandita_sid'];
                $this->beranda();
            });
        });
    }

    private function beranda(): void
    {
        $this->tahap = 'beranda';
        $this->minta('GET /beranda', 'apl', 'GET', '/beranda', null, [], function (int $kode, string $isi): void {
            if ($kode !== 200 || preg_match('/data-csrf="([^"]+)"/', $isi, $m) !== 1) {
                $this->cobaLagi($this->beranda(...), 'GET /beranda', $kode);

                return;
            }
            $this->csrf = $m[1];
            // Alamat mulai berkas .seb ujian itu (KONTRAK.md §23).
            $this->minta('GET /ujian/N/masuk', 'apl', 'GET', "/ujian/{$this->ujian}/masuk", null, [], function (int $kode, string $isi, array $h): void {
                if (! in_array($kode, [302, 303], true) || ! str_ends_with((string) ($h['location'][0] ?? ''), "/ujian-saya/{$this->sesi}")) {
                    $this->cobaLagi($this->beranda(...), 'GET /ujian/N/masuk', $kode);

                    return;
                }
                $this->halamanMulai();
            });
        });
    }

    private function halamanMulai(): void
    {
        $this->tahap = 'halaman mulai';
        $this->minta('GET /ujian-saya/S', 'apl', 'GET', "/ujian-saya/{$this->sesi}", null, [], function (int $kode, string $isi): void {
            if ($kode !== 200 || ! str_contains($isi, 'data-mulai-form')) {
                $this->cobaLagi($this->halamanMulai(...), 'GET /ujian-saya/S', $kode);

                return;
            }
            if (preg_match('/data-csrf="([^"]+)"/', $isi, $m) === 1) {
                $this->csrf = $m[1];
            }
            $this->denyut($this->generasi);
            if (str_contains($isi, 'data-menerima="1"')) {
                $this->mulai();
            } else {
                $this->tahap = 'menunggu dosen';
                $this->mesin->nanti(5, $this->tanyaTunggu(...));   // tunggu.js
            }
        });
    }

    private function tanyaTunggu(): void
    {
        $this->minta('GET keadaan (menunggu)', 'apl', 'GET', "/api/sesi/{$this->sesi}/keadaan", null, ['Accept' => 'application/json'], function (int $kode, string $isi): void {
            $k = $kode === 200 ? json_decode($isi, true) : null;
            if (is_array($k) && ($k['menerima'] ?? false) === true) {
                $this->mulai();

                return;
            }
            $this->mesin->nanti(5, $this->tanyaTunggu(...));
        });
    }

    private function mulai(): void
    {
        $this->tahap = 'mulai';
        $this->form('POST /ujian-saya/S/mulai', 'apl', "/ujian-saya/{$this->sesi}/mulai", ['csrf' => $this->csrf], function (int $kode, string $isi, array $h): void {
            if (! in_array($kode, [302, 303], true) || ! str_ends_with((string) ($h['location'][0] ?? ''), '/kerjakan')) {
                $this->cobaLagi($this->mulai(...), 'POST mulai', $kode);

                return;
            }
            $this->bukaHalaman(false);
        });
    }

    // ------------------------------------------------------------------ halaman ujian

    private function bukaHalaman(bool $lanjut): void
    {
        $this->tahap = $lanjut ? 'membuka ulang halaman' : 'membuka halaman';
        $this->minta('GET /ujian-saya/S/kerjakan', 'apl', 'GET', "/ujian-saya/{$this->sesi}/kerjakan", null, [], function (int $kode, string $isi, array $h) use ($lanjut): void {
            if (in_array($kode, [302, 303], true)) {
                // Pengerjaan sudah berakhir: server mengalihkan ke tanda terima.
                $this->henti('dialihkan ke tanda terima');

                return;
            }
            if ($kode !== 200 || preg_match('/data-halaman="([0-9a-f]{32})"/', $isi, $m) !== 1) {
                $this->cobaLagi(fn () => $this->bukaHalaman($lanjut), 'GET kerjakan', $kode);

                return;
            }
            $this->token = $m[1];
            if (preg_match('/data-csrf="([^"]+)"/', $isi, $c) === 1) {
                $this->csrf = $c[1];
            }
            $baru = self::bacaSoal($isi);
            if ($baru === []) {
                $this->janggal('halaman ujian tanpa soal');
            }
            if ($lanjut) {
                // Lanjut sesudah "menutup peramban": pencacah yang ditulis server di halaman harus
                // sekurangnya yang terakhir diterimanya — kalau kurang, sebuah jawaban hilang.
                foreach ($baru as $i => $b) {
                    if ($b['n'] < ($this->diterima[$b['id']] ?? 0)) {
                        $this->janggal("lanjut: soal {$b['id']} berpencacah {$b['n']} di halaman, {$this->diterima[$b['id']]} sudah diterima");
                    }
                    // Nilai di halaman tiruan tetap yang terakhir "diketik": yang belum terkirim hilang
                    // bersama halaman lama, seperti di peramban; di sini dikirim lagi dari ingatan murid.
                    $baru[$i]['nilai'] = $this->soal[$i]['nilai'] ?? null;
                    $baru[$i]['ragu']  = $this->soal[$i]['ragu'] ?? false;
                }
                $this->lanjut++;
            }
            $this->soal = $baru;
            $this->generasi++;
            $gen = $this->generasi;
            foreach (array_unique(self::cari('#/gambar/[0-9a-f]{32}#', $isi)) as $g) {
                $this->minta('GET /gambar/K', 'apl', 'GET', $g, null, [], static function (): void {});
            }
            $this->tahap = 'mengerjakan';
            $this->mesin->nanti(10, fn () => $this->tanyaKeadaan($gen));
            $this->denyut($gen);
            if (! $lanjut) {
                $this->kini = 0;
                $this->rencanakan();
            }
            $this->mesin->nanti(mt_rand(3000, 12000) / 1000, fn () => $this->kerja($gen));
        });
    }

    /**
     * @return list<string>
     */
    private static function cari(string $pola, string $isi): array
    {
        preg_match_all($pola, $isi, $m);

        return $m[0];
    }

    /**
     * Soal-soal halaman ujian, dari atribut data-* yang juga dibaca ujian.js.
     *
     * @return list<array<string, mixed>>
     */
    private static function bacaSoal(string $isi): array
    {
        $soal   = [];
        $bagian = preg_split('/<section class="ujian-soal"/', $isi) ?: [];
        array_shift($bagian);
        foreach ($bagian as $b) {
            if (preg_match('/data-id="(\d+)" data-tipe="([a-z_]+)"/', $b, $m) !== 1 || preg_match('/data-n="(\d+)"/', $b, $n) !== 1) {
                continue;
            }
            preg_match_all('/type="radio" name="[^"]*" value="(\d+)"/', $b, $o);
            $soal[] = [
                'id' => (int) $m[1], 'tipe' => $m[2], 'n' => (int) $n[1], 'opsi' => array_map('intval', $o[1]),
                'nilai' => in_array($m[2], ['isian', 'uraian'], true) ? '' : null, 'ragu' => false,
                'kotor' => false, 'terbang' => false, 'tunda' => null, 'paling_lama' => null, 'target' => null,
            ];
        }

        return $soal;
    }

    /** Peristiwa yang direncanakan untuk murid ini: mengumpulkan sendiri, sambungan putus, menutup peramban. */
    private function rencanakan(): void
    {
        if (($this->rencana['putus_pada'] ?? null) !== null) {
            $this->mesin->nanti((float) $this->rencana['putus_pada'], function (): void {
                $this->diamSampai = $this->mesin->kini() + 75;   // lebih dari 60 detik: "reconnected"
                $this->mesin->nanti(76, fn () => $this->peristiwa(['jenis' => 'reconnected', 'detik' => 75]));
            });
        }
        if (($this->rencana['lanjut_pada'] ?? null) !== null) {
            $this->mesin->nanti((float) $this->rencana['lanjut_pada'], function (): void {
                if (! $this->berhenti) {
                    $this->generasi++;                        // halaman lama mati bersama perambannya
                    foreach ($this->soal as $i => $s) {
                        $this->mesin->batal($s['tunda']);
                        $this->mesin->batal($s['paling_lama']);
                        $this->soal[$i]['terbang'] = false;
                    }
                    $this->mesin->nanti(8, fn () => $this->bukaHalaman(true));
                }
            });
        }
        if (($this->rencana['kumpul_pada'] ?? null) !== null) {
            $this->mesin->nanti((float) $this->rencana['kumpul_pada'], $this->kumpulkan(...));
        }
    }

    /**
     * Peristiwa integritas yang dilaporkan halaman (KONTRAK.md §23).
     *
     * @param array<string, mixed> $badan
     */
    private function peristiwa(array $badan): void
    {
        if ($this->berhenti || $this->putus()) {
            return;
        }
        $this->minta('POST peristiwa', 'apl', 'POST', "/api/sesi/{$this->sesi}/peristiwa", json_encode($badan, JSON_THROW_ON_ERROR), ['Content-Type' => 'application/json', 'X-CSRF-TOKEN' => $this->csrf, 'Accept' => 'application/json'], function (int $kode, string $isi): void {
            if ($kode >= 400 && $kode < 500 && $kode !== 429 && $kode !== 409 && $kode !== 403) {
                $this->janggal("peristiwa dijawab {$kode}: " . substr($isi, 0, 120));
            }
        });
    }

    private function putus(): bool
    {
        return $this->mesin->kini() < $this->diamSampai;
    }

    private function henti(string $alasan): void
    {
        if (! $this->berhenti) {
            $this->berhenti       = true;
            $this->alasanBerhenti = $alasan;
            $this->tahap          = 'berhenti';
            $this->generasi++;
        }
    }

    // ------------------------------------------------------------------ tiap 10 dan 25 detik

    private function tanyaKeadaan(int $gen): void
    {
        if ($this->berhenti || $gen !== $this->generasi) {
            return;
        }
        $this->mesin->nanti(10, fn () => $this->tanyaKeadaan($gen));
        if ($this->putus()) {
            return;
        }
        $this->minta('GET keadaan', 'apl', 'GET', "/api/sesi/{$this->sesi}/keadaan?halaman={$this->token}", null, ['Accept' => 'application/json'], function (int $kode, string $isi) use ($gen): void {
            if ($this->berhenti || $gen !== $this->generasi) {
                return;
            }
            if ($kode === 403) {
                $this->henti('keadaan 403: ' . substr($isi, 0, 80));

                return;
            }
            $p = $kode === 200 ? (json_decode($isi, true)['pengerjaan'] ?? null) : null;
            if (! is_array($p)) {
                return;
            }
            if ($p['diambil_alih'] ?? false) {
                $this->henti('diambil_alih');
            } elseif ($p['dikumpulkan'] ?? false) {
                $this->henti('dikumpulkan');
            } elseif (! ($p['berjalan'] ?? true) && ! $this->adaTerbang()) {
                $this->henti('waktu_habis');
            }
        });
    }

    private function denyut(int $gen): void
    {
        $this->mesin->nanti(25, function () use ($gen): void {
            if ($this->berhenti || $gen !== $this->generasi) {
                return;
            }
            $this->denyut($gen);
            if (! $this->putus()) {
                $this->form('POST /api/seb/denyut', 'apl', '/api/seb/denyut', ['csrf' => $this->csrf], static function (): void {});
            }
        });
    }

    private function adaTerbang(): bool
    {
        foreach ($this->soal as $s) {
            if ($s['terbang']) {
                return true;
            }
        }

        return false;
    }

    // ------------------------------------------------------------------ simpan (ujian.js: ubah, kirim, jadwalUlang)

    private function ubah(int $i, bool $seketika): void
    {
        if ($this->berhenti) {
            return;
        }
        $gen                       = $this->generasi;
        $this->soal[$i]['kotor'] = true;
        if ($seketika) {
            $this->kirim($i, $gen);

            return;
        }
        $this->mesin->batal($this->soal[$i]['tunda']);
        $this->soal[$i]['tunda'] = $this->mesin->nanti(2, fn () => $this->kirim($i, $gen));
        $this->soal[$i]['paling_lama'] ??= $this->mesin->nanti(10, fn () => $this->kirim($i, $gen));
    }

    private function kirim(int $i, int $gen): void
    {
        if ($this->berhenti || $gen !== $this->generasi) {
            return;
        }
        $s = &$this->soal[$i];
        $this->mesin->batal($s['tunda']);
        $this->mesin->batal($s['paling_lama']);
        $s['tunda'] = $s['paling_lama'] = null;
        if ($s['terbang']) {
            return;
        }
        if ($this->putus()) {
            // Tanpa sambungan: fetch gagal seketika, jawaban tetap di halaman dan dicoba lagi.
            $s['kotor'] = true;
            $this->gagalBeruntun++;
            $this->jadwalUlang($i, $gen);

            return;
        }
        $s['terbang'] = true;
        $s['kotor']   = false;
        $s['n']++;
        $n                              = $s['n'];
        $id                             = $s['id'];
        $this->terkirim[$id][$n]        = [$s['nilai'], $s['ragu']];
        $badan                          = json_encode(['halaman' => $this->token, 'soal' => $id, 'nilai' => $s['nilai'], 'ragu' => $s['ragu'], 'n' => $n], JSON_THROW_ON_ERROR);
        unset($s);
        $this->minta('POST jawaban', 'apl', 'POST', "/api/sesi/{$this->sesi}/jawaban", $badan, ['Content-Type' => 'application/json', 'X-CSRF-TOKEN' => $this->csrf, 'Accept' => 'application/json'], function (int $kode, string $isi) use ($i, $gen, $n, $id): void {
            $b = json_decode($isi, true);
            if ($kode === 200 && is_array($b) && ($b['ok'] ?? false) === true) {
                // Diterima: server sudah meng-commit-nya (KONTRAK.md §22). Dicatat walau halaman ini
                // sudah diganti — janji server tidak bergantung pada siapa yang mendengarnya.
                $this->diterima[$id] = max($this->diterima[$id] ?? 0, $n);
                $this->simpanOk++;
                if ((int) ($b['n'] ?? 0) !== $n) {
                    $this->janggal("simpan soal {$id}: dikirim n={$n}, server menjawab n=" . ($b['n'] ?? '?'));
                }
            }
            if ($this->berhenti || $gen !== $this->generasi) {
                return;
            }
            $s            = &$this->soal[$i];
            $s['terbang'] = false;
            if ($kode === 200 && is_array($b) && ($b['ok'] ?? false) === true) {
                $this->gagalBeruntun = 0;
                if ($s['kotor']) {
                    $this->kirim($i, $gen);
                }
            } elseif ($kode === 409 || $kode === 403) {
                $s['kotor'] = false;
                $this->henti((string) ($b['error'] ?? "simpan {$kode}"));
            } elseif ($kode === 422 || $kode === 404) {
                $this->janggal("simpan soal {$id} dijawab {$kode}: " . substr($isi, 0, 120));
                $this->gagalBeruntun = 0;
            } else {
                $s['kotor'] = true;
                $this->gagalBeruntun++;
                $this->ulang++;
                $this->jadwalUlang($i, $gen);
            }
        });
    }

    private function jadwalUlang(int $i, int $gen): void
    {
        $jeda = min(10.0, 1.5 * 2 ** min($this->gagalBeruntun, 3));
        $this->mesin->nanti($jeda, function () use ($i, $gen): void {
            if ($gen === $this->generasi && $this->soal[$i]['kotor'] && ! $this->soal[$i]['terbang']) {
                $this->kirim($i, $gen);
            }
        });
    }

    // ------------------------------------------------------------------ murid mengerjakan

    private const KATA = ['algoritma', 'rekursi', 'variabel', 'larik', 'fungsi', 'kelas', 'objek', 'pewarisan', 'antrean', 'tumpukan',
        'memanggil', 'dirinya', 'sendiri', 'sampai', 'kasus', 'dasar', 'tercapai', 'karena', 'itu', 'hasilnya', 'مرحبا', 'اختبار', 'contohnya', 'yaitu'];

    /** Satu langkah murid pada soal yang sedang dibukanya; lalu menjadwalkan langkah berikutnya. */
    private function kerja(int $gen): void
    {
        if ($this->berhenti || $gen !== $this->generasi || $this->soal === []) {
            return;
        }
        if (mt_rand(1, 400) === 1) {
            // Sesekali halaman kehilangan fokus sebentar (di SEB: dialog sistem).
            $this->peristiwa(['jenis' => 'focus_lost', 'durasi' => mt_rand(1, 20)]);
        }
        $i    = $this->kini;
        $s    = &$this->soal[$i];
        $jeda = 1.0;
        if ($s['tipe'] === 'benar_salah' || $s['tipe'] === 'pilihan_ganda') {
            $s['nilai'] = $s['tipe'] === 'benar_salah' ? (mt_rand(0, 1) === 1 ? 'benar' : 'salah') : $s['opsi'][array_rand($s['opsi'])];
            if (mt_rand(1, 100) <= 6) {
                $s['ragu'] = ! $s['ragu'];
            }
            unset($s);
            $this->ubah($i, true);
            $this->pindah();
            $jeda = mt_rand(8000, 40000) / 1000;    // membaca soal berikutnya
        } else {
            // Mengetik: sekitar empat karakter per detik, satu peristiwa "input" per detik.
            $s['target'] ??= mb_strlen((string) $s['nilai']) + ($s['tipe'] === 'isian' ? mt_rand(8, 40) : mt_rand(250, 900));
            $tambah       = self::KATA[array_rand(self::KATA)] . (mt_rand(1, 14) === 1 && $s['tipe'] === 'uraian' ? "\n" : ' ');
            $maks         = $s['tipe'] === 'isian' ? 200 : 20000;
            $s['nilai']   = mb_substr($s['nilai'] . $tambah, 0, $maks);
            $selesai      = mb_strlen($s['nilai']) >= min($maks, $s['target']);
            if ($selesai) {
                $s['target'] = null;
            }
            unset($s);
            $this->ubah($i, false);
            if ($selesai) {
                // Pindah soal: yang sedang diketik disimpan dulu (tampil() di ujian.js).
                if ($this->soal[$i]['kotor']) {
                    $this->kirim($i, $gen);
                }
                $this->pindah();
                $jeda = mt_rand(8000, 30000) / 1000;
            } else {
                $jeda = mt_rand(1200, 2200) / 1000;
            }
        }
        $this->mesin->nanti($jeda, fn () => $this->kerja($gen));
    }

    /** Berurutan sampai soal terakhir, sesudah itu kembali ke soal acak (memeriksa ulang, menambah uraian). */
    private function pindah(): void
    {
        $this->kini = $this->kini + 1 < count($this->soal) && mt_rand(1, 10) > 1 ? $this->kini + 1 : mt_rand(0, count($this->soal) - 1);
    }

    private function kumpulkan(): void
    {
        if ($this->berhenti) {
            return;
        }
        $gen = $this->generasi;
        foreach ($this->soal as $i => $s) {
            if ($s['kotor']) {
                $this->kirim($i, $gen);
            }
        }
        foreach ($this->soal as $s) {
            if ($s['kotor'] || $s['terbang']) {
                // Halaman tidak mengumpulkan selama masih ada jawaban yang belum diterima server.
                $this->mesin->nanti(2, $this->kumpulkan(...));

                return;
            }
        }
        $this->generasi++;   // murid berhenti mengetik
        $this->form('POST /ujian-saya/S/kumpulkan', 'apl', "/ujian-saya/{$this->sesi}/kumpulkan", ['csrf' => $this->csrf], function (int $kode): void {
            if (! in_array($kode, [302, 303], true)) {
                $this->janggal("kumpulkan dijawab {$kode}");
            }
            $this->henti('mengumpulkan sendiri');
            $this->minta('GET /ujian-saya/S (tanda terima)', 'apl', 'GET', "/ujian-saya/{$this->sesi}", null, [], static function (): void {});
        });
    }
}
