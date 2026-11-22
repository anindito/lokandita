<?php

declare(strict_types=1);

namespace App\Libraries;

use App\Policy\Akses;
use CodeIgniter\Database\BaseConnection;

/**
 * Pengumuman kelas (SPEC.md §14, KONTRAK.md §17). Untuk satu kelas; teks polos.
 *
 * Tidak ada sunting dan tidak ada hapus: pengumuman yang keliru DITARIK (barisnya tetap ada,
 * mahasiswa tidak melihatnya lagi) dan diganti pengumuman baru — yang sudah dibaca mahasiswa
 * tidak pernah berubah diam-diam. Syarat lihat kelas dan pendaftaran sendiri ada di dalam kueri.
 */
final class Pengumuman
{
    public const JUDUL_MAKS = 150;

    public const ISI_MAKS = 5000;

    public const BATAS_DAFTAR = 100;

    private BaseConnection $db;

    public function __construct(private readonly Akses $akses)
    {
        $this->db = db_connect();
    }

    /**
     * Teks dari isian: baris dirapikan; null bila kosong atau lebih dari $maks karakter.
     */
    public static function teks(string $isian, int $maks): ?string
    {
        $teks = trim(str_replace(["\r\n", "\r"], "\n", $isian));

        return $teks === '' || mb_strlen($teks) > $maks ? null : $teks;
    }

    // ---- dosen ------------------------------------------------------------------------

    /**
     * Semua pengumuman kelas itu, termasuk yang ditarik; terbaru lebih dulu.
     *
     * @return list<array<string, mixed>>
     */
    public function kelas(int $kelasId): array
    {
        return $this->db->query("
            SELECT a.*
            FROM announcements a
            JOIN classes k ON k.id = a.class_id
            JOIN courses c ON c.id = k.course_id
            WHERE k.id = ? AND {$this->akses->lihatKelas()}
            ORDER BY a.id DESC LIMIT ?", [$kelasId, self::BATAS_DAFTAR])->getResultArray();
    }

    /**
     * @param array<string, mixed> $kelas baris dari Akademik::kelas() — kelas yang memang boleh dilihat pengguna ini
     */
    public function buat(array $kelas, string $judul, string $isi): int
    {
        $this->db->transStart();
        $this->db->table('announcements')->insert([
            'class_id' => (int) $kelas['id'], 'author_id' => $this->akses->identitas()->userId,
            'title' => $judul, 'body' => $isi, 'created_at' => Waktu::kini(),
        ]);
        $id = (int) $this->db->insertID();
        Audit::catat('pengumuman.dibuat', 'announcement', $id, ['judul' => $judul], (int) $kelas['course_id'], (int) $kelas['id']);
        $this->db->transComplete();

        return $id;
    }

    /**
     * Menarik pengumuman kelas itu. null bila tidak ada atau bukan milik kelas itu; false bila
     * sudah ditarik sebelumnya (tidak ada yang berubah dan tidak ada audit kedua); true bila ditarik sekarang.
     *
     * @param array<string, mixed> $kelas baris dari Akademik::kelas()
     */
    public function tarik(array $kelas, int $id): ?bool
    {
        if ($this->db->query('SELECT id FROM announcements WHERE id = ? AND class_id = ?', [$id, (int) $kelas['id']])->getRowArray() === null) {
            return null;
        }
        $this->db->transStart();
        $this->db->query('UPDATE announcements SET withdrawn_at = ?, withdrawn_by = ? WHERE id = ? AND class_id = ? AND withdrawn_at IS NULL',
            [Waktu::kini(), $this->akses->identitas()->userId, $id, (int) $kelas['id']]);
        $berubah = $this->db->affectedRows() === 1;
        if ($berubah) {
            Audit::catat('pengumuman.ditarik', 'announcement', $id, [], (int) $kelas['course_id'], (int) $kelas['id']);
        }
        $this->db->transComplete();

        return $berubah;
    }

    // ---- mahasiswa --------------------------------------------------------------------

    /**
     * Pengumuman yang tidak ditarik dari kelas-kelas tempat mahasiswa ini AKTIF atau LULUS; terbaru lebih dulu.
     *
     * @return list<array<string, mixed>>
     */
    public function untukSaya(int $batas = self::BATAS_DAFTAR): array
    {
        return $this->db->query("
            SELECT a.id, a.title, a.body, a.created_at, a.author_id, k.name AS kelas_nama, c.code AS matkul_kode
            FROM enrollments e
            JOIN classes k ON k.id = e.class_id
            JOIN courses c ON c.id = k.course_id
            JOIN announcements a ON a.class_id = k.id AND a.withdrawn_at IS NULL
            WHERE {$this->akses->pendaftaranSendiri()} AND e.status IN ('AKTIF', 'LULUS')
            ORDER BY a.id DESC LIMIT ?", [$batas])->getResultArray();
    }
}
