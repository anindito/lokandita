<?php

declare(strict_types=1);

namespace App\Libraries\Soal;

use App\Libraries\Antrean;
use App\Libraries\Audit;
use App\Libraries\Waktu;
use CodeIgniter\Database\BaseConnection;

/**
 * Impor soal (SPEC.md §8.3, KONTRAK.md §7): dari berkas JSON sampai versi soal
 * berstatus PENDING_REVIEW. Dipakai oleh unggahan web, `spark soal:impor`, dan
 * suntingan satu soal.
 *
 *   mulai()    memeriksa bentuk berkas, lalu mengirim satu pekerjaan VALIDATE
 *              per soal ke judge. Tidak ada yang disimpan ke bank soal di sini.
 *   majukan()  untuk tiap soal yang validasinya sudah selesai: simpan versinya
 *              (diterima) atau catat alasannya (ditolak). Aman dipanggil
 *              berulang kali dan oleh beberapa permintaan sekaligus.
 *
 * Tidak ada proses latar di sisi web: majukan() dipanggil oleh halaman
 * kemajuan dan oleh perintah CLI. Impor yang halamannya ditutup selesai saat
 * dibuka lagi; sampai itu tidak ada yang sampai ke mahasiswa, karena versi
 * baru selalu menunggu tinjauan pengampu.
 *
 * Kelas ini TIDAK memeriksa hak akses; pemanggilnya (pengendali, perintah CLI)
 * yang memastikan pelakunya berhak.
 */
final class Impor
{
    public const MAKS_BYTE = 8 * 1024 * 1024;

    private readonly BaseConnection $db;

    public function __construct()
    {
        $this->db = db_connect();
    }

    /**
     * @return string id impor
     */
    public function mulai(string $isi, string $namaBerkas, string $pelakuId, string $sumber = 'web'): string
    {
        $id         = Antrean::uuid();
        $namaBerkas = mb_substr(basename(str_replace('\\', '/', $namaBerkas)), 0, 190);

        [$data, $galat] = $this->urai($isi);
        $pertemuan = null;
        if ($galat === []) {
            $galat = Skema::periksaBerkas($data);
        }
        if ($galat === []) {
            [$pertemuan, $galat] = $this->pertemuan($data);
        }
        if ($galat !== []) {
            $this->db->transStart();
            $this->db->table('imports')->insert([
                'id' => $id, 'source' => $sumber, 'file_name' => $namaBerkas, 'actor_id' => $pelakuId,
                'status' => 'DITOLAK', 'file_errors' => json_encode($galat, JSON_UNESCAPED_UNICODE),
                'created_at' => Waktu::kini(), 'finished_at' => Waktu::kini(),
            ]);
            Audit::catat('soal.impor.ditolak', 'import', $id, ['file_name' => $namaBerkas, 'source' => $sumber, 'errors' => count($galat)], null, null, $pelakuId);
            $this->db->transComplete();

            return $id;
        }

        $baris = PosisiJson::petakan($isi);
        $butir = [];
        $slugTerlihat = [];
        $konsepBahasa = $this->konsepBahasa($pertemuan['language']);
        foreach ($data['problems'] as $i => $mentah) {
            [$normal, $galatSoal] = Skema::periksaSoal($mentah, $pertemuan['language'], $konsepBahasa);
            $slug = is_array($mentah) && is_string($mentah['slug'] ?? null) ? mb_substr($mentah['slug'], 0, 80) : null;
            if ($slug !== null && $galatSoal === [] && isset($slugTerlihat[$slug])) {
                $galatSoal[] = ['field' => 'slug', 'kode' => 'slugGanda', 'argumen' => [$slug, (string) $slugTerlihat[$slug]]];
            }
            if ($slug !== null) {
                $slugTerlihat[$slug] ??= $i + 1;
            }
            $garis = [];
            foreach ($baris as $jalur => $nomor) {
                if (str_starts_with($jalur, "problems/{$i}/")) {
                    $garis[substr($jalur, strlen("problems/{$i}/"))] = $nomor;
                }
            }
            $butir[] = [
                'urutan' => $i + 1, 'slug' => $slug, 'json_line' => $baris["problems/{$i}"] ?? null,
                'normal' => $normal, 'galat' => $galatSoal, 'garis' => $garis,
            ];
        }

        $this->simpanButir($id, $sumber, $namaBerkas, $pelakuId, $pertemuan, $butir);
        $this->kirimValidasi($id);
        $this->majukan($id);

        return $id;
    }

    /**
     * Suntingan satu soal oleh pengampu atau superadmin: versi baru dari soal
     * yang sudah ada. Melewati pemeriksaan dan validasi yang sama dengan impor.
     *
     * @param array<string, mixed> $soal      baris problems (dengan meeting_id)
     * @param mixed                $mentah    soal dalam bentuk KONTRAK.md §7
     *
     * @return array{0: string|null, 1: list<array{field: string, kode: string, argumen: list<string>}>} id impor, atau galat bentuk
     */
    public function sunting(array $soal, mixed $mentah, string $pelakuId): array
    {
        $pertemuan = $this->db->query('SELECT m.*, c.code FROM meetings m JOIN courses c ON c.id = m.course_id WHERE m.id = ?', [$soal['meeting_id']])->getRowArray();
        [$normal, $galat] = Skema::periksaSoal($mentah, $pertemuan['language'], $this->konsepBahasa($pertemuan['language']));
        if ($galat === [] && $normal['slug'] !== $soal['slug']) {
            $galat[] = ['field' => 'slug', 'kode' => 'slugTetap', 'argumen' => [(string) $soal['slug']]];
        }
        if ($galat !== []) {
            return [null, $galat];
        }
        $id = Antrean::uuid();
        $this->simpanButir($id, 'sunting', $soal['slug'] . '.json', $pelakuId, $pertemuan, [
            ['urutan' => 1, 'slug' => $normal['slug'], 'json_line' => null, 'normal' => $normal, 'galat' => [], 'garis' => []],
        ]);
        $this->kirimValidasi($id);
        $this->majukan($id);

        return [$id, []];
    }

    /**
     * @return array{0: mixed, 1: list<array{field: string, kode: string, argumen: list<string>}>}
     */
    private function urai(string $isi): array
    {
        if (strlen($isi) > self::MAKS_BYTE) {
            return [null, [['field' => '', 'kode' => 'berkasBesar', 'argumen' => []]]];
        }
        if (str_starts_with($isi, "\xEF\xBB\xBF")) {
            $isi = substr($isi, 3);
        }
        try {
            $data = json_decode($isi, true, 32, JSON_THROW_ON_ERROR | JSON_BIGINT_AS_STRING);
        } catch (\JsonException $e) {
            return [null, [['field' => '', 'kode' => 'bukanJson', 'argumen' => [$e->getMessage()]]]];
        }
        // Larik berisi satu obyek diterima dan dibuka (KONTRAK.md §7).
        if (is_array($data) && array_is_list($data) && count($data) === 1) {
            $data = $data[0];
        }

        return [$data, []];
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return array{0: array<string, mixed>|null, 1: list<array{field: string, kode: string, argumen: list<string>}>}
     */
    private function pertemuan(array $data): array
    {
        $matkul = $this->db->query('SELECT id, code FROM courses WHERE code = ?', [$data['course']])->getRowArray();
        if ($matkul === null) {
            $ada = array_column($this->db->query('SELECT code FROM courses ORDER BY code')->getResultArray(), 'code');

            return [null, [['field' => 'course', 'kode' => 'courseTidakAda', 'argumen' => [(string) $data['course'], implode(', ', $ada) ?: '—']]]];
        }
        $p = $this->db->query('SELECT m.*, c.code FROM meetings m JOIN courses c ON c.id = m.course_id WHERE m.course_id = ? AND m.number = ?', [$matkul['id'], $data['meeting']])->getRowArray();
        if ($p === null) {
            return [null, [['field' => 'meeting', 'kode' => 'meetingTidakAda', 'argumen' => [(string) $data['meeting'], (string) $matkul['code']]]]];
        }
        if ($p['language'] !== $data['language']) {
            return [null, [['field' => 'language', 'kode' => 'languageBeda', 'argumen' => [(string) $p['language'], (string) $data['language'], (string) $data['meeting']]]]];
        }

        return [$p, []];
    }

    /**
     * @return list<string>
     */
    private function konsepBahasa(string $bahasa): array
    {
        return array_column($this->db->query('SELECT code FROM concepts WHERE FIND_IN_SET(?, languages) > 0', [$bahasa])->getResultArray(), 'code');
    }

    /**
     * Konsep yang diizinkan di pertemuan: konsep dasar mata kuliah dan konsep
     * semua pertemuan sampai dan termasuk pertemuan ini (KONTRAK.md §6).
     *
     * @param array<string, mixed> $pertemuan
     *
     * @return list<string>
     */
    private function konsepDiizinkan(array $pertemuan): array
    {
        $baris = $this->db->query('
            SELECT concept_code FROM course_base_concepts WHERE course_id = ?
            UNION
            SELECT mc.concept_code FROM meeting_concepts mc JOIN meetings m ON m.id = mc.meeting_id
            WHERE m.course_id = ? AND m.number <= ?', [$pertemuan['course_id'], $pertemuan['course_id'], $pertemuan['number']])->getResultArray();

        return array_values(array_column($baris, 'concept_code'));
    }

    /**
     * @param array<string, mixed>       $pertemuan
     * @param list<array<string, mixed>> $butir
     */
    private function simpanButir(string $id, string $sumber, string $namaBerkas, string $pelakuId, array $pertemuan, array $butir): void
    {
        $this->db->transStart();
        $this->db->table('imports')->insert([
            'id' => $id, 'source' => $sumber, 'file_name' => $namaBerkas, 'actor_id' => $pelakuId,
            'course_id' => $pertemuan['course_id'], 'meeting_id' => $pertemuan['id'], 'status' => 'MENUNGGU',
            'created_at' => Waktu::kini(),
        ]);
        foreach ($butir as $b) {
            $keadaan = 'MENUNGGU';
            $alasan  = null;
            if ($b['galat'] !== []) {
                $keadaan = 'DITOLAK';
                $alasan  = array_map(static fn (array $g): array => [
                    'code' => 'schema', 'kode' => $g['kode'], 'argumen' => $g['argumen'], 'field' => $g['field'],
                    'json_line' => $b['garis'][explode('.', $g['field'])[0]] ?? $b['json_line'],
                ], $b['galat']);
            } else {
                $sidik    = Skema::sidik($b['normal'], $pertemuan['language']);
                $terakhir = $this->db->query('
                    SELECT v.content_hash FROM problems p JOIN problem_versions v ON v.problem_id = p.id
                    WHERE p.meeting_id = ? AND p.slug = ? ORDER BY v.version DESC LIMIT 1', [$pertemuan['id'], $b['slug']])->getRowArray();
                if ($terakhir !== null && $terakhir['content_hash'] === $sidik) {
                    $keadaan = 'TIDAK_BERUBAH';
                }
            }
            $this->db->table('import_items')->insert([
                'import_id' => $id, 'urutan' => $b['urutan'], 'slug' => $b['slug'], 'json_line' => $b['json_line'],
                'state' => $keadaan,
                'data' => $keadaan === 'MENUNGGU' ? json_encode(['soal' => $b['normal'], 'garis' => $b['garis']], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null,
                'reasons' => $alasan === null ? null : json_encode($alasan, JSON_UNESCAPED_UNICODE),
            ]);
        }
        $this->db->transComplete();
    }

    /**
     * Mengirim pekerjaan VALIDATE untuk butir yang belum punya. Kunci unik per
     * butir membuat pengiriman ulang (setelah proses terputus) tidak menggandakan.
     */
    private function kirimValidasi(string $id): void
    {
        $impor = $this->db->query('SELECT i.*, m.language, m.course_id AS mc, m.number, m.id AS mid FROM imports i JOIN meetings m ON m.id = i.meeting_id WHERE i.id = ?', [$id])->getRowArray();
        if ($impor === null) {
            return;
        }
        $diizinkan = $this->konsepDiizinkan(['course_id' => $impor['mc'], 'number' => $impor['number']]);
        $butir     = $this->db->query("SELECT id, urutan, data FROM import_items WHERE import_id = ? AND state = 'MENUNGGU' AND job_id IS NULL ORDER BY urutan", [$id])->getResultArray();
        foreach ($butir as $b) {
            $soal = json_decode((string) $b['data'], true)['soal'];
            $job  = Antrean::kirim('VALIDATE', [
                'language' => $impor['language'], 'generator' => $soal['generator'], 'reference_solution' => $soal['reference_solution'],
                'samples_count' => $soal['samples_count'], 'hidden_count' => $soal['hidden_count'], 'time_limit_ms' => $soal['time_limit_ms'],
                'allowed_concepts' => $diizinkan, 'comparison_mode' => $soal['comparison_mode'],
            ], $impor['actor_id'], 'normal', "impor:{$id}:{$b['urutan']}");
            $this->db->query('UPDATE import_items SET job_id = ? WHERE id = ? AND job_id IS NULL', [$job, $b['id']]);
        }
    }

    /**
     * Menyelesaikan butir yang validasinya sudah dijawab judge.
     */
    public function majukan(string $id): void
    {
        $impor = $this->db->query('SELECT * FROM imports WHERE id = ?', [$id])->getRowArray();
        if ($impor === null || $impor['status'] !== 'MENUNGGU') {
            return;
        }
        $this->kirimValidasi($id); // butir yang pengirimannya terputus

        $butir = $this->db->query("
            SELECT it.id, j.status, j.result
            FROM import_items it JOIN judge_jobs j ON j.id = it.job_id
            WHERE it.import_id = ? AND it.state = 'MENUNGGU' AND j.status IN ('DONE', 'FAILED')
            ORDER BY it.urutan", [$id])->getResultArray();
        foreach ($butir as $b) {
            $this->selesaikanButir($impor, (int) $b['id'], (string) $b['status'], json_decode((string) $b['result'], true) ?? []);
        }

        // Semua butir selesai: tutup impor dan catat di log audit, tepat satu kali.
        $this->db->transStart();
        $kini = $this->db->query('SELECT status FROM imports WHERE id = ? FOR UPDATE', [$id])->getRowArray();
        $sisa = (int) $this->db->query("SELECT COUNT(*) AS n FROM import_items WHERE import_id = ? AND state = 'MENUNGGU'", [$id])->getRowArray()['n'];
        if ($kini['status'] === 'MENUNGGU' && $sisa === 0) {
            $this->db->query("UPDATE imports SET status = 'SELESAI', finished_at = ? WHERE id = ?", [Waktu::kini(), $id]);
            $hitung = $this->hitung($id);
            Audit::catat($impor['source'] === 'sunting' ? 'soal.disunting' : 'soal.impor', 'import', $id,
                ['file_name' => $impor['file_name'], 'source' => $impor['source'], 'meeting_id' => (int) $impor['meeting_id']] + $hitung,
                (int) $impor['course_id'], null, (string) $impor['actor_id']);
        }
        $this->db->transComplete();
    }

    /**
     * @param array<string, mixed> $impor
     * @param array<string, mixed> $hasil  judge_jobs.result
     */
    private function selesaikanButir(array $impor, int $butirId, string $statusJob, array $hasil): void
    {
        $this->db->transStart();
        // Kunci baris: dua permintaan yang berpacu tidak membuat dua versi.
        $butir = $this->db->query('SELECT * FROM import_items WHERE id = ? FOR UPDATE', [$butirId])->getRowArray();
        if ($butir === null || $butir['state'] !== 'MENUNGGU') {
            $this->db->transComplete();

            return;
        }
        $data  = json_decode((string) $butir['data'], true);
        $soal  = $data['soal'];
        $garis = $data['garis'];
        $dengan = static function (array $alasan) use ($garis, $butir): array {
            $alasan['json_line'] = isset($alasan['where']) ? ($garis[$alasan['where']] ?? $butir['json_line']) : $butir['json_line'];

            return $alasan;
        };

        if ($statusJob === 'DONE' && ($hasil['ok'] ?? false) === true) {
            $pertemuan = $this->db->query('SELECT language FROM meetings WHERE id = ?', [$impor['meeting_id']])->getRowArray();
            $this->db->query('INSERT IGNORE INTO problems (meeting_id, slug, created_at) VALUES (?, ?, ?)', [$impor['meeting_id'], $soal['slug'], Waktu::kini()]);
            $soalId = (int) $this->db->query('SELECT id FROM problems WHERE meeting_id = ? AND slug = ? FOR UPDATE', [$impor['meeting_id'], $soal['slug']])->getRowArray()['id'];
            $versi  = 1 + (int) $this->db->query('SELECT COALESCE(MAX(version), 0) AS v FROM problem_versions WHERE problem_id = ?', [$soalId])->getRowArray()['v'];
            $this->db->table('problem_versions')->insert([
                'problem_id' => $soalId, 'version' => $versi, 'status' => 'PENDING_REVIEW', 'language' => $pertemuan['language'],
                'difficulty' => $soal['difficulty'], 'comparison_mode' => $soal['comparison_mode'], 'time_limit_ms' => $soal['time_limit_ms'],
                'samples_count' => $soal['samples_count'], 'hidden_count' => $soal['hidden_count'],
                'params' => json_encode((object) $soal['params'], JSON_UNESCAPED_UNICODE),
                'translations' => json_encode($soal['translations'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'generator' => $soal['generator'], 'reference_solution' => $soal['reference_solution'],
                'concepts' => json_encode(array_values($hasil['concepts'] ?? [])),
                'required_in_submission' => json_encode($soal['required_concepts_in_submission']),
                'content_hash' => Skema::sidik($soal, $pertemuan['language']), 'import_id' => $impor['id'],
                'created_by' => $impor['actor_id'], 'created_at' => Waktu::kini(),
            ]);
            $versiId    = (int) $this->db->insertID();
            $peringatan = array_map($dengan, $hasil['warnings'] ?? []);
            $this->db->query("UPDATE import_items SET state = 'DITERIMA', problem_version_id = ?, reasons = ?, data = NULL WHERE id = ?",
                [$versiId, $peringatan === [] ? null : json_encode($peringatan, JSON_UNESCAPED_UNICODE), $butirId]);
        } else {
            $alasan = $statusJob === 'DONE'
                ? array_map($dengan, $hasil['reasons'] ?? [])
                : [$dengan(['code' => 'judge_' . ($hasil['error'] ?? 'unavailable'), 'message' => (string) ($hasil['message'] ?? '')])];
            $this->db->query("UPDATE import_items SET state = 'DITOLAK', reasons = ?, data = NULL WHERE id = ?", [json_encode($alasan, JSON_UNESCAPED_UNICODE), $butirId]);
        }
        $this->db->transComplete();
    }

    /**
     * @return array{total: int, accepted: int, rejected: int, unchanged: int, waiting: int}
     */
    public function hitung(string $id): array
    {
        $n = ['MENUNGGU' => 0, 'DITERIMA' => 0, 'DITOLAK' => 0, 'TIDAK_BERUBAH' => 0];
        foreach ($this->db->query('SELECT state, COUNT(*) AS n FROM import_items WHERE import_id = ? GROUP BY state', [$id])->getResultArray() as $b) {
            $n[$b['state']] = (int) $b['n'];
        }

        return ['total' => array_sum($n), 'accepted' => $n['DITERIMA'], 'rejected' => $n['DITOLAK'], 'unchanged' => $n['TIDAK_BERUBAH'], 'waiting' => $n['MENUNGGU']];
    }

    /**
     * Impor beserta butirnya, untuk halaman laporan dan CLI.
     *
     * @return array<string, mixed>|null
     */
    public function laporan(string $id): ?array
    {
        $impor = $this->db->query('
            SELECT i.*, c.code AS matkul_kode, m.number AS pertemuan_nomor, m.title AS pertemuan_judul
            FROM imports i LEFT JOIN courses c ON c.id = i.course_id LEFT JOIN meetings m ON m.id = i.meeting_id
            WHERE i.id = ?', [$id])->getRowArray();
        if ($impor === null) {
            return null;
        }
        $impor['file_errors'] = json_decode((string) ($impor['file_errors'] ?? '[]'), true) ?? [];
        $impor['hitung']      = $this->hitung($id);
        $impor['butir']       = [];
        $butir = $this->db->query('
            SELECT it.urutan, it.slug, it.json_line, it.state, it.reasons, it.problem_version_id, v.problem_id, v.version, j.progress
            FROM import_items it
            LEFT JOIN problem_versions v ON v.id = it.problem_version_id
            LEFT JOIN judge_jobs j ON j.id = it.job_id
            WHERE it.import_id = ? ORDER BY it.urutan', [$id])->getResultArray();
        foreach ($butir as $b) {
            $b['reasons']  = json_decode((string) ($b['reasons'] ?? '[]'), true) ?? [];
            $b['progress'] = json_decode((string) ($b['progress'] ?? 'null'), true);
            $impor['butir'][] = $b;
        }

        return $impor;
    }

    /**
     * Kalimat untuk satu alasan atau peringatan, dari berkas bahasa (sama di web dan CLI).
     *
     * @param array<string, mixed> $a
     */
    public static function kalimat(array $a): string
    {
        if (($a['code'] ?? '') === 'schema') {
            return lang('Soal.skema.' . $a['kode'], $a['argumen'] ?? []);
        }
        $kode = (string) ($a['code'] ?? '');
        $teks = lang('Soal.alasan.' . $kode, [
            (string) ($a['concept'] ?? ''), (string) ($a['construct'] ?? ''), (string) ($a['verdict'] ?? ''),
            (string) ($a['seed'] ?? ''), (string) ($a['index'] ?? ''), (string) ($a['message'] ?? ''),
        ]);

        // Kode yang belum punya kalimat tetap ditampilkan apa adanya, tidak disembunyikan.
        return $teks === 'Soal.alasan.' . $kode ? $kode . ': ' . (string) ($a['message'] ?? '') : $teks;
    }

    /**
     * Letak sebuah alasan: bagian soal, baris dan kolom di sumbernya, dan baris JSON.
     *
     * @param array<string, mixed> $a
     */
    public static function letak(array $a): string
    {
        $bagian = [];
        if (isset($a['where'])) {
            $bagian[] = lang('Soal.letak.' . $a['where']);
        } elseif (($a['field'] ?? '') !== '') {
            $bagian[] = (string) $a['field'];
        }
        if (isset($a['line'])) {
            $bagian[] = isset($a['column']) ? lang('Soal.letak.barisKolom', [(string) (int) $a['line'], (string) (int) $a['column']]) : lang('Soal.letak.baris', [(string) (int) $a['line']]);
        }
        if (isset($a['json_line'])) {
            $bagian[] = lang('Soal.letak.barisJson', [(string) (int) $a['json_line']]);
        }

        return implode(', ', $bagian);
    }
}
