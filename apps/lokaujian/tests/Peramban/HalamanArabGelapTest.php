<?php

declare(strict_types=1);

namespace Tests\Peramban;

use Lokandita\Auth\Testing\Fixtures;
use Lokandita\Auth\Testing\Peramban;
use Tests\Support\UjianCase;

/**
 * Halaman dosen, superadmin dan hasil mahasiswa dalam bahasa Arab (kanan-ke-kiri) dan mode gelap,
 * di peramban sungguhan (tahap U6, SPEC-LOKAUJIAN.md §15: "tinjauan bahasa Arab dan mode gelap
 * oleh orang"). Halaman ujian sendiri diukur HalamanUjianTest.
 *
 * Yang DIUKUR di sini hanya tata letak: arah halaman, tidak ada yang meluber ke samping, tidak
 * ada galat skrip dan tidak ada pelanggaran CSP. Yang TIDAK bisa diukur: apakah teks Arabnya
 * benar dan wajar, dan apakah warnanya enak dibaca. Untuk itu, dengan TANGKAPAN=<direktori>
 * (infra/tes.sh) setiap halaman ditulis sebagai tangkapan layar untuk dilihat orang.
 *
 * Tes ini GAGAL, tidak dilewati, bila peramban tidak ada.
 */
final class HalamanArabGelapTest extends UjianCase
{
    private const ASAL = 'https://lokaujian.lokandita.com';

    /** bahasa, skema warna, lebar layar */
    private const RAGAM = [['ar', 'light', 1280], ['ar', 'dark', 1280], ['id', 'dark', 1280], ['ar', 'dark', 390]];

    /**
     * @param array<string, mixed>   $pengguna
     * @param array<string, string> $halaman nama berkas => jalur
     */
    private function lihat(array $pengguna, array $halaman): void
    {
        foreach (self::RAGAM as [$bahasa, $skema, $lebar]) {
            $this->idn->query("UPDATE users SET locale = '{$bahasa}' WHERE id = '{$pengguna['id']}'");
            $p = new Peramban(self::ASAL);
            $p->ukuran($lebar, 900);
            $p->perintah('Emulation.setEmulatedMedia', ['features' => [['name' => 'prefers-color-scheme', 'value' => $skema]]]);
            $p->kuki('lokandita_sid', Fixtures::session($this->idn, (string) $pengguna['id']));
            foreach ($halaman as $nama => $jalur) {
                $apa = "{$nama} {$bahasa}/{$skema}/{$lebar}";
                $this->assertSame(200, $p->buka($jalur), $apa);
                $this->assertSame($bahasa === 'ar' ? 'rtl' : 'ltr', $p->js('document.documentElement.dir'), $apa);
                $this->assertSame($bahasa, $p->js('document.documentElement.lang'), $apa);
                $this->assertLessThanOrEqual(1, $p->js('document.documentElement.scrollWidth - document.documentElement.clientWidth'), "{$apa}: halaman meluber ke samping");
                $this->assertSame([], $p->js('window.__csp'), "{$apa}: pelanggaran CSP");
                $this->assertSame([], $p->js('window.__galat'), "{$apa}: galat skrip");
                $dir = (string) getenv('TANGKAPAN_DIR');
                if ($dir !== '') {
                    $tinggi = (int) min(6000, max(900, (int) $p->js('document.documentElement.scrollHeight')));
                    $p->ukuran($lebar, $tinggi);
                    file_put_contents("{$dir}/{$nama}-{$bahasa}-{$skema}-{$lebar}.png", $p->tangkap());
                    $p->ukuran($lebar, 900);
                }
            }
            unset($p);
        }
        $this->idn->query("UPDATE users SET locale = 'id' WHERE id = '{$pengguna['id']}'");
    }

    public function testHalamanDosenSuperadminDanHasilMahasiswa(): void
    {
        $d    = $this->duniaSesi();
        $s    = $d['sesi'];
        $m    = $d['matkulX'];
        $u    = $d['ujian'];
        $ani  = $this->sebagai($d['mhsA']);
        $this->mulaiKerja($ani, $s, (string) $d['mhsA']['id']);
        $t = $this->bukaHalamanUjian($ani, $s);
        $this->simpan($ani, $s, $t, $d['q']['BS'], 'benar', 1);
        $this->simpan($ani, $s, $t, $d['q']['ISIAN'], 'جاكرتا', 1);
        $this->simpan($ani, $s, $t, $d['q']['URAIAN'], "العودية هي دالة تستدعي نفسها.\nRekursi memanggil dirinya sendiri.", 1);
        $ani->postJson("/api/sesi/{$s}/peristiwa", ['jenis' => 'focus_lost', 'durasi' => 12], ['X-CSRF-TOKEN' => $this->csrf($ani)]);
        $this->mulaiKerja($this->sebagai($d['mhsC']), $s, (string) $d['mhsC']['id']);
        $this->kirim($d['dosen'], "/sesi/{$s}/tambah-waktu", ['mahasiswa' => [(string) $d['mhsC']['id']], 'menit' => '5', 'yakin' => '1']);
        $soal = (int) $this->db->query("SELECT id FROM questions WHERE text LIKE 'PG:%'")->fetch_assoc()['id'];

        // Selagi sesi berjalan: halaman-halaman pengelola dan tampilan langsung.
        $this->lihat($d['pengampuX'], [
            'staf-01-beranda'       => '/beranda',
            'staf-02-mata-kuliah'   => "/mata-kuliah/{$m}",
            'staf-03-kelas'         => "/kelas/{$d['kelasXA']}",
            'staf-04-bank'          => "/mata-kuliah/{$m}/bank",
            'staf-05-soal'          => "/mata-kuliah/{$m}/bank/soal/{$soal}",
            'staf-06-soal-sunting'  => "/mata-kuliah/{$m}/bank/soal/{$soal}/sunting",
            'staf-07-ujian-daftar'  => "/mata-kuliah/{$m}/ujian",
            'staf-08-ujian'         => "/mata-kuliah/{$m}/ujian/{$u}",
            'staf-09-seb'           => "/mata-kuliah/{$m}/ujian/{$u}/seb",
            'staf-10-ekspor'        => "/mata-kuliah/{$m}/ujian/{$u}/ekspor",
            'staf-11-sesi-berjalan' => "/sesi/{$s}",
            'staf-12-peristiwa'     => "/sesi/{$s}/peristiwa",
            'staf-13-koreksi'       => "/mata-kuliah/{$m}/ujian/{$u}/koreksi",
        ]);

        // Sesi berakhir, penjadwal menandai dan menilai, hasil dirilis beserta kunci.
        $this->db->query("UPDATE sessions SET end_at = UTC_TIMESTAMP() - INTERVAL 5 MINUTE WHERE id = {$s}");
        $this->spark(['ujian:jadwal', '--sekali']);
        $this->assertStringEndsWith('?pesan=dirilis', (string) $this->kirim($d['dosen'], "/sesi/{$s}/rilis", ['tingkat' => 'nilai_dan_kunci', 'yakin' => '1'])->header('Location'));

        $this->lihat($d['pengampuX'], ['staf-14-sesi-selesai' => "/sesi/{$s}"]);
        $this->lihat($d['superadmin'], ['admin-01-audit' => '/audit']);
        // Kontainer lokaujian-tes berjalan tanpa penegakan SEB: pita merah MODE UJI ikut terlihat
        // di tangkapan halaman mahasiswa. Di produksi pita itu tidak ada.
        $this->lihat($d['mhsA'], [
            'mhs-01-beranda'      => '/beranda',
            'mhs-02-tanda-terima' => "/ujian-saya/{$s}",
            'mhs-03-tinjauan'     => "/ujian-saya/{$s}/tinjau",
            'mhs-04-data-saya'    => '/data-saya',
        ]);
    }
}
