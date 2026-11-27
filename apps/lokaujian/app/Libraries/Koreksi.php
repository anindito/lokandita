<?php

declare(strict_types=1);

namespace App\Libraries;

use App\Policy\Akses;
use CodeIgniter\Database\BaseConnection;

/**
 * KOREKSI sesudah ujian (SPEC-LOKAUJIAN.md §11): yang mengelola mata kuliahnya boleh mengubah
 * KUNCI atau POIN satu soal pada salinan beku ujian yang sudah terbit. Teks dan opsi tidak
 * pernah diubah di sini, dan soal tidak bisa dianulir. Soal di bank tidak ikut berubah.
 *
 * Sesudah setiap koreksi SEMUA pengerjaan ujian itu yang sudah dikumpulkan dinilai ulang penuh
 * (App\Libraries\Nilai), dan perubahan, nilai lama dan baru, serta jumlah pengerjaan yang
 * skornya berubah dicatat di log audit — semuanya dalam satu transaksi.
 */
final class Koreksi
{
    private readonly BaseConnection $db;

    public function __construct(private readonly Akses $akses)
    {
        $this->db = db_connect();
    }

    /**
     * Ujian terbit atau arsip yang dikelola pengguna ini, dengan isi bekunya (termasuk kunci).
     *
     * @return array{ujian: array<string, mixed>, butir: list<array<string, mixed>>, pengerjaan: int}|null
     */
    public function lihat(int $matkulId, int $ujianId): ?array
    {
        $u = $this->db->query("SELECT * FROM exams WHERE id = ? AND course_id = ? AND status <> 'draf' AND {$this->akses->diKelola()}", [$ujianId, $matkulId])->getRowArray();
        if ($u === null) {
            return null;
        }

        return [
            'ujian'      => $u,
            'butir'      => Ujian::beku($this->db, $ujianId),
            'pengerjaan' => (int) $this->db->query('SELECT COUNT(*) AS n FROM attempts WHERE exam_id = ?', [$ujianId])->getRowArray()['n'],
        ];
    }

    /**
     * @return array<int, string|null> id pengerjaan => skor
     */
    private function skor(int $ujianId): array
    {
        $peta = [];
        foreach ($this->db->query('SELECT id, score FROM attempts WHERE exam_id = ? AND submitted_at IS NOT NULL', [$ujianId])->getResultArray() as $b) {
            $peta[(int) $b['id']] = $b['score'] === null ? null : (string) $b['score'];
        }

        return $peta;
    }

    /**
     * Mengubah kunci dan/atau poin satu soal beku. Isian kosong berarti "tidak diubah".
     *
     * @param string $poin  angka 0,25–100 langkah 0,25 (koma diterima), atau ''
     * @param string $kunci benar_salah: 'benar'|'salah'; pilihan_ganda: id opsi; atau ''
     *
     * @return array{pengerjaan: int, berubah: int}|'tidak_ada'|'poin'|'kunci'|'sama'
     */
    public function ubah(int $matkulId, int $ujianId, int $soalId, string $poin, string $kunci): array|string
    {
        $this->db->transBegin();
        try {
            $hasil = $this->ubahDalam($matkulId, $ujianId, $soalId, trim($poin), trim($kunci));
            is_array($hasil) ? $this->db->transCommit() : $this->db->transRollback();

            return $hasil;
        } catch (\Throwable $e) {
            $this->db->transRollback();

            throw $e;
        }
    }

    /**
     * @return array{pengerjaan: int, berubah: int}|string
     */
    private function ubahDalam(int $matkulId, int $ujianId, int $soalId, string $poin, string $kunci): array|string
    {
        $u = $this->db->query("SELECT id FROM exams WHERE id = ? AND course_id = ? AND status <> 'draf' AND {$this->akses->diKelola()} FOR UPDATE", [$ujianId, $matkulId])->getRowArray();
        $q = $u === null ? null : $this->db->query('SELECT * FROM exam_questions WHERE id = ? AND exam_id = ? FOR UPDATE', [$soalId, $ujianId])->getRowArray();
        if ($q === null) {
            return 'tidak_ada';
        }
        $detail = ['soal' => (int) $q['position'], 'tipe' => (string) $q['type']];
        // Skor SEBELUM koreksi, sesudah yang belum sempat dinilai penjadwal dinilai dulu — supaya
        // "berapa yang skornya berubah" hanya menghitung akibat koreksi ini.
        Nilai::hitung($this->db, 'a.exam_id = ? AND a.submitted_at IS NOT NULL AND a.scored_at IS NULL', [$ujianId]);
        $sebelum = $this->skor($ujianId);

        if ($poin !== '') {
            $angka = str_replace(',', '.', $poin);
            $baru  = is_numeric($angka) ? Aturan::poin((float) $angka) : null;
            if ($baru === null) {
                return 'poin';
            }
            if ($baru !== number_format((float) $q['points'], 2, '.', '')) {
                $detail += ['poin_lama' => Nilai::tampil($q['points']), 'poin_baru' => Nilai::tampil($baru)];
                $this->db->query('UPDATE exam_questions SET points = ? WHERE id = ?', [$baru, $soalId]);
            }
        }

        if ($kunci !== '') {
            if ($q['type'] === 'benar_salah') {
                if ($kunci !== 'benar' && $kunci !== 'salah') {
                    return 'kunci';
                }
                $lama = (int) $q['answer'] === 1 ? 'benar' : 'salah';
                if ($kunci !== $lama) {
                    $detail += ['kunci_lama' => $lama, 'kunci_baru' => $kunci];
                    $this->db->query('UPDATE exam_questions SET answer = ? WHERE id = ?', [(int) ($kunci === 'benar'), $soalId]);
                }
            } elseif ($q['type'] === 'pilihan_ganda') {
                $opsi  = $this->db->query('SELECT id, position, correct FROM exam_options WHERE exam_question_id = ? ORDER BY position', [$soalId])->getResultArray();
                $huruf = [];
                $lama  = null;
                foreach ($opsi as $i => $o) {
                    $huruf[(int) $o['id']] = chr(65 + $i);
                    if ((int) $o['correct'] === 1) {
                        $lama = (int) $o['id'];
                    }
                }
                if (preg_match('/^\d{1,18}$/', $kunci) !== 1 || ! isset($huruf[(int) $kunci])) {
                    return 'kunci';
                }
                if ((int) $kunci !== $lama) {
                    // Kunci dicatat sebagai huruf opsinya dalam urutan ujian, bukan teksnya.
                    $detail += ['kunci_lama' => $lama === null ? null : $huruf[$lama], 'kunci_baru' => $huruf[(int) $kunci]];
                    $this->db->query('UPDATE exam_options SET correct = (id = ?) WHERE exam_question_id = ?', [(int) $kunci, $soalId]);
                }
            } else {
                return 'kunci'; // isian dan uraian tidak punya kunci
            }
        }
        if (! isset($detail['poin_baru']) && ! isset($detail['kunci_baru'])) {
            return 'sama';
        }

        Nilai::hitung($this->db, 'a.exam_id = ? AND a.submitted_at IS NOT NULL', [$ujianId]);
        $berubah = 0;
        foreach ($this->skor($ujianId) as $id => $skor) {
            if ((float) ($sebelum[$id] ?? 0) !== (float) $skor) {
                $berubah++;
            }
        }
        $detail += ['pengerjaan' => count($sebelum), 'nilai_berubah' => $berubah];
        Audit::catat('soal.dikoreksi', 'ujian', $ujianId, $detail, $matkulId);

        return ['pengerjaan' => count($sebelum), 'berubah' => $berubah];
    }
}
