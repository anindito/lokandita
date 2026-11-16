<?php

declare(strict_types=1);

namespace App\Libraries;

use App\Policy\Akses;
use CodeIgniter\Database\BaseConnection;

/**
 * Pembacaan pertemuan dan konsep (SPEC.md §6, §9). Seperti App\Libraries\Akademik:
 * setiap kueri memuat syarat dari App\Policy\Akses; tidak ada fungsi yang
 * mengambil pertemuan hanya berdasarkan id.
 *
 * Pertemuan TERLIHAT oleh siapa pun yang melihat mata kuliahnya, tetapi isinya
 * (konsep, bank soal) hanya untuk pengelola: superadmin dan pengampu. Kolom
 * `boleh_kelola` membedakannya; pengendali menjawab 403 bila nilainya 0.
 */
final class Kurikulum
{
    public const BAHASA = ['c', 'cpp', 'python'];

    private readonly BaseConnection $db;

    public function __construct(private readonly Akses $akses)
    {
        $this->db = db_connect();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function daftarPertemuan(int $matkulId): array
    {
        return $this->db->query("
            SELECT m.*,
                   (SELECT COUNT(*) FROM problems p WHERE p.meeting_id = m.id) AS jumlah_soal,
                   (SELECT COUNT(*) FROM meeting_problems mp WHERE mp.meeting_id = m.id) AS jumlah_disetujui
            FROM meetings m JOIN courses c ON c.id = m.course_id
            WHERE m.course_id = ? AND {$this->akses->lihatMatkul()}
            ORDER BY m.number", [$matkulId])->getResultArray();
    }

    /**
     * @return array<string, mixed>|null null bila tidak ada ATAU di luar hak pengguna
     */
    public function pertemuan(int $id): ?array
    {
        return $this->db->query("
            SELECT m.*, c.code AS matkul_kode, c.name AS matkul_nama,
                   ({$this->akses->kelolaMatkul()}) AS boleh_kelola,
                   (SELECT COUNT(*) FROM problems p WHERE p.meeting_id = m.id) AS jumlah_soal
            FROM meetings m JOIN courses c ON c.id = m.course_id
            WHERE m.id = ? AND {$this->akses->lihatMatkul()}", [$id])->getRowArray();
    }

    /**
     * Katalog konsep, urut seperti docs/PETA-KONSEP.md. $bahasa menyaring konsep
     * yang berlaku untuk bahasa itu.
     *
     * @return list<array<string, mixed>>
     */
    public function katalog(?string $bahasa = null): array
    {
        if ($bahasa === null) {
            return $this->db->query('SELECT * FROM concepts ORDER BY urutan')->getResultArray();
        }

        return $this->db->query('SELECT * FROM concepts WHERE FIND_IN_SET(?, languages) > 0 ORDER BY urutan', [$bahasa])->getResultArray();
    }

    /**
     * @return list<string> kode konsep dasar mata kuliah (dikuasai sebelum pertemuan 1)
     */
    public function konsepDasar(int $matkulId): array
    {
        return array_column($this->db->query('
            SELECT b.concept_code FROM course_base_concepts b JOIN concepts k ON k.code = b.concept_code
            WHERE b.course_id = ? ORDER BY k.urutan', [$matkulId])->getResultArray(), 'concept_code');
    }

    /**
     * @return list<string> kode konsep yang BARU di pertemuan ini
     */
    public function konsepTambahan(int $pertemuanId): array
    {
        return array_column($this->db->query('
            SELECT mc.concept_code FROM meeting_concepts mc JOIN concepts k ON k.code = mc.concept_code
            WHERE mc.meeting_id = ? ORDER BY k.urutan', [$pertemuanId])->getResultArray(), 'concept_code');
    }

    /**
     * Konsep yang diwarisi pertemuan ini: konsep dasar mata kuliah dan konsep
     * semua pertemuan bernomor lebih kecil (SPEC.md §9: kumulatif).
     *
     * @return list<string>
     */
    public function konsepWarisan(int $matkulId, int $nomor): array
    {
        $dari = array_column($this->db->query('
            SELECT DISTINCT mc.concept_code FROM meeting_concepts mc JOIN meetings m ON m.id = mc.meeting_id
            WHERE m.course_id = ? AND m.number < ?', [$matkulId, $nomor])->getResultArray(), 'concept_code');

        return array_values(array_unique(array_merge($this->konsepDasar($matkulId), $dari)));
    }

    /**
     * Semua konsep yang diizinkan di pertemuan ini (KONTRAK.md §6).
     *
     * @param array<string, mixed> $pertemuan baris meetings
     *
     * @return list<string>
     */
    public function konsepDiizinkan(array $pertemuan): array
    {
        $semua = array_merge(
            $this->konsepWarisan((int) $pertemuan['course_id'], (int) $pertemuan['number']),
            $this->konsepTambahan((int) $pertemuan['id']),
        );

        return array_values(array_unique($semua));
    }

    /**
     * Nama konsep dalam bahasa tampilan; kembali ke bahasa Indonesia bila kosong.
     *
     * @param array<string, mixed> $konsep baris concepts
     */
    public static function namaKonsep(array $konsep): string
    {
        $kolom = ['en' => 'name_en', 'ar' => 'name_ar'][service('request')->getLocale()] ?? 'name';

        return (string) ($konsep[$kolom] ?? '') !== '' ? (string) $konsep[$kolom] : (string) $konsep['name'];
    }
}
