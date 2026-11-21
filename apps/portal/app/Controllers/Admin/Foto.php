<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\Aplikasi;
use App\Libraries\Foto as Pustaka;
use App\Libraries\Gambar;
use App\Libraries\Teks;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\ResponseInterface;
use Lokandita\Auth\Current;
use Lokandita\Auth\UserDirectory;

/**
 * Foto pembuka halaman depan Portal (KONTRAK.md §16) — bagian dari penyuntingan halaman
 * publik di /admin/teks, bukan penyunting kedua. Semua rute di sini berada di belakang
 * filter `masuk:superadmin` dan, untuk POST, pemeriksaan CSRF; kelas ini tidak memeriksa
 * peran lagi.
 */
final class Foto extends BaseController
{
    private const PESAN = [
        'tayang'        => ['sukses', 'Sunting.foto.pesan.tayang'],
        'sudah-tayang'  => ['sukses', 'Sunting.foto.pesan.sudahTayang'],
        'bawaan'        => ['sukses', 'Sunting.foto.pesan.bawaan'],
        'sudah-bawaan'  => ['sukses', 'Sunting.foto.pesan.sudahBawaan'],
        'diunggah'      => ['sukses', 'Sunting.foto.pesan.diunggah'],
    ];

    public function index(): ResponseInterface
    {
        return $this->daftar([], [], null);
    }

    /**
     * POST: unggah. Fotonya diolah dan disimpan sebagai foto yang BELUM tayang, lalu
     * pratinjaunya dibuka.
     */
    public function unggah(): ResponseInterface
    {
        [$isian, $galat] = Pustaka::isian((array) $this->request->getPost());
        $berkas          = $this->request->getFile('foto');
        $galatBerkas     = null;
        if ($berkas === null || $berkas->getError() === UPLOAD_ERR_NO_FILE) {
            $galatBerkas = 'kosong';
        } elseif (in_array($berkas->getError(), [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true)) {
            $galatBerkas = 'terlalu_besar';
        } elseif ($berkas->getError() !== UPLOAD_ERR_OK || ! is_uploaded_file($berkas->getTempName())) {
            $galatBerkas = 'tidak_terbaca';
        }
        if ($galat !== [] || $galatBerkas !== null) {
            return $this->daftar($isian, $galat, $galatBerkas, 422);
        }

        // Membuka foto besar memakan memori: sekitar 5 byte per piksel, paling banyak Gambar::MAKS_PIKSEL.
        ini_set('memory_limit', '512M');
        set_time_limit(120);
        $hasil = Pustaka::unggah($berkas->getTempName(), $berkas->getClientName(), $isian, Current::identity()->userId);
        if (is_string($hasil)) {
            return $this->daftar($isian, [], $hasil, 422);
        }

        return redirect()->to('/admin/teks/foto/' . $hasil . '?pesan=diunggah');
    }

    /**
     * Pratinjau satu foto di tata letak halaman depan yang sebenarnya, dalam bahasa
     * Indonesia dan Arab, dengan tombol untuk menayangkannya.
     */
    public function lihat(string $id): ResponseInterface
    {
        $baris = $this->foto($id);
        $depan = [];
        foreach (['id', 'ar'] as $b) {
            $depan[$b] = [
                't'        => Teks::ambil('depan', $b),
                'ket'      => Teks::ambil('aplikasi', $b),
                'aplikasi' => Aplikasi::daftar(),
                'foto'     => Pustaka::untuk($baris, $b),
                'bahasa'   => $b,
            ];
        }

        return $this->response->setBody(view('admin/foto_lihat', [
            'judul'  => lang('Sunting.foto.pratinjauJudul'),
            'baris'  => $baris,
            'depan'  => $depan,
            'tayang' => Pustaka::idTayang() === (int) $baris['id'],
            'pesan'  => $this->pesan(),
        ]));
    }

    /**
     * POST: tayangkan foto ini (atau tayangkan LAGI foto lama).
     */
    public function tayang(string $id): ResponseInterface
    {
        $baris   = $this->foto($id);
        $berubah = Pustaka::tayangkan((int) $baris['id'], Current::identity()->userId);

        return redirect()->to('/admin/teks/foto?pesan=' . ($berubah ? 'tayang' : 'sudah-tayang'));
    }

    /**
     * POST: kembali ke foto bawaan.
     */
    public function bawaan(): ResponseInterface
    {
        $berubah = Pustaka::keBawaan(Current::identity()->userId);

        return redirect()->to('/admin/teks/foto?pesan=' . ($berubah ? 'bawaan' : 'sudah-bawaan'));
    }

    /**
     * @return array<string, mixed>
     */
    private function foto(string $id): array
    {
        $baris = ctype_digit($id) ? Pustaka::cari((int) $id) : null;
        if ($baris === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        return $baris;
    }

    /**
     * @return list<array{jenis: string, teks: string}>
     */
    private function pesan(): array
    {
        $k = (string) $this->request->getGet('pesan');

        return isset(self::PESAN[$k]) ? [['jenis' => self::PESAN[$k][0], 'teks' => lang(self::PESAN[$k][1])]] : [];
    }

    /**
     * @param array<string, string> $isian       isian form yang dikembalikan sesudah galat
     * @param list<string>          $galat       nama isian yang salah
     * @param string|null           $galatBerkas kode galat berkas
     */
    private function daftar(array $isian, array $galat, ?string $galatBerkas, int $status = 200): ResponseInterface
    {
        $semua = Pustaka::semua();
        $pesan = $this->pesan();
        if ($galat !== [] || $galatBerkas !== null) {
            $pesan = [['jenis' => 'galat', 'teks' => lang('Sunting.foto.galat')]];
        }
        $bahasa = $this->request->getLocale();

        return $this->response->setStatusCode($status)->setBody(view('admin/foto_daftar', [
            'judul'       => lang('Sunting.foto.judul'),
            'semua'       => $semua,
            'idTayang'    => Pustaka::idTayang(),
            'bawaan'      => Pustaka::bawaan($bahasa),
            'orang'       => UserDirectory::byIds(array_column($semua, 'uploaded_by')),
            'isian'       => $isian,
            'galat'       => $galat,
            'galatBerkas' => $galatBerkas,
            'batas'       => ['mb' => intdiv(Gambar::MAKS_BYTE, 1024 * 1024), 'lebar' => Gambar::MIN_LEBAR, 'tinggi' => Gambar::MIN_TINGGI, 'mp' => intdiv(Gambar::MAKS_PIKSEL, 1_000_000)],
            'pesan'       => $pesan,
        ]));
    }
}
