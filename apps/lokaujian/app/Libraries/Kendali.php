<?php

declare(strict_types=1);

namespace App\Libraries;

use App\Policy\Akses;
use CodeIgniter\Database\BaseConnection;
use Lokandita\Auth\UserDirectory;

/**
 * Kendali dosen atas satu sesi yang sedang atau sudah berjalan (SPEC-LOKAUJIAN.md §7.3, §10):
 * tampilan langsung per mahasiswa, tambahan waktu, dan buka ulang untuk satu mahasiswa.
 *
 * Hak aksesnya sama dengan menjalankan sesi: setiap fungsi mengambil sesinya lewat Sesi::lihat(),
 * yang mengikat kueri ke kelas yang boleh dilihat pengguna ini. Sesi kelas lain = tidak ada.
 * Mahasiswa yang boleh dikenai tindakan hanyalah PESERTA sesi itu: mahasiswa AKTIF kelasnya
 * (dibaca dari roster dengan syarat hak yang sama) dan pemilik pengerjaan di sesi itu.
 *
 * Kedua tindakan hanya MENAMBAH waktu, dicatat di time_grants dan di log audit dalam transaksi
 * yang sama. Aturan waktunya sendiri tetap di App\Libraries\Tenggat.
 */
final class Kendali
{
    public const TAMBAH_MAKS = 240;
    public const BUKA_MAKS   = 600;
    public const ALASAN_MAKS = 500;

    private readonly BaseConnection $db;

    private readonly Sesi $sesi;

    public function __construct(private readonly Akses $akses, private readonly Roster $roster)
    {
        $this->db   = db_connect();
        $this->sesi = new Sesi($akses);
    }

    /**
     * Peserta satu sesi: id pengguna => masih AKTIF di kelasnya atau tidak.
     *
     * @param array<string, mixed> $s baris Sesi::lihat
     *
     * @return array<string, bool>
     */
    public function peserta(array $s): array
    {
        $peserta = array_fill_keys($this->roster->mahasiswaAktif((int) $s['class_id']), true);
        foreach ($this->db->query('SELECT user_id FROM attempts WHERE session_id = ?', [(int) $s['id']])->getResultArray() as $b) {
            $peserta[(string) $b['user_id']] ??= false;
        }

        return $peserta;
    }

    /**
     * Pengerjaan seorang peserta di sesi ini, TERKUNCI, dengan keputusan aturan waktunya.
     *
     * @return array<string, mixed>|null
     */
    private function pengerjaan(int $sesiId, string $userId): ?array
    {
        return $this->db->query(
            'SELECT a.id, a.reopen_until, ' . Tenggat::berjalan() . ' AS berjalan
             FROM attempts a JOIN sessions s ON s.id = a.session_id JOIN exams x ON x.id = a.exam_id
             WHERE a.session_id = ? AND a.user_id = ? FOR UPDATE OF a',
            [$sesiId, $userId],
        )->getRowArray();
    }

    /** Sesi ini tidak lagi menerima MULAI dan tidak akan menerimanya lagi menurut waktunya. */
    private static function lewat(array $s): bool
    {
        return in_array($s['keadaan'], ['tertutup', 'selesai'], true);
    }

    /**
     * Satu tindakan dalam satu transaksi dengan baris sesi terkunci. $kerja mengembalikan kode
     * galat (tidak ada yang berubah) atau 'ok'. Dengan $terapkan = false semuanya diperiksa dan
     * tidak ada yang ditulis — itulah yang dipakai halaman konfirmasi.
     *
     * @param callable(array<string, mixed>): string $kerja
     */
    private function tindak(int $sesiId, bool $terapkan, callable $kerja): string
    {
        $this->db->transBegin();
        try {
            $s = $this->sesi->lihat($sesiId, true);
            if ($s === null) {
                $hasil = 'tidak_ada';
            } elseif ($s['ujian_status'] !== 'terbit') {
                $hasil = 'diarsipkan';
            } else {
                $hasil = $kerja($s);
            }
            $hasil === 'ok' && $terapkan ? $this->db->transCommit() : $this->db->transRollback();

            return $hasil;
        } catch (\Throwable $e) {
            $this->db->transRollback();

            throw $e;
        }
    }

    /**
     * TAMBAHAN WAKTU 1–240 menit untuk satu atau beberapa peserta, sebelum atau selama ujian,
     * berapa kali pun. Hanya menambah. Peserta yang belum mulai menerimanya saat ia mulai.
     * Pengerjaan yang sudah berakhir tidak bisa diberi tambahan: itu pekerjaan "buka ulang".
     * Satu peserta yang tidak memenuhi syarat menolak seluruh permintaan.
     *
     * @param list<string> $userIds
     *
     * @return 'ok'|'tidak_ada'|'diarsipkan'|'menit'|'kosong'|'asing'|'sudah_berakhir'
     */
    public function tambahWaktu(int $sesiId, array $userIds, int $menit, bool $terapkan = true): string
    {
        $userIds = array_values(array_unique($userIds));

        return $this->tindak($sesiId, $terapkan, function (array $s) use ($userIds, $menit, $terapkan): string {
            if ($menit < 1 || $menit > self::TAMBAH_MAKS) {
                return 'menit';
            }
            if ($userIds === []) {
                return 'kosong';
            }
            $peserta = $this->peserta($s);
            $sid     = (int) $s['id'];
            $punya   = [];
            foreach ($userIds as $u) {
                if (! isset($peserta[$u])) {
                    return 'asing';
                }
                $a = $this->pengerjaan($sid, $u);
                if ($a === null ? self::lewat($s) : (int) $a['berjalan'] !== 1) {
                    return 'sudah_berakhir';
                }
                $punya[$u] = $a === null ? null : (int) $a['id'];
            }
            if (! $terapkan) {
                return 'ok';
            }
            foreach ($punya as $u => $attemptId) {
                $this->db->query(
                    "INSERT INTO time_grants (session_id, user_id, kind, minutes, created_by, created_at) VALUES (?, ?, 'tambahan', ?, ?, UTC_TIMESTAMP())",
                    [$sid, $u, $menit, $this->akses->identitas()->userId],
                );
                if ($attemptId !== null) {
                    // Selama buka ulang, tenggatnya adalah reopen_until: itu yang dimajukan. NULL + selang = NULL.
                    $this->db->query(
                        'UPDATE attempts SET extra_minutes = extra_minutes + ?, reopen_until = reopen_until + INTERVAL ? MINUTE WHERE id = ?',
                        [$menit, $menit, $attemptId],
                    );
                }
                Audit::catat('waktu.ditambah', 'sesi', $sid, ['ujian' => (int) $s['exam_id'], 'mahasiswa' => $u, 'menit' => $menit, 'sudah_mulai' => $attemptId !== null], (int) $s['course_id'], (int) $s['class_id']);
            }

            return 'ok';
        });
    }

    /**
     * BUKA ULANG untuk satu peserta: 1–600 menit dihitung dari saat ini, dengan alasan.
     * Pengerjaan yang sudah berakhir dilanjutkan — pengerjaan yang SAMA, semua jawaban tetap;
     * peserta yang tidak pernah mulai boleh mulai walau sesinya sudah lewat. Sesinya sendiri
     * tidak berubah bagi yang lain.
     *
     * Ditolak selama kelas itu dirilis dengan kunci (`dirilis`): teman sekelasnya sudah bisa
     * membaca kunci. Tarik rilisnya dulu.
     *
     * @return 'ok'|'tidak_ada'|'diarsipkan'|'menit'|'alasan'|'asing'|'dirilis'|'masih_berjalan'|'masih_bisa_mulai'
     */
    public function bukaUlang(int $sesiId, string $userId, int $menit, string $alasan, bool $terapkan = true): string
    {
        $alasan = Aturan::rapikan($alasan);

        return $this->tindak($sesiId, $terapkan, function (array $s) use ($userId, $menit, $alasan, $terapkan): string {
            if ($menit < 1 || $menit > self::BUKA_MAKS) {
                return 'menit';
            }
            if ($alasan === '' || mb_strlen($alasan) > self::ALASAN_MAKS) {
                return 'alasan';
            }
            $peserta = $this->peserta($s);
            if (! isset($peserta[$userId])) {
                return 'asing';
            }
            if ($s['release_level'] === 'nilai_dan_kunci') {
                return 'dirilis';
            }
            $sid = (int) $s['id'];
            $a   = $this->pengerjaan($sid, $userId);
            if ($a !== null && (int) $a['berjalan'] === 1) {
                return 'masih_berjalan';
            }
            if ($a === null && ! self::lewat($s)) {
                return 'masih_bisa_mulai';
            }
            if (! $terapkan) {
                return 'ok';
            }
            $this->db->query(
                "INSERT INTO time_grants (session_id, user_id, kind, minutes, reason, until_at, created_by, created_at)
                 VALUES (?, ?, 'buka_ulang', ?, ?, UTC_TIMESTAMP() + INTERVAL ? MINUTE, ?, UTC_TIMESTAMP())",
                [$sid, $userId, $menit, $alasan, $menit, $this->akses->identitas()->userId],
            );
            if ($a !== null) {
                // Pengerjaan yang sama berjalan lagi; skornya dihitung ulang saat ia berakhir lagi.
                $this->db->query(
                    'UPDATE attempts SET submitted_at = NULL, submit_how = NULL, score = NULL, scored_at = NULL,
                            reopen_until = UTC_TIMESTAMP() + INTERVAL ? MINUTE, reopened_minutes = reopened_minutes + ? WHERE id = ?',
                    [$menit, $menit, (int) $a['id']],
                );
            }
            Audit::catat('pengerjaan.dibuka_ulang', 'sesi', $sid, ['ujian' => (int) $s['exam_id'], 'mahasiswa' => $userId, 'menit' => $menit, 'alasan' => $alasan, 'sudah_mulai' => $a !== null], (int) $s['course_id'], (int) $s['class_id']);

            return 'ok';
        });
    }

    /**
     * TAMPILAN LANGSUNG (SPEC-LOKAUJIAN.md §10): satu baris per peserta. Tidak memuat jawaban
     * maupun skor.
     *
     * @param array<string, mixed> $s baris Sesi::lihat
     *
     * @return array{ringkas: array<string, int>, baris: list<array<string, mixed>>, hadir_diketahui: bool}
     */
    public function langsung(array $s): array
    {
        $sid     = (int) $s['id'];
        $peserta = $this->peserta($s);
        $ids     = array_map('strval', array_keys($peserta));
        $total   = (int) $this->db->query('SELECT COUNT(*) AS n FROM exam_questions WHERE exam_id = ?', [(int) $s['exam_id']])->getRowArray()['n'];

        $pengerjaan = [];
        $baris      = $this->db->query(
            'SELECT a.user_id, a.submitted_at, a.submit_how, a.extra_minutes, a.reopened_minutes, a.last_saved_at, '
            . Tenggat::berjalan() . ' AS berjalan, ' . Tenggat::sisa() . " AS sisa,
                    (x.mode = 'dikendalikan_dosen' AND s.end_at IS NULL AND a.reopen_until IS NULL) AS tanpa_akhir,
                    (SELECT COUNT(*) FROM answers j WHERE j.attempt_id = a.id AND (j.option_id IS NOT NULL OR j.benar IS NOT NULL OR COALESCE(j.text, '') <> '')) AS terjawab
             FROM attempts a JOIN sessions s ON s.id = a.session_id JOIN exams x ON x.id = a.exam_id
             WHERE a.session_id = ?",
            [$sid],
        )->getResultArray();
        foreach ($baris as $b) {
            $pengerjaan[(string) $b['user_id']] = $b;
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
        // Tambahan waktu yang menunggu peserta yang belum mulai.
        $menunggu = [];
        $baris    = $this->db->query("SELECT user_id, SUM(minutes) AS menit FROM time_grants WHERE session_id = ? AND kind = 'tambahan' GROUP BY user_id", [$sid])->getResultArray();
        foreach ($baris as $b) {
            $menunggu[(string) $b['user_id']] = (int) $b['menit'];
        }

        $orang = UserDirectory::byIds($ids);
        $hadir = Hadir::baca($sid, $ids);
        $kini  = time();
        $wib   = new \DateTimeZone('Asia/Jakarta');
        $jam   = static fn (?string $utc, string $bentuk): ?string => $utc === null ? null : (new \DateTimeImmutable($utc, new \DateTimeZone('UTC')))->setTimezone($wib)->format($bentuk);

        $hasil   = [];
        $ringkas = ['peserta' => count($ids), 'hadir' => 0, 'mengerjakan' => 0, 'terputus' => 0, 'selesai' => 0];
        foreach ($ids as $u) {
            $a      = $pengerjaan[$u] ?? null;
            $h      = $hadir[$u] ?? null;
            $segar  = $h !== null && $kini - $h['waktu'] <= Hadir::SEGAR;
            $baris1 = [
                'id' => $u, 'nomor_induk' => (string) ($orang[$u]['nomor_induk'] ?? ''), 'nama' => (string) ($orang[$u]['nama'] ?? ''),
                'aktif' => $peserta[$u], 'status' => 'belum_hadir', 'tersambung' => null, 'sejak' => null, 'cara' => null,
                'terjawab' => 0, 'total' => $total, 'simpan' => null, 'sisa' => null, 'tambahan' => $menunggu[$u] ?? 0, 'buka_ulang' => 0,
                'peristiwa' => $peristiwa[$u] ?? 0,
            ];
            if ($a === null) {
                if ($segar) {
                    $baris1['status'] = 'menunggu';
                    $ringkas['hadir']++;
                }
            } else {
                $ringkas['hadir']++;
                $baris1['terjawab']   = (int) $a['terjawab'];
                $baris1['simpan']     = $jam($a['last_saved_at'], 'H:i:s');
                $baris1['tambahan']   = (int) $a['extra_minutes'];
                $baris1['buka_ulang'] = (int) $a['reopened_minutes'];
                if ((int) $a['berjalan'] === 1) {
                    $baris1['status'] = 'mengerjakan';
                    $baris1['sisa']   = (int) $a['tanpa_akhir'] === 1 ? null : (int) $a['sisa'];
                    $ringkas['mengerjakan']++;
                    if ($hadir !== null) {
                        $baris1['tersambung'] = $segar;
                        if (! $segar) {
                            $baris1['sejak'] = $h === null ? null : (new \DateTimeImmutable('@' . $h['waktu']))->setTimezone($wib)->format('H:i');
                            $ringkas['terputus']++;
                        }
                    }
                } else {
                    $baris1['status'] = 'selesai';
                    // Tenggat lewat tetapi penjadwal belum sempat menandai: tetap "waktu habis".
                    $baris1['cara'] = (string) ($a['submit_how'] ?? 'waktu_habis');
                    $ringkas['selesai']++;
                }
            }
            $baris1['teks'] = self::teks($baris1);
            $hasil[]        = $baris1;
        }
        usort($hasil, static fn (array $x, array $y): int => [$x['nomor_induk'], $x['id']] <=> [$y['nomor_induk'], $y['id']]);

        return [
            'ringkas'         => $ringkas,
            'ringkas_teks'    => lang('Hasil.langsung.ringkas', [$ringkas['hadir'], $ringkas['peserta'], $ringkas['mengerjakan'], $ringkas['terputus'], $ringkas['selesai']]),
            'baris'           => $hasil,
            'hadir_diketahui' => $hadir !== null,
        ];
    }

    /**
     * Teks tiap sel satu baris tampilan langsung, dalam bahasa pemintanya. Halaman sesi dan
     * jawaban JSON memakai fungsi yang sama, jadi skrip halaman hanya menyalin teks ini.
     *
     * @param array<string, mixed> $b
     *
     * @return array<string, string>
     */
    private static function teks(array $b): array
    {
        $status = match ($b['status']) {
            'mengerjakan' => $b['tersambung'] === null ? lang('Hasil.langsung.mengerjakan')
                : ($b['tersambung'] ? lang('Hasil.langsung.tersambung')
                    : ($b['sejak'] === null ? lang('Hasil.langsung.terputus') : lang('Hasil.langsung.terputusSejak', [$b['sejak']]))),
            'selesai'  => lang('Hasil.langsung.selesai', [lang('Hasil.cara.' . $b['cara'])]),
            'menunggu' => lang('Hasil.langsung.menunggu'),
            default    => lang('Hasil.langsung.belumHadir'),
        };
        if (! $b['aktif']) {
            $status .= ' (' . lang('Hasil.tidakDiKelas') . ')';
        }
        $sisa = '—';
        if ($b['status'] === 'mengerjakan') {
            $d    = max(0, (int) $b['sisa']);
            $sisa = $b['sisa'] === null ? lang('Hasil.langsung.tanpaAkhir') : sprintf('%d:%02d:%02d', intdiv($d, 3600), intdiv($d % 3600, 60), $d % 60);
        }
        $punya    = in_array($b['status'], ['mengerjakan', 'selesai'], true);
        $tambahan = $b['tambahan'] > 0 ? '+' . $b['tambahan'] : '—';
        if ($b['buka_ulang'] > 0) {
            $tambahan = ($b['tambahan'] > 0 ? $tambahan . ' · ' : '') . lang('Hasil.langsung.bukaUlang', [$b['buka_ulang']]);
        }

        return [
            'status'    => $status,
            'terjawab'  => $punya ? $b['terjawab'] . ' / ' . $b['total'] : '—',
            'simpan'    => $b['simpan'] ?? '—',
            'sisa'      => $sisa,
            'tambahan'  => $tambahan,
            'peristiwa' => (string) $b['peristiwa'],
        ];
    }
}
