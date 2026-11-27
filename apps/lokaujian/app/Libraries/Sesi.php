<?php

declare(strict_types=1);

namespace App\Libraries;

use App\Policy\Akses;
use CodeIgniter\Database\BaseConnection;
use Lokandita\Auth\Waktu;

/**
 * Sesi dari sisi staf (SPEC-LOKAUJIAN.md §7): satu ujian terbit yang diberikan kepada satu kelas.
 *
 * Dua lingkup, keduanya diikat DI KUERI (App\Policy\Akses):
 *   mengatur  (memberikan ujian ke kelas, jadwal, menghapus sesi) — yang mengelola mata
 *             kuliahnya: Akses::diKelola();
 *   menjalankan (buka, tutup, mulai, atur akhir, akhiri, melihat halaman sesi) — yang melihat
 *             kelasnya, termasuk pengajarnya: Akses::diJalankan().
 * Tidak ada fungsi yang mengambil sesi hanya berdasarkan id.
 *
 * Keadaan sesi tidak disimpan: selalu diturunkan dari waktunya (App\Libraries\Tenggat).
 */
final class Sesi
{
    private readonly BaseConnection $db;

    public function __construct(private readonly Akses $akses)
    {
        $this->db = db_connect();
    }

    private const PILIH = "
        s.*, x.title, x.kind, x.mode, x.duration_minutes, x.status AS ujian_status, x.instructions,
        UTC_TIMESTAMP() AS kini,
        IF(x.mode = 'per_mahasiswa', NULL, %akhir%) AS akhir,
        (CASE
            WHEN x.mode = 'per_mahasiswa' THEN
                CASE WHEN %terbuka% THEN 'terbuka'
                     WHEN s.open_at IS NOT NULL AND s.open_at > UTC_TIMESTAMP() THEN 'terjadwal'
                     WHEN s.open_at IS NULL THEN 'belum_dijadwalkan'
                     ELSE 'tertutup' END
            WHEN s.started_at IS NULL THEN 'menunggu'
            WHEN %berlangsung% THEN IF(s.ended_by IS NULL, 'berlangsung', 'diakhiri')
            ELSE 'selesai' END) AS keadaan,
        %usai% AS usai";

    private static function pilih(): string
    {
        return strtr(self::PILIH, ['%akhir%' => Tenggat::akhirSesi(), '%terbuka%' => Tenggat::terbuka(), '%berlangsung%' => Tenggat::berlangsung(), '%usai%' => Tenggat::usai()]);
    }

    /**
     * Sesi yang boleh DIJALANKAN pengguna ini; null bila tidak ada atau di luar kelasnya.
     *
     * @return array<string, mixed>|null
     */
    public function lihat(int $id, bool $kunci = false): ?array
    {
        $s = $this->db->query(
            'SELECT ' . self::pilih() . " FROM sessions s JOIN exams x ON x.id = s.exam_id
             WHERE s.id = ? AND x.status <> 'draf' AND {$this->akses->diJalankan('s.class_id')}" . ($kunci ? ' FOR UPDATE' : ''),
            [$id],
        )->getRowArray();
        if ($s !== null) {
            $s['boleh_atur'] = in_array((int) $s['course_id'], $this->akses->idMatkulDiampu(), true);
        }

        return $s;
    }

    /**
     * Sesi-sesi satu ujian (bagi yang mengelola mata kuliahnya).
     *
     * @return list<array<string, mixed>>
     */
    public function untukUjian(int $matkulId, int $ujianId): array
    {
        return $this->db->query(
            'SELECT ' . self::pilih() . ", (SELECT COUNT(*) FROM attempts a WHERE a.session_id = s.id) AS pengerjaan
             FROM sessions s JOIN exams x ON x.id = s.exam_id
             WHERE s.exam_id = ? AND s.course_id = ? AND {$this->akses->diKelola('s.course_id')} ORDER BY s.id",
            [$ujianId, $matkulId],
        )->getResultArray();
    }

    /**
     * Sesi-sesi satu kelas yang ujiannya terbit (bagi yang melihat kelas itu).
     *
     * @return list<array<string, mixed>>
     */
    public function untukKelas(int $kelasId): array
    {
        return $this->db->query(
            'SELECT ' . self::pilih() . ", (SELECT COUNT(*) FROM attempts a WHERE a.session_id = s.id) AS pengerjaan
             FROM sessions s JOIN exams x ON x.id = s.exam_id
             WHERE s.class_id = ? AND x.status = 'terbit' AND {$this->akses->diJalankan('s.class_id')} ORDER BY s.id DESC",
            [$kelasId],
        )->getResultArray();
    }

    /**
     * Hitungan untuk halaman sesi: hadir (punya pengerjaan), sedang mengerjakan, sudah mengumpulkan.
     * Pengerjaan yang tenggatnya lewat dihitung selesai walau penjadwal belum menandainya.
     *
     * @return array{hadir: int, mengerjakan: int, selesai: int}
     */
    public function hitungan(int $sesiId): array
    {
        $b = (array) $this->db->query(
            'SELECT COUNT(*) AS hadir, COALESCE(SUM(' . Tenggat::berjalan() . '), 0) AS mengerjakan
             FROM attempts a JOIN sessions s ON s.id = a.session_id JOIN exams x ON x.id = a.exam_id WHERE a.session_id = ?',
            [$sesiId],
        )->getRowArray();

        return ['hadir' => (int) $b['hadir'], 'mengerjakan' => (int) $b['mengerjakan'], 'selesai' => (int) $b['hadir'] - (int) $b['mengerjakan']];
    }

    /**
     * Waktu setempat dari form (`datetime-local`, Asia/Jakarta) menjadi UTC; null bila kosong;
     * false bila bukan waktu yang sah.
     */
    public static function keUtc(string $lokal): string|false|null
    {
        $lokal = trim($lokal);
        if ($lokal === '') {
            return null;
        }
        $t = \DateTimeImmutable::createFromFormat('!Y-m-d\TH:i', substr($lokal, 0, 16), new \DateTimeZone('Asia/Jakarta'));
        if ($t === false || $t->format('Y-m-d\TH:i') !== substr($lokal, 0, 16)) {
            return false;
        }

        return $t->setTimezone(new \DateTimeZone('UTC'))->format('Y-m-d H:i:s');
    }

    /** Kebalikan keUtc(), untuk mengisi kembali form. */
    public static function keLokal(?string $utc): string
    {
        if ($utc === null || $utc === '') {
            return '';
        }

        return (new \DateTimeImmutable($utc, new \DateTimeZone('UTC')))->setTimezone(new \DateTimeZone('Asia/Jakarta'))->format('Y-m-d\TH:i');
    }

    /**
     * Jadwal per_mahasiswa yang sah: buka wajib, tutup sesudah buka.
     */
    private static function jadwalSah(string|false|null $buka, string|false|null $tutup): bool
    {
        return is_string($buka) && is_string($tutup) && $tutup > $buka;
    }

    /**
     * Memberikan ujian terbit kepada satu kelas mata kuliah itu. $kelas adalah baris roster yang
     * sudah dibaca pemanggil DENGAN syarat hak (Roster::kelas), jadi kelas mata kuliah lain tidak
     * pernah sampai ke sini.
     *
     * @param array<string, mixed> $kelas
     *
     * @return int|string id sesi, atau `tidak_ada`, `bukan_terbit`, `kelas_asing`, `sudah_ada`, `jadwal`
     */
    public function buat(int $matkulId, int $ujianId, array $kelas, string|false|null $buka, string|false|null $tutup): int|string
    {
        $this->db->transBegin();
        try {
            $hasil = $this->buatDalam($matkulId, $ujianId, $kelas, $buka, $tutup);
            is_int($hasil) ? $this->db->transCommit() : $this->db->transRollback();

            return $hasil;
        } catch (\Throwable $e) {
            $this->db->transRollback();

            throw $e;
        }
    }

    /**
     * @param array<string, mixed> $kelas
     */
    private function buatDalam(int $matkulId, int $ujianId, array $kelas, string|false|null $buka, string|false|null $tutup): int|string
    {
        $u = $this->db->query("SELECT * FROM exams WHERE id = ? AND course_id = ? AND {$this->akses->diKelola()} FOR UPDATE", [$ujianId, $matkulId])->getRowArray();
        if ($u === null) {
            return 'tidak_ada';
        }
        if ($u['status'] !== 'terbit') {
            return 'bukan_terbit';
        }
        if ((int) $kelas['course_id'] !== $matkulId || $kelas['archived_at'] !== null || ($kelas['tahun_status'] ?? '') !== 'active') {
            return 'kelas_asing';
        }
        if ($this->db->query('SELECT id FROM sessions WHERE exam_id = ? AND class_id = ?', [$ujianId, (int) $kelas['id']])->getRowArray() !== null) {
            return 'sudah_ada';
        }
        $per = $u['mode'] === 'per_mahasiswa';
        if ($per && ! self::jadwalSah($buka, $tutup)) {
            return 'jadwal';
        }
        $kini = Waktu::kini();
        $this->db->table('sessions')->insert([
            'exam_id' => $ujianId, 'course_id' => $matkulId, 'class_id' => (int) $kelas['id'],
            'open_at' => $per ? $buka : null, 'close_at' => $per ? $tutup : null,
            'created_by' => $this->akses->identitas()->userId, 'created_at' => $kini, 'updated_at' => $kini,
        ]);
        $id = (int) $this->db->insertID();
        Audit::catat('sesi.dibuat', 'sesi', $id, ['ujian' => $ujianId, 'judul' => $u['title'], 'buka' => $per ? $buka : null, 'tutup' => $per ? $tutup : null], $matkulId, (int) $kelas['id']);

        return $id;
    }

    /**
     * Satu tindakan atas sesi, dalam satu transaksi dengan baris sesi terkunci.
     *
     * @param callable(array<string, mixed>): (array{0: string, 1: array<string, mixed>, 2: array<string, mixed>}|string) $kerja
     *        menerima sesi; mengembalikan kode galat, atau [aksi audit, kolom yang diubah, detail audit]
     */
    private function tindak(int $id, bool $harusAtur, callable $kerja): string
    {
        $this->db->transBegin();
        try {
            $s = $this->lihat($id, true);
            if ($s === null || ($harusAtur && ! $s['boleh_atur'])) {
                $this->db->transRollback();

                return 'tidak_ada';
            }
            // Ujian yang diarsipkan tidak dijalankan lagi; datanya tetap bisa dilihat.
            if ($s['ujian_status'] !== 'terbit') {
                $this->db->transRollback();

                return 'diarsipkan';
            }
            $hasil = $kerja($s);
            if (is_string($hasil)) {
                $this->db->transRollback();

                return $hasil;
            }
            [$aksi, $ubah, $detail] = $hasil;
            if ($ubah !== []) {
                $set  = [];
                $ikat = [];
                foreach ($ubah as $kolom => $nilai) {
                    // Nilai `sql:…` adalah ungkapan waktu tulisan kode ini sendiri (jam MySQL), bukan masukan.
                    if (is_string($nilai) && str_starts_with($nilai, 'sql:')) {
                        $set[] = "{$kolom} = " . substr($nilai, 4);
                    } else {
                        $set[]  = "{$kolom} = ?";
                        $ikat[] = $nilai;
                    }
                }
                $ikat[] = $id;
                $this->db->query('UPDATE sessions SET ' . implode(', ', $set) . ', updated_at = UTC_TIMESTAMP() WHERE id = ?', $ikat);
            }
            Audit::catat($aksi, 'sesi', $id, $detail + ['ujian' => (int) $s['exam_id']], (int) $s['course_id'], (int) $s['class_id']);
            $this->db->transCommit();

            return 'ok';
        } catch (\Throwable $e) {
            $this->db->transRollback();

            throw $e;
        }
    }

    /**
     * Mengubah jadwal sesi per_mahasiswa (yang mengelola mata kuliahnya).
     */
    public function jadwal(int $id, string|false|null $buka, string|false|null $tutup): string
    {
        return $this->tindak($id, true, static function (array $s) use ($buka, $tutup): array|string {
            if ($s['mode'] !== 'per_mahasiswa') {
                return 'mode';
            }
            if (! self::jadwalSah($buka, $tutup)) {
                return 'jadwal';
            }

            return ['sesi.dijadwalkan', ['open_at' => $buka, 'close_at' => $tutup], ['buka' => $buka, 'tutup' => $tutup]];
        });
    }

    /**
     * Membuka sesi per_mahasiswa dengan tangan: terbuka mulai sekarang. Waktu tutup yang masih
     * di depan dipertahankan; yang sudah lewat dikosongkan (terbuka sampai ditutup).
     */
    public function buka(int $id): string
    {
        return $this->tindak($id, false, static function (array $s): array|string {
            if ($s['mode'] !== 'per_mahasiswa') {
                return 'mode';
            }
            if ($s['keadaan'] === 'terbuka') {
                return 'sudah';
            }

            return ['sesi.dibuka', ['open_at' => 'sql:UTC_TIMESTAMP()', 'close_at' => 'sql:IF(close_at > UTC_TIMESTAMP(), close_at, NULL)'], ['dengan' => 'tangan']];
        });
    }

    /**
     * Menutup sesi per_mahasiswa dengan tangan: tidak ada MULAI baru. Yang sedang mengerjakan
     * tetap sampai tenggatnya sendiri.
     */
    public function tutup(int $id): string
    {
        return $this->tindak($id, false, static function (array $s): array|string {
            if ($s['mode'] !== 'per_mahasiswa') {
                return 'mode';
            }
            if ($s['keadaan'] !== 'terbuka') {
                return 'sudah';
            }

            return ['sesi.ditutup', ['close_at' => 'sql:UTC_TIMESTAMP()'], ['dengan' => 'tangan']];
        });
    }

    /**
     * Ungkapan SQL untuk waktu akhir yang diminta: `menit` dari sekarang, atau jam dinding
     * Asia/Jakarta `jam` (HH:MM; bila jam itu hari ini sudah lewat, yang dimaksud besok).
     * null = tidak diisi; false = tidak sah.
     */
    private static function akhirDiminta(string $menit, string $jam): string|false|null
    {
        $menit = trim($menit);
        $jam   = trim($jam);
        if ($menit !== '') {
            if (preg_match('/^\d{1,3}$/', $menit) !== 1 || (int) $menit < 1 || (int) $menit > Tenggat::BATAS_JAM * 60) {
                return false;
            }

            return 'UTC_TIMESTAMP() + INTERVAL ' . (int) $menit . ' MINUTE';
        }
        if ($jam === '') {
            return null;
        }
        if (preg_match('/^([01]\d|2[0-3]):([0-5]\d)$/', $jam) !== 1) {
            return false;
        }
        $zona = new \DateTimeZone('Asia/Jakarta');
        $kini = new \DateTimeImmutable('now', $zona);
        $t    = \DateTimeImmutable::createFromFormat('!Y-m-d H:i', $kini->format('Y-m-d') . ' ' . $jam, $zona);
        if ($t === false) {
            return false;
        }
        if ($t <= $kini) {
            $t = $t->modify('+1 day');
        }

        return "'" . $t->setTimezone(new \DateTimeZone('UTC'))->format('Y-m-d H:i:s') . "'";
    }

    /**
     * MULAI sesi dikendalikan dosen: jam berjalan untuk seluruh kelas. Waktu akhir boleh ikut
     * ditetapkan; tanpa itu sesi berakhir sendiri 8 jam sesudah mulai.
     */
    public function mulai(int $id, string $menit = '', string $jam = ''): string
    {
        $akhir = self::akhirDiminta($menit, $jam);

        return $this->tindak($id, false, static function (array $s) use ($akhir, $menit, $jam): array|string {
            if ($s['mode'] !== 'dikendalikan_dosen') {
                return 'mode';
            }
            if ($s['keadaan'] !== 'menunggu') {
                return 'sudah';
            }
            if ($akhir === false) {
                return 'akhir';
            }
            $ubah = ['started_at' => 'sql:UTC_TIMESTAMP()'];
            if ($akhir !== null) {
                $ubah['end_at'] = 'sql:LEAST(' . $akhir . ', UTC_TIMESTAMP() + INTERVAL ' . Tenggat::BATAS_JAM . ' HOUR)';
            }

            return ['sesi.dimulai', $ubah, ['menit' => $menit !== '' ? (int) $menit : null, 'jam' => $jam !== '' ? $jam : null]];
        });
    }

    /**
     * Menetapkan atau mengubah waktu akhir sesi yang sedang berlangsung. Sesudah "Akhiri" waktu
     * akhir tidak bisa diubah lagi.
     */
    public function aturAkhir(int $id, string $menit, string $jam): string
    {
        $akhir = self::akhirDiminta($menit, $jam);

        return $this->tindak($id, false, static function (array $s) use ($akhir, $menit, $jam): array|string {
            if ($s['mode'] !== 'dikendalikan_dosen') {
                return 'mode';
            }
            if ($s['keadaan'] !== 'berlangsung') {
                return 'sudah';
            }
            if (! is_string($akhir)) {
                return 'akhir';
            }

            return ['sesi.akhir_ditetapkan', ['end_at' => 'sql:LEAST(' . $akhir . ', started_at + INTERVAL ' . Tenggat::BATAS_JAM . ' HOUR)'], ['menit' => $menit !== '' ? (int) $menit : null, 'jam' => $jam !== '' ? $jam : null, 'sebelumnya' => $s['end_at']]];
        });
    }

    /**
     * AKHIRI: sesi berakhir untuk semua 30 detik dari sekarang (atau pada waktu akhirnya sendiri
     * bila itu lebih dekat). Halaman mahasiswa mengumumkannya.
     */
    public function akhiri(int $id): string
    {
        $pelaku = $this->akses->identitas()->userId;

        return $this->tindak($id, false, static function (array $s) use ($pelaku): array|string {
            if ($s['mode'] !== 'dikendalikan_dosen') {
                return 'mode';
            }
            if ($s['keadaan'] !== 'berlangsung') {
                return 'sudah';
            }
            $batas = 'started_at + INTERVAL ' . Tenggat::BATAS_JAM . ' HOUR';

            return ['sesi.diakhiri', [
                'end_at'   => 'sql:LEAST(COALESCE(end_at, ' . $batas . '), ' . $batas . ', UTC_TIMESTAMP() + INTERVAL ' . Tenggat::UMUMKAN_DETIK . ' SECOND)',
                'ended_by' => $pelaku,
            ], ['sebelumnya' => $s['end_at']]];
        });
    }

    /**
     * Kelas-kelas LAIN yang menerima ujian yang sama dan sesinya belum usai (belum dimulai,
     * masih berjalan, atau masih ada yang mengerjakan). Merilis kunci selagi ada kelas seperti itu
     * perlu konfirmasi kedua (SPEC-LOKAUJIAN.md §11).
     *
     * @return list<int> id kelas
     */
    public function kelasBelumUsai(int $ujianId, int $kecualiSesi): array
    {
        $baris = $this->db->query(
            'SELECT s.class_id FROM sessions s JOIN exams x ON x.id = s.exam_id
             WHERE s.exam_id = ? AND s.id <> ? AND NOT ' . Tenggat::usai() . ' ORDER BY s.class_id',
            [$ujianId, $kecualiSesi],
        )->getResultArray();

        return array_map('intval', array_column($baris, 'class_id'));
    }

    /**
     * MERILIS hasil satu kelas (yang mengelola mata kuliahnya), pada tingkat `nilai` atau
     * `nilai_dan_kunci`. Hanya sesudah sesi kelas itu usai. Tingkat `nilai_dan_kunci` selagi kelas
     * lain belum usai mengembalikan `kelas_lain` kecuali $yakin.
     *
     * @return 'ok'|'tidak_ada'|'diarsipkan'|'tingkat'|'belum_usai'|'sudah'|'kelas_lain'
     */
    public function rilis(int $id, string $tingkat, bool $yakin): string
    {
        $pelaku = $this->akses->identitas()->userId;

        return $this->tindak($id, true, function (array $s) use ($tingkat, $yakin, $pelaku): array|string {
            if (! in_array($tingkat, ['nilai', 'nilai_dan_kunci'], true)) {
                return 'tingkat';
            }
            if ((int) $s['usai'] !== 1) {
                return 'belum_usai';
            }
            if ($s['release_level'] === $tingkat) {
                return 'sudah';
            }
            $lain = $tingkat === 'nilai_dan_kunci' ? $this->kelasBelumUsai((int) $s['exam_id'], (int) $s['id']) : [];
            if ($lain !== [] && ! $yakin) {
                return 'kelas_lain';
            }

            return ['hasil.dirilis', ['release_level' => $tingkat, 'released_at' => 'sql:UTC_TIMESTAMP()', 'released_by' => $pelaku], ['tingkat' => $tingkat, 'sebelumnya' => $s['release_level'], 'kelas_belum_usai' => $lain]];
        });
    }

    /**
     * MENARIK rilis: mahasiswa kelas itu kembali tidak melihat apa pun.
     */
    public function tarikRilis(int $id): string
    {
        return $this->tindak($id, true, static function (array $s): array|string {
            if ($s['release_level'] === null) {
                return 'sudah';
            }

            return ['hasil.ditarik', ['release_level' => null, 'released_at' => null, 'released_by' => null], ['tingkat' => $s['release_level']]];
        });
    }

    /**
     * Sesi-sesi satu ujian yang boleh DIJALANKAN pengguna ini — untuk ekspor CSV "semua kelas
     * yang saya lihat" (SPEC-LOKAUJIAN.md §12). Pengajar hanya mendapat kelasnya sendiri.
     *
     * @return list<array<string, mixed>>
     */
    public function dilihatUntukUjian(int $ujianId): array
    {
        return $this->db->query(
            'SELECT ' . self::pilih() . " FROM sessions s JOIN exams x ON x.id = s.exam_id
             WHERE s.exam_id = ? AND x.status <> 'draf' AND {$this->akses->diJalankan('s.class_id')} ORDER BY s.id",
            [$ujianId],
        )->getResultArray();
    }

    /**
     * Menghapus sesi yang belum punya pengerjaan (yang mengelola mata kuliahnya).
     *
     * @return 'ok'|'tidak_ada'|'ada_pengerjaan'
     */
    public function hapus(int $id): string
    {
        $this->db->transBegin();
        try {
            $s = $this->lihat($id, true);
            if ($s === null || ! $s['boleh_atur']) {
                $this->db->transRollback();

                return 'tidak_ada';
            }
            if ((int) $this->db->query('SELECT COUNT(*) AS n FROM attempts WHERE session_id = ?', [$id])->getRowArray()['n'] > 0) {
                $this->db->transRollback();

                return 'ada_pengerjaan';
            }
            $this->db->query('DELETE FROM sessions WHERE id = ?', [$id]);
            Audit::catat('sesi.dihapus', 'sesi', $id, ['ujian' => (int) $s['exam_id'], 'judul' => $s['title']], (int) $s['course_id'], (int) $s['class_id']);
            $this->db->transCommit();

            return 'ok';
        } catch (\Throwable $e) {
            $this->db->transRollback();

            throw $e;
        }
    }

    /**
     * Peristiwa integritas satu sesi, terbaru di atas (paling banyak 500). Pemanggil sudah
     * memastikan pengguna boleh menjalankan sesi itu (lihat()).
     *
     * @return list<array<string, mixed>>
     */
    public function peristiwa(int $sesiId): array
    {
        return $this->db->query(
            'SELECT e.id, e.user_id, e.kind, e.detail, e.created_at
             FROM integrity_events e
             WHERE e.session_id = ? OR e.attempt_id IN (SELECT a.id FROM attempts a WHERE a.session_id = ?)
             ORDER BY e.id DESC LIMIT 500',
            [$sesiId, $sesiId],
        )->getResultArray();
    }

    public function jumlahPeristiwa(int $sesiId): int
    {
        return (int) $this->db->query(
            'SELECT COUNT(*) AS n FROM integrity_events e WHERE e.session_id = ? OR e.attempt_id IN (SELECT a.id FROM attempts a WHERE a.session_id = ?)',
            [$sesiId, $sesiId],
        )->getRowArray()['n'];
    }
}
