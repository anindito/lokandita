<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Libraries\Antrean;
use App\Libraries\Latihan;
use App\Libraries\Penilaian;
use App\Libraries\Ruang;
use CodeIgniter\HTTP\ResponseInterface;
use Lokandita\Auth\Log;

/**
 * GET /api/executions/{id} — dibaca peramban tiap detik selama program berjalan
 * (KONTRAK.md §6). Seorang pengguna hanya bisa membaca pekerjaan yang ia buat
 * sendiri; itu berlaku juga untuk dosen dan superadmin.
 *
 * Jawaban tidak pernah memuat payload (kode sumber, masukan); isi `result`
 * ditentukan worker dan tidak pernah memuat masukan atau keluaran tes tersembunyi.
 */
final class Eksekusi extends BaseController
{
    public function lihat(string $id): ResponseInterface
    {
        $this->response->setHeader('Cache-Control', 'no-store');
        $pengguna = $this->akses()->identitas()->userId;
        $baris    = Antrean::uuidSah($id) ? Antrean::milik($id, $pengguna) : null;
        if ($baris === null) {
            // Milik orang lain dan tidak ada sengaja tidak dibedakan.
            Log::event('access.denied', ['user' => $pengguna, 'reason' => 'outside_scope_or_missing', 'what' => 'execution']);

            return $this->response->setStatusCode(404)->setJSON(['error' => 'execution_not_found']);
        }

        // SUBMIT yang sudah berakhir: hasilnya disalin ke kiriman dan nilainya dihitung
        // di sini (KONTRAK.md §6), lalu ringkasannya ikut dalam jawaban.
        if ($baris['kind'] === 'SUBMIT' && in_array($baris['status'], ['DONE', 'FAILED'], true)) {
            Penilaian::selesaikan($pengguna, $id);
            $ruang = new Ruang($this->akses());
            $k     = $ruang->kirimanDariJob($id);
            if ($k !== null) {
                $baris['submission'] = RuangLatihan::bentukKiriman($k, Latihan::POIN[$k['difficulty']]);
                $soal                = $ruang->soal((int) $k['meeting_problem_id']);
                if ($soal !== null) {
                    $baris['meeting'] = $ruang->nilai($soal);
                }
            }
        }

        return $this->response->setJSON($baris);
    }
}
