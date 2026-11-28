<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Libraries\Akademik;
use App\Libraries\Notifikasi;
use App\Policy\Akses;
use CodeIgniter\Controller;
use CodeIgniter\HTTP\ResponseInterface;
use Lokandita\Auth\Current;
use Lokandita\Auth\Log;

/**
 * Dasar semua halaman LokaKode. Setiap rute di sini berada di belakang filter
 * `masuk` (KONTRAK.md §4), jadi identitas selalu ada.
 */
abstract class BaseController extends Controller
{
    protected $helpers = ['form', 'angka'];

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

    protected function akademik(): Akademik
    {
        return new Akademik($this->akses());
    }

    /**
     * @param array<string, mixed> $data
     */
    protected function halaman(string $view, array $data = [], int $status = 200): ResponseInterface
    {
        $data['akses'] = $this->akses();
        $data['pesan'] ??= [];
        // Pemberitahuan mahasiswa (KONTRAK.md §17) dibuat dan dihitung saat halaman dimuat:
        // tidak ada proses latar. Jumlahnya ditampilkan App\Views\_nav.
        if ($this->akses()->mahasiswa()) {
            $saya = $this->akses()->identitas()->userId;
            Notifikasi::segarkan($saya);
            $data['notifBelum'] = Notifikasi::belumDibaca($saya);
        }

        return $this->response->setStatusCode($status)->setBody(view($view, $data));
    }

    /**
     * Pesan sesudah pengalihan: ?pesan=<kunci>. Hanya kunci yang terdaftar yang ditampilkan.
     *
     * @param array<string, array{0: string, 1: string}> $peta kunci => [jenis, kunci bahasa]
     *
     * @return list<array{jenis: string, teks: string}>
     */
    protected function pesanDariQuery(array $peta): array
    {
        $k = (string) $this->request->getGet('pesan');
        // ?n=<angka> mengisi {0} pada pesan yang menyebut jumlah.
        $n = max(0, (int) $this->request->getGet('n'));

        return isset($peta[$k]) ? [['jenis' => $peta[$k][0], 'teks' => lang($peta[$k][1], [$n])]] : [];
    }

    /**
     * Jawaban untuk baris yang tidak ada ATAU di luar hak pengguna. Keduanya
     * sengaja tidak dibedakan, supaya nomor di URL tidak bisa dipakai menebak
     * kelas atau mata kuliah mana yang ada.
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
            'teks'  => lang('Kode.tidakDitemukan'),
        ]));
    }

    /**
     * Klarifikasi dan pengumuman kelas (KONTRAK.md §17): siapa pun yang melihat kelas itu boleh
     * menulis, kecuali dosen di tahun ajaran yang sudah diarsipkan (hanya baca).
     *
     * @param array<string, mixed> $kelas baris dari Akademik::kelas()
     */
    protected function bolehMenulis(array $kelas): bool
    {
        return $kelas['tahun_status'] === 'active' || $this->akses()->superadmin();
    }

    /**
     * Peran ini tidak pernah boleh melakukan tindakan ini.
     */
    protected function terlarang(string $kunciTeks = 'Lokandita.tidakBerhak'): ResponseInterface
    {
        Log::event('access.denied', [
            'user'   => $this->akses()->identitas()->userId,
            'reason' => 'role',
            'path'   => $this->request->getUri()->getPath(),
        ]);

        return $this->response->setStatusCode(403)->setBody(view('Lokandita\Auth\Views\pesan', [
            'judul' => lang('Lokandita.tidakBerhak'),
            'teks'  => lang($kunciTeks),
        ]));
    }
}
