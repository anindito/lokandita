<?php

declare(strict_types=1);

namespace Tests\Seb;

use App\Libraries\Seb\Plist;
use App\Libraries\Seb\Setelan;
use PHPUnit\Framework\TestCase;

/**
 * Config Key dan plist (KONTRAK.md §8). Bentuk SEB-JSON di sini diturunkan dari kode sumber
 * SEB untuk Windows 3.10.2 (Json.cs). Tes ini membuktikan bahwa kode kita melakukan apa yang
 * kita baca di sana; bahwa klien SUNGGUHAN menghasilkan kunci yang sama hanya bisa dibuktikan
 * dengan SEB 3.10.2 di Windows (halaman /seb/periksa, gerbang tahap 10a).
 */
final class PlistTest extends TestCase
{
    public function testSebJsonTanpaSpasiTerurutTanpaEscape(): void
    {
        $json = Plist::json([
            'startURL'   => 'https://lokakode.lokandita.com/beranda',
            'allowQuit'  => true,
            'URLFilterRules' => [['regex' => false, 'action' => 1, 'expression' => 'https://.lokandita.com', 'active' => true]],
            'permittedProcesses' => [],
            'examKeySalt' => Plist::data("\x00\x01\x02garam"),
            'clipboardPolicy' => 2,
            'hashedQuitPassword' => '',
        ]);
        // Urutan tanpa membedakan huruf besar-kecil: URLFilterRules sesudah startURL, bukan di depan.
        $this->assertSame(
            '{"allowQuit":true,"clipboardPolicy":2,"examKeySalt":"AAECZ2FyYW0=","hashedQuitPassword":"","permittedProcesses":[],'
            . '"startURL":"https://lokakode.lokandita.com/beranda",'
            . '"URLFilterRules":[{"action":1,"active":true,"expression":"https://.lokandita.com","regex":false}]}',
            $json,
        );
    }

    public function testUrutanKunciSepertiInvariantCulture(): void
    {
        $kunci = ['allowWLAN', 'URLFilterEnable', 'allowWlan', 'enableF10', 'enableF1', 'allowedDisplaysMaxNumber', 'allowedDisplayBuiltinEnforce', 'URLFilterEnableContentFilter'];
        usort($kunci, Plist::banding(...));
        $this->assertSame(
            ['allowedDisplayBuiltinEnforce', 'allowedDisplaysMaxNumber', 'allowWlan', 'allowWLAN', 'enableF1', 'enableF10', 'URLFilterEnable', 'URLFilterEnableContentFilter'],
            $kunci,
        );
    }

    /**
     * Keanehan klien yang harus ditiru: originatorVersion dilewati, tetapi koma tetap ditulis
     * sesudah butir sebelumnya bila originatorVersion adalah kunci terakhir dalam urutan.
     */
    public function testOriginatorVersionDilewatiDenganKomaMenggantung(): void
    {
        $this->assertSame('{"a":1,}', Plist::json(['a' => 1, 'originatorVersion' => 'SEB_Win_3.10.2']));
        $this->assertSame('{"a":1,"z":2}', Plist::json(['z' => 2, 'originatorVersion' => 'x', 'a' => 1]));
    }

    public function testTeksYangBerbedaDiXmlDanJsonDitolak(): void
    {
        foreach (['a"b', 'a&b', 'a<b', 'a\\b', 'é'] as $buruk) {
            try {
                Plist::json(['k' => $buruk]);
                $this->fail("teks {$buruk} seharusnya ditolak");
            } catch (\InvalidArgumentException) {
                $this->addToAssertionCount(1);
            }
        }
    }

    /**
     * Setelan yang diminta pemilik (tahap 10), dengan nama kunci SEB yang sebenarnya.
     */
    public function testSetelanMemuatPengerasanYangDiminta(): void
    {
        $s = Setelan::bangun('https://lokakode.lokandita.com/beranda', 'https://lokakode.lokandita.com/seb/keluar',
            ['https://lokakode.lokandita.com', 'https://auth.lokandita.com', 'https://lokandita.com'], hash('sha256', 'kata-sandi-keluar'), random_bytes(32));

        $harus = [
            'createNewDesktop' => true, 'killExplorerShell' => false,            // kios "Create new desktop"
            'monitorProcesses' => true, 'permittedProcesses' => [],              // pantau proses; tak ada aplikasi lain
            'allowVirtualMachine' => false,
            'enablePrintScreen' => false, 'allowScreenCapture' => false,
            'allowScreenSharing' => false,                                        // sesi jarak jauh / berbagi layar
            'allowedDisplaysMaxNumber' => 1,
            'sendBrowserExamKey' => true,                                         // BEK dan Config Key di header
            'URLFilterEnable' => true, 'allowDeveloperConsole' => false,
            'sebConfigPurpose' => 0, 'startURL' => 'https://lokakode.lokandita.com/beranda',
            'hashedQuitPassword' => hash('sha256', 'kata-sandi-keluar'),
        ];
        foreach ($harus as $kunci => $nilai) {
            $this->assertSame($nilai, $s[$kunci], $kunci);
        }

        // Daftar URL: asal aplikasi DAN asal LokaAuth selalu ada; host persis (titik di depan), aksi 1 = allow.
        $ekspresi = array_column($s['URLFilterRules'], 'expression');
        $this->assertSame(['https://.lokakode.lokandita.com', 'https://.auth.lokandita.com', 'https://.lokandita.com'], $ekspresi);
        foreach ($s['URLFilterRules'] as $a) {
            $this->assertSame(['action' => 1, 'active' => true, 'regex' => false], ['action' => $a['action'], 'active' => $a['active'], 'regex' => $a['regex']]);
        }

        // Proses terlarang: semuanya untuk Windows (os = 1), aktif, dihentikan paksa; tidak ada proses sistem.
        $exe = array_map(strtolower(...), array_column($s['prohibitedProcesses'], 'executable'));
        $this->assertSame(count($exe), count(array_unique($exe)), 'tidak ada proses yang tertulis dua kali');
        foreach ($s['prohibitedProcesses'] as $p) {
            $this->assertSame([true, 1, true], [$p['active'], $p['os'], $p['strongKill']], $p['executable']);
        }
        foreach (['chrome.exe', 'msedge.exe', 'firefox.exe', 'anydesk.exe', 'teamviewer.exe', 'mstsc.exe', 'obs64.exe', 'zoom.exe', 'discord.exe', 'whatsapp.exe'] as $wajib) {
            $this->assertContains($wajib, $exe);
        }
        foreach (['explorer.exe', 'svchost.exe', 'sethc.exe', 'taskmgr.exe', 'dwm.exe', 'csrss.exe', 'winlogon.exe', 'lsass.exe', 'services.exe', 'msedgewebview2.exe', 'searchhost.exe', 'ctfmon.exe'] as $sistem) {
            $this->assertNotContains($sistem, $exe, 'proses sistem tidak boleh ada di daftar');
        }
    }

    /**
     * XML yang ditulis terbaca kembali menjadi nilai yang sama, dan Config Key dari hasil baca
     * sama dengan Config Key dari larik aslinya — berkas dan kunci tidak bisa berbeda.
     */
    public function testXmlDanKunciBerasalDariSetelanYangSama(): void
    {
        $s   = Setelan::bangun('https://a.example/beranda', 'https://a.example/seb/keluar', ['https://a.example', 'https://b.example'], hash('sha256', 'x'), random_bytes(32));
        $xml = Plist::xml($s);
        $this->assertStringStartsWith('<?xm', $xml, 'SEB 3.10.2 mengenali berkas XML dari empat byte pertamanya');
        $baca = self::bacaPlist($xml);
        $this->assertSame(Plist::json($s), Plist::json($baca));
        $this->assertSame(Plist::kunciKonfig($s), Plist::kunciKonfig($baca));
        $this->assertMatchesRegularExpression('/^[0-9a-f]{64}$/', Plist::kunciKonfig($s));
    }

    /**
     * Pembaca plist kecil yang TIDAK memakai kode aplikasi, dengan jenis seperti XmlParser SEB.
     *
     * @return array<string, mixed>
     */
    public static function bacaPlist(string $xml): array
    {
        $dok = new \DOMDocument();
        $dok->loadXML($xml, LIBXML_NONET);
        $akar = $dok->documentElement;
        $baca = static function (\DOMElement $e) use (&$baca): mixed {
            $anak = [];
            foreach ($e->childNodes as $n) {
                if ($n instanceof \DOMElement) {
                    $anak[] = $n;
                }
            }
            switch ($e->tagName) {
                case 'dict':
                    $hasil = [];
                    for ($i = 0; $i < count($anak); $i += 2) {
                        $hasil[$anak[$i]->textContent] = $baca($anak[$i + 1]);
                    }

                    return $hasil;
                case 'array':   return array_map($baca, $anak);
                case 'true':    return true;
                case 'false':   return false;
                case 'integer': return (int) $e->textContent;
                case 'string':  return $e->textContent;
                case 'data':    return Plist::data((string) base64_decode($e->textContent, true));
                default: throw new \RuntimeException('elemen plist tak dikenal: ' . $e->tagName);
            }
        };
        foreach ($akar->childNodes as $n) {
            if ($n instanceof \DOMElement) {
                return $baca($n);
            }
        }

        throw new \RuntimeException('plist tanpa dict');
    }
}
