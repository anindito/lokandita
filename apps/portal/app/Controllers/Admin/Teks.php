<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\Aplikasi;
use App\Libraries\Foto;
use App\Libraries\Teks as Pustaka;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\ResponseInterface;
use Lokandita\Auth\Current;
use Lokandita\Auth\Locale;
use Lokandita\Auth\UserDirectory;

/**
 * Penyuntingan teks halaman publik Portal (KONTRAK.md §15). Semua rute di sini berada
 * di belakang filter `masuk:superadmin`; kelas ini tidak memeriksa peran lagi.
 */
final class Teks extends BaseController
{
    private const PESAN = [
        'tersimpan'      => ['sukses', 'Sunting.pesan.tersimpan'],
        'tidak-berubah'  => ['sukses', 'Sunting.pesan.tidakBerubah'],
        'dikembalikan'   => ['sukses', 'Sunting.pesan.dikembalikan'],
        'sudah-bawaan'   => ['sukses', 'Sunting.pesan.sudahBawaan'],
    ];

    public function index(): ResponseInterface
    {
        $baris = [];
        $ids   = [];
        foreach (Locale::DAFTAR as $b) {
            $baris[$b] = Pustaka::baris($b);
            $ids       = array_merge($ids, array_column($baris[$b], 'updated_by'));
        }
        $k     = (string) $this->request->getGet('pesan');
        $pesan = isset(self::PESAN[$k]) ? [['jenis' => self::PESAN[$k][0], 'teks' => lang(self::PESAN[$k][1])]] : [];

        return $this->response->setBody(view('admin/teks_daftar', [
            'judul'   => lang('Sunting.judul'),
            'halaman' => Pustaka::HALAMAN,
            'bahasa'  => Locale::DAFTAR,
            'baris'   => $baris,
            'orang'   => UserDirectory::byIds($ids),
            'pesan'   => $pesan,
        ]));
    }

    public function sunting(string $halaman, string $bahasa): ResponseInterface
    {
        $this->harusDikenal($halaman, $bahasa);

        return $this->form($halaman, $bahasa, $this->berlaku($halaman, $bahasa), [], null);
    }

    /**
     * POST: `aksi` = pratinjau (tidak menyimpan apa pun) atau simpan (langsung berlaku).
     */
    public function kirim(string $halaman, string $bahasa): ResponseInterface
    {
        $this->harusDikenal($halaman, $bahasa);
        $t              = $this->request->getPost('t');
        [$isi, $galat]  = Pustaka::dariForm(is_array($t) ? $t : [], Pustaka::bawaan($halaman, $bahasa));
        $aksi           = (string) $this->request->getPost('aksi');
        if ($galat !== [] || ! in_array($aksi, ['pratinjau', 'simpan'], true)) {
            return $this->form($halaman, $bahasa, $isi, $galat, null, 422);
        }
        if ($aksi === 'pratinjau') {
            return $this->form($halaman, $bahasa, $isi, [], $isi);
        }
        $berubah = Pustaka::simpan($halaman, $bahasa, $isi, Current::identity()->userId);

        return redirect()->to('/admin/teks?pesan=' . ($berubah ? 'tersimpan' : 'tidak-berubah'));
    }

    /**
     * POST: hapus teks pengganti; halaman kembali ke berkas bahasa.
     */
    public function bawaan(string $halaman, string $bahasa): ResponseInterface
    {
        $this->harusDikenal($halaman, $bahasa);
        $ada = Pustaka::kembalikan($halaman, $bahasa, Current::identity()->userId);

        return redirect()->to('/admin/teks?pesan=' . ($ada ? 'dikembalikan' : 'sudah-bawaan'));
    }

    /**
     * 200 baris audit terakhir, dengan teks sebelum dan sesudahnya.
     */
    public function riwayat(): ResponseInterface
    {
        $baris = db_connect()->query('SELECT id, actor_id, action, halaman, bahasa, sebelum, sesudah, created_at FROM audit_log ORDER BY id DESC LIMIT 200')->getResultArray();
        foreach ($baris as &$b) {
            foreach (['sebelum', 'sesudah'] as $k) {
                $b[$k] = $b[$k] === null ? null : self::ratakan((array) json_decode((string) $b[$k], true));
            }
        }
        unset($b);

        return $this->response->setBody(view('admin/teks_riwayat', [
            'judul' => lang('Sunting.riwayat.judul'),
            'baris' => $baris,
            'orang' => UserDirectory::byIds(array_column($baris, 'actor_id')),
        ]));
    }

    /**
     * Waktu UTC dari basis data, ditampilkan dalam Asia/Jakarta.
     */
    public static function wib(string $utc): string
    {
        return (new \DateTimeImmutable($utc, new \DateTimeZone('UTC')))->setTimezone(new \DateTimeZone('Asia/Jakarta'))->format('d-m-Y H:i') . ' WIB';
    }

    private function harusDikenal(string $halaman, string $bahasa): void
    {
        if (! Pustaka::dikenal($halaman, $bahasa)) {
            throw PageNotFoundException::forPageNotFound();
        }
    }

    /**
     * Teks yang sedang berlaku TANPA tanggal buatan: itulah yang diisikan ke form.
     *
     * @return array<string, mixed>
     */
    private function berlaku(string $halaman, string $bahasa): array
    {
        return Pustaka::baris($bahasa)[$halaman]['isi'] ?? Pustaka::bawaan($halaman, $bahasa);
    }

    /**
     * @param array<string, mixed>      $nilai     isi form
     * @param list<string>              $galat     kunci isian yang salah
     * @param array<string, mixed>|null $pratinjau teks yang dipratinjau, atau null
     */
    private function form(string $halaman, string $bahasa, array $nilai, array $galat, ?array $pratinjau, int $status = 200): ResponseInterface
    {
        $data = [
            'judul'     => lang('Sunting.halaman.' . $halaman) . ' — ' . lang('Lokandita.bahasa' . ucfirst($bahasa)),
            'halaman'   => $halaman,
            'bahasa'    => $bahasa,
            'bawaan'    => Pustaka::bawaan($halaman, $bahasa),
            'nilai'     => $nilai,
            'galat'     => $galat,
            'diubah'    => isset(Pustaka::baris($bahasa)[$halaman]),
            'pratinjau' => $pratinjau,
            'kontak'    => (string) getenv('CONTACT_EMAIL'),
            'pesan'     => $galat === [] ? [] : [['jenis' => 'galat', 'teks' => lang('Sunting.galat')]],
        ];
        if ($pratinjau !== null) {
            // Kartu aplikasi tampil di halaman depan, jadi keduanya dipratinjau di sana.
            $data['pratinjauDepan'] = [
                't'        => $halaman === 'depan' ? $pratinjau : Pustaka::ambil('depan', $bahasa),
                'ket'      => $halaman === 'aplikasi' ? $pratinjau : Pustaka::ambil('aplikasi', $bahasa),
                'aplikasi' => Aplikasi::daftar(),
                'foto'     => Foto::tayang($bahasa),
                'bahasa'   => $bahasa,
            ];
        }

        return $this->response->setStatusCode($status)->setBody(view('admin/teks_sunting', $data));
    }

    /**
     * Teks bersarang menjadi baris "kunci: teks" untuk dibaca dan disalin dari riwayat.
     *
     * @param array<int|string, mixed> $a
     *
     * @return list<string>
     */
    private static function ratakan(array $a, string $awalan = ''): array
    {
        $hasil = [];
        foreach ($a as $k => $v) {
            $nama = $awalan . (is_int($k) ? (string) ($k + 1) : $k);
            if (is_array($v)) {
                $hasil = array_merge($hasil, self::ratakan($v, $nama . '.'));
            } else {
                $hasil[] = $nama . ': ' . (string) $v;
            }
        }

        return $hasil;
    }
}
