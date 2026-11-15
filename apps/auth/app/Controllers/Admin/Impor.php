<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\Audit;
use App\Libraries\Brankas;
use App\Libraries\Importer;
use App\Libraries\Passwords;
use App\Libraries\Tabular;
use CodeIgniter\HTTP\ResponseInterface;
use Lokandita\Auth\Current;

/**
 * Impor massal pengguna dari CSV/XLSX dengan pratinjau (SPEC.md §4.1).
 */
final class Impor extends BaseController
{
    public function form(): ResponseInterface
    {
        return $this->halaman('admin/impor_form', ['judul' => lang('Admin.impor.judul'), 'pesan' => []]);
    }

    /**
     * POST /admin/impor — membaca berkas dan menampilkan pratinjau. Belum ada yang disimpan.
     */
    public function unggah(): ResponseInterface
    {
        $berkas = $this->request->getFile('berkas');
        $ext    = $berkas === null ? '' : strtolower($berkas->getClientExtension());
        if ($berkas === null || ! $berkas->isValid() || ! in_array($ext, ['csv', 'xlsx'], true) || $berkas->getSize() > 2 * 1024 * 1024) {
            return $this->halaman('admin/impor_form', ['judul' => lang('Admin.impor.judul'), 'pesan' => $this->galat(lang('Admin.impor.berkasSalah'))], 422);
        }
        try {
            $baris = Tabular::read($berkas->getTempName(), $ext);
        } catch (\Throwable $e) {
            return $this->halaman('admin/impor_form', ['judul' => lang('Admin.impor.judul'), 'pesan' => $this->galat(lang('Admin.impor.tidakTerbaca'))], 422);
        }
        $kolom = $baris === [] ? [] : array_keys($baris[0]);
        $kurang = array_diff(['nomor_induk', 'nama', 'global_role'], $kolom);
        if ($baris === [] || $kurang !== []) {
            return $this->halaman('admin/impor_form', [
                'judul' => lang('Admin.impor.judul'),
                'pesan' => $this->galat($baris === [] ? lang('Admin.impor.kosong') : lang('Admin.impor.kolomKurang', [implode(', ', $kurang)])),
            ], 422);
        }
        if (count($baris) > Importer::MAKS_BARIS) {
            return $this->halaman('admin/impor_form', ['judul' => lang('Admin.impor.judul'), 'pesan' => $this->galat(lang('Admin.impor.terlaluBanyak', [Importer::MAKS_BARIS]))], 422);
        }

        $hasil = Importer::analyse($baris);
        $token = Brankas::simpan('impor', Current::identity()->userId, $hasil + ['nama_berkas' => $berkas->getClientName()]);

        return $this->pratinjau($token, $hasil, []);
    }

    /**
     * POST /admin/impor/jalankan — membuat akun untuk baris "baru" dari pratinjau.
     */
    public function jalankan(): ResponseInterface
    {
        $aktor = Current::identity()->userId;
        $token = (string) $this->request->getPost('token');
        $hasil = Brankas::lihat('impor', $aktor, $token);
        if ($hasil === null) {
            return $this->halaman('admin/impor_form', ['judul' => lang('Admin.impor.judul'), 'pesan' => $this->galat(lang('Admin.impor.kedaluwarsa'))], 410);
        }

        $bersama = null;
        if ($this->request->getPost('mode') === 'sama') {
            $bersama = (string) $this->request->getPost('sandi_bersama');
            $masalah = Passwords::problems($bersama);
            if ($masalah !== []) {
                return $this->pratinjau($token, $hasil, $this->galat(lang('Auth.sandi.' . $masalah[0], [Passwords::MIN])), 422);
            }
        }

        // Hash Argon2id per akun memakan waktu; ratusan akun bisa butuh puluhan detik.
        set_time_limit(0);
        Brankas::ambil('impor', $aktor, $token);
        $jalan = Importer::run($hasil['baru'], $bersama, $aktor);
        Audit::log('user.imported', $aktor, null, [
            'file'            => (string) $hasil['nama_berkas'],
            'created'         => count($jalan['dibuat']),
            'skipped'         => $jalan['dilewati'],
            'already_existed' => count($hasil['ada']),
            'invalid'         => count($hasil['salah']),
            'password_mode'   => $bersama === null ? 'random' : 'shared',
        ]);

        return $this->halaman('admin/impor_hasil', [
            'judul'    => lang('Admin.impor.hasilJudul'),
            'dibuat'   => count($jalan['dibuat']),
            'dilewati' => $jalan['dilewati'],
            'token'    => $jalan['dibuat'] === [] ? null : Brankas::simpan('kredensial', $aktor, $jalan['dibuat']),
        ])->setHeader('Cache-Control', 'no-store');
    }

    /**
     * @param array<string, mixed>                     $hasil
     * @param list<array{jenis: string, teks: string}> $pesan
     */
    private function pratinjau(string $token, array $hasil, array $pesan, int $status = 200): ResponseInterface
    {
        return $this->halaman('admin/impor_pratinjau', ['judul' => lang('Admin.impor.pratinjauJudul'), 'token' => $token, 'hasil' => $hasil, 'pesan' => $pesan], $status)
            ->setHeader('Cache-Control', 'no-store');
    }
}
