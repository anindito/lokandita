<?php

declare(strict_types=1);

namespace Tests\Latihan;

use App\Libraries\Penilaian;
use App\Libraries\Ruang;
use Lokandita\Auth\Testing\Http;
use Tests\Support\KodeCase;

/**
 * Kebijakan terlambat dan latihan sesudah tutup (SPEC.md §6, §7; KONTRAK.md §6),
 * dari form pengajar sampai nilai di basis data. Kiriman di sini dinilai judge
 * worker sungguhan.
 */
final class TelatTest extends KodeCase
{
    private const BENAR = "#include <iostream>\nint main(){long a,b;std::cin>>a>>b;std::cout<<a+b<<\"\\n\";}\n";

    private const LEWAT = ['override' => 'none', 'open_at' => '2020-01-01 00:00:00', 'close_at' => '2020-01-02 00:00:00'];

    /**
     * Dunia dengan satu soal MUDAH (5 poin) yang jendelanya SUDAH tutup menurut jadwal.
     *
     * @param array<string, mixed> $jendela
     * @param array<string, mixed> $pertemuan
     *
     * @return array<string, mixed>
     */
    private function duniaTutup(array $jendela = [], array $pertemuan = []): array
    {
        $d = $this->dunia();
        $d['soal'] = $this->soalSiap($d['pertemuanX1'], 'jumlah-dua-bilangan', 'MUDAH');
        $this->terbit($d['pertemuanX1'], $pertemuan);
        $d['jendela'] = $this->jendela($d['pertemuanX1'], $d['kelasXA'], $jendela + self::LEWAT);
        $d['instans'] = $this->instans($d['mhsA'], $d['soal']);
        $d['api']     = '/api/soal/' . $d['soal']['mp'];
        $d['hal']     = '/latihan/' . $d['pertemuanX1'];
        $d['halSoal'] = '/latihan/soal/' . $d['soal']['mp'];
        $this->lupakanLaju();

        return $d;
    }

    /**
     * @return array<string, mixed>
     */
    private function kirimDanTunggu(Http $http, string $api, string $csrf): array
    {
        $this->lupakanLaju();
        $r = $http->post($api . '/kirim', ['csrf' => $csrf, 'source' => self::BENAR]);
        $this->assertSame(202, $r->status, $r->body);
        $id    = $r->json()['id'];
        $batas = microtime(true) + 120;
        while (microtime(true) < $batas) {
            $data = $http->get('/api/executions/' . $id)->json();
            if (in_array($data['status'], ['DONE', 'FAILED'], true)) {
                return $data;
            }
            usleep(300_000);
        }
        $this->fail('kiriman tidak selesai dinilai dalam 120 detik');
    }

    /**
     * @return array<string, string>|null
     */
    private function nilaiSoal(array $d): ?array
    {
        return $this->row("SELECT best_score, attempts FROM problem_scores WHERE user_id = '{$d['mhsA']['id']}' AND meeting_problem_id = {$d['soal']['mp']}");
    }

    public function testJenisKirimanDitentukanJendelaPendaftaranDanTahun(): void
    {
        $dasar = [
            'override' => 'none', 'open_at' => '2026-11-20 01:00:00', 'close_at' => '2026-11-20 03:00:00',
            'late_policy' => 'reject', 'late_factor' => '0.50', 'late_until' => null, 'practice_after_close' => 0,
            'post_close_visibility' => 'hidden', 'status_daftar' => 'AKTIF', 'tahun_status' => 'active',
        ];
        $sebelum = '2026-11-20 00:59:59';
        $selama  = '2026-11-20 02:00:00';
        $sesudah = '2026-11-20 03:00:00';   // tepat pada waktu tutup: sudah tutup
        $telat   = ['late_policy' => 'multiplier'];
        $batas   = $telat + ['late_until' => '2026-11-20 05:00:00'];

        foreach ([
            'belum buka'                                => ['', [], $sebelum],
            'terbuka'                                   => ['biasa', [], $selama],
            'tutup, ditolak'                            => ['', [], $sesudah],
            'tutup, multiplier tanpa batas'             => ['telat', $telat, '2030-01-01 00:00:00'],
            'tutup, multiplier, sebelum batas'          => ['telat', $batas, '2026-11-20 04:59:59'],
            'tutup, multiplier, tepat pada batas'       => ['', $batas, '2026-11-20 05:00:00'],
            'batas lewat, latihan diizinkan'            => ['latihan', $batas + ['practice_after_close' => 1], '2026-11-20 05:00:00'],
            'telat mendahului latihan'                  => ['telat', $batas + ['practice_after_close' => 1], '2026-11-20 04:00:00'],
            'tutup, latihan diizinkan'                  => ['latihan', ['practice_after_close' => 1], $sesudah],
            'ditutup manual mengakhiri masa terlambat'  => ['', $telat + ['override' => 'closed'], $selama],
            'ditutup manual, latihan diizinkan'         => ['latihan', $telat + ['override' => 'closed', 'practice_after_close' => 1], $selama],
            'dibuka manual sesudah tutup bukan telat'   => ['biasa', $telat + ['override' => 'open'], $sesudah],
            'multiplier tidak berlaku sebelum tutup'    => ['biasa', $telat, $selama],
            'LULUS tidak mengirim, juga saat telat'     => ['', $telat + ['status_daftar' => 'LULUS'], $sesudah],
            'LULUS tidak berlatih'                      => ['', ['practice_after_close' => 1, 'status_daftar' => 'LULUS'], $sesudah],
            'tahun diarsipkan tidak menerima telat'     => ['', $telat + ['tahun_status' => 'archived'], $sesudah],
            'tahun diarsipkan tidak menerima latihan'   => ['', ['practice_after_close' => 1, 'tahun_status' => 'archived'], $sesudah],
            'tahun diarsipkan, jendela terbuka'         => ['', ['tahun_status' => 'archived'], $selama],
        ] as $nama => [$harap, $ubah, $kini]) {
            $this->assertSame($harap, Ruang::jenis($ubah + $dasar, $kini), $nama);
        }
    }

    public function testPengajarMenyimpanKebijakanTerlambatDanIsianSalahDitolak(): void
    {
        $d    = $this->dunia();
        $http = $this->sebagai($d['pengajarA']);
        $csrf = $this->csrf($http);
        $url  = '/kelas/' . $d['kelasXA'] . '/pertemuan/' . $d['pertemuanX1'];
        $jadwal = ['csrf' => $csrf, 'open_at' => '2030-11-20T08:00', 'close_at' => '2030-11-20T10:00', 'post_close_visibility' => 'hidden'];
        $baris  = fn () => $this->row("SELECT late_policy, late_factor, late_until FROM meeting_class_windows WHERE meeting_id = {$d['pertemuanX1']} AND class_id = {$d['kelasXA']}");

        foreach ([
            [['late_policy' => 'kadang'], 'Pilih salah satu dari dua kebijakan.'],
            [['late_policy' => 'multiplier', 'late_factor' => '1.5'], 'Faktor harus angka dari 0 sampai 1'],
            [['late_policy' => 'multiplier', 'late_factor' => 'setengah'], 'Faktor harus angka dari 0 sampai 1'],
            [['late_policy' => 'multiplier', 'late_factor' => '-0.5'], 'Faktor harus angka dari 0 sampai 1'],
            [['late_policy' => 'multiplier', 'late_factor' => '0.5', 'late_until' => '2030-11-20T10:00'], 'Batas akhir terlambat harus sesudah waktu tutup'],
            [['late_policy' => 'multiplier', 'late_factor' => '0.5', 'late_until' => '2030-11-20T09:00'], 'Batas akhir terlambat harus sesudah waktu tutup'],
            [['late_policy' => 'multiplier', 'late_factor' => '0.5', 'late_until' => 'lusa'], 'Waktu tidak dikenali.'],
            [['late_policy' => 'multiplier', 'late_factor' => '0.5', 'late_until' => '2030-11-21T10:00', 'open_at' => '', 'close_at' => ''], 'Batas akhir terlambat harus sesudah waktu tutup'],
        ] as [$isi, $pesan]) {
            $r = $http->post($url, $isi + $jadwal);
            $this->assertSame(422, $r->status, $pesan);
            $this->assertStringContainsString($pesan, $r->body);
        }
        $this->assertSame(0, $this->hitung('SELECT COUNT(*) FROM meeting_class_windows'));

        // Sah: koma desimal diterima; batas akhir disimpan dalam UTC (12:00 WIB = 05:00 UTC).
        $r = $http->post($url, ['late_policy' => 'multiplier', 'late_factor' => '0,75', 'late_until' => '2030-11-20T12:00'] + $jadwal);
        $this->assertSame(303, $r->status, $r->body);
        $this->assertSame(['late_policy' => 'multiplier', 'late_factor' => '0.75', 'late_until' => '2030-11-20 05:00:00'], $baris());
        $audit = json_decode((string) $this->row("SELECT detail FROM audit_log WHERE action = 'jendela.disimpan' ORDER BY id DESC LIMIT 1")['detail'], true);
        $this->assertSame(['multiplier', 0.75, '2030-11-20 05:00:00'], [$audit['sesudah']['late_policy'], $audit['sesudah']['late_factor'], $audit['sesudah']['late_until']]);

        $hal = $http->get($url)->body;
        $this->assertStringContainsString('value="0.75"', $hal);
        $this->assertStringContainsString('value="2030-11-20T12:00"', $hal);
        $this->assertMatchesRegularExpression('/name="late_policy" value="multiplier" checked/', $hal);

        // Permintaan tanpa isian kebijakan (form lama, skrip) tidak mengubahnya.
        $this->assertSame(303, $http->post($url, $jadwal)->status);
        $this->assertSame(['late_policy' => 'multiplier', 'late_factor' => '0.75', 'late_until' => '2030-11-20 05:00:00'], $baris());

        // Kembali ke "ditolak": batas akhir dihapus, faktor dibiarkan.
        $this->assertSame(303, $http->post($url, ['late_policy' => 'reject', 'late_factor' => 'apa saja', 'late_until' => '2030-11-20T12:00'] + $jadwal)->status);
        $this->assertSame(['late_policy' => 'reject', 'late_factor' => '0.75', 'late_until' => null], $baris());

        // Halaman pengajar tidak mengatakan "tidak bisa lagi mengirim" selama terlambat atau latihan masih diterima.
        $atur = fn (string $set) => $this->db->query("UPDATE meeting_class_windows SET {$set} WHERE meeting_id = {$d['pertemuanX1']} AND class_id = {$d['kelasXA']}");
        $atur("open_at = '2020-01-01 00:00:00', close_at = '2020-01-02 00:00:00'");
        $this->assertStringContainsString('data-keadaan-info="tutup"', $http->get($url)->body);
        $atur("late_policy = 'multiplier', late_until = NULL");
        $hal = $http->get($url)->body;
        $this->assertStringContainsString('data-keadaan-info="telat"', $hal);
        $this->assertStringContainsString('masih diterima sebagai terlambat', $hal);
        $atur("late_until = '2020-01-03 00:00:00'");
        $this->assertStringContainsString('data-keadaan-info="tutup"', $http->get($url)->body);
        $this->db->query("UPDATE meetings SET practice_after_close = 1 WHERE id = {$d['pertemuanX1']}");
        $this->assertStringContainsString('data-keadaan-info="latihan"', $http->get($url)->body);
        $this->db->query("UPDATE meetings SET practice_after_close = 0 WHERE id = {$d['pertemuanX1']}");
        $atur("late_policy = 'reject', late_until = NULL, open_at = '2030-11-20 01:00:00', close_at = '2030-11-20 03:00:00'");

        // Pengajar kelas lain tidak bisa mengubahnya, dan tidak ada yang berubah.
        $lain = $this->sebagai($d['pengajarB']);
        $r    = $lain->post($url, ['late_policy' => 'multiplier', 'late_factor' => '1', 'csrf' => $this->csrf($lain)] + $jadwal);
        $this->assertSame(404, $r->status);
        $this->assertSame('reject', $baris()['late_policy']);
    }

    public function testPengampuMengaturLatihanSesudahTutupDanPapanAnonim(): void
    {
        $d    = $this->dunia();
        $http = $this->sebagai($d['pengampuX']);
        $csrf = $this->csrf($http);
        $url  = '/pertemuan/' . $d['pertemuanX1'];
        $isi  = ['csrf' => $csrf, 'target_points' => '60', 'min_mudah_sekali' => '8', 'min_mudah' => '8', 'min_mudah_sedang' => '6',
            'min_sedang' => '6', 'min_sulit' => '4', 'starter_template' => '', 'compiler_flags' => '', 'autocomplete' => '1'];
        $baris = fn () => $this->row("SELECT practice_after_close, anonymize_leaderboard FROM meetings WHERE id = {$d['pertemuanX1']}");

        $this->assertSame(303, $http->post($url . '/latihan', $isi + ['practice_after_close' => '1', 'anonymize_leaderboard' => '1'])->status);
        $this->assertSame(['practice_after_close' => '1', 'anonymize_leaderboard' => '1'], $baris());
        $hal = $http->get($url)->body;
        $this->assertMatchesRegularExpression('/name="practice_after_close" value="1" checked/', $hal);
        $this->assertMatchesRegularExpression('/name="anonymize_leaderboard" value="1" checked/', $hal);

        // Kotak yang tidak dicentang berarti mati.
        $this->assertSame(303, $http->post($url . '/latihan', $isi)->status);
        $this->assertSame(['practice_after_close' => '0', 'anonymize_leaderboard' => '0'], $baris());

        // Pengajar yang bukan pengampu tidak boleh.
        $lain = $this->sebagai($d['pengajarA']);
        $this->assertSame(403, $lain->post($url . '/latihan', ['csrf' => $this->csrf($lain), 'practice_after_close' => '1'] + $isi)->status);
        $this->assertSame('0', $baris()['practice_after_close']);
    }

    public function testKirimanTerlambatDikaliFaktorSaatDibuatDanTidakMenurunkanNilaiTerbaik(): void
    {
        $d    = $this->duniaTutup(['late_policy' => 'multiplier', 'late_factor' => '0.50', 'post_close_visibility' => 'review_only']);
        $http = $this->sebagai($d['mhsA']);
        $csrf = $this->csrf($http);
        $atur = fn (string $set) => $this->db->query("UPDATE meeting_class_windows SET {$set} WHERE id = {$d['jendela']}");

        // Tepat waktu (disisipkan): 4 dari 10 tes, nilai 2 dari 5.
        $this->kirimanDinilai($d['mhsA'], $d['soal'], $d['instans'], 4);
        $this->assertSame(['best_score' => '2.00', 'attempts' => '1'], $this->nilaiSoal($d));

        // Halaman mengatakan dengan jelas bahwa kiriman sekarang terlambat, dan faktornya.
        foreach ([$d['hal'], $d['halSoal']] as $alamat) {
            $r = $http->get($alamat);
            $this->assertSame(200, $r->status);
            $this->assertStringContainsString('data-jenis-kerja="telat"', $r->body);
            $this->assertStringContainsString('nilai kiriman sekarang dikali 0.5', $r->body);
            $this->assertStringContainsString('sampai pengajar menutup pertemuan ini', $r->body);
        }
        $this->assertStringContainsString('data-aksi="kirim"', $http->get($d['halSoal'])->body);

        // Terlambat dan benar semua: 5 × 0,5 = 2,5. Lebih baik daripada 2, jadi nilai terbaik naik.
        $data = $this->kirimDanTunggu($http, $d['api'], $csrf);
        $k    = $data['submission'];
        $this->assertSame(['DINILAI', 'AC', 10, true, 0.5, false], [$k['status'], $k['verdict'], $k['passed'], $k['late'], $k['late_factor'], $k['practice']]);
        $this->assertEqualsWithDelta(2.5, $k['score'], 0.001);
        $baris = $this->row("SELECT late, late_factor, practice, score FROM submissions WHERE id = {$k['id']}");
        $this->assertSame(['late' => '1', 'late_factor' => '0.50', 'practice' => '0', 'score' => '2.50'], $baris);
        $this->assertSame(['best_score' => '2.50', 'attempts' => '2'], $this->nilaiSoal($d));
        $this->assertEqualsWithDelta(2.5, $data['meeting']['terhitung'], 0.001);
        $riwayat = $http->get($d['api'] . '/riwayat')->json()['submissions'];
        $this->assertSame([true, false], [$riwayat[0]['late'], $riwayat[1]['late']]);

        // Pengajar menurunkan faktor: kiriman yang SUDAH ada tidak berubah, juga sesudah dihitung ulang.
        $atur("late_factor = '0.10'");
        Penilaian::hitungUlangPertemuan($d['pertemuanX1']);
        $this->assertSame(['best_score' => '2.50', 'attempts' => '2'], $this->nilaiSoal($d));
        $this->assertSame('2.50', $this->row("SELECT score FROM submissions WHERE id = {$k['id']}")['score']);

        // Kiriman berikutnya memakai faktor baru (0,5) dan tidak menurunkan nilai terbaik.
        $data = $this->kirimDanTunggu($http, $d['api'], $csrf);
        $this->assertEqualsWithDelta(0.5, $data['submission']['score'], 0.001);
        $this->assertSame(0.1, $data['submission']['late_factor']);
        $this->assertSame(['best_score' => '2.50', 'attempts' => '3'], $this->nilaiSoal($d));

        // Batas akhir terlambat lewat: tidak bisa mengirim lagi.
        $atur("late_until = '2020-01-03 00:00:00'");
        $this->lupakanLaju();
        $r = $http->post($d['api'] . '/kirim', ['csrf' => $csrf, 'source' => self::BENAR]);
        $this->assertSame([403, 'tertutup'], [$r->status, $r->json()['error']]);
        $this->assertStringNotContainsString('data-jenis-kerja', $http->get($d['hal'])->body);

        // Ditutup manual: masa terlambat berakhir walaupun batasnya belum lewat.
        $atur("late_until = NULL, override = 'closed'");
        $this->assertSame(403, $http->post($d['api'] . '/kirim', ['csrf' => $csrf, 'source' => self::BENAR])->status);

        // Dibuka manual: kiriman biasa, nilai penuh.
        $atur("override = 'open'");
        $data = $this->kirimDanTunggu($http, $d['api'], $csrf);
        $this->assertSame([false, null], [$data['submission']['late'], $data['submission']['late_factor']]);
        $this->assertEqualsWithDelta(5.0, $data['submission']['score'], 0.001);
        $this->assertSame(['best_score' => '5.00', 'attempts' => '4'], $this->nilaiSoal($d));
        $this->assertSame(4, $this->hitung('SELECT COUNT(*) FROM submissions'), 'permintaan yang ditolak tidak membuat kiriman');
    }

    public function testKebijakanDitolakTetapMenolakSesudahTutup(): void
    {
        $d    = $this->duniaTutup(['post_close_visibility' => 'review_only']);
        $http = $this->sebagai($d['mhsA']);
        $r    = $http->post($d['api'] . '/kirim', ['csrf' => $this->csrf($http), 'source' => self::BENAR]);
        $this->assertSame([403, 'tertutup'], [$r->status, $r->json()['error']]);
        $this->assertSame(0, $this->hitung('SELECT COUNT(*) FROM submissions'));
    }

    public function testLatihanSesudahTutupDinilaiTetapiTidakPernahDihitung(): void
    {
        // Kelas memilih "hanya melihat nilainya": tanpa izin latihan, soal tidak terlihat.
        $d    = $this->duniaTutup(['override' => 'closed']);
        $http = $this->sebagai($d['mhsA']);
        $csrf = $this->csrf($http);
        $this->kirimanDinilai($d['mhsA'], $d['soal'], $d['instans'], 4);
        $nilaiSebelum = $this->row("SELECT * FROM meeting_scores WHERE user_id = '{$d['mhsA']['id']}'");
        $this->assertSame(404, $http->get($d['halSoal'])->status);
        $this->assertSame(404, $http->post($d['api'] . '/kirim', ['csrf' => $csrf, 'source' => self::BENAR])->status);

        // Pengampu mengizinkan latihan sesudah tutup: soal terlihat lagi, dengan penjelasan.
        $this->db->query("UPDATE meetings SET practice_after_close = 1 WHERE id = {$d['pertemuanX1']}");
        foreach ([$d['hal'], $d['halSoal']] as $alamat) {
            $r = $http->get($alamat);
            $this->assertSame(200, $r->status);
            $this->assertStringContainsString('data-jenis-kerja="latihan"', $r->body);
            $this->assertStringContainsString('hasilnya tidak mengubah nilai Anda', $r->body);
        }

        // Benar semua, dinilai judge sungguhan: hasilnya ditampilkan, ditandai latihan …
        $data = $this->kirimDanTunggu($http, $d['api'], $csrf);
        $k    = $data['submission'];
        $this->assertSame(['DINILAI', 'AC', 10, 10, true, false], [$k['status'], $k['verdict'], $k['passed'], $k['total'], $k['practice'], $k['late']]);
        $this->assertSame(['practice' => '1', 'late' => '0', 'late_factor' => null], $this->row("SELECT practice, late, late_factor FROM submissions WHERE id = {$k['id']}"));
        $this->assertTrue($http->get($d['api'] . '/riwayat')->json()['submissions'][0]['practice']);

        // … tetapi nilai soal, jumlah percobaan dan nilai pertemuan tidak berubah sedikit pun.
        $this->assertSame(['best_score' => '2.00', 'attempts' => '1'], $this->nilaiSoal($d));
        $this->assertEqualsWithDelta(2.0, $data['meeting']['terhitung'], 0.001);
        $this->assertSame(1, $data['meeting']['kiriman']);
        Penilaian::hitungUlangPertemuan($d['pertemuanX1']);
        $sesudah = $this->row("SELECT * FROM meeting_scores WHERE user_id = '{$d['mhsA']['id']}'");
        unset($nilaiSebelum['updated_at'], $sesudah['updated_at']);
        $this->assertSame($nilaiSebelum, $sesudah);

        // Mahasiswa yang HANYA punya kiriman latihan tidak punya nilai sama sekali.
        $baru = $this->user('mahasiswa', 'Mahasiswa Citra');
        $this->daftar($baru, $d['kelasXA']);
        $this->instans($baru, $d['soal']);
        $lain = $this->sebagai($baru);
        $data = $this->kirimDanTunggu($lain, $d['api'], $this->csrf($lain));
        $this->assertSame(['AC', true], [$data['submission']['verdict'], $data['submission']['practice']]);
        $this->assertSame(0, $this->hitung("SELECT COUNT(*) FROM problem_scores WHERE user_id = '{$baru['id']}'"));
        $this->assertSame(0, $this->hitung("SELECT COUNT(*) FROM meeting_scores WHERE user_id = '{$baru['id']}'"));
        $this->assertSame(0, $data['meeting']['kiriman']);

        // Yang sudah LULUS tidak berlatih: membaca saja pun tidak, karena kelasnya memilih "hanya nilai".
        $this->roster->query("UPDATE enrollments SET status = 'LULUS', graduated_on = '2026-11-01' WHERE id = {$d['daftarA']}");
        $this->assertSame(404, $http->post($d['api'] . '/kirim', ['csrf' => $csrf, 'source' => self::BENAR])->status);
        $this->assertSame(2, $this->hitung('SELECT COUNT(*) FROM submissions WHERE practice = 1'));
    }
}
