<?php

declare(strict_types=1);

namespace Tests\Policy;

use Tests\Support\KodeCase;

/**
 * Matriks hak akses (SPEC.md §3) untuk SETIAP rute LokaKode, lewat HTTP sungguhan.
 *
 * Dua jaminan:
 *   1. rute yang terdaftar di Config/Routes.php tetapi tidak ada di matriks ini
 *      menggagalkan tes — rute baru tidak bisa lolos tanpa uji hak akses;
 *   2. setiap rute dicoba oleh enam pelaku; yang tidak berhak harus mendapat
 *      403 (peran tidak pernah boleh) atau 404 (di luar lingkupnya).
 *
 * Sasaran rute berparameter selalu mata kuliah X dan kelas XA dari KodeCase::dunia().
 */
final class MatriksAksesTest extends KodeCase
{
    private const PELAKU = ['superadmin', 'pengampuX', 'pengampuY', 'pengajarA', 'pengajarB', 'mhsA'];

    // Rute paket bersama: diuji oleh ClientAppTest di paket (KONTRAK.md §4).
    private const PAKET = ['GET healthz', 'POST keluar', 'POST sesi/perpanjang', 'POST preferensi'];

    // Rute tanpa sesi. Hanya boleh berisi rute yang tidak membaca data apa pun.
    private const PUBLIK = ['GET /'];

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
            'GET beranda' => ['/beranda', $semua],

            'GET mata-kuliah' => ['/mata-kuliah', $staf],
            // Pengajar A dan B mengajar kelas di mata kuliah X, jadi boleh melihat mata kuliahnya.
            'GET mata-kuliah/{n}' => ['/mata-kuliah/' . $d['matkulX'], ['pengampuY' => 404] + $staf],
            // Gerbang tahap 6: pengajar B tidak bisa membuka kelas XA milik pengajar A.
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
        $d    = $this->dunia();
        $http = [];
        $csrf = [];
        foreach (self::PELAKU as $p) {
            $http[$p] = $this->sebagai($d[$p]);
            $csrf[$p] = $this->csrf($http[$p]);
            $this->assertNotSame('', $csrf[$p], "token CSRF {$p}");
        }

        foreach ($this->matriks($d) as $pola => [$jalur, $harapan]) {
            $metode = explode(' ', $pola)[0];
            foreach (self::PELAKU as $p) {
                $r = $metode === 'GET' ? $http[$p]->get($jalur) : $http[$p]->post($jalur, ['csrf' => $csrf[$p]]);
                if ($harapan[$p] === 'ok') {
                    $this->assertNotContains($r->status, [401, 403, 404, 500], "{$pola} sebagai {$p}: seharusnya diizinkan, dijawab {$r->status}");
                    $this->assertStringNotContainsString('/login?next=', (string) $r->header('Location'), "{$pola} sebagai {$p}");
                } else {
                    $this->assertSame($harapan[$p], $r->status, "{$pola} sebagai {$p}: seharusnya ditolak");
                }
            }
        }
    }

    public function testRutePublikHanyaMengalihkan(): void
    {
        $r = $this->tamu()->get('/');
        $this->assertContains($r->status, [302, 303, 307]);
        $this->assertSame('https://lokakode.lokandita.com/beranda', $r->header('Location'));
        $this->assertSame('', trim(strip_tags($r->body)), 'akar tidak memuat isi apa pun');
    }

    public function testTanpaSesiSemuaRuteDitolak(): void
    {
        $d = $this->dunia();
        foreach ($this->matriks($d) as $pola => [$jalur]) {
            if (str_starts_with($pola, 'GET ')) {
                $r = $this->tamu()->get($jalur);
                $this->assertContains($r->status, [302, 303, 307], $pola);
                $this->assertStringStartsWith('https://auth.lokandita.com/login?next=', (string) $r->header('Location'), $pola);
            } else {
                // POST tanpa sesi berhenti di CSRF.
                $this->assertSame(403, $this->tamu()->post($jalur)->status, $pola);
            }
        }
    }
}
