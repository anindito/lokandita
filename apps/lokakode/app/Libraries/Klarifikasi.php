<?php

declare(strict_types=1);

namespace App\Libraries;

use App\Policy\Akses;
use CodeIgniter\Database\BaseConnection;

/**
 * Klarifikasi per soal (SPEC.md §14, KONTRAK.md §17): mahasiswa bertanya; dosen menjawab
 * secara pribadi atau menerbitkan jawabannya ke kelas penanya.
 *
 * Mahasiswa melihat pertanyaannya sendiri, dan pertanyaan yang diterbitkan ke kelas tempat
 * ia AKTIF sekarang (dengan nama penanya — keputusan pemilik). Jawaban pribadi hanya untuk
 * penanya dan dosen. Syarat lihat kelas dan "milik sendiri" ada di dalam kueri.
 *
 * Teks adalah teks polos; penampilnya yang meng-escape.
 */
final class Klarifikasi
{
    public const PANJANG_MAKS = 2000;

    public const BATAS_DAFTAR = 300;

    private BaseConnection $db;

    public function __construct(private readonly Akses $akses)
    {
        $this->db = db_connect();
    }

    /**
     * Teks pertanyaan atau jawaban dari isian: baris dirapikan; null bila kosong atau terlalu panjang.
     */
    public static function teks(string $isian): ?string
    {
        $teks = trim(str_replace(["\r\n", "\r"], "\n", $isian));

        return $teks === '' || mb_strlen($teks) > self::PANJANG_MAKS ? null : $teks;
    }

    // ---- mahasiswa --------------------------------------------------------------------

    /**
     * Yang boleh dilihat mahasiswa ini pada satu soal: pertanyaannya sendiri, dan yang diterbitkan ke kelasnya.
     *
     * @param array<string, mixed> $soal baris App\Libraries\Ruang::soal() — soal yang memang boleh ia buka
     *
     * @return list<array<string, mixed>>
     */
    public function untukMahasiswa(array $soal): array
    {
        return $this->db->query("
            SELECT q.id, q.user_id, q.question, q.created_at, q.answer, q.answered_at, q.visibility
            FROM clarifications q
            WHERE q.meeting_problem_id = ?
              AND ({$this->akses->milikSendiri('q')} OR (q.visibility = 'class' AND q.class_id = ?))
            ORDER BY q.id DESC LIMIT ?", [(int) $soal['mp_id'], (int) $soal['kelas_id'], self::BATAS_DAFTAR])->getResultArray();
    }

    /**
     * @param array<string, mixed> $soal baris Ruang::soal()
     */
    public function tanya(array $soal, string $teks): int
    {
        $this->db->table('clarifications')->insert([
            'meeting_problem_id' => (int) $soal['mp_id'], 'class_id' => (int) $soal['kelas_id'],
            'user_id' => $this->akses->identitas()->userId, 'question' => $teks, 'created_at' => Waktu::kini(),
        ]);

        return (int) $this->db->insertID();
    }

    // ---- dosen ------------------------------------------------------------------------

    private const DARI_KELAS = '
        FROM clarifications q
        JOIN classes k ON k.id = q.class_id
        JOIN courses c ON c.id = k.course_id
        JOIN meeting_problems mp ON mp.id = q.meeting_problem_id
        JOIN meetings m ON m.id = mp.meeting_id
        JOIN problems p ON p.id = mp.problem_id
        JOIN problem_versions v ON v.id = mp.problem_version_id';

    /**
     * Pertanyaan kelas itu: yang belum dijawab lebih dulu, lalu yang terbaru.
     *
     * @return list<array<string, mixed>>
     */
    public function kelas(int $kelasId, ?int $pertemuanId = null): array
    {
        $ikat = [$kelasId];
        if ($pertemuanId !== null) {
            $ikat[] = $pertemuanId;
        }
        $ikat[] = self::BATAS_DAFTAR;

        return $this->db->query('
            SELECT q.*, m.id AS pertemuan_id, m.number, p.slug, v.translations' . self::DARI_KELAS . "
            WHERE k.id = ? AND {$this->akses->lihatKelas()}" . ($pertemuanId !== null ? ' AND m.id = ?' : '') . '
            ORDER BY (q.answered_at IS NULL) DESC, q.id DESC LIMIT ?', $ikat)->getResultArray();
    }

    /**
     * @return array<string, mixed>|null null bila tidak ada, bukan pertanyaan kelas itu, atau kelasnya di luar hak pengguna
     */
    public function satu(int $kelasId, int $id): ?array
    {
        return $this->db->query('SELECT q.*, k.course_id' . self::DARI_KELAS . "
            WHERE q.id = ? AND k.id = ? AND {$this->akses->lihatKelas()}", [$id, $kelasId])->getRowArray();
    }

    public function belumDijawab(int $kelasId): int
    {
        return (int) $this->db->query("
            SELECT COUNT(*) AS n FROM clarifications q
            JOIN classes k ON k.id = q.class_id
            JOIN courses c ON c.id = k.course_id
            WHERE k.id = ? AND q.answered_at IS NULL AND {$this->akses->lihatKelas()}", [$kelasId])->getRow()->n;
    }

    /**
     * Menjawab, mengubah jawaban, atau mengubah siapa yang melihatnya. Setiap perubahan dicatat.
     *
     * @param array<string, mixed> $q baris dari satu()
     */
    public function jawab(array $q, string $jawaban, bool $keKelas): void
    {
        $kini     = Waktu::kini();
        $teksBaru = $q['answer'] !== $jawaban;
        $dulu     = $q['visibility'] === 'class';
        $set      = ['visibility' => $keKelas ? 'class' : 'private', 'published_at' => $keKelas ? ($dulu ? $q['published_at'] : $kini) : null];
        if ($teksBaru) {
            $set += ['answer' => $jawaban, 'answered_by' => $this->akses->identitas()->userId, 'answered_at' => $kini];
        }
        $this->db->transStart();
        $this->db->table('clarifications')->where('id', (int) $q['id'])->update($set);
        $detail = ['soal' => (int) $q['meeting_problem_id'], 'penanya' => $q['user_id']];
        if ($teksBaru) {
            Audit::catat('klarifikasi.dijawab', 'clarification', (int) $q['id'], $detail + ['diubah' => $q['answer'] !== null], (int) $q['course_id'], (int) $q['class_id']);
        }
        if ($keKelas !== $dulu) {
            Audit::catat($keKelas ? 'klarifikasi.diterbitkan' : 'klarifikasi.ditarik', 'clarification', (int) $q['id'], $detail, (int) $q['course_id'], (int) $q['class_id']);
        }
        $this->db->transComplete();
    }
}
