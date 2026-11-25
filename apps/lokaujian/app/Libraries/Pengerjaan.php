<?php

declare(strict_types=1);

namespace App\Libraries;

use App\Policy\Akses;
use CodeIgniter\Database\BaseConnection;

/**
 * Pengerjaan dari sisi MAHASISWA (SPEC-LOKAUJIAN.md §7.4, §8): daftar ujiannya, mulai, halaman
 * ujian, simpan jawaban, kumpulkan.
 *
 * Hak akses ada di kueri: seorang mahasiswa hanya menemukan sesi kelas tempat ia AKTIF
 * (Akses::diKelasSaya) atau sesi tempat pengerjaannya sendiri sudah ada, dan hanya pengerjaan
 * dengan user_id dirinya. Alamat tidak pernah memuat id pengerjaan: yang ada hanya id sesi, dan
 * pengerjaannya dicari dari (ujian sesi itu, pengguna ini). Pengerjaan orang lain tidak bisa
 * dialamatkan sama sekali.
 *
 * Boleh-tidaknya menjawab diputuskan satu aturan, di App\Libraries\Tenggat.
 */
final class Pengerjaan
{
    public const ISIAN_MAKS  = 200;
    public const URAIAN_MAKS = 20_000;

    /** Simpan per pengerjaan per menit. Pengetik biasa menghasilkan jauh di bawah ini. */
    public const LAJU_PER_MENIT = 240;

    private readonly BaseConnection $db;

    private readonly string $u;

    public function __construct(private readonly Akses $akses)
    {
        $this->db = db_connect();
        $this->u  = $akses->idSql();
    }

    private function pilih(): string
    {
        $tenggat = Tenggat::tenggat();
        $akhir   = Tenggat::akhirSesi();

        return "
            SELECT s.id AS sesi_id, s.exam_id, s.class_id, s.course_id, s.open_at, s.close_at, s.started_at AS sesi_mulai, s.end_at,
                   s.ended_by, x.title, x.kind, x.mode, x.duration_minutes, x.instructions, x.shuffle_questions, x.shuffle_options,
                   (SELECT COUNT(*) FROM exam_questions q WHERE q.exam_id = x.id) AS jumlah_soal,
                   a.id AS attempt_id, a.started_at AS mulai, a.submitted_at, a.submit_how, a.extra_minutes, a.page_token, a.urutan,
                   IF(a.id IS NULL, NULL, {$tenggat}) AS tenggat,
                   IF(a.id IS NULL, NULL, " . Tenggat::sisa() . ') AS sisa,
                   IF(a.id IS NULL, 0, ' . Tenggat::berjalan() . ') AS berjalan,
                   ' . Tenggat::menerimaMulai() . " AS menerima,
                   IF(x.mode = 'dikendalikan_dosen' AND s.started_at IS NOT NULL, TIMESTAMPDIFF(SECOND, s.started_at, UTC_TIMESTAMP()), NULL) AS berlalu,
                   IF(x.mode = 'dikendalikan_dosen' AND s.started_at IS NOT NULL AND UTC_TIMESTAMP() >= {$akhir}, 1, 0) AS sesi_selesai,
                   (SELECT COUNT(*) FROM answers j WHERE j.attempt_id = a.id AND (j.option_id IS NOT NULL OR j.benar IS NOT NULL OR COALESCE(j.text, '') <> '')) AS terjawab
            FROM sessions s
            JOIN exams x ON x.id = s.exam_id
            LEFT JOIN attempts a ON a.exam_id = s.exam_id AND a.user_id = {$this->u}
            WHERE x.status = 'terbit'
              AND ((a.id IS NULL AND {$this->akses->diKelasSaya('s.class_id')}) OR a.session_id = s.id)";
    }

    /**
     * Ujian dan kuis mahasiswa ini: sesi kelas-kelasnya dan sesi tempat ia punya pengerjaan.
     * Tidak memuat apa pun dari ISI ujian.
     *
     * @return list<array<string, mixed>>
     */
    public function daftar(): array
    {
        if (! $this->akses->mahasiswa()) {
            return [];
        }

        return $this->db->query($this->pilih() . ' ORDER BY s.id DESC')->getResultArray();
    }

    /**
     * Satu sesi milik mahasiswa ini; null bila tidak ada atau bukan untuknya.
     *
     * @return array<string, mixed>|null
     */
    public function sesi(int $sesiId): ?array
    {
        if (! $this->akses->mahasiswa()) {
            return null;
        }

        return $this->db->query($this->pilih() . ' AND s.id = ?', [$sesiId])->getRowArray();
    }

    /**
     * Apakah mahasiswa ini punya pengerjaan di sesi itu. Dipakai alamat JSON sebelum memeriksa
     * bentuk kirimannya; sengaja ringan, karena dipanggil pada setiap simpan.
     */
    public function punya(int $sesiId): bool
    {
        return $this->akses->mahasiswa()
            && $this->db->query("SELECT 1 FROM attempts WHERE session_id = ? AND user_id = {$this->u}", [$sesiId])->getRowArray() !== null;
    }

    /**
     * Urutan soal dan opsi untuk satu mahasiswa (SPEC-LOKAUJIAN.md §6.2). Satuan acak adalah
     * kelompok dan soal lepas; di dalam kelompok urutan tidak pernah berubah. Benar/Salah tidak
     * punya opsi untuk diacak.
     *
     * @param list<array<string, mixed>> $butir isi beku ujian
     *
     * @return array{soal: list<int>, opsi: array<string, list<int>>}
     */
    public static function acak(array $butir, bool $acakSoal, bool $acakOpsi): array
    {
        if ($acakSoal) {
            $butir = self::kocok($butir);
        }
        $urutan = ['soal' => [], 'opsi' => []];
        foreach ($butir as $b) {
            foreach ($b['jenis'] === 'soal' ? [$b['soal']] : $b['kelompok']['questions'] as $s) {
                $urutan['soal'][] = (int) $s['id'];
                if ($s['type'] === 'pilihan_ganda') {
                    $ids                               = array_map(static fn (array $o): int => (int) $o['id'], $s['options']);
                    $urutan['opsi'][(string) $s['id']] = $acakOpsi ? self::kocok($ids) : $ids;
                }
            }
        }

        return $urutan;
    }

    /**
     * Fisher–Yates dengan random_int.
     *
     * @template T
     *
     * @param list<T> $larik
     *
     * @return list<T>
     */
    private static function kocok(array $larik): array
    {
        for ($i = count($larik) - 1; $i > 0; $i--) {
            $j                       = random_int(0, $i);
            [$larik[$i], $larik[$j]] = [$larik[$j], $larik[$i]];
        }

        return $larik;
    }

    /**
     * MULAI: membuat pengerjaan mahasiswa ini, atau mengembalikan yang sudah ada. Satu
     * pengerjaan per mahasiswa per ujian dijaga UNIQUE(exam_id, user_id): dua MULAI bersamaan
     * menghasilkan satu baris.
     *
     * @return 'ok'|'tidak_ada'|'belum' `belum`: sesi tidak (atau belum) menerima mulai
     */
    public function mulai(int $sesiId): string
    {
        $s = $this->sesi($sesiId);
        if ($s === null) {
            return 'tidak_ada';
        }
        if ($s['attempt_id'] !== null) {
            return 'ok';
        }
        if ((int) $s['menerima'] !== 1) {
            return 'belum';
        }
        $urutan = self::acak(Ujian::beku($this->db, (int) $s['exam_id']), (bool) $s['shuffle_questions'], (bool) $s['shuffle_options']);
        // Syarat "menerima mulai" diperiksa lagi DI DALAM pernyataan yang menyisipkan, dengan jam
        // MySQL yang sama: tidak ada celah antara memeriksa dan menulis.
        $this->db->query(
            "INSERT IGNORE INTO attempts (session_id, exam_id, class_id, user_id, started_at, urutan, page_token)
             SELECT s.id, s.exam_id, s.class_id, {$this->u}, UTC_TIMESTAMP(), ?, ?
             FROM sessions s JOIN exams x ON x.id = s.exam_id
             WHERE s.id = ? AND x.status = 'terbit' AND {$this->akses->diKelasSaya('s.class_id')} AND " . Tenggat::menerimaMulai(),
            [json_encode($urutan), bin2hex(random_bytes(16)), $sesiId],
        );
        $s = $this->sesi($sesiId);

        return $s !== null && $s['attempt_id'] !== null ? 'ok' : 'belum';
    }

    /**
     * Membuka HALAMAN UJIAN: hanya untuk pengerjaan yang berjalan. Halaman ini mengambil alih
     * pengerjaan (token halaman baru); halaman lama berhenti pada simpan berikutnya.
     *
     * @return array<string, mixed>|null sesi + `soal` (menurut urutan mahasiswa ini, masing-masing
     *                                   dengan `kelompok` dan `jawab`) + `kelompok` (bagian bersama)
     */
    public function bukaHalaman(int $sesiId): ?array
    {
        $s = $this->sesi($sesiId);
        if ($s === null || $s['attempt_id'] === null || (int) $s['berjalan'] !== 1) {
            return null;
        }
        $token = bin2hex(random_bytes(16));
        $this->db->query("UPDATE attempts SET page_token = ? WHERE id = ? AND user_id = {$this->u}", [$token, (int) $s['attempt_id']]);
        $s['page_token'] = $token;

        $urutan   = json_decode((string) $s['urutan'], true, 8, JSON_THROW_ON_ERROR);
        $soal     = [];
        $kelompok = [];
        foreach (Ujian::beku($this->db, (int) $s['exam_id']) as $b) {
            if ($b['jenis'] === 'kelompok') {
                $kelompok[(int) $b['kelompok']['id']] = ['id' => (int) $b['kelompok']['id'], 'text' => $b['kelompok']['text'], 'image' => $b['kelompok']['image']];
            }
            foreach ($b['jenis'] === 'soal' ? [$b['soal']] : $b['kelompok']['questions'] as $q) {
                unset($q['answer']); // kunci tidak pernah sampai ke view mahasiswa
                foreach ($q['options'] as &$o) {
                    unset($o['correct']);
                }
                unset($o);
                $soal[(int) $q['id']] = $q;
            }
        }
        $jawab = [];
        foreach ($this->db->query('SELECT * FROM answers WHERE attempt_id = ?', [(int) $s['attempt_id']])->getResultArray() as $j) {
            $jawab[(int) $j['exam_question_id']] = [
                'option_id' => $j['option_id'] === null ? null : (int) $j['option_id'], 'benar' => $j['benar'] === null ? null : (bool) $j['benar'],
                'text' => (string) ($j['text'] ?? ''), 'ragu' => (bool) $j['ragu'], 'counter' => (int) $j['counter'],
            ];
        }
        $s['soal'] = [];
        foreach ($urutan['soal'] as $id) {
            if (! isset($soal[$id])) {
                continue;
            }
            $q = $soal[$id];
            if (isset($urutan['opsi'][(string) $id])) {
                $perId = array_column($q['options'], null, 'id');
                $q['options'] = array_values(array_filter(array_map(static fn (int $o): ?array => $perId[$o] ?? null, $urutan['opsi'][(string) $id])));
            }
            $q['jawab']  = $jawab[$id] ?? null;
            $s['soal'][] = $q;
        }
        $s['kelompok'] = $kelompok;

        return $s;
    }

    /**
     * Baris pengerjaan mahasiswa ini di sesi itu, TERKUNCI, dengan keputusan aturan waktu.
     * Hanya baris attempts yang dikunci (FOR UPDATE OF a): baris sesi dan ujian dibaca bersama
     * oleh seluruh kelas dan tidak boleh menjadi antrean.
     *
     * @return array<string, mixed>|null
     */
    private function kunci(int $sesiId): ?array
    {
        return $this->db->query(
            'SELECT a.id, a.exam_id, a.page_token, a.submitted_at, a.urutan, ' . Tenggat::bolehSimpan() . ' AS boleh, ' . Tenggat::berjalan() . ' AS berjalan, '
            . Tenggat::sisa() . " AS sisa
             FROM attempts a JOIN sessions s ON s.id = a.session_id JOIN exams x ON x.id = a.exam_id
             WHERE a.session_id = ? AND a.user_id = {$this->u} FOR UPDATE OF a",
            [$sesiId],
        )->getRowArray();
    }

    /**
     * SIMPAN satu jawaban.
     *
     * Urutannya: (1) pengerjaan ini milik pengguna ini; (2) halaman pengirim masih memegangnya;
     * (3) aturan waktu (Tenggat::bolehSimpan); (4) soal itu soal ujian ini dan nilainya sah;
     * (5) ditulis hanya bila pencacahnya lebih besar daripada yang tersimpan. Jawaban "tersimpan"
     * baru dikirim sesudah COMMIT.
     *
     * @param mixed $nilai benar_salah: 'benar'|'salah'|null; pilihan_ganda: id opsi|null; isian/uraian: string
     *
     * @return array{kode: string, sisa?: int, disimpan?: string, n?: int}
     *         kode: ok | usang | tidak_ada | diambil_alih | dikumpulkan | waktu_habis | soal | nilai
     */
    public function simpan(int $sesiId, string $token, int $soalId, mixed $nilai, bool $ragu, int $pencacah): array
    {
        $this->db->transBegin();
        try {
            $hasil = $this->simpanDalam($sesiId, $token, $soalId, $nilai, $ragu, $pencacah);
            in_array($hasil['kode'], ['ok', 'usang'], true) ? $this->db->transCommit() : $this->db->transRollback();

            return $hasil;
        } catch (\Throwable $e) {
            $this->db->transRollback();

            throw $e;
        }
    }

    /**
     * @return array{kode: string, sisa?: int, disimpan?: string, n?: int}
     */
    private function simpanDalam(int $sesiId, string $token, int $soalId, mixed $nilai, bool $ragu, int $pencacah): array
    {
        $a = $this->kunci($sesiId);
        if ($a === null) {
            return ['kode' => 'tidak_ada'];
        }
        if (! hash_equals((string) $a['page_token'], $token)) {
            return ['kode' => 'diambil_alih'];
        }
        if ((int) $a['boleh'] !== 1) {
            return ['kode' => $a['submitted_at'] !== null ? 'dikumpulkan' : 'waktu_habis'];
        }
        $q = $this->db->query('SELECT id, type FROM exam_questions WHERE id = ? AND exam_id = ?', [$soalId, (int) $a['exam_id']])->getRowArray();
        if ($q === null || $pencacah < 1) {
            return ['kode' => 'soal'];
        }
        $opsi  = null;
        $benar = null;
        $teks  = null;
        if ($q['type'] === 'benar_salah') {
            if ($nilai !== null && $nilai !== 'benar' && $nilai !== 'salah') {
                return ['kode' => 'nilai'];
            }
            $benar = $nilai === null ? null : (int) ($nilai === 'benar');
        } elseif ($q['type'] === 'pilihan_ganda') {
            if ($nilai !== null) {
                if (! is_int($nilai) || $this->db->query('SELECT id FROM exam_options WHERE id = ? AND exam_question_id = ?', [$nilai, $soalId])->getRowArray() === null) {
                    return ['kode' => 'nilai'];
                }
                $opsi = $nilai;
            }
        } else {
            if (! is_string($nilai) || ! mb_check_encoding($nilai, 'UTF-8')) {
                return ['kode' => 'nilai'];
            }
            $teks = str_replace(["\r\n", "\r"], "\n", $nilai);
            if ($q['type'] === 'isian') {
                $teks = str_replace("\n", ' ', $teks);
            }
            if (mb_strlen($teks) > ($q['type'] === 'isian' ? self::ISIAN_MAKS : self::URAIAN_MAKS)) {
                return ['kode' => 'nilai'];
            }
        }
        // Simpan yang lebih tua tidak pernah menimpa yang lebih baru: baris hanya berubah bila
        // pencacah kiriman ini lebih besar. `counter` diubah terakhir, karena MySQL menghitung
        // penugasan dari kiri ke kanan.
        $this->db->query(
            'INSERT INTO answers (attempt_id, exam_question_id, option_id, benar, text, ragu, counter, saved_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, UTC_TIMESTAMP()) AS baru
             ON DUPLICATE KEY UPDATE
                option_id = IF(baru.counter > answers.counter, baru.option_id, answers.option_id),
                benar     = IF(baru.counter > answers.counter, baru.benar, answers.benar),
                text      = IF(baru.counter > answers.counter, baru.text, answers.text),
                ragu      = IF(baru.counter > answers.counter, baru.ragu, answers.ragu),
                saved_at  = IF(baru.counter > answers.counter, baru.saved_at, answers.saved_at),
                counter   = GREATEST(answers.counter, baru.counter)',
            [(int) $a['id'], $soalId, $opsi, $benar, $teks, (int) $ragu, $pencacah],
        );
        $j = (array) $this->db->query('SELECT counter, saved_at FROM answers WHERE attempt_id = ? AND exam_question_id = ?', [(int) $a['id'], $soalId])->getRowArray();
        $this->db->query('UPDATE attempts SET last_saved_at = UTC_TIMESTAMP() WHERE id = ?', [(int) $a['id']]);

        return [
            'kode'     => (int) $j['counter'] === $pencacah ? 'ok' : 'usang',
            'sisa'     => (int) $a['sisa'],
            'n'        => (int) $j['counter'],
            'disimpan' => (new \DateTimeImmutable((string) $j['saved_at'], new \DateTimeZone('UTC')))->setTimezone(new \DateTimeZone('Asia/Jakarta'))->format('H:i:s'),
        ];
    }

    /**
     * KUMPULKAN. Pengerjaan yang sudah berakhir (dikumpulkan atau lewat tenggat) dibiarkan apa
     * adanya; jawabannya memang sudah masuk.
     *
     * @return 'ok'|'tidak_ada'
     */
    public function kumpulkan(int $sesiId): string
    {
        $this->db->transBegin();
        try {
            $a = $this->kunci($sesiId);
            if ($a === null) {
                $this->db->transRollback();

                return 'tidak_ada';
            }
            if ((int) $a['berjalan'] === 1) {
                $this->db->query("UPDATE attempts SET submitted_at = UTC_TIMESTAMP(), submit_how = 'mahasiswa' WHERE id = ?", [(int) $a['id']]);
            }
            $this->db->transCommit();

            return 'ok';
        } catch (\Throwable $e) {
            $this->db->transRollback();

            throw $e;
        }
    }

    /**
     * Keadaan untuk halaman menunggu dan halaman ujian (polling). Tidak memuat apa pun dari isi ujian.
     *
     * @return array<string, mixed>|null
     */
    public function keadaan(int $sesiId, string $token = ''): ?array
    {
        $s = $this->sesi($sesiId);
        if ($s === null) {
            return null;
        }
        $k = ['menerima' => (int) $s['menerima'] === 1, 'sesi_selesai' => (int) $s['sesi_selesai'] === 1, 'pengerjaan' => null];
        if ($s['attempt_id'] !== null) {
            $k['pengerjaan'] = [
                'berjalan'     => (int) $s['berjalan'] === 1,
                'sisa'         => (int) $s['sisa'],
                // Dikendalikan dosen tanpa waktu akhir: yang ditampilkan waktu berlalu, bukan hitung mundur.
                'tanpa_akhir'  => $s['mode'] === 'dikendalikan_dosen' && $s['end_at'] === null,
                'berlalu'      => $s['berlalu'] === null ? null : (int) $s['berlalu'],
                'diakhiri'     => $s['ended_by'] !== null,
                'tambahan'     => (int) $s['extra_minutes'],
                'dikumpulkan'  => $s['submitted_at'] !== null,
                'diambil_alih' => $token !== '' && ! hash_equals((string) $s['page_token'], $token),
            ];
        }

        return $k;
    }

    /**
     * Mencatat peristiwa integritas pengerjaan mahasiswa ini (tahap U3: paste_blocked dan
     * page_taken_over). Mengembalikan false bila ia tidak punya pengerjaan di sesi itu.
     *
     * @param array<string, mixed> $detail
     */
    public function peristiwa(int $sesiId, string $jenis, array $detail, ?string $ip, ?string $agen): bool
    {
        $a = $this->db->query("SELECT id FROM attempts WHERE session_id = ? AND user_id = {$this->u}", [$sesiId])->getRowArray();
        if ($a === null) {
            return false;
        }
        // Halaman lama yang diambil alih dicatat sekali per halaman, walau ia mencoba menyimpan berkali-kali.
        if ($jenis === 'page_taken_over') {
            $ada = $this->db->query(
                "SELECT id FROM integrity_events WHERE attempt_id = ? AND kind = 'page_taken_over' AND JSON_UNQUOTE(JSON_EXTRACT(detail, '$.halaman')) = ?",
                [(int) $a['id'], (string) ($detail['halaman'] ?? '')],
            )->getRowArray();
            if ($ada !== null) {
                return true;
            }
        }
        $this->db->query(
            "INSERT INTO integrity_events (user_id, attempt_id, session_id, kind, detail, ip, user_agent, created_at) VALUES ({$this->u}, ?, ?, ?, ?, ?, ?, UTC_TIMESTAMP())",
            [(int) $a['id'], $sesiId, $jenis, json_encode($detail, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), $ip, $agen === null ? null : substr($agen, 0, 255)],
        );

        return true;
    }
}
