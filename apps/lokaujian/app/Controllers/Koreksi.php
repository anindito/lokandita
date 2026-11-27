<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Libraries\Koreksi as Pustaka;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Koreksi kunci atau poin sesudah ujian (SPEC-LOKAUJIAN.md §11), bagi yang mengelola mata
 * kuliahnya. Halaman ini — tidak seperti pratinjau — MENAMPILKAN kunci.
 */
final class Koreksi extends BaseController
{
    private const PESAN = [
        'dikoreksi' => ['sukses', 'Hasil.koreksi.pesanDikoreksi'],
        'g_poin'    => ['galat', 'Hasil.koreksi.galatPoin'],
        'g_kunci'   => ['galat', 'Hasil.koreksi.galatKunci'],
        'g_sama'    => ['galat', 'Hasil.koreksi.galatSama'],
    ];

    public function lihat(string $matkulId, string $ujianId): ResponseInterface
    {
        $matkul = $this->matkulDikelola((int) $matkulId);
        if ($matkul instanceof ResponseInterface) {
            return $matkul;
        }
        $data = (new Pustaka($this->akses()))->lihat((int) $matkul['id'], (int) $ujianId);
        if ($data === null) {
            return $this->tidakDitemukan('exam');
        }
        $pesan = $this->pesanDariQuery(self::PESAN);
        if ($pesan !== [] && $this->request->getGet('pesan') === 'dikoreksi') {
            // Angkanya hanya bilangan dari query; teksnya dari berkas bahasa.
            $pesan[0]['teks'] = lang('Hasil.koreksi.pesanDikoreksi', [(int) $this->request->getGet('p'), (int) $this->request->getGet('b')]);
        }

        return $this->halaman('ujian_koreksi', ['judul' => lang('Hasil.koreksi.judul') . ' — ' . $data['ujian']['title'], 'matkul' => $matkul, 'pesan' => $pesan] + $data);
    }

    public function ubah(string $matkulId, string $ujianId, string $soalId): ResponseInterface
    {
        $matkul = $this->matkulDikelola((int) $matkulId);
        if ($matkul instanceof ResponseInterface) {
            return $matkul;
        }
        $hasil = (new Pustaka($this->akses()))->ubah((int) $matkul['id'], (int) $ujianId, (int) $soalId, (string) $this->request->getPost('poin'), (string) $this->request->getPost('kunci'));
        if ($hasil === 'tidak_ada') {
            return $this->tidakDitemukan('exam_question');
        }
        $kembali = '/mata-kuliah/' . (int) $matkul['id'] . '/ujian/' . (int) $ujianId . '/koreksi';

        return redirect()->to($kembali . (is_array($hasil) ? '?pesan=dikoreksi&p=' . $hasil['pengerjaan'] . '&b=' . $hasil['berubah'] : '?pesan=g_' . $hasil));
    }
}
