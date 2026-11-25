<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Libraries\Aturan;
use App\Libraries\Bank as Pustaka;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Kelompok soal (SPEC-LOKAUJIAN.md §5.2): teks bersama dan/atau satu gambar, dengan 1–20 soal
 * dalam urutan tetap. Soal-soalnya dibuat dan disunting lewat App\Controllers\Soal.
 */
final class Kelompok extends BaseController
{
    private const PESAN = [
        'disimpan'    => ['sukses', 'Bank.pesan.disimpan'],
        'soalDihapus' => ['sukses', 'Bank.pesan.soalDihapus'],
        'dipakai'     => ['galat', 'Bank.pesan.dipakai'],
        'diarsip'     => ['sukses', 'Bank.pesan.diarsipkan'],
        'dipulihkan'  => ['sukses', 'Bank.pesan.dipulihkan'],
    ];

    private function alamat(int $matkulId): string
    {
        return '/mata-kuliah/' . $matkulId . '/bank';
    }

    /**
     * @param array<string, mixed>      $matkul
     * @param array<string, mixed>|null $kelompok
     * @param array<string, mixed>      $isian
     * @param list<string>              $galat
     */
    private function form(array $matkul, ?array $kelompok, array $isian, array $galat = [], int $status = 200): ResponseInterface
    {
        return $this->halaman('kelompok_form', [
            'judul'    => lang($kelompok === null ? 'Bank.kelompok.baru' : 'Bank.kelompok.sunting') . ' — ' . $matkul['code'],
            'matkul'   => $matkul,
            'kelompok' => $kelompok,
            'isian'    => $isian,
            'galat'    => $galat,
        ], $status);
    }

    public function baru(string $matkulId): ResponseInterface
    {
        $matkul = $this->matkulDikelola((int) $matkulId);

        return $matkul instanceof ResponseInterface ? $matkul : $this->form($matkul, null, ['text' => '', 'alt' => '', 'gambar' => null]);
    }

    public function buat(string $matkulId): ResponseInterface
    {
        $matkul = $this->matkulDikelola((int) $matkulId);

        return $matkul instanceof ResponseInterface ? $matkul : $this->terima($matkul, null);
    }

    public function lihat(string $matkulId, string $id): ResponseInterface
    {
        $matkul = $this->matkulDikelola((int) $matkulId);
        if ($matkul instanceof ResponseInterface) {
            return $matkul;
        }
        $bank     = new Pustaka($this->akses());
        $kelompok = $bank->kelompok((int) $matkul['id'], (int) $id);
        if ($kelompok === null) {
            return $this->tidakDitemukan('group');
        }

        return $this->halaman('kelompok_lihat', [
            'judul'    => lang('Bank.kelompok.satu') . ' — ' . $matkul['code'],
            'matkul'   => $matkul,
            'kelompok' => $kelompok,
            'dipakai'  => $bank->pemakaian((int) $matkul['id'])['k' . $kelompok['id']] ?? [],
            'pesan'    => $this->pesanDariQuery(self::PESAN),
        ]);
    }

    public function sunting(string $matkulId, string $id): ResponseInterface
    {
        $matkul = $this->matkulDikelola((int) $matkulId);
        if ($matkul instanceof ResponseInterface) {
            return $matkul;
        }
        $kelompok = (new Pustaka($this->akses()))->kelompok((int) $matkul['id'], (int) $id);
        if ($kelompok === null) {
            return $this->tidakDitemukan('group');
        }

        return $this->form($matkul, $kelompok, ['text' => $kelompok['text'], 'alt' => $kelompok['image']['alt'] ?? '', 'gambar' => $kelompok['image']]);
    }

    public function simpan(string $matkulId, string $id): ResponseInterface
    {
        $matkul = $this->matkulDikelola((int) $matkulId);
        if ($matkul instanceof ResponseInterface) {
            return $matkul;
        }
        $kelompok = (new Pustaka($this->akses()))->kelompok((int) $matkul['id'], (int) $id);

        return $kelompok === null ? $this->tidakDitemukan('group') : $this->terima($matkul, $kelompok);
    }

    /**
     * @param array<string, mixed>      $matkul
     * @param array<string, mixed>|null $lama
     */
    private function terima(array $matkul, ?array $lama): ResponseInterface
    {
        $matkulId = (int) $matkul['id'];
        $isian    = [
            'text' => (string) $this->request->getPost('text'), 'alt' => (string) $this->request->getPost('alt'),
            'gambar' => $lama['image'] ?? null,
        ];
        $rencana = $this->rencanaGambar('gambar', $isian['gambar'], (string) $this->request->getPost('hapus_gambar') === '1');
        $aturan  = new Aturan();
        $baku    = $aturan->kelompok(['text' => $isian['text'], 'image' => $rencana['apa'] === 'tanpa' ? null : true], '', false);
        $galat   = [];
        foreach ($aturan->galat() as [, $kode, $arg]) {
            // Kelompok tanpa teks dan tanpa gambar tidak punya bagian bersama sama sekali.
            $galat[] = lang('Bank.medan.teksBersama') . ': ' . lang($kode === 'kosong' ? 'Bank.galat.kelompokKosong' : 'Bank.galat.' . $kode, $arg);
        }
        $alt = Aturan::rapikan($isian['alt']);
        if ($rencana['apa'] === 'galat') {
            $galat[] = lang('Bank.medan.gambar') . ': ' . lang('Bank.galat.gambar_' . $rencana['galat']);
        } elseif ($rencana['apa'] !== 'tanpa' && ($alt === '' || mb_strlen($alt) > Aturan::ALT_MAKS)) {
            $galat[] = lang('Bank.medan.gambar') . ': ' . lang('Bank.galat.altWajib', [Aturan::ALT_MAKS]);
        }
        if ($galat !== [] || $baku === null) {
            return $this->form($matkul, $lama, $isian, $galat, 422);
        }
        $baku['image'] = $this->wujudkanGambar($matkulId, $rencana, $alt);
        if (is_string($baku['image'])) {
            return $this->form($matkul, $lama, $isian, [lang('Bank.medan.gambar') . ': ' . lang('Bank.galat.gambar_' . $baku['image'])], 422);
        }
        $id = (new Pustaka($this->akses()))->simpanKelompok($matkulId, $lama['id'] ?? null, $baku);
        if (is_string($id)) {
            return $this->tidakDitemukan('group');
        }

        return redirect()->to($this->alamat($matkulId) . '/kelompok/' . $id . '?pesan=disimpan');
    }

    public function arsip(string $matkulId, string $id): ResponseInterface
    {
        $matkul = $this->matkulDikelola((int) $matkulId);
        if ($matkul instanceof ResponseInterface) {
            return $matkul;
        }
        $arsip = (string) $this->request->getPost('arsip') === '1';
        if (! (new Pustaka($this->akses()))->arsipkan('kelompok', (int) $matkul['id'], (int) $id, $arsip)) {
            return $this->tidakDitemukan('group');
        }

        return redirect()->to($this->alamat((int) $matkul['id']) . '/kelompok/' . (int) $id . '?pesan=' . ($arsip ? 'diarsip' : 'dipulihkan'));
    }

    public function hapus(string $matkulId, string $id): ResponseInterface
    {
        $matkul = $this->matkulDikelola((int) $matkulId);
        if ($matkul instanceof ResponseInterface) {
            return $matkul;
        }
        $hasil = (new Pustaka($this->akses()))->hapusKelompok((int) $matkul['id'], (int) $id);
        if ($hasil === 'tidak_ada') {
            return $this->tidakDitemukan('group');
        }
        $dasar = $this->alamat((int) $matkul['id']);

        return redirect()->to($hasil === 'ok' ? $dasar . '?pesan=dihapus' : $dasar . '/kelompok/' . (int) $id . '?pesan=dipakai');
    }
}
