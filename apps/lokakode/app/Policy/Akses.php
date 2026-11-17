<?php

declare(strict_types=1);

namespace App\Policy;

use Lokandita\Auth\Identity;

/**
 * Satu-satunya tempat aturan "siapa boleh apa" LokaKode (SPEC.md §3).
 *
 * Aturannya berbentuk syarat SQL yang ditempelkan ke kueri, sehingga baris di
 * luar hak pengguna tidak pernah terbaca — bukan dibaca dulu lalu disembunyikan.
 * Peran lingkup diturunkan dari relasi, tidak dari kolom peran:
 *   pengampu  = courses.owner_id
 *   pengajar  = class_teachers
 *   mahasiswa = enrollments
 *
 * Alias tabel yang dipakai syarat: `c` = courses, `k` = classes. Kueri yang
 * memakai syarat ini wajib memakai alias yang sama.
 */
final class Akses
{
    private const SEMUA = '1 = 1';
    private const TIDAK = '1 = 0';

    private readonly string $u; // id pengguna, sudah di-escape dan berkutip untuk SQL

    public function __construct(private readonly Identity $identitas)
    {
        $this->u = db_connect()->escape($identitas->userId);
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
     * Superadmin atau dosen. Mahasiswa tidak pernah mendapat halaman pengelolaan.
     */
    public function staf(): bool
    {
        return $this->superadmin() || $this->dosen();
    }

    /**
     * Mata kuliah yang boleh DILIHAT: superadmin semua; dosen yang mengampunya
     * atau mengajar salah satu kelasnya.
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
            SELECT 1 FROM classes kx JOIN class_teachers ctx ON ctx.class_id = kx.id
            WHERE kx.course_id = c.id AND ctx.user_id = {$this->u}))";
    }

    /**
     * Mata kuliah yang boleh DIKELOLA (kelas, pendaftaran, pengajar): superadmin
     * semua; dosen hanya yang diampunya.
     */
    public function kelolaMatkul(): string
    {
        if ($this->superadmin()) {
            return self::SEMUA;
        }

        return $this->dosen() ? "c.owner_id = {$this->u}" : self::TIDAK;
    }

    /**
     * Kelas yang boleh DILIHAT (daftar mahasiswa, nilai): superadmin semua;
     * pengampu semua kelas mata kuliahnya; pengajar kelas yang ditugaskan kepadanya.
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
            SELECT 1 FROM class_teachers ctx WHERE ctx.class_id = k.id AND ctx.user_id = {$this->u}))";
    }

    /**
     * Kelas yang boleh DIKELOLA: sama dengan mengelola mata kuliahnya.
     * Pengajar yang bukan pengampu hanya melihat.
     */
    public function kelolaKelas(): string
    {
        return $this->kelolaMatkul();
    }

    public function mahasiswa(): bool
    {
        return $this->identitas->globalRole === 'mahasiswa';
    }

    /**
     * Baris milik pengguna ini sendiri di tabel beralias $alias (kiriman, draf,
     * instans, nilai): `<alias>.user_id = <pengguna>`.
     */
    public function milikSendiri(string $alias): string
    {
        if (preg_match('/^[a-z]{1,3}$/', $alias) !== 1) {
            throw new \InvalidArgumentException('alias tabel tidak sah');
        }

        return "{$alias}.user_id = {$this->u}";
    }

    /**
     * Pendaftaran milik pengguna ini sendiri (halaman mahasiswa). Alias `e` = enrollments.
     */
    public function pendaftaranSendiri(): string
    {
        return "e.user_id = {$this->u}";
    }
}
