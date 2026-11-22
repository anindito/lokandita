<?php

declare(strict_types=1);

namespace Tests\Peramban;

use Lokandita\Auth\Testing\Fixtures;
use Tests\Support\KodeCase;
use Tests\Support\Peramban;

/**
 * Ketikan tak wajar (SPEC.md §13.2, KONTRAK.md §17) di PERAMBAN SUNGGUHAN: yang mengukur
 * adalah public/js/soal.js dari peristiwa perubahan Monaco, jadi hanya di sini bisa dibuktikan
 * bahwa teks yang masuk sekaligus dicatat, dan bahwa mengetik biasa, Reset dan pemulihan
 * draf TIDAK dicatat.
 *
 * Teks dimasukkan dengan Input.insertText (peristiwa masukan peramban, seperti alat yang
 * "mengetik" untuk pengguna) dan dengan tombol sungguhan — bukan dengan memanggil skrip halaman.
 */
final class KetikanTest extends KodeCase
{
    /**
     * @param array<string, int|string> $pertemuan pengaturan pertemuan yang diganti
     *
     * @return array<string, mixed>
     */
    private function duniaKerja(array $pertemuan = []): array
    {
        $d         = $this->dunia();
        $d['soal'] = $this->soalSiap($d['pertemuanX1'], 'jumlah-dua-bilangan');
        $this->terbit($d['pertemuanX1'], $pertemuan);
        $this->jendela($d['pertemuanX1'], $d['kelasXA']);
        $this->instans($d['mhsA'], $d['soal']);
        $d['jalur'] = '/latihan/soal/' . $d['soal']['mp'];
        $this->lupakanLaju();

        return $d;
    }

    /**
     * @param array<string, mixed> $d
     */
    private function bukaSoal(array $d): Peramban
    {
        $p = new Peramban();
        $p->kuki('lokandita_sid', Fixtures::session($this->idn, (string) $d['mhsA']['id']));
        $this->assertSame(200, $p->buka($d['jalur']));
        $p->tunggu("document.documentElement.dataset.editorSiap === '1'", 30, 'Monaco siap');
        $p->js('(function () { var e = window.lokakodeEditor; e.focus(); e.setPosition({ lineNumber: 1, column: 1 }); return true; })()');

        return $p;
    }

    /**
     * @return list<array<string, mixed>> rincian peristiwa ketikan tak wajar, menurut urutan kejadian
     */
    private function tercatat(): array
    {
        return array_map(
            static fn (array $b): array => json_decode((string) $b['detail'], true),
            $this->db->query("SELECT detail FROM integrity_events WHERE kind = 'typing_anomaly' ORDER BY id")->fetch_all(MYSQLI_ASSOC),
        );
    }

    public function testTeksYangMasukSekaligusDicatatDanUndoDitandai(): void
    {
        $d = $this->duniaKerja();
        $p = $this->bukaSoal($d);

        // 400 karakter dalam satu peristiwa masukan.
        $teks = str_repeat('int x = 12345; ', 26) . '//12345678';
        $this->assertSame(400, strlen($teks));
        $p->perintah('Input.insertText', ['text' => $teks]);
        $this->assertStringStartsWith('int x = 12345; int x', (string) $p->js('window.lokakodeEditor.getValue()'));
        $this->tungguSampai(fn () => count($this->tercatat()) === 1, 6, 'peristiwa ketikan tak wajar');
        $e = $this->row('SELECT * FROM integrity_events');
        $this->assertSame([$d['mhsA']['id'], 'typing_anomaly', (string) $d['soal']['mp']], [$e['user_id'], $e['kind'], $e['meeting_problem_id']]);
        $satu = $this->tercatat()[0];
        $this->assertSame([400, 'ketik'], [$satu['karakter'], $satu['sebab']]);
        $this->assertGreaterThanOrEqual(150, $satu['cps']);
        // Tidak ada pesan untuk mahasiswa: ini catatan bagi dosen, bukan pemblokiran.
        $this->assertSame(0, $p->js("document.querySelectorAll('[data-toast-soal] .toast').length"));

        // Hapus semuanya lalu urungkan: 400 karakter lebih kembali sekaligus, ditandai undo.
        usleep(2_200_000); // jendela ukur dua detik lewat; batas satu peristiwa per detik lewat
        $this->lupakanLaju();
        $p->js("(function () { var e = window.lokakodeEditor; e.executeEdits('uji', [{ range: e.getModel().getFullModelRange(), text: '' }]); e.pushUndoStop(); return true; })()");
        $this->assertSame('', (string) $p->js('window.lokakodeEditor.getValue()'));
        $p->tekan('z', 'KeyZ', 90, Peramban::CTRL, ['undo']);
        $p->tunggu('window.lokakodeEditor.getValue().length > 400', 5, 'undo mengembalikan teks');
        $this->tungguSampai(fn () => count($this->tercatat()) === 2, 6, 'peristiwa dari undo');
        $dua = $this->tercatat()[1];
        $this->assertSame('undo', $dua['sebab']);
        $this->assertGreaterThanOrEqual(400, $dua['karakter']);
        $this->assertSame([[], []], [$p->js('window.__csp'), $p->js('window.__galat')]);
    }

    public function testMengetikBiasaResetDanPemulihanDrafTidakDicatat(): void
    {
        // Templat pertemuan dan draf sama-sama lebih panjang dari 300 karakter.
        $templat = "#include <iostream>\nusing namespace std;\n\n" . str_repeat("// baris templat yang panjang untuk pertemuan ini\n", 8) . "int main() {\n    return 0;\n}\n";
        $this->assertGreaterThan(300, strlen($templat));
        $d    = $this->duniaKerja(['starter_template' => $templat]);
        $draf = str_repeat("// draf lama mahasiswa, disimpan kemarin\n", 10);
        Fixtures::insert($this->db, 'drafts', ['user_id' => $d['mhsA']['id'], 'meeting_problem_id' => $d['soal']['mp'], 'source' => $draf, 'updated_at' => gmdate('Y-m-d H:i:s')]);

        // Draf dipulihkan saat halaman dibuka.
        $p = $this->bukaSoal($d);
        $this->assertSame($draf, (string) $p->js('window.lokakodeEditor.getValue()'));

        // Mengetik 320 karakter dengan kecepatan manusia: 40 karakter, jeda, 40 karakter, …
        for ($i = 0; $i < 8; $i++) {
            $p->ketik(str_repeat('abcd ', 8));
            usleep(600_000);
        }
        $this->assertStringStartsWith(str_repeat('abcd ', 64), (string) $p->js('window.lokakodeEditor.getValue()'));

        // Reset: templat panjang masuk sekaligus, dipasang oleh halaman itu sendiri.
        $p->klik('[data-aksi="reset"]');
        $this->assertSame($templat, (string) $p->js('window.lokakodeEditor.getValue()'));

        usleep(1_500_000);
        $this->assertSame(0, $this->hitung('SELECT COUNT(*) FROM integrity_events'), 'tidak ada yang dicatat');
        $this->assertSame([[], []], [$p->js('window.__csp'), $p->js('window.__galat')]);
    }

    public function testPenggunaYangBolehMenempelTidakDiukur(): void
    {
        $d = $this->duniaKerja();
        $this->idn->query("UPDATE users SET paste_allowed = 1 WHERE id = '{$d['mhsA']['id']}'");
        $p = $this->bukaSoal($d);
        $p->perintah('Input.insertText', ['text' => str_repeat('x', 500)]);
        $this->assertStringStartsWith(str_repeat('x', 500), (string) $p->js('window.lokakodeEditor.getValue()'));
        usleep(1_500_000);
        $this->assertSame(0, $this->hitung('SELECT COUNT(*) FROM integrity_events'));
        // Halaman bahkan tidak mengirim permintaan itu.
        $this->assertFalse($p->js("performance.getEntriesByType('resource').some(function (r) { return r.name.indexOf('/api/integritas') > 0; })"));
    }
}
