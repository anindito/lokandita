<?php

declare(strict_types=1);

namespace App\Libraries\Berkas;

use App\Libraries\Aturan;
use App\Libraries\Audit;
use App\Libraries\Bank;
use App\Libraries\Media;
use App\Libraries\Roster;
use App\Libraries\Ujian;
use App\Policy\Akses;

/**
 * Impor satu ujian dari berkas JSON berbentuk KONTRAK.md §20 (SPEC-LOKAUJIAN.md §6.3).
 *
 * Ketat: kunci yang tidak dikenal ditolak di setiap tingkat, jenis nilai diperiksa, dan SATU
 * kesalahan di mana pun menolak seluruh berkas — tidak ada soal, gambar atau ujian yang
 * tersimpan. Semua kesalahan yang ditemukan dilaporkan sekaligus, dengan jalur JSON dan
 * barisnya, dalam kalimat yang menyebut apa yang harus diubah.
 *
 * Berkas yang diterima menjadi SATU ujian baru berstatus draf. Soal lepas atau kelompok yang
 * isinya sama dengan yang sudah ada di bank mata kuliah itu dipakai ulang; gambar yang sama
 * dengan gambar tersimpan mata kuliah itu juga.
 */
final class Impor
{
    public const MAKS_BYTE   = 24 * 1024 * 1024;
    public const MAKS_GAMBAR = 40;
    private const MAKS_GALAT = 50;

    private Aturan $aturan;

    private ?Posisi $posisi = null;

    /** @var array<string, array{type: string, alt: string, sha256: string, biner: string}> gambar berkas yang lolos pemeriksaan bentuk */
    private array $gambar = [];

    /** @var array<string, true> nama gambar yang dipakai sebuah butir */
    private array $dipakai = [];

    public function __construct(private readonly Akses $akses)
    {
        $this->aturan = new Aturan();
    }

    /**
     * @param int|null $matkulHalaman dari web: id mata kuliah halaman tempat berkas diunggah;
     *                                `course` di berkas harus mata kuliah itu. Dari CLI: null.
     *
     * @return array{ok: true, ujian: int, matkul: int, soal: int, soal_baru: int, gambar: int, gambar_baru: int}|array{ok: false, galat: list<array{jalur: string, baris: int, pesan: string}>}
     */
    public function jalankan(string $isi, string $namaBerkas, ?int $matkulHalaman = null): array
    {
        ini_set('memory_limit', '512M');
        $namaBerkas = mb_substr((string) preg_replace('/[\x00-\x1f\/\\\\]/u', '', $namaBerkas), 0, 190);
        $matkul     = null;
        $siap       = $this->periksa($isi, $matkulHalaman, $matkul);
        if ($siap === null) {
            return $this->ditolak($namaBerkas, $matkul === null ? $matkulHalaman : (int) $matkul['id']);
        }
        [$setelan, $butir] = $siap;
        $matkulId          = (int) $matkul['id'];

        $bank   = new Bank($this->akses);
        $media  = new Media($this->akses);
        $baru   = []; // jalur berkas gambar yang ditulis impor ini; dihapus lagi bila impor batal
        $hitung = ['soal' => 0, 'soal_baru' => 0, 'gambar' => count($this->gambar), 'gambar_baru' => 0];
        try {
            $ujianId = $bank->transaksi(function () use ($bank, $media, $matkulId, $setelan, $butir, $namaBerkas, &$baru, &$hitung): ?int {
                // 1. Gambar. Yang sha256-nya sama dengan gambar tersimpan mata kuliah ini dipakai apa
                //    adanya; yang lain melewati pemeriksaan unggahan dan ditulis ulang dari pikselnya.
                $baris = [];
                foreach ($this->gambar as $nama => $g) {
                    $ada = $media->dariSha($matkulId, $g['sha256']);
                    if ($ada === null) {
                        $sementara = (string) tempnam(sys_get_temp_dir(), 'impor');
                        file_put_contents($sementara, $g['biner']);
                        $ada = $media->simpan($matkulId, $sementara, $this->akses->identitas()->userId);
                        @unlink($sementara);
                        if (is_string($ada)) {
                            $this->aturan->tolak("images.{$nama}.data", 'gambar_' . $ada);

                            continue;
                        }
                        if ($ada['baru']) {
                            $baru[] = Media::jalur($ada);
                            $hitung['gambar_baru']++;
                        }
                    }
                    $baris[$nama] = $ada;
                }
                if ($this->aturan->galat() !== []) {
                    throw new Batal();
                }
                $ikat = static fn (mixed $nama, string $alt): ?array => $nama === null ? null : ['alt' => $alt] + $baris[$nama];

                // 2. Soal dan kelompok: dipakai ulang bila isinya sudah ada di bank, selain itu ditambahkan.
                $daftar = [];
                foreach ($butir as $b) {
                    if ($b['jenis'] === 'soal') {
                        $s  = $this->ikatGambar($b['soal'], $ikat);
                        $id = $bank->soalLepasBersidik($matkulId, Bank::sidikSoal($s));
                        $hitung['soal']++;
                        if ($id === null) {
                            $id = $bank->simpanSoal($matkulId, null, $s);
                            $hitung['soal_baru']++;
                        }
                        $daftar[] = ['soal', (int) $id];

                        continue;
                    }
                    $soal   = array_map(fn (array $s): array => $this->ikatGambar($s, $ikat), $b['soal']);
                    $kepala = ['text' => $b['kelompok']['text'], 'image' => $ikat($b['kelompok']['image'], $this->alt($b['kelompok']['image']))];
                    $id     = $bank->kelompokBersidik($matkulId, Bank::sidikKelompok($kepala['text'], $kepala['image'], array_map(Bank::sidikSoal(...), $soal)));
                    $hitung['soal'] += count($soal);
                    if ($id === null) {
                        $id = (int) $bank->simpanKelompok($matkulId, null, $kepala);
                        foreach ($soal as $s) {
                            $bank->simpanSoal($matkulId, null, $s, $id);
                        }
                        $hitung['soal_baru'] += count($soal);
                    }
                    $daftar[] = ['kelompok', $id];
                }

                // 3. Ujian baru berstatus draf, dengan baris auditnya, dalam transaksi yang sama.
                $ujianId = (new Ujian($this->akses))->buat($matkulId, $setelan, $daftar, 'ujian.diimpor', [
                    'berkas' => $namaBerkas, 'soal_baru' => $hitung['soal_baru'], 'gambar' => $hitung['gambar'], 'gambar_baru' => $hitung['gambar_baru'],
                ]);
                if (is_string($ujianId)) {
                    $this->aturan->tolak('items', 'ujian_' . $ujianId);

                    throw new Batal();
                }

                return $ujianId;
            });
        } catch (\Throwable $e) {
            // Transaksi sudah dibatalkan; berkas gambar yang sempat ditulis ikut dibuang.
            foreach ($baru as $jalur) {
                @unlink($jalur);
            }
            if (! $e instanceof Batal) {
                throw $e;
            }

            return $this->ditolak($namaBerkas, $matkulId);
        }

        return ['ok' => true, 'ujian' => (int) $ujianId, 'matkul' => $matkulId] + $hitung;
    }

    /**
     * Teks alternatif gambar bernama $nama di berkas.
     */
    private function alt(mixed $nama): string
    {
        return $nama === null ? '' : $this->gambar[$nama]['alt'];
    }

    /**
     * Mengganti nama gambar di sebuah soal dengan baris gambarnya.
     *
     * @param array<string, mixed>              $s
     * @param callable(mixed, string): ?array<string, mixed> $ikat
     *
     * @return array<string, mixed>
     */
    private function ikatGambar(array $s, callable $ikat): array
    {
        $s['image'] = $ikat($s['image'], $this->alt($s['image']));
        foreach ($s['options'] as &$o) {
            $o['image'] = $ikat($o['image'], $this->alt($o['image']));
        }

        return $s;
    }

    /**
     * Menutup impor yang ditolak: galatnya menjadi kalimat dengan jalur dan baris, dan
     * penolakannya dicatat di log audit (KONTRAK.md §20).
     *
     * @return array{ok: false, galat: list<array{jalur: string, baris: int, pesan: string}>}
     */
    private function ditolak(string $namaBerkas, ?int $matkulId): array
    {
        $galat = [];
        foreach (array_slice($this->aturan->galat(), 0, self::MAKS_GALAT) as [$jalur, $kode, $arg]) {
            $galat[] = ['jalur' => $jalur, 'baris' => $this->posisi?->baris($jalur) ?? 1, 'pesan' => lang('Bank.galat.' . $kode, $arg)];
        }
        $semua = $this->aturan->galat();
        Audit::catat('ujian.impor.ditolak', 'berkas', null, [
            'berkas' => $namaBerkas, 'alasan' => $semua[0][1] ?? '', 'di' => $semua[0][0] ?? '', 'kesalahan' => count($semua),
        ], $matkulId);

        return ['ok' => false, 'galat' => $galat];
    }

    /**
     * Semua pemeriksaan yang tidak menulis apa pun. Mengisi $matkul (baris roster) bila mata
     * kuliahnya dikenali.
     *
     * @param array<string, mixed>|null $matkul
     *
     * @return array{0: array<string, mixed>, 1: list<array<string, mixed>>}|null setelan ujian dan butirnya, atau null bila ada galat
     */
    private function periksa(string $isi, ?int $matkulHalaman, ?array &$matkul): ?array
    {
        $a = $this->aturan;
        if (strlen($isi) > self::MAKS_BYTE) {
            $a->tolak('', 'berkasBesar', [24]);

            return null;
        }
        // Tanda urutan byte (BOM) di awal berkas diterima dan dibuang.
        if (str_starts_with($isi, "\xEF\xBB\xBF")) {
            $isi = substr($isi, 3);
        }
        $data = json_decode($isi, true, 16);
        if (! is_array($data) || ($data !== [] && array_is_list($data)) || $data === []) {
            $a->tolak('', 'bukanJson', [json_last_error() === JSON_ERROR_NONE ? '—' : json_last_error_msg()]);

            return null;
        }
        $this->posisi = Posisi::petakan($isi);
        foreach ($this->posisi->ganda as $jalur) {
            $a->tolak($jalur, 'kunciGanda');
        }
        $a->kunciDikenal($data, ['format', 'course', 'title', 'kind', 'instructions', 'settings', 'items', 'images'], '');
        if (($data['format'] ?? null) !== Ekspor::FORMAT) {
            $a->tolak('format', 'format', [Ekspor::FORMAT]);
        }

        // Mata kuliah: kode yang persis sama, dan pengguna ini superadmin atau pengampunya.
        $kode = $data['course'] ?? null;
        if (! is_string($kode) || $kode === '') {
            $a->tolak('course', 'harusTeks');
        } else {
            $matkul = (new Roster($this->akses))->matkulDiampuDariKode($kode);
            if ($matkul === null) {
                $a->tolak('course', 'matkul', [$kode]);
            } elseif ($matkulHalaman !== null && (int) $matkul['id'] !== $matkulHalaman) {
                $a->tolak('course', 'matkulHalaman', [$kode]);
                $matkul = null;
            }
        }

        $setelan = $a->ujian($data);
        $this->periksaGambar($data['images'] ?? []);

        $butir  = [];
        $jumlah = 0;
        $daftar = $data['items'] ?? null;
        if (! is_array($daftar) || ! array_is_list($daftar)) {
            $a->tolak('items', 'harusLarik');
        } else {
            foreach ($daftar as $i => $b) {
                $j = "items[{$i}]";
                if (is_array($b) && ($b['type'] ?? null) === 'kelompok') {
                    $kepala = $a->kelompok($b, $j, true);
                    $soal   = [];
                    $isiK   = $b['questions'] ?? null;
                    if (! is_array($isiK) || ! array_is_list($isiK) || $isiK === [] || count($isiK) > Aturan::SOAL_KELOMPOK_MAKS) {
                        $a->tolak($j . '.questions', 'jumlahSoalKelompok', [Aturan::SOAL_KELOMPOK_MAKS]);
                    } else {
                        foreach ($isiK as $n => $s) {
                            // Kelompok di dalam kelompok tertolak di sini: `kelompok` bukan salah satu dari empat tipe soal.
                            $satu = $a->soal($s, "{$j}.questions[{$n}]");
                            if ($satu !== null) {
                                $this->rujukGambar($satu, "{$j}.questions[{$n}]");
                                $soal[] = $satu;
                            }
                        }
                        $jumlah += count($isiK);
                    }
                    if ($kepala !== null) {
                        $this->rujuk($kepala['image'], $j . '.image');
                        $butir[] = ['jenis' => 'kelompok', 'kelompok' => $kepala, 'soal' => $soal];
                    }
                } else {
                    $satu = $a->soal($b, $j);
                    $jumlah++;
                    if ($satu !== null) {
                        $this->rujukGambar($satu, $j);
                        $butir[] = ['jenis' => 'soal', 'soal' => $satu];
                    }
                }
            }
            if ($jumlah < 1 || $jumlah > Aturan::SOAL_UJIAN_MAKS) {
                $a->tolak('items', 'jumlahSoal', [Aturan::SOAL_UJIAN_MAKS, $jumlah]);
            }
        }
        // Setiap gambar di berkas harus dipakai sebuah butir. Diperiksa hanya bila tidak ada galat
        // lain: soal yang ditolak tidak sempat merujuk gambarnya.
        foreach ($a->galat() === [] ? array_keys($this->gambar) : [] as $nama) {
            if (! isset($this->dipakai[$nama])) {
                $a->tolak("images.{$nama}", 'gambarTakDipakai');
            }
        }

        return $a->galat() === [] && $setelan !== null && $matkul !== null ? [$setelan, $butir] : null;
    }

    /**
     * @param array<string, mixed> $soal bentuk baku dengan `image` berupa nama gambar
     */
    private function rujukGambar(array $soal, string $jalur): void
    {
        $this->rujuk($soal['image'], $jalur . '.image');
        foreach ($soal['options'] as $i => $o) {
            $this->rujuk($o['image'], "{$jalur}.options[{$i}].image");
        }
    }

    private function rujuk(mixed $nama, string $jalur): void
    {
        if ($nama === null) {
            return;
        }
        if (! is_string($nama) || ! isset($this->gambar[$nama])) {
            // Gambar yang bentuknya sudah ditolak di `images` tidak dilaporkan dua kali.
            if (! is_string($nama) || ! isset($this->gambarDitolak[$nama])) {
                $this->aturan->tolak($jalur, 'gambarTakAda', [is_string($nama) ? $nama : '?']);
            }

            return;
        }
        $this->dipakai[$nama] = true;
    }

    /** @var array<string, true> nama gambar di berkas yang bentuknya ditolak */
    private array $gambarDitolak = [];

    /**
     * Bentuk bagian `images`: nama, jenis, teks alternatif, sha256 dan isi Base64 yang cocok
     * dengan sha256-nya. Pikselnya sendiri diperiksa saat gambar diolah (Media::simpan).
     */
    private function periksaGambar(mixed $gambar): void
    {
        $a = $this->aturan;
        if (! is_array($gambar) || ($gambar !== [] && array_is_list($gambar))) {
            $a->tolak('images', 'harusObyek');

            return;
        }
        if (count($gambar) > self::MAKS_GAMBAR) {
            $a->tolak('images', 'jumlahGambar', [self::MAKS_GAMBAR, count($gambar)]);
        }
        foreach ($gambar as $nama => $g) {
            $nama  = (string) $nama;
            $j     = "images.{$nama}";
            $awal  = count($a->galat());
            $biner = false;
            $alt   = null;
            if (preg_match('/^[a-z0-9_-]{1,40}$/', $nama) !== 1) {
                $a->tolak($j, 'namaGambar');
            }
            if (! is_array($g) || array_is_list($g)) {
                $a->tolak($j, 'harusObyek');
            } else {
                $a->kunciDikenal($g, ['type', 'alt', 'sha256', 'data'], $j);
                if (! in_array($g['type'] ?? null, ['png', 'jpeg'], true)) {
                    $a->tolak($j . '.type', 'jenisGambar');
                }
                $alt = $a->teks($g['alt'] ?? null, $j . '.alt', 1, Aturan::ALT_MAKS);
                $sha = $g['sha256'] ?? null;
                if (! is_string($sha) || preg_match('/^[0-9a-f]{64}$/', $sha) !== 1) {
                    $a->tolak($j . '.sha256', 'sha256');
                }
                $biner = is_string($g['data'] ?? null) ? base64_decode($g['data'], true) : false;
                if ($biner === false || $biner === '') {
                    $a->tolak($j . '.data', 'base64');
                } elseif (is_string($sha) && hash('sha256', $biner) !== $sha) {
                    $a->tolak($j . '.sha256', 'sha256Beda');
                } elseif (in_array($g['type'] ?? null, ['png', 'jpeg'], true)) {
                    // Jenis yang ditulis harus jenis ISI berkasnya.
                    $jenis = (new \finfo(FILEINFO_MIME_TYPE))->buffer($biner);
                    if ($jenis !== 'image/' . $g['type']) {
                        $a->tolak($j . '.type', 'jenisGambarBeda', [(string) $g['type']]);
                    }
                }
            }
            if (count($a->galat()) > $awal) {
                $this->gambarDitolak[$nama] = true;
            } else {
                $this->gambar[$nama] = ['type' => (string) $g['type'], 'alt' => (string) $alt, 'sha256' => (string) $g['sha256'], 'biner' => (string) $biner];
            }
        }
    }
}
