<?php

declare(strict_types=1);

namespace App\Libraries;

use App\Policy\Akses;
use CodeIgniter\Database\BaseConnection;
use Lokandita\Auth\Waktu;

/**
 * Bank soal satu mata kuliah (SPEC-LOKAUJIAN.md §5, §5.4; KONTRAK.md §20).
 *
 * SETIAP kueri di sini memuat dua syarat: mata kuliah yang diminta, dan Akses::diKelola() —
 * daftar id mata kuliah yang diampu pengguna, dibaca dari roster. Tidak ada fungsi yang
 * mengambil soal, kelompok atau gambar hanya berdasarkan id-nya, sehingga soal mata kuliah lain
 * tidak pernah terbaca, bukan dibaca dulu lalu disembunyikan.
 *
 * Bentuk baku sebuah soal (hasil App\Libraries\Aturan, dengan gambar yang sudah menjadi baris
 * `images`):
 *   ['type', 'text', 'points' => '2.50', 'answer' => ?bool,
 *    'image' => null | ['id', 'alt', 'sha256', …],
 *    'options' => [['text', 'correct' => bool, 'image' => null | […]], …]]
 */
final class Bank
{
    private readonly BaseConnection $db;

    public function __construct(private readonly Akses $akses)
    {
        $this->db = db_connect();
    }

    /**
     * Menjalankan $kerja dalam satu transaksi. Boleh bersarang: hanya transaksi terluar yang
     * benar-benar menyimpan.
     *
     * @template T
     *
     * @param callable(): T $kerja
     *
     * @return T
     */
    public function transaksi(callable $kerja): mixed
    {
        $this->db->transBegin();
        try {
            $hasil = $kerja();
            $this->db->transCommit();

            return $hasil;
        } catch (\Throwable $e) {
            $this->db->transRollback();

            throw $e;
        }
    }

    // ------------------------------------------------------------------ sidik isi

    /**
     * @param array<string, mixed>|null $gambar
     *
     * @return array{0: string, 1: string}|null
     */
    private static function sidikGambar(?array $gambar): ?array
    {
        return $gambar === null ? null : [(string) $gambar['sha256'], (string) $gambar['alt']];
    }

    /**
     * SHA-256 isi sebuah soal: tipe, teks, poin, kunci, gambar (isi berkas dan teks
     * alternatifnya), dan opsi dengan urutan serta kuncinya. Dua soal dengan sidik yang sama
     * adalah soal yang sama bagi impor (KONTRAK.md §20).
     *
     * @param array<string, mixed> $s bentuk baku
     */
    public static function sidikSoal(array $s): string
    {
        $opsi = [];
        foreach ($s['options'] as $o) {
            $opsi[] = [$o['text'], (bool) $o['correct'], self::sidikGambar($o['image'])];
        }

        return hash('sha256', (string) json_encode(
            [$s['type'], $s['text'], number_format((float) $s['points'], 2, '.', ''), $s['answer'], self::sidikGambar($s['image']), $opsi],
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES,
        ));
    }

    /**
     * @param array<string, mixed>|null $gambar
     * @param list<string>              $sidikSoal sidik soal-soalnya, menurut urutan
     */
    public static function sidikKelompok(string $teks, ?array $gambar, array $sidikSoal): string
    {
        return hash('sha256', (string) json_encode(['kelompok', $teks, self::sidikGambar($gambar), $sidikSoal], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }

    // ------------------------------------------------------------------ membaca

    /**
     * Soal-soal lengkap dengan opsi dan gambarnya, menurut id.
     *
     * @param string $syarat syarat SQL atas alias q (sudah aman), $ikat nilainya
     * @param list<mixed> $ikat
     *
     * @return array<int, array<string, mixed>> id => soal
     */
    private function muatSoal(string $syarat, array $ikat, string $urut = 'q.id'): array
    {
        $baris = $this->db->query("
            SELECT q.*, i.kunci AS g_kunci, i.jenis AS g_jenis, i.sha256 AS g_sha256, i.lebar AS g_lebar, i.tinggi AS g_tinggi
            FROM questions q LEFT JOIN images i ON i.id = q.image_id
            WHERE {$syarat} AND {$this->akses->diKelola('q.course_id')}
            ORDER BY {$urut}", $ikat)->getResultArray();
        $soal = [];
        foreach ($baris as $b) {
            $soal[(int) $b['id']] = [
                'id' => (int) $b['id'], 'course_id' => (int) $b['course_id'],
                'group_id' => $b['group_id'] === null ? null : (int) $b['group_id'],
                'position' => $b['position'] === null ? null : (int) $b['position'],
                'type' => (string) $b['type'], 'text' => (string) $b['text'], 'points' => (string) $b['points'],
                'answer' => $b['answer'] === null ? null : (bool) $b['answer'],
                'image' => self::gambarDariBaris($b, 'image_id', 'image_alt'),
                'options' => [], 'archived_at' => $b['archived_at'], 'content_hash' => (string) $b['content_hash'],
                'created_at' => (string) $b['created_at'], 'updated_at' => (string) $b['updated_at'],
            ];
        }
        if ($soal === []) {
            return [];
        }
        $opsi = $this->db->query('
            SELECT o.*, i.kunci AS g_kunci, i.jenis AS g_jenis, i.sha256 AS g_sha256, i.lebar AS g_lebar, i.tinggi AS g_tinggi
            FROM question_options o LEFT JOIN images i ON i.id = o.image_id
            WHERE o.question_id IN (' . implode(',', array_keys($soal)) . ')
            ORDER BY o.question_id, o.position')->getResultArray();
        foreach ($opsi as $o) {
            $soal[(int) $o['question_id']]['options'][] = [
                'id' => (int) $o['id'], 'text' => (string) $o['text'], 'correct' => (bool) $o['correct'],
                'image' => self::gambarDariBaris($o, 'image_id', 'image_alt'),
            ];
        }

        return $soal;
    }

    /**
     * @param array<string, mixed> $b
     *
     * @return array<string, mixed>|null
     */
    private static function gambarDariBaris(array $b, string $kolomId, string $kolomAlt): ?array
    {
        return $b[$kolomId] === null ? null : [
            'id' => (int) $b[$kolomId], 'alt' => (string) $b[$kolomAlt], 'kunci' => (string) $b['g_kunci'], 'jenis' => (string) $b['g_jenis'],
            'sha256' => (string) $b['g_sha256'], 'lebar' => (int) $b['g_lebar'], 'tinggi' => (int) $b['g_tinggi'],
        ];
    }

    /**
     * Satu soal mata kuliah itu; null bila tidak ada, milik mata kuliah lain, atau di luar hak.
     *
     * @return array<string, mixed>|null
     */
    public function soal(int $matkulId, int $id): ?array
    {
        return $this->muatSoal('q.id = ? AND q.course_id = ?', [$id, $matkulId])[$id] ?? null;
    }

    /**
     * Kelompok-kelompok lengkap dengan soalnya (berurutan), menurut syarat atas alias g.
     *
     * @param list<mixed> $ikat
     *
     * @return array<int, array<string, mixed>>
     */
    private function muatKelompok(string $syarat, array $ikat): array
    {
        $baris = $this->db->query("
            SELECT g.*, i.kunci AS g_kunci, i.jenis AS g_jenis, i.sha256 AS g_sha256, i.lebar AS g_lebar, i.tinggi AS g_tinggi
            FROM question_groups g LEFT JOIN images i ON i.id = g.image_id
            WHERE {$syarat} AND {$this->akses->diKelola('g.course_id')}
            ORDER BY g.id", $ikat)->getResultArray();
        $kelompok = [];
        foreach ($baris as $b) {
            $kelompok[(int) $b['id']] = [
                'id' => (int) $b['id'], 'course_id' => (int) $b['course_id'], 'text' => (string) $b['text'],
                'image' => self::gambarDariBaris($b, 'image_id', 'image_alt'), 'questions' => [],
                'archived_at' => $b['archived_at'], 'content_hash' => (string) $b['content_hash'],
                'created_at' => (string) $b['created_at'], 'updated_at' => (string) $b['updated_at'],
            ];
        }
        if ($kelompok === []) {
            return [];
        }
        $soal = $this->muatSoal('q.group_id IN (' . implode(',', array_keys($kelompok)) . ')', [], 'q.group_id, q.position');
        foreach ($soal as $s) {
            $kelompok[(int) $s['group_id']]['questions'][] = $s;
        }

        return $kelompok;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function kelompok(int $matkulId, int $id): ?array
    {
        return $this->muatKelompok('g.id = ? AND g.course_id = ?', [$id, $matkulId])[$id] ?? null;
    }

    /**
     * Ujian yang memakai tiap soal lepas dan tiap kelompok mata kuliah itu.
     *
     * @return array<string, list<array{id: int, title: string, status: string}>> "s<id>" atau "k<id>" => ujian
     */
    public function pemakaian(int $matkulId): array
    {
        $baris = $this->db->query("
            SELECT x.question_id, x.group_id, u.id, u.title, u.status
            FROM exam_items x JOIN exams u ON u.id = x.exam_id
            WHERE u.course_id = ? AND {$this->akses->diKelola('u.course_id')}
            ORDER BY u.id", [$matkulId])->getResultArray();
        $peta = [];
        foreach ($baris as $b) {
            $kunci          = $b['question_id'] !== null ? 's' . $b['question_id'] : 'k' . $b['group_id'];
            $peta[$kunci][] = ['id' => (int) $b['id'], 'title' => (string) $b['title'], 'status' => (string) $b['status']];
        }

        return $peta;
    }

    /**
     * Isi bank: soal lepas dan kelompok, berurutan menurut waktu dibuat. Saringan: `tipe` (salah
     * satu dari empat tipe, atau `kelompok`), `q` (kata di dalam teks soal atau teks kelompok),
     * `arsip` (true = yang diarsipkan saja; bawaan: yang tidak diarsipkan).
     *
     * @param array{tipe?: string, q?: string, arsip?: bool} $saring
     *
     * @return list<array<string, mixed>> butir: ['jenis' => 'soal', 'soal' => …] atau ['jenis' => 'kelompok', 'kelompok' => …]
     */
    public function daftar(int $matkulId, array $saring = []): array
    {
        $tipe  = (string) ($saring['tipe'] ?? '');
        $kata  = trim((string) ($saring['q'] ?? ''));
        $arsip = ($saring['arsip'] ?? false) ? 'IS NOT NULL' : 'IS NULL';
        $pola  = '%' . $this->db->escapeLikeString($kata) . '%';
        $butir = [];

        if ($tipe !== 'kelompok') {
            $syarat = "q.course_id = ? AND q.group_id IS NULL AND q.archived_at {$arsip}";
            $ikat   = [$matkulId];
            if (in_array($tipe, Aturan::TIPE, true)) {
                $syarat .= ' AND q.type = ?';
                $ikat[]  = $tipe;
            }
            if ($kata !== '') {
                $syarat .= " AND q.text LIKE ? ESCAPE '!'";
                $ikat[]  = $pola;
            }
            foreach ($this->muatSoal($syarat, $ikat) as $s) {
                $butir[] = ['jenis' => 'soal', 'soal' => $s, 'urut' => [$s['created_at'], 0, $s['id']]];
            }
        }
        if ($tipe === '' || $tipe === 'kelompok') {
            $syarat = "g.course_id = ? AND g.archived_at {$arsip}";
            $ikat   = [$matkulId];
            if ($kata !== '') {
                $syarat .= " AND (g.text LIKE ? ESCAPE '!' OR EXISTS (SELECT 1 FROM questions qx WHERE qx.group_id = g.id AND qx.text LIKE ? ESCAPE '!'))";
                array_push($ikat, $pola, $pola);
            }
            foreach ($this->muatKelompok($syarat, $ikat) as $k) {
                $butir[] = ['jenis' => 'kelompok', 'kelompok' => $k, 'urut' => [$k['created_at'], 1, $k['id']]];
            }
        }
        // Urutan penulisan: yang dibuat lebih dulu di atas. Urutan ini juga menjadi urutan butir
        // draf ujian yang dibuat dari daftar ini.
        usort($butir, static fn (array $a, array $b): int => $a['urut'] <=> $b['urut']);

        return $butir;
    }

    // ------------------------------------------------------------------ menulis

    /**
     * Membuat ($id null) atau mengubah satu soal. $kelompokId hanya dipakai saat membuat: soal
     * itu menjadi soal terakhir kelompok tersebut. Mengembalikan id soal, atau kode galat:
     * `tidak_ada`, `kelompok_penuh`.
     *
     * @param array<string, mixed> $baku bentuk baku
     */
    public function simpanSoal(int $matkulId, ?int $id, array $baku, ?int $kelompokId = null): int|string
    {
        return $this->transaksi(function () use ($matkulId, $id, $baku, $kelompokId): int|string {
            $pelaku = $this->akses->identitas()->userId;
            $kini   = Waktu::kini();
            $kolom  = [
                'type' => $baku['type'], 'text' => $baku['text'], 'points' => $baku['points'],
                'answer' => $baku['answer'] === null ? null : (int) $baku['answer'],
                'image_id' => $baku['image']['id'] ?? null, 'image_alt' => $baku['image']['alt'] ?? null,
                'content_hash' => self::sidikSoal($baku), 'updated_by' => $pelaku, 'updated_at' => $kini,
            ];
            if ($id === null) {
                $posisi = null;
                if ($kelompokId !== null) {
                    // Baris kelompok dikunci, supaya dua penambahan bersamaan tidak mendapat urutan yang sama.
                    $k = $this->db->query("SELECT id FROM question_groups WHERE id = ? AND course_id = ? AND {$this->akses->diKelola()} FOR UPDATE", [$kelompokId, $matkulId])->getRowArray();
                    if ($k === null) {
                        return 'tidak_ada';
                    }
                    $jumlah = (int) $this->db->query('SELECT COUNT(*) AS n FROM questions WHERE group_id = ?', [$kelompokId])->getRow()->n;
                    if ($jumlah >= Aturan::SOAL_KELOMPOK_MAKS) {
                        return 'kelompok_penuh';
                    }
                    $posisi = $jumlah + 1;
                }
                $this->db->table('questions')->insert($kolom + [
                    'course_id' => $matkulId, 'group_id' => $kelompokId, 'position' => $posisi, 'created_by' => $pelaku, 'created_at' => $kini,
                ]);
                $id = (int) $this->db->insertID();
            } else {
                $lama = $this->soal($matkulId, $id);
                if ($lama === null) {
                    return 'tidak_ada';
                }
                $kelompokId = $lama['group_id'];
                $this->db->table('questions')->where('id', $id)->where('course_id', $matkulId)->update($kolom);
                $this->db->table('question_options')->where('question_id', $id)->delete();
            }
            foreach ($baku['options'] as $i => $o) {
                $this->db->table('question_options')->insert([
                    'question_id' => $id, 'position' => $i + 1, 'text' => $o['text'], 'correct' => (int) $o['correct'],
                    'image_id' => $o['image']['id'] ?? null, 'image_alt' => $o['image']['alt'] ?? null,
                ]);
            }
            if ($kelompokId !== null) {
                $this->segarkanSidikKelompok($kelompokId);
            }

            return $id;
        });
    }

    /**
     * Membuat atau mengubah kepala kelompok (teks bersama, gambar). Mengembalikan id, atau `tidak_ada`.
     *
     * @param array{text: string, image: array<string, mixed>|null} $baku
     */
    public function simpanKelompok(int $matkulId, ?int $id, array $baku): int|string
    {
        return $this->transaksi(function () use ($matkulId, $id, $baku): int|string {
            $pelaku = $this->akses->identitas()->userId;
            $kini   = Waktu::kini();
            $kolom  = [
                'text' => $baku['text'], 'image_id' => $baku['image']['id'] ?? null, 'image_alt' => $baku['image']['alt'] ?? null,
                'updated_by' => $pelaku, 'updated_at' => $kini,
            ];
            if ($id === null) {
                $this->db->table('question_groups')->insert($kolom + [
                    'course_id' => $matkulId, 'content_hash' => self::sidikKelompok($baku['text'], $baku['image'], []),
                    'created_by' => $pelaku, 'created_at' => $kini,
                ]);

                return (int) $this->db->insertID();
            }
            if ($this->kelompok($matkulId, $id) === null) {
                return 'tidak_ada';
            }
            $this->db->table('question_groups')->where('id', $id)->where('course_id', $matkulId)->update($kolom);
            $this->segarkanSidikKelompok($id);

            return $id;
        });
    }

    /**
     * Sidik kelompok memuat sidik soal-soalnya, jadi dihitung ulang setiap kali isinya berubah.
     */
    private function segarkanSidikKelompok(int $id): void
    {
        $k = $this->muatKelompok('g.id = ?', [$id])[$id] ?? null;
        if ($k !== null) {
            $sidik = self::sidikKelompok($k['text'], $k['image'], array_column($k['questions'], 'content_hash'));
            $this->db->table('question_groups')->where('id', $id)->update(['content_hash' => $sidik]);
        }
    }

    /**
     * Mengarsipkan atau memulihkan soal lepas atau kelompok: disembunyikan dari bank, tetap ada
     * untuk ujian yang memakainya. Soal anggota kelompok mengikuti kelompoknya.
     *
     * @param 'soal'|'kelompok' $jenis
     */
    public function arsipkan(string $jenis, int $matkulId, int $id, bool $arsip): bool
    {
        // Yang ada dan boleh diarsipkan dipastikan dulu; soal anggota kelompok tidak diarsipkan sendiri.
        $ada = $jenis === 'soal' ? $this->soal($matkulId, $id) : $this->kelompok($matkulId, $id);
        if ($ada === null || ($jenis === 'soal' && $ada['group_id'] !== null)) {
            return false;
        }
        $tabel = $jenis === 'soal' ? 'questions' : 'question_groups';
        $this->db->query(
            "UPDATE {$tabel} SET archived_at = ? WHERE id = ? AND course_id = ? AND {$this->akses->diKelola()}",
            [$arsip ? Waktu::kini() : null, $id, $matkulId],
        );

        return true;
    }

    /**
     * Menghapus satu soal untuk selamanya. Hanya soal yang tidak dipakai ujian mana pun (soal
     * anggota kelompok: kelompoknya tidak dipakai); selain itu hanya bisa diarsipkan.
     *
     * @return 'ok'|'tidak_ada'|'dipakai'
     */
    public function hapusSoal(int $matkulId, int $id): string
    {
        return $this->transaksi(function () use ($matkulId, $id): string {
            $s = $this->soal($matkulId, $id);
            if ($s === null) {
                return 'tidak_ada';
            }
            $dipakai = $s['group_id'] === null
                ? $this->db->query('SELECT COUNT(*) AS n FROM exam_items WHERE question_id = ?', [$id])->getRow()->n
                : $this->db->query('SELECT COUNT(*) AS n FROM exam_items WHERE group_id = ?', [$s['group_id']])->getRow()->n;
            if ((int) $dipakai > 0) {
                return 'dipakai';
            }
            $this->db->table('questions')->where('id', $id)->where('course_id', $matkulId)->delete(); // opsinya ikut terhapus (ON DELETE CASCADE)
            if ($s['group_id'] !== null) {
                // Urutan sisanya dirapatkan, dari yang terkecil supaya tidak ada dua soal bernomor sama.
                $this->db->query('UPDATE questions SET position = position - 1 WHERE group_id = ? AND position > ? ORDER BY position', [$s['group_id'], $s['position']]);
                $this->segarkanSidikKelompok($s['group_id']);
            }
            Audit::catat('soal.dihapus', 'soal', $id, ['tipe' => $s['type'], 'kelompok' => $s['group_id']], $matkulId);

            return 'ok';
        });
    }

    /**
     * Menghapus satu kelompok beserta soal-soalnya. Hanya bila tidak dipakai ujian mana pun.
     *
     * @return 'ok'|'tidak_ada'|'dipakai'
     */
    public function hapusKelompok(int $matkulId, int $id): string
    {
        return $this->transaksi(function () use ($matkulId, $id): string {
            $k = $this->kelompok($matkulId, $id);
            if ($k === null) {
                return 'tidak_ada';
            }
            if ((int) $this->db->query('SELECT COUNT(*) AS n FROM exam_items WHERE group_id = ?', [$id])->getRow()->n > 0) {
                return 'dipakai';
            }
            $this->db->table('questions')->where('group_id', $id)->where('course_id', $matkulId)->delete();
            $this->db->table('question_groups')->where('id', $id)->where('course_id', $matkulId)->delete();
            Audit::catat('kelompok.dihapus', 'kelompok', $id, ['soal' => count($k['questions'])], $matkulId);

            return 'ok';
        });
    }

    /**
     * Menukar satu soal anggota kelompok dengan tetangganya. Urutan di dalam kelompok adalah
     * urutan yang dilihat mahasiswa dan tidak pernah diacak (SPEC-LOKAUJIAN.md §5.2).
     *
     * @param 'naik'|'turun' $arah
     */
    public function geser(int $matkulId, int $id, string $arah): bool
    {
        return $this->transaksi(function () use ($matkulId, $id, $arah): bool {
            $s = $this->soal($matkulId, $id);
            if ($s === null || $s['group_id'] === null) {
                return false;
            }
            $tujuan   = $s['position'] + ($arah === 'naik' ? -1 : 1);
            $tetangga = $this->db->query('SELECT id FROM questions WHERE group_id = ? AND position = ?', [$s['group_id'], $tujuan])->getRowArray();
            if ($tetangga !== null) {
                // Lewat urutan 0 (tidak pernah dipakai), karena (group_id, position) unik.
                $this->db->query('UPDATE questions SET position = 0 WHERE id = ?', [$id]);
                $this->db->query('UPDATE questions SET position = ? WHERE id = ?', [$s['position'], $tetangga['id']]);
                $this->db->query('UPDATE questions SET position = ? WHERE id = ?', [$tujuan, $id]);
                $this->segarkanSidikKelompok($s['group_id']);
            }

            return true;
        });
    }

    // ------------------------------------------------------------------ untuk impor

    /**
     * Soal lepas mata kuliah itu yang isinya sama (termasuk yang diarsipkan); yang tertua.
     */
    public function soalLepasBersidik(int $matkulId, string $sidik): ?int
    {
        $b = $this->db->query(
            "SELECT id FROM questions WHERE course_id = ? AND group_id IS NULL AND content_hash = ? AND {$this->akses->diKelola()} ORDER BY id LIMIT 1",
            [$matkulId, $sidik],
        )->getRowArray();

        return $b === null ? null : (int) $b['id'];
    }

    public function kelompokBersidik(int $matkulId, string $sidik): ?int
    {
        $b = $this->db->query(
            "SELECT id FROM question_groups WHERE course_id = ? AND content_hash = ? AND {$this->akses->diKelola()} ORDER BY id LIMIT 1",
            [$matkulId, $sidik],
        )->getRowArray();

        return $b === null ? null : (int) $b['id'];
    }
}
