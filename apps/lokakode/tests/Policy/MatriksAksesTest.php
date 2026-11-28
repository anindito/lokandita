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
    // Halaman depan publik dan pilihan bahasa (KONTRAK.md §4 "Public routes"): tests/Publik/DepanTest.php
    // dan ClientAppTest di paket.
    // Halaman SEB publik (petunjuk, periksa, keluar, berkas .seb): tests/Seb/SebTest.php.
    private const PUBLIK = ['GET /', 'GET bahasa/([^/]+)', 'GET seb', 'GET seb/periksa', 'GET seb/keluar', 'GET seb/unduh/([^/]+)'];

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
        $kelolaXA = ['superadmin' => 'ok', 'pengampuX' => 'ok', 'pengampuY' => 404, 'pengajarA' => 403, 'pengajarB' => 404, 'mhsA' => 403];
        $lihatXA  = ['pengampuY' => 404, 'pengajarB' => 404] + $staf;
        $mhs      = ['mhsA' => 'ok'] + array_fill_keys(self::PELAKU, 403);
        // Superadmin saja: semua yang lain 403, apa pun lingkupnya.
        $admin = ['superadmin' => 'ok'] + array_fill_keys(self::PELAKU, 403);

        return [
            'GET beranda' => ['/beranda', $semua],
            // Ekspor data sendiri (KONTRAK.md §14): semua peran, selalu data pengguna sesi itu.
            // Bahwa berkasnya tidak memuat data orang lain diuji di tests/Publik/DataSayaTest.php.
            'GET data-saya'         => ['/data-saya', $semua],
            'POST data-saya/unduh'  => ['/data-saya/unduh', $semua],
            // Pekerjaan judge milik mhsA: hanya pembuatnya; superadmin dan dosen pun 404 (KONTRAK.md §6).
            'GET api/executions/([^/]+)' => ['/api/executions/' . $d['jobA'], ['mhsA' => 'ok'] + array_fill_keys(self::PELAKU, 404)],

            'GET mata-kuliah' => ['/mata-kuliah', $staf],
            // Pengajar A dan B mengajar kelas di mata kuliah X, jadi boleh melihat mata kuliahnya.
            'GET mata-kuliah/{n}' => ['/mata-kuliah/' . $d['matkulX'], ['pengampuY' => 404] + $staf],
            // Alamat lama tahun ajaran dan mata kuliah: pengalihan ke Akun Lokandita, superadmin saja.
            'GET tahun-ajaran'               => ['/tahun-ajaran', $admin],
            'POST tahun-ajaran'              => ['/tahun-ajaran', $admin],
            'GET tahun-ajaran/{n}'           => ['/tahun-ajaran/' . $d['tahun'], $admin],
            'POST tahun-ajaran/{n}'          => ['/tahun-ajaran/' . $d['tahun'], $admin],
            'POST tahun-ajaran/{n}/aktifkan' => ['/tahun-ajaran/' . $d['tahun'] . '/aktifkan', $admin],
            'GET tahun-ajaran/baru'            => ['/tahun-ajaran/baru', $admin],
            'POST tahun-ajaran/baru'           => ['/tahun-ajaran/baru', $admin],
            'POST tahun-ajaran/baru/jalankan'  => ['/tahun-ajaran/baru/jalankan', $admin],
            'GET audit'                      => ['/audit', $admin],
            'GET mata-kuliah/baru'           => ['/mata-kuliah/baru', $admin],
            'POST mata-kuliah'               => ['/mata-kuliah', $admin],
            'GET mata-kuliah/{n}/ubah'       => ['/mata-kuliah/' . $d['matkulX'] . '/ubah', $admin],
            'POST mata-kuliah/{n}'           => ['/mata-kuliah/' . $d['matkulX'], $admin],

            // Alamat lama pengelolaan kelas dan pendaftaran (KONTRAK.md §18): sejak tahap A2 hanya mengalihkan
            // superadmin ke Akun Lokandita; pengampu X pun 403. Bahwa tidak ada yang berubah: RosterHanyaBacaTest.
            'GET kelas/{n}/ubah'            => ['/kelas/' . $d['kelasXA'] . '/ubah', $admin],
            'POST kelas/{n}'                => ['/kelas/' . $d['kelasXA'], $admin],
            'POST kelas/{n}/arsip'          => ['/kelas/' . $d['kelasXA'] . '/arsip', $admin],
            'POST kelas/{n}/hapus'          => ['/kelas/' . $d['kelasXA'] . '/hapus', $admin],
            'POST kelas/{n}/pengajar'       => ['/kelas/' . $d['kelasXA'] . '/pengajar', $admin],
            'POST kelas/{n}/pengajar/hapus' => ['/kelas/' . $d['kelasXA'] . '/pengajar/hapus', $admin],
            'POST kelas/{n}/daftar'          => ['/kelas/' . $d['kelasXA'] . '/daftar', $admin],
            'POST kelas/{n}/daftar/jalankan' => ['/kelas/' . $d['kelasXA'] . '/daftar/jalankan', $admin],
            'POST kelas/{n}/mundur'          => ['/kelas/' . $d['kelasXA'] . '/mundur', $admin],
            'POST kelas/{n}/pindah'          => ['/kelas/' . $d['kelasXA'] . '/pindah', $admin],
            'POST kelas/{n}/lulus'           => ['/kelas/' . $d['kelasXA'] . '/lulus', $admin],
            'POST kelas/{n}/batal-lulus'     => ['/kelas/' . $d['kelasXA'] . '/batal-lulus', $admin],
            // Reset kata sandi: siapa pun yang melihat kelas XA, termasuk pengajar A (SPEC.md §3).
            // Tanpa user_id jawabannya 404 untuk semua; hak yang sebenarnya diuji di ResetSandiTest.
            'POST kelas/{n}/reset-sandi'     => ['/kelas/' . $d['kelasXA'] . '/reset-sandi', array_fill_keys(['superadmin', 'pengampuX', 'pengampuY', 'pengajarA', 'pengajarB'], 404) + ['mhsA' => 403]],
            'POST mata-kuliah/{n}/kelas'    => ['/mata-kuliah/' . $d['matkulX'] . '/kelas', $admin],

            // Pertemuan dan konsep mata kuliah X: superadmin dan pengampu X. Kedua pengajar
            // melihat mata kuliahnya tetapi bukan pengelola (403); pengampu Y tidak melihatnya (404).
            'GET mata-kuliah/{n}/pertemuan'     => ['/mata-kuliah/' . $d['matkulX'] . '/pertemuan', ['pengajarB' => 403] + $kelolaXA],
            'POST mata-kuliah/{n}/pertemuan'    => ['/mata-kuliah/' . $d['matkulX'] . '/pertemuan', ['pengajarB' => 403] + $kelolaXA],
            'POST mata-kuliah/{n}/konsep-dasar' => ['/mata-kuliah/' . $d['matkulX'] . '/konsep-dasar', ['pengajarB' => 403] + $kelolaXA],
            'GET pertemuan/{n}'                 => ['/pertemuan/' . $d['pertemuanX1'], ['pengajarB' => 403] + $kelolaXA],
            'POST pertemuan/{n}'                => ['/pertemuan/' . $d['pertemuanX1'], ['pengajarB' => 403] + $kelolaXA],

            // Konfigurasi SEB mata kuliah X (SPEC.md §13.1): superadmin dan pengampu X; rincian di tests/Seb/SebTest.php.
            'GET mata-kuliah/{n}/seb'        => ['/mata-kuliah/' . $d['matkulX'] . '/seb', ['pengajarB' => 403] + $kelolaXA],
            'POST mata-kuliah/{n}/seb'       => ['/mata-kuliah/' . $d['matkulX'] . '/seb', ['pengajarB' => 403] + $kelolaXA],
            'POST mata-kuliah/{n}/seb/kunci' => ['/mata-kuliah/' . $d['matkulX'] . '/seb/kunci', ['pengajarB' => 403] + $kelolaXA],
            // Denyut SEB: hanya peran mahasiswa.
            'POST api/seb/denyut'            => ['/api/seb/denyut', $mhs],

            // Pengaturan latihan dan penerbitan: sama dengan pertemuannya.
            'POST pertemuan/{n}/latihan'   => ['/pertemuan/' . $d['pertemuanX1'] . '/latihan', ['pengajarB' => 403] + $kelolaXA],
            'POST pertemuan/{n}/terbitkan' => ['/pertemuan/' . $d['pertemuanX1'] . '/terbitkan', ['pengajarB' => 403] + $kelolaXA],
            'POST pertemuan/{n}/tarik'     => ['/pertemuan/' . $d['pertemuanX1'] . '/tarik', ['pengajarB' => 403] + $kelolaXA],
            // Jendela kelas XA dan pra-pembangkitan: siapa pun yang melihat kelas XA, termasuk pengajar A
            // (SPEC.md §3). Pengajar B dan pengampu Y tidak melihat kelas itu (404).
            'GET kelas/{n}/pertemuan'               => ['/kelas/' . $d['kelasXA'] . '/pertemuan', $lihatXA],
            'GET kelas/{n}/pertemuan/{n}'           => ['/kelas/' . $d['kelasXA'] . '/pertemuan/' . $d['pertemuanX1'], $lihatXA],
            'POST kelas/{n}/pertemuan/{n}'          => ['/kelas/' . $d['kelasXA'] . '/pertemuan/' . $d['pertemuanX1'], $lihatXA],
            'POST kelas/{n}/pertemuan/{n}/status'   => ['/kelas/' . $d['kelasXA'] . '/pertemuan/' . $d['pertemuanX1'] . '/status', $lihatXA],
            'POST kelas/{n}/pertemuan/{n}/siapkan'  => ['/kelas/' . $d['kelasXA'] . '/pertemuan/' . $d['pertemuanX1'] . '/siapkan', $lihatXA],
            // Pertemuan ujian (SPEC.md §6): buka selama N menit seperti jendela; merilis nilai hanya superadmin dan
            // pengampu — pengajar A melihat kelasnya tetapi mendapat 403. Rincian di tests/Latihan/UjianTest.php.
            'POST kelas/{n}/pertemuan/{n}/buka-selama' => ['/kelas/' . $d['kelasXA'] . '/pertemuan/' . $d['pertemuanX1'] . '/buka-selama', $lihatXA],
            'POST kelas/{n}/pertemuan/{n}/rilis'       => ['/kelas/' . $d['kelasXA'] . '/pertemuan/' . $d['pertemuanX1'] . '/rilis', $kelolaXA],
            // Papan peringkat (SPEC.md §3): kelas XA untuk yang melihat kelas itu; seluruh mata kuliah
            // hanya superadmin dan pengampu — pengajar A dan B melihat mata kuliahnya tetapi mendapat 403.
            'GET kelas/{n}/pertemuan/{n}/papan' => ['/kelas/' . $d['kelasXA'] . '/pertemuan/' . $d['pertemuanX1'] . '/papan', $lihatXA],
            'GET pertemuan/{n}/papan'           => ['/pertemuan/' . $d['pertemuanX1'] . '/papan', ['pengajarB' => 403] + $kelolaXA],
            // Analitik, kiriman dan kode sumber mahasiswa, ekspor (SPEC.md §3): per kelas untuk yang melihat
            // kelas XA; kiriman mhsA (kelas XA) lewat alamat kelas XA. Lintas kelas: superadmin dan pengampu.
            'GET kelas/{n}/analitik'               => ['/kelas/' . $d['kelasXA'] . '/analitik', $lihatXA],
            'GET kelas/{n}/pertemuan/{n}/analitik' => ['/kelas/' . $d['kelasXA'] . '/pertemuan/' . $d['pertemuanX2'] . '/analitik', $lihatXA],
            'GET kelas/{n}/pertemuan/{n}/kiriman'  => ['/kelas/' . $d['kelasXA'] . '/pertemuan/' . $d['pertemuanX2'] . '/kiriman', $lihatXA],
            'GET kelas/{n}/kiriman/{n}'            => ['/kelas/' . $d['kelasXA'] . '/kiriman/' . $d['kirimanA'], $lihatXA],
            'GET kelas/{n}/ekspor/([^/]+)'         => ['/kelas/' . $d['kelasXA'] . '/ekspor/csv', $lihatXA],
            'GET mata-kuliah/{n}/analitik'         => ['/mata-kuliah/' . $d['matkulX'] . '/analitik', ['pengajarB' => 403] + $kelolaXA],
            'GET mata-kuliah/{n}/ekspor/([^/]+)'   => ['/mata-kuliah/' . $d['matkulX'] . '/ekspor/csv', ['pengajarB' => 403] + $kelolaXA],

            // Peristiwa integritas kelas XA dan ekspornya (SPEC.md §13.2): siapa pun yang melihat kelas XA;
            // isi dan saringannya diuji di tests/Integritas/IntegritasTest.php.
            'GET kelas/{n}/integritas'                 => ['/kelas/' . $d['kelasXA'] . '/integritas', $lihatXA],
            'GET kelas/{n}/integritas/ekspor/([^/]+)'  => ['/kelas/' . $d['kelasXA'] . '/integritas/ekspor/csv', $lihatXA],

            // Klarifikasi soal kelas XA (SPEC.md §14): siapa pun yang melihat kelas XA membaca dan menjawab.
            // Pertanyaannya milik mahasiswa A di kelas XA; rinciannya di tests/Komunikasi/KlarifikasiTest.php.
            'GET kelas/{n}/klarifikasi'             => ['/kelas/' . $d['kelasXA'] . '/klarifikasi', $lihatXA],
            'POST kelas/{n}/klarifikasi/{n}/jawab'  => ['/kelas/' . $d['kelasXA'] . '/klarifikasi/' . $d['klarA'] . '/jawab', $lihatXA],

            // Pengumuman kelas XA (SPEC.md §14): siapa pun yang melihat kelas XA menulis dan menarik.
            'GET kelas/{n}/pengumuman'             => ['/kelas/' . $d['kelasXA'] . '/pengumuman', $lihatXA],
            'POST kelas/{n}/pengumuman'            => ['/kelas/' . $d['kelasXA'] . '/pengumuman', $lihatXA],
            'POST kelas/{n}/pengumuman/{n}/tarik'  => ['/kelas/' . $d['kelasXA'] . '/pengumuman/' . $d['umumA'] . '/tarik', $lihatXA],

            // Bank soal mata kuliah X: sama dengan pertemuannya.
            'GET soal/{n}'             => ['/soal/' . $d['soalX'], ['pengajarB' => 403] + $kelolaXA],
            'POST soal/{n}/setujui'    => ['/soal/' . $d['soalX'] . '/setujui', ['pengajarB' => 403] + $kelolaXA],
            'POST soal/{n}/tolak'      => ['/soal/' . $d['soalX'] . '/tolak', ['pengajarB' => 403] + $kelolaXA],
            'POST soal/{n}/terjemahan' => ['/soal/' . $d['soalX'] . '/terjemahan', ['pengajarB' => 403] + $kelolaXA],
            'GET soal/{n}/ubah'        => ['/soal/' . $d['soalX'] . '/ubah', ['pengajarB' => 403] + $kelolaXA],
            'POST soal/{n}/ubah'       => ['/soal/' . $d['soalX'] . '/ubah', ['pengajarB' => 403] + $kelolaXA],
            // Unggah soal: superadmin saja; pengampu dan pengajar 403 di halaman dan di titik akhirnya (SPEC.md §8.3).
            'GET impor'  => ['/impor', $admin],
            'POST impor' => ['/impor', $admin],
            // Laporan sebuah unggahan: superadmin saja. Bagi dosen mana pun ia sama seperti tidak ada,
            // juga bagi pengampu mata kuliah tujuannya (yang hanya melihat suntingannya sendiri).
            'GET impor/([^/]+)' => ['/impor/' . $d['imporWeb'], ['superadmin' => 'ok', 'mhsA' => 403] + array_fill_keys(self::PELAKU, 404)],

            // Ruang latihan: hanya peran mahasiswa; staf ditolak filter (403). Bahwa mahasiswa LAIN
            // tidak bisa membuka soal, draf atau kiriman mahasiswa A diuji di tests/Latihan/RuangTest.php.
            'GET latihan/{n}'                => ['/latihan/' . $d['pertemuanX2'], $mhs],
            // Papan kelasnya sendiri; papan kelas lain tidak punya rute mahasiswa (tests/Latihan/PapanTest.php).
            'GET latihan/{n}/papan'          => ['/latihan/' . $d['pertemuanX2'] . '/papan', $mhs],
            'GET latihan/soal/{n}'           => ['/latihan/soal/' . $d['mpA'], $mhs],
            'GET api/soal/{n}/keadaan'       => ['/api/soal/' . $d['mpA'] . '/keadaan', $mhs],
            'POST api/soal/{n}/draf'         => ['/api/soal/' . $d['mpA'] . '/draf', $mhs],
            'POST api/soal/{n}/jalankan'     => ['/api/soal/' . $d['mpA'] . '/jalankan', $mhs],
            'POST api/soal/{n}/kirim'        => ['/api/soal/' . $d['mpA'] . '/kirim', $mhs],
            'GET api/soal/{n}/riwayat'       => ['/api/soal/' . $d['mpA'] . '/riwayat', $mhs],
            'GET api/soal/{n}/riwayat/{n}'   => ['/api/soal/' . $d['mpA'] . '/riwayat/' . $d['kirimanA'], $mhs],
            // Klarifikasi soal: bertanya dan membaca (tests/Komunikasi/KlarifikasiTest.php).
            'GET api/soal/{n}/klarifikasi'   => ['/api/soal/' . $d['mpA'] . '/klarifikasi', $mhs],
            'POST api/soal/{n}/klarifikasi'  => ['/api/soal/' . $d['mpA'] . '/klarifikasi', $mhs],
            'POST api/integritas'            => ['/api/integritas', $mhs],
            'POST api/panel'                 => ['/api/panel', $mhs],
            // Pengumuman kelasnya sendiri (tests/Komunikasi/PengumumanTest.php).
            'GET pengumuman'                 => ['/pengumuman', $mhs],
            // Pemberitahuan milik sendiri (tests/Komunikasi/NotifikasiTest.php).
            'GET notifikasi'                 => ['/notifikasi', $mhs],
            'POST notifikasi/baca'           => ['/notifikasi/baca', $mhs],

            // Gerbang tahap 6: pengajar B tidak bisa membuka kelas XA milik pengajar A.
            'GET kelas/{n}' => ['/kelas/' . $d['kelasXA'], ['pengampuY' => 404, 'pengajarB' => 404] + $staf],
        ];
    }

    /**
     * Dunia uji ditambah latihan: pertemuan X2 terbit dan terbuka untuk kelas XA, dengan satu soal
     * yang instans dan satu kirimannya milik mahasiswa A.
     *
     * @return array<string, mixed>
     */
    private function duniaLatihan(): array
    {
        $d    = $this->dunia();
        $soal = $this->soalSiap($d['pertemuanX2'], 'latihan-a');
        $this->terbit($d['pertemuanX2']);
        $this->jendela($d['pertemuanX2'], $d['kelasXA']);
        $d['mpA']      = $soal['mp'];
        $d['kirimanA'] = $this->kirimanDinilai($d['mhsA'], $soal, $this->instans($d['mhsA'], $soal), 7);
        // Satu pertanyaan klarifikasi mahasiswa A pada soal itu (tahap 10b).
        \Lokandita\Auth\Testing\Fixtures::insert($this->db, 'clarifications', [
            'meeting_problem_id' => $soal['mp'], 'class_id' => $d['kelasXA'], 'user_id' => $d['mhsA']['id'],
            'question' => 'PERTANYAAN-RAHASIA', 'created_at' => gmdate('Y-m-d H:i:s'),
        ]);
        $d['klarA'] = (int) $this->db->insert_id;
        // Satu pengumuman kelas XA yang masih tayang (tahap 10b). Menarik yang sudah ditarik bukan galat,
        // jadi ketiga pelaku yang berhak boleh mencobanya berturut-turut.
        \Lokandita\Auth\Testing\Fixtures::insert($this->db, 'announcements', [
            'class_id' => $d['kelasXA'], 'author_id' => $d['pengajarA']['id'], 'title' => 'PENGUMUMAN-RAHASIA', 'body' => 'Isi.', 'created_at' => gmdate('Y-m-d H:i:s'),
        ]);
        $d['umumA'] = (int) $this->db->insert_id;

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
        $diuji = array_merge(self::PAKET, self::PUBLIK, array_keys($this->matriks($this->duniaLatihan())));
        sort($diuji);
        $this->assertSame($diuji, $this->ruteTerdaftar(), 'Rute di Config/Routes.php dan matriks hak akses tidak sama.');
    }

    public function testSetiapRuteMenolakYangTidakBerhak(): void
    {
        $d    = $this->duniaLatihan();
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

    /**
     * Permintaan yang ditolak di atas tidak boleh meninggalkan perubahan apa pun.
     */
    public function testPenolakanTidakMengubahData(): void
    {
        $d      = $this->duniaLatihan();
        $calon  = $this->user('mahasiswa', 'Calon Mahasiswa');
        $potret = fn () => [
            $this->db->query('SELECT * FROM classes ORDER BY id')->fetch_all(MYSQLI_ASSOC),
            $this->db->query('SELECT class_id, user_id FROM class_teachers ORDER BY class_id, user_id')->fetch_all(MYSQLI_ASSOC),
            $this->db->query('SELECT id, class_id, status FROM enrollments ORDER BY id')->fetch_all(MYSQLI_ASSOC),
            $this->db->query('SELECT id, code, name, owner_id FROM courses ORDER BY id')->fetch_all(MYSQLI_ASSOC),
            $this->db->query('SELECT id, label, status FROM academic_years ORDER BY id')->fetch_all(MYSQLI_ASSOC),
            $this->db->query('SELECT id, course_id, number, title, language, enabled, target_points, min_mudah, starter_template, compiler_flags, published_at FROM meetings ORDER BY id')->fetch_all(MYSQLI_ASSOC),
            $this->db->query('SELECT * FROM meeting_class_windows ORDER BY id')->fetch_all(MYSQLI_ASSOC),
            $this->db->query('SELECT id FROM problem_instances ORDER BY id')->fetch_all(MYSQLI_ASSOC),
            $this->db->query('SELECT id, status, score FROM submissions ORDER BY id')->fetch_all(MYSQLI_ASSOC),
            $this->db->query('SELECT * FROM drafts')->fetch_all(MYSQLI_ASSOC),
            $this->db->query('SELECT * FROM integrity_events')->fetch_all(MYSQLI_ASSOC),
            $this->db->query('SELECT * FROM ui_prefs')->fetch_all(MYSQLI_ASSOC),
            $this->db->query('SELECT * FROM problem_scores')->fetch_all(MYSQLI_ASSOC),
            $this->db->query('SELECT * FROM meeting_concepts ORDER BY meeting_id, concept_code')->fetch_all(MYSQLI_ASSOC),
            $this->db->query('SELECT * FROM course_base_concepts ORDER BY course_id, concept_code')->fetch_all(MYSQLI_ASSOC),
            $this->db->query('SELECT id, status, translations, review_note FROM problem_versions ORDER BY id')->fetch_all(MYSQLI_ASSOC),
            $this->db->query('SELECT * FROM meeting_problems ORDER BY id')->fetch_all(MYSQLI_ASSOC),
            $this->db->query('SELECT id FROM imports ORDER BY id')->fetch_all(MYSQLI_ASSOC),
            $this->db->query('SELECT id FROM judge_jobs ORDER BY id')->fetch_all(MYSQLI_ASSOC),
            $this->db->query('SELECT id, course_id, version, config_key, retired_at FROM seb_configs ORDER BY id')->fetch_all(MYSQLI_ASSOC),
            $this->db->query('SELECT seb_config_id, exam_key FROM seb_exam_keys ORDER BY id')->fetch_all(MYSQLI_ASSOC),
            // Tahap 10b. Tabel notifications tidak dipotret: barisnya dibuat sistem saat mahasiswa A memuat halaman.
            $this->db->query('SELECT * FROM clarifications ORDER BY id')->fetch_all(MYSQLI_ASSOC),
            $this->db->query('SELECT * FROM announcements ORDER BY id')->fetch_all(MYSQLI_ASSOC),
        ];
        $awal = $potret();

        // Isian yang SAH, dikirim oleh yang tidak berhak.
        $isian = [
            'POST kelas/{n}'                => ['name' => 'DIBAJAK', 'capacity' => '5'],
            'POST kelas/{n}/arsip'          => ['arsip' => '1'],
            'POST kelas/{n}/hapus'          => ['yakin' => '1'],
            'POST kelas/{n}/pengajar'       => ['nomor_induk' => $d['pengajarB']['nomor_induk']],
            'POST kelas/{n}/pengajar/hapus' => ['user_id' => $d['pengajarA']['id']],
            'POST mata-kuliah/{n}/kelas'    => ['name' => 'DIBAJAK'],
            'POST kelas/{n}/daftar'          => ['nomor' => $calon['nomor_induk']],
            'POST kelas/{n}/daftar/jalankan' => ['nomor' => [$calon['nomor_induk']]],
            'POST kelas/{n}/mundur'          => ['ids' => [$d['daftarA']]],
            'POST kelas/{n}/pindah'          => ['ids' => [$d['daftarA']], 'tujuan' => (string) $d['kelasXB']],
            'POST kelas/{n}/lulus'           => ['ids' => [$d['daftarA']], 'tanggal' => '2027-01-31', 'semua' => '1'],
            'POST kelas/{n}/batal-lulus'     => ['ids' => [$d['daftarA']]],
            'POST kelas/{n}/reset-sandi'     => ['user_id' => $d['mhsA']['id'], 'yakin' => '1'],
            'POST mata-kuliah/{n}'          => ['code' => 'DIBAJAK', 'name' => 'Dibajak', 'pengampu' => $d['pengajarB']['nomor_induk']],
            'POST mata-kuliah'              => ['code' => 'BARU100', 'name' => 'Baru', 'pengampu' => $d['pengajarB']['nomor_induk']],
            'POST mata-kuliah/{n}/pertemuan'    => ['number' => '9', 'title' => 'Dibajak', 'language' => 'cpp'],
            'POST mata-kuliah/{n}/konsep-dasar' => ['konsep' => ['POINTER', 'STRING']],
            'POST pertemuan/{n}'                => ['number' => '1', 'title' => 'Dibajak', 'language' => 'cpp', 'enabled' => '1', 'konsep' => ['POINTER']],
            'POST pertemuan/{n}/latihan'   => ['target_points' => '1', 'min_mudah_sekali' => '0', 'min_mudah' => '0', 'min_mudah_sedang' => '0', 'min_sedang' => '0', 'min_sulit' => '0',
                'starter_template' => 'DIBAJAK', 'compiler_flags' => '-Wall', 'autocomplete' => '1'],
            'POST kelas/{n}/pertemuan/{n}'         => ['open_at' => '2026-11-20T08:00', 'close_at' => '2026-11-20T10:00', 'post_close_visibility' => 'review_only'],
            'POST kelas/{n}/pertemuan/{n}/status'  => ['override' => 'open'],
            'POST kelas/{n}/pertemuan/{n}/buka-selama' => ['menit' => '150'],
            'POST kelas/{n}/pertemuan/{n}/rilis'   => ['rilis' => '1'],
            'POST api/soal/{n}/draf'     => ['source' => 'int main(){return 1;}'],
            'POST api/soal/{n}/jalankan' => ['source' => 'int main(){return 1;}'],
            'POST api/soal/{n}/kirim'    => ['source' => 'int main(){return 1;}'],
            'POST api/integritas'        => ['mp' => (string) $d['mpA'], 'jalur' => 'paste'],
            'POST api/panel'             => ['kiri' => '30', 'atas' => '50'],
            'POST api/soal/{n}/klarifikasi'         => ['pertanyaan' => 'DIBAJAK'],
            'POST kelas/{n}/klarifikasi/{n}/jawab'  => ['jawaban' => 'DIBAJAK', 'tampil' => 'kelas'],
            'POST kelas/{n}/pengumuman'             => ['judul' => 'DIBAJAK', 'isi' => 'DIBAJAK'],
            'POST soal/{n}/setujui'    => ['versi_id' => (string) $d['versiX']],
            'POST soal/{n}/tolak'      => ['versi_id' => (string) $d['versiX'], 'catatan' => 'Dibajak'],
            'POST soal/{n}/terjemahan' => ['versi_id' => (string) $d['versiX'], 'bahasa' => 'en'],
            'POST soal/{n}/ubah'       => ['difficulty' => 'sulit', 'comparison_mode' => 'trimmed', 'time_limit_ms' => '2000', 'samples_count' => '2',
                'hidden_count' => '10', 't_id_title' => 'Dibajak', 't_id_statement' => 'Dibajak', 'generator' => 'print(1)', 'reference_solution' => 'int main(){}'],
            'POST tahun-ajaran'             => ['label' => 'Dibajak', 'starts_on' => '2027-02-01', 'ends_on' => '2027-07-31'],
            'POST tahun-ajaran/{n}'         => ['label' => 'Dibajak', 'starts_on' => '2027-02-01', 'ends_on' => '2027-07-31'],
            'POST tahun-ajaran/baru'          => ['label' => 'Dibajak', 'starts_on' => '2027-02-01', 'ends_on' => '2027-07-31'],
            'POST tahun-ajaran/baru/jalankan' => ['label' => 'Dibajak', 'starts_on' => '2027-02-01', 'ends_on' => '2027-07-31'],
        ];
        foreach ($this->matriks($d) as $pola => [$jalur, $harapan]) {
            if (! str_starts_with($pola, 'POST ')) {
                continue;
            }
            foreach (self::PELAKU as $p) {
                // Dengan isian yang sah, reset kata sandi memang diizinkan bagi yang melihat kelas XA.
                $berhak = $pola === 'POST kelas/{n}/reset-sandi' && in_array($p, ['superadmin', 'pengampuX', 'pengajarA'], true);
                if ($harapan[$p] === 'ok' || $berhak) {
                    continue;
                }
                $http = $this->sebagai($d[$p]);
                $r    = $http->post($jalur, ['csrf' => $this->csrf($http)] + ($isian[$pola] ?? []));
                $this->assertSame($harapan[$p], $r->status, "{$pola} sebagai {$p}");
            }
        }
        $this->assertSame($awal, $potret(), 'permintaan yang ditolak mengubah data');
        $this->assertSame($d['mhsA']['password_hash'], $this->idn->query("SELECT password_hash FROM users WHERE id = '{$d['mhsA']['id']}'")->fetch_row()[0], 'kata sandi mahasiswa tidak berubah');
        $this->assertSame(0, $this->hitung('SELECT COUNT(*) FROM audit_log'), 'permintaan yang ditolak tidak menulis audit perubahan');
    }

    /**
     * Rute publik tidak membaca data: halaman depan sama persis sebelum dan sesudah dunia uji dibuat,
     * dan tidak menyebut satu pun nama, nomor induk, kelas atau mata kuliah dari dunia itu.
     */
    public function testRutePublikTidakMemuatData(): void
    {
        $bersih = static fn (string $html): string => (string) preg_replace('/nonce-[A-Za-z0-9+\/=_-]+|name="csrf" value="[^"]*"/', '', $html);
        $sebelum = $this->tamu()->get('/');
        $this->assertSame(200, $sebelum->status);

        $d = $this->duniaLatihan();
        $r = $this->tamu()->get('/');
        $this->assertSame(200, $r->status);
        $this->assertSame($bersih($sebelum->body), $bersih($r->body), 'halaman depan berubah setelah data dibuat');
        foreach (['superadmin', 'pengampuX', 'pengampuY', 'pengajarA', 'pengajarB', 'mhsA'] as $p) {
            $this->assertStringNotContainsString($d[$p]['nama'], $r->body, $p);
            $this->assertStringNotContainsString($d[$p]['nomor_induk'], $r->body, $p);
        }
        foreach ($this->db->query('SELECT code AS t FROM courses UNION SELECT name FROM courses UNION SELECT slug FROM problems')->fetch_all(MYSQLI_ASSOC) as $b) {
            $this->assertStringNotContainsString($b['t'], $r->body);
        }
    }

    public function testTanpaSesiSemuaRuteDitolak(): void
    {
        $d = $this->duniaLatihan();
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
