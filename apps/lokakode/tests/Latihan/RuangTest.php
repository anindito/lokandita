<?php

declare(strict_types=1);

namespace Tests\Latihan;

use Tests\Support\KodeCase;

/**
 * Ruang latihan mahasiswa (SPEC.md §11) lewat HTTP sungguhan: halaman
 * pertemuan, halaman soal, draf, peristiwa integritas — dan terutama apa yang
 * TIDAK boleh sampai ke peramban atau ke mahasiswa lain.
 */
final class RuangTest extends KodeCase
{
    /**
     * Dunia uji dengan pertemuan X1 terbit; kelas XA terbuka menurut jadwal (sampai 2099).
     *
     * @return array<string, mixed>
     */
    private function duniaTerbuka(): array
    {
        $d = $this->dunia();
        $d['ms']    = $this->soalSiap($d['pertemuanX1'], 'salam-pembuka', 'MUDAH_SEKALI');
        $d['mudah'] = $this->soalSiap($d['pertemuanX1'], 'jumlah-dua-bilangan', 'MUDAH');
        $d['sulit'] = $this->soalSiap($d['pertemuanX1'], 'paling-sulit', 'SULIT');
        $this->terbit($d['pertemuanX1']);
        $d['jendela'] = $this->jendela($d['pertemuanX1'], $d['kelasXA'], ['override' => 'none', 'open_at' => '2020-01-01 00:00:00', 'close_at' => '2099-01-01 03:00:00']);

        return $d;
    }

    /**
     * Isi blok data halaman soal (yang dibaca public/js/soal.js).
     *
     * @return array<string, mixed>
     */
    private function dataSoal(string $html): array
    {
        $this->assertSame(1, preg_match('#<script type="application/json" id="data-soal"[^>]*>(.*?)</script>#s', $html, $m), 'blok data-soal');

        return json_decode($m[1], true, 512, JSON_THROW_ON_ERROR);
    }

    public function testBerandaDanHalamanPertemuanMenampilkanKartuDanRingkasanTanpaIsiSoal(): void
    {
        $d = $this->duniaTerbuka();
        // Mudah sekali 3 × 10/10 = 3; mudah 5 × 6/10 = 3. Terhitung 6 dari target 60.
        $this->kirimanDinilai($d['mhsA'], $d['ms'], $this->instans($d['mhsA'], $d['ms']), 10);
        $this->kirimanDinilai($d['mhsA'], $d['mudah'], $this->instans($d['mhsA'], $d['mudah'], 1), 6);
        $http = $this->sebagai($d['mhsA']);

        $beranda = $http->get('/beranda');
        $this->assertSame(200, $beranda->status);
        $this->assertStringContainsString('href="/latihan/' . $d['pertemuanX1'] . '"', $beranda->body);
        $this->assertStringContainsString('mode-kerja', $beranda->body);
        $this->assertStringContainsString('<td>10</td>', $beranda->body, 'nilai pertemuan 6/60 = 10');
        // Pertemuan mata kuliahnya yang belum terbit tampil juga, dengan sebabnya (keputusan pemilik, tahap 9b).
        $this->assertStringContainsString('/latihan/' . $d['pertemuanX2'], $beranda->body);
        $this->assertStringContainsString('data-sebab="belum_terbit"', $beranda->body);

        $hal = $http->get('/latihan/' . $d['pertemuanX1']);
        $this->assertSame(200, $hal->status);
        // Kartu: nomor, judul, tingkat, poin, status, poin yang didapat.
        $this->assertSame(3, substr_count($hal->body, 'class="kartu-soal '));
        foreach (['Mudah sekali', '3 poin', 'Mudah', '5 poin', 'Sulit', '15 poin', 'Selesai', 'Didapat 3 dari 3', 'Sudah dicoba', 'Didapat 3 dari 5', 'Belum dicoba', '1 kali kirim'] as $teks) {
            $this->assertStringContainsString($teks, $hal->body, $teks);
        }
        foreach (['tingkat-mudah_sekali', 'tingkat-mudah"', 'tingkat-sulit'] as $kelas) {
            $this->assertStringContainsString($kelas, $hal->body);
        }
        // Kepala: poin menuju target, kuota mudah sekali, poin yang masih dibutuhkan, sisa waktu.
        $this->assertStringContainsString('6 dari 60 poin', $hal->body);
        $this->assertStringContainsString('Mudah sekali: 1/4 terhitung', $hal->body);
        $this->assertStringContainsString('Masih perlu 54 poin', $hal->body);
        $this->assertStringContainsString('data-hitung-mundur="2099-01-01T03:00:00Z"', $hal->body);
        $this->assertStringContainsString('Ditutup 01-01-2099 10:00 WIB', $hal->body);
        // SPEC.md §11: hanya isi soal yang dibuka yang dikirim ke peramban.
        foreach (['Diberikan', 'random.Random', 'std::cin', '900001', '1700008'] as $rahasia) {
            $this->assertStringNotContainsString($rahasia, $hal->body, "halaman pertemuan memuat isi soal: {$rahasia}");
        }
        $this->assertStringContainsString("style-src 'self';", (string) $hal->header('Content-Security-Policy'), 'CSP halaman lain tidak dilonggarkan');
    }

    public function testModePertemuanMenentukanApaYangTerlihatDanBisaDilakukan(): void
    {
        $d    = $this->duniaTerbuka();
        $ins  = $this->instans($d['mhsA'], $d['mudah']);
        $this->kirimanDinilai($d['mhsA'], $d['mudah'], $ins, 10);
        $http = $this->sebagai($d['mhsA']);
        $csrf = $this->csrf($http);
        $hal  = '/latihan/' . $d['pertemuanX1'];
        $soal = '/latihan/soal/' . $d['mudah']['mp'];
        $api  = '/api/soal/' . $d['mudah']['mp'];
        $atur = fn (string $set) => $this->db->query("UPDATE meeting_class_windows SET {$set} WHERE id = {$d['jendela']}");
        $kode = 'int main(){return 0;}';

        // kerja: jendela terbuka.
        $this->assertSame(200, $http->get($soal)->status);
        $this->assertStringContainsString('data-aksi="kirim"', $http->get($soal)->body);

        // tunggu: jadwal belum tiba. Judul dan waktu buka saja; tidak ada kartu, soal 404.
        $atur("open_at = '2098-01-01 01:00:00'");
        $r = $http->get($hal);
        $this->assertSame(200, $r->status);
        $this->assertStringContainsString('Pertemuan ini belum dibuka untuk kelas Anda.', $r->body);
        $this->assertStringContainsString('Dibuka 01-01-2098 08:00 WIB', $r->body);
        $this->assertStringNotContainsString('kartu-soal', $r->body);
        $this->assertStringNotContainsString('Jumlah Dua Bilangan', $r->body);
        $this->assertSame(404, $http->get($soal)->status);
        $this->assertSame(404, $http->post($api . '/kirim', ['csrf' => $csrf, 'source' => $kode])->status);
        $this->assertSame(404, $http->get($api . '/riwayat')->status);

        // nilai: sudah tutup, hidden. Nilainya saja.
        $atur("open_at = '2020-01-01 00:00:00', close_at = '2020-01-02 00:00:00', post_close_visibility = 'hidden'");
        $r = $http->get($hal);
        $this->assertSame(200, $r->status);
        $this->assertStringContainsString('Pertemuan ini sudah ditutup. Soalnya tidak ditampilkan lagi', $r->body);
        $this->assertStringContainsString('5 dari 60 poin', $r->body);
        $this->assertStringNotContainsString('kartu-soal', $r->body);
        $this->assertSame(404, $http->get($soal)->status);
        $this->assertSame(404, $http->get($api . '/riwayat')->status);
        $this->assertSame(404, $http->post($api . '/jalankan', ['csrf' => $csrf, 'source' => $kode])->status);

        // baca: sudah tutup, review_only. Soal dan kiriman sendiri terbaca; tidak bisa Run, Submit atau menyimpan draf.
        $atur("post_close_visibility = 'review_only'");
        $r = $http->get($hal);
        $this->assertStringContainsString('kartu-soal', $r->body);
        $this->assertStringContainsString('tidak bisa mengirim jawaban lagi', $r->body);
        $r = $http->get($soal);
        $this->assertSame(200, $r->status);
        $this->assertStringNotContainsString('data-aksi="kirim"', $r->body);
        $this->assertStringNotContainsString('data-aksi="jalankan"', $r->body);
        $this->assertSame('baca', $this->dataSoal($r->body)['mode']);
        $this->assertSame(200, $http->get($api . '/riwayat')->status);
        foreach (['/kirim', '/jalankan', '/draf'] as $aksi) {
            $r = $http->post($api . $aksi, ['csrf' => $csrf, 'source' => $kode]);
            $this->assertSame(403, $r->status, $aksi);
            $this->assertSame('tertutup', $r->json()['error']);
        }

        // Dibuka manual lagi: kerja. Tetapi tahun ajaran yang diarsipkan tidak menerima kiriman.
        $atur("override = 'open'");
        $this->assertSame(200, $http->post($api . '/draf', ['csrf' => $csrf, 'source' => $kode])->status);
        $this->db->query("UPDATE academic_years SET status = 'archived' WHERE id = {$d['tahun']}");
        $this->assertSame(403, $http->post($api . '/kirim', ['csrf' => $csrf, 'source' => $kode])->status);
        $this->db->query("UPDATE academic_years SET status = 'active' WHERE id = {$d['tahun']}");

        // Sudah LULUS: masih terbaca, tidak bisa mengirim (SPEC.md §5).
        $this->db->query("UPDATE enrollments SET status = 'LULUS', graduated_on = '2026-11-01' WHERE id = {$d['daftarA']}");
        $this->assertStringContainsString('Anda sudah dinyatakan lulus di kelas ini.', $http->get($hal)->body);
        $this->assertSame(200, $http->get($soal)->status);
        $this->assertSame(403, $http->post($api . '/kirim', ['csrf' => $csrf, 'source' => $kode])->status);

        // Mengundurkan diri, atau pertemuan tidak dipakai latihan: tidak ada.
        $this->db->query("UPDATE enrollments SET status = 'MENGUNDURKAN_DIRI' WHERE id = {$d['daftarA']}");
        $this->assertSame(404, $http->get($hal)->status);
        $this->assertSame(404, $http->get($soal)->status);
        $this->db->query("UPDATE enrollments SET status = 'AKTIF' WHERE id = {$d['daftarA']}");
        $this->assertSame(200, $http->get($hal)->status);
        // Pertemuan ditarik: halamannya menyebut sebabnya (testMahasiswaDiberiTahuMengapa…), soalnya tidak ada,
        // dan jendela yang terbuka tidak membuatnya bisa dikerjakan.
        $this->db->query("UPDATE meetings SET published_at = NULL WHERE id = {$d['pertemuanX1']}");
        $r = $http->get($hal);
        $this->assertSame(200, $r->status);
        $this->assertStringContainsString('data-sebab="belum_terbit"', $r->body);
        $this->assertStringNotContainsString('kartu-soal', $r->body);
        $this->assertSame(404, $http->get($soal)->status);
        $this->assertSame(404, $http->post($api . '/kirim', ['csrf' => $csrf, 'source' => $kode])->status);
        $this->terbit($d['pertemuanX1'], ['enabled' => 0]);
        $this->assertSame(404, $http->get($hal)->status);

        $this->assertSame(1, $this->hitung('SELECT COUNT(*) FROM submissions'), 'tidak ada kiriman baru dari permintaan yang ditolak');
        // Dua pekerjaan saja: milik dunia uji dan milik kiriman yang disisipkan di atas.
        $this->assertSame(2, $this->hitung('SELECT COUNT(*) FROM judge_jobs'), 'tidak ada pekerjaan judge baru');
    }

    public function testMahasiswaDiberiTahuMengapaPertemuanBelumBisaDikerjakan(): void
    {
        $d = $this->dunia();
        $d['mudah'] = $this->soalSiap($d['pertemuanX1'], 'jumlah-dua-bilangan', 'MUDAH');
        $this->db->query("UPDATE meetings SET title = 'Masukan dan Keluaran' WHERE id = {$d['pertemuanX1']}");
        $http = $this->sebagai($d['mhsA']);
        $hal  = '/latihan/' . $d['pertemuanX1'];
        $soal = '/latihan/soal/' . $d['mudah']['mp'];

        // 1. Belum diterbitkan (dan belum ada jendela): beranda dan halaman pertemuan menyebut sebabnya.
        //    Yang terlihat hanya nomor dan judul; tidak ada kartu, judul soal, atau soal.
        $beranda = $http->get('/beranda');
        $this->assertStringContainsString('Masukan dan Keluaran', $beranda->body);
        $this->assertStringContainsString('data-sebab="belum_terbit"', $beranda->body);
        $this->assertStringContainsString('Belum diterbitkan dosen pengampu', $beranda->body);
        $r = $http->get($hal);
        $this->assertSame(200, $r->status);
        $this->assertStringContainsString('Pertemuan ini belum diterbitkan.', $r->body);
        $this->assertStringContainsString('Dosen pengampu belum menerbitkan soalnya', $r->body);
        $this->assertStringNotContainsString('kartu-soal', $r->body);
        $this->assertStringNotContainsString('Jumlah Dua Bilangan', $r->body);
        $this->assertSame(404, $http->get($soal)->status);

        // 2. Diterbitkan, tetapi kelas XA belum punya jendela: "belum dibuka untuk kelas Anda", dengan nama kelasnya.
        $this->terbit($d['pertemuanX1']);
        $this->assertStringContainsString('data-sebab="belum_dibuka"', $http->get('/beranda')->body);
        $r = $http->get($hal);
        $this->assertSame(200, $r->status);
        $this->assertStringContainsString('Pertemuan ini belum dibuka untuk kelas Anda.', $r->body);
        $this->assertStringContainsString('pengajar kelas XA belum membukanya', $r->body);
        $this->assertStringNotContainsString('kartu-soal', $r->body);
        $this->assertSame(404, $http->get($soal)->status);

        // Kelas LAIN di mata kuliah yang sama sudah dibuka: itu tidak membuka apa pun untuk kelas XA.
        $this->jendela($d['pertemuanX1'], $d['kelasXB']);
        $this->assertStringContainsString('data-sebab="belum_dibuka"', $http->get($hal)->body);
        $this->assertSame(404, $http->get($soal)->status);
        $lain = $this->sebagai($d['mhsB']);
        $this->assertStringContainsString('kartu-soal', $lain->get($hal)->body);

        // 3. Jendela ada tetapi belum dijadwalkan dan tidak dibuka manual: sebab yang sama.
        $j = $this->jendela($d['pertemuanX1'], $d['kelasXA'], ['override' => 'none']);
        $this->assertStringContainsString('data-sebab="belum_dibuka"', $http->get($hal)->body);

        // 4. Terjadwal: waktu bukanya disebut.
        $this->db->query("UPDATE meeting_class_windows SET open_at = '2098-01-01 01:00:00', close_at = '2098-01-01 03:00:00' WHERE id = {$j}");
        $r = $http->get($hal);
        $this->assertStringContainsString('data-sebab="terjadwal"', $r->body);
        $this->assertStringContainsString('Dibuka 01-01-2098 08:00 WIB', $r->body);
        $this->assertStringContainsString('Dibuka 01-01-2098 08:00 WIB', $http->get('/beranda')->body);

        // 5. Terbuka: tidak ada sebab lagi.
        $this->db->query("UPDATE meeting_class_windows SET override = 'open' WHERE id = {$j}");
        $r = $http->get($hal);
        $this->assertStringNotContainsString('data-sebab', $r->body);
        $this->assertStringContainsString('kartu-soal', $r->body);

        // Mata kuliah LAIN tetap tidak ada sama sekali — terbit atau belum, jawabannya 404 yang sama
        // dengan nomor yang tidak ada, dan tidak muncul di beranda.
        $asing = $http->get('/latihan/' . $d['pertemuanY1']);
        $isiAsing = self::inti($asing->body, '/latihan/' . $d['pertemuanY1']);
        $this->assertSame(404, $asing->status);
        $tiada = $http->get('/latihan/999999');
        $this->assertSame(404, $tiada->status);
        $this->assertSame($isiAsing, self::inti($tiada->body, '/latihan/999999'));
        $this->terbit($d['pertemuanY1']);
        $this->assertSame(404, $http->get('/latihan/' . $d['pertemuanY1'])->status);
        $this->assertStringNotContainsString('UJY201', $http->get('/beranda')->body);

        // Pertemuan yang tidak dipakai untuk latihan (UTS, UAS) tidak ditampilkan dan tidak dijelaskan.
        $this->db->query("UPDATE meetings SET enabled = 0 WHERE id = {$d['pertemuanX2']}");
        $this->assertSame(404, $http->get('/latihan/' . $d['pertemuanX2'])->status);
        $this->assertStringNotContainsString('/latihan/' . $d['pertemuanX2'] . '"', $http->get('/beranda')->body);
    }

    public function testMahasiswaLainTidakBisaMembukaSoalDrafAtauKirimanOrangLain(): void
    {
        $d   = $this->duniaTerbuka();
        $ins = $this->instans($d['mhsA'], $d['mudah']);
        $kir = $this->kirimanDinilai($d['mhsA'], $d['mudah'], $ins, 10, 10, 'int main(){/* SUMBER-RAHASIA-A */}');
        $job = $this->row("SELECT job_id FROM submissions WHERE id = {$kir}")['job_id'];
        $mp  = $d['mudah']['mp'];
        $a   = $this->sebagai($d['mhsA']);
        $this->assertSame(200, $a->post("/api/soal/{$mp}/draf", ['csrf' => $this->csrf($a), 'source' => 'DRAF-RAHASIA-A'])->status);
        $potret = fn () => [
            $this->db->query('SELECT * FROM drafts')->fetch_all(MYSQLI_ASSOC),
            $this->hitung('SELECT COUNT(*) FROM submissions'), $this->hitung('SELECT COUNT(*) FROM judge_jobs'),
            $this->hitung('SELECT COUNT(*) FROM integrity_events'), $this->hitung('SELECT COUNT(*) FROM problem_instances'),
        ];
        $awal = $potret();

        // Mahasiswa B: mata kuliah yang sama, kelas XB yang tidak punya jendela. Mahasiswa Y: mata kuliah lain.
        foreach (['mhsB', 'mhsY'] as $siapa) {
            $http  = $this->sebagai($d[$siapa]);
            $csrf  = $this->csrf($http);
            $tiada = self::inti($http->get('/latihan/soal/999999')->body, '/latihan/soal/999999');
            $r     = $http->get("/latihan/soal/{$mp}");
            $this->assertSame(404, $r->status, $siapa);
            $this->assertGreaterThan(500, strlen($tiada));
            $this->assertSame($tiada, self::inti($r->body, "/latihan/soal/{$mp}"), 'soal kelas lain dijawab seperti soal yang tidak ada');
            // Halaman pertemuan: mahasiswa mata kuliah lain 404. Mahasiswa B (mata kuliah sama, kelasnya belum
            // dibuka) mendapat SEBABNYA saja sejak tahap 9b — tanpa kartu, judul soal, atau apa pun milik orang lain.
            $hal = $http->get('/latihan/' . $d['pertemuanX1']);
            $this->assertSame($siapa === 'mhsB' ? 200 : 404, $hal->status, $siapa);
            if ($siapa === 'mhsB') {
                $this->assertStringContainsString('data-sebab="belum_dibuka"', $hal->body);
            }
            foreach (['kartu-soal', 'RAHASIA', 'Jumlah Dua Bilangan', $d['mhsA']['nomor_induk']] as $bocor) {
                $this->assertStringNotContainsString($bocor, $hal->body, "{$siapa}: {$bocor}");
            }
            foreach (["/api/soal/{$mp}/keadaan", "/api/soal/{$mp}/riwayat", "/api/soal/{$mp}/riwayat/{$kir}", "/api/executions/{$job}"] as $jalur) {
                $r = $http->get($jalur);
                $this->assertSame(404, $r->status, "{$siapa} {$jalur}");
                $this->assertStringNotContainsString('RAHASIA', $r->body);
            }
            foreach (['draf', 'jalankan', 'kirim'] as $aksi) {
                $this->assertSame(404, $http->post("/api/soal/{$mp}/{$aksi}", ['csrf' => $csrf, 'source' => 'int main(){}'])->status, "{$siapa} {$aksi}");
            }
            $this->assertSame(404, $http->post('/api/integritas', ['csrf' => $csrf, 'mp' => (string) $mp, 'jalur' => 'paste'])->status);
        }

        // Mahasiswa B kini punya jendela sendiri dan instans sendiri: ia tetap hanya melihat miliknya.
        $this->jendela($d['pertemuanX1'], $d['kelasXB']);
        $this->instans($d['mhsB'], $d['mudah'], 5);
        $b = $this->sebagai($d['mhsB']);
        $this->assertSame(200, $b->get("/latihan/soal/{$mp}")->status);
        $this->assertSame(404, $b->get("/api/soal/{$mp}/riwayat/{$kir}")->status, 'kiriman mahasiswa A');
        $this->assertSame(404, $b->get("/api/executions/{$job}")->status, 'pekerjaan judge mahasiswa A');
        $this->assertSame([], $b->get("/api/soal/{$mp}/riwayat")->json()['submissions']);
        $hal = $b->get("/latihan/soal/{$mp}");
        $this->assertNull($this->dataSoal($hal->body)['draf'], 'draf mahasiswa A tidak terbaca');
        $this->assertStringNotContainsString('RAHASIA', $hal->body);
        // Contohnya milik instans B (bibit 5), bukan milik A.
        $this->assertStringContainsString('905001 805007', $hal->body);
        $this->assertStringNotContainsString('900001 800007', $hal->body);
        $this->assertStringContainsString('Belum ada kiriman', $hal->body);

        $akhir    = $potret();
        $akhir[4] -= 1; // instans B yang disisipkan tes ini
        $this->assertSame($awal, $akhir, 'permintaan yang ditolak mengubah data');
    }

    public function testHalamanSoalHanyaMemuatSoalItuDanTesContoh(): void
    {
        $d = $this->duniaTerbuka();
        $this->instans($d['mhsA'], $d['mudah']);
        $this->db->query("UPDATE meetings SET autocomplete = 0 WHERE id = {$d['pertemuanX1']}");
        $http = $this->sebagai($d['mhsA']);

        $hal = $http->get('/latihan/soal/' . $d['mudah']['mp']);
        $this->assertSame(200, $hal->status);
        $this->assertStringContainsString('no-store', (string) $hal->header('Cache-Control'));
        // Pernyataan: Markdown dirender, {{n}} diisi, HTML mentah ditampilkan sebagai teks.
        $this->assertStringContainsString('<strong>dua</strong> bilangan sampai 100.', $hal->body);
        $this->assertStringContainsString('&lt;script&gt;alert(1)&lt;/script&gt;', $hal->body);
        $this->assertStringNotContainsString('<script>alert(1)</script>', $hal->body);
        $this->assertStringContainsString('Format masukan', $hal->body);
        $this->assertStringContainsString('Batas waktu: 2 detik per tes', $hal->body);
        // Terjemahan Inggris ada (bertanda terjemahan mesin) dan bisa dipilih; Arab tidak ada.
        $this->assertStringContainsString('data-bahasa-soal="en"', $hal->body);
        $this->assertStringContainsString('terjemahan mesin', $hal->body);
        $this->assertStringNotContainsString('data-bahasa-soal="ar"', $hal->body);

        // Dua tes contoh tampil; sepuluh tes tersembunyi tidak ada di mana pun di halaman.
        $tes = self::tesInstans();
        foreach (array_slice($tes, 0, 2) as $t) {
            $this->assertStringContainsString(trim($t['in']), $hal->body);
            $this->assertStringContainsString(trim($t['out']), $hal->body);
        }
        foreach (array_slice($tes, 2) as $i => $t) {
            $this->assertStringNotContainsString(trim($t['in']), $hal->body, "masukan tes tersembunyi {$i}");
            $this->assertStringNotContainsString(trim($t['out']), $hal->body, "keluaran tes tersembunyi {$i}");
        }
        // Generator, solusi referensi dan soal lain tidak ikut.
        foreach (['random.Random', 'std::cin', 'os.environ', 'salam-pembuka', 'paling-sulit'] as $rahasia) {
            $this->assertStringNotContainsString($rahasia, $hal->body, $rahasia);
        }

        // Tanda air: nomor induk dan nama; soal bertanda tidak bisa disalin.
        $this->assertStringContainsString('data-tanda-air', $hal->body);
        $this->assertStringContainsString(esc($d['mhsA']['nomor_induk'] . ' · Mahasiswa Ani · '), $hal->body);
        $this->assertStringContainsString('class="pernyataan-wadah tanpa-salin"', $hal->body);
        $this->assertStringContainsString('Halaman soal tidak bisa dicetak.', $hal->body);

        // Data untuk skrip: templat bawaan C++, belum ada draf, pelengkapan dimatikan pertemuan ini.
        $data = $this->dataSoal($hal->body);
        $this->assertSame(['mp' => $d['mudah']['mp'], 'bahasa' => 'cpp', 'mode' => 'kerja', 'draf' => null, 'lengkapi' => false, 'bolehTempel' => false, 'poin' => 5],
            array_intersect_key($data, array_flip(['mp', 'bahasa', 'mode', 'draf', 'lengkapi', 'bolehTempel', 'poin'])));
        $this->assertSame("#include <iostream>\nusing namespace std;\n\nint main() {\n\n    return 0;\n}\n", $data['templat']);
        $this->assertSame(['nomor' => $d['mhsA']['nomor_induk'], 'nama' => 'Mahasiswa Ani'], $data['tanda']);
        $this->assertSame('Menempel ke editor tidak diizinkan. Ketik kode Anda sendiri.', $data['teks']['tempelDiblokir']);

        // CSP: hanya halaman ini yang mengizinkan <style> buatan skrip (Monaco); skrip tetap ketat.
        $csp = (string) $hal->header('Content-Security-Policy');
        $this->assertStringContainsString("style-src 'self' 'unsafe-inline';", $csp);
        $this->assertMatchesRegularExpression("#script-src 'self' 'nonce-[A-Za-z0-9+/=_-]+';#", $csp);
        $this->assertStringNotContainsString('unsafe-eval', $csp);
        // Monaco dan skrip halaman disajikan dari host sendiri.
        foreach (['/monaco/vs/loader.js', '/monaco/vs/editor/editor.main.js', '/js/soal.js', '/js/pertemuan.js', '/js/siapkan.js'] as $aset) {
            $r = $this->tamu()->get($aset);
            $this->assertSame(200, $r->status, $aset);
            $this->assertGreaterThan(500, strlen($r->body), $aset);
        }
        $this->assertStringNotContainsString('//cdn', $hal->body);
    }

    public function testTemplatPertemuanDrafDanPenggunaYangBolehMenempel(): void
    {
        $d = $this->duniaTerbuka();
        $this->instans($d['mhsA'], $d['mudah']);
        $templat = "// Templat pertemuan 1\n#include <iostream>\nint main() {\n}\n";
        $this->terbit($d['pertemuanX1'], ['starter_template' => $templat]);
        $this->idn->query("UPDATE users SET paste_allowed = 1 WHERE id = '{$d['mhsA']['id']}'");
        $http = $this->sebagai($d['mhsA']);
        $csrf = $this->csrf($http);
        $mp   = $d['mudah']['mp'];

        $data = $this->dataSoal($http->get("/latihan/soal/{$mp}")->body);
        $this->assertSame([$templat, null, true], [$data['templat'], $data['draf'], $data['bolehTempel']]);

        // Draf disimpan (akhir baris dinormalkan), ditimpa, dan dipulihkan saat halaman dibuka lagi.
        $r = $http->post("/api/soal/{$mp}/draf", ['csrf' => $csrf, 'source' => "int main() {\r\n  return 1;\r\n}\r\n"]);
        $this->assertSame(200, $r->status);
        $this->assertTrue($r->json()['ok']);
        $http->post("/api/soal/{$mp}/draf", ['csrf' => $csrf, 'source' => "int main() {\n  return 2;\n}\n"]);
        $this->assertSame(1, $this->hitung('SELECT COUNT(*) FROM drafts'));
        $data = $this->dataSoal($http->get("/latihan/soal/{$mp}")->body);
        $this->assertSame("int main() {\n  return 2;\n}\n", $data['draf']);
        $this->assertSame($templat, $data['templat'], 'Reset tetap mengembalikan templat, bukan draf');
        // Draf kosong sah (mahasiswa menghapus semuanya); draf raksasa ditolak.
        $this->assertSame(200, $http->post("/api/soal/{$mp}/draf", ['csrf' => $csrf, 'source' => ''])->status);
        $r = $http->post("/api/soal/{$mp}/draf", ['csrf' => $csrf, 'source' => str_repeat('x', 256 * 1024 + 1)]);
        $this->assertSame(422, $r->status);
        $this->assertSame('sumber_besar', $r->json()['error']);
        $this->assertSame('', $this->row('SELECT source FROM drafts')['source']);
        // Tanpa token CSRF tidak ada yang tersimpan.
        $this->assertSame(403, $http->post("/api/soal/{$mp}/draf", ['source' => 'x'])->status);
    }

    public function testPercobaanTempelDicatatSebagaiPeristiwaIntegritasDanUkuranPanelDisimpan(): void
    {
        $d = $this->duniaTerbuka();
        $this->instans($d['mhsA'], $d['mudah']);
        $http = $this->sebagai($d['mhsA']);
        $csrf = $this->csrf($http);
        $mp   = $d['mudah']['mp'];

        $r = $http->post('/api/integritas', ['csrf' => $csrf, 'mp' => (string) $mp, 'jalur' => 'shift_insert']);
        $this->assertSame(200, $r->status);
        $e = $this->row('SELECT * FROM integrity_events');
        $this->assertSame([$d['mhsA']['id'], 'paste_blocked', (string) $mp, '{"jalur": "shift_insert"}'], [$e['user_id'], $e['kind'], $e['meeting_problem_id'], $e['detail']]);
        $this->assertNotNull($e['ip']);
        // Satu peristiwa per detik: menahan tombol tidak membanjiri tabel.
        $this->assertSame(200, $http->post('/api/integritas', ['csrf' => $csrf, 'mp' => (string) $mp, 'jalur' => 'paste'])->status);
        $this->assertSame(1, $this->hitung('SELECT COUNT(*) FROM integrity_events'));
        $this->lupakanLaju();
        $this->assertSame(200, $http->post('/api/integritas', ['csrf' => $csrf, 'mp' => (string) $mp, 'jalur' => 'drop'])->status);
        $this->assertSame(2, $this->hitung('SELECT COUNT(*) FROM integrity_events'));
        $this->lupakanLaju();
        $this->assertSame(422, $http->post('/api/integritas', ['csrf' => $csrf, 'mp' => (string) $mp, 'jalur' => 'lainnya'])->status);
        $this->assertSame(404, $http->post('/api/integritas', ['csrf' => $csrf, 'mp' => '999999', 'jalur' => 'paste'])->status);
        $this->assertSame(2, $this->hitung('SELECT COUNT(*) FROM integrity_events'));

        // Ukuran panel: per pengguna, muncul lagi di halaman soal.
        $this->assertNull($this->dataSoal($http->get("/latihan/soal/{$mp}")->body)['panel']);
        $this->assertSame(200, $http->post('/api/panel', ['csrf' => $csrf, 'kiri' => '33', 'atas' => '55'])->status);
        $this->assertSame(200, $http->post('/api/panel', ['csrf' => $csrf, 'kiri' => '35', 'atas' => '70'])->status);
        $this->assertSame(1, $this->hitung('SELECT COUNT(*) FROM ui_prefs'));
        $this->assertEquals(['kiri' => 35, 'atas' => 70], $this->dataSoal($http->get("/latihan/soal/{$mp}")->body)['panel']);
        foreach ([['kiri' => '5', 'atas' => '50'], ['kiri' => '50', 'atas' => '99'], ['kiri' => 'lebar', 'atas' => '50'], []] as $salah) {
            $this->assertSame(422, $http->post('/api/panel', ['csrf' => $csrf] + $salah)->status);
        }
        $this->assertSame('{"atas": 70, "kiri": 35}', $this->row('SELECT panels FROM ui_prefs')['panels']);
    }

    public function testSoalYangBelumPunyaInstansDisiapkanSaatDibukaDanTidakPernahDibuatUlang(): void
    {
        $d    = $this->duniaTerbuka();
        $mp   = $d['mudah']['mp'];
        $http = $this->sebagai($d['mhsA']);

        // Pra-pembangkitan sudah mengantrekan soal ini di antrean rendah, tetapi belum dikerjakan
        // (barisnya tidak ada di Redis; pemindai worker baru mengambilnya sesudah 10 detik).
        $kunci = "gen:{$mp}:{$d['mhsA']['id']}";
        $seed  = \App\Libraries\Soal\Benih::untuk($d['mhsA']['id'], $d['mudah']['versi']);
        $this->db->query("INSERT INTO judge_jobs (id, kind, priority, status, payload, idempotency_key, created_by, created_at)
            VALUES (UUID(), 'GENERATE', 'low', 'QUEUED', '{\"problem_version_id\": {$d['mudah']['versi']}, \"meeting_problem_id\": {$mp}, \"user_id\": \"{$d['mhsA']['id']}\", \"seed\": {$seed}}',
                '{$kunci}', '{$d['pengajarA']['id']}', UTC_TIMESTAMP())");

        $hal = $http->get("/latihan/soal/{$mp}");
        $this->assertSame(200, $hal->status);
        $this->assertStringContainsString('Menyiapkan soal Anda', $hal->body);
        $this->assertStringContainsString('data-siapkan="/api/soal/' . $mp . '/keadaan"', $hal->body);
        $this->assertStringContainsString('Perkiraan menunggu: sekitar', $hal->body);
        $this->assertStringNotContainsString('data-editor', $hal->body);
        $this->assertStringNotContainsString('Diberikan', $hal->body, 'isi soal belum dikirim sebelum instans ada');
        // Mahasiswanya sedang menunggu: pekerjaan yang sama didahulukan, bukan dibuat dua kali.
        $job = $this->db->query("SELECT priority FROM judge_jobs WHERE idempotency_key = '{$kunci}'")->fetch_all(MYSQLI_ASSOC);
        $this->assertSame([['priority' => 'normal']], $job);

        $keadaan = [];
        $this->tungguSampai(function () use ($http, $mp, &$keadaan) {
            $keadaan = $http->get("/api/soal/{$mp}/keadaan")->json();

            return $keadaan['siap'] === true;
        }, 120, 'instans siap');
        $this->assertSame(['siap' => true, 'gagal' => false, 'perkiraan' => 0, 'worker' => true], $keadaan);

        $hal = $http->get("/latihan/soal/{$mp}");
        $this->assertStringContainsString('data-editor', $hal->body);
        $ins = $this->row("SELECT id, seed, tests FROM problem_instances WHERE user_id = '{$d['mhsA']['id']}' AND meeting_problem_id = {$mp}");
        $this->assertSame((string) $seed, $ins['seed'], 'seed = HMAC(INSTANCE_SECRET, user | versi)');
        $tes = json_decode($ins['tests'], true);
        $this->assertStringContainsString(trim($tes[0]['in']), $hal->body);
        $this->assertStringNotContainsString('>' . trim($tes[7]['in']) . '<', $hal->body);

        // Membuka lagi tidak membuat ulang (SPEC.md §8.2), juga sesudah versi baru disetujui.
        $v2 = $this->versiBaris($d['mudah']['soal'], 2, 'APPROVED', ['generator' => self::GEN_ACAK, 'reference_solution' => self::SOLUSI_JUMLAH, 'difficulty' => 'SULIT']);
        $this->db->query("UPDATE meeting_problems SET problem_version_id = {$v2} WHERE id = {$mp}");
        $hal = $http->get("/latihan/soal/{$mp}");
        $this->assertSame($ins, $this->row("SELECT id, seed, tests FROM problem_instances WHERE user_id = '{$d['mhsA']['id']}' AND meeting_problem_id = {$mp}"));
        $this->assertSame(5, $this->dataSoal($hal->body)['poin'], 'mahasiswa tetap mengerjakan versi instansnya (MUDAH, 5 poin)');
        $this->assertSame(1, $this->hitung("SELECT COUNT(*) FROM judge_jobs WHERE kind = 'GENERATE'"));
    }

    public function testSoalYangGagalDisiapkanDilaporkanDenganJujur(): void
    {
        $d     = $this->duniaTerbuka();
        $rusak = $this->soalSiap($d['pertemuanX1'], 'generator-rusak', 'MUDAH', ['generator' => "import sys\nsys.exit('RAHASIA-GENERATOR')\n"]);
        $http  = $this->sebagai($d['mhsA']);

        $this->assertStringContainsString('Menyiapkan soal Anda', $http->get('/latihan/soal/' . $rusak['mp'])->body);
        $keadaan = [];
        $this->tungguSampai(function () use ($http, $rusak, &$keadaan) {
            $keadaan = $http->get('/api/soal/' . $rusak['mp'] . '/keadaan')->json();

            return $keadaan['gagal'] === true;
        }, 120, 'pembangkitan gagal');
        $this->assertFalse($keadaan['siap']);
        $this->assertStringNotContainsString('RAHASIA-GENERATOR', json_encode($keadaan));
        // Run dan Submit tanpa instans ditolak dengan pesan yang jelas.
        $csrf = $this->csrf($http);
        foreach (['jalankan', 'kirim'] as $aksi) {
            $r = $http->post('/api/soal/' . $rusak['mp'] . '/' . $aksi, ['csrf' => $csrf, 'source' => 'int main(){}']);
            $this->assertSame(409, $r->status, $aksi);
            $this->assertSame('belum_siap', $r->json()['error']);
        }
        $this->assertSame(0, $this->hitung('SELECT COUNT(*) FROM submissions'));
    }
}
