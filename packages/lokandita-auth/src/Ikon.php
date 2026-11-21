<?php

declare(strict_types=1);

namespace Lokandita\Auth;

/**
 * Ikon garis Lucide (lisensi ISC, docs/branding/LISENSI.md), disajikan dari berkas SVG
 * di paket ini dan ditulis LANGSUNG ke dalam HTML: tidak ada font ikon, tidak ada CDN,
 * tidak ada permintaan tambahan. Warnanya mengikuti warna teks (`currentColor`), jadi
 * ikut mode gelap. Ukuran dan tebal garisnya sama di mana-mana (kelas CSS `ikon`).
 *
 * - Ikon hiasan (bawaan) disembunyikan dari pembaca layar.
 * - Ikon yang membawa arti sendiri diberi $label, yang dibacakan.
 * - Ikon berarah (panah, masuk, keluar) diberi $berarah supaya dibalik di halaman
 *   kanan-ke-kiri (`[dir=rtl] .ikon-arah`).
 */
final class Ikon
{
    /** @var array<string, string> nama => isi di dalam <svg> */
    private static array $isi = [];

    public static function svg(string $nama, string $label = '', bool $berarah = false): string
    {
        if (! isset(self::$isi[$nama])) {
            $berkas = __DIR__ . '/../ikon/' . $nama . '.svg';
            if (preg_match('/^[a-z0-9-]+$/', $nama) !== 1 || ! is_file($berkas)) {
                throw new \InvalidArgumentException("ikon tidak dikenal: {$nama}");
            }
            // Hanya isi <svg> yang diambil; atribut luarnya ditulis di sini supaya seragam.
            preg_match('#<svg[^>]*>(.*)</svg>#s', (string) file_get_contents($berkas), $m);
            self::$isi[$nama] = trim((string) preg_replace('/\s+/', ' ', $m[1] ?? ''));
        }
        $akses = $label === ''
            ? 'aria-hidden="true"'
            : 'role="img" aria-label="' . htmlspecialchars($label, ENT_QUOTES) . '"';

        return '<svg class="ikon' . ($berarah ? ' ikon-arah' : '') . '" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"'
            . ' fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" focusable="false" '
            . $akses . '>' . self::$isi[$nama] . '</svg>';
    }
}
