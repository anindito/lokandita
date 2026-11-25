<?php

declare(strict_types=1);

namespace App\Policy;

use Lokandita\Auth\Identity;

/**
 * Satu-satunya tempat aturan "siapa boleh apa" LokaUjian (SPEC-LOKAUJIAN.md §3).
 *
 * Peran global berasal dari LokaAuth. Peran lingkup (pengampu, pengajar, mahasiswa sebuah
 * kelas) diturunkan dari view roster pada setiap permintaan; tidak ada kolom peran dan tidak
 * ada yang disimpan melewati satu permintaan.
 */
final class Akses
{
    public function __construct(private readonly Identity $identitas)
    {
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
}
