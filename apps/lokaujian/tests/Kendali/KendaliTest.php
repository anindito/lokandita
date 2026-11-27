<?php

declare(strict_types=1);

namespace Tests\Kendali;

use Tests\Support\UjianCase;

/**
 * Kendali dosen (SPEC-LOKAUJIAN.md §7.3, §10; KONTRAK.md §24), lewat HTTP sungguhan: tampilan
 * langsung per mahasiswa, tambahan waktu dan buka ulang — dengan aturan waktu dibuktikan lewat
 * alamat simpan yang sebenarnya, dan lingkup kelas: pengajar kelas B tidak pernah menyentuh
 * sesi kelas A.
 */
final class KendaliTest extends UjianCase
{
    /**
     * @return array<string, array<string, mixed>> nama => baris tampilan langsung
     */
    private function langsung(\Lokandita\Auth\Testing\Http $http, int $sesi): array
    {
        $r = $http->get("/sesi/{$sesi}/langsung");
        $this->assertSame(200, $r->status);
        $this->assertStringContainsString('no-store', (string) $r->header('Cache-Control'));

        return ['_' => $r->json()] + array_column($r->json()['baris'], null, 'nama');
    }

    private function tenggat(int $attempt): int
    {
        return (int) $this->db->query(
            "SELECT TIMESTAMPDIFF(SECOND, UTC_TIMESTAMP(), COALESCE(a.reopen_until, CASE WHEN x.mode = 'per_mahasiswa' THEN a.started_at + INTERVAL x.duration_minutes MINUTE
                    ELSE LEAST(COALESCE(s.end_at, s.started_at + INTERVAL 8 HOUR), s.started_at + INTERVAL 8 HOUR) END + INTERVAL a.extra_minutes MINUTE)) AS sisa
             FROM attempts a JOIN sessions s ON s.id = a.session_id JOIN exams x ON x.id = a.exam_id WHERE a.id = {$attempt}",
        )->fetch_assoc()['sisa'];
    }

    public function testTampilanLangsungSatuBarisPerMahasiswaTanpaJawabanDanSkor(): void
    {
        $d    = $this->duniaSesi();
        $ajar = $this->sebagai($d['pengajarA']);
        $ani  = $this->sebagai($d['mhsA']);

        // Sebelum siapa pun datang: dua mahasiswa AKTIF, keduanya belum hadir. Yang lulus,
        // mengundurkan diri dan mahasiswa kelas lain tidak ada di daftar.
        $l = $this->langsung($ajar, $d['sesi']);
        $nama = array_values(array_diff(array_keys($l), ['_']));
        sort($nama); // barisnya sendiri diurutkan menurut nomor induk
        $this->assertSame(['Mahasiswa Ani', 'Mahasiswa Citra'], $nama);
        $this->assertSame($urut = array_column($l['_']['baris'], 'nomor_induk'), (static function (array $n): array { sort($n); return $n; })($urut));
        $this->assertSame(['peserta' => 2, 'hadir' => 0, 'mengerjakan' => 0, 'terputus' => 0, 'selesai' => 0], $l['_']['ringkas']);
        $this->assertSame('belum_hadir', $l['Mahasiswa Ani']['status']);
        $this->assertSame((string) $d['mhsA']['nomor_induk'], $l['Mahasiswa Ani']['nomor_induk']);
        $this->assertSame('berlangsung', $l['_']['keadaan']);
        $this->assertMatchesRegularExpression('/^\d\d:\d\d:\d\d$/', (string) $l['_']['akhir']);

        // Citra membuka halaman menunggu (bertanya keadaan) tanpa mulai: menunggu. Ani mulai dan
        // menyimpan dua jawaban: mengerjakan, tersambung, 2 dari 6, dengan sisa waktu.
        $this->assertSame(200, $this->sebagai($d['mhsC'])->get("/api/sesi/{$d['sesi']}/keadaan")->status);
        $this->mulaiKerja($ani, $d['sesi'], (string) $d['mhsA']['id']);
        $token = $this->bukaHalamanUjian($ani, $d['sesi']);
        $this->assertSame(200, $this->simpan($ani, $d['sesi'], $token, $d['q']['BS'], 'benar', 1)->status);
        $this->assertSame(200, $this->simpan($ani, $d['sesi'], $token, $d['q']['ISIAN'], 'RAHASIA-JAWABAN-ANI', 1)->status);
        $r = $ajar->get("/sesi/{$d['sesi']}/langsung");
        $this->assertStringNotContainsString('RAHASIA-JAWABAN-ANI', $r->body, 'tampilan langsung tidak memuat jawaban');
        $this->assertStringNotContainsString('score', $r->body);
        $l = ['_' => $r->json()] + array_column($r->json()['baris'], null, 'nama');
        $this->assertSame('menunggu', $l['Mahasiswa Citra']['status']);
        $a = $l['Mahasiswa Ani'];
        $this->assertSame(['mengerjakan', true, 2, 6], [$a['status'], $a['tersambung'], $a['terjawab'], $a['total']]);
        $this->assertMatchesRegularExpression('/^\d\d:\d\d:\d\d$/', (string) $a['simpan']);
        $this->assertGreaterThan(3500, $a['sisa']);
        $this->assertLessThanOrEqual(3600, $a['sisa']);
        $this->assertSame('2 / 6', $a['teks']['terjawab']);
        $this->assertSame(['peserta' => 2, 'hadir' => 2, 'mengerjakan' => 1, 'terputus' => 0, 'selesai' => 0], $l['_']['ringkas']);

        // Tanda "terakhir terlihat" yang sudah tua = terputus sejak jam itu. Redis kosong = tidak diketahui.
        $redis = $this->redisTes();
        $redis->setex("lokaujian:hadir:{$d['sesi']}:{$d['mhsA']['id']}", 600, 'kerja|' . (time() - 300));
        $a = $this->langsung($ajar, $d['sesi'])['Mahasiswa Ani'];
        $this->assertFalse($a['tersambung']);
        $this->assertMatchesRegularExpression('/^\d\d:\d\d$/', (string) $a['sejak']);
        $this->assertStringContainsString((string) $a['sejak'], $a['teks']['status']);
        $this->assertSame(1, $this->langsung($ajar, $d['sesi'])['_']['ringkas']['terputus']);
        $redis->del("lokaujian:hadir:{$d['sesi']}:{$d['mhsA']['id']}");
        $a = $this->langsung($ajar, $d['sesi'])['Mahasiswa Ani'];
        $this->assertSame(['mengerjakan', false, null], [$a['status'], $a['tersambung'], $a['sejak']]);

        // Ani mengumpulkan: selesai, oleh mahasiswa. Halaman sesinya sendiri memuat baris yang sama.
        $this->kirim($ani, "/ujian-saya/{$d['sesi']}/kumpulkan");
        $l = $this->langsung($ajar, $d['sesi']);
        $this->assertSame(['selesai', 'mahasiswa'], [$l['Mahasiswa Ani']['status'], $l['Mahasiswa Ani']['cara']]);
        $this->assertSame(1, $l['_']['ringkas']['selesai']);
        $halaman = $ajar->get("/sesi/{$d['sesi']}")->body;
        $this->assertStringContainsString('data-id="' . $d['mhsA']['id'] . '" data-status="selesai"', $halaman);
        $this->assertStringContainsString('/js/langsung.js', $halaman);
        $this->assertStringNotContainsString('RAHASIA-JAWABAN-ANI', $halaman);

        // Lingkup: pengajar B dan pengampu Y mendapat jawaban yang sama dengan sesi yang tidak ada.
        foreach (['pengajarB', 'pengampuY'] as $p) {
            $r = $this->sebagai($d[$p])->get("/sesi/{$d['sesi']}/langsung");
            $this->assertSame(404, $r->status, $p);
            $this->assertStringNotContainsString('Mahasiswa Ani', $r->body, $p);
        }
    }

    public function testTambahanWaktuDenganKonfirmasiHanyaMenambahDanDiaudit(): void
    {
        $d    = $this->duniaSesi();
        $ajar = $this->sebagai($d['pengajarA']);
        $ani  = $this->sebagai($d['mhsA']);
        $a    = (int) $this->mulaiKerja($ani, $d['sesi'], (string) $d['mhsA']['id'])['id'];
        $ini  = "/sesi/{$d['sesi']}/tambah-waktu";
        $dua  = ['mahasiswa' => [(string) $d['mhsA']['id'], (string) $d['mhsC']['id']]];

        // Langkah pertama: halaman konfirmasi yang menyebut mahasiswa dan menitnya; belum ada yang berubah.
        $sebelum = $this->sidikBank();
        $r       = $this->kirim($ajar, $ini, $dua + ['menit' => '15']);
        $this->assertSame(200, $r->status);
        foreach (['Mahasiswa Ani', 'Mahasiswa Citra', (string) $d['mhsA']['nomor_induk'], '15', 'name="yakin" value="1"'] as $teks) {
            $this->assertStringContainsString($teks, $r->body, "konfirmasi memuat «{$teks}»");
        }
        $this->assertSame($sebelum, $this->sidikBank(), 'konfirmasi belum mengubah apa pun');

        // Langkah kedua: diterapkan. Ani (sudah mulai) bertambah 15 menit; Citra (belum mulai)
        // menerimanya saat mulai. Dua baris audit, masing-masing menyebut mahasiswanya.
        $sisa = $this->tenggat($a);
        $this->assertStringEndsWith('?pesan=ditambah', (string) $this->kirim($ajar, $ini, $dua + ['menit' => '15', 'yakin' => '1'])->header('Location'));
        $this->assertEqualsWithDelta($sisa + 900, $this->tenggat($a), 3);
        $this->assertSame('15', (string) $this->pengerjaan($d['sesi'], (string) $d['mhsA']['id'])['extra_minutes']);
        $audit = $this->db->query("SELECT detail, class_id, actor_id FROM audit_log WHERE action = 'waktu.ditambah' ORDER BY id")->fetch_all(MYSQLI_ASSOC);
        $this->assertCount(2, $audit);
        $this->assertSame([(string) $d['mhsA']['id'], 15, true], [json_decode($audit[0]['detail'], true)['mahasiswa'], json_decode($audit[0]['detail'], true)['menit'], json_decode($audit[0]['detail'], true)['sudah_mulai']]);
        $this->assertSame([(string) $d['kelasXA'], (string) $d['pengajarA']['id']], [(string) $audit[1]['class_id'], (string) $audit[1]['actor_id']]);
        $l = $this->langsung($ajar, $d['sesi']);
        $this->assertSame([15, 15], [$l['Mahasiswa Ani']['tambahan'], $l['Mahasiswa Citra']['tambahan']]);
        // Hitung mundur mahasiswa mengikuti: jawaban keadaan memuat sisa yang baru.
        $k = $ani->get("/api/sesi/{$d['sesi']}/keadaan")->json()['pengerjaan'];
        $this->assertSame(15, $k['tambahan']);
        $this->assertEqualsWithDelta($sisa + 900, $k['sisa'], 5);

        // Berapa kali pun, dan hanya menambah.
        $this->kirim($ajar, $ini, ['mahasiswa' => [(string) $d['mhsA']['id']], 'menit' => '5', 'yakin' => '1']);
        $this->assertSame('20', (string) $this->pengerjaan($d['sesi'], (string) $d['mhsA']['id'])['extra_minutes']);
        $c = (int) $this->mulaiKerja($this->sebagai($d['mhsC']), $d['sesi'], (string) $d['mhsC']['id'])['id'];
        $this->assertSame('15', (string) $this->pengerjaan($d['sesi'], (string) $d['mhsC']['id'])['extra_minutes'], 'tambahan sebelum mulai diterapkan saat mulai');
        $this->assertEqualsWithDelta($this->tenggat($a) - 300, $this->tenggat($c), 3);

        // Yang ditolak tidak mengubah apa pun: menit di luar 1–240, tanpa centang, mahasiswa kelas
        // lain, dan yang sudah selesai (seluruh permintaan ditolak, juga bagi Citra).
        $this->kirim($ani, "/ujian-saya/{$d['sesi']}/kumpulkan");
        $sebelum = $this->sidikBank();
        foreach ([
            'g_menit'          => ['mahasiswa' => [(string) $d['mhsC']['id']], 'menit' => '241'],
            'g_menit '         => ['mahasiswa' => [(string) $d['mhsC']['id']], 'menit' => '-5'],
            'g_kosong'         => ['menit' => '10'],
            'g_asing'          => ['mahasiswa' => [(string) $d['mhsB']['id']], 'menit' => '10'],
            'g_asing '         => ['mahasiswa' => [(string) $d['mhsLulus']['id']], 'menit' => '10'],
            'g_sudah_berakhir' => $dua + ['menit' => '10'],
        ] as $pesan => $isian) {
            $this->assertStringEndsWith('?pesan=' . trim($pesan), (string) $this->kirim($ajar, $ini, $isian + ['yakin' => '1'])->header('Location'), $pesan);
        }
        $this->assertSame($sebelum, $this->sidikBank());

        // Pengajar B: 404, tidak ada yang berubah.
        $r = $this->kirim($this->sebagai($d['pengajarB']), $ini, ['mahasiswa' => [(string) $d['mhsC']['id']], 'menit' => '10', 'yakin' => '1']);
        $this->assertSame(404, $r->status);
        $this->assertSame($sebelum, $this->sidikBank());
    }

    public function testTambahanWaktuMembuatSimpanDiterimaSesudahAkhirSesi(): void
    {
        $d   = $this->duniaSesi();
        $ani = $this->sebagai($d['mhsA']);
        $cit = $this->sebagai($d['mhsC']);
        $this->mulaiKerja($ani, $d['sesi'], (string) $d['mhsA']['id']);
        $this->mulaiKerja($cit, $d['sesi'], (string) $d['mhsC']['id']);
        $ta = $this->bukaHalamanUjian($ani, $d['sesi']);
        $tc = $this->bukaHalamanUjian($cit, $d['sesi']);
        $this->kirim($d['dosen'], "/sesi/{$d['sesi']}/tambah-waktu", ['mahasiswa' => [(string) $d['mhsA']['id']], 'menit' => '10', 'yakin' => '1']);
        // Sesi berakhir semenit yang lalu: Citra ditolak, Ani — dengan tambahannya — masih diterima.
        $this->db->query("UPDATE sessions SET end_at = UTC_TIMESTAMP() - INTERVAL 1 MINUTE WHERE id = {$d['sesi']}");
        $this->assertSame(409, $this->simpan($cit, $d['sesi'], $tc, $d['q']['ISIAN'], 'terlambat', 1)->status);
        $r = $this->simpan($ani, $d['sesi'], $ta, $d['q']['ISIAN'], 'masih boleh', 1);
        $this->assertSame(200, $r->status);
        $this->assertEqualsWithDelta(540, $r->json()['sisa'], 5);
        $l = $this->langsung($d['dosen'], $d['sesi']);
        $this->assertSame(['mengerjakan', 'selesai'], [$l['Mahasiswa Ani']['status'], $l['Mahasiswa Citra']['status']]);
        $this->assertSame('waktu_habis', $l['Mahasiswa Citra']['cara']);
    }

    public function testBukaUlangMelanjutkanPengerjaanYangSamaDanMemulaiYangBelumPernah(): void
    {
        $d    = $this->duniaSesi();
        $ajar = $this->sebagai($d['pengajarA']);
        $ani  = $this->sebagai($d['mhsA']);
        $cit  = $this->sebagai($d['mhsC']);
        $a    = (int) $this->mulaiKerja($ani, $d['sesi'], (string) $d['mhsA']['id'])['id'];
        $t    = $this->bukaHalamanUjian($ani, $d['sesi']);
        $this->assertSame(200, $this->simpan($ani, $d['sesi'], $t, $d['q']['BS'], 'benar', 1)->status);
        $this->assertSame(200, $this->simpan($ani, $d['sesi'], $t, $d['q']['URAIAN'], 'jawaban sebelum laptop mati', 1)->status);
        $ini  = "/sesi/{$d['sesi']}/buka-ulang";
        $isiA = ['mahasiswa' => (string) $d['mhsA']['id'], 'menit' => '30', 'alasan' => 'Laptop mati di tengah ujian'];

        // Selagi masih berjalan: ditolak (beri tambahan waktu saja). Citra belum mulai dan sesi masih
        // berlangsung: ditolak (cukup menekan Mulai).
        $sebelum = $this->sidikBank();
        $this->assertStringEndsWith('?pesan=g_masih_berjalan', (string) $this->kirim($ajar, $ini, $isiA + ['yakin' => '1'])->header('Location'));
        $this->assertStringEndsWith('?pesan=g_masih_bisa_mulai', (string) $this->kirim($ajar, $ini, ['mahasiswa' => (string) $d['mhsC']['id'], 'menit' => '30', 'alasan' => 'x', 'yakin' => '1'])->header('Location'));
        $this->assertSame($sebelum, $this->sidikBank());

        // Sesi berakhir; penjadwal menandai dan menilai Ani (BS benar = 1 poin).
        $this->db->query("UPDATE sessions SET end_at = UTC_TIMESTAMP() - INTERVAL 5 MINUTE WHERE id = {$d['sesi']}");
        $this->spark(['ujian:jadwal', '--sekali']);
        $baris = $this->pengerjaan($d['sesi'], (string) $d['mhsA']['id']);
        $this->assertSame(['waktu_habis', '1.00'], [$baris['submit_how'], $baris['score']]);
        $this->assertSame(409, $this->simpan($ani, $d['sesi'], $t, $d['q']['ISIAN'], 'ditolak', 1)->status);

        // Yang ditolak: menit di luar 1–600, tanpa alasan, mahasiswa kelas lain, pengajar B.
        $sebelum = $this->sidikBank();
        foreach (['g_menit' => ['menit' => '601'], 'g_alasan' => ['alasan' => '  '], 'g_asing' => ['mahasiswa' => (string) $d['mhsB']['id']]] as $pesan => $ganti) {
            $this->assertStringEndsWith('?pesan=' . $pesan, (string) $this->kirim($ajar, $ini, $ganti + $isiA + ['yakin' => '1'])->header('Location'), $pesan);
        }
        $this->assertSame(404, $this->kirim($this->sebagai($d['pengajarB']), $ini, $isiA + ['yakin' => '1'])->status);
        $this->assertSame($sebelum, $this->sidikBank());

        // Konfirmasi menyebut mahasiswa, menit dan alasan; belum mengubah apa pun.
        $r = $this->kirim($ajar, $ini, $isiA);
        $this->assertSame(200, $r->status);
        foreach (['Mahasiswa Ani', '30', 'Laptop mati di tengah ujian'] as $teks) {
            $this->assertStringContainsString($teks, $r->body);
        }
        $this->assertSame($sebelum, $this->sidikBank());

        // Dibuka ulang: pengerjaan yang SAMA, jawaban tetap, tenggat = sekarang + 30 menit, urutan tetap.
        $this->assertStringEndsWith('?pesan=dibuka_ulang', (string) $this->kirim($ajar, $ini, $isiA + ['yakin' => '1'])->header('Location'));
        $lagi = $this->pengerjaan($d['sesi'], (string) $d['mhsA']['id']);
        $this->assertSame([(string) $a, null, null, null, '30', $baris['urutan']], [(string) $lagi['id'], $lagi['submitted_at'], $lagi['submit_how'], $lagi['score'], (string) $lagi['reopened_minutes'], $lagi['urutan']]);
        $this->assertEqualsWithDelta(1800, $this->tenggat($a), 3);
        $k = $ani->get("/api/sesi/{$d['sesi']}/keadaan")->json()['pengerjaan'];
        $this->assertSame([true, false, false, false], [$k['berjalan'], $k['dikumpulkan'], $k['diakhiri'], $k['tanpa_akhir']]);
        $halaman = $ani->get("/ujian-saya/{$d['sesi']}/kerjakan");
        $this->assertSame(200, $halaman->status);
        $this->assertStringContainsString('jawaban sebelum laptop mati', $halaman->body);
        preg_match('/data-halaman="([0-9a-f]{32})"/', $halaman->body, $m);
        $this->assertSame(200, $this->simpan($ani, $d['sesi'], $m[1], $d['q']['PG'], (int) $this->db->query("SELECT id FROM exam_options WHERE exam_question_id = {$d['q']['PG']} AND correct = 1")->fetch_assoc()['id'], 1)->status);
        $audit = json_decode((string) $this->db->query("SELECT detail FROM audit_log WHERE action = 'pengerjaan.dibuka_ulang'")->fetch_assoc()['detail'], true);
        $this->assertSame([(string) $d['mhsA']['id'], 30, 'Laptop mati di tengah ujian', true], [$audit['mahasiswa'], $audit['menit'], $audit['alasan'], $audit['sudah_mulai']]);
        // Sesinya sendiri tidak berubah bagi yang lain: Citra tetap tidak bisa mulai.
        $this->assertStringEndsWith('?pesan=belum', (string) $this->kirim($cit, "/ujian-saya/{$d['sesi']}/mulai")->header('Location'));
        $this->assertSame('selesai', $this->langsung($ajar, $d['sesi'])['_']['keadaan']);

        // Tambahan waktu selama buka ulang memajukan tenggat buka ulang itu.
        $this->kirim($ajar, "/sesi/{$d['sesi']}/tambah-waktu", ['mahasiswa' => [(string) $d['mhsA']['id']], 'menit' => '10', 'yakin' => '1']);
        $this->assertEqualsWithDelta(2400, $this->tenggat($a), 3);

        // Waktu buka ulang habis: ditandai waktu_habis dan dinilai ulang penuh (BS + PG = 2 poin).
        $this->db->query("UPDATE attempts SET reopen_until = UTC_TIMESTAMP() - INTERVAL 1 MINUTE WHERE id = {$a}");
        $this->assertSame(409, $this->simpan($ani, $d['sesi'], $m[1], $d['q']['ISIAN'], 'sesudah habis', 1)->status);
        $this->spark(['ujian:jadwal', '--sekali']);
        $akhir = $this->pengerjaan($d['sesi'], (string) $d['mhsA']['id']);
        $this->assertSame(['waktu_habis', '2.00'], [$akhir['submit_how'], $akhir['score']]);

        // Citra, yang tidak pernah mulai: dibuka ulang 20 menit sesudah sesi lewat → boleh mulai,
        // tenggatnya saat buka ulang + 20 menit.
        $this->assertStringEndsWith('?pesan=dibuka_ulang', (string) $this->kirim($ajar, $ini, ['mahasiswa' => (string) $d['mhsC']['id'], 'menit' => '20', 'alasan' => 'Listrik padam', 'yakin' => '1'])->header('Location'));
        $this->assertTrue($cit->get("/api/sesi/{$d['sesi']}/keadaan")->json()['menerima']);
        $c = (int) $this->mulaiKerja($cit, $d['sesi'], (string) $d['mhsC']['id'])['id'];
        $this->assertEqualsWithDelta(1200, $this->tenggat($c), 5);
        $this->assertSame(200, $this->simpan($cit, $d['sesi'], $this->bukaHalamanUjian($cit, $d['sesi']), $d['q']['ISIAN'], 'akhirnya', 1)->status);
        // Mahasiswa B (kelas lain) tetap tidak menemukan sesi itu.
        $this->assertSame(404, $this->sebagai($d['mhsB'])->get("/ujian-saya/{$d['sesi']}")->status);
    }
}
