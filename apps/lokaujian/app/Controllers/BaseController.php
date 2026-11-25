<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Libraries\Media;
use App\Libraries\Roster;
use App\Policy\Akses;
use CodeIgniter\Controller;
use CodeIgniter\HTTP\ResponseInterface;
use Lokandita\Auth\Current;
use Lokandita\Auth\Log;

/**
 * Dasar semua halaman bersesi LokaUjian. Setiap rute di sini berada di belakang filter
 * `masuk` (KONTRAK.md §4), jadi identitas selalu ada.
 */
abstract class BaseController extends Controller
{
    private ?Akses $akses = null;

    protected function akses(): Akses
    {
        if ($this->akses === null) {
            $identitas = Current::identity();
            if ($identitas === null) {
                throw new \LogicException('Rute ini tidak dipasangi filter masuk.');
            }
            $this->akses = new Akses($identitas);
        }

        return $this->akses;
    }

    protected function roster(): Roster
    {
        return new Roster($this->akses());
    }

    /**
     * Jawaban untuk baris yang tidak ada ATAU di luar hak pengguna. Keduanya sengaja tidak
     * dibedakan, supaya nomor di URL tidak bisa dipakai menebak kelas atau mata kuliah mana
     * yang ada.
     */
    protected function tidakDitemukan(string $apa): ResponseInterface
    {
        Log::event('access.denied', [
            'user'   => $this->akses()->identitas()->userId,
            'reason' => 'outside_scope_or_missing',
            'what'   => $apa,
            'path'   => $this->request->getUri()->getPath(),
        ]);

        return $this->response->setStatusCode(404)->setBody(view('Lokandita\Auth\Views\pesan', [
            'judul' => lang('Lokandita.tidakDitemukan'),
            'teks'  => lang('Ujian.tidakDitemukan'),
        ]));
    }

    /**
     * Jawaban untuk yang terlihat tetapi bukan haknya: pengajar pada bank soal mata kuliah
     * tempatnya mengajar (SPEC-LOKAUJIAN.md §3).
     */
    protected function terlarang(): ResponseInterface
    {
        Log::event('access.denied', [
            'user'   => $this->akses()->identitas()->userId,
            'reason' => 'role',
            'path'   => $this->request->getUri()->getPath(),
        ]);

        return $this->response->setStatusCode(403)->setBody(view('Lokandita\Auth\Views\pesan', [
            'judul' => lang('Lokandita.tidakBerhak'),
            'teks'  => lang('Bank.bukanPengampu'),
        ]));
    }

    /**
     * Mata kuliah yang bank soal dan ujiannya boleh dikelola pengguna ini: superadmin, atau
     * pengampunya. Mengembalikan baris roster, atau jawaban penolakannya: 404 bila mata kuliah
     * itu tidak ada atau di luar yang dilihatnya, 403 bila ia hanya pengajar di sana.
     *
     * @return array<string, mixed>|ResponseInterface
     */
    protected function matkulDikelola(int $id): array|ResponseInterface
    {
        $matkul = $this->roster()->matkul($id);
        if ($matkul === null) {
            return $this->tidakDitemukan('course');
        }

        return (int) $matkul['diampu'] === 1 ? $matkul : $this->terlarang();
    }

    /**
     * Langkah pertama untuk satu isian gambar di form: apakah ada berkas baru, gambar lama yang
     * dipertahankan, atau tidak ada gambar. Belum ada yang disimpan di sini.
     *
     * @param array<string, mixed>|null $lama gambar yang sekarang terpasang
     *
     * @return array{apa: 'baru'|'lama'|'tanpa'|'galat', jalur?: string, lama?: array<string, mixed>, galat?: string}
     */
    protected function rencanaGambar(string $medan, ?array $lama, bool $hapus): array
    {
        $berkas = $this->request->getFile($medan);
        if ($berkas !== null && $berkas->getError() !== UPLOAD_ERR_NO_FILE) {
            if ($berkas->getError() !== UPLOAD_ERR_OK) {
                // Ditolak PHP sebelum sampai ke aplikasi: hampir selalu karena ukurannya.
                return ['apa' => 'galat', 'galat' => in_array($berkas->getError(), [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true) ? 'terlalu_besar' : 'tidak_terbaca'];
            }

            return ['apa' => 'baru', 'jalur' => $berkas->getTempName()];
        }

        return $lama !== null && ! $hapus ? ['apa' => 'lama', 'lama' => $lama] : ['apa' => 'tanpa'];
    }

    /**
     * Langkah kedua, sesudah semua isian lolos: berkas baru diperiksa dan diolah
     * (App\Libraries\Media), gambar lama dipakai lagi dengan teks alternatif yang baru.
     *
     * @param array<string, mixed> $rencana hasil rencanaGambar()
     *
     * @return array<string, mixed>|string|null gambar untuk bentuk baku; kode galat; null bila tanpa gambar
     */
    protected function wujudkanGambar(int $matkulId, array $rencana, string $alt): array|string|null
    {
        if ($rencana['apa'] === 'baru') {
            $baris = (new Media($this->akses()))->simpan($matkulId, $rencana['jalur'], $this->akses()->identitas()->userId);

            return is_string($baris) ? $baris : ['alt' => $alt] + $baris;
        }

        return $rencana['apa'] === 'lama' ? ['alt' => $alt] + $rencana['lama'] : null;
    }

    /**
     * Pesan sesudah redirect: ?pesan=<kunci> dipetakan ke teks di berkas bahasa. Kunci yang tidak
     * dikenal tidak menampilkan apa pun, jadi query tidak bisa dipakai menyisipkan teks.
     *
     * @param array<string, array{0: string, 1: string}> $peta kunci => [jenis, kunci bahasa]
     *
     * @return list<array{jenis: string, teks: string}>
     */
    protected function pesanDariQuery(array $peta): array
    {
        $k = (string) $this->request->getGet('pesan');

        return isset($peta[$k]) ? [['jenis' => $peta[$k][0], 'teks' => lang($peta[$k][1])]] : [];
    }

    /**
     * @param array<string, mixed> $data
     */
    protected function halaman(string $view, array $data = [], int $status = 200): ResponseInterface
    {
        $data['akses'] = $this->akses();

        return $this->response->setStatusCode($status)->setBody(view($view, $data));
    }
}
