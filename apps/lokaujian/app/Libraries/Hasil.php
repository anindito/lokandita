<?php

declare(strict_types=1);

namespace App\Libraries;

use App\Policy\Akses;
use CodeIgniter\Database\BaseConnection;
use Lokandita\Auth\UserDirectory;

/**
 * Isi kedua berkas CSV jawaban dan berkas peristiwa integritas (SPEC-LOKAUJIAN.md §9.3, §12).
 *
 * Setiap fungsi menerima baris sesi yang SUDAH diambil lewat App\Libraries\Sesi (jadi sudah
 * diikat ke kelas yang boleh dilihat pemintanya) dan hanya membaca pengerjaan sesi-sesi itu.
 *
 *   PANJANG  satu baris per mahasiswa per soal, untuk setiap mahasiswa yang punya pengerjaan;
 *   LEBAR    satu baris per peserta kelas (mahasiswa AKTIF, termasuk yang tidak pernah mulai, dan
 *            pemilik pengerjaan yang sudah tidak di kelas itu), satu kolom per soal.
 *
 * Berkas panjang memuat KUNCI. Bentuk berkasnya (BOM, pemisah, penjagaan rumus) ada di Csv.
 */
final class Hasil
{
    private readonly BaseConnection $db;

    private readonly Kendali $kendali;

    private const WIB = 'Asia/Jakarta';

    public function __construct(Akses $akses, private readonly Roster $roster)
    {
        $this->db      = db_connect();
        $this->kendali = new Kendali($akses, $roster);
    }

    private static function wib(?string $utc): ?string
    {
        return $utc === null || $utc === '' ? null : (new \DateTimeImmutable($utc, new \DateTimeZone('UTC')))->setTimezone(new \DateTimeZone(self::WIB))->format('Y-m-d H:i:s');
    }

    /**
     * Soal beku ujian dalam urutan ujian, masing-masing dengan nomor kelompoknya dan huruf opsinya.
     *
     * @return array<int, array<string, mixed>> id soal beku => soal
     */
    private function soal(int $ujianId): array
    {
        $soal     = [];
        $kelompok = 0;
        foreach (Ujian::beku($this->db, $ujianId) as $b) {
            if ($b['jenis'] === 'kelompok') {
                $kelompok++;
            }
            foreach ($b['jenis'] === 'soal' ? [$b['soal']] : $b['kelompok']['questions'] as $q) {
                $q['nomor']    = count($soal) + 1;
                $q['kelompok'] = $b['jenis'] === 'kelompok' ? $kelompok : null;
                $q['kunci']    = null;
                $q['huruf']    = [];
                foreach ($q['options'] as $i => $o) {
                    $q['huruf'][(int) $o['id']] = self::opsi($i, $o);
                    if ($o['correct']) {
                        $q['kunci'] = self::opsi($i, $o);
                    }
                }
                if ($q['type'] === 'benar_salah') {
                    $q['kunci'] = lang($q['answer'] ? 'Bank.benar' : 'Bank.salah');
                }
                $soal[(int) $q['id']] = $q;
            }
        }

        return $soal;
    }

    /**
     * Satu opsi pilihan ganda sebagai teks sel: hurufnya dalam urutan ujian dan teksnya; opsi
     * yang hanya berupa gambar ditulis dengan teks alternatif gambarnya.
     *
     * @param array<string, mixed> $o
     */
    private static function opsi(int $indeks, array $o): string
    {
        $teks = Markdown::ringkas((string) $o['text'], 300);
        if ($teks === '' && $o['image'] !== null) {
            $teks = '[' . lang('Hasil.gambar', [(string) $o['image']['alt']]) . ']';
        }

        return chr(65 + $indeks) . '. ' . $teks;
    }

    /**
     * Jawaban satu soal sebagai teks sel, dan apakah benar (null bila tidak dinilai otomatis).
     *
     * @param array<string, mixed>      $q
     * @param array<string, mixed>|null $j baris answers
     *
     * @return array{0: string, 1: ?int}
     */
    private static function jawaban(array $q, ?array $j): array
    {
        if ($q['type'] === 'benar_salah') {
            if ($j === null || $j['benar'] === null) {
                return ['', 0];
            }

            return [lang((int) $j['benar'] === 1 ? 'Bank.benar' : 'Bank.salah'), (int) (((int) $j['benar'] === 1) === (bool) $q['answer'])];
        }
        if ($q['type'] === 'pilihan_ganda') {
            if ($j === null || $j['option_id'] === null) {
                return ['', 0];
            }
            $teks = (string) ($q['huruf'][(int) $j['option_id']] ?? '');

            return [$teks, (int) ($teks !== '' && $teks === $q['kunci'])];
        }

        return [(string) ($j['text'] ?? ''), null];
    }

    /**
     * Semua yang dibutuhkan kedua berkas untuk satu sesi.
     *
     * @param array<string, mixed> $s
     *
     * @return array<string, mixed>
     */
    private function data(array $s): array
    {
        $sid        = (int) $s['id'];
        $kelas      = $this->roster->kelas((int) $s['class_id']);
        $peserta    = $this->kendali->peserta($s);
        $pengerjaan = [];
        $baris      = $this->db->query(
            'SELECT a.*, ' . Tenggat::berjalan() . ' AS berjalan, ' . Tenggat::tenggat() . ' AS tenggat
             FROM attempts a JOIN sessions s ON s.id = a.session_id JOIN exams x ON x.id = a.exam_id WHERE a.session_id = ?',
            [$sid],
        )->getResultArray();
        foreach ($baris as $a) {
            $a['jawab'] = [];
            $pengerjaan[(string) $a['user_id']] = $a;
        }
        $perId = array_column($pengerjaan, 'user_id', 'id');
        foreach ($this->db->query('SELECT j.* FROM answers j JOIN attempts a ON a.id = j.attempt_id WHERE a.session_id = ?', [$sid])->getResultArray() as $j) {
            $pengerjaan[(string) $perId[$j['attempt_id']]]['jawab'][(int) $j['exam_question_id']] = $j;
        }
        $peristiwa = [];
        $baris     = $this->db->query(
            'SELECT e.user_id, COUNT(*) AS n FROM integrity_events e
             WHERE e.session_id = ? OR e.attempt_id IN (SELECT a.id FROM attempts a WHERE a.session_id = ?) GROUP BY e.user_id',
            [$sid, $sid],
        )->getResultArray();
        foreach ($baris as $b) {
            $peristiwa[(string) $b['user_id']] = (int) $b['n'];
        }
        $menunggu = [];
        foreach ($this->db->query("SELECT user_id, SUM(minutes) AS m FROM time_grants WHERE session_id = ? AND kind = 'tambahan' GROUP BY user_id", [$sid])->getResultArray() as $b) {
            $menunggu[(string) $b['user_id']] = (int) $b['m'];
        }
        $ids   = array_map('strval', array_keys($peserta));
        $orang = UserDirectory::byIds($ids);
        usort($ids, static fn (string $x, string $y): int => [(string) ($orang[$x]['nomor_induk'] ?? ''), $x] <=> [(string) ($orang[$y]['nomor_induk'] ?? ''), $y]);

        return [
            'kelas' => (string) ($kelas['name'] ?? ''), 'peserta' => $peserta, 'urut' => $ids, 'orang' => $orang,
            'pengerjaan' => $pengerjaan, 'peristiwa' => $peristiwa, 'menunggu' => $menunggu,
        ];
    }

    /**
     * Berkas PANJANG.
     *
     * @param list<array<string, mixed>> $sesi sesi-sesi SATU ujian
     *
     * @return array{0: list<string>, 1: list<list<string|int|float|null>>}
     */
    public function panjang(array $sesi): array
    {
        $kepala = array_map(static fn (string $k): string => lang('Hasil.panjang.' . $k), [
            'kelas', 'nomorInduk', 'nama', 'nomor', 'nomorMahasiswa', 'idSoal', 'kelompok', 'tipe', 'teks', 'jawaban', 'kunci', 'benar', 'poinMaks', 'poin', 'disimpan',
        ]);
        $baris = [];
        foreach ($sesi as $s) {
            $soal = $this->soal((int) $s['exam_id']);
            $d    = $this->data($s);
            foreach ($d['urut'] as $u) {
                $a = $d['pengerjaan'][$u] ?? null;
                if ($a === null) {
                    continue;
                }
                $urutan = array_flip(array_map('intval', json_decode((string) $a['urutan'], true)['soal'] ?? []));
                foreach ($soal as $id => $q) {
                    $j                = $a['jawab'][$id] ?? null;
                    [$teks, $benar] = self::jawaban($q, $j);
                    $baris[]          = [
                        $d['kelas'], (string) ($d['orang'][$u]['nomor_induk'] ?? ''), (string) ($d['orang'][$u]['nama'] ?? ''),
                        (int) $q['nomor'], isset($urutan[$id]) ? $urutan[$id] + 1 : null, $id, $q['kelompok'],
                        lang('Bank.tipe.' . $q['type']), Markdown::ringkas((string) $q['text'], 200), $teks, $q['kunci'],
                        $benar, (float) $q['points'], $benar === null ? null : (float) ($benar === 1 ? $q['points'] : 0),
                        self::wib($j['saved_at'] ?? null),
                    ];
                }
            }
        }

        return [$kepala, $baris];
    }

    /**
     * Berkas LEBAR.
     *
     * @param list<array<string, mixed>> $sesi sesi-sesi SATU ujian
     *
     * @return array{0: list<string>, 1: list<list<string|int|float|null>>}
     */
    public function lebar(array $sesi): array
    {
        $kepala = array_map(static fn (string $k): string => lang('Hasil.lebar.' . $k), [
            'kelas', 'nomorInduk', 'nama', 'status', 'mulai', 'kumpul', 'cara', 'tambahan', 'bukaUlang', 'terjawab', 'skor', 'poinObjektif', 'poinManual', 'peristiwa',
        ]);
        $baris = [];
        $soal  = [];
        foreach ($sesi as $n => $s) {
            $soal     = $this->soal((int) $s['exam_id']);
            $tersedia = Nilai::tersedia($this->db, (int) $s['exam_id']);
            if ($n === 0) {
                foreach ($soal as $q) {
                    $kepala[] = lang('Hasil.lebar.soal', [(int) $q['nomor']]);
                }
            }
            $d = $this->data($s);
            foreach ($d['urut'] as $u) {
                $a      = $d['pengerjaan'][$u] ?? null;
                $jalan  = $a !== null && (int) $a['berjalan'] === 1;
                $status = lang('Hasil.status.' . ($a === null ? 'belumMulai' : ($jalan ? 'mengerjakan' : 'selesai')));
                if (! $d['peserta'][$u]) {
                    $status .= ' (' . lang('Hasil.tidakDiKelas') . ')';
                }
                $terjawab = 0;
                $sel      = [];
                foreach ($soal as $id => $q) {
                    [$teks] = self::jawaban($q, $a['jawab'][$id] ?? null);
                    $sel[]  = $teks;
                    $terjawab += $teks === '' ? 0 : 1;
                }
                $baris[] = array_merge([
                    $d['kelas'], (string) ($d['orang'][$u]['nomor_induk'] ?? ''), (string) ($d['orang'][$u]['nama'] ?? ''), $status,
                    self::wib($a['started_at'] ?? null),
                    // Pengerjaan yang tenggatnya lewat sudah berakhir pada tenggatnya, walau belum ditandai penjadwal.
                    $a === null || $jalan ? null : self::wib($a['submitted_at'] ?? $a['tenggat']),
                    $a === null || $jalan ? null : lang('Hasil.cara.' . ($a['submit_how'] ?? 'waktu_habis')),
                    $a === null ? ($d['menunggu'][$u] ?? 0) : (int) $a['extra_minutes'], $a === null ? 0 : (int) $a['reopened_minutes'],
                    $a === null ? null : $terjawab,
                    $a === null || $jalan || $a['score'] === null ? null : (float) $a['score'],
                    (float) $tersedia['otomatis'], (float) $tersedia['manual'], $d['peristiwa'][$u] ?? 0,
                ], $sel);
            }
        }

        return [$kepala, $baris];
    }

    /**
     * Peristiwa integritas satu sesi, terlama di atas.
     *
     * @param array<string, mixed> $s
     *
     * @return array{0: list<string>, 1: list<list<string|int|float|null>>}
     */
    public function peristiwa(array $s): array
    {
        $sid    = (int) $s['id'];
        $kelas  = (string) ($this->roster->kelas((int) $s['class_id'])['name'] ?? '');
        $kepala = array_map(static fn (string $k): string => lang('Hasil.peristiwa.' . $k), ['waktu', 'kelas', 'nomorInduk', 'nama', 'jenis', 'rincian']);
        $data   = $this->db->query(
            'SELECT e.user_id, e.kind, e.detail, e.created_at FROM integrity_events e
             WHERE e.session_id = ? OR e.attempt_id IN (SELECT a.id FROM attempts a WHERE a.session_id = ?) ORDER BY e.id',
            [$sid, $sid],
        )->getResultArray();
        $orang = UserDirectory::byIds(array_values(array_unique(array_map(static fn (array $b): string => (string) $b['user_id'], $data))));
        $baris = [];
        foreach ($data as $e) {
            $rinci = [];
            foreach ((array) json_decode((string) $e['detail'], true) as $k => $v) {
                if (is_scalar($v)) {
                    $rinci[] = $k . ': ' . (is_bool($v) ? ($v ? '1' : '0') : (string) $v);
                }
            }
            $u       = (string) $e['user_id'];
            $baris[] = [self::wib((string) $e['created_at']), $kelas, (string) ($orang[$u]['nomor_induk'] ?? ''), (string) ($orang[$u]['nama'] ?? ''), (string) $e['kind'], implode('; ', $rinci)];
        }

        return [$kepala, $baris];
    }
}
