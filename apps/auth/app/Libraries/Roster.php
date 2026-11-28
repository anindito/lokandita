<?php

declare(strict_types=1);

namespace App\Libraries;

use CodeIgniter\Database\BaseConnection;

/**
 * Roster akademik di inti Lokandita (KONTRAK.md §18): tahun ajaran, mata kuliah, kelas,
 * pengajar dan pendaftaran. Sejak tahap A2 hanya Akun Lokandita yang menulisnya.
 *
 * Semua kueri berjalan di koneksi bawaan (auth_app, skema identity) dengan nama skema
 * roster di depan nama tabel. Karena koneksinya satu, perubahan roster dan baris auditnya
 * (identity.audit_log, app = 'roster') berada dalam SATU transaksi.
 *
 * Kelas ini TIDAK memeriksa hak pengguna: semua pemanggilnya berada di belakang filter
 * `sesi:superadmin` (Routes.php) atau perintah spark dengan pelaku superadmin.
 */
final class Roster
{
    /** Nilai `app` pada baris audit roster. */
    public const APP = 'roster';

    /**
     * Nama skema roster dari ROSTER_SCHEMA (KONTRAK.md §10), sudah diberi tanda kutip balik.
     */
    public static function skema(): string
    {
        $nama = (string) getenv('ROSTER_SCHEMA');
        if (preg_match('/^roster(_test)?$/', $nama) !== 1) {
            throw new \RuntimeException('ROSTER_SCHEMA harus "roster" (atau "roster_test" dalam tes).');
        }

        return '`' . $nama . '`';
    }

    /**
     * Nama tabel roster lengkap dengan skemanya, untuk SQL dan untuk $db->table().
     */
    public static function t(string $tabel): string
    {
        if (! in_array($tabel, ['academic_years', 'courses', 'classes', 'class_teachers', 'enrollments', 'enrollment_moves'], true)) {
            throw new \LogicException("Bukan tabel roster: {$tabel}");
        }

        return self::skema() . '.`' . $tabel . '`';
    }

    public static function db(): BaseConnection
    {
        return db_connect();
    }

    /**
     * Memulai transaksi roster. Di dalam transaksi CodeIgniter biasanya menelan kegagalan kueri
     * dan baru membatalkan semuanya di transComplete() tanpa memberi tahu; di sini kegagalan
     * melempar DatabaseException (sesudah dibatalkan), supaya halaman tidak pernah melaporkan
     * "disimpan" untuk perubahan yang tidak terjadi.
     */
    public static function mulai(): void
    {
        self::db()->transException(true)->transStart();
    }

    /**
     * Baris audit sebuah perubahan roster. Dipanggil DI DALAM transaksi perubahannya.
     * Nama aksi sama dengan yang dulu ditulis LokaKode, supaya riwayatnya satu.
     *
     * target_id berisi id pengguna bila sasarannya orang; selain itu id baris roster.
     * Jenis sasaran, mata kuliah dan kelasnya ada di detail.
     *
     * @param array<string, mixed> $detail
     */
    public static function audit(
        string $aksi,
        string $pelakuId,
        string $targetTipe,
        int|string $targetId,
        array $detail = [],
        ?int $matkulId = null,
        ?int $kelasId = null,
    ): void {
        Audit::log($aksi, $pelakuId, (string) $targetId, $detail + [
            'target_tipe' => $targetTipe,
            'course_id'   => $matkulId,
            'class_id'    => $kelasId,
        ], self::APP);
    }

    // ---- Tahun ajaran ----

    /**
     * @return array<string, mixed>|null
     */
    public static function tahunAktif(): ?array
    {
        return self::db()->query('SELECT * FROM ' . self::t('academic_years') . " WHERE status = 'active'")->getRowArray();
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function tahun(int $id): ?array
    {
        return self::db()->query('SELECT * FROM ' . self::t('academic_years') . ' WHERE id = ?', [$id])->getRowArray();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function semuaTahun(): array
    {
        return self::db()->query('SELECT * FROM ' . self::t('academic_years') . ' ORDER BY starts_on DESC, id DESC')->getResultArray();
    }

    // ---- Mata kuliah ----

    /**
     * @return list<array<string, mixed>> dengan pengampu_nama dan pengampu_nomor (null bila akunnya tidak ada)
     */
    public static function daftarMatkul(): array
    {
        return self::db()->query('
            SELECT c.*, u.nama AS pengampu_nama, u.nomor_induk AS pengampu_nomor
            FROM ' . self::t('courses') . ' c LEFT JOIN users u ON u.id = c.owner_id
            ORDER BY c.code')->getResultArray();
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function matkul(int $id): ?array
    {
        return self::db()->query('
            SELECT c.*, u.nama AS pengampu_nama, u.nomor_induk AS pengampu_nomor
            FROM ' . self::t('courses') . ' c LEFT JOIN users u ON u.id = c.owner_id
            WHERE c.id = ?', [$id])->getRowArray();
    }

    // ---- Kelas ----

    /**
     * Kelas satu tahun ajaran; bila $matkulId diisi, hanya kelas mata kuliah itu.
     *
     * @return list<array<string, mixed>>
     */
    public static function daftarKelas(int $tahunId, ?int $matkulId = null): array
    {
        $ikat   = [$tahunId];
        $syarat = '';
        if ($matkulId !== null) {
            $syarat = ' AND k.course_id = ?';
            $ikat[] = $matkulId;
        }

        return self::db()->query('
            SELECT k.*, c.code AS matkul_kode, c.name AS matkul_nama, c.owner_id,
                   (SELECT COUNT(*) FROM ' . self::t('enrollments') . " e WHERE e.class_id = k.id AND e.status = 'AKTIF') AS jumlah_aktif,
                   (SELECT COUNT(*) FROM " . self::t('class_teachers') . ' ct WHERE ct.class_id = k.id) AS jumlah_pengajar
            FROM ' . self::t('classes') . ' k JOIN ' . self::t('courses') . " c ON c.id = k.course_id
            WHERE k.academic_year_id = ?{$syarat}
            ORDER BY c.code, k.name", $ikat)->getResultArray();
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function kelas(int $id): ?array
    {
        return self::db()->query('
            SELECT k.*, c.code AS matkul_kode, c.name AS matkul_nama, c.owner_id,
                   t.label AS tahun_label, t.status AS tahun_status,
                   u.nama AS pengampu_nama
            FROM ' . self::t('classes') . ' k
            JOIN ' . self::t('courses') . ' c ON c.id = k.course_id
            JOIN ' . self::t('academic_years') . ' t ON t.id = k.academic_year_id
            LEFT JOIN users u ON u.id = c.owner_id
            WHERE k.id = ?', [$id])->getRowArray();
    }

    /**
     * Pengajar per kelas, urut menurut waktu penugasan.
     *
     * @param list<int> $kelasIds
     *
     * @return array<int, list<array{user_id: string, nama: string|null, nomor_induk: string|null}>>
     */
    public static function pengajar(array $kelasIds): array
    {
        $kelasIds = array_values(array_unique(array_map('intval', $kelasIds)));
        if ($kelasIds === []) {
            return [];
        }
        $hasil = [];
        $baris = self::db()->query('
            SELECT ct.class_id, ct.user_id, u.nama, u.nomor_induk
            FROM ' . self::t('class_teachers') . ' ct LEFT JOIN users u ON u.id = ct.user_id
            WHERE ct.class_id IN ?
            ORDER BY ct.assigned_at, ct.user_id', [$kelasIds])->getResultArray();
        foreach ($baris as $b) {
            $hasil[(int) $b['class_id']][] = ['user_id' => (string) $b['user_id'], 'nama' => $b['nama'], 'nomor_induk' => $b['nomor_induk']];
        }

        return $hasil;
    }

    /**
     * Pendaftaran satu kelas dengan data akunnya, urut nomor induk; akun yang tidak ada di akhir.
     *
     * @return list<array<string, mixed>>
     */
    public static function pendaftaranKelas(int $kelasId): array
    {
        return self::db()->query('
            SELECT e.*, u.nama, u.nomor_induk, u.status AS akun_status
            FROM ' . self::t('enrollments') . ' e LEFT JOIN users u ON u.id = e.user_id
            WHERE e.class_id = ?
            ORDER BY u.nomor_induk IS NULL, u.nomor_induk, e.id', [$kelasId])->getResultArray();
    }

    /**
     * Aplikasi pembaca roster yang BELUM boleh dimasuki tiap pengguna (app_access).
     *
     * @param list<string> $userIds
     *
     * @return array<string, list<string>> id pengguna => kode aplikasi yang belum diberi
     */
    public static function tanpaAkses(array $userIds): array
    {
        $userIds = array_values(array_unique($userIds));
        if ($userIds === []) {
            return [];
        }
        $punya = [];
        foreach (self::db()->table('app_access')->select('user_id, app')->whereIn('user_id', $userIds)->where('enabled', 1)->get()->getResultArray() as $a) {
            $punya[$a['user_id']][$a['app']] = true;
        }
        $hasil = [];
        foreach ($userIds as $id) {
            $kurang = array_values(array_filter(array_keys(self::PEMBACA), static fn ($app) => ! isset($punya[$id][$app])));
            if ($kurang !== []) {
                $hasil[$id] = $kurang;
            }
        }

        return $hasil;
    }

    /** Aplikasi yang membaca roster, dengan nama yang ditampilkan. */
    public const PEMBACA = ['lokakode' => 'LokaKode', 'lokaujian' => 'LokaUjian'];

    /**
     * Dosen aktif menurut nomor induk, atau kunci galat: tidakAda, bukanDosen, tidakAktif.
     *
     * @return array<string, mixed>|string
     */
    public static function dosenAktif(string $nomorInduk): array|string
    {
        $u = $nomorInduk === '' ? null : self::db()->query('SELECT id, nomor_induk, nama, global_role, status FROM users WHERE nomor_induk = ?', [$nomorInduk])->getRowArray();

        return match (true) {
            $u === null                   => 'tidakAda',
            $u['global_role'] !== 'dosen' => 'bukanDosen',
            $u['status'] !== 'active'     => 'tidakAktif',
            default                       => $u,
        };
    }

    // ---- Tanggal ----

    /**
     * Benar bila $s adalah tanggal kalender yang sah berformat YYYY-MM-DD.
     */
    public static function tanggalSah(string $s): bool
    {
        return preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $s, $m) === 1 && checkdate((int) $m[2], (int) $m[3], (int) $m[1]);
    }

    public static function tanggal(?string $tanggal): string
    {
        return $tanggal === null || $tanggal === '' ? '—' : date('d-m-Y', (int) strtotime($tanggal));
    }
}
