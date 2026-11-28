<?php

declare(strict_types=1);

namespace App\Libraries;

use CodeIgniter\Database\BaseConnection;
use Lokandita\Auth\UserDirectory;

/**
 * Data awal kurikulum (SPEC.md §17): 16 pertemuan dengan konsepnya untuk tiap mata kuliah di
 * `app/Data/benih.json`.
 *
 * Mata kuliah dan kelas adalah baris roster (KONTRAK.md §18): sejak tahap A2 LokaKode hanya
 * membacanya. Yang belum ada dilaporkan (KURANG) supaya dibuat di Akun Lokandita; perintah ini
 * tidak membuatnya.
 *
 * Untuk pertemuan dan konsep aturannya satu: yang BELUM ADA dibuat; yang SUDAH ADA dan berbeda hanya
 * dilaporkan (BEDA) dan tidak disentuh. Dua pengecualian, keduanya tidak bisa
 * merusak apa pun yang sudah dipakai:
 *   - judul pertemuan disamakan dengan benih (keputusan pemilik, 2026-11-19);
 *   - pertemuan atau mata kuliah yang konsepnya masih KOSONG diisi dari benih.
 *
 * Karena itu perintahnya aman dijalankan berulang: kali kedua tidak mengubah apa-apa.
 */
final class BenihKurikulum
{
    /** @var list<string> baris laporan, selain yang "sama" */
    private array $baris = [];

    /** @var array{dibuat: int, diubah: int, beda: int, sama: int} */
    private array $hitung = ['dibuat' => 0, 'diubah' => 0, 'beda' => 0, 'sama' => 0];

    private BaseConnection $db;

    private function __construct(private readonly string $pelakuId, private readonly bool $tulis)
    {
        $this->db = db_connect();
    }

    /**
     * @return array<string, mixed>
     */
    public static function berkas(): array
    {
        return json_decode((string) file_get_contents(APPPATH . 'Data/benih.json'), true, 16, JSON_THROW_ON_ERROR);
    }

    /**
     * @param array<string, mixed> $benih
     *
     * @return array{baris: list<string>, hitung: array{dibuat: int, diubah: int, beda: int, sama: int}}
     *
     * @throws \RuntimeException bila benih tidak bisa diterapkan sama sekali (tidak ada yang ditulis)
     */
    public static function terapkan(array $benih, string $pelakuId, bool $tulis): array
    {
        $ini = new self($pelakuId, $tulis);
        $ini->periksaBenih($benih);

        $nomor    = (string) $benih['pengampu'];
        $pengampu = UserDirectory::byNomorInduk([$nomor])[$nomor] ?? null;
        if ($pengampu === null || $pengampu['global_role'] !== 'dosen' || $pengampu['status'] !== 'active') {
            throw new \RuntimeException("pengampu {$nomor} bukan dosen yang aktif di LokaAuth");
        }
        $tahun = $ini->db->query("SELECT id, label FROM academic_years WHERE status = 'active'")->getRowArray();
        if ($tahun === null) {
            throw new \RuntimeException('belum ada tahun ajaran yang aktif; kelas tidak bisa diperiksa');
        }

        $ini->db->transStart();
        foreach ($benih['matkul'] as $m) {
            $ini->matkul($m, $pengampu, $tahun);
        }
        $ini->db->transComplete();
        if (! $ini->db->transStatus()) {
            throw new \RuntimeException('gagal menyimpan; tidak ada yang diubah');
        }

        return ['baris' => $ini->baris, 'hitung' => $ini->hitung];
    }

    /**
     * Benih yang salah ketik harus berhenti di sini, bukan menjadi baris di basis data.
     *
     * @param array<string, mixed> $benih
     */
    private function periksaBenih(array $benih): void
    {
        $katalog = [];
        foreach ($this->db->query('SELECT code, languages FROM concepts')->getResultArray() as $k) {
            $katalog[$k['code']] = explode(',', (string) $k['languages']);
        }
        foreach ($benih['matkul'] as $m) {
            $nomor = array_column($m['pertemuan'], 'nomor');
            if ($nomor !== range(1, 16)) {
                throw new \RuntimeException("benih {$m['kode']}: pertemuan harus bernomor 1 sampai 16, berurutan");
            }
            $terpakai = $m['konsep_dasar'];
            foreach ($m['pertemuan'] as $p) {
                $terpakai = array_merge($terpakai, $p['konsep']);
            }
            foreach ($terpakai as $kode) {
                if (! isset($katalog[$kode])) {
                    throw new \RuntimeException("benih {$m['kode']}: konsep {$kode} tidak ada di katalog");
                }
                if (! in_array($m['bahasa'], $katalog[$kode], true)) {
                    throw new \RuntimeException("benih {$m['kode']}: konsep {$kode} tidak berlaku untuk bahasa {$m['bahasa']}");
                }
            }
            if (count($terpakai) !== count(array_unique($terpakai))) {
                throw new \RuntimeException("benih {$m['kode']}: sebuah konsep tertulis lebih dari sekali");
            }
        }
    }

    /**
     * @param array<string, mixed>  $m
     * @param array<string, string> $pengampu
     * @param array<string, mixed>  $tahun
     */
    private function matkul(array $m, array $pengampu, array $tahun): void
    {
        $kode = (string) $m['kode'];
        $ada  = $this->db->query('SELECT id, name, owner_id FROM courses WHERE code = ?', [$kode])->getRowArray();
        $id   = $ada === null ? null : (int) $ada['id'];

        if ($ada === null) {
            // Mata kuliah adalah baris roster: sejak tahap A2 hanya Akun Lokandita yang membuatnya.
            $this->catat('beda', "KURANG {$kode} mata kuliah \"{$m['nama']}\": belum ada. Buat di Akun Lokandita, lalu ulangi; pertemuan dan kelasnya dilewati");

            return;
        } elseif ($ada['owner_id'] !== $pengampu['id']) {
            $this->catat('beda', "BEDA   {$kode} mata kuliah: pengampunya bukan {$pengampu['nomor_induk']}; tidak diubah");
        } elseif ($ada['name'] !== $m['nama']) {
            $this->catat('beda', "BEDA   {$kode} mata kuliah: namanya \"{$ada['name']}\", benih \"{$m['nama']}\"; tidak diubah");
        } else {
            $this->hitung['sama']++;
        }

        $this->konsepDasar($kode, $id, $m['konsep_dasar']);
        foreach ($m['pertemuan'] as $p) {
            $this->pertemuan($kode, $id, (string) $m['bahasa'], $p);
        }
        foreach ($m['kelas'] as $nama) {
            $this->kelas($kode, $id, (string) $nama, $tahun);
        }
    }

    /**
     * @param list<string> $benih
     */
    private function konsepDasar(string $kode, ?int $matkulId, array $benih): void
    {
        $ada = $matkulId === null ? [] : array_column(
            $this->db->query('SELECT concept_code FROM course_base_concepts WHERE course_id = ?', [$matkulId])->getResultArray(), 'concept_code');
        if (self::samaIsi($ada, $benih)) {
            $this->hitung['sama']++;

            return;
        }
        if ($ada !== []) {
            $this->catat('beda', "BEDA   {$kode} konsep dasar: tersimpan " . self::daftar($ada) . ', benih ' . self::daftar($benih) . '; tidak diubah');

            return;
        }
        $this->catat('diubah', "ISI    {$kode} konsep dasar: " . self::daftar($benih));
        if ($this->tulis) {
            foreach ($benih as $k) {
                $this->db->table('course_base_concepts')->insert(['course_id' => $matkulId, 'concept_code' => $k]);
            }
            $this->audit('matkul.konsepDasar', 'course', $matkulId, ['sebelum' => [], 'sesudah' => $benih], $matkulId);
        }
    }

    /**
     * @param array<string, mixed> $p
     */
    private function pertemuan(string $kode, ?int $matkulId, string $bahasa, array $p): void
    {
        $nomor   = (int) $p['nomor'];
        $latihan = $p['latihan'] ? 1 : 0;
        $apa     = "{$kode} pertemuan {$nomor}";
        $ada     = $matkulId === null ? null
            : $this->db->query('SELECT id, title, language, enabled FROM meetings WHERE course_id = ? AND number = ?', [$matkulId, $nomor])->getRowArray();

        if ($ada === null) {
            $this->catat('dibuat', "BUAT   {$apa} \"{$p['judul']}\" ({$bahasa}" . ($latihan ? '' : '; bukan untuk latihan') . '; ' . self::daftar($p['konsep']) . ')');
            if ($this->tulis) {
                $this->db->table('meetings')->insert([
                    'course_id' => $matkulId, 'number' => $nomor, 'title' => $p['judul'], 'language' => $bahasa,
                    'enabled' => $latihan, 'created_at' => Waktu::kini(), 'updated_at' => Waktu::kini(),
                ]);
                $id = (int) $this->db->insertID();
                foreach ($p['konsep'] as $k) {
                    $this->db->table('meeting_concepts')->insert(['meeting_id' => $id, 'concept_code' => $k]);
                }
                $this->audit('pertemuan.dibuat', 'meeting', $id,
                    ['number' => $nomor, 'title' => $p['judul'], 'language' => $bahasa, 'enabled' => $latihan, 'konsep' => $p['konsep']], $matkulId);
            }

            return;
        }

        $id     = (int) $ada['id'];
        $konsep = array_column($this->db->query('SELECT concept_code FROM meeting_concepts WHERE meeting_id = ?', [$id])->getResultArray(), 'concept_code');
        $sama   = true;

        if ($ada['language'] !== $bahasa) {
            $sama = false;
            $this->catat('beda', "BEDA   {$apa}: bahasanya {$ada['language']}, benih {$bahasa}; tidak diubah");
        }
        if ((int) $ada['enabled'] !== $latihan) {
            $sama = false;
            $this->catat('beda', "BEDA   {$apa}: " . ($latihan ? 'dimatikan untuk latihan, benih menyalakannya' : 'dipakai untuk latihan, benih mematikannya') . '; tidak diubah');
        }
        if ($ada['title'] !== $p['judul']) {
            $sama = false;
            $this->catat('diubah', "JUDUL  {$apa}: \"{$ada['title']}\" → \"{$p['judul']}\"");
            if ($this->tulis) {
                $this->db->table('meetings')->where('id', $id)->update(['title' => $p['judul'], 'updated_at' => Waktu::kini()]);
                $this->audit('pertemuan.diubah', 'meeting', $id, ['sebelum' => ['title' => $ada['title']], 'sesudah' => ['title' => $p['judul']]], $matkulId);
            }
        }
        if (! self::samaIsi($konsep, $p['konsep'])) {
            $sama = false;
            if ($konsep !== []) {
                $this->catat('beda', "BEDA   {$apa} konsep: tersimpan " . self::daftar($konsep) . ', benih ' . self::daftar($p['konsep']) . '; tidak diubah');
            } else {
                $this->catat('diubah', "ISI    {$apa} konsep: " . self::daftar($p['konsep']));
                if ($this->tulis) {
                    foreach ($p['konsep'] as $k) {
                        $this->db->table('meeting_concepts')->insert(['meeting_id' => $id, 'concept_code' => $k]);
                    }
                    $this->audit('pertemuan.diubah', 'meeting', $id, ['sebelum' => ['konsep' => []], 'sesudah' => ['konsep' => $p['konsep']]], $matkulId);
                }
            }
        }
        if ($sama) {
            $this->hitung['sama']++;
        }
    }

    /**
     * @param array<string, mixed> $tahun
     */
    private function kelas(string $kode, ?int $matkulId, string $nama, array $tahun): void
    {
        $ada = $matkulId === null ? null
            : $this->db->query('SELECT id, archived_at FROM classes WHERE course_id = ? AND academic_year_id = ? AND name = ?', [$matkulId, $tahun['id'], $nama])->getRowArray();
        if ($ada !== null) {
            if ($ada['archived_at'] === null) {
                $this->hitung['sama']++;
            } else {
                $this->catat('beda', "BEDA   {$kode} kelas {$nama} ({$tahun['label']}): sudah ada tetapi diarsipkan; tidak diubah");
            }

            return;
        }
        // Kelas adalah baris roster: sejak tahap A2 hanya Akun Lokandita yang membuatnya.
        $this->catat('beda', "KURANG {$kode} kelas {$nama} ({$tahun['label']}): belum ada. Buat di Akun Lokandita");
    }

    private function catat(string $jenis, string $baris): void
    {
        $this->hitung[$jenis]++;
        $this->baris[] = $baris;
    }

    /**
     * @param array<string, mixed> $detail
     */
    private function audit(string $aksi, string $tipe, ?int $id, array $detail, ?int $matkulId, ?int $kelasId = null): void
    {
        Audit::catat($aksi, $tipe, $id, $detail + ['lewat' => 'benih'], $matkulId, $kelasId, $this->pelakuId);
    }

    /**
     * @param list<string> $a
     * @param list<string> $b
     */
    private static function samaIsi(array $a, array $b): bool
    {
        sort($a);
        sort($b);

        return $a === $b;
    }

    /**
     * @param list<string> $kode
     */
    private static function daftar(array $kode): string
    {
        return $kode === [] ? 'tanpa konsep' : implode(', ', $kode);
    }
}
