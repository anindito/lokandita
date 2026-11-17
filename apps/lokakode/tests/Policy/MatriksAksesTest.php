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
        $kelolaXA = ['superadmin' => 'ok', 'pengampuX' => 'ok', 'pengampuY' => 404, 'pengajarA' => 403, 'pengajarB' => 404, 'mhsA' => 403];
        $lihatXA  = ['pengampuY' => 404, 'pengajarB' => 404] + $staf;
        $mhs      = ['mhsA' => 'ok'] + array_fill_keys(self::PELAKU, 403);
        // Superadmin saja: semua yang lain 403, apa pun lingkupnya.
        $admin = ['superadmin' => 'ok'] + array_fill_keys(self::PELAKU, 403);

        return [
            'GET beranda' => ['/beranda', $semua],
            // Pekerjaan judge milik mhsA: hanya pembuatnya; superadmin dan dosen pun 404 (KONTRAK.md §6).
            'GET api/executions/([^/]+)' => ['/api/executions/' . $d['jobA'], ['mhsA' => 'ok'] + array_fill_keys(self::PELAKU, 404)],

            'GET mata-kuliah' => ['/mata-kuliah', $staf],
            // Pengajar A dan B mengajar kelas di mata kuliah X, jadi boleh melihat mata kuliahnya.
            'GET mata-kuliah/{n}' => ['/mata-kuliah/' . $d['matkulX'], ['pengampuY' => 404] + $staf],
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
            // Pengampu X sekalipun tidak boleh mengubah mata kuliahnya sendiri.
            'POST mata-kuliah/{n}'           => ['/mata-kuliah/' . $d['matkulX'], $admin],

            // Mengubah kelas XA: superadmin dan pengampu X. Pengajar A melihat kelas itu
            // tetapi bukan pengelola (403); pengajar B dan pengampu Y tidak melihatnya (404).
            'GET kelas/{n}/ubah'            => ['/kelas/' . $d['kelasXA'] . '/ubah', $kelolaXA],
            'POST kelas/{n}'                => ['/kelas/' . $d['kelasXA'], $kelolaXA],
            'POST kelas/{n}/arsip'          => ['/kelas/' . $d['kelasXA'] . '/arsip', $kelolaXA],
            'POST kelas/{n}/hapus'          => ['/kelas/' . $d['kelasXA'] . '/hapus', $kelolaXA],
            'POST kelas/{n}/pengajar'       => ['/kelas/' . $d['kelasXA'] . '/pengajar', $kelolaXA],
            'POST kelas/{n}/pengajar/hapus' => ['/kelas/' . $d['kelasXA'] . '/pengajar/hapus', $kelolaXA],
            // Pendaftaran di kelas XA: sama dengan mengubah kelas.
            'POST kelas/{n}/daftar'          => ['/kelas/' . $d['kelasXA'] . '/daftar', $kelolaXA],
            'POST kelas/{n}/daftar/jalankan' => ['/kelas/' . $d['kelasXA'] . '/daftar/jalankan', $kelolaXA],
            'POST kelas/{n}/mundur'          => ['/kelas/' . $d['kelasXA'] . '/mundur', $kelolaXA],
            'POST kelas/{n}/pindah'          => ['/kelas/' . $d['kelasXA'] . '/pindah', $kelolaXA],
            'POST kelas/{n}/lulus'           => ['/kelas/' . $d['kelasXA'] . '/lulus', $kelolaXA],
            'POST kelas/{n}/batal-lulus'     => ['/kelas/' . $d['kelasXA'] . '/batal-lulus', $kelolaXA],
            // Reset kata sandi: siapa pun yang melihat kelas XA, termasuk pengajar A (SPEC.md §3).
            // Tanpa user_id jawabannya 404 untuk semua; hak yang sebenarnya diuji di KelulusanDanSandiTest.
            'POST kelas/{n}/reset-sandi'     => ['/kelas/' . $d['kelasXA'] . '/reset-sandi', array_fill_keys(['superadmin', 'pengampuX', 'pengampuY', 'pengajarA', 'pengajarB'], 404) + ['mhsA' => 403]],
            // Kelas baru di mata kuliah X: kedua pengajar melihat mata kuliahnya, tetapi bukan pengelola.
            'POST mata-kuliah/{n}/kelas'    => ['/mata-kuliah/' . $d['matkulX'] . '/kelas', ['pengajarB' => 403] + $kelolaXA],

            // Pertemuan dan konsep mata kuliah X: superadmin dan pengampu X. Kedua pengajar
            // melihat mata kuliahnya tetapi bukan pengelola (403); pengampu Y tidak melihatnya (404).
            'GET mata-kuliah/{n}/pertemuan'     => ['/mata-kuliah/' . $d['matkulX'] . '/pertemuan', ['pengajarB' => 403] + $kelolaXA],
            'POST mata-kuliah/{n}/pertemuan'    => ['/mata-kuliah/' . $d['matkulX'] . '/pertemuan', ['pengajarB' => 403] + $kelolaXA],
            'POST mata-kuliah/{n}/konsep-dasar' => ['/mata-kuliah/' . $d['matkulX'] . '/konsep-dasar', ['pengajarB' => 403] + $kelolaXA],
            'GET pertemuan/{n}'                 => ['/pertemuan/' . $d['pertemuanX1'], ['pengajarB' => 403] + $kelolaXA],
            'POST pertemuan/{n}'                => ['/pertemuan/' . $d['pertemuanX1'], ['pengajarB' => 403] + $kelolaXA],

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
            'GET latihan/soal/{n}'           => ['/latihan/soal/' . $d['mpA'], $mhs],
            'GET api/soal/{n}/keadaan'       => ['/api/soal/' . $d['mpA'] . '/keadaan', $mhs],
            'POST api/soal/{n}/draf'         => ['/api/soal/' . $d['mpA'] . '/draf', $mhs],
            'POST api/soal/{n}/jalankan'     => ['/api/soal/' . $d['mpA'] . '/jalankan', $mhs],
            'POST api/soal/{n}/kirim'        => ['/api/soal/' . $d['mpA'] . '/kirim', $mhs],
            'GET api/soal/{n}/riwayat'       => ['/api/soal/' . $d['mpA'] . '/riwayat', $mhs],
            'GET api/soal/{n}/riwayat/{n}'   => ['/api/soal/' . $d['mpA'] . '/riwayat/' . $d['kirimanA'], $mhs],
            'POST api/integritas'            => ['/api/integritas', $mhs],
            'POST api/panel'                 => ['/api/panel', $mhs],

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
            'POST api/soal/{n}/draf'     => ['source' => 'int main(){return 1;}'],
            'POST api/soal/{n}/jalankan' => ['source' => 'int main(){return 1;}'],
            'POST api/soal/{n}/kirim'    => ['source' => 'int main(){return 1;}'],
            'POST api/integritas'        => ['mp' => (string) $d['mpA'], 'jalur' => 'paste'],
            'POST api/panel'             => ['kiri' => '30', 'atas' => '50'],
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

    public function testRutePublikHanyaMengalihkan(): void
    {
        $r = $this->tamu()->get('/');
        $this->assertContains($r->status, [302, 303, 307]);
        $this->assertSame('https://lokakode.lokandita.com/beranda', $r->header('Location'));
        $this->assertSame('', trim(strip_tags($r->body)), 'akar tidak memuat isi apa pun');
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
