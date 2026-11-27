<?php

declare(strict_types=1);

namespace App\Libraries;

use Lokandita\Auth\Ekspor;
use Lokandita\Auth\RosterDb;

/**
 * Isi ekspor data sendiri LokaUjian (KONTRAK.md §14). SETIAP kueri diikat ke id pengguna yang
 * diberikan pemanggil — pemilik sesi — dan tidak ada yang menerima id dari permintaan.
 *
 * Yang ada: pengerjaannya (waktu, cara kumpul, tambahan waktu), jawabannya sendiri menurut nomor
 * soal yang dilihatnya, peristiwa integritasnya, dan skor HANYA bila hasil kelasnya dirilis.
 * Yang tidak pernah ada: teks soal, opsi, kunci, gambar, poin per soal, dan baris orang lain.
 */
final class DataSaya
{
    /**
     * @return array{0: array<string, mixed>, 1: list<string>}
     */
    public static function susun(string $userId): array
    {
        $db        = db_connect();
        $terpotong = [];
        $batas     = Ekspor::BATAS + 1;

        $pengerjaan = $db->query(
            "SELECT a.id, a.urutan, a.started_at, a.submitted_at, a.submit_how, a.extra_minutes, a.reopened_minutes, a.score,
                    s.course_id, s.class_id, s.release_level, x.title,
                    (SELECT COALESCE(SUM(q.points), 0) FROM exam_questions q WHERE q.exam_id = x.id AND q.type IN ('benar_salah','pilihan_ganda')) AS poin_objektif
             FROM attempts a JOIN sessions s ON s.id = a.session_id JOIN exams x ON x.id = a.exam_id
             WHERE a.user_id = ? ORDER BY a.id DESC LIMIT {$batas}",
            [$userId],
        )->getResultArray();
        $pengerjaan = Ekspor::potong('pengerjaan', $pengerjaan, $terpotong);
        $matkul     = self::nama('roster_courses', 'code', array_column($pengerjaan, 'course_id'));
        $kelas      = self::nama('roster_classes', 'name', array_column($pengerjaan, 'class_id'));

        $daftar = [];
        $nomor  = []; // id pengerjaan => [id soal => nomor yang dilihat, huruf opsi]
        foreach ($pengerjaan as $a) {
            $baris = [
                'ujian' => $a['title'], 'mata_kuliah' => $matkul[(int) $a['course_id']] ?? null, 'kelas' => $kelas[(int) $a['class_id']] ?? null,
                'mulai' => Ekspor::waktu($a['started_at']), 'kumpul' => Ekspor::waktu($a['submitted_at']), 'cara_kumpul' => $a['submit_how'],
                'tambahan_menit' => (int) $a['extra_minutes'], 'buka_ulang_menit' => (int) $a['reopened_minutes'],
            ];
            // Skor hanya bila hasil kelas itu dirilis (tingkat mana pun) dan pengerjaannya sudah bernilai.
            if ($a['release_level'] !== null && $a['submitted_at'] !== null && $a['score'] !== null) {
                $baris['nilai_objektif'] = (float) $a['score'];
                $baris['poin_objektif']  = (float) $a['poin_objektif'];
            }
            $daftar[]             = $baris;
            $u                    = (array) json_decode((string) $a['urutan'], true);
            $nomor[(int) $a['id']] = ['soal' => array_flip(array_map('intval', (array) ($u['soal'] ?? []))), 'opsi' => (array) ($u['opsi'] ?? []), 'ujian' => $a['title']];
        }

        $jawaban = $db->query(
            "SELECT j.attempt_id, j.exam_question_id, j.option_id, j.benar, j.text, j.saved_at, q.type
             FROM answers j JOIN attempts a ON a.id = j.attempt_id JOIN exam_questions q ON q.id = j.exam_question_id
             WHERE a.user_id = ? ORDER BY j.attempt_id DESC, j.exam_question_id LIMIT {$batas}",
            [$userId],
        )->getResultArray();
        $jawaban = Ekspor::potong('jawaban', $jawaban, $terpotong);
        $isi     = [];
        foreach ($jawaban as $j) {
            $n = $nomor[(int) $j['attempt_id']] ?? null;
            if ($n === null) {
                continue; // pengerjaannya terpotong dari daftar di atas
            }
            $id = (int) $j['exam_question_id'];
            if ($j['type'] === 'benar_salah') {
                $teks = $j['benar'] === null ? null : ((int) $j['benar'] === 1 ? 'benar' : 'salah');
            } elseif ($j['type'] === 'pilihan_ganda') {
                // Huruf opsi sebagaimana mahasiswa itu melihatnya (urutan opsinya sendiri).
                $letak = $j['option_id'] === null ? false : array_search((int) $j['option_id'], array_map('intval', (array) ($n['opsi'][(string) $id] ?? [])), true);
                $teks  = $letak === false ? null : chr(65 + (int) $letak);
            } else {
                $teks = (string) ($j['text'] ?? '');
            }
            $isi[] = ['ujian' => $n['ujian'], 'nomor' => isset($n['soal'][$id]) ? $n['soal'][$id] + 1 : null, 'tipe' => $j['type'], 'jawaban' => $teks, 'disimpan' => Ekspor::waktu($j['saved_at'])];
        }
        usort($isi, static fn (array $x, array $y): int => [$x['ujian'], $x['nomor']] <=> [$y['ujian'], $y['nomor']]);

        $peristiwa = $db->query("SELECT created_at, kind FROM integrity_events WHERE user_id = ? ORDER BY id DESC LIMIT {$batas}", [$userId])->getResultArray();
        $peristiwa = Ekspor::potong('peristiwa_integritas', $peristiwa, $terpotong);

        // Staf: sesi yang dijalankannya dan ujian yang dibuatnya — judul dan waktu saja.
        $sesi = $db->query(
            "SELECT l.created_at, l.action, x.title FROM audit_log l
             LEFT JOIN sessions s ON s.id = CAST(l.target_id AS UNSIGNED) LEFT JOIN exams x ON x.id = s.exam_id
             WHERE l.actor_id = ? AND l.target_tipe = 'sesi' ORDER BY l.id DESC LIMIT {$batas}",
            [$userId],
        )->getResultArray();
        $sesi  = Ekspor::potong('sesi_dijalankan', $sesi, $terpotong);
        $ujian = $db->query("SELECT title, created_at FROM exams WHERE created_by = ? ORDER BY id DESC LIMIT {$batas}", [$userId])->getResultArray();
        $ujian = Ekspor::potong('ujian_dibuat', $ujian, $terpotong);

        return [[
            'pengerjaan'           => $daftar,
            'jawaban'              => $isi,
            'peristiwa_integritas' => array_map(static fn (array $e): array => ['created_at' => Ekspor::waktu($e['created_at']), 'kind' => $e['kind']], $peristiwa),
            'sesi_dijalankan'      => array_map(static fn (array $b): array => ['waktu' => Ekspor::waktu($b['created_at']), 'tindakan' => $b['action'], 'ujian' => $b['title']], $sesi),
            'ujian_dibuat'         => array_map(static fn (array $b): array => ['judul' => $b['title'], 'created_at' => Ekspor::waktu($b['created_at'])], $ujian),
        ], $terpotong];
    }

    /**
     * Kode mata kuliah atau nama kelas menurut id, dari view roster. Ini nama kelas dan mata
     * kuliah TEMPAT PENGERJAANNYA SENDIRI; tidak ada baris orang lain yang dibaca.
     *
     * @param list<mixed> $ids
     *
     * @return array<int, string>
     */
    private static function nama(string $view, string $kolom, array $ids): array
    {
        $ids = array_values(array_unique(array_map('intval', $ids)));
        if ($ids === []) {
            return [];
        }
        $peta = [];
        foreach (RosterDb::conn()->query("SELECT id, {$kolom} AS nama FROM {$view} WHERE id IN (" . implode(',', $ids) . ')')->fetch_all(MYSQLI_ASSOC) as $b) {
            $peta[(int) $b['id']] = (string) $b['nama'];
        }

        return $peta;
    }
}
