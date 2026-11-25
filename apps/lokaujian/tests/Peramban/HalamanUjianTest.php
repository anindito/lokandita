<?php

declare(strict_types=1);

namespace Tests\Peramban;

use Lokandita\Auth\Testing\Fixtures;
use Lokandita\Auth\Testing\Peramban;
use Tests\Support\UjianCase;

/**
 * Halaman ujian di PERAMBAN SUNGGUHAN (Chromium tanpa layar, KONTRAK.md §12): simpan otomatis,
 * hitung mundur, melanjutkan, bekerja tanpa sambungan, blokir tempel dan salin, pengambilalihan,
 * dan mengumpulkan. Yang diuji adalah public/js/ujian.js sebagaimana dijalankan peramban, lewat
 * HTTPS dan CSP yang sama dengan produksi. Tombol dan klik adalah peristiwa masukan peramban.
 *
 * Tes ini GAGAL, tidak dilewati, bila peramban tidak ada (Peramban melempar).
 */
final class HalamanUjianTest extends UjianCase
{
    private const ASAL     = 'https://lokaujian.lokandita.com';
    private const TEMPELAN = 'TEMPELAN-DARI-LUAR';

    /**
     * Ujian per mahasiswa 20 menit di kelas XA; mahasiswa A sudah mulai.
     *
     * @return array<string, mixed>
     */
    private function duniaKerja(): array
    {
        $d          = $this->dunia();
        $http       = $this->sebagai($d['pengampuX']);
        $d['ujian'] = $this->ujianTerbit($http, $d['matkulX'], 'Kuis peramban', ['mode' => 'per_mahasiswa', 'durasi' => '20']);
        $d['sesi']  = $this->beri($http, $d['matkulX'], $d['ujian'], $d['kelasXA']);
        $d['a']     = $this->mulaiKerja($this->sebagai($d['mhsA']), $d['sesi'], (string) $d['mhsA']['id']);
        $d['q']     = $this->soalBeku($d['ujian']);
        $d['jalur'] = "/ujian-saya/{$d['sesi']}/kerjakan";

        return $d;
    }

    /**
     * @param array<string, mixed> $d
     */
    private function buka(array $d): Peramban
    {
        $p = new Peramban(self::ASAL);
        $p->kuki('lokandita_sid', Fixtures::session($this->idn, (string) $d['mhsA']['id']));
        $this->assertSame(200, $p->buka($d['jalur']));
        $p->tunggu("document.getElementById('ujian').dataset.siap === '1'", 20, 'skrip halaman ujian siap');

        return $p;
    }

    private function tungguSampai(callable $syarat, float $detik, string $apa): void
    {
        $batas = microtime(true) + $detik;
        do {
            if ($syarat()) {
                $this->addToAssertionCount(1);

                return;
            }
            usleep(150_000);
        } while (microtime(true) < $batas);
        $this->fail('tidak tercapai dalam ' . $detik . ' detik: ' . $apa);
    }

    private function teksJawaban(array $d, string $soal): ?string
    {
        $b = $this->db->query("SELECT text FROM answers WHERE attempt_id = {$d['a']['id']} AND exam_question_id = {$d['q'][$soal]}")->fetch_assoc();

        return $b === null ? null : (string) $b['text'];
    }

    /** Membuka soal ke-$nomor lewat peta soal (klik sungguhan) dan memfokuskan kotak jawabannya. */
    private function keSoal(Peramban $p, array $d, string $soal, bool $fokus = true): string
    {
        $pemilih = '[data-halaman-soal][data-id="' . $d['q'][$soal] . '"]';
        $indeks  = (int) $p->js("document.querySelector('{$pemilih}').dataset.halamanSoal");
        $p->klik('[data-ke="' . $indeks . '"]');
        $p->tunggu("!document.querySelector('{$pemilih}').hidden", 5, 'soal tampil');
        if ($fokus) {
            $p->klik($pemilih . ' input[type="text"], ' . $pemilih . ' textarea');
        }

        return $pemilih;
    }

    private function keadaanSimpan(Peramban $p): string
    {
        return (string) $p->js("document.querySelector('[data-simpan]').dataset.keadaan");
    }

    private function tanpaGalat(Peramban $p): void
    {
        $this->assertSame([], $p->js('window.__csp'), 'pelanggaran CSP di halaman ujian');
        $this->assertSame([], $p->js('window.__galat'), 'galat skrip di halaman ujian');
    }

    public function testSatuSoalTampilPetaDanSimpanOtomatis(): void
    {
        $d = $this->duniaKerja();
        $p = $this->buka($d);

        // Satu soal pada satu waktu; kepala memuat judul, nama, nomor induk dan keadaan simpan.
        $this->assertSame(1, $p->js("document.querySelectorAll('[data-halaman-soal]:not([hidden])').length"));
        $this->assertSame(6, $p->js("document.querySelectorAll('[data-ke]').length"));
        $kepala = (string) $p->js("document.querySelector('[data-kepala]').innerText");
        $this->assertStringContainsString('Kuis peramban', $kepala);
        $this->assertStringContainsString((string) $d['mhsA']['nomor_induk'], $kepala);
        $this->assertStringContainsString('Tersimpan', $kepala);
        $this->assertStringContainsString((string) $d['mhsA']['nomor_induk'], (string) $p->js("document.querySelector('.tanda-air').textContent"));
        $this->assertStringContainsString('MODE UJI', (string) $p->js('document.body.innerText'));

        // Pilihan disimpan seketika.
        $bs = '[data-halaman-soal][data-id="' . $d['q']['BS'] . '"]';
        $this->keSoal($p, $d, 'BS', false);
        $p->klik($bs . ' input[value="salah"]');
        $this->tungguSampai(fn () => ($this->db->query("SELECT benar FROM answers WHERE exam_question_id = {$d['q']['BS']}")->fetch_assoc()['benar'] ?? null) === '0', 5, 'pilihan tersimpan seketika');
        $p->tunggu("document.querySelector('[data-simpan]').dataset.keadaan === 'tersimpan'", 5);
        $this->assertMatchesRegularExpression('/Tersimpan \d\d:\d\d:\d\d/', (string) $p->js("document.querySelector('[data-simpan]').innerText"));
        $this->assertTrue($p->js("document.querySelector('[data-ke=\"0\"]').classList.contains('terjawab') || document.querySelectorAll('.peta-tombol.terjawab').length === 1"));
        // Hapus pilihan.
        $p->klik($bs . ' [data-hapus-pilihan]');
        $this->tungguSampai(fn () => $this->db->query("SELECT benar FROM answers WHERE exam_question_id = {$d['q']['BS']}")->fetch_assoc()['benar'] === null, 5, 'pilihan terhapus');
        $this->assertSame(0, $p->js("document.querySelectorAll('.peta-tombol.terjawab').length"));

        // Teks: "Menyimpan…" selagi mengetik, tersimpan sekitar 2 detik sesudah ketikan berhenti.
        $this->keSoal($p, $d, 'URAIAN');
        $p->ketik('Rekursi memanggil dirinya');
        $this->assertSame('menyimpan', $this->keadaanSimpan($p));
        $this->assertNull($this->teksJawaban($d, 'URAIAN'), 'teks tersimpan sebelum ketikan berhenti 2 detik');
        $this->tungguSampai(fn () => $this->teksJawaban($d, 'URAIAN') === 'Rekursi memanggil dirinya', 6, 'teks tersimpan sesudah ketikan berhenti');
        $p->tunggu("document.querySelector('[data-simpan]').dataset.keadaan === 'tersimpan'", 5);
        $this->assertStringContainsString('25 / 20000', (string) $p->js("document.querySelector('[data-halaman-soal]:not([hidden]) [data-hitung]').textContent"));
        // Pindah soal menyimpan yang sedang diketik tanpa menunggu.
        $p->ketik(' sendiri');
        $p->klik('[data-berikutnya]');
        $this->tungguSampai(fn () => $this->teksJawaban($d, 'URAIAN') === 'Rekursi memanggil dirinya sendiri', 1.5, 'pindah soal menyimpan seketika');
        // Kelompok: bagian bersama tampil bersama soalnya, dan hanya di sana.
        $this->keSoal($p, $d, 'KEL-2', false);
        $this->assertFalse($p->js("document.querySelector('[data-bersama]').hidden"));
        $this->keSoal($p, $d, 'ISIAN', false);
        $this->assertTrue($p->js("document.querySelector('[data-bersama]').hidden"));
        // Ragu-ragu.
        $p->klik('[data-halaman-soal]:not([hidden]) [data-ragu]');
        $this->tungguSampai(fn () => ($this->db->query("SELECT ragu FROM answers WHERE exam_question_id = {$d['q']['ISIAN']}")->fetch_assoc()['ragu'] ?? null) === '1', 5, 'ragu-ragu tersimpan');
        $this->assertSame(1, $p->js("document.querySelectorAll('.peta-tombol.ragu').length"));
        $this->tanpaGalat($p);
    }

    public function testHitungMundurMengikutiServer(): void
    {
        $d = $this->duniaKerja();
        $this->db->query("UPDATE attempts SET started_at = UTC_TIMESTAMP() - INTERVAL 9 MINUTE - INTERVAL 30 SECOND WHERE id = {$d['a']['id']}");
        $p     = $this->buka($d);
        $detik = static function (Peramban $p): int {
            [$m, $s] = array_map('intval', explode(':', (string) $p->js("document.querySelector('[data-jam]').textContent")));

            return $m * 60 + $s;
        };
        $awal = $detik($p);
        $this->assertEqualsWithDelta(630, $awal, 4);
        sleep(3);
        $this->assertEqualsWithDelta($awal - 3, $detik($p), 2, 'hitung mundur tidak berjalan');
        // Jam peramban tidak dipakai: memundurkannya satu jam tidak mengubah hitungan.
        $p->js('(function () { var asli = Date.now; Date.now = function () { return asli() - 3600000; }; return true; })()');
        sleep(1);
        $this->assertEqualsWithDelta($awal - 4, $detik($p), 3);
        // Melewati 10 menit: warna berubah dan diumumkan ke pembaca layar.
        $this->db->query("UPDATE attempts SET started_at = UTC_TIMESTAMP() - INTERVAL 10 MINUTE + INTERVAL 13 SECOND WHERE id = {$d['a']['id']}");
        $p->tunggu("document.querySelector('.ujian-jam').dataset.tingkat === '10'", 30, 'tingkat 10 menit sesudah server menggeser tenggat');
        $p->tunggu("document.querySelector('[data-umumkan]').textContent.indexOf('10 menit') >= 0", 20, 'pengumuman 10 menit');
        // Tambahan waktu dari server terlihat dalam hitungan detik.
        $this->db->query("UPDATE attempts SET extra_minutes = 30 WHERE id = {$d['a']['id']}");
        $this->tungguSampai(fn () => $detik($p) > 2000, 14, 'tambahan waktu tampil di hitung mundur');
        $this->tanpaGalat($p);
    }

    /**
     * Gerbang: menutup peramban di tengah ujian lalu kembali mempertahankan jawaban dan sisa waktu.
     */
    public function testMenutupPerambanLaluKembaliMelanjutkan(): void
    {
        $d = $this->duniaKerja();
        $p = $this->buka($d);
        $this->keSoal($p, $d, 'ISIAN');
        $p->ketik('Jakarta');
        $this->keSoal($p, $d, 'BS', false);
        $p->klik('[data-halaman-soal]:not([hidden]) input[value="benar"]');
        $this->tungguSampai(fn () => $this->teksJawaban($d, 'ISIAN') === 'Jakarta' && $this->db->query("SELECT COUNT(*) AS n FROM answers WHERE benar = 1")->fetch_assoc()['n'] === '1', 6, 'jawaban tersimpan sebelum ditutup');
        $urut = $p->js("Array.prototype.map.call(document.querySelectorAll('[data-halaman-soal]'), function (e) { return e.dataset.id; })");
        unset($p); // peramban ditutup

        // Waktu berjalan terus selama peramban tertutup (di sini: 5 menit).
        $this->db->query("UPDATE attempts SET started_at = started_at - INTERVAL 5 MINUTE WHERE id = {$d['a']['id']}");
        $p2 = $this->buka($d);
        $this->assertSame($urut, $p2->js("Array.prototype.map.call(document.querySelectorAll('[data-halaman-soal]'), function (e) { return e.dataset.id; })"), 'urutan soal berubah sesudah kembali');
        $this->assertSame('Jakarta', $p2->js("document.querySelector('[data-halaman-soal][data-id=\"{$d['q']['ISIAN']}\"] input[type=text]').value"));
        $this->assertTrue($p2->js("document.querySelector('[data-halaman-soal][data-id=\"{$d['q']['BS']}\"] input[value=benar]').checked"));
        $this->assertSame(2, $p2->js("document.querySelectorAll('.peta-tombol.terjawab').length"));
        [$m, $s] = array_map('intval', explode(':', (string) $p2->js("document.querySelector('[data-jam]').textContent")));
        $this->assertEqualsWithDelta(900, $m * 60 + $s, 20, 'sisa waktu sesudah kembali harus 20 menit dikurangi yang sudah berlalu');
        // Melanjutkan: suntingan sesudah kembali tersimpan di atas yang lama.
        $this->keSoal($p2, $d, 'ISIAN');
        $p2->tekan('End', 'End', 35);
        $p2->ketik(' Pusat');
        $this->tungguSampai(fn () => $this->teksJawaban($d, 'ISIAN') === 'Jakarta Pusat', 6, 'suntingan sesudah kembali tersimpan');
        $this->tanpaGalat($p2);
    }

    public function testTanpaSambunganJawabanDitahanLaluTerkirim(): void
    {
        $d = $this->duniaKerja();
        $p = $this->buka($d);
        $this->keSoal($p, $d, 'URAIAN');
        $p->ketik('sebelum putus');
        $this->tungguSampai(fn () => $this->teksJawaban($d, 'URAIAN') === 'sebelum putus', 6, 'simpan pertama');

        $p->perintah('Network.emulateNetworkConditions', ['offline' => true, 'latency' => 0, 'downloadThroughput' => -1, 'uploadThroughput' => -1]);
        $p->ketik(' — diketik tanpa sambungan');
        $p->tunggu("document.querySelector('[data-simpan]').dataset.keadaan === 'belum'", 10, 'kepala menulis Belum tersimpan');
        $this->assertStringContainsString('Belum tersimpan — mencoba lagi', (string) $p->js("document.querySelector('[data-simpan]').innerText"));
        $this->assertFalse($p->js("document.querySelector('[data-luring]').hidden"), 'pita sambungan terputus');
        // Mahasiswa tetap bekerja: pindah soal, menjawab yang lain; tidak ada yang dibuang halaman.
        $this->keSoal($p, $d, 'ISIAN');
        $p->ketik('juga tanpa sambungan');
        sleep(4);
        $this->assertSame('sebelum putus', $this->teksJawaban($d, 'URAIAN'));
        $this->assertNull($this->teksJawaban($d, 'ISIAN'));
        $this->assertSame('sebelum putus — diketik tanpa sambungan', $p->js("document.querySelector('[data-halaman-soal][data-id=\"{$d['q']['URAIAN']}\"] textarea').value"));
        $this->assertNotSame('tersimpan', $this->keadaanSimpan($p), 'hanya konfirmasi server yang menghasilkan Tersimpan');

        $p->perintah('Network.emulateNetworkConditions', ['offline' => false, 'latency' => 0, 'downloadThroughput' => -1, 'uploadThroughput' => -1]);
        $this->tungguSampai(fn () => $this->teksJawaban($d, 'URAIAN') === 'sebelum putus — diketik tanpa sambungan' && $this->teksJawaban($d, 'ISIAN') === 'juga tanpa sambungan', 20, 'jawaban yang ditahan terkirim sesudah sambungan kembali');
        $p->tunggu("document.querySelector('[data-simpan]').dataset.keadaan === 'tersimpan' && document.querySelector('[data-luring]').hidden", 15, 'kembali Tersimpan dan pita hilang');
        $this->assertSame([], $p->js('window.__csp'));
    }

    /**
     * Gerbang: Ctrl+V ditolak di setiap kotak teks. Setiap jalur tempel dicoba pada isian dan uraian.
     */
    public function testTempelDitolakDiSetiapKotakTeksPadaSetiapJalur(): void
    {
        $d = $this->duniaKerja();
        $p = $this->buka($d);
        $jalurTercatat = fn (): array => array_map(
            static fn (array $b): string => (string) json_decode((string) $b['detail'], true)['jalur'],
            $this->db->query("SELECT detail FROM integrity_events WHERE kind = 'paste_blocked' ORDER BY id")->fetch_all(MYSQLI_ASSOC),
        );
        foreach (['ISIAN' => 'input[type="text"]', 'URAIAN' => 'textarea', 'KEL-1' => 'input[type="text"]'] as $soal => $jenis) {
            $pemilih = $this->keSoal($p, $d, $soal) . ' ' . $jenis;
            $nilai   = fn (): string => (string) $p->js('document.querySelector(' . json_encode($pemilih) . ').value');
            $p->ketik('ketikan sendiri');
            $this->assertSame(self::TEMPELAN, $p->papanKlip(self::TEMPELAN));
            $p->klik($pemilih);
            $utuh = function (string $jalur) use ($nilai, $soal): void {
                usleep(200_000);
                $this->assertSame('ketikan sendiri', $nilai(), "{$soal}: tempelan lolos lewat {$jalur}");
            };

            $p->tekan('v', 'KeyV', 86, Peramban::CTRL, ['paste']);
            $utuh('Ctrl+V');
            $this->assertFalse($p->js("document.querySelector('[data-toast-ujian]').hidden"), 'pesan singkat tampil');
            $this->assertStringContainsString('Menempel tidak diizinkan', (string) $p->js("document.querySelector('[data-toast-ujian]').textContent"));
            $p->tekan('Insert', 'Insert', 45, Peramban::SHIFT, ['paste']);
            $utuh('Shift+Insert');
            // Peristiwa paste itu sendiri (menu Edit > Tempel, pilihan utama X11).
            $dicegah = $p->js("(function () { var dt = new DataTransfer(); dt.setData('text/plain', " . json_encode(self::TEMPELAN) . ');'
                . " var e = new ClipboardEvent('paste', { clipboardData: dt, bubbles: true, cancelable: true }); document.activeElement.dispatchEvent(e); return e.defaultPrevented; })()");
            $this->assertTrue($dicegah);
            $utuh('peristiwa paste');
            // Seret dan lepas.
            $dicegah = $p->js("(function () { var dt = new DataTransfer(); dt.setData('text/plain', " . json_encode(self::TEMPELAN) . ');'
                . " var e = new DragEvent('drop', { dataTransfer: dt, bubbles: true, cancelable: true }); document.activeElement.dispatchEvent(e); return e.defaultPrevented; })()");
            $this->assertTrue($dicegah);
            $utuh('seret dan lepas');
            // beforeinput jenis tempel (IME, atau peramban yang tidak mengirim peristiwa paste).
            $dicegah = $p->js("(function () { var e = new InputEvent('beforeinput', { inputType: 'insertFromPaste', data: " . json_encode(self::TEMPELAN) . ', bubbles: true, cancelable: true }); document.activeElement.dispatchEvent(e); return e.defaultPrevented; })()');
            $this->assertTrue($dicegah);
            // Klik tengah dan menu konteks (klik kanan sungguhan).
            $p->js("(function () { window.__konteks = null; document.addEventListener('contextmenu', function (e) { window.__konteks = e.defaultPrevented; }); return true; })()");
            $p->klik($pemilih, 'middle');
            $utuh('klik tengah');
            $p->klik($pemilih, 'right');
            $this->assertTrue($p->js('window.__konteks'), 'menu konteks tidak dicegah');
            $utuh('menu konteks');
            // Kotaknya tetap bisa diketik sesudah semua itu.
            $p->klik($pemilih);
            $p->tekan('End', 'End', 35);
            $p->ketik('!');
            $this->assertSame('ketikan sendiri!', $nilai());
        }
        $this->tungguSampai(fn () => count(array_intersect(['keyboard', 'shift_insert', 'paste', 'drop', 'middle_click', 'context_menu'], $jalurTercatat())) === 6, 8, 'keenam jalur tempel tercatat sebagai peristiwa integritas: ' . implode(',', $jalurTercatat()));
        // Tidak satu pun tempelan sampai ke server.
        $this->tungguSampai(fn () => $this->teksJawaban($d, 'URAIAN') === 'ketikan sendiri!', 8, 'ketikan tersimpan');
        $this->assertSame('0', (string) $this->db->query("SELECT COUNT(*) AS n FROM answers WHERE text LIKE '%TEMPELAN%'")->fetch_assoc()['n']);
        $this->tanpaGalat($p);
    }

    public function testSalinPotongSeleksiDanCetakDiblokir(): void
    {
        $d = $this->duniaKerja();
        $p = $this->buka($d);
        $pemilih = $this->keSoal($p, $d, 'URAIAN') . ' textarea';
        $p->ketik('jawaban rahasia saya');
        $this->assertSame('PENANDA-AWAL', $p->papanKlip('PENANDA-AWAL'));
        $p->klik($pemilih);
        $p->tekan('a', 'KeyA', 65, Peramban::CTRL, ['selectAll']);
        $p->tekan('c', 'KeyC', 67, Peramban::CTRL, ['copy']);
        $this->assertSame('PENANDA-AWAL', $p->papanKlip(), 'salin dari kotak jawaban lolos');
        $p->tekan('x', 'KeyX', 88, Peramban::CTRL, ['cut']);
        $this->assertSame('PENANDA-AWAL', $p->papanKlip(), 'potong dari kotak jawaban lolos');
        $this->assertSame('jawaban rahasia saya', $p->js('document.querySelector(' . json_encode($pemilih) . ').value'));
        $this->assertStringContainsString('Menyalin tidak diizinkan', (string) $p->js("document.querySelector('[data-toast-ujian]').textContent"));
        // Teks soal tidak bisa diseleksi, dan peristiwa copy di sana dicegah.
        $this->assertSame('none', $p->js("getComputedStyle(document.querySelector('[data-halaman-soal]:not([hidden]) .soal-teks')).userSelect"));
        $this->assertTrue($p->js("(function () { var e = new ClipboardEvent('copy', { bubbles: true, cancelable: true }); document.querySelector('[data-halaman-soal]:not([hidden]) .soal-teks').dispatchEvent(e); return e.defaultPrevented; })()"));
        // Ctrl+P dicegah; lembar cetak kosong.
        $this->assertTrue($p->js("(function () { var e = new KeyboardEvent('keydown', { key: 'p', ctrlKey: true, bubbles: true, cancelable: true }); document.body.dispatchEvent(e); return e.defaultPrevented; })()"));
        $p->media('print');
        $this->assertSame('none', $p->js("getComputedStyle(document.querySelector('main')).display"));
        $p->media('screen');
        $this->tungguSampai(fn () => count($this->db->query("SELECT id FROM integrity_events WHERE kind = 'paste_blocked' AND JSON_EXTRACT(detail, '$.jalur') IN ('copy', 'cut')")->fetch_all()) >= 2, 6, 'salin dan potong tercatat');
        $this->tanpaGalat($p);
    }

    public function testPasteAllowedBolehMenempelDanTidakDicatat(): void
    {
        $d = $this->duniaKerja();
        $this->idn->query("UPDATE users SET paste_allowed = 1 WHERE id = '{$d['mhsA']['id']}'");
        $p       = $this->buka($d);
        $pemilih = $this->keSoal($p, $d, 'URAIAN') . ' textarea';
        $this->assertSame(self::TEMPELAN, $p->papanKlip(self::TEMPELAN));
        $p->klik($pemilih);
        $p->tekan('v', 'KeyV', 86, Peramban::CTRL, ['paste']);
        $this->tungguSampai(fn () => $p->js('document.querySelector(' . json_encode($pemilih) . ').value') === self::TEMPELAN, 5, 'dengan paste_allowed tombol yang sama benar-benar menempel');
        $this->tungguSampai(fn () => $this->teksJawaban($d, 'URAIAN') === self::TEMPELAN, 6, 'tempelan tersimpan');
        $this->assertSame('0', (string) $this->db->query('SELECT COUNT(*) AS n FROM integrity_events')->fetch_assoc()['n']);
    }

    public function testHalamanLamaBerhentiKetikaDiambilAlih(): void
    {
        $d    = $this->duniaKerja();
        $lama = $this->buka($d);
        $this->keSoal($lama, $d, 'ISIAN');
        $lama->ketik('dari halaman lama');
        $this->tungguSampai(fn () => $this->teksJawaban($d, 'ISIAN') === 'dari halaman lama', 6, 'simpan halaman lama');
        $baru = $this->buka($d); // komputer lain
        $this->assertSame('dari halaman lama', $baru->js("document.querySelector('[data-halaman-soal][data-id=\"{$d['q']['ISIAN']}\"] input[type=text]').value"));

        $lama->ketik(' yang terlambat');
        $lama->tunggu("!document.querySelector('[data-henti]').hidden", 10, 'halaman lama berhenti pada simpan berikutnya');
        $this->assertStringContainsString('dibuka di halaman atau komputer lain', (string) $lama->js("document.querySelector('[data-henti]').innerText"));
        $this->assertTrue($lama->js("document.querySelector('[data-badan]').hidden"));
        $this->assertSame('dari halaman lama', $this->teksJawaban($d, 'ISIAN'));
        // Halaman baru bekerja seperti biasa.
        $this->keSoal($baru, $d, 'ISIAN');
        $baru->tekan('End', 'End', 35);
        $baru->ketik(' lalu halaman baru');
        $this->tungguSampai(fn () => $this->teksJawaban($d, 'ISIAN') === 'dari halaman lama lalu halaman baru', 6, 'simpan halaman baru');
        $this->assertSame('1', (string) $this->db->query("SELECT COUNT(*) AS n FROM integrity_events WHERE kind = 'page_taken_over'")->fetch_assoc()['n']);
    }

    public function testWaktuHabisMenghentikanHalamanDanKetikanTerakhirMasuk(): void
    {
        $d = $this->duniaKerja();
        $p = $this->buka($d);
        $this->keSoal($p, $d, 'URAIAN');
        // Tenggat 6 detik lagi; mahasiswa masih mengetik sampai detik terakhir.
        $this->db->query("UPDATE attempts SET started_at = UTC_TIMESTAMP() - INTERVAL 20 MINUTE + INTERVAL 6 SECOND WHERE id = {$d['a']['id']}");
        $p->ketik('kalimat terakhir sebelum waktu habis');
        $p->tunggu("!document.querySelector('[data-henti]').hidden", 40, 'halaman berhenti sesudah waktu habis');
        $this->assertStringContainsString('Waktu habis', (string) $p->js("document.querySelector('[data-henti]').innerText"));
        $this->assertTrue($p->js("document.querySelector('[data-halaman-soal] textarea').disabled"));
        $this->assertSame('kalimat terakhir sebelum waktu habis', $this->teksJawaban($d, 'URAIAN'));
        // Dari sana ke tanda terima.
        $p->klik('[data-henti] a');
        $p->tunggu("!!document.querySelector('[data-tanda-terima]')", 10, 'tanda terima');
        $this->assertStringContainsString('1 dari 6', (string) $p->js("document.querySelector('[data-terjawab]').textContent"));
    }

    public function testTinjauLaluKumpulkan(): void
    {
        $d = $this->duniaKerja();
        $p = $this->buka($d);
        $this->keSoal($p, $d, 'ISIAN');
        $p->ketik('Jakarta');
        $p->klik('[data-halaman-soal]:not([hidden]) [data-ragu]');
        $p->klik('[data-selesai]');
        $p->tunggu("!document.querySelector('[data-tinjau]').hidden", 5, 'halaman tinjau');
        $this->assertStringContainsString('1 dari 6 soal sudah dijawab', (string) $p->js("document.querySelector('[data-tinjau-ringkas]').textContent"));
        $this->assertSame(5, $p->js("document.querySelectorAll('[data-tinjau-belum] button').length"));
        $this->assertSame(1, $p->js("document.querySelectorAll('[data-tinjau-ragu] button').length"));
        // Nomor di tinjauan membawa kembali ke soalnya.
        $p->klik('[data-tinjau-belum] button');
        $p->tunggu("document.querySelector('[data-tinjau]').hidden && !document.querySelector('[data-badan]').hidden", 5, 'kembali ke soal');
        $p->klik('[data-selesai]');
        $p->tunggu("!document.querySelector('[data-tinjau]').hidden", 5);
        $this->tungguSampai(fn () => $this->teksJawaban($d, 'ISIAN') === 'Jakarta', 6, 'jawaban tersimpan sebelum mengumpulkan');
        $p->tunggu("document.querySelector('[data-simpan]').dataset.keadaan === 'tersimpan'", 6);
        $p->klik('[data-kumpulkan]');
        $p->tunggu("!!document.querySelector('[data-tanda-terima]')", 15, 'tanda terima sesudah mengumpulkan');
        $this->assertSame('mahasiswa', $this->db->query("SELECT submit_how FROM attempts WHERE id = {$d['a']['id']}")->fetch_assoc()['submit_how']);
        $this->assertStringContainsString('1 dari 6', (string) $p->js("document.querySelector('[data-terjawab]').textContent"));
        $this->assertSame([], $p->js('window.__csp'));
    }

    /**
     * Bahasa Arab (kanan-ke-kiri) dan mode gelap: halaman ujian tetap utuh — tidak ada yang meluber
     * ke samping, angka hitung mundur tetap kiri-ke-kanan, peta soal dan kotak jawaban terlihat,
     * tanpa galat skrip. Ini mengukur TATA LETAK; mutu terjemahan Arab belum dibaca penuturnya.
     * Dengan TANGKAPAN=<direktori> tangkapan layarnya ditulis untuk dilihat orang.
     */
    public function testArabDanModeGelapTataLetakUtuh(): void
    {
        $d = $this->duniaKerja();
        foreach ([['id', 'light', 1280], ['ar', 'dark', 1280], ['ar', 'light', 390]] as [$bahasa, $skema, $lebar]) {
            $this->idn->query("UPDATE users SET locale = '{$bahasa}' WHERE id = '{$d['mhsA']['id']}'");
            $p = new Peramban(self::ASAL);
            $p->ukuran($lebar, 900);
            $p->perintah('Emulation.setEmulatedMedia', ['features' => [['name' => 'prefers-color-scheme', 'value' => $skema]]]);
            $p->kuki('lokandita_sid', Fixtures::session($this->idn, (string) $d['mhsA']['id']));
            $this->assertSame(200, $p->buka($d['jalur']));
            $p->tunggu("document.getElementById('ujian').dataset.siap === '1'", 20, "halaman ujian {$bahasa}/{$skema}/{$lebar}");
            $apa = "{$bahasa}/{$skema}/{$lebar}";
            $this->assertSame($bahasa === 'ar' ? 'rtl' : 'ltr', $p->js('document.documentElement.dir'), $apa);
            $this->assertLessThanOrEqual(1, $p->js('document.documentElement.scrollWidth - document.documentElement.clientWidth'), "{$apa}: halaman meluber ke samping");
            $this->assertSame('ltr', $p->js("getComputedStyle(document.querySelector('[data-jam]')).direction"), "{$apa}: hitung mundur harus kiri-ke-kanan");
            $this->assertMatchesRegularExpression('/^\d\d:\d\d$/', (string) $p->js("document.querySelector('[data-jam]').textContent"), $apa);
            foreach (['[data-kepala]', '[data-halaman-soal]:not([hidden]) .soal-teks', '.ujian-peta', '[data-selesai]'] as $pemilih) {
                $k = $p->js('(function () { var k = document.querySelector(' . json_encode($pemilih) . ').getBoundingClientRect(); return [k.left, k.right, k.width, k.height]; })()');
                $this->assertGreaterThan(0, $k[2] * $k[3], "{$apa}: {$pemilih} tidak terlihat");
                $this->assertGreaterThanOrEqual(-1, $k[0], "{$apa}: {$pemilih} keluar di kiri");
                $this->assertLessThanOrEqual($lebar + 1, $k[1], "{$apa}: {$pemilih} keluar di kanan");
            }
            $this->keSoal($p, $d, 'URAIAN');
            $p->ketik('اختبار 123');
            $p->tunggu("document.querySelector('[data-simpan]').dataset.keadaan === 'tersimpan'", 8, "{$apa}: tersimpan");
            if ((string) getenv('TANGKAPAN_DIR') !== '') {
                file_put_contents(getenv('TANGKAPAN_DIR') . "/ujian-{$bahasa}-{$skema}-{$lebar}.png", $p->tangkap());
                $p->klik('[data-selesai]');
                usleep(300_000);
                file_put_contents(getenv('TANGKAPAN_DIR') . "/tinjau-{$bahasa}-{$skema}-{$lebar}.png", $p->tangkap());
            }
            $this->tanpaGalat($p);
            unset($p);
        }
    }

    public function testHalamanMenungguBerubahSendiriKetikaDosenMemulai(): void
    {
        $d    = $this->dunia();
        $http = $this->sebagai($d['pengampuX']);
        $u    = $this->ujianTerbit($http, $d['matkulX'], 'Ujian serentak peramban');
        $sesi = $this->beri($http, $d['matkulX'], $u, $d['kelasXA']);
        $p    = new Peramban(self::ASAL);
        $p->kuki('lokandita_sid', Fixtures::session($this->idn, (string) $d['mhsA']['id']));
        $this->assertSame(200, $p->buka("/ujian-saya/{$sesi}"));
        $this->assertStringContainsString('Menunggu dosen memulai ujian', (string) $p->js('document.body.innerText'));
        sleep(6); // satu putaran tanya: belum dimulai, halaman tetap menunggu
        $this->assertTrue($p->js("!!document.querySelector('[data-menunggu]')"));
        $this->kirim($http, "/sesi/{$sesi}/mulai", ['menit' => '30']);
        $p->tunggu("!!document.getElementById('ujian') && document.getElementById('ujian').dataset.siap === '1'", 15, 'halaman menunggu berubah menjadi halaman ujian');
        $this->assertSame('1', (string) $this->db->query('SELECT COUNT(*) AS n FROM attempts')->fetch_assoc()['n']);
        $this->assertSame([], $p->js('window.__csp'));
        $this->assertSame([], $p->js('window.__galat'));
    }
}
