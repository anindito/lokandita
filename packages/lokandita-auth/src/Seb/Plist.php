<?php

declare(strict_types=1);

namespace Lokandita\Auth\Seb;

/**
 * Setelan SEB dalam dua bentuk dari SATU larik yang sama (KONTRAK.md §8):
 *
 *   xml()          plist XML — isi berkas .seb;
 *   kunciKonfig()  Config Key — SHA-256 atas "SEB-JSON" setelan itu.
 *
 * Karena keduanya dihitung dari larik yang sama, berkas yang diunduh mahasiswa dan
 * kunci yang diperiksa server tidak bisa berbeda.
 *
 * Bentuk larik: larik berkunci teks = <dict>; larik berurutan (termasuk larik kosong) =
 * <array>; bool, int, string; data biner dibungkus Plist::data(). Bilangan pecahan dan
 * tanggal sengaja tidak didukung: penulisannya di sisi klien bergantung pada .NET dan
 * setelan kita tidak membutuhkannya.
 *
 * SEB-JSON di sini meniru SEB untuk Windows 3.10.2 (Json.cs di kode sumbernya) sampai
 * ke keanehannya, karena kunci harus sama byte demi byte dengan hitungan klien:
 *   - kunci diurutkan seperti StringComparer.InvariantCulture: tanpa membedakan huruf
 *     besar-kecil; bila sama, huruf kecil lebih dulu;
 *   - `originatorVersion` dan <dict> kosong dilewati, TETAPI koma tetap ditulis sesudah
 *     setiap butir yang bukan kunci terakhir dalam urutan — termasuk bila kunci terakhir
 *     itu dilewati (menghasilkan ",}");
 *   - tanpa spasi, tanpa escape; data = base64 berkutip; string kosong = "".
 */
final class Plist
{
    /**
     * @return array{__data: string}
     */
    public static function data(string $biner): array
    {
        return ['__data' => base64_encode($biner)];
    }

    /**
     * @param array<string, mixed> $setelan
     */
    public static function xml(array $setelan): string
    {
        return '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
            . '<!DOCTYPE plist PUBLIC "-//Apple//DTD PLIST 1.0//EN" "http://www.apple.com/DTDs/PropertyList-1.0.dtd">' . "\n"
            . '<plist version="1.0">' . self::xmlNilai($setelan) . '</plist>' . "\n";
    }

    /**
     * @param array<string, mixed> $setelan
     */
    public static function kunciKonfig(array $setelan): string
    {
        return hash('sha256', self::json($setelan));
    }

    /**
     * @param array<string, mixed> $setelan
     */
    public static function json(array $setelan): string
    {
        return self::jsonNilai($setelan);
    }

    private static function adalahData(mixed $v): bool
    {
        return is_array($v) && array_keys($v) === ['__data'];
    }

    private static function xmlNilai(mixed $v): string
    {
        if (self::adalahData($v)) {
            return '<data>' . $v['__data'] . '</data>';
        }
        if (is_array($v)) {
            if ($v === []) {
                return '<array/>';
            }
            if (array_is_list($v)) {
                return '<array>' . implode('', array_map(self::xmlNilai(...), $v)) . '</array>';
            }
            $isi = '';
            foreach ($v as $kunci => $nilai) {
                $isi .= '<key>' . self::teks((string) $kunci) . '</key>' . self::xmlNilai($nilai);
            }

            return '<dict>' . $isi . '</dict>';
        }
        if (is_bool($v)) {
            return $v ? '<true/>' : '<false/>';
        }
        if (is_int($v)) {
            if ($v < -2147483648 || $v > 2147483647) {
                throw new \InvalidArgumentException('bilangan di luar int32: klien SEB menolaknya');
            }

            return '<integer>' . $v . '</integer>';
        }
        if (is_string($v)) {
            return '<string>' . self::teks($v) . '</string>';
        }

        throw new \InvalidArgumentException('jenis nilai plist tidak didukung: ' . get_debug_type($v));
    }

    /**
     * Teks yang aman ditulis apa adanya di XML DAN di SEB-JSON (yang tidak meng-escape apa pun):
     * karakter yang berarti di salah satunya ditolak, supaya kedua bentuk selalu memuat teks yang sama.
     */
    private static function teks(string $s): string
    {
        if (preg_match('/^[\x20\x21\x23-\x25\x27-\x3B\x3D\x3F-\x5B\x5D-\x7E]*$/', $s) !== 1) {
            throw new \InvalidArgumentException('teks setelan SEB hanya boleh ASCII tercetak tanpa " & < > \\');
        }

        return $s;
    }

    private static function jsonNilai(mixed $v): string
    {
        if (self::adalahData($v)) {
            return '"' . $v['__data'] . '"';
        }
        if (is_array($v)) {
            if ($v === [] || array_is_list($v)) {
                // Klien tidak menulis koma sesudah butir yang "sama rujukan" dengan butir terakhir;
                // itu hanya terjadi pada butir null, yang tidak pernah kita hasilkan.
                return '[' . implode(',', array_map(self::jsonNilai(...), $v)) . ']';
            }
            $kunci = array_map(strval(...), array_keys($v));
            usort($kunci, self::banding(...));
            $akhir = $kunci[count($kunci) - 1];
            $hasil = '{';
            foreach ($kunci as $k) {
                $nilai = $v[$k];
                // <dict> kosong tidak pernah ada di sini: larik kosong selalu ditulis <array/>.
                if (strcasecmp($k, 'originatorVersion') === 0) {
                    continue;
                }
                $hasil .= '"' . self::teks($k) . '":' . self::jsonNilai($nilai);
                if ($k !== $akhir) {
                    $hasil .= ',';
                }
            }

            return $hasil . '}';
        }
        if (is_bool($v)) {
            return $v ? 'true' : 'false';
        }
        if (is_int($v)) {
            return (string) $v;
        }
        if (is_string($v)) {
            return '"' . self::teks($v) . '"';
        }

        throw new \InvalidArgumentException('jenis nilai plist tidak didukung: ' . get_debug_type($v));
    }

    /**
     * Urutan StringComparer.InvariantCulture untuk nama kunci (huruf dan angka ASCII).
     */
    public static function banding(string $a, string $b): int
    {
        $ka = strtolower($a);
        $kb = strtolower($b);
        if ($ka !== $kb) {
            return strcmp($ka, $kb) <=> 0;
        }
        for ($i = 0, $n = strlen($a); $i < $n; $i++) {
            if ($a[$i] !== $b[$i]) {
                return ctype_lower($a[$i]) ? -1 : 1;
            }
        }

        return 0;
    }
}
