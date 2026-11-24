<?php

declare(strict_types=1);

namespace Lokandita\Auth\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\ResponseInterface;
use Lokandita\Auth\Current;
use Lokandita\Auth\Identity;
use Lokandita\Auth\Log;
use Lokandita\Auth\Seb\Seb;

/**
 * Panel SEB satu mata kuliah (SPEC.md §13.1): superadmin dan pengampunya. Pengajar yang
 * melihat mata kuliah itu mendapat 403; yang tidak melihatnya 404. Siapa melihat dan siapa
 * mengelola diputuskan aplikasi (Sumber::matkulUntukPanel), bukan di sini.
 * Sama di setiap aplikasi yang memakai SEB; rutenya didaftarkan Lokandita\Auth\Routes::seb().
 */
final class SebKelola extends Controller
{
    protected $helpers = ['form'];

    private const PESAN = [
        'dibuat'   => ['sukses', 'Seb.kelola.pesanDibuat'],
        'dirotasi' => ['sukses', 'Seb.kelola.pesanDirotasi'],
        'kunci'    => ['sukses', 'Seb.kelola.pesanKunci'],
    ];

    public function lihat(int $matkulId): ResponseInterface
    {
        $matkul = $this->matkulUntukDikelola($matkulId);

        return $matkul instanceof ResponseInterface ? $matkul : $this->tampil($matkul, [], '');
    }

    /**
     * Membuat konfigurasi pertama, atau merotasinya (kunci lama tidak berlaku lagi).
     */
    public function simpan(int $matkulId): ResponseInterface
    {
        $matkul = $this->matkulUntukDikelola($matkulId);
        if ($matkul instanceof ResponseInterface) {
            return $matkul;
        }
        $sandi = (string) $this->request->getPost('sandi_keluar');
        if (mb_strlen($sandi) < Seb::SANDI_KELUAR_MIN || mb_strlen($sandi) > 200) {
            return $this->tampil($matkul, ['sandi_keluar' => 'sandiKeluar'], '', 422);
        }
        $versi = Seb::buat($matkulId, $sandi);

        return redirect()->to('/mata-kuliah/' . $matkulId . '/seb?pesan=' . ($versi === 1 ? 'dibuat' : 'dirotasi'));
    }

    /**
     * Mendaftarkan Browser Exam Key, satu per baris; isi kotak menggantikan seluruh daftar.
     */
    public function kunci(int $matkulId): ResponseInterface
    {
        $matkul = $this->matkulUntukDikelola($matkulId);
        if ($matkul instanceof ResponseInterface) {
            return $matkul;
        }
        $isian = (string) $this->request->getPost('kunci');
        if (Seb::aktif($matkulId) === null) {
            return $this->tampil($matkul, ['kunci' => 'belumAda'], $isian, 422);
        }
        $kunci = [];
        foreach (preg_split('/\R/', $isian) ?: [] as $baris) {
            $baris = strtolower(trim($baris));
            if ($baris === '') {
                continue;
            }
            if (preg_match('/^[0-9a-f]{64}$/', $baris) !== 1) {
                return $this->tampil($matkul, ['kunci' => 'kunciBentuk'], $isian, 422);
            }
            $kunci[] = $baris;
        }
        if (count($kunci) > 20) {
            return $this->tampil($matkul, ['kunci' => 'kunciBanyak'], $isian, 422);
        }
        Seb::daftarkanKunci($matkulId, $kunci);

        return redirect()->to('/mata-kuliah/' . $matkulId . '/seb?pesan=kunci');
    }

    private function identitas(): Identity
    {
        $identitas = Current::identity();
        if ($identitas === null) {
            throw new \LogicException('Rute ini tidak dipasangi filter masuk.');
        }

        return $identitas;
    }

    /**
     * @return array{id: int, code: string, name: string, boleh_kelola: bool}|ResponseInterface
     */
    private function matkulUntukDikelola(int $id): array|ResponseInterface
    {
        $matkul = Seb::sumber()->matkulUntukPanel($this->identitas(), $id);
        if ($matkul === null) {
            // Tidak ada ATAU di luar hak pengguna: sengaja tidak dibedakan.
            return $this->tolak(404, 'outside_scope_or_missing', 'Lokandita.tidakDitemukan', 'Seb.kelola.tidakDitemukan', ['what' => 'course']);
        }

        return $matkul['boleh_kelola'] ? $matkul : $this->tolak(403, 'role', 'Lokandita.tidakBerhak', 'Lokandita.tidakBerhak');
    }

    /**
     * @param array<string, string> $lain butir tambahan untuk log
     */
    private function tolak(int $status, string $alasan, string $kunciJudul, string $kunciTeks, array $lain = []): ResponseInterface
    {
        Log::event('access.denied', ['user' => $this->identitas()->userId, 'reason' => $alasan] + $lain + ['path' => $this->request->getUri()->getPath()]);

        return $this->response->setStatusCode($status)->setBody(view('Lokandita\Auth\Views\pesan', [
            'judul' => lang($kunciJudul),
            'teks'  => lang($kunciTeks),
        ]));
    }

    /**
     * @param array{id: int, code: string, name: string, boleh_kelola: bool} $matkul
     * @param array<string, string>                                          $galat  kolom => kunci Seb.galat.*
     */
    private function tampil(array $matkul, array $galat, string $isianKunci, int $status = 200): ResponseInterface
    {
        $aktif = Seb::aktif($matkul['id']);
        $kunci = $aktif === null ? [] : Seb::kunciTerdaftar((int) $aktif['id']);
        $k     = (string) $this->request->getGet('pesan');

        return $this->response->setStatusCode($status)->setBody(view('Lokandita\Auth\Views\seb_kelola', [
            'judul'       => lang('Seb.kelola.judul'),
            'nav'         => Seb::sumber()->navigasi($this->identitas()),
            'matkul'      => $matkul,
            'aktif'       => $aktif,
            'kunci'       => $kunci,
            'isianKunci'  => $galat === [] ? implode("\n", $kunci) : $isianKunci,
            'galat'       => $galat,
            'pesan'       => isset(self::PESAN[$k]) ? [['jenis' => self::PESAN[$k][0], 'teks' => lang(self::PESAN[$k][1])]] : [],
            'diberlakukan' => Seb::diberlakukan(),
            'asal'        => Seb::asal(),
        ]));
    }
}
