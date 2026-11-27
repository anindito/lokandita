<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Libraries\Hadir;
use App\Libraries\Jadwal;
use App\Libraries\Pengerjaan;
use App\Libraries\SebUjian;
use CodeIgniter\HTTP\ResponseInterface;
use Lokandita\Auth\Log;
use Lokandita\Auth\RosterDb;

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

    /**
     * Tinjauan sesudah hasil kelasnya dirilis dengan kunci (SPEC-LOKAUJIAN.md §11), di dalam SEB
     * dengan berkas ujian itu: tiap soal, jawaban mahasiswa itu sendiri, dan kunci soal yang
     * dinilai otomatis. Selain itu kembali ke tanda terimanya.
     */
    public function tinjau(string $sesiId): ResponseInterface
    {
        Jadwal::lintasan();
        $pustaka = $this->pustaka();
        if ($pustaka->sesi((int) $sesiId) === null) {
            return $this->tidakDitemukan('session');
        }
        $data = $pustaka->tinjau((int) $sesiId);
        if ($data === null) {
            return redirect()->to('/ujian-saya/' . (int) $sesiId);
        }

        return $this->halaman('kerja_tinjau', ['judul' => lang('Hasil.tinjau.judul') . ' — ' . $data['title'], 'kelasBadan' => 'badan-ujian', 'sesi' => $data]);
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
        if ($k === null) {
            return $this->jsonTidakAda();
        }
        // Tanda "terakhir terlihat" untuk tampilan langsung dosen (SPEC-LOKAUJIAN.md §10). Hanya
        // tampilan: tidak ada keputusan yang membacanya.
        if ($k['pengerjaan'] === null || $k['pengerjaan']['berjalan']) {
            Hadir::catat((int) $sesiId, $this->akses()->identitas()->userId, $k['pengerjaan'] === null ? 'tunggu' : 'kerja');
        }

        return $this->json(200, $k);
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
                Hadir::catat((int) $sesiId, $this->akses()->identitas()->userId, 'kerja');

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
     * Peristiwa integritas dari halaman ujian (SPEC-LOKAUJIAN.md §9.3). Badan JSON:
     *   { jenis: "paste_blocked",   jalur }                    tempel, salin atau potong yang diblokir
     *   { jenis: "focus_lost",      durasi }                   halaman kehilangan fokus atau disembunyikan, detik
     *   { jenis: "display_changed", lebar, tinggi, layar }     ukuran layar atau jumlah layar berubah
     *   { jenis: "reconnected",     detik }                    server tak terjangkau lebih dari 60 detik lalu kembali
     * Yang dicatat hanya nilai yang sudah diperiksa di sini; teks bebas tidak pernah disimpan.
     * Pengguna yang dibebaskan (paste_allowed) tidak pernah dicatat untuk paste_blocked.
     */
    public function peristiwa(string $sesiId): ResponseInterface
    {
        $pustaka = $this->pustaka();
        if (! $pustaka->punya((int) $sesiId)) {
            return $this->jsonTidakAda();
        }
        $b      = $this->badanJson() ?? [];
        $angka  = static fn (string $k, int $min, int $maks): ?int => is_int($b[$k] ?? null) && $b[$k] >= $min && $b[$k] <= $maks ? $b[$k] : null;
        $detail = null;
        switch ($b['jenis'] ?? '') {
            case 'paste_blocked':
                $jalur = $b['jalur'] ?? null;
                if (in_array($jalur, ['paste', 'copy', 'cut', 'drop', 'middle_click', 'context_menu', 'keyboard', 'shift_insert'], true)) {
                    $detail = ['jalur' => $jalur];
                }
                break;

            case 'focus_lost':
                if (($durasi = $angka('durasi', 0, 86_400)) !== null) {
                    $detail = ['durasi' => $durasi];
                }
                break;

            case 'display_changed':
                $lebar  = $angka('lebar', 1, 100_000);
                $tinggi = $angka('tinggi', 1, 100_000);
                if ($lebar !== null && $tinggi !== null) {
                    $detail = ['lebar' => $lebar, 'tinggi' => $tinggi, 'layar' => is_bool($b['layar'] ?? null) ? $b['layar'] : null];
                }
                break;

            case 'reconnected':
                if (($detik = $angka('detik', 60, 86_400)) !== null) {
                    $detail = ['detik' => $detik];
                }
                break;
        }
        if ($detail === null) {
            return $this->json(422, ['error' => 'bentuk']);
        }
        if ($b['jenis'] === 'paste_blocked' && $this->akses()->identitas()->pasteAllowed) {
            return $this->json(200, ['ok' => true]);
        }
        if (! $this->lajuPeristiwa((int) $sesiId)) {
            return $this->json(429, ['error' => 'terlalu_cepat']);
        }
        $pustaka->peristiwa((int) $sesiId, (string) $b['jenis'], $detail, $this->request->getIPAddress(), (string) $this->request->getUserAgent());

        return $this->json(200, ['ok' => true]);
    }

    /**
     * Paling banyak 60 peristiwa per menit per pengguna per sesi: halaman yang rusak atau skrip
     * iseng tidak bisa membanjiri tabel. Tanpa Redis semuanya diterima.
     */
    private function lajuPeristiwa(int $sesiId): bool
    {
        try {
            $r     = Jadwal::redis();
            $kunci = 'lokaujian:laju:peristiwa:' . $sesiId . ':' . $this->akses()->identitas()->userId . ':' . intdiv(time(), 60);
            $n     = $r->incr($kunci);
            if ($n === 1) {
                $r->expire($kunci, 120);
            }

            return $n <= 60;
        } catch (\Throwable) {
            return true;
        }
    }

    /**
     * Berkas .seb ujian sesi ini, untuk mahasiswanya sendiri (KONTRAK.md §23). Rute ini DI LUAR
     * filter `seb`: berkasnya justru diunduh di peramban biasa, dari beranda. Sesi kelas lain
     * dijawab 404; tidak ada daftar publik ujian.
     */
    public function unduhSeb(string $sesiId): ResponseInterface
    {
        $s = $this->pustaka()->sesi((int) $sesiId);
        if ($s === null) {
            return $this->tidakDitemukan('session');
        }
        $kode   = RosterDb::conn()->execute_query('SELECT code FROM roster_courses WHERE id = ?', [(int) $s['course_id']])->fetch_assoc();
        $berkas = SebUjian::berkas((int) $s['exam_id'], (string) ($kode['code'] ?? ''));

        return $berkas === null ? $this->tidakDitemukan('seb_file') : SebPanel::kirim($this->response, $berkas);
    }

    /**
     * Alamat yang dibuka SEB ketika berkas sebuah ujian dijalankan (startURL). Mahasiswa diarahkan
     * ke sesinya sendiri atas ujian itu; siapa pun yang tidak punya sesi di sana ke beranda.
     * Tidak menampilkan apa pun dan tidak membedakan ujian yang ada dari yang tidak ada.
     */
    public function masuk(string $ujianId): ResponseInterface
    {
        foreach ($this->pustaka()->daftar() as $s) {
            if ((int) $s['exam_id'] === (int) $ujianId) {
                return redirect()->to('/ujian-saya/' . (int) $s['sesi_id']);
            }
        }

        return redirect()->to('/beranda');
    }
}
