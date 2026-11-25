<?php

declare(strict_types=1);

namespace App\Libraries;

use App\Policy\Akses;
use CodeIgniter\Database\BaseConnection;
use Lokandita\Auth\Waktu;

/**
 * Ujian dan kuis satu mata kuliah (SPEC-LOKAUJIAN.md §6). Tahap U2 hanya mengenal DRAF: dibuat
 * dari butir bank yang dicentang atau dari impor berkas, dilihat, diekspor, dihapus. Menyusun
 * ulang, mengubah setelan dan menerbitkan adalah tahap U3.
 *
 * Seperti App\Libraries\Bank, setiap kueri diikat ke mata kuliah yang diminta DAN ke
 * Akses::diKelola().
 */
final class Ujian
{
    private readonly BaseConnection $db;

    private readonly Bank $bank;

    public function __construct(private readonly Akses $akses)
    {
        $this->db   = db_connect();
        $this->bank = new Bank($akses);
    }

    /**
     * Jumlah soal dan poin sebuah daftar butir: total, yang dinilai otomatis (benar/salah dan
     * pilihan ganda) dan yang dinilai dosen (isian dan uraian) — SPEC-LOKAUJIAN.md §6.1.
     *
     * @param list<array<string, mixed>> $butir
     *
     * @return array{soal: int, poin: float, otomatis: float, manual: float}
     */
    public static function ringkasan(array $butir): array
    {
        $r = ['soal' => 0, 'poin' => 0.0, 'otomatis' => 0.0, 'manual' => 0.0];
        foreach ($butir as $b) {
            foreach ($b['jenis'] === 'soal' ? [$b['soal']] : $b['kelompok']['questions'] as $s) {
                $r['soal']++;
                $r['poin'] += (float) $s['points'];
                $r[in_array($s['type'], ['benar_salah', 'pilihan_ganda'], true) ? 'otomatis' : 'manual'] += (float) $s['points'];
            }
        }

        return $r;
    }

    /**
     * Butir sebuah ujian, berurutan, dengan soal atau kelompoknya lengkap.
     *
     * @return list<array<string, mixed>>
     */
    private function butir(int $matkulId, int $ujianId): array
    {
        $baris = $this->db->query('SELECT question_id, group_id FROM exam_items WHERE exam_id = ? ORDER BY position', [$ujianId])->getResultArray();
        $butir = [];
        foreach ($baris as $b) {
            if ($b['question_id'] !== null) {
                $s = $this->bank->soal($matkulId, (int) $b['question_id']);
                if ($s !== null) {
                    $butir[] = ['jenis' => 'soal', 'soal' => $s];
                }
            } else {
                $k = $this->bank->kelompok($matkulId, (int) $b['group_id']);
                if ($k !== null) {
                    $butir[] = ['jenis' => 'kelompok', 'kelompok' => $k];
                }
            }
        }

        return $butir;
    }

    /**
     * Ujian mata kuliah itu, terbaru di atas, masing-masing dengan ringkasan poinnya.
     *
     * @return list<array<string, mixed>>
     */
    public function daftar(int $matkulId): array
    {
        $ujian = $this->db->query(
            "SELECT * FROM exams WHERE course_id = ? AND {$this->akses->diKelola()} ORDER BY id DESC",
            [$matkulId],
        )->getResultArray();
        foreach ($ujian as &$u) {
            $u['ringkasan'] = self::ringkasan($this->butir($matkulId, (int) $u['id']));
        }

        return $ujian;
    }

    /**
     * Satu ujian dengan butirnya; null bila tidak ada, milik mata kuliah lain, atau di luar hak.
     *
     * @return array<string, mixed>|null
     */
    public function lihat(int $matkulId, int $id): ?array
    {
        $u = $this->db->query(
            "SELECT * FROM exams WHERE id = ? AND course_id = ? AND {$this->akses->diKelola()}",
            [$id, $matkulId],
        )->getRowArray();
        if ($u === null) {
            return null;
        }
        $u['butir']     = $this->butir($matkulId, $id);
        $u['ringkasan'] = self::ringkasan($u['butir']);

        return $u;
    }

    /**
     * Membuat draf ujian dari butir bank. Mengembalikan id ujian, atau kode galat:
     * `tanpa_butir`, `butir_asing` (bukan soal lepas atau kelompok mata kuliah ini),
     * `kelompok_kosong`, `terlalu_banyak` (lebih dari 200 soal).
     *
     * @param array{title: string, kind: string, instructions: string, mode: string, duration_minutes: ?int, shuffle_questions: bool, shuffle_options: bool} $setelan hasil Aturan::ujian()
     * @param list<array{0: 'soal'|'kelompok', 1: int}> $butir   menurut urutan ujian
     * @param array<string, mixed>                      $detail  tambahan untuk baris audit
     */
    public function buat(int $matkulId, array $setelan, array $butir, string $aksi = 'ujian.dibuat', array $detail = []): int|string
    {
        if ($butir === []) {
            return 'tanpa_butir';
        }

        return $this->bank->transaksi(function () use ($matkulId, $setelan, $butir, $aksi, $detail): int|string {
            $jumlah = 0;
            $lihat  = [];
            foreach ($butir as [$jenis, $id]) {
                // Butir yang sama dua kali tidak bermakna: satu soal muncul sekali dalam satu ujian.
                if (isset($lihat[$jenis . $id])) {
                    return 'butir_asing';
                }
                $lihat[$jenis . $id] = true;
                if ($jenis === 'soal') {
                    $s = $this->bank->soal($matkulId, $id);
                    if ($s === null || $s['group_id'] !== null) {
                        return 'butir_asing';
                    }
                    $jumlah++;
                } else {
                    $k = $this->bank->kelompok($matkulId, $id);
                    if ($k === null) {
                        return 'butir_asing';
                    }
                    if ($k['questions'] === []) {
                        return 'kelompok_kosong';
                    }
                    $jumlah += count($k['questions']);
                }
            }
            if ($jumlah > Aturan::SOAL_UJIAN_MAKS) {
                return 'terlalu_banyak';
            }
            $pelaku = $this->akses->identitas()->userId;
            $kini   = Waktu::kini();
            $this->db->table('exams')->insert([
                'course_id' => $matkulId, 'title' => $setelan['title'], 'kind' => $setelan['kind'], 'instructions' => $setelan['instructions'],
                'mode' => $setelan['mode'], 'duration_minutes' => $setelan['duration_minutes'],
                'shuffle_questions' => (int) $setelan['shuffle_questions'], 'shuffle_options' => (int) $setelan['shuffle_options'],
                'status' => 'draf', 'created_by' => $pelaku, 'created_at' => $kini, 'updated_at' => $kini,
            ]);
            $ujianId = (int) $this->db->insertID();
            foreach ($butir as $i => [$jenis, $id]) {
                $this->db->table('exam_items')->insert([
                    'exam_id' => $ujianId, 'position' => $i + 1,
                    'question_id' => $jenis === 'soal' ? $id : null, 'group_id' => $jenis === 'kelompok' ? $id : null,
                ]);
            }
            Audit::catat($aksi, 'ujian', $ujianId, ['judul' => $setelan['title'], 'butir' => count($butir), 'soal' => $jumlah] + $detail, $matkulId);

            return $ujianId;
        });
    }

    /**
     * Menghapus draf. Ujian yang sudah terbit tidak dihapus di sini (tahap U3 mengaturnya:
     * ujian yang punya pengerjaan tidak pernah dihapus). Soal-soalnya tetap di bank.
     *
     * @return 'ok'|'tidak_ada'|'bukan_draf'
     */
    public function hapus(int $matkulId, int $id): string
    {
        return $this->bank->transaksi(function () use ($matkulId, $id): string {
            $u = $this->lihat($matkulId, $id);
            if ($u === null) {
                return 'tidak_ada';
            }
            if ($u['status'] !== 'draf') {
                return 'bukan_draf';
            }
            $this->db->table('exams')->where('id', $id)->where('course_id', $matkulId)->delete(); // butirnya ikut (ON DELETE CASCADE)
            Audit::catat('ujian.dihapus', 'ujian', $id, ['judul' => $u['title'], 'soal' => $u['ringkasan']['soal']], $matkulId);

            return 'ok';
        });
    }
}
