<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Libraries\Soal\Tinjau;
use App\Libraries\Antrean;
use App\Libraries\Audit;
use App\Libraries\BankSoal;
use App\Libraries\Kurikulum;
use App\Libraries\Soal\Ekspor;
use App\Libraries\Soal\Impor;
use App\Libraries\Soal\Skema;
use App\Libraries\Waktu;
use CodeIgniter\HTTP\ResponseInterface;
use Lokandita\Auth\UserDirectory;

/**
 * Bank soal (SPEC.md §8.1): melihat, menyetujui, menolak dan menyunting soal.
 * Superadmin dan pengampu mata kuliahnya; pengajar yang bukan pengampu 403;
 * di luar mata kuliahnya 404.
 */
final class Soal extends BaseController
{
    private const PESAN = [
        'disetujui'  => ['sukses', 'Soal.pesan.disetujui'],
        'ditolak'    => ['sukses', 'Soal.pesan.ditolak'],
        'diperiksa'  => ['sukses', 'Soal.pesan.terjemahanDiperiksa'],
        'bukan-menunggu' => ['galat', 'Soal.galat.bukanMenunggu'],
        'catatan'    => ['galat', 'Soal.galat.catatan'],
    ];

    private function bank(): BankSoal
    {
        return new BankSoal($this->akses());
    }

    /**
     * @return array<string, mixed>|ResponseInterface
     */
    private function soalKelola(int $id): array|ResponseInterface
    {
        $soal = $this->bank()->soal($id);
        if ($soal === null) {
            return $this->tidakDitemukan('problem');
        }

        return (int) $soal['boleh_kelola'] === 1 ? $soal : $this->terlarang();
    }

    public function lihat(int $id): ResponseInterface
    {
        $soal = $this->soalKelola($id);
        if ($soal instanceof ResponseInterface) {
            return $soal;
        }
        $semua = $this->bank()->versi($id);
        $nomor = (int) $this->request->getGet('versi');
        $versi = $semua[0];
        foreach ($semua as $v) {
            if ((int) $v['version'] === $nomor) {
                $versi = $v;
            }
        }
        $katalog = [];
        foreach ((new Kurikulum($this->akses()))->katalog() as $k) {
            $katalog[$k['code']] = Kurikulum::namaKonsep($k);
        }

        return $this->halaman('soal_lihat', [
            'judul'      => $soal['slug'] . ' — ' . $soal['matkul_kode'],
            'soal'       => $soal,
            'versi'      => $versi,
            'semua'      => $semua,
            'terjemahan' => json_decode((string) $versi['translations'], true) ?? [],
            'params'     => json_decode((string) $versi['params'], true) ?? [],
            'konsep'     => json_decode((string) $versi['concepts'], true) ?? [],
            'syarat'     => json_decode((string) $versi['required_in_submission'], true) ?? [],
            'katalog'    => $katalog,
            'orang'      => UserDirectory::byIds(array_filter([$versi['created_by'], $versi['reviewed_by']])),
            'pesan'      => $this->pesanDariQuery(self::PESAN),
        ]);
    }

    /**
     * Versi yang disebut form, milik soal ini. null bila bukan.
     *
     * @return array<string, mixed>|null
     */
    private function versiDariForm(int $soalId): ?array
    {
        $versiId = (int) $this->request->getPost('versi_id');
        foreach ($this->bank()->versi($soalId) as $v) {
            if ((int) $v['id'] === $versiId) {
                return $v;
            }
        }

        return null;
    }

    public function setujui(int $id): ResponseInterface
    {
        $soal = $this->soalKelola($id);
        if ($soal instanceof ResponseInterface) {
            return $soal;
        }
        $versi = $this->versiDariForm($id);
        if ($versi === null) {
            // Versi yang tidak disebut atau bukan milik soal ini: tidak ada yang ditinjau.
            return redirect()->to('/soal/' . $id . '?pesan=bukan-menunggu');
        }
        $berubah = Tinjau::setujui($soal, $versi, $this->akses()->identitas()->userId);

        return redirect()->to('/soal/' . $id . '?pesan=' . ($berubah ? 'disetujui' : 'bukan-menunggu'));
    }

    public function tolak(int $id): ResponseInterface
    {
        $soal = $this->soalKelola($id);
        if ($soal instanceof ResponseInterface) {
            return $soal;
        }
        $versi = $this->versiDariForm($id);
        if ($versi === null) {
            // Versi yang tidak disebut atau bukan milik soal ini: tidak ada yang ditinjau.
            return redirect()->to('/soal/' . $id . '?pesan=bukan-menunggu');
        }
        $catatan = trim((string) $this->request->getPost('catatan'));
        if (mb_strlen($catatan) < 3 || mb_strlen($catatan) > 500) {
            return redirect()->to('/soal/' . $id . '?versi=' . (int) $versi['version'] . '&pesan=catatan');
        }
        $db = db_connect();
        $db->transStart();
        $db->query("UPDATE problem_versions SET status = 'REJECTED', reviewed_by = ?, reviewed_at = ?, review_note = ? WHERE id = ? AND status = 'PENDING_REVIEW'",
            [$this->akses()->identitas()->userId, Waktu::kini(), $catatan, $versi['id']]);
        $berubah = $db->affectedRows() === 1;
        if ($berubah) {
            Audit::catat('soal.ditolak', 'problem_version', (int) $versi['id'], ['slug' => $soal['slug'], 'version' => (int) $versi['version'], 'note' => $catatan], (int) $soal['course_id']);
        }
        $db->transComplete();

        return redirect()->to('/soal/' . $id . '?pesan=' . ($berubah ? 'ditolak' : 'bukan-menunggu'));
    }

    /**
     * Pengampu sudah memeriksa terjemahan mesin: lencananya dihapus (SPEC.md §8.1).
     * Hanya tanda itu yang berubah, bukan isi soal, jadi tidak ada versi baru.
     */
    public function terjemahan(int $id): ResponseInterface
    {
        $soal = $this->soalKelola($id);
        if ($soal instanceof ResponseInterface) {
            return $soal;
        }
        $versi  = $this->versiDariForm($id);
        $bahasa = (string) $this->request->getPost('bahasa');
        $isi    = $versi === null ? [] : (json_decode((string) $versi['translations'], true) ?? []);
        if ($versi === null || ! in_array($bahasa, ['en', 'ar'], true) || ($isi[$bahasa]['machine'] ?? false) !== true) {
            return redirect()->to('/soal/' . $id);
        }
        $isi[$bahasa]['machine'] = false;
        $db = db_connect();
        $db->transStart();
        $db->query('UPDATE problem_versions SET translations = ? WHERE id = ?', [json_encode($isi, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), $versi['id']]);
        Audit::catat('soal.terjemahanDiperiksa', 'problem_version', (int) $versi['id'], ['slug' => $soal['slug'], 'version' => (int) $versi['version'], 'locale' => $bahasa], (int) $soal['course_id']);
        $db->transComplete();

        return redirect()->to('/soal/' . $id . '?versi=' . (int) $versi['version'] . '&pesan=diperiksa');
    }

    public function sunting(int $id): ResponseInterface
    {
        $soal = $this->soalKelola($id);
        if ($soal instanceof ResponseInterface) {
            return $soal;
        }
        $terakhir = $this->bank()->versi($id)[0];

        return $this->form($soal, Ekspor::soal($terakhir), []);
    }

    /**
     * @param array<string, mixed> $soal
     * @param array<string, mixed> $isi   soal berbentuk KONTRAK.md §7
     * @param list<array{field: string, kode: string, argumen: list<string>}> $galat
     */
    private function form(array $soal, array $isi, array $galat, int $status = 200, ?string $pesan = null): ResponseInterface
    {
        $pesanList = [];
        if ($pesan !== null) {
            $pesanList[] = ['jenis' => 'galat', 'teks' => $pesan];
        } elseif ($galat !== []) {
            $pesanList[] = ['jenis' => 'galat', 'teks' => lang('Kode.pesan.periksa')];
        }

        return $this->halaman('soal_form', [
            'judul'   => lang('Soal.soal.ubah') . ' — ' . $soal['slug'],
            'soal'    => $soal,
            'isi'     => json_decode((string) json_encode($isi), true),
            'galat'   => $galat,
            'katalog' => (new Kurikulum($this->akses()))->katalog((string) $soal['language']),
            'pesan'   => $pesanList,
        ], $status);
    }

    public function simpan(int $id): ResponseInterface
    {
        $soal = $this->soalKelola($id);
        if ($soal instanceof ResponseInterface) {
            return $soal;
        }
        $pos = fn (string $k): string => str_replace("\r\n", "\n", (string) $this->request->getPost($k));

        $terjemahan = [];
        foreach (['id', 'en', 'ar'] as $b) {
            $satu = [];
            foreach (Skema::KUNCI_TEKS as $kunci) {
                $satu[$kunci] = $pos("t_{$b}_{$kunci}");
            }
            // Bahasa selain Indonesia yang seluruhnya kosong berarti "belum diterjemahkan".
            if ($b !== 'id' && trim(implode('', $satu)) === '') {
                continue;
            }
            if ($b !== 'id') {
                $satu['machine'] = $this->request->getPost("t_{$b}_machine") === '1';
            }
            $terjemahan[$b] = $satu;
        }
        $params = json_decode($pos('params') === '' ? '{}' : $pos('params'), true);
        $bulat  = static fn (string $v): int|string => preg_match('/^\d{1,6}$/', $v) === 1 ? (int) $v : $v;
        $isi    = [
            'slug'                            => $soal['slug'],
            'difficulty'                      => $pos('difficulty'),
            'comparison_mode'                 => $pos('comparison_mode'),
            'time_limit_ms'                   => $bulat($pos('time_limit_ms')),
            'samples_count'                   => $bulat($pos('samples_count')),
            'hidden_count'                    => $bulat($pos('hidden_count')),
            'required_concepts_in_submission' => array_values(array_map('strval', (array) ($this->request->getPost('syarat') ?? []))),
            'params'                          => $params ?? $pos('params'),
            'translations'                    => $terjemahan,
            'generator'                       => $pos('generator'),
            'reference_solution'              => $pos('reference_solution'),
        ];

        if (Antrean::denyut() === null) {
            return $this->form($soal, $isi, [], 422, lang('Soal.impor.galat.judgeMati'));
        }
        [$imporId, $galat] = (new Impor())->sunting($soal, $isi, $this->akses()->identitas()->userId);
        if ($imporId === null) {
            return $this->form($soal, $isi, $galat, 422);
        }

        return redirect()->to('/impor/' . $imporId);
    }
}
