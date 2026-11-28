<?php

declare(strict_types=1);

namespace Tests\Support;

use Lokandita\Auth\Testing\Http;
use Lokandita\Auth\Testing\RosterFixtures;
use Lokandita\Auth\Testing\TestSchema;

/**
 * Dasar tes halaman roster di Akun Lokandita (KONTRAK.md §18): HTTP sungguhan ke auth-tes,
 * roster di roster_test dalam tata letak produksi (tabel dibuat migrasi LokaKode lalu dipindahkan).
 */
abstract class RosterCase extends AuthCase
{
    protected \mysqli $roster; // roster_test sebagai auth_app

    protected function setUp(): void
    {
        parent::setUp();
        $skema = (string) getenv('ROSTER_SCHEMA');
        $this->roster = TestSchema::connect((string) preg_replace('#/[a-z_]+$#', '/' . $skema, (string) getenv('DB_DSN')));
        RosterFixtures::kosongkan($this->roster);
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function r(string $sql): ?array
    {
        return $this->roster->query($sql)->fetch_assoc();
    }

    protected function n(string $tabelDanSyarat): int
    {
        return (int) $this->r("SELECT COUNT(*) AS n FROM {$tabelDanSyarat}")['n'];
    }

    /**
     * POST dengan token CSRF klien itu.
     *
     * @param array<string, mixed> $form
     */
    protected function kirim(Http $http, string $jalur, array $form = []): Http
    {
        return $http->post($jalur, $form + ['csrf' => $this->csrf($http, '/admin/roster')]);
    }

    /**
     * Baris audit roster terakhir dengan aksi ini.
     *
     * @return array<string, mixed>|null
     */
    protected function auditRoster(string $aksi): ?array
    {
        $row = $this->row("SELECT * FROM audit_log WHERE app = 'roster' AND action = '{$aksi}' ORDER BY id DESC LIMIT 1");
        if ($row !== null) {
            $row['detail'] = json_decode((string) $row['detail'], true);
        }

        return $row;
    }

    /**
     * Jumlah baris audit roster (semua, atau satu aksi). identity_test tidak dikosongkan di antara
     * tes, jadi tes membandingkan jumlah sebelum dan sesudah.
     */
    protected function jumlahAuditRoster(?string $aksi = null): int
    {
        return (int) $this->row("SELECT COUNT(*) AS n FROM audit_log WHERE app = 'roster'" . ($aksi === null ? '' : " AND action = '{$aksi}'"))['n'];
    }

    /**
     * Satu mata kuliah dengan pengampu, tahun aktif dan dua kelas (A dan B).
     *
     * @return array{tahun: int, matkul: int, a: int, b: int, pengampu: array<string, mixed>}
     */
    protected function dasar(): array
    {
        $pengampu = $this->user(['global_role' => 'dosen', 'nama' => 'Dosen Pengampu']);
        $tahun    = RosterFixtures::tahun($this->roster, ['label' => '2026/2027 Ganjil']);
        $matkul   = RosterFixtures::matkul($this->roster, $pengampu['id'], ['code' => 'UJI101']);

        return [
            'tahun' => $tahun, 'matkul' => $matkul, 'pengampu' => $pengampu,
            'a' => RosterFixtures::kelas($this->roster, $matkul, $tahun, ['name' => 'A']),
            'b' => RosterFixtures::kelas($this->roster, $matkul, $tahun, ['name' => 'B']),
        ];
    }
}
