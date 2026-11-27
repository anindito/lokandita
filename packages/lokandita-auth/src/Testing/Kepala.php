<?php

declare(strict_types=1);

namespace Lokandita\Auth\Testing;

use PHPUnit\Framework\Assert;

/**
 * Ukuran kepala halaman (tata_letak.php) di peramban sungguhan, untuk tes tata letak tiap aplikasi.
 *
 * Aturannya (pemilik, 2026-11-27): kepala SELALU satu baris dan tidak ada yang bertumpuk — pada
 * lebar ponsel sampai desktop, dalam ketiga bahasa, sebelum dan sesudah masuk.
 */
final class Kepala
{
    /** Lebar layar yang diperiksa pemilik di gerbangnya. */
    public const LEBAR = [360, 390, 412, 768, 1024, 1440];

    /** Semua yang tampak di kepala, dengan kotaknya; panel menu yang terbuka diukur terpisah. */
    public const UKUR = <<<'JS'
        (function () {
            function kotak(el) { var r = el.getBoundingClientRect(); return { kiri: r.left, kanan: r.right, atas: r.top, bawah: r.bottom }; }
            function tampak(el) { return !!el && el.getClientRects().length > 0 && getComputedStyle(el).visibility !== 'hidden'; }
            var kepala = document.querySelector('.kepala'), g = getComputedStyle(kepala);
            var tombol = document.querySelector('[data-menu-tombol]'), panel = document.querySelector('.kepala-menu');
            var buka = !!tombol && tombol.getAttribute('aria-expanded') === 'true';
            var pemilih = '.merek, .kepala-publik > .tombol, .kepala-menu-tombol, .akun-tombol' + (buka ? '' : ', .nav-publik a, .bahasa-kepala a');
            var butir = Array.prototype.filter.call(kepala.querySelectorAll(pemilih), tampak).map(function (el) {
                var k = kotak(el);
                k.apa = (el.className || el.tagName) + ' ' + el.textContent.trim().slice(0, 20);
                k.baris = el.matches('a') && !el.matches('.merek') ? el.getClientRects().length : 1;
                return k;
            });
            return {
                lebar: document.documentElement.clientWidth, gulir: document.documentElement.scrollWidth,
                kepala: kotak(kepala), isiKiri: kepala.getBoundingClientRect().left + parseFloat(g.paddingLeft), isiKanan: kepala.getBoundingClientRect().right - parseFloat(g.paddingRight),
                butir: butir,
                tombol: tampak(tombol), buka: buka, kendali: tombol ? tombol.getAttribute('aria-controls') : null, label: tombol ? tombol.getAttribute('aria-label') : null,
                panel: tampak(panel) ? kotak(panel) : null, panelId: panel ? panel.id : null,
                tautan: Array.prototype.filter.call(document.querySelectorAll('.nav-publik a, .bahasa-kepala a'), tampak).length,
                fokus: document.activeElement ? (document.activeElement.className || document.activeElement.tagName) + '|' + document.activeElement.textContent.trim() : '',
                latar: g.backgroundColor, csp: window.__csp, galat: window.__galat
            };
        })()
        JS;

    /**
     * Kepala satu baris: semua butir memotong satu garis mendatar yang sama, tidak ada dua butir
     * yang bertumpuk, tidak ada teks yang turun baris, dan tidak ada yang keluar dari layar.
     *
     * @param array<string, mixed> $u hasil self::UKUR
     */
    public static function assertSatuBaris(array $u, string $apa): void
    {
        Assert::assertSame([[], []], [$u['csp'], $u['galat']], "{$apa}: CSP dan galat skrip");
        Assert::assertLessThanOrEqual($u['lebar'] + 1, $u['gulir'], "{$apa}: halaman melebar melewati layar");
        Assert::assertGreaterThanOrEqual(2, count($u['butir']), "{$apa}: merek dan sekurangnya satu kendali");
        Assert::assertLessThan(72, $u['kepala']['bawah'] - $u['kepala']['atas'], "{$apa}: tinggi kepala lebih dari satu baris");
        $atas  = max(array_column($u['butir'], 'atas'));
        $bawah = min(array_column($u['butir'], 'bawah'));
        Assert::assertLessThan($bawah, $atas, "{$apa}: butir kepala tidak sebaris");
        $urut = $u['butir'];
        usort($urut, static fn (array $a, array $b): int => $a['kiri'] <=> $b['kiri']);
        foreach ($urut as $i => $k) {
            Assert::assertSame(1, $k['baris'], "{$apa}: teks '{$k['apa']}' turun baris");
            Assert::assertGreaterThanOrEqual($u['isiKiri'] - 0.5, $k['kiri'], "{$apa}: '{$k['apa']}' keluar di kiri");
            Assert::assertLessThanOrEqual($u['isiKanan'] + 0.5, $k['kanan'], "{$apa}: '{$k['apa']}' keluar di kanan");
            if ($i > 0) {
                Assert::assertGreaterThanOrEqual($urut[$i - 1]['kanan'] + 4, $k['kiri'], "{$apa}: '{$urut[$i - 1]['apa']}' dan '{$k['apa']}' bertumpuk atau berimpit");
            }
        }
    }
}
