<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Libraries\Antrean;
use App\Libraries\Instans;
use App\Libraries\Latihan;
use App\Libraries\Papan;
use App\Libraries\Penilaian;
use App\Libraries\Ruang;
use App\Libraries\Soal\Pernyataan;
use App\Libraries\Waktu;
use CodeIgniter\HTTP\ResponseInterface;
use Lokandita\Auth\Filters\SecurityHeaders;
use Lokandita\Auth\Log;

/**
 * Halaman dan titik akhir mahasiswa (SPEC.md §11): halaman pertemuan, halaman
 * soal, Run, Submit, draf, riwayat. Rutenya hanya untuk peran mahasiswa.
 *
 * Semua pembacaan lewat App\Libraries\Ruang, yang berangkat dari pendaftaran
 * mahasiswa itu sendiri: soal pertemuan lain, kelas lain atau mahasiswa lain
 * dijawab 404, sama dengan soal yang tidak ada.
 *
 * Masukan dan keluaran tes tersembunyi tidak pernah dikirim dari sini
 * (SPEC.md §10): yang sampai ke peramban hanya tes contoh.
 */
final class RuangLatihan extends BaseController
{
    private const SUMBER_MAKS = 256 * 1024;    // byte, sama dengan batas worker (KONTRAK.md §6)

    private const STDIN_MAKS = 1024 * 1024;

    private const JEDA_RUN_MS = 3000;          // SPEC.md §7: Run sekali per 3 detik

    private const JEDA_SUBMIT_MS = 10000;      // Submit sekali per 10 detik per soal

    private const JALUR_TEMPEL = ['paste', 'keyboard', 'shift_insert', 'middle_click', 'drop'];

    private function ruang(): Ruang
    {
        return new Ruang($this->akses());
    }

    private function pengguna(): string
    {
        return $this->akses()->identitas()->userId;
    }

    // ---- halaman ------------------------------------------------------------------

    public function pertemuan(int $id): ResponseInterface
    {
        $ruang = $this->ruang();
        $p     = $ruang->pertemuan($id);
        if ($p === null) {
            return $this->tidakDitemukan('meeting');
        }
        // Kiriman yang hasilnya sudah ada tetapi belum disalin (tab ditutup saat menunggu) diselesaikan di sini.
        Penilaian::selesaikan($this->pengguna());

        return $this->halaman('ruang_pertemuan', [
            'judul' => $p['matkul_kode'] . ' — ' . lang('Soal.pertemuan.ke', [(int) $p['number']]),
            'p'     => $p,
            'nilai' => $ruang->nilai($p),
            'kartu' => in_array($p['mode'], ['kerja', 'baca'], true) ? $ruang->kartu($p) : [],
        ]);
    }

    /**
     * Papan peringkat KELASNYA SENDIRI untuk pertemuan itu (SPEC.md §3, §12). Kelasnya
     * diambil dari pendaftaran mahasiswa itu, tidak pernah dari URL; papan kelas lain
     * dan papan mata kuliah tidak punya rute untuk mahasiswa.
     */
    public function papan(int $id): ResponseInterface
    {
        $p = $this->ruang()->pertemuan($id);
        // Sebelum pertemuan dibuka untuk kelasnya belum ada papan.
        if ($p === null || $p['mode'] === 'tunggu') {
            return $this->tidakDitemukan('meeting');
        }
        Penilaian::selesaikan($this->pengguna());
        $samar = (int) $p['anonymize_leaderboard'] === 1;

        return $this->halaman('papan_mahasiswa', [
            'judul' => lang('Papan.judul') . ' — ' . $p['matkul_kode'] . ' ' . lang('Soal.pertemuan.ke', [(int) $p['number']]),
            'p'     => $p,
            'samar' => $samar,
            'papan' => Papan::untukMahasiswa(Papan::kelas($id, (int) $p['kelas_id']), $this->pengguna(), $samar),
        ]);
    }

    public function soal(int $mpId): ResponseInterface
    {
        $ruang = $this->ruang();
        $soal  = $ruang->soal($mpId);
        if ($soal === null || ! in_array($soal['mode'], ['kerja', 'baca'], true)) {
            return $this->tidakDitemukan('problem');
        }
        Penilaian::selesaikan($this->pengguna());

        // Instans dibuat sekali, saat soal pertama kali dibuka (SPEC.md §8.2), bila pra-pembangkitan belum sampai ke sini.
        if ($soal['instans_id'] === null && $soal['mode'] === 'kerja') {
            Instans::minta(['id' => $mpId, 'problem_version_id' => (int) $soal['versi_pertemuan']], $this->pengguna(), $this->pengguna(), 'normal');

            return $this->halaman('ruang_siapkan', [
                'judul'   => lang('Latihan.siapkan.judul'),
                'soal'    => $soal,
                'keadaan' => $this->keadaanInstans($mpId),
            ]);
        }

        $semua      = json_decode((string) $soal['translations'], true) ?? [];
        $params     = json_decode((string) $soal['params'], true) ?? [];
        $pernyataan = [];
        foreach (['id', 'en', 'ar'] as $b) {
            $t = $semua[$b] ?? [];
            if (trim((string) ($t['title'] ?? '')) === '' || trim((string) ($t['statement'] ?? '')) === '') {
                continue;
            }
            $pernyataan[$b] = ['judul' => Pernyataan::isi((string) $t['title'], $params), 'mesin' => ($t['machine'] ?? false) === true];
            foreach (['statement', 'input_format', 'output_format', 'constraints'] as $bagian) {
                $teks = trim((string) ($t[$bagian] ?? ''));
                $pernyataan[$b][$bagian] = $teks === '' ? '' : Pernyataan::html($teks, $params);
            }
        }
        $locale = service('request')->getLocale();
        $skor   = db_connect()->query(
            "SELECT ps.best_score, ps.attempts FROM problem_scores ps WHERE ps.meeting_problem_id = ? AND {$this->akses()->milikSendiri('ps')}",
            [$mpId],
        )->getRowArray();
        $panel  = db_connect()->query("SELECT up.panels FROM ui_prefs up WHERE {$this->akses()->milikSendiri('up')}")->getRowArray();

        // Monaco membuat elemen <style> sendiri; hanya halaman ini yang mengizinkannya (DECISIONS.md).
        SecurityHeaders::izinkanGayaSebaris();
        $this->response->setHeader('Cache-Control', 'no-store');

        return $this->halaman('ruang_soal', [
            'judul'      => $pernyataan[$locale]['judul'] ?? $pernyataan['id']['judul'] ?? $soal['slug'],
            'kelasBadan' => 'halaman-soal',
            'soal'       => $soal,
            'pernyataan' => $pernyataan,
            'bahasaAwal' => isset($pernyataan[$locale]) ? $locale : 'id',
            'contoh'     => Ruang::contoh($soal),
            'nilai'      => $ruang->nilai($soal),
            'skor'       => $skor,
            'data'       => [
                'mp'        => $mpId,
                'bahasa'    => $soal['versi_bahasa'],
                'mode'      => $soal['mode'],
                'templat'   => Latihan::templat(['starter_template' => $soal['starter_template'], 'language' => $soal['versi_bahasa']]),
                'draf'      => $ruang->draf($mpId),
                'lengkapi'  => (int) $soal['autocomplete'] === 1,
                'bolehTempel' => $this->akses()->identitas()->pasteAllowed,
                'panel'     => $panel === null ? null : json_decode((string) $panel['panels'], true),
                'poin'      => Latihan::POIN[$soal['difficulty']],
                'tanda'     => ['nomor' => $this->akses()->identitas()->nomorInduk, 'nama' => $this->akses()->identitas()->nama],
                'teks'      => lang('Latihan.js'),
                'verdict'   => lang('Latihan.verdict'),
            ],
        ]);
    }

    // ---- JSON ---------------------------------------------------------------------

    /**
     * @param array<string, mixed> $data
     */
    private function json(array $data, int $status = 200): ResponseInterface
    {
        return $this->response->setStatusCode($status)->setHeader('Cache-Control', 'no-store')->setJSON($data);
    }

    private function galat(int $status, string $kode, array $lain = []): ResponseInterface
    {
        if ($status === 404) {
            Log::event('access.denied', ['user' => $this->pengguna(), 'reason' => 'outside_scope_or_missing', 'what' => 'problem',
                'path' => $this->request->getUri()->getPath()]);
        }

        return $this->json(['error' => $kode, 'message' => lang('Latihan.api.' . $kode, [$lain['retry_after'] ?? 0])] + $lain, $status);
    }

    /**
     * Soal untuk DIKERJAKAN (Run, Submit, draf): 404 bila bukan miliknya, 403 bila jendelanya tidak terbuka.
     *
     * @return array<string, mixed>|ResponseInterface
     */
    private function soalKerja(int $mpId): array|ResponseInterface
    {
        $soal = $this->ruang()->soal($mpId);
        if ($soal === null || ! in_array($soal['mode'], ['kerja', 'baca'], true)) {
            return $this->galat(404, 'tidak_ada');
        }

        return $soal['mode'] === 'kerja' ? $soal : $this->galat(403, 'tertutup');
    }

    /**
     * Kode sumber dari isian `source`, atau jawaban 422.
     */
    private function sumber(): string|ResponseInterface
    {
        $sumber = str_replace(["\r\n", "\r"], "\n", (string) $this->request->getPost('source'));
        if (trim($sumber) === '') {
            return $this->galat(422, 'sumber_kosong');
        }

        return strlen($sumber) > self::SUMBER_MAKS ? $this->galat(422, 'sumber_besar') : $sumber;
    }

    /**
     * Pembatas laju di Redis: benar bila boleh lanjut. Bila Redis tidak bisa
     * dihubungi permintaan diteruskan (dan dicatat); penjaga kedua ada di basis
     * data, di pemanggilnya.
     *
     * @return int 0 = boleh; selain itu detik yang harus ditunggu
     */
    private function tunggu(string $kunci, int $ms): int
    {
        try {
            $r = Antrean::redis();
            if ($r->set($kunci, '1', ['nx', 'px' => $ms]) !== false) {
                return 0;
            }
            $sisa = (int) $r->pttl($kunci);

            return max(1, (int) ceil(($sisa > 0 ? $sisa : $ms) / 1000));
        } catch (\RedisException $e) {
            Log::event('ratelimit.unavailable', ['reason' => $e->getMessage()]);

            return 0;
        }
    }

    /**
     * @return array{siap: bool, gagal: bool, perkiraan: int|null, worker: bool}
     */
    private function keadaanInstans(int $mpId): array
    {
        $db   = db_connect();
        $siap = $db->query("SELECT i.id FROM problem_instances i WHERE i.meeting_problem_id = ? AND {$this->akses()->milikSendiri('i')}", [$mpId])->getRowArray() !== null;
        $job  = $siap ? null : Antrean::denganKunci(Instans::kunci($mpId, $this->pengguna()));
        // Yang ada di depan: pekerjaan antrean normal yang menunggu, ditambah pekerjaan ini sendiri.
        $depan = $siap ? 0 : 1 + (int) ($db->query("SELECT COUNT(*) AS n FROM judge_jobs WHERE status = 'QUEUED' AND priority = 'normal'")->getRowArray()['n'] ?? 0);

        return [
            'siap'      => $siap,
            'gagal'     => ! $siap && ($job === null || $job['status'] === 'FAILED'),
            'perkiraan' => $siap ? 0 : Instans::perkiraan($depan),
            'worker'    => Instans::workerHidup(),
        ];
    }

    public function keadaan(int $mpId): ResponseInterface
    {
        $soal = $this->soalKerja($mpId);

        return $soal instanceof ResponseInterface ? $soal : $this->json($this->keadaanInstans($mpId));
    }

    public function draf(int $mpId): ResponseInterface
    {
        $soal = $this->soalKerja($mpId);
        if ($soal instanceof ResponseInterface) {
            return $soal;
        }
        $sumber = str_replace(["\r\n", "\r"], "\n", (string) $this->request->getPost('source'));
        if (strlen($sumber) > self::SUMBER_MAKS) {
            return $this->galat(422, 'sumber_besar');
        }
        $this->ruang()->simpanDraf($mpId, $sumber);

        return $this->json(['ok' => true, 'saved_at' => Waktu::kini()]);
    }

    /**
     * Run: tes contoh instans, atau satu kali jalan dengan masukan kustom
     * (`kustom=1`, `stdin`). Tidak dinilai dan tidak dihitung (SPEC.md §10).
     */
    public function jalankan(int $mpId): ResponseInterface
    {
        $soal = $this->soalKerja($mpId);
        if ($soal instanceof ResponseInterface) {
            return $soal;
        }
        $sumber = $this->sumber();
        if ($sumber instanceof ResponseInterface) {
            return $sumber;
        }
        $kustom = $this->request->getPost('kustom') === '1';
        $stdin  = str_replace(["\r\n", "\r"], "\n", (string) $this->request->getPost('stdin'));
        if ($kustom && strlen($stdin) > self::STDIN_MAKS) {
            return $this->galat(422, 'stdin_besar');
        }
        if (! $kustom && $soal['instans_id'] === null) {
            return $this->galat(409, 'belum_siap');
        }
        // Penjaga di basis data: paling banyak dua Run milik pengguna ini yang belum selesai.
        $jalan = (int) (db_connect()->query(
            "SELECT COUNT(*) AS n FROM judge_jobs WHERE created_by = ? AND kind = 'RUN' AND status IN ('QUEUED', 'RUNNING')",
            [$this->pengguna()],
        )->getRowArray()['n'] ?? 0);
        $tunggu = $jalan >= 2 ? 3 : $this->tunggu('lokakode:laju:run:' . $this->pengguna(), self::JEDA_RUN_MS);
        if ($tunggu > 0) {
            return $this->galat(429, 'terlalu_cepat', ['retry_after' => $tunggu])->setHeader('Retry-After', (string) $tunggu);
        }

        $this->ruang()->simpanDraf($mpId, $sumber);
        $payload = ['language' => $soal['versi_bahasa'], 'source' => $sumber, 'flags' => Ruang::bendera($soal)]
            + ($kustom ? ['stdin' => $stdin] : ['instance_id' => (int) $soal['instans_id']]);

        return $this->json(['id' => Antrean::kirim('RUN', $payload, $this->pengguna()), 'kind' => $kustom ? 'kustom' : 'contoh'], 202);
    }

    /**
     * Submit: semua tes tersembunyi, dinilai (SPEC.md §7).
     */
    public function kirim(int $mpId): ResponseInterface
    {
        $soal = $this->soalKerja($mpId);
        if ($soal instanceof ResponseInterface) {
            return $soal;
        }
        $sumber = $this->sumber();
        if ($sumber instanceof ResponseInterface) {
            return $sumber;
        }
        if ($soal['instans_id'] === null) {
            return $this->galat(409, 'belum_siap');
        }
        // Penjaga di basis data: satu kiriman yang sedang dinilai per soal.
        $ruang = $this->ruang();
        Penilaian::selesaikan($this->pengguna());
        foreach ($ruang->riwayat($mpId, 3) as $k) {
            if ($k['status'] === 'MENUNGGU') {
                return $this->galat(409, 'masih_dinilai', ['id' => $k['job_id']]);
            }
        }
        $tunggu = $this->tunggu('lokakode:laju:submit:' . $this->pengguna() . ':' . $mpId, self::JEDA_SUBMIT_MS);
        if ($tunggu > 0) {
            return $this->galat(429, 'terlalu_cepat', ['retry_after' => $tunggu])->setHeader('Retry-After', (string) $tunggu);
        }

        $ruang->simpanDraf($mpId, $sumber);
        $baru = $ruang->kirim($soal, $sumber);

        return $this->json(['id' => $baru['job'], 'submission' => $baru['kiriman']], 202);
    }

    public function riwayat(int $mpId): ResponseInterface
    {
        $ruang = $this->ruang();
        $soal  = $ruang->soal($mpId);
        if ($soal === null || ! in_array($soal['mode'], ['kerja', 'baca'], true)) {
            return $this->galat(404, 'tidak_ada');
        }
        Penilaian::selesaikan($this->pengguna());
        $poin = Latihan::POIN[$soal['difficulty']];

        return $this->json(['points' => $poin, 'submissions' => array_map(static fn (array $k): array => [
            'id' => (int) $k['id'], 'status' => $k['status'], 'verdict' => $k['verdict'],
            'passed' => $k['passed'] === null ? null : (int) $k['passed'], 'total' => $k['total'] === null ? null : (int) $k['total'],
            'score' => $k['score'] === null ? null : (float) $k['score'], 'at' => Waktu::lokal($k['created_at']),
            'job' => $k['status'] === 'MENUNGGU' ? $k['job_id'] : null,
            'late' => (int) $k['late'] === 1, 'practice' => (int) $k['practice'] === 1,
        ], $ruang->riwayat($mpId))]);
    }

    /**
     * Satu kiriman sendiri: kode sumber dan status per tes. Tidak ada isi tes.
     */
    public function kiriman(int $mpId, int $id): ResponseInterface
    {
        $ruang = $this->ruang();
        $soal  = $ruang->soal($mpId);
        $k     = $soal === null || ! in_array($soal['mode'], ['kerja', 'baca'], true) ? null : $ruang->kiriman($mpId, $id);
        if ($k === null) {
            return $this->galat(404, 'tidak_ada');
        }

        return $this->json(self::bentukKiriman($k, Latihan::POIN[$soal['difficulty']]) + ['source' => $k['source'], 'at' => Waktu::lokal($k['created_at'])]);
    }

    /**
     * Bentuk kiriman untuk peramban (juga dipakai GET /api/executions/{id}).
     *
     * @param array<string, mixed> $k
     *
     * @return array<string, mixed>
     */
    public static function bentukKiriman(array $k, int $poin): array
    {
        return [
            'id'          => (int) $k['id'],
            'status'      => $k['status'],
            'verdict'     => $k['verdict'],
            'passed'      => $k['passed'] === null ? null : (int) $k['passed'],
            'total'       => $k['total'] === null ? null : (int) $k['total'],
            'score'       => $k['score'] === null ? null : (float) $k['score'],
            'points'      => $poin,
            'duration_ms' => $k['duration_ms'] === null ? null : (int) $k['duration_ms'],
            'compile_log' => (string) ($k['compile_log'] ?? ''),
            'tests'       => json_decode((string) ($k['tests'] ?? '[]'), true) ?? [],
            'missing'     => json_decode((string) ($k['missing_concepts'] ?? '[]'), true) ?? [],
            // Terlambat: nilainya sudah dikali faktor. Latihan: dinilai, tetapi tidak dihitung ke mana pun.
            'late'        => (int) ($k['late'] ?? 0) === 1,
            'late_factor' => ($k['late_factor'] ?? null) === null ? null : (float) $k['late_factor'],
            'practice'    => (int) ($k['practice'] ?? 0) === 1,
        ];
    }

    /**
     * Percobaan tempel yang diblokir editor (SPEC.md §11): dicatat sebagai peristiwa integritas.
     */
    public function integritas(): ResponseInterface
    {
        $mpId  = (int) $this->request->getPost('mp');
        $jalur = (string) $this->request->getPost('jalur');
        if (! in_array($jalur, self::JALUR_TEMPEL, true)) {
            return $this->galat(422, 'isian_salah');
        }
        if ($this->ruang()->soal($mpId) === null) {
            return $this->galat(404, 'tidak_ada');
        }
        // Satu peristiwa per detik per pengguna: menahan tombol tempel tidak membanjiri tabel.
        if ($this->tunggu('lokakode:laju:integritas:' . $this->pengguna(), 1000) === 0) {
            db_connect()->table('integrity_events')->insert([
                'user_id' => $this->pengguna(), 'kind' => 'paste_blocked', 'meeting_problem_id' => $mpId,
                'detail' => json_encode(['jalur' => $jalur]), 'ip' => $this->request->getIPAddress(),
                'user_agent' => substr((string) $this->request->getUserAgent(), 0, 255), 'created_at' => Waktu::kini(),
            ]);
        }

        return $this->json(['ok' => true]);
    }

    /**
     * Ukuran panel halaman soal, disimpan per pengguna (SPEC.md §11).
     */
    public function panel(): ResponseInterface
    {
        $kiri = (int) $this->request->getPost('kiri');
        $atas = (int) $this->request->getPost('atas');
        if ($kiri < 15 || $kiri > 75 || $atas < 15 || $atas > 85) {
            return $this->galat(422, 'isian_salah');
        }
        db_connect()->query(
            'INSERT INTO ui_prefs (user_id, panels, updated_at) VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE panels = VALUES(panels), updated_at = VALUES(updated_at)',
            [$this->pengguna(), json_encode(['kiri' => $kiri, 'atas' => $atas]), Waktu::kini()],
        );

        return $this->json(['ok' => true]);
    }
}
