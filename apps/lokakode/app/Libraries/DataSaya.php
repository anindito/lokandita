<?php

declare(strict_types=1);

namespace App\Libraries;

use Lokandita\Auth\Ekspor;

/**
 * Ekspor data milik satu pengguna dari LokaKode (SPEC.md §16, KONTRAK.md §14).
 *
 * Setiap kueri terikat pada satu user id. Yang tidak pernah diambil: isi tes
 * (problem_instances.tests), generator dan solusi referensi, dan baris milik orang lain.
 * Soal disebut dengan kode mata kuliah, nomor pertemuan dan slug-nya saja; soal pertemuan UJIAN
 * disebut `soal-<nomor>` tanpa slug, karena soal ujian tersembunyi di luar jendelanya (SPEC.md §6).
 */
final class DataSaya
{
    // Dari satu baris yang menunjuk meeting_problems ke nama soal yang bisa dibaca orang.
    private const SOAL = 'JOIN meeting_problems mp ON mp.id = x.meeting_problem_id
        JOIN problems p ON p.id = mp.problem_id
        JOIN meetings m ON m.id = mp.meeting_id
        JOIN courses c ON c.id = m.course_id';

    private const NAMA_SOAL = "IF(m.kind = 'ujian', CONCAT('soal-', mp.id), p.slug) AS soal";

    /**
     * @return array{0: array<string, mixed>, 1: list<string>} isi berkas dan nama daftar yang terpotong
     */
    public static function susun(string $userId): array
    {
        $db        = db_connect();
        $ambil     = Ekspor::BATAS + 1;
        $terpotong = [];
        $kelas     = 'JOIN classes k ON k.id = x.class_id JOIN courses c ON c.id = k.course_id JOIN academic_years t ON t.id = k.academic_year_id';

        // Nilai ujian yang belum dirilis untuk kelasnya tidak ikut diekspor (SPEC.md §6): baris nilainya
        // dilewati, dan kirimannya tetap ada (kode sumbernya miliknya) tetapi tanpa verdict dan nilai.
        $tampak = Latihan::sqlNilaiTampak('m', 'x.user_id');

        $pendaftaran = $db->query(
            "SELECT c.code AS mata_kuliah, k.name AS kelas, t.label AS tahun_ajaran, x.status, x.graduated_on, x.created_at
             FROM enrollments x {$kelas} WHERE x.user_id = ? ORDER BY x.created_at DESC",
            [$userId],
        )->getResultArray();
        $mengajar = $db->query(
            "SELECT c.code AS mata_kuliah, k.name AS kelas, t.label AS tahun_ajaran, x.assigned_at
             FROM class_teachers x {$kelas} WHERE x.user_id = ? ORDER BY x.assigned_at DESC",
            [$userId],
        )->getResultArray();
        $mengampu = $db->query('SELECT code AS mata_kuliah, name AS nama FROM courses WHERE owner_id = ? ORDER BY code', [$userId])->getResultArray();

        $nilaiPertemuan = $db->query(
            "SELECT c.code AS mata_kuliah, m.number AS pertemuan, x.score AS nilai, x.total_points, x.counted_points, x.target_points,
                    x.surplus, x.very_easy_counted, x.submissions AS kiriman, x.reached_at, x.updated_at
             FROM meeting_scores x JOIN meetings m ON m.id = x.meeting_id JOIN courses c ON c.id = m.course_id
             WHERE x.user_id = ? AND {$tampak} ORDER BY x.updated_at DESC LIMIT {$ambil}",
            [$userId],
        )->getResultArray();
        $nilaiSoal = $db->query(
            'SELECT c.code AS mata_kuliah, m.number AS pertemuan, ' . self::NAMA_SOAL . ', x.difficulty, x.points, x.best_score, x.attempts, x.reached_at, x.updated_at
             FROM problem_scores x ' . self::SOAL . " WHERE x.user_id = ? AND {$tampak} ORDER BY x.updated_at DESC LIMIT {$ambil}",
            [$userId],
        )->getResultArray();
        $kiriman = $db->query(
            "SELECT x.id, x.created_at, x.judged_at, c.code AS mata_kuliah, m.number AS pertemuan, " . self::NAMA_SOAL . ", x.language, x.status,
                    IF({$tampak}, x.verdict, NULL) AS verdict, IF({$tampak}, x.passed, NULL) AS passed, IF({$tampak}, x.total, NULL) AS total,
                    IF({$tampak}, x.score, NULL) AS score, x.late, x.late_factor, x.practice, IF({$tampak}, x.duration_ms, NULL) AS duration_ms, x.source
             FROM submissions x " . self::SOAL . " WHERE x.user_id = ? ORDER BY x.id DESC LIMIT {$ambil}",
            [$userId],
        )->getResultArray();
        $draf = $db->query(
            'SELECT c.code AS mata_kuliah, m.number AS pertemuan, ' . self::NAMA_SOAL . ', x.updated_at, x.source
             FROM drafts x ' . self::SOAL . " WHERE x.user_id = ? ORDER BY x.updated_at DESC LIMIT {$ambil}",
            [$userId],
        )->getResultArray();
        // Peristiwa integritas tidak selalu menunjuk soal, jadi LEFT JOIN; `detail` tidak diambil.
        $integritas = $db->query(
            "SELECT x.created_at, x.kind, " . self::NAMA_SOAL . "
             FROM integrity_events x LEFT JOIN meeting_problems mp ON mp.id = x.meeting_problem_id LEFT JOIN problems p ON p.id = mp.problem_id LEFT JOIN meetings m ON m.id = mp.meeting_id
             WHERE x.user_id = ? ORDER BY x.id DESC LIMIT {$ambil}",
            [$userId],
        )->getResultArray();
        // Tahap 10b (KONTRAK.md §17). `data` pemberitahuan tidak diambil: ia memuat id dosen yang mereset kata sandi.
        $notifikasi = $db->query("SELECT created_at, kind, read_at FROM notifications WHERE user_id = ? ORDER BY id DESC LIMIT {$ambil}", [$userId])->getResultArray();
        // Pertanyaan klarifikasinya sendiri beserta jawaban untuknya; penjawabnya tidak disebut.
        $klarifikasi = $db->query(
            "SELECT x.created_at, c.code AS mata_kuliah, m.number AS pertemuan, " . self::NAMA_SOAL . ", x.question AS pertanyaan, x.answer AS jawaban,
                    x.answered_at, (x.visibility = 'class') AS terbit_ke_kelas
             FROM clarifications x " . self::SOAL . " WHERE x.user_id = ? ORDER BY x.id DESC LIMIT {$ambil}",
            [$userId],
        )->getResultArray();
        // Jawaban yang ia tulis sebagai dosen: jawabannya saja — pertanyaannya milik orang lain.
        $jawaban = $db->query(
            "SELECT x.answered_at, c.code AS mata_kuliah, m.number AS pertemuan, " . self::NAMA_SOAL . ", x.answer AS jawaban, (x.visibility = 'class') AS terbit_ke_kelas
             FROM clarifications x " . self::SOAL . " WHERE x.answered_by = ? ORDER BY x.id DESC LIMIT {$ambil}",
            [$userId],
        )->getResultArray();
        $pengumuman = $db->query(
            "SELECT x.created_at, c.code AS mata_kuliah, k.name AS kelas, x.title AS judul, x.body AS isi, x.withdrawn_at
             FROM announcements x JOIN classes k ON k.id = x.class_id JOIN courses c ON c.id = k.course_id
             WHERE x.author_id = ? ORDER BY x.id DESC LIMIT {$ambil}",
            [$userId],
        )->getResultArray();
        $panel = $db->query('SELECT panels FROM ui_prefs WHERE user_id = ?', [$userId])->getRowArray();

        $isi = [
            'pendaftaran'          => $pendaftaran,
            'mengajar'             => $mengajar,
            'mengampu'             => $mengampu,
            'nilai_pertemuan'      => Ekspor::potong('nilai_pertemuan', $nilaiPertemuan, $terpotong),
            'nilai_soal'           => Ekspor::potong('nilai_soal', $nilaiSoal, $terpotong),
            'kiriman'              => Ekspor::potong('kiriman', $kiriman, $terpotong),
            'draf'                 => Ekspor::potong('draf', $draf, $terpotong),
            'peristiwa_integritas' => Ekspor::potong('peristiwa_integritas', $integritas, $terpotong),
            'notifikasi'           => Ekspor::potong('notifikasi', $notifikasi, $terpotong),
            'klarifikasi'          => Ekspor::potong('klarifikasi', array_map(self::bool('terbit_ke_kelas'), $klarifikasi), $terpotong),
            'jawaban_klarifikasi'  => Ekspor::potong('jawaban_klarifikasi', array_map(self::bool('terbit_ke_kelas'), $jawaban), $terpotong),
            'pengumuman'           => Ekspor::potong('pengumuman', $pengumuman, $terpotong),
            'preferensi_tampilan'  => $panel === null ? null : json_decode((string) $panel['panels'], true),
        ];
        // Waktu ke ISO 8601 UTC; tanggal (graduated_on) dibiarkan.
        foreach ($isi as $nama => $daftar) {
            if (! is_array($daftar) || $nama === 'preferensi_tampilan') {
                continue;
            }
            foreach ($daftar as $i => $baris) {
                foreach (['created_at', 'updated_at', 'judged_at', 'reached_at', 'assigned_at', 'read_at', 'answered_at', 'withdrawn_at'] as $k) {
                    if (array_key_exists($k, $baris)) {
                        $isi[$nama][$i][$k] = Ekspor::waktu($baris[$k]);
                    }
                }
            }
        }

        return [$isi, $terpotong];
    }

    /**
     * Kolom 0/1 dari MySQL menjadi true/false di berkas.
     *
     * @return \Closure(array<string, mixed>): array<string, mixed>
     */
    private static function bool(string $kolom): \Closure
    {
        return static function (array $baris) use ($kolom): array {
            $baris[$kolom] = (int) $baris[$kolom] === 1;

            return $baris;
        };
    }
}
