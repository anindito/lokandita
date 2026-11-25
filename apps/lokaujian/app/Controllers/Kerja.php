<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Libraries\Jadwal;
use App\Libraries\Pengerjaan;
use CodeIgniter\HTTP\ResponseInterface;
use Lokandita\Auth\Log;

/**
 * Halaman MAHASISWA (SPEC-LOKAUJIAN.md §8): sebelum mulai, menunggu, halaman ujian, tanda terima;
 * dan tiga alamat JSON yang dipakai halaman ujian (keadaan, simpan jawaban, peristiwa).
 *
 * Semua rute di sini di belakang `masuk:mahasiswa` dan `seb`. Alamatnya hanya memuat id SESI;
 * pengerjaan selalu dicari dari pengguna yang masuk, jadi pengerjaan orang lain tidak bisa
 * dialamatkan. Sesi kelas lain dijawab 404, sama dengan nomor yang tidak ada.
 */
final class Kerja extends BaseController
{
    private function pustaka(): Pengerjaan
    {
        return new Pengerjaan($this->akses());
    }

    private function json(int $status, array $isi): ResponseInterface
    {
        return $this->response->setStatusCode($status)->setHeader('Cache-Control', 'no-store')->setJSON($isi);
    }

    /**
     * Badan permintaan sebagai obyek JSON; null bila bukan JSON yang sah (mis. form biasa).
     *
     * @return array<string, mixed>|null
     */
    private function badanJson(): ?array
    {
        $b = json_decode((string) $this->request->getBody(), true, 8);

        return is_array($b) && ! array_is_list($b) ? $b : null;
    }

    private function jsonTidakAda(): ResponseInterface
    {
        Log::event('access.denied', ['user' => $this->akses()->identitas()->userId, 'reason' => 'outside_scope_or_missing', 'path' => $this->request->getUri()->getPath()]);

        return $this->json(404, ['error' => 'tidak_ada']);
    }

    /**
     * Satu alamat untuk tiga keadaan: belum mulai (aturan dan tombol Mulai, atau halaman
     * menunggu), sedang berjalan (tautan Lanjutkan), sudah berakhir (tanda terima).
     */
    public function lihat(string $sesiId): ResponseInterface
    {
        $s = $this->pustaka()->sesi((int) $sesiId);
        if ($s === null) {
            return $this->tidakDitemukan('session');
        }

        return $this->halaman('kerja_mulai', [
            'judul' => (string) $s['title'],
            'sesi'  => $s,
            'pesan' => $this->pesanDariQuery(['belum' => ['galat', 'Kerja.galat.belum']]),
        ]);
    }

    public function mulai(string $sesiId): ResponseInterface
    {
        $hasil = $this->pustaka()->mulai((int) $sesiId);
        if ($hasil === 'tidak_ada') {
            return $this->tidakDitemukan('session');
        }

        return redirect()->to('/ujian-saya/' . (int) $sesiId . ($hasil === 'ok' ? '/kerjakan' : '?pesan=belum'));
    }

    /**
     * Halaman ujian: dimuat sekali dengan seluruh ujian. Pengerjaan yang sudah berakhir diarahkan
     * ke tanda terimanya.
     */
    public function kerjakan(string $sesiId): ResponseInterface
    {
        $pustaka = $this->pustaka();
        if ($pustaka->sesi((int) $sesiId) === null) {
            return $this->tidakDitemukan('session');
        }
        $data = $pustaka->bukaHalaman((int) $sesiId);
        if ($data === null) {
            return redirect()->to('/ujian-saya/' . (int) $sesiId);
        }

        return $this->halaman('kerja_ujian', [
            'judul'       => (string) $data['title'],
            'kelasBadan'  => 'badan-ujian',
            'sesi'        => $data,
            'bolehTempel' => $this->akses()->identitas()->pasteAllowed,
        ]);
    }

    public function kumpulkan(string $sesiId): ResponseInterface
    {
        if ($this->pustaka()->kumpulkan((int) $sesiId) === 'tidak_ada') {
            return $this->tidakDitemukan('session');
        }

        return redirect()->to('/ujian-saya/' . (int) $sesiId);
    }

    public function keadaan(string $sesiId): ResponseInterface
    {
        $k = $this->pustaka()->keadaan((int) $sesiId, (string) $this->request->getGet('halaman'));

        return $k === null ? $this->jsonTidakAda() : $this->json(200, $k);
    }

    /**
     * Simpan satu jawaban. Badan JSON: { halaman, soal, nilai, ragu, n }.
     * 200 = tersimpan (sudah COMMIT). 409 = tidak diterima dan tidak akan pernah diterima
     * (waktu habis, sudah dikumpulkan, halaman diambil alih): halaman berhenti mencoba.
     */
    public function jawaban(string $sesiId): ResponseInterface
    {
        // Hak akses lebih dulu daripada bentuk kiriman: sesi yang bukan miliknya dijawab 404 apa pun isinya.
        $pustaka = $this->pustaka();
        if (! $pustaka->punya((int) $sesiId)) {
            return $this->jsonTidakAda();
        }
        $b = $this->badanJson();
        if ($b === null || ! is_string($b['halaman'] ?? null) || ! is_int($b['soal'] ?? null) || ! is_int($b['n'] ?? null) || ! array_key_exists('nilai', $b)) {
            return $this->json(422, ['error' => 'bentuk']);
        }
        if (! $this->lajuWajar((int) $sesiId)) {
            return $this->json(429, ['error' => 'terlalu_cepat'])->setHeader('Retry-After', '5');
        }
        $hasil = $pustaka->simpan((int) $sesiId, $b['halaman'], $b['soal'], $b['nilai'], ($b['ragu'] ?? false) === true, $b['n']);

        switch ($hasil['kode']) {
            case 'ok':
            case 'usang':
                return $this->json(200, ['ok' => true, 'n' => $hasil['n'], 'disimpan' => $hasil['disimpan'], 'sisa' => $hasil['sisa']]);

            case 'tidak_ada':
                return $this->jsonTidakAda();

            case 'diambil_alih':
                $pustaka->peristiwa((int) $sesiId, 'page_taken_over', ['halaman' => substr($b['halaman'], 0, 32)], $this->request->getIPAddress(), (string) $this->request->getUserAgent());

                return $this->json(409, ['error' => 'diambil_alih']);

            case 'dikumpulkan':
            case 'waktu_habis':
                return $this->json(409, ['error' => $hasil['kode']]);

            default:
                return $this->json(422, ['error' => $hasil['kode']]);
        }
    }

    /**
     * Batas laju simpan per pengguna per sesi, supaya skrip yang rusak tidak membanjiri basis
     * data. Jauh di atas pengetik mana pun. Redis hanya penghitung: bila ia kosong atau tidak
     * terjangkau, simpan tetap diterima (SPEC-LOKAUJIAN.md §14).
     */
    private function lajuWajar(int $sesiId): bool
    {
        try {
            $r     = Jadwal::redis();
            $kunci = 'lokaujian:laju:simpan:' . $sesiId . ':' . $this->akses()->identitas()->userId . ':' . intdiv(time(), 60);
            $n     = $r->incr($kunci);
            if ($n === 1) {
                $r->expire($kunci, 120);
            }

            return $n <= Pengerjaan::LAJU_PER_MENIT;
        } catch (\Throwable) {
            return true;
        }
    }

    /**
     * Peristiwa integritas dari halaman ujian. Tahap U3 hanya menerima `paste_blocked`
     * (tempel, salin atau potong yang diblokir, dengan jalurnya). Pengguna yang dibebaskan
     * (paste_allowed) tidak pernah mengirimnya, dan kalaupun ada tidak dicatat.
     */
    public function peristiwa(string $sesiId): ResponseInterface
    {
        if (! $this->pustaka()->punya((int) $sesiId)) {
            return $this->jsonTidakAda();
        }
        $b     = $this->badanJson();
        $jalur = $b['jalur'] ?? null;
        if ($b === null || ($b['jenis'] ?? '') !== 'paste_blocked' || ! in_array($jalur, ['paste', 'copy', 'cut', 'drop', 'middle_click', 'context_menu', 'keyboard', 'shift_insert'], true)) {
            return $this->json(422, ['error' => 'bentuk']);
        }
        if ($this->akses()->identitas()->pasteAllowed) {
            return $this->json(200, ['ok' => true]);
        }
        $ada = $this->pustaka()->peristiwa((int) $sesiId, 'paste_blocked', ['jalur' => $jalur], $this->request->getIPAddress(), (string) $this->request->getUserAgent());

        return $ada ? $this->json(200, ['ok' => true]) : $this->jsonTidakAda();
    }
}
