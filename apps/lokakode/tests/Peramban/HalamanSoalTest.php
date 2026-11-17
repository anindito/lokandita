<?php

declare(strict_types=1);

namespace Tests\Peramban;

use Lokandita\Auth\Testing\Fixtures;
use Tests\Support\KodeCase;
use Tests\Support\Peramban;

/**
 * Halaman mahasiswa di PERAMBAN SUNGGUHAN (Chromium tanpa layar, dijalankan
 * infra/tes.sh): yang tidak bisa dibuktikan dengan permintaan HTTP saja —
 * Monaco di bawah CSP, simpan draf otomatis, Reset, blokir tempel di setiap
 * jalur, blokir salin dan cetak, tanda air, Run dan Submit dari tombolnya,
 * panel yang bisa diubah ukurannya.
 *
 * Tombol dan klik dikirim sebagai peristiwa masukan peramban (bukan peristiwa
 * buatan skrip), kecuali di tempat yang disebut jelas.
 */
final class HalamanSoalTest extends KodeCase
{
    private const BENAR = "#include <iostream>\nint main(){long a,b;std::cin>>a>>b;std::cout<<a+b<<\"\\n\";}\n";

    private const TEMPLAT = "#include <iostream>\nusing namespace std;\n\nint main() {\n\n    return 0;\n}\n";

    private const TEMPELAN = 'TEMPELAN-RAHASIA-123';

    /**
     * @return array<string, mixed>
     */
    private function duniaKerja(bool $denganInstans = true): array
    {
        $d         = $this->dunia();
        $d['ms']   = $this->soalSiap($d['pertemuanX1'], 'salam-pembuka', 'MUDAH_SEKALI');
        $d['soal'] = $this->soalSiap($d['pertemuanX1'], 'jumlah-dua-bilangan', 'MUDAH');
        $d['sulit'] = $this->soalSiap($d['pertemuanX1'], 'paling-sulit', 'SULIT');
        $this->terbit($d['pertemuanX1']);
        $this->jendela($d['pertemuanX1'], $d['kelasXA'], ['override' => 'none', 'open_at' => '2020-01-01 00:00:00', 'close_at' => '2099-01-01 03:00:00']);
        if ($denganInstans) {
            $this->instans($d['mhsA'], $d['soal']);
        }
        $d['jalur'] = '/latihan/soal/' . $d['soal']['mp'];
        $this->lupakanLaju();

        return $d;
    }

    /**
     * Peramban baru yang sudah masuk sebagai $mhs (sesi dibuat langsung di identity_test).
     *
     * @param array<string, mixed> $mhs
     */
    private function peramban(array $mhs): Peramban
    {
        $p = new Peramban();
        $p->kuki('lokandita_sid', Fixtures::session($this->idn, (string) $mhs['id']));

        return $p;
    }

    /**
     * Membuka halaman soal dan menunggu Monaco siap.
     *
     * @param array<string, mixed> $d
     */
    private function bukaSoal(array $d): Peramban
    {
        $p = $this->peramban($d['mhsA']);
        $this->assertSame(200, $p->buka($d['jalur']));
        $p->tunggu("document.documentElement.dataset.editorSiap === '1'", 30, 'Monaco siap');

        return $p;
    }

    private function isiEditor(Peramban $p): string
    {
        return (string) $p->js('window.lokakodeEditor.getValue()');
    }

    private function fokusEditor(Peramban $p, bool $diAkhir = false): void
    {
        $p->js('(function () { var e = window.lokakodeEditor; e.focus(); var m = e.getModel(); var b = ' . ($diAkhir ? 'm.getLineCount()' : '1') . ';'
            . ' e.setPosition({ lineNumber: b, column: ' . ($diAkhir ? 'm.getLineMaxColumn(b)' : '1') . ' }); return true; })()');
    }

    /**
     * @return list<string> jalur tempel yang tercatat sebagai peristiwa integritas, menurut urutan kejadian
     */
    private function jalurTercatat(): array
    {
        return array_map(
            static fn (array $b): string => (string) json_decode((string) $b['detail'], true)['jalur'],
            $this->db->query('SELECT detail FROM integrity_events ORDER BY id')->fetch_all(MYSQLI_ASSOC),
        );
    }

    private function tungguJalur(string $jalur): void
    {
        $this->tungguSampai(fn () => in_array($jalur, $this->jalurTercatat(), true), 6, "peristiwa integritas {$jalur}");
        // Skrip halaman dan server sama-sama hanya mencatat satu peristiwa per detik.
        usleep(1_150_000);
        $this->lupakanLaju();
    }

    public function testPerambanTesHanyaBisaMenjangkauLokakodeTes(): void
    {
        $p = new Peramban();
        foreach (['https://auth.lokandita.com/', 'https://lokandita.com/', 'https://example.com/'] as $url) {
            $this->assertStringContainsString('ERR_NAME_NOT_RESOLVED', $p->galatBuka($url), $url);
        }
        // Tanpa sesi, LokaKode tes mengalihkan ke LokaAuth — yang tidak terjangkau dari sini.
        $this->assertStringContainsString('ERR_NAME_NOT_RESOLVED', $p->galatBuka(Peramban::ASAL . '/beranda'));
    }

    public function testMonacoDimuatDariHostSendiriTanpaPelanggaranCsp(): void
    {
        $d = $this->duniaKerja();
        $p = $this->bukaSoal($d);

        $this->assertSame(self::TEMPLAT, $this->isiEditor($p), 'editor mulai dengan templat bahasa');
        $this->assertTrue($p->js("document.querySelectorAll('[data-editor] .monaco-editor').length === 1"));
        $this->assertTrue($p->js("document.querySelector('[data-editor-cadangan]').hidden"), 'kotak cadangan disembunyikan setelah Monaco ada');
        $this->assertSame('cpp', $p->js('window.lokakodeEditor.getModel().getLanguageId()'));
        // Pewarnaan sintaks C++ benar-benar berjalan: lebih dari satu kelas warna token di baris-baris kode.
        $p->tunggu("new Set(Array.prototype.map.call(document.querySelectorAll('.monaco-editor .view-line span span'), function (s) { return s.className; })).size >= 3", 15, 'pewarnaan sintaks');
        // Semua yang dimuat halaman berasal dari host sendiri.
        $asal = $p->js("Array.from(new Set(performance.getEntriesByType('resource').map(function (r) { return new URL(r.name).origin; })))");
        $this->assertSame([Peramban::ASAL], $asal);
        $this->assertTrue($p->js("performance.getEntriesByType('resource').some(function (r) { return r.name.indexOf('/monaco/vs/editor/editor.main.js') > 0; })"));

        usleep(1_500_000); // pekerja latar Monaco sempat mulai
        $this->assertSame([], $p->js('window.__csp'), 'tidak ada pelanggaran CSP di halaman soal');
        $this->assertSame([], $p->js('window.__galat'), 'tidak ada galat skrip di halaman soal');

        // Halaman pertemuan: tanpa pelanggaran CSP juga; saringan dan hitung mundur bekerja.
        $this->assertSame(200, $p->buka('/latihan/' . $d['pertemuanX1']));
        $this->assertSame(3, $p->js("document.querySelectorAll('.kartu-soal:not([hidden])').length"));
        $p->js("(function () { var s = document.querySelector('[data-saring=tingkat]'); s.value = 'SULIT'; s.dispatchEvent(new Event('change', { bubbles: true })); return true; })()");
        $this->assertSame(1, $p->js("document.querySelectorAll('.kartu-soal:not([hidden])').length"));
        $this->assertMatchesRegularExpression('/^\d+ j \d+ m$/', (string) $p->js("document.querySelector('[data-hitung-mundur]').textContent"));
        $this->assertSame([[], []], [$p->js('window.__csp'), $p->js('window.__galat')]);
    }

    public function testDrafTersimpanOtomatisDipulihkanSetelahTabDitutupDanResetMengembalikanTemplat(): void
    {
        $d = $this->duniaKerja();
        $p = $this->bukaSoal($d);

        $this->fokusEditor($p, true);
        $p->ketik('// ketikan uji 123');
        $this->assertStringEndsWith('// ketikan uji 123', $this->isiEditor($p));

        // Tanpa menekan apa pun: draf sampai ke server dalam 5 detik (diberi kelonggaran).
        $mulai = microtime(true);
        $this->tungguSampai(fn () => str_contains((string) ($this->row('SELECT source FROM drafts')['source'] ?? ''), 'ketikan uji 123'), 9, 'draf tersimpan otomatis');
        fwrite(STDERR, sprintf("\n[ukur] draf tersimpan otomatis %.1f detik setelah ketikan terakhir\n", microtime(true) - $mulai));
        $p->tunggu("document.querySelector('[data-status-draf]').textContent.indexOf('Draf tersimpan') === 0", 3);

        // Tab ditutup (peramban baru, sesi baru): draf dipulihkan.
        unset($p);
        $p = $this->bukaSoal($d);
        $this->assertStringEndsWith('// ketikan uji 123', $this->isiEditor($p));

        // Reset bertanya dulu, lalu mengembalikan templat; itu pun tersimpan.
        $p->klik('[data-aksi="reset"]');
        $this->assertSame(1, $p->js('window.__konfirmasi'));
        $this->assertSame(self::TEMPLAT, $this->isiEditor($p));
        $this->tungguSampai(fn () => $this->row('SELECT source FROM drafts')['source'] === self::TEMPLAT, 9, 'draf sesudah Reset');
        $this->assertSame([[], []], [$p->js('window.__csp'), $p->js('window.__galat')]);
    }

    public function testPenggunaYangDiizinkanBisaMenempelJadiJalurTempelDiTesIniSungguhan(): void
    {
        // Pembanding untuk tes berikutnya: dengan paste_allowed, urutan tombol yang SAMA benar-benar menempel.
        $d = $this->duniaKerja();
        $this->idn->query("UPDATE users SET paste_allowed = 1 WHERE id = '{$d['mhsA']['id']}'");
        $p = $this->bukaSoal($d);
        $this->assertSame(self::TEMPELAN, $p->papanKlip(self::TEMPELAN));

        $this->fokusEditor($p);
        $p->tekan('v', 'KeyV', 86, Peramban::CTRL, ['paste']);
        $p->tunggu('window.lokakodeEditor.getValue().indexOf(' . json_encode(self::TEMPELAN) . ') === 0', 5, 'Ctrl+V menempel');
        $this->fokusEditor($p);
        $p->tekan('Insert', 'Insert', 45, Peramban::SHIFT, ['paste']);
        $p->tunggu('window.lokakodeEditor.getValue().indexOf(' . json_encode(self::TEMPELAN . self::TEMPELAN) . ') === 0', 5, 'Shift+Insert menempel');
        $p->js("window.lokakodeEditor.trigger('keyboard', 'paste', { text: 'LEWAT-PERINTAH' }) || true");
        $this->assertStringContainsString('LEWAT-PERINTAH', $this->isiEditor($p));

        usleep(1_200_000);
        $this->assertSame(0, $this->hitung('SELECT COUNT(*) FROM integrity_events'));
        $this->assertSame(0, $p->js("document.querySelectorAll('[data-toast-soal] .toast').length"));
    }

    public function testTempelDiblokirDiSetiapJalurDanTercatatSebagaiPeristiwaIntegritas(): void
    {
        $d = $this->duniaKerja();
        $p = $this->bukaSoal($d);
        $this->assertSame(self::TEMPELAN, $p->papanKlip(self::TEMPELAN));
        $utuh = function (string $jalur) use ($p): void {
            usleep(400_000);
            $this->assertSame(self::TEMPLAT, $this->isiEditor($p), "isi editor berubah lewat jalur {$jalur}");
        };

        // 1. Ctrl+V (tombol sungguhan, dengan perintah tempel peramban).
        $this->fokusEditor($p);
        $p->tekan('v', 'KeyV', 86, Peramban::CTRL, ['paste']);
        $utuh('Ctrl+V');
        $this->assertStringContainsString('Menempel ke editor tidak diizinkan.', (string) $p->js("document.querySelector('[data-toast-soal] .toast').textContent"));
        $this->assertSame('alert', $p->js("document.querySelector('[data-toast-soal] .toast').getAttribute('role')"));
        $this->tungguJalur('keyboard');

        // 2. Shift+Insert.
        $this->fokusEditor($p);
        $p->tekan('Insert', 'Insert', 45, Peramban::SHIFT, ['paste']);
        $utuh('Shift+Insert');
        $this->tungguJalur('shift_insert');

        // 3. Peristiwa paste itu sendiri (menu Edit > Tempel, atau pilihan utama X11). Dibuat skrip, dengan isi papan klip.
        $this->fokusEditor($p);
        $dicegah = $p->js("(function () { var dt = new DataTransfer(); dt.setData('text/plain', " . json_encode(self::TEMPELAN) . ");"
            . " var e = new ClipboardEvent('paste', { clipboardData: dt, bubbles: true, cancelable: true }); document.activeElement.dispatchEvent(e); return e.defaultPrevented; })()");
        $this->assertTrue($dicegah);
        $utuh('peristiwa paste');
        $this->tungguJalur('paste');

        // 4. Seret dan lepas teks ke editor.
        $dicegah = $p->js("(function () { var dt = new DataTransfer(); dt.setData('text/plain', " . json_encode(self::TEMPELAN) . ");"
            . " var e = new DragEvent('drop', { dataTransfer: dt, bubbles: true, cancelable: true, clientX: 900, clientY: 200 });"
            . " document.querySelector('.monaco-editor .view-lines').dispatchEvent(e); return e.defaultPrevented; })()");
        $this->assertTrue($dicegah);
        $utuh('seret dan lepas');
        $this->tungguJalur('drop');

        // 5. Klik tengah (tombol tetikus sungguhan).
        $p->klik('.monaco-editor .view-lines', 'middle');
        $utuh('klik tengah');
        $this->tungguJalur('middle_click');

        // 6. Menu konteks: klik kanan sungguhan tidak membuka menu Monaco (yang punya butir Tempel).
        $p->klik('.monaco-editor .view-lines', 'right');
        usleep(300_000);
        $this->assertSame(0, $p->js("document.querySelectorAll('.monaco-menu, .context-view .monaco-scrollable-element').length"));
        $utuh('menu konteks');

        // 7. beforeinput jenis tempel (jalur IME atau peramban yang tidak mengirim peristiwa paste).
        $this->fokusEditor($p);
        $this->assertTrue($p->js("(function () { var e = new InputEvent('beforeinput', { inputType: 'insertFromPaste', data: 'X', bubbles: true, cancelable: true });"
            . ' document.activeElement.dispatchEvent(e); return e.defaultPrevented; })()'));
        $utuh('beforeinput');
        $this->tungguJalur('paste');

        // 8. Perintah tempel Monaco sendiri (palet perintah, API): tempelannya dibatalkan.
        $p->js("window.lokakodeEditor.trigger('keyboard', 'paste', { text: " . json_encode(self::TEMPELAN) . ' }) || true');
        $utuh('perintah tempel Monaco');

        // Sesudah semua itu: tempelan tidak pernah ada di editor, dan mengetik biasa tetap bisa.
        $this->assertStringNotContainsString('TEMPELAN', $this->isiEditor($p));
        $this->fokusEditor($p);
        $p->ketik('int x;');
        $this->assertStringStartsWith('int x;', $this->isiEditor($p));

        $jalur = $this->jalurTercatat();
        foreach (['keyboard', 'shift_insert', 'paste', 'drop', 'middle_click'] as $harus) {
            $this->assertContains($harus, $jalur);
        }
        $e = $this->row('SELECT user_id, kind, meeting_problem_id FROM integrity_events ORDER BY id LIMIT 1');
        $this->assertSame([$d['mhsA']['id'], 'paste_blocked', (string) $d['soal']['mp']], array_values($e));
        $this->assertSame([], $p->js('window.__galat'));
    }

    public function testSoalTidakBisaDisalinAtauDicetakDanTandaAirMenutupinya(): void
    {
        $d = $this->duniaKerja();
        $p = $this->bukaSoal($d);
        $p->js("document.addEventListener('contextmenu', function (e) { window.__menu = e.defaultPrevented; });"
            . " window.addEventListener('keydown', function (e) { window.__tombol = window.__tombol || {}; window.__tombol[e.key] = e.defaultPrevented; }); true");
        $this->assertSame('PENANDA-AWAL', $p->papanKlip('PENANDA-AWAL'));

        // Teks soal tidak bisa dipilih.
        $this->assertSame('none', $p->js("getComputedStyle(document.querySelector('[data-pernyataan]:not([hidden]) p')).userSelect"));
        // Pilihan dipaksakan lewat skrip, lalu Ctrl+C sungguhan: papan klip tidak berisi soal.
        $p->js("(function () { var s = getSelection(); s.removeAllRanges(); s.selectAllChildren(document.querySelector('[data-pernyataan]:not([hidden])')); return true; })()");
        $p->tekan('c', 'KeyC', 67, Peramban::CTRL, ['copy']);
        usleep(300_000);
        $klip = $p->papanKlip();
        $this->assertStringNotContainsString('Diberikan', $klip);
        $this->assertStringNotContainsString('bilangan', $klip);
        $this->assertTrue($p->js('window.__tombol.c'), 'Ctrl+C di luar editor dicegah');
        // Ctrl+X dan Ctrl+A di soal, Ctrl+P di mana pun.
        $p->klik('[data-pernyataan]:not([hidden]) h1');
        $p->tekan('a', 'KeyA', 65, Peramban::CTRL, ['selectAll']);
        $p->tekan('x', 'KeyX', 88, Peramban::CTRL, ['cut']);
        $p->tekan('p', 'KeyP', 80, Peramban::CTRL);
        $this->assertSame([true, true, true], [$p->js('window.__tombol.a'), $p->js('window.__tombol.x'), $p->js('window.__tombol.p')]);
        $this->assertStringNotContainsString('Diberikan', (string) $p->js('getSelection().toString()'));
        // Menu konteks di atas soal.
        $p->klik('[data-pernyataan]:not([hidden]) p', 'right');
        $this->assertTrue($p->js('window.__menu'));
        // Tes contoh di tab Test Cases juga tidak bisa dipilih.
        $this->assertSame('none', $p->js("getComputedStyle(document.querySelector('#isi-tes .contoh-pasang pre')).userSelect"));

        // Kode sendiri di editor tetap bisa disalin (yang diblokir adalah soal).
        $p->js('window.lokakodeEditor.focus(); window.lokakodeEditor.setSelection(window.lokakodeEditor.getModel().getFullModelRange()); true');
        $p->tekan('c', 'KeyC', 67, Peramban::CTRL, ['copy']);
        usleep(300_000);
        $this->assertStringContainsString('#include <iostream>', $p->papanKlip());

        // Tanda air: nomor induk, nama dan waktu; menutupi seluruh soal; tidak menghalangi klik.
        $tanda = (string) $p->js("document.querySelector('[data-tanda-air] span').textContent");
        $this->assertMatchesRegularExpression('/^' . preg_quote($d['mhsA']['nomor_induk'], '/') . ' · Mahasiswa Ani · \d{2}-\d{2}-\d{4} \d{2}:\d{2}$/u', $tanda);
        $ukur = $p->js("(function () { var t = document.querySelector('[data-tanda-air]'), w = document.querySelector('.pernyataan-wadah'), g = getComputedStyle(t);"
            . ' var a = t.getBoundingClientRect(), b = w.getBoundingClientRect();'
            . ' return { menutupi: a.left <= b.left && a.top <= b.top && a.right >= b.right && a.bottom >= b.bottom, klik: g.pointerEvents, pudar: parseFloat(g.opacity),'
            . " banyak: t.querySelectorAll('span').length, tampak: g.display !== 'none' && g.visibility === 'visible' }; })()");
        $this->assertSame(['menutupi' => true, 'klik' => 'none', 'banyak' => 40, 'tampak' => true], array_diff_key($ukur, ['pudar' => 1]));
        $this->assertGreaterThan(0.05, $ukur['pudar']);
        $this->assertLessThan(0.4, $ukur['pudar']);

        // Dicetak: isi halaman soal hilang, yang tampil hanya pemberitahuan.
        $p->media('print');
        $this->assertSame(['none', 'block'], [$p->js("getComputedStyle(document.querySelector('.soal-tata')).display"), $p->js("getComputedStyle(document.querySelector('.hanya-cetak')).display")]);
        $p->media('screen');
        $this->assertSame('none', $p->js("getComputedStyle(document.querySelector('.hanya-cetak')).display"));
        $this->assertSame([], $p->js('window.__galat'));
    }

    public function testJalankanDanKirimDariTombolSampaiNilaiTampilDiHalaman(): void
    {
        $d   = $this->duniaKerja();
        $p   = $this->bukaSoal($d);
        $tes = self::tesInstans();
        $p->js('window.lokakodeEditor.setValue(' . json_encode(self::BENAR) . ') || true');

        // Jalankan: dua tes contoh, keluaran program tampil, tidak dinilai.
        $p->klik('[data-aksi="jalankan"]');
        $p->tunggu("document.querySelectorAll('[data-keluaran] .verdict-ac').length === 3", 90, 'hasil Run pada dua contoh');
        $this->assertStringContainsString('Hasil pada tes contoh (tidak dinilai)', (string) $p->js("document.querySelector('[data-keluaran]').textContent"));
        $p->klik('#tab-tes');
        $this->assertSame(trim($tes[0]['out']), trim((string) $p->js("document.querySelector('[data-tes-contoh=\"0\"] [data-tes-keluaran]').textContent")));
        $this->assertSame('Benar', $p->js("document.querySelector('[data-tes-contoh=\"1\"] [data-tes-verdict]').textContent"));
        $this->assertSame(0, $this->hitung('SELECT COUNT(*) FROM submissions'));

        // Kirim dengan Ctrl+Shift+Enter dari dalam editor.
        $this->lupakanLaju();
        $this->fokusEditor($p);
        $p->tekan('Enter', 'Enter', 13, Peramban::CTRL | Peramban::SHIFT);
        $p->tunggu("(document.querySelector('[data-keluaran] .hasil-nilai') || {}).textContent === 'Nilai kiriman ini: 5 dari 5'", 120, 'hasil Submit');
        $this->assertSame(['Hasil penilaian Benar', '10 dari 10 tes tersembunyi lulus', 10, 10], [
            $p->js("document.querySelector('[data-keluaran] .hasil-judul').textContent"),
            $p->js("document.querySelector('[data-keluaran] .hasil-nilai').nextElementSibling.textContent"),
            $p->js("document.querySelectorAll('[data-keluaran] .tes-daftar li').length"),
            $p->js("document.querySelectorAll('[data-keluaran] .tes-daftar .verdict-ac').length"),
        ]);
        // Kepala halaman diperbarui tanpa memuat ulang.
        $this->assertSame(['Nilai terbaik: 5 dari 5', '5 dari 60 poin', 'Masih perlu 55 poin', '8.33'], [
            $p->js("document.querySelector('[data-skor-soal]').textContent"),
            $p->js("document.querySelector('[data-ringkasan] [data-poin]').textContent"),
            $p->js("document.querySelector('[data-ringkasan] [data-kurang]').textContent"),
            $p->js("document.querySelector('[data-ringkasan] [data-nilai]').textContent"),
        ]);
        $this->assertSame(['DINILAI', 'AC', '5.00'], array_values($this->row('SELECT status, verdict, score FROM submissions')));

        // Riwayat: satu kiriman; "Lihat" menampilkan kodenya sendiri.
        $p->klik('#tab-riwayat');
        $p->tunggu("document.querySelectorAll('[data-riwayat] tbody tr').length === 1", 10, 'riwayat');
        $p->klik('[data-riwayat] tbody button');
        $p->tunggu("document.querySelector('[data-keluaran]').textContent.indexOf('std::cin>>a>>b') > 0", 10, 'kode kiriman');

        // Ctrl+Enter = Jalankan.
        $this->lupakanLaju();
        $this->fokusEditor($p);
        $p->tekan('Enter', 'Enter', 13, Peramban::CTRL);
        $p->tunggu("document.querySelector('[data-keluaran]').textContent.indexOf('Hasil pada tes contoh') === 0 && document.querySelectorAll('[data-keluaran] .verdict-ac').length === 3", 90, 'Ctrl+Enter');

        // SPEC.md §18: tes tersembunyi hanya statusnya. Tidak satu pun masukan atau keluarannya ada di halaman.
        $html = (string) $p->js('document.documentElement.outerHTML');
        foreach (array_slice($tes, 2) as $i => $t) {
            $this->assertStringNotContainsString(trim($t['in']), $html, "masukan tes tersembunyi {$i}");
            $this->assertStringNotContainsString(trim($t['out']), $html, "keluaran tes tersembunyi {$i}");
        }
        $this->assertSame([[], []], [$p->js('window.__csp'), $p->js('window.__galat')]);
    }

    public function testSesiYangBerakhirDilaporkanDenganJelasBukanDiam(): void
    {
        $d = $this->duniaKerja();
        $p = $this->bukaSoal($d);
        $this->idn->query("UPDATE sessions SET revoked_at = UTC_TIMESTAMP() WHERE user_id = '{$d['mhsA']['id']}'");

        $p->klik('[data-aksi="jalankan"]');
        $p->tunggu("Array.prototype.some.call(document.querySelectorAll('[data-toast-soal] .toast'), function (t) { return t.textContent.indexOf('Sesi Anda sudah berakhir.') === 0; })", 10, 'pesan sesi berakhir');
        $this->assertFalse($p->js("document.querySelector('[data-aksi=\"jalankan\"]').disabled"), 'tombol tidak tertinggal dalam keadaan sibuk');
        $this->assertSame(0, $this->hitung("SELECT COUNT(*) FROM judge_jobs WHERE created_by = '{$d['mhsA']['id']}' AND kind = 'RUN' AND created_at > UTC_TIMESTAMP() - INTERVAL 1 MINUTE AND payload LIKE '%instance_id%'"));
    }

    public function testPanelBisaDiubahUkurannyaTersimpanDanMenjadiTabDiLayarSempit(): void
    {
        $d = $this->duniaKerja();
        $p = $this->bukaSoal($d);
        $kiri = fn () => (string) $p->js("document.querySelector('[data-soal]').style.getPropertyValue('--kiri')");
        $this->assertSame('40%', $kiri());

        // Dengan papan ketik: tiga kali panah kanan pada pembagi tegak.
        $p->js("document.querySelector('[data-pembagi=kiri]').focus() || true");
        for ($i = 0; $i < 3; $i++) {
            $p->tekan('ArrowRight', 'ArrowRight', 39);
        }
        $this->assertSame('46%', $kiri());
        $this->tungguSampai(fn () => ($this->row('SELECT panels FROM ui_prefs')['panels'] ?? '') === '{"atas": 60, "kiri": 46}', 6, 'ukuran panel tersimpan');

        // Dengan tetikus: pembagi datar diseret ke atas.
        [$x, $y] = $p->tengah('[data-pembagi=atas]');
        $p->perintah('Input.dispatchMouseEvent', ['type' => 'mousePressed', 'x' => $x, 'y' => $y, 'button' => 'left', 'buttons' => 1, 'clickCount' => 1]);
        $p->perintah('Input.dispatchMouseEvent', ['type' => 'mouseMoved', 'x' => $x, 'y' => $y - 150, 'button' => 'left', 'buttons' => 1]);
        $p->perintah('Input.dispatchMouseEvent', ['type' => 'mouseReleased', 'x' => $x, 'y' => $y - 150, 'button' => 'left', 'buttons' => 0, 'clickCount' => 1]);
        $atas = (float) $p->js("parseFloat(document.querySelector('[data-soal]').style.getPropertyValue('--atas'))");
        $this->assertLessThan(55.0, $atas, 'editor mengecil setelah pembagi diseret ke atas');
        $this->assertGreaterThanOrEqual(15.0, $atas);

        // Dibuka lagi: ukurannya kembali.
        usleep(1_200_000);
        unset($p);
        $p = $this->bukaSoal($d);
        $this->assertSame('46%', (string) $p->js("document.querySelector('[data-soal]').style.getPropertyValue('--kiri')"));
        $lebar = $p->js("(function () { var a = document.querySelector('.panel-soal').getBoundingClientRect().width, b = document.querySelector('[data-soal]').getBoundingClientRect().width; return a / b; })()");
        $this->assertEqualsWithDelta(0.46, $lebar, 0.02);

        // Di bawah 1024 px: tiga tab bertumpuk, satu panel terlihat pada satu waktu.
        $tampak = fn (string $pemilih) => $p->js('document.querySelector(' . json_encode($pemilih) . ').getBoundingClientRect().width > 0');
        $this->assertSame('none', $p->js("getComputedStyle(document.querySelector('[data-tab-sempit]')).display"));
        $p->ukuran(800, 900);
        $this->assertSame('flex', $p->js("getComputedStyle(document.querySelector('[data-tab-sempit]')).display"));
        $this->assertSame([true, false, false], [$tampak('.panel-soal'), $tampak('.panel-editor'), $tampak('.panel-hasil')]);
        $p->klik('[data-tab-sempit] [data-panel=editor]');
        $this->assertSame([false, true, false], [$tampak('.panel-soal'), $tampak('.panel-editor'), $tampak('.panel-hasil')]);
        $p->tunggu("document.querySelector('.monaco-editor').getBoundingClientRect().width > 600", 5, 'Monaco mengisi layar sempit');
        $p->klik('[data-tab-sempit] [data-panel=hasil]');
        $this->assertSame([false, false, true], [$tampak('.panel-soal'), $tampak('.panel-editor'), $tampak('.panel-hasil')]);
        $this->assertSame([], $p->js('window.__galat'));
    }

    public function testLayarMenyiapkanSoalBergantiSendiriBegituInstansAda(): void
    {
        $d = $this->duniaKerja(false);
        $p = $this->peramban($d['mhsA']);
        $this->assertSame(200, $p->buka($d['jalur']));
        $this->assertSame('Menyiapkan soal Anda', $p->js("document.querySelector('h1').textContent"));
        $this->assertMatchesRegularExpression('/Perkiraan menunggu: sekitar \d+ (detik|menit)/', (string) $p->js("document.querySelector('[data-siapkan-pesan]').textContent"));
        // Tanpa disentuh: halaman bertanya sendiri, lalu berganti menjadi halaman soal dengan editor.
        $p->tunggu("document.documentElement.dataset.editorSiap === '1'", 120, 'halaman soal muncul sendiri');
        $this->assertSame(1, $this->hitung("SELECT COUNT(*) FROM problem_instances WHERE user_id = '{$d['mhsA']['id']}'"));
        $this->assertSame([], $p->js('window.__csp'));
    }
}
