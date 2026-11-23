<?php

declare(strict_types=1);

namespace App\Libraries\Soal;

use App\Libraries\Audit;
use App\Libraries\Waktu;

/**
 * Persetujuan sebuah versi soal (SPEC.md §8.1). Satu jalan untuk halaman web
 * dan untuk `spark soal:setujui`, supaya keduanya tidak bisa berbeda.
 *
 * Pemanggil sudah memeriksa hak pelakunya (superadmin atau pengampu mata
 * kuliah itu); fungsi ini tidak memeriksanya lagi.
 */
final class Tinjau
{
    /**
     * Menyetujui versi yang masih PENDING_REVIEW dan menjadikannya versi yang
     * dipakai pertemuannya. Instans mahasiswa yang sudah ada tetap pada versinya.
     *
     * @param array<string, mixed> $soal  kolom id, slug, meeting_id, course_id
     * @param array<string, mixed> $versi kolom id, version
     * @param string|null          $lewat 'cli' bila disetujui lewat perintah, dicatat di log audit
     *
     * @return bool false bila versinya sudah tidak menunggu tinjauan (tidak ada yang berubah)
     */
    public static function setujui(array $soal, array $versi, string $pelakuId, ?string $lewat = null): bool
    {
        $db = db_connect();
        $db->transStart();
        // Syarat status ada di UPDATE: dua persetujuan yang berpacu menyetujui sekali saja.
        $db->query("UPDATE problem_versions SET status = 'APPROVED', reviewed_by = ?, reviewed_at = ?, review_note = NULL WHERE id = ? AND status = 'PENDING_REVIEW'",
            [$pelakuId, Waktu::kini(), (int) $versi['id']]);
        $berubah = $db->affectedRows() === 1;
        if ($berubah) {
            $db->query('INSERT INTO meeting_problems (meeting_id, problem_id, problem_version_id, updated_at) VALUES (?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE problem_version_id = VALUES(problem_version_id), updated_at = VALUES(updated_at)',
                [(int) $soal['meeting_id'], (int) $soal['id'], (int) $versi['id'], Waktu::kini()]);
            $detail = ['slug' => $soal['slug'], 'version' => (int) $versi['version'], 'meeting_id' => (int) $soal['meeting_id']];
            if ($lewat !== null) {
                $detail['lewat'] = $lewat;
            }
            Audit::catat('soal.disetujui', 'problem_version', (int) $versi['id'], $detail, (int) $soal['course_id'], null, $pelakuId);
        }
        $db->transComplete();
        // Pertemuan ujian: targetnya selalu jumlah poin soal yang terpasang; nilai yang sudah ada mengikuti.
        if ($berubah && \App\Libraries\Latihan::selaraskanTargetUjian((int) $soal['meeting_id'])) {
            \App\Libraries\Penilaian::hitungUlangPertemuan((int) $soal['meeting_id']);
        }

        return $berubah;
    }
}
