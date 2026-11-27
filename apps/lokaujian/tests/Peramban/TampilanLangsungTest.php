<?php

declare(strict_types=1);

namespace Tests\Peramban;

use Lokandita\Auth\Testing\Fixtures;
use Lokandita\Auth\Testing\Peramban;
use Tests\Support\UjianCase;

/**
 * Tampilan langsung halaman sesi di PERAMBAN SUNGGUHAN (SPEC-LOKAUJIAN.md §10): public/js/langsung.js
 * menyegarkan tabel tiap 5 detik tanpa memuat ulang halaman, di bawah CSP yang sama dengan produksi.
 * Tes ini GAGAL, tidak dilewati, bila peramban tidak ada.
 */
final class TampilanLangsungTest extends UjianCase
{
    private const ASAL = 'https://lokaujian.lokandita.com';

    public function testTabelMenyegarkanDiriDanCentangBertahan(): void
    {
        $d   = $this->duniaSesi();
        $s   = $d['sesi'];
        $ani = $this->sebagai($d['mhsA']);
        $p   = new Peramban(self::ASAL);
        $p->kuki('lokandita_sid', Fixtures::session($this->idn, (string) $d['pengajarA']['id']));
        $this->assertSame(200, $p->buka("/sesi/{$s}"));
        $baris = 'tr[data-id="' . $d['mhsA']['id'] . '"]';
        $teks  = static fn (string $sel): string => "document.querySelector('{$baris} [data-sel=\"{$sel}\"]').textContent";
        $this->assertSame('Belum hadir', $p->js($teks('status')));
        // Dosen mencentang Citra; lalu Ani mulai dan menyimpan satu jawaban dari tempat lain.
        $p->klik('tr[data-id="' . $d['mhsC']['id'] . '"] input[type="checkbox"]');
        $this->mulaiKerja($ani, $s, (string) $d['mhsA']['id']);
        $this->simpan($ani, $s, $this->bukaHalamanUjian($ani, $s), $d['q']['BS'], 'benar', 1);

        // Tanpa memuat ulang: status, jumlah terjawab, sisa waktu dan ringkasan berubah sendiri.
        $p->js('window.__tanda = 1');
        $p->tunggu($teks('terjawab') . " === '1 / 6'", 15, 'jumlah terjawab tampil sendiri');
        $this->assertStringContainsString('Mengerjakan', (string) $p->js($teks('status')));
        $this->assertMatchesRegularExpression('/^0:5\d:\d\d$/', (string) $p->js($teks('sisa')));
        $this->assertStringContainsString('mengerjakan 1', (string) $p->js("document.querySelector('[data-ringkas]').textContent"));
        $this->assertSame(1, $p->js('window.__tanda'), 'halaman tidak dimuat ulang');
        $this->assertTrue($p->js("document.querySelector('tr[data-id=\"{$d['mhsC']['id']}\"] input[type=\"checkbox\"]').checked"), 'centang dosen bertahan');
        $this->assertTrue($p->js("document.querySelector('[data-usang]').hidden"));

        // Tambahan waktu yang diberikan dosen lain terlihat di sini dalam satu putaran.
        $this->kirim($d['dosen'], "/sesi/{$s}/tambah-waktu", ['mahasiswa' => [(string) $d['mhsA']['id']], 'menit' => '10', 'yakin' => '1']);
        $p->tunggu($teks('tambahan') . " === '+10'", 15, 'tambahan waktu tampil sendiri');
        $this->assertMatchesRegularExpression('/^1:0\d:\d\d$/', (string) $p->js($teks('sisa')));

        // Keadaan sesi berubah (diakhiri): halaman meminta dimuat ulang, tidak memuat ulang sendiri.
        $this->kirim($d['dosen'], "/sesi/{$s}/akhiri");
        $p->tunggu("!document.querySelector('[data-usang]').hidden", 15, 'pemberitahuan keadaan berubah');
        $this->assertSame(1, $p->js('window.__tanda'));
        $this->assertSame([], $p->js('window.__csp'));
        $this->assertSame([], $p->js('window.__galat'));
    }
}
