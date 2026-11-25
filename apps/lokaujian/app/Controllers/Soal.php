<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Libraries\Aturan;
use App\Libraries\Bank as Pustaka;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Soal bank satu mata kuliah (SPEC-LOKAUJIAN.md §5.1, §5.4): keempat tipe, dengan poin dan
 * gambar. Hanya superadmin dan pengampu mata kuliah itu; soal mata kuliah lain dijawab 404.
 *
 * Tipe soal ditetapkan saat dibuat dan tidak diubah lagi. Soal yang dibuat dengan
 * ?kelompok=<id> menjadi anggota kelompok itu.
 */
final class Soal extends BaseController
{
    private const PESAN = [
        'disimpan' => ['sukses', 'Bank.pesan.soalDisimpan'],
    ];

    private function alamat(int $matkulId): string
    {
        return '/mata-kuliah/' . $matkulId . '/bank';
    }

    /**
     * Isian form dari soal yang tersimpan (atau kosong untuk soal baru).
     *
     * @param array<string, mixed>|null $soal
     *
     * @return array<string, mixed>
     */
    private static function isianDari(?array $soal, string $tipe): array
    {
        $opsi = [];
        for ($i = 0; $i < Aturan::OPSI_MAKS; $i++) {
            $o      = $soal['options'][$i] ?? null;
            $opsi[] = ['text' => $o['text'] ?? '', 'alt' => $o['image']['alt'] ?? '', 'gambar' => $o['image'] ?? null];
        }
        $benar = null;
        foreach ($soal['options'] ?? [] as $i => $o) {
            if ($o['correct']) {
                $benar = $i;
            }
        }

        return [
            'type' => $tipe, 'text' => $soal['text'] ?? '', 'points' => $soal === null ? '1' : self::poinTampil((string) $soal['points']),
            'answer' => $soal === null || $soal['answer'] === null ? '' : ($soal['answer'] ? 'benar' : 'salah'),
            'benar' => $benar, 'alt' => $soal['image']['alt'] ?? '', 'gambar' => $soal['image'] ?? null, 'opsi' => $opsi,
        ];
    }

    public static function poinTampil(string $poin): string
    {
        return rtrim(rtrim($poin, '0'), '.');
    }

    /**
     * @param array<string, mixed>      $matkul
     * @param array<string, mixed>|null $soal     soal yang disunting; null untuk soal baru
     * @param array<string, mixed>|null $kelompok kelompok tempat soal baru ditambahkan
     * @param array<string, mixed>      $isian
     * @param list<string>              $galat
     */
    private function form(array $matkul, ?array $soal, ?array $kelompok, array $isian, array $galat = [], int $status = 200): ResponseInterface
    {
        return $this->halaman('soal_form', [
            'judul'    => lang($soal === null ? 'Bank.soal.baru' : 'Bank.soal.sunting') . ' — ' . $matkul['code'],
            'matkul'   => $matkul,
            'soal'     => $soal,
            'kelompok' => $kelompok,
            'isian'    => $isian,
            'galat'    => $galat,
        ], $status);
    }

    public function baru(string $matkulId): ResponseInterface
    {
        $matkul = $this->matkulDikelola((int) $matkulId);
        if ($matkul instanceof ResponseInterface) {
            return $matkul;
        }
        $tipe = (string) $this->request->getGet('tipe');
        if (! in_array($tipe, Aturan::TIPE, true)) {
            return $this->tidakDitemukan('question_type');
        }
        $kelompok = null;
        if ($this->request->getGet('kelompok') !== null) {
            $kelompok = (new Pustaka($this->akses()))->kelompok((int) $matkul['id'], (int) $this->request->getGet('kelompok'));
            if ($kelompok === null) {
                return $this->tidakDitemukan('group');
            }
        }

        return $this->form($matkul, null, $kelompok, self::isianDari(null, $tipe));
    }

    public function buat(string $matkulId): ResponseInterface
    {
        $matkul = $this->matkulDikelola((int) $matkulId);
        if ($matkul instanceof ResponseInterface) {
            return $matkul;
        }
        $tipe = (string) $this->request->getPost('type');
        if (! in_array($tipe, Aturan::TIPE, true)) {
            return $this->tidakDitemukan('question_type');
        }
        $bank     = new Pustaka($this->akses());
        $kelompok = null;
        if ((string) $this->request->getPost('kelompok') !== '') {
            $kelompok = $bank->kelompok((int) $matkul['id'], (int) $this->request->getPost('kelompok'));
            if ($kelompok === null) {
                return $this->tidakDitemukan('group');
            }
        }

        return $this->terima($matkul, null, $kelompok, $tipe);
    }

    public function lihat(string $matkulId, string $id): ResponseInterface
    {
        $matkul = $this->matkulDikelola((int) $matkulId);
        if ($matkul instanceof ResponseInterface) {
            return $matkul;
        }
        $bank = new Pustaka($this->akses());
        $soal = $bank->soal((int) $matkul['id'], (int) $id);
        if ($soal === null) {
            return $this->tidakDitemukan('question');
        }
        // Soal anggota kelompok dipratinjau bersama kelompoknya: bagian bersama selalu tampil dengannya.
        if ($soal['group_id'] !== null) {
            return redirect()->to($this->alamat((int) $matkul['id']) . '/kelompok/' . $soal['group_id']);
        }

        return $this->halaman('soal_lihat', [
            'judul'    => lang('Bank.pratinjau') . ' — ' . $matkul['code'],
            'matkul'   => $matkul,
            'soal'     => $soal,
            'dipakai'  => $bank->pemakaian((int) $matkul['id'])['s' . $soal['id']] ?? [],
            'pesan'    => $this->pesanDariQuery(self::PESAN + [
                'dipakai'   => ['galat', 'Bank.pesan.dipakai'],
                'diarsip'   => ['sukses', 'Bank.pesan.diarsipkan'],
                'dipulihkan' => ['sukses', 'Bank.pesan.dipulihkan'],
            ]),
        ]);
    }

    public function sunting(string $matkulId, string $id): ResponseInterface
    {
        $matkul = $this->matkulDikelola((int) $matkulId);
        if ($matkul instanceof ResponseInterface) {
            return $matkul;
        }
        $bank = new Pustaka($this->akses());
        $soal = $bank->soal((int) $matkul['id'], (int) $id);
        if ($soal === null) {
            return $this->tidakDitemukan('question');
        }
        $kelompok = $soal['group_id'] === null ? null : $bank->kelompok((int) $matkul['id'], $soal['group_id']);

        return $this->form($matkul, $soal, $kelompok, self::isianDari($soal, $soal['type']));
    }

    public function simpan(string $matkulId, string $id): ResponseInterface
    {
        $matkul = $this->matkulDikelola((int) $matkulId);
        if ($matkul instanceof ResponseInterface) {
            return $matkul;
        }
        $bank = new Pustaka($this->akses());
        $soal = $bank->soal((int) $matkul['id'], (int) $id);
        if ($soal === null) {
            return $this->tidakDitemukan('question');
        }
        $kelompok = $soal['group_id'] === null ? null : $bank->kelompok((int) $matkul['id'], $soal['group_id']);

        return $this->terima($matkul, $soal, $kelompok, $soal['type']);
    }

    /**
     * Membaca form, memeriksa dengan aturan yang sama dengan impor (App\Libraries\Aturan), lalu
     * menyimpan. Bila ada yang salah, form tampil lagi dengan semua yang sudah diketik; berkas
     * gambar harus dipilih ulang, karena peramban tidak mengirimkannya dua kali.
     *
     * @param array<string, mixed>      $matkul
     * @param array<string, mixed>|null $lama
     * @param array<string, mixed>|null $kelompok
     */
    private function terima(array $matkul, ?array $lama, ?array $kelompok, string $tipe): ResponseInterface
    {
        $matkulId = (int) $matkul['id'];
        $isian    = self::isianDari($lama, $tipe);
        $kirim    = $this->request->getPost();
        $isian['text']   = (string) ($kirim['text'] ?? '');
        $isian['points'] = trim((string) ($kirim['points'] ?? ''));
        $isian['answer'] = (string) ($kirim['answer'] ?? '');
        $isian['alt']    = (string) ($kirim['alt'] ?? '');
        $isian['benar']  = isset($kirim['benar']) && $kirim['benar'] !== '' ? (int) $kirim['benar'] : null;

        // Angka poin boleh diketik dengan koma, seperti kebiasaan di Indonesia.
        $angka  = str_replace(',', '.', $isian['points']);
        $mentah = [
            'type' => $tipe, 'text' => $isian['text'],
            'points' => is_numeric($angka) ? (float) $angka : null,
        ];
        $rencana         = ['soal' => $this->rencanaGambar('gambar', $isian['gambar'], ($kirim['hapus_gambar'] ?? '') === '1'), 'opsi' => []];
        $mentah['image'] = in_array($rencana['soal']['apa'], ['baru', 'lama'], true) ? true : null;
        if ($tipe === 'benar_salah') {
            $mentah['answer'] = ['benar' => true, 'salah' => false][$isian['answer']] ?? null;
        }
        $barisOpsi = []; // indeks opsi di $mentah => nomor baris form
        if ($tipe === 'pilihan_ganda') {
            $mentah['options'] = [];
            for ($i = 0; $i < Aturan::OPSI_MAKS; $i++) {
                $isian['opsi'][$i]['text'] = (string) ($kirim['opsi_teks'][$i] ?? '');
                $isian['opsi'][$i]['alt']  = (string) ($kirim['opsi_alt'][$i] ?? '');
                $r = $this->rencanaGambar('opsi_gambar_' . $i, $isian['opsi'][$i]['gambar'], ($kirim['opsi_hapus_gambar'][$i] ?? '') === '1');
                $adaGambar = in_array($r['apa'], ['baru', 'lama', 'galat'], true);
                // Baris yang kosong sama sekali bukan opsi.
                if (trim($isian['opsi'][$i]['text']) === '' && ! $adaGambar) {
                    continue;
                }
                $barisOpsi[]         = $i;
                $rencana['opsi'][]   = $r;
                $mentah['options'][] = ['text' => $isian['opsi'][$i]['text'], 'correct' => $isian['benar'] === $i, 'image' => $adaGambar ? true : null];
            }
        }

        $aturan = new Aturan();
        $baku   = $aturan->soal($mentah, '');
        $galat  = [];
        foreach ($aturan->galat() as [$jalur, $kode, $arg]) {
            $galat[] = $this->labelJalur($jalur, $barisOpsi) . ': ' . lang('Bank.galat.' . $kode, $arg);
        }
        // Teks alternatif wajib untuk setiap gambar (SPEC-LOKAUJIAN.md §5.3).
        $alt = ['soal' => Aturan::rapikan($isian['alt']), 'opsi' => []];
        $this->periksaAlt($rencana['soal'], $alt['soal'], lang('Bank.medan.gambar'), $galat);
        foreach ($rencana['opsi'] as $n => $r) {
            $alt['opsi'][$n] = Aturan::rapikan($isian['opsi'][$barisOpsi[$n]]['alt']);
            $this->periksaAlt($r, $alt['opsi'][$n], lang('Bank.medan.opsiKe', [$barisOpsi[$n] + 1]), $galat);
        }
        if ($galat !== [] || $baku === null) {
            return $this->form($matkul, $lama, $kelompok, $isian, $galat, 422);
        }

        // Semua isian lolos: baru sekarang berkas gambar diolah dan disimpan.
        $baku['image'] = $this->wujudkanGambar($matkulId, $rencana['soal'], $alt['soal']);
        if (is_string($baku['image'])) {
            $galat[] = lang('Bank.medan.gambar') . ': ' . lang('Bank.galat.gambar_' . $baku['image']);
        }
        foreach ($baku['options'] as $n => &$o) {
            $o['image'] = $this->wujudkanGambar($matkulId, $rencana['opsi'][$n], $alt['opsi'][$n]);
            if (is_string($o['image'])) {
                $galat[] = lang('Bank.medan.opsiKe', [$barisOpsi[$n] + 1]) . ': ' . lang('Bank.galat.gambar_' . $o['image']);
            }
        }
        unset($o);
        if ($galat !== []) {
            return $this->form($matkul, $lama, $kelompok, $isian, $galat, 422);
        }

        $id = (new Pustaka($this->akses()))->simpanSoal($matkulId, $lama['id'] ?? null, $baku, $lama === null ? ($kelompok['id'] ?? null) : null);
        if ($id === 'kelompok_penuh') {
            return $this->form($matkul, $lama, $kelompok, $isian, [lang('Bank.galat.kelompokPenuh', [Aturan::SOAL_KELOMPOK_MAKS])], 422);
        }
        if (is_string($id)) {
            return $this->tidakDitemukan('question');
        }
        $tujuan = $kelompok !== null ? '/kelompok/' . $kelompok['id'] : '/soal/' . $id;

        return redirect()->to($this->alamat($matkulId) . $tujuan . '?pesan=disimpan');
    }

    /**
     * @param array<string, mixed> $rencana
     * @param list<string>         $galat
     */
    private function periksaAlt(array $rencana, string $alt, string $label, array &$galat): void
    {
        if ($rencana['apa'] === 'galat') {
            $galat[] = $label . ': ' . lang('Bank.galat.gambar_' . $rencana['galat']);
        } elseif ($rencana['apa'] !== 'tanpa' && ($alt === '' || mb_strlen($alt) > Aturan::ALT_MAKS)) {
            $galat[] = $label . ': ' . lang('Bank.galat.altWajib', [Aturan::ALT_MAKS]);
        }
    }

    /**
     * Nama isian form untuk sebuah jalur galat dari Aturan (".options[1].text" → "Opsi 3").
     *
     * @param list<int> $barisOpsi
     */
    private function labelJalur(string $jalur, array $barisOpsi): string
    {
        if (preg_match('/^\.options\[(\d+)\]/', $jalur, $m) === 1) {
            return lang('Bank.medan.opsiKe', [($barisOpsi[(int) $m[1]] ?? (int) $m[1]) + 1]);
        }

        return lang(['.text' => 'Bank.medan.teks', '.points' => 'Bank.medan.poin', '.answer' => 'Bank.medan.kunci', '.options' => 'Bank.medan.opsi'][$jalur] ?? 'Bank.medan.soal');
    }

    public function arsip(string $matkulId, string $id): ResponseInterface
    {
        $matkul = $this->matkulDikelola((int) $matkulId);
        if ($matkul instanceof ResponseInterface) {
            return $matkul;
        }
        $arsip = (string) $this->request->getPost('arsip') === '1';
        if (! (new Pustaka($this->akses()))->arsipkan('soal', (int) $matkul['id'], (int) $id, $arsip)) {
            return $this->tidakDitemukan('question');
        }

        return redirect()->to($this->alamat((int) $matkul['id']) . '/soal/' . (int) $id . '?pesan=' . ($arsip ? 'diarsip' : 'dipulihkan'));
    }

    public function hapus(string $matkulId, string $id): ResponseInterface
    {
        $matkul = $this->matkulDikelola((int) $matkulId);
        if ($matkul instanceof ResponseInterface) {
            return $matkul;
        }
        $bank  = new Pustaka($this->akses());
        $soal  = $bank->soal((int) $matkul['id'], (int) $id);
        $hasil = $bank->hapusSoal((int) $matkul['id'], (int) $id);
        if ($hasil === 'tidak_ada' || $soal === null) {
            return $this->tidakDitemukan('question');
        }
        $dasar = $this->alamat((int) $matkul['id']);
        if ($soal['group_id'] !== null) {
            return redirect()->to($dasar . '/kelompok/' . $soal['group_id'] . '?pesan=' . ($hasil === 'ok' ? 'soalDihapus' : 'dipakai'));
        }

        return redirect()->to($hasil === 'ok' ? $dasar . '?pesan=dihapus' : $dasar . '/soal/' . (int) $id . '?pesan=dipakai');
    }

    public function geser(string $matkulId, string $id): ResponseInterface
    {
        $matkul = $this->matkulDikelola((int) $matkulId);
        if ($matkul instanceof ResponseInterface) {
            return $matkul;
        }
        $bank = new Pustaka($this->akses());
        $soal = $bank->soal((int) $matkul['id'], (int) $id);
        $arah = (string) $this->request->getPost('arah') === 'naik' ? 'naik' : 'turun';
        if ($soal === null || ! $bank->geser((int) $matkul['id'], (int) $id, $arah)) {
            return $this->tidakDitemukan('question');
        }

        return redirect()->to($this->alamat((int) $matkul['id']) . '/kelompok/' . $soal['group_id']);
    }
}
