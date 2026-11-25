<?php

declare(strict_types=1);

namespace App\Policy;

use Lokandita\Auth\Identity;
use Lokandita\Auth\RosterDb;

/**
 * Satu-satunya tempat aturan "siapa boleh apa" LokaUjian (SPEC-LOKAUJIAN.md §3).
 *
 * Aturannya berbentuk syarat SQL yang ditempelkan ke kueri roster (App\Libraries\Roster),
 * sehingga baris di luar hak pengguna tidak pernah terbaca — bukan dibaca dulu lalu
 * disembunyikan. Peran global berasal dari LokaAuth. Peran lingkup diturunkan dari view
 * roster (KONTRAK.md §18) pada setiap permintaan; tidak ada kolom peran dan tidak ada yang
 * disimpan melewati satu permintaan:
 *   pengampu  = roster_courses.owner_id (juga bertindak sebagai pengajar semua kelasnya)
 *   pengajar  = roster_class_teachers
 *   mahasiswa = roster_enrollments berstatus AKTIF, kelas tidak diarsipkan, tahun ajaran aktif
 *
 * Alias yang dipakai syarat: `c` = roster_courses, `k` = roster_classes,
 * `e` = roster_enrollments, `t` = roster_academic_years. Kueri yang memakai syarat ini wajib
 * memakai alias yang sama.
 */
final class Akses
{
    private const SEMUA = '1 = 1';
    private const TIDAK = '1 = 0';

    private readonly string $u; // id pengguna, sudah di-escape dan berkutip untuk SQL

    /** @var list<int>|null id mata kuliah yang diampu; dibaca sekali per permintaan */
    private ?array $idDiampu = null;

    /** @var list<int>|null id kelas yang boleh dilihat staf ini; dibaca sekali per permintaan */
    private ?array $idKelasStaf = null;

    /** @var list<int>|null id kelas tempat pengguna ini mahasiswa; dibaca sekali per permintaan */
    private ?array $idKelasSaya = null;

    public function __construct(private readonly Identity $identitas)
    {
        $this->u = "'" . RosterDb::conn()->real_escape_string($identitas->userId) . "'";
    }

    public function identitas(): Identity
    {
        return $this->identitas;
    }

    public function superadmin(): bool
    {
        return $this->identitas->globalRole === 'superadmin';
    }

    public function dosen(): bool
    {
        return $this->identitas->globalRole === 'dosen';
    }

    /**
     * Superadmin atau dosen. Mahasiswa tidak pernah mendapat halaman staf.
     */
    public function staf(): bool
    {
        return $this->superadmin() || $this->dosen();
    }

    public function mahasiswa(): bool
    {
        return $this->identitas->globalRole === 'mahasiswa';
    }

    /**
     * Mata kuliah yang boleh DILIHAT: superadmin semua; dosen yang mengampunya atau mengajar
     * salah satu kelasnya. Pengajar hanya melihat kode, nama dan kelasnya sendiri di sana.
     */
    public function lihatMatkul(): string
    {
        if ($this->superadmin()) {
            return self::SEMUA;
        }
        if (! $this->dosen()) {
            return self::TIDAK;
        }

        return "(c.owner_id = {$this->u} OR EXISTS (
            SELECT 1 FROM roster_classes kx JOIN roster_class_teachers ctx ON ctx.class_id = kx.id
            WHERE kx.course_id = c.id AND ctx.user_id = {$this->u}))";
    }

    /**
     * Mata kuliah yang DIAMPU (bank soal, ujian, rilis hasil di tahap berikutnya): superadmin
     * semua; dosen hanya yang diampunya. Di tahap U1 dipakai untuk menandai peran di daftar.
     */
    public function ampuMatkul(): string
    {
        if ($this->superadmin()) {
            return self::SEMUA;
        }

        return $this->dosen() ? "c.owner_id = {$this->u}" : self::TIDAK;
    }

    /**
     * Kelas yang boleh DILIHAT: superadmin semua; pengampu semua kelas mata kuliahnya;
     * pengajar kelas yang ditugaskan kepadanya.
     */
    public function lihatKelas(): string
    {
        if ($this->superadmin()) {
            return self::SEMUA;
        }
        if (! $this->dosen()) {
            return self::TIDAK;
        }

        return "(c.owner_id = {$this->u} OR EXISTS (
            SELECT 1 FROM roster_class_teachers ctx WHERE ctx.class_id = k.id AND ctx.user_id = {$this->u}))";
    }

    /**
     * Kelas tempat pengguna ini MAHASISWA (SPEC-LOKAUJIAN.md §3): pendaftaran AKTIF miliknya,
     * kelas tidak diarsipkan, tahun ajaran aktif.
     */
    public function kelasMahasiswa(): string
    {
        if (! $this->mahasiswa()) {
            return self::TIDAK;
        }

        return "(e.user_id = {$this->u} AND e.status = 'AKTIF' AND k.archived_at IS NULL AND t.status = 'active')";
    }

    /**
     * id mata kuliah yang DIAMPU pengguna ini (superadmin: semua), dibaca dari view roster.
     *
     * LokaUjian tidak boleh men-join skemanya sendiri dengan roster (KONTRAK.md §18). Jadi untuk
     * tabel milik LokaUjian — bank soal, gambar, ujian — hak akses berbentuk daftar id ini:
     * setiap kueri pada tabel itu diikat ke daftarnya lewat diKelola(). Daftarnya dibaca dari
     * roster sekali per permintaan dan tidak disimpan melewatinya.
     *
     * @return list<int>
     */
    public function idMatkulDiampu(): array
    {
        if ($this->idDiampu === null) {
            $this->idDiampu = [];
            if ($this->staf()) {
                $hasil = RosterDb::conn()->query("SELECT c.id FROM roster_courses c WHERE {$this->ampuMatkul()} ORDER BY c.id");
                foreach ($hasil->fetch_all(MYSQLI_NUM) as $b) {
                    $this->idDiampu[] = (int) $b[0];
                }
            }
        }

        return $this->idDiampu;
    }

    /**
     * Syarat SQL untuk tabel LokaUjian yang punya kolom course_id: hanya baris mata kuliah yang
     * diampu pengguna ini. $kolom adalah nama kolom yang ditulis kode, bukan masukan pengguna.
     */
    public function diKelola(string $kolom = 'course_id'): string
    {
        $ids = $this->idMatkulDiampu();

        return $ids === [] ? self::TIDAK : $kolom . ' IN (' . implode(',', $ids) . ')';
    }

    /**
     * id kelas yang boleh DILIHAT staf ini (superadmin: semua; pengampu: semua kelas mata
     * kuliahnya; pengajar: kelasnya), dibaca dari view roster sekali per permintaan. Dipakai untuk
     * mengikat kueri sesi (tahap U3): siapa yang melihat kelasnya boleh menjalankan sesinya
     * (SPEC-LOKAUJIAN.md §3).
     *
     * @return list<int>
     */
    public function idKelasDilihat(): array
    {
        if ($this->idKelasStaf === null) {
            $this->idKelasStaf = [];
            if ($this->staf()) {
                $hasil = RosterDb::conn()->query(
                    "SELECT k.id FROM roster_classes k JOIN roster_courses c ON c.id = k.course_id WHERE {$this->lihatKelas()} ORDER BY k.id",
                );
                foreach ($hasil->fetch_all(MYSQLI_NUM) as $b) {
                    $this->idKelasStaf[] = (int) $b[0];
                }
            }
        }

        return $this->idKelasStaf;
    }

    /**
     * Syarat SQL untuk tabel LokaUjian yang punya kolom class_id: hanya baris kelas yang boleh
     * dilihat staf ini.
     */
    public function diJalankan(string $kolom = 'class_id'): string
    {
        $ids = $this->idKelasDilihat();

        return $ids === [] ? self::TIDAK : $kolom . ' IN (' . implode(',', $ids) . ')';
    }

    /**
     * id kelas tempat pengguna ini MAHASISWA menurut kelasMahasiswa(), dibaca sekali per
     * permintaan.
     *
     * @return list<int>
     */
    public function idKelasMahasiswa(): array
    {
        if ($this->idKelasSaya === null) {
            $this->idKelasSaya = [];
            if ($this->mahasiswa()) {
                $hasil = RosterDb::conn()->query("
                    SELECT k.id FROM roster_enrollments e
                    JOIN roster_classes k ON k.id = e.class_id
                    JOIN roster_academic_years t ON t.id = k.academic_year_id
                    WHERE {$this->kelasMahasiswa()} ORDER BY k.id");
                foreach ($hasil->fetch_all(MYSQLI_NUM) as $b) {
                    $this->idKelasSaya[] = (int) $b[0];
                }
            }
        }

        return $this->idKelasSaya;
    }

    /**
     * Syarat SQL: baris kelas tempat pengguna ini mahasiswa.
     */
    public function diKelasSaya(string $kolom = 'class_id'): string
    {
        $ids = $this->idKelasMahasiswa();

        return $ids === [] ? self::TIDAK : $kolom . ' IN (' . implode(',', $ids) . ')';
    }

    /**
     * id pengguna ini, sudah di-escape dan berkutip, untuk kueri pada tabel LokaUjian sendiri
     * (kolom user_id). Hanya huruf heksadesimal dan tanda hubung yang lolos.
     */
    public function idSql(): string
    {
        return "'" . preg_replace('/[^0-9A-Za-z-]/', '', $this->identitas->userId) . "'";
    }
}
