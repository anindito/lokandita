<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Libraries\SebUjian;
use App\Libraries\Ujian;
use CodeIgniter\HTTP\ResponseInterface;
use Lokandita\Auth\Seb\Seb;

/**
 * Panel Safe Exam Browser SATU UJIAN (SPEC-LOKAUJIAN.md §9.1; KONTRAK.md §23), bagi yang mengelola
 * mata kuliahnya: membuat atau merotasi konfigurasi, mengunduh berkas .seb, mendaftarkan Browser
 * Exam Key. Ujian mata kuliah lain dijawab 404; pengajar 403 — seperti rute ujian lainnya.
 */
final class SebPanel extends BaseController
{
    private const PESAN = [
        'dibuat'    => ['sukses', 'SebUjian.pesan.dibuat'],
        'dirotasi'  => ['sukses', 'SebUjian.pesan.dirotasi'],
        'kunci'     => ['sukses', 'SebUjian.pesan.kunci'],
    ];

    /**
     * @return array{0: array<string, mixed>, 1: array<string, mixed>}|ResponseInterface [mata kuliah, ujian]
     */
    private function diminta(string $matkulId, string $id): array|ResponseInterface
    {
        $matkul = $this->matkulDikelola((int) $matkulId);
        if ($matkul instanceof ResponseInterface) {
            return $matkul;
        }
        $ujian = (new Ujian($this->akses()))->lihat((int) $matkul['id'], (int) $id);

        return $ujian === null ? $this->tidakDitemukan('exam') : [$matkul, $ujian];
    }

    /**
     * @param array<string, mixed> $matkul
     * @param array<string, mixed> $ujian
     * @param list<string>         $galat
     */
    private function tampil(array $matkul, array $ujian, array $galat = [], string $kunciDiketik = '', int $status = 200): ResponseInterface
    {
        $aktif = SebUjian::aktif((int) $ujian['id']);

        return $this->halaman('seb_panel', [
            'judul'        => lang('SebUjian.panel.judul') . ' — ' . $ujian['title'],
            'matkul'       => $matkul,
            'ujian'        => $ujian,
            'aktif'        => $aktif,
            'kunci'        => $aktif === null ? [] : SebUjian::kunciTerdaftar((int) $aktif['id']),
            'kunciDiketik' => $kunciDiketik,
            'dijalankan'   => SebUjian::sedangDijalankan((int) $ujian['id']),
            'diberlakukan' => Seb::diberlakukan(),
            'urlMulai'     => SebUjian::urlMulai((int) $ujian['id']),
            'galat'        => $galat,
            'pesan'        => $this->pesanDariQuery(self::PESAN),
        ], $status);
    }

    public function lihat(string $matkulId, string $id): ResponseInterface
    {
        $d = $this->diminta($matkulId, $id);

        return $d instanceof ResponseInterface ? $d : $this->tampil($d[0], $d[1]);
    }

    /**
     * Membuat konfigurasi, atau merotasinya. Rotasi mematikan berkas dan semua kunci lama seketika,
     * dan ditolak (409) selama ujian itu sedang dijalankan.
     */
    public function simpan(string $matkulId, string $id): ResponseInterface
    {
        $d = $this->diminta($matkulId, $id);
        if ($d instanceof ResponseInterface) {
            return $d;
        }
        [$matkul, $ujian] = $d;
        $sandi            = (string) $this->request->getPost('sandi_keluar');
        if (mb_strlen($sandi) < Seb::SANDI_KELUAR_MIN || mb_strlen($sandi) > 200) {
            return $this->tampil($matkul, $ujian, [lang('SebUjian.galat.sandi', [Seb::SANDI_KELUAR_MIN])], '', 422);
        }
        $versi = SebUjian::buat((int) $matkul['id'], (int) $ujian['id'], $sandi);
        if (! is_int($versi)) {
            return $this->tampil($matkul, $ujian, [lang('SebUjian.galat.dijalankan')], '', 409);
        }

        return redirect()->to('/mata-kuliah/' . (int) $matkul['id'] . '/ujian/' . (int) $ujian['id'] . '/seb?pesan=' . ($versi === 1 ? 'dibuat' : 'dirotasi'));
    }

    /**
     * Mengganti daftar Browser Exam Key: satu per baris, 64 karakter heksadesimal.
     */
    public function kunci(string $matkulId, string $id): ResponseInterface
    {
        $d = $this->diminta($matkulId, $id);
        if ($d instanceof ResponseInterface) {
            return $d;
        }
        [$matkul, $ujian] = $d;
        $ketik            = (string) $this->request->getPost('kunci');
        $kunci            = [];
        foreach (preg_split('/\s+/', trim($ketik)) ?: [] as $baris) {
            if ($baris === '') {
                continue;
            }
            $k = strtolower($baris);
            if (preg_match('/^[0-9a-f]{64}$/', $k) !== 1) {
                return $this->tampil($matkul, $ujian, [lang('SebUjian.galat.kunci')], $ketik, 422);
            }
            $kunci[] = $k;
        }
        $kunci = array_values(array_unique($kunci));
        if (count($kunci) > SebUjian::KUNCI_MAKS) {
            return $this->tampil($matkul, $ujian, [lang('SebUjian.galat.kunciBanyak', [SebUjian::KUNCI_MAKS])], $ketik, 422);
        }
        if (! SebUjian::daftarkanKunci((int) $matkul['id'], (int) $ujian['id'], $kunci)) {
            return $this->tampil($matkul, $ujian, [lang('SebUjian.galat.belumAda')], $ketik, 422);
        }

        return redirect()->to('/mata-kuliah/' . (int) $matkul['id'] . '/ujian/' . (int) $ujian['id'] . '/seb?pesan=kunci');
    }

    public function unduh(string $matkulId, string $id): ResponseInterface
    {
        $d = $this->diminta($matkulId, $id);
        if ($d instanceof ResponseInterface) {
            return $d;
        }
        $berkas = SebUjian::berkas((int) $d[1]['id'], (string) $d[0]['code']);

        return $berkas === null ? $this->tidakDitemukan('seb_file') : self::kirim($this->response, $berkas);
    }

    /**
     * @param array{nama: string, isi: string} $berkas
     */
    public static function kirim(ResponseInterface $response, array $berkas): ResponseInterface
    {
        return $response
            ->setStatusCode(200)
            ->setHeader('Content-Type', 'application/seb')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $berkas['nama'] . '"')
            ->setHeader('Cache-Control', 'no-store')
            ->setHeader('X-Content-Type-Options', 'nosniff')
            ->setBody($berkas['isi']);
    }
}
