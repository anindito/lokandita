<?php

declare(strict_types=1);

namespace App\Libraries;

use App\Policy\Akses;
use CodeIgniter\Database\BaseConnection;

/**
 * Peristiwa integritas satu kelas untuk dosen (SPEC.md §13.2, KONTRAK.md §17).
 *
 * Yang tampil: peristiwa mahasiswa yang terdaftar (AKTIF atau LULUS) di kelas itu, pada soal
 * mata kuliah kelas itu, ditambah peristiwa tanpa soal (penolakan SEB), sejak mahasiswa itu
 * terdaftar. Peristiwa mengikuti mahasiswanya: sesudah pindah kelas ia tampil di kelas baru.
 * Syarat lihat kelas (App\Policy\Akses) ada di dalam kueri.
 *
 * Sistem hanya melaporkan; yang memutuskan adalah dosen.
 */
final class Integritas
{
    public const JENIS = ['paste_blocked', 'typing_anomaly', 'seb_rejected', 'seb_terminated'];

    // SPEC.md §13.2: ≥ 300 karakter ditambahkan dengan laju ≥ 150 karakter per detik.
    public const KETIK_KARAKTER = 300;

    public const KETIK_PER_DETIK = 150;

    public const PER_HALAMAN = 100;

    public const EKSPOR_MAKS = 20000;

    private BaseConnection $db;

    public function __construct(private readonly Akses $akses)
    {
        $this->db = db_connect();
    }

    /**
     * Benar bila tambahan $karakter dalam $ms milidetik memenuhi kedua ambang.
     */
    public static function ketikanTakWajar(int $karakter, int $ms): bool
    {
        return $ms >= 0 && $karakter >= self::KETIK_KARAKTER && $karakter * 1000 >= self::KETIK_PER_DETIK * $ms;
    }

    /**
     * Saringan dari query string, hanya nilai yang sah.
     *
     * @param array<string, mixed> $get
     *
     * @return array{jenis: ?string, mahasiswa: ?string, pertemuan: ?int, dari: ?string, sampai: ?string}
     */
    public static function saringan(array $get): array
    {
        $teks  = static fn (string $k): string => is_string($get[$k] ?? null) ? $get[$k] : '';
        $tgl   = static fn (string $k): ?string => Waktu::tanggalSah($teks($k)) ? $teks($k) : null;

        return [
            'jenis'     => in_array($teks('jenis'), self::JENIS, true) ? $teks('jenis') : null,
            'mahasiswa' => preg_match('/^[0-9a-f-]{36}$/', $teks('mahasiswa')) === 1 ? $teks('mahasiswa') : null,
            'pertemuan' => (int) $teks('pertemuan') > 0 ? (int) $teks('pertemuan') : null,
            'dari'      => $tgl('dari'),
            'sampai'    => $tgl('sampai'),
        ];
    }

    /**
     * @param array{jenis: ?string, mahasiswa: ?string, pertemuan: ?int, dari: ?string, sampai: ?string} $s
     *
     * @return list<array<string, mixed>> terbaru lebih dulu
     */
    public function daftar(int $kelasId, array $s, int $batas, int $lewati = 0): array
    {
        $where = '';
        $ikat  = [$kelasId];
        if ($s['jenis'] !== null) {
            $where .= ' AND ie.kind = ?';
            $ikat[] = $s['jenis'];
        }
        if ($s['mahasiswa'] !== null) {
            $where .= ' AND ie.user_id = ?';
            $ikat[] = $s['mahasiswa'];
        }
        if ($s['pertemuan'] !== null) {
            $where .= ' AND m.id = ?';
            $ikat[] = $s['pertemuan'];
        }
        // Tanggal saringan adalah tanggal Asia/Jakarta; kolomnya UTC.
        if ($s['dari'] !== null) {
            $where .= ' AND ie.created_at >= ?';
            $ikat[] = Waktu::dariIsian($s['dari'] . 'T00:00');
        }
        if ($s['sampai'] !== null) {
            $where .= ' AND ie.created_at < ? + INTERVAL 1 DAY';
            $ikat[] = Waktu::dariIsian($s['sampai'] . 'T00:00');
        }
        $ikat[] = $batas;
        $ikat[] = $lewati;

        return $this->db->query("
            SELECT ie.id, ie.user_id, ie.kind, ie.detail, ie.ip, ie.user_agent, ie.created_at, ie.meeting_problem_id,
                   m.id AS pertemuan_id, m.number, p.slug, v.translations
            FROM enrollments e
            JOIN classes k ON k.id = e.class_id
            JOIN courses c ON c.id = k.course_id
            JOIN integrity_events ie ON ie.user_id = e.user_id AND ie.created_at >= e.created_at
            LEFT JOIN meeting_problems mp ON mp.id = ie.meeting_problem_id
            LEFT JOIN meetings m ON m.id = mp.meeting_id
            LEFT JOIN problems p ON p.id = mp.problem_id
            LEFT JOIN problem_versions v ON v.id = mp.problem_version_id
            WHERE k.id = ? AND e.status IN ('AKTIF', 'LULUS') AND {$this->akses->lihatKelas()}
              AND (ie.meeting_problem_id IS NULL OR m.course_id = k.course_id) {$where}
            ORDER BY ie.id DESC LIMIT ? OFFSET ?", $ikat)->getResultArray();
    }

    /**
     * Rincian sebuah peristiwa sebagai satu kalimat untuk dosen.
     */
    public static function uraian(string $jenis, ?string $detailJson): string
    {
        $d = json_decode((string) $detailJson, true);
        $d = is_array($d) ? $d : [];
        if ($jenis === 'paste_blocked') {
            $jalur = (string) ($d['jalur'] ?? '');

            return $jalur === '' ? '' : lang('Integritas.jalur.' . $jalur);
        }
        if ($jenis === 'typing_anomaly') {
            return lang('Integritas.ketik.' . (($d['sebab'] ?? '') === 'undo' ? 'undo' : 'ketik'), [
                (int) ($d['karakter'] ?? 0), angka(round(((int) ($d['ms'] ?? 0)) / 1000, 2)), (int) ($d['cps'] ?? 0),
            ]);
        }
        if ($jenis === 'seb_rejected' || $jenis === 'seb_terminated') {
            $alasan = (string) ($d['alasan'] ?? '');

            return trim(($alasan === '' ? '' : lang('Integritas.seb.' . $alasan)) . ' ' . (string) ($d['jalur'] ?? ''));
        }

        return '';
    }

    /**
     * Judul soal sebuah baris daftar(), atau '' untuk peristiwa tanpa soal.
     *
     * @param array<string, mixed> $b
     */
    public static function soal(array $b): string
    {
        if ($b['meeting_problem_id'] === null) {
            return '';
        }

        return (string) (Ruang::terjemahan(json_decode((string) $b['translations'], true) ?? [])['title'] ?? $b['slug']);
    }
}
