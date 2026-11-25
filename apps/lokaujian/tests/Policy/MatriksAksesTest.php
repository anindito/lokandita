<?php

declare(strict_types=1);

namespace Tests\Policy;

use Tests\Support\UjianCase;

/**
 * Matriks hak akses (SPEC-LOKAUJIAN.md §3) untuk SETIAP rute LokaUjian, lewat HTTP sungguhan.
 *
 * Dua jaminan:
 *   1. rute yang terdaftar di Config/Routes.php tetapi tidak ada di matriks ini menggagalkan
 *      tes — rute baru tidak bisa lolos tanpa uji hak akses;
 *   2. setiap rute dicoba oleh enam pelaku; yang tidak berhak harus mendapat 403 (peran tidak
 *      pernah boleh) atau 404 (di luar lingkupnya).
 *
 * Sasaran rute berparameter selalu mata kuliah X dan kelas XA dari UjianCase::dunia().
 * Tahap U1 belum punya rute yang mengubah data; saat ada, tes "penolakan tidak mengubah data"
 * ditambahkan di sini seperti di LokaKode.
 */
final class MatriksAksesTest extends UjianCase
{
    private const PELAKU = ['superadmin', 'pengampuX', 'pengampuY', 'pengajarA', 'pengajarB', 'mhsA'];

    // Rute paket bersama: diuji oleh ClientAppTest di paket (KONTRAK.md §4).
    private const PAKET = ['GET healthz', 'POST keluar', 'POST sesi/perpanjang', 'POST preferensi'];

    // Rute tanpa sesi. Hanya boleh berisi rute yang tidak membaca data apa pun:
    // halaman depan dan pilihan bahasa (tests/KerangkaTest.php dan ClientAppTest di paket).
    private const PUBLIK = ['GET /', 'GET bahasa/([^/]+)'];

    /**
     * Pola rute => [jalur untuk dunia uji, jawaban yang diharapkan per pelaku].
     * 'ok' = diizinkan (bukan 403/404); angka = status penolakan yang diharapkan.
     *
     * @param array<string, mixed> $d
     *
     * @return array<string, array{0: string, 1: array<string, int|string>}>
     */
    private function matriks(array $d): array
    {
        $semua = array_fill_keys(self::PELAKU, 'ok');
        $staf  = ['mhsA' => 403] + $semua;

        return [
            'GET beranda'     => ['/beranda', $semua],
            'GET mata-kuliah' => ['/mata-kuliah', $staf],
            // Pengajar A dan B mengajar kelas di mata kuliah X, jadi boleh melihat mata kuliahnya.
            'GET mata-kuliah/{n}' => ['/mata-kuliah/' . $d['matkulX'], ['pengampuY' => 404] + $staf],
            // Gerbang tahap U1: pengajar B tidak bisa membuka kelas XA milik pengajar A.
            'GET kelas/{n}' => ['/kelas/' . $d['kelasXA'], ['pengampuY' => 404, 'pengajarB' => 404] + $staf],
        ];
    }

    /**
     * @return list<string> "METODE pola", parameter angka ditulis {n}
     */
    private function ruteTerdaftar(): array
    {
        $rute   = service('routes')->loadRoutes();
        $daftar = [];
        foreach (['GET', 'POST', 'PUT', 'PATCH', 'DELETE'] as $metode) {
            foreach (array_keys($rute->getRoutes($metode, false)) as $pola) {
                $daftar[] = $metode . ' ' . str_replace('([0-9]+)', '{n}', (string) $pola);
            }
        }
        sort($daftar);

        return $daftar;
    }

    public function testSetiapRuteTerdaftarAdaDiMatriks(): void
    {
        $diuji = array_merge(self::PAKET, self::PUBLIK, array_keys($this->matriks($this->dunia())));
        sort($diuji);
        $this->assertSame($diuji, $this->ruteTerdaftar(), 'Rute di Config/Routes.php dan matriks hak akses tidak sama.');
    }

    public function testSetiapRuteMenolakYangTidakBerhak(): void
    {
        $d = $this->dunia();
        foreach ($this->matriks($d) as $pola => [$jalur, $harapan]) {
            $this->assertStringStartsWith('GET ', $pola, 'tahap U1 hanya punya rute baca; rute tulis butuh token CSRF dan tes tanpa-perubahan');
            foreach (self::PELAKU as $p) {
                $r = $this->sebagai($d[$p])->get($jalur);
                if ($harapan[$p] === 'ok') {
                    $this->assertSame(200, $r->status, "{$pola} sebagai {$p}: seharusnya diizinkan");
                } else {
                    $this->assertSame($harapan[$p], $r->status, "{$pola} sebagai {$p}: seharusnya ditolak");
                    // Penolakan tidak memuat apa pun tentang sasarannya. Nama dan nomor induk mahasiswa A
                    // tidak diperiksa pada jawaban untuk dirinya sendiri: keduanya ada di menu akunnya.
                    $rahasiaSasaran = ['UJX101', 'Uji Ujian X', 'Pengajar Aminah', 'Pengampu Xaverius'];
                    if ($p !== 'mhsA') {
                        $rahasiaSasaran = array_merge($rahasiaSasaran, ['Mahasiswa Ani', (string) $d['mhsA']['nomor_induk']]);
                    }
                    foreach ($rahasiaSasaran as $rahasia) {
                        $this->assertStringNotContainsString($rahasia, $r->body, "{$pola} sebagai {$p}: penolakan memuat «{$rahasia}»");
                    }
                }
            }
        }
    }

    /**
     * Tanpa sesi tidak ada satu pun rute matriks yang menjawab: semuanya ke halaman masuk
     * LokaAuth, dengan `next` kembali ke alamat yang diminta.
     */
    public function testTanpaSesiSemuaRuteMengarahKeHalamanMasuk(): void
    {
        $d   = $this->dunia();
        $url = rtrim((string) getenv('APP_URL'), '/');
        foreach ($this->matriks($d) as $pola => [$jalur]) {
            $r = $this->tamu()->get($jalur);
            $this->assertContains($r->status, [302, 303, 307], $pola);
            $this->assertSame((string) getenv('AUTH_LOGIN_URL') . '?next=' . rawurlencode($url . $jalur), $r->header('Location'), $pola);
            $this->assertSame('', trim($r->body), "{$pola}: pengalihan tanpa isi");
        }
    }

    /**
     * Akses aplikasi diperiksa sebelum lingkup: pengampu tanpa akses `lokaujian` tidak
     * melihat mata kuliahnya sendiri di sini.
     */
    public function testTanpaAksesAplikasiPengampuPunDitolak(): void
    {
        $d = $this->dunia();
        $this->idn->query("UPDATE app_access SET enabled = 0 WHERE user_id = '{$d['pengampuX']['id']}' AND app = 'lokaujian'");
        foreach (['/beranda', '/mata-kuliah', '/mata-kuliah/' . $d['matkulX'], '/kelas/' . $d['kelasXA']] as $jalur) {
            $r = $this->sebagai($d['pengampuX'])->get($jalur);
            $this->assertSame(403, $r->status, $jalur);
            $this->assertStringNotContainsString('UJX101', $r->body, $jalur);
        }
    }
}
