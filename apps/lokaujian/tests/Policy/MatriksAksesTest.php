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
 * Sasaran rute berparameter selalu mata kuliah X dan kelas XA dari UjianCase::dunia(), dan —
 * sejak tahap U2 — soal, kelompok, ujian dan gambar mata kuliah X. Untuk rute yang mengubah
 * data, setiap penolakan juga dibuktikan TIDAK mengubah satu baris pun.
 */
final class MatriksAksesTest extends UjianCase
{
    private const PELAKU = ['superadmin', 'pengampuX', 'pengampuY', 'pengajarA', 'pengajarB', 'mhsA', 'mhsB'];

    // Rute paket bersama: diuji oleh ClientAppTest di paket (KONTRAK.md §4).
    private const PAKET = ['GET healthz', 'POST keluar', 'POST sesi/perpanjang', 'POST preferensi'];

    // Rute tanpa sesi. Hanya boleh berisi rute yang tidak membaca data apa pun:
    // halaman depan dan pilihan bahasa (tests/KerangkaTest.php dan ClientAppTest di paket).
    private const PUBLIK = ['GET /', 'GET bahasa/([^/]+)', 'GET seb', 'GET seb/periksa', 'GET seb/keluar'];

    /**
     * Pola rute => [jalur untuk dunia uji, jawaban yang diharapkan per pelaku].
     * 'ok' = diizinkan (bukan 403/404); angka = status penolakan yang diharapkan.
     *
     * Bank soal dan draf ujian mata kuliah X (tahap U2): hanya superadmin dan pengampu X. Pengampu Y
     * tidak melihat mata kuliah X sama sekali (404); pengajar A dan B melihatnya tetapi bukan
     * pengampunya (403); mahasiswa ditolak filter (403). Gambar: siapa pun yang tidak mengelola
     * mata kuliahnya mendapat 404, sama dengan kunci yang tidak ada.
     *
     * @param array<string, mixed> $d
     *
     * @return array<string, array{0: string, 1: array<string, int|string>}>
     */
    private function matriks(array $d): array
    {
        $semua  = array_fill_keys(self::PELAKU, 'ok');
        $staf   = ['mhsA' => 403, 'mhsB' => 403] + $semua;
        $kelola = ['pengampuY' => 404, 'pengajarA' => 403, 'pengajarB' => 403] + $staf;
        // Tahap U3. Menjalankan sesi kelas XA: superadmin, pengampu X, pengajar A. Mengatur sesi
        // (jadwal, hapus): hanya yang mengelola mata kuliahnya — bagi pengajar A tindakan itu tidak
        // ada (404). Rute mahasiswa: staf ditolak filter (403); mahasiswa B, yang bukan anggota
        // kelas XA, mendapat 404 — sama dengan sesi yang tidak ada.
        $jalankan = ['pengampuY' => 404, 'pengajarB' => 404] + $staf;
        $atur     = ['pengajarA' => 404] + $jalankan;
        $mhs      = ['superadmin' => 403, 'pengampuX' => 403, 'pengampuY' => 403, 'pengajarA' => 403, 'pengajarB' => 403, 'mhsA' => 'ok', 'mhsB' => 404];
        $t        = $d['ujianT'] ?? 0;
        $ss       = '/sesi/' . ($d['sesiXA'] ?? 0);
        $us       = '/ujian-saya/' . ($d['sesiXA'] ?? 0);
        $api      = '/api/sesi/' . ($d['sesiXA'] ?? 0);
        $x      = '/mata-kuliah/' . $d['matkulX'];
        $s      = $d['soalX'] ?? 0;
        $k      = $d['kelompokX'] ?? 0;
        $u      = $d['ujianX'] ?? 0;

        return [
            'GET beranda'     => ['/beranda', $semua],
            'GET mata-kuliah' => ['/mata-kuliah', $staf],
            // Pengajar A dan B mengajar kelas di mata kuliah X, jadi boleh melihat mata kuliahnya.
            'GET mata-kuliah/{n}' => [$x, ['pengampuY' => 404] + $staf],
            // Gerbang tahap U1: pengajar B tidak bisa membuka kelas XA milik pengajar A.
            'GET kelas/{n}' => ['/kelas/' . $d['kelasXA'], ['pengampuY' => 404, 'pengajarB' => 404] + $staf],

            'GET mata-kuliah/{n}/bank'                       => [$x . '/bank', $kelola],
            'GET mata-kuliah/{n}/bank/soal/baru'             => [$x . '/bank/soal/baru?tipe=isian', $kelola],
            'POST mata-kuliah/{n}/bank/soal'                 => [$x . '/bank/soal', $kelola],
            'GET mata-kuliah/{n}/bank/soal/{n}'              => [$x . '/bank/soal/' . $s, $kelola],
            'GET mata-kuliah/{n}/bank/soal/{n}/sunting'      => [$x . '/bank/soal/' . $s . '/sunting', $kelola],
            'POST mata-kuliah/{n}/bank/soal/{n}'             => [$x . '/bank/soal/' . $s, $kelola],
            'POST mata-kuliah/{n}/bank/soal/{n}/arsip'       => [$x . '/bank/soal/' . $s . '/arsip', $kelola],
            'POST mata-kuliah/{n}/bank/soal/{n}/hapus'       => [$x . '/bank/soal/' . $s . '/hapus', $kelola],
            'POST mata-kuliah/{n}/bank/soal/{n}/geser'       => [$x . '/bank/soal/' . $s . '/geser', $kelola],
            'GET mata-kuliah/{n}/bank/kelompok/baru'         => [$x . '/bank/kelompok/baru', $kelola],
            'POST mata-kuliah/{n}/bank/kelompok'             => [$x . '/bank/kelompok', $kelola],
            'GET mata-kuliah/{n}/bank/kelompok/{n}'          => [$x . '/bank/kelompok/' . $k, $kelola],
            'GET mata-kuliah/{n}/bank/kelompok/{n}/sunting'  => [$x . '/bank/kelompok/' . $k . '/sunting', $kelola],
            'POST mata-kuliah/{n}/bank/kelompok/{n}'         => [$x . '/bank/kelompok/' . $k, $kelola],
            'POST mata-kuliah/{n}/bank/kelompok/{n}/arsip'   => [$x . '/bank/kelompok/' . $k . '/arsip', $kelola],
            'POST mata-kuliah/{n}/bank/kelompok/{n}/hapus'   => [$x . '/bank/kelompok/' . $k . '/hapus', $kelola],
            'GET mata-kuliah/{n}/ujian'                      => [$x . '/ujian', $kelola],
            'POST mata-kuliah/{n}/ujian'                     => [$x . '/ujian', $kelola],
            'POST mata-kuliah/{n}/ujian/impor'               => [$x . '/ujian/impor', $kelola],
            'GET mata-kuliah/{n}/ujian/{n}'                  => [$x . '/ujian/' . $u, $kelola],
            'GET mata-kuliah/{n}/ujian/{n}/ekspor'           => [$x . '/ujian/' . $u . '/ekspor', $kelola],
            'POST mata-kuliah/{n}/ujian/{n}/ekspor'          => [$x . '/ujian/' . $u . '/ekspor', $kelola],
            'POST mata-kuliah/{n}/ujian/{n}/hapus'           => [$x . '/ujian/' . $u . '/hapus', $kelola],

            'GET mata-kuliah/{n}/ujian/{n}/sunting'           => [$x . '/ujian/' . $u . '/sunting', $kelola],
            'POST mata-kuliah/{n}/ujian/{n}'                  => [$x . '/ujian/' . $u, $kelola],
            'POST mata-kuliah/{n}/ujian/{n}/butir'            => [$x . '/ujian/' . $u . '/butir', $kelola],
            'POST mata-kuliah/{n}/ujian/{n}/butir/{n}/geser'  => [$x . '/ujian/' . $u . '/butir/1/geser', $kelola],
            'POST mata-kuliah/{n}/ujian/{n}/butir/{n}/hapus'  => [$x . '/ujian/' . $u . '/butir/1/hapus', $kelola],
            'POST mata-kuliah/{n}/ujian/{n}/terbit'           => [$x . '/ujian/' . $u . '/terbit', $kelola],
            'POST mata-kuliah/{n}/ujian/{n}/tarik'            => [$x . '/ujian/' . $t . '/tarik', $kelola],
            'POST mata-kuliah/{n}/ujian/{n}/arsip'            => [$x . '/ujian/' . $t . '/arsip', $kelola],
            'POST mata-kuliah/{n}/ujian/{n}/sesi'             => [$x . '/ujian/' . $t . '/sesi', $kelola],

            // Tahap U4: panel SEB ujian — hanya yang mengelola mata kuliahnya.
            'GET mata-kuliah/{n}/ujian/{n}/seb'         => [$x . '/ujian/' . $t . '/seb', $kelola],
            'POST mata-kuliah/{n}/ujian/{n}/seb'        => [$x . '/ujian/' . $t . '/seb', $kelola],
            'POST mata-kuliah/{n}/ujian/{n}/seb/kunci'  => [$x . '/ujian/' . $t . '/seb/kunci', $kelola],
            'GET mata-kuliah/{n}/ujian/{n}/seb/unduh'   => [$x . '/ujian/' . $t . '/seb/unduh', $kelola],
            'GET sesi/{n}/peristiwa'  => [$ss . '/peristiwa', $jalankan],
            // Berkas .seb ujian: mahasiswa kelasnya sendiri; mahasiswa B 404. Alamat masuk (startURL)
            // hanya mengalihkan — ke sesi sendiri atau ke beranda — dan tidak membedakan siapa pun.
            'GET ujian-saya/{n}/seb'  => [$us . '/seb', $mhs],
            'GET ujian/{n}/masuk'     => ['/ujian/' . $t . '/masuk', $semua, 302],
            // Denyut SEB: mahasiswa mana pun (di kontainer tes ini penegakan dinonaktifkan).
            'POST api/seb/denyut'     => ['/api/seb/denyut', ['mhsA' => 'ok', 'mhsB' => 'ok'] + array_fill_keys(self::PELAKU, 403)],

            'GET sesi/{n}'            => [$ss, $jalankan],
            'GET sesi/{n}/pratinjau'  => [$ss . '/pratinjau', $jalankan],
            'POST sesi/{n}/jadwal'    => [$ss . '/jadwal', $atur],
            'POST sesi/{n}/buka'      => [$ss . '/buka', $jalankan],
            'POST sesi/{n}/tutup'     => [$ss . '/tutup', $jalankan],
            'POST sesi/{n}/mulai'     => [$ss . '/mulai', $jalankan],
            'POST sesi/{n}/akhir'     => [$ss . '/akhir', $jalankan],
            'POST sesi/{n}/akhiri'    => [$ss . '/akhiri', $jalankan],
            'POST sesi/{n}/hapus'     => [$ss . '/hapus', $atur],

            // Tahap U5. Tampilan langsung, tambahan waktu, buka ulang dan CSV: yang menjalankan sesi.
            // Rilis hasil dan koreksi: hanya yang mengelola mata kuliahnya (404 bagi pengajar A pada
            // rilis, seperti jadwal; 403 pada koreksi, seperti bank). Log audit: superadmin saja.
            'GET sesi/{n}/langsung'       => [$ss . '/langsung', $jalankan],
            'POST sesi/{n}/tambah-waktu'  => [$ss . '/tambah-waktu', $jalankan],
            'POST sesi/{n}/buka-ulang'    => [$ss . '/buka-ulang', $jalankan],
            'POST sesi/{n}/csv'           => [$ss . '/csv', $jalankan],
            'POST sesi/{n}/rilis'         => [$ss . '/rilis', $atur],
            'POST sesi/{n}/rilis/tarik'   => [$ss . '/rilis/tarik', $atur],
            'POST ujian/{n}/csv'          => ['/ujian/' . $t . '/csv', $jalankan],
            'GET mata-kuliah/{n}/ujian/{n}/koreksi'       => [$x . '/ujian/' . $t . '/koreksi', $kelola],
            'POST mata-kuliah/{n}/ujian/{n}/koreksi/{n}'  => [$x . '/ujian/' . $t . '/koreksi/' . ($d['soalT'] ?? 0), $kelola],
            'GET audit'                   => ['/audit', ['superadmin' => 'ok'] + array_fill_keys(self::PELAKU, 403)],
            'GET data-saya'               => ['/data-saya', $semua],
            'POST data-saya/unduh'        => ['/data-saya/unduh', $semua],
            // Tinjauan: mahasiswa A diarahkan kembali ke tanda terimanya (hasil belum dirilis).
            'GET ujian-saya/{n}/tinjau'   => [$us . '/tinjau', $mhs, 302],

            'GET ujian-saya/{n}'             => [$us, $mhs],
            'POST ujian-saya/{n}/mulai'      => [$us . '/mulai', $mhs],
            'GET ujian-saya/{n}/kerjakan'    => [$us . '/kerjakan', $mhs],
            'POST ujian-saya/{n}/kumpulkan'  => [$us . '/kumpulkan', $mhs],
            'GET api/sesi/{n}/keadaan'       => [$api . '/keadaan', $mhs],
            'POST api/sesi/{n}/jawaban'      => [$api . '/jawaban', $mhs],
            'POST api/sesi/{n}/peristiwa'    => [$api . '/peristiwa', $mhs],

            // Gambar X ada di ujian terbit yang diberikan ke kelas XA: pengajar A melihatnya lewat
            // pratinjau sesi, dan mahasiswa A selama pengerjaannya berjalan. Selain itu 404.
            'GET gambar/([0-9a-f]{32})' => ['/gambar/' . ($d['gambarX'] ?? str_repeat('0', 32)), ['pengampuY' => 404, 'pengajarB' => 404, 'mhsB' => 404] + $semua],
        ];
    }

    /**
     * Dunia uji ditambah isi bank mata kuliah X: satu soal lepas bergambar, satu kelompok dengan
     * satu soal, dan satu draf ujian yang memakai keduanya — dibuat pengampu X lewat form sungguhan.
     *
     * @return array<string, mixed>
     */
    private function duniaBerisi(): array
    {
        $d    = $this->dunia();
        $http = $this->sebagai($d['pengampuX']);
        $d['soalX']     = $this->buatSoal($http, $d['matkulX'], 'isian', ['text' => 'Rahasia soal X', 'alt' => 'Diagram rahasia X'], ['gambar' => ['x.png', self::gambar()]]);
        $d['kelompokX'] = $this->buatKelompok($http, $d['matkulX'], 'Rahasia bacaan X');
        $this->buatSoal($http, $d['matkulX'], 'uraian', ['text' => 'Rahasia uraian X'], [], $d['kelompokX']);
        $d['ujianX']  = $this->buatDraf($http, $d['matkulX'], 'Rahasia ujian X', ['s' . $d['soalX'], 'k' . $d['kelompokX']]);
        $d['gambarX'] = (string) $this->db->query('SELECT kunci FROM images')->fetch_assoc()['kunci'];
        // Tahap U3: ujian terbit kedua (memuat soal bergambar itu), diberikan ke kelas XA, sudah
        // dimulai dosen, dan mahasiswa A sedang mengerjakannya.
        $d['ujianT'] = $this->buatDraf($http, $d['matkulX'], 'Rahasia ujian terbit X', ['s' . $d['soalX']]);
        $this->assertContains($this->kirim($http, '/mata-kuliah/' . $d['matkulX'] . '/ujian/' . $d['ujianT'] . '/terbit')->status, [302, 303]);
        $d['sesiXA'] = $this->beri($http, $d['matkulX'], $d['ujianT'], $d['kelasXA']);
        $d['soalT']  = (int) $this->db->query('SELECT id FROM exam_questions WHERE exam_id = ' . $d['ujianT'])->fetch_assoc()['id'];
        // Tahap U4: ujian terbit itu punya konfigurasi SEB (supaya berkasnya ada untuk diunduh).
        $this->assertStringEndsWith('?pesan=dibuat', (string) $this->kirim($http, '/mata-kuliah/' . $d['matkulX'] . '/ujian/' . $d['ujianT'] . '/seb', ['sandi_keluar' => 'kata-sandi-keluar-uji'])->header('Location'));
        $this->assertStringEndsWith('?pesan=dimulai', (string) $this->kirim($http, '/sesi/' . $d['sesiXA'] . '/mulai', ['menit' => '60'])->header('Location'));
        $this->mulaiKerja($this->sebagai($d['mhsA']), $d['sesiXA'], (string) $d['mhsA']['id']);

        return $d;
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
        $d       = $this->duniaBerisi();
        $rahasia = ['UJX101', 'Uji Ujian X', 'Pengajar Aminah', 'Pengampu Xaverius', 'Rahasia', 'Diagram rahasia'];
        foreach ($this->matriks($d) as $pola => $baris) {
            [$jalur, $harapan] = $baris;
            $statusOk          = $baris[2] ?? 200;
            $tulis             = str_starts_with($pola, 'POST ');
            foreach (self::PELAKU as $p) {
                $http = $this->sebagai($d[$p]);
                if ($harapan[$p] === 'ok') {
                    // Rute tulis yang diizinkan diuji isinya di tests/Bank dan tests/Berkas; di sini cukup rute baca.
                    if (! $tulis) {
                        $this->assertSame($statusOk, $http->get($jalur)->status, "{$pola} sebagai {$p}: seharusnya diizinkan");
                    }

                    continue;
                }
                $sebelum = $this->sidikBank();
                // Permintaan tulis membawa token CSRF yang sah milik pelaku itu: penolakannya berasal
                // dari hak akses, bukan dari token yang hilang.
                $r = $tulis ? $this->kirim($http, $jalur, [
                    'arsip' => '1', 'judul' => 'Curian', 'text' => 'Curian', 'type' => 'isian', 'points' => '1', 'arah' => 'naik',
                    // Tahap U5: isian yang SAH bagi yang berhak, supaya penolakannya berasal dari hak akses.
                    'mahasiswa' => (string) $d['mhsA']['id'], 'menit' => '10', 'alasan' => 'Curian', 'yakin' => '1', 'tingkat' => 'nilai', 'bentuk' => 'panjang', 'poin' => '5', 'kunci' => '',
                ]) : $http->get($jalur);
                $this->assertSame($harapan[$p], $r->status, "{$pola} sebagai {$p}: seharusnya ditolak");
                $this->assertSame($sebelum, $this->sidikBank(), "{$pola} sebagai {$p}: penolakan mengubah data");
                // Penolakan tidak memuat apa pun tentang sasarannya. Nama dan nomor induk mahasiswa A
                // tidak diperiksa pada jawaban untuk dirinya sendiri: keduanya ada di menu akunnya.
                $rahasiaSasaran = $p === 'mhsA' ? $rahasia : array_merge($rahasia, ['Mahasiswa Ani', (string) $d['mhsA']['nomor_induk']]);
                // Nama pelaku sendiri ada di menu akunnya, di halaman penolakan sekalipun.
                $rahasiaSasaran = array_values(array_diff($rahasiaSasaran, [(string) $d[$p]['nama']]));
                foreach ($rahasiaSasaran as $teks) {
                    $this->assertStringNotContainsString($teks, $r->body, "{$pola} sebagai {$p}: penolakan memuat «{$teks}»");
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
        $d   = $this->duniaBerisi();
        $url = rtrim((string) getenv('APP_URL'), '/');
        foreach ($this->matriks($d) as $pola => $baris) {
            $jalur = $baris[0];
            if (str_starts_with($pola, 'POST ')) {
                // Tanpa sesi tidak ada token CSRF: permintaan tulis berhenti di sana dan tidak mengubah apa pun.
                $sebelum = $this->sidikBank();
                $r       = $this->tamu()->post($jalur, ['text' => 'Curian', 'type' => 'isian', 'judul' => 'Curian']);
                $this->assertNotContains($r->status, [200, 201, 204], $pola);
                $this->assertSame($sebelum, $this->sidikBank(), "{$pola} tanpa sesi mengubah data");

                continue;
            }
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
        foreach (['/beranda', '/mata-kuliah', '/mata-kuliah/' . $d['matkulX'], '/kelas/' . $d['kelasXA'], '/mata-kuliah/' . $d['matkulX'] . '/bank', '/mata-kuliah/' . $d['matkulX'] . '/ujian', '/sesi/1'] as $jalur) {
            $r = $this->sebagai($d['pengampuX'])->get($jalur);
            $this->assertSame(403, $r->status, $jalur);
            $this->assertStringNotContainsString('UJX101', $r->body, $jalur);
        }
    }
}
