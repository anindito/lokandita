<?php

declare(strict_types=1);

namespace Lokandita\Auth\Seb;

/**
 * Setelan SEB yang dibuat server untuk satu mata kuliah (SPEC.md §13.1, KONTRAK.md §8).
 *
 * Nama kunci mengikuti SEB untuk Windows 3.10.2 (Keys.cs di kode sumbernya) dan dokumen
 * "SEB Configuration Setting Keys". Dua kunci ditulis walau klien Windows 3.10.2 tidak
 * membacanya — `monitorProcesses` (pemantauan selalu aktif pada mode "Create new desktop")
 * dan `allowScreenCapture` (hanya macOS) — supaya SEB Configuration Tool menampilkan
 * keadaan yang diminta pemilik. Kunci yang tidak ditulis memakai bawaan klien.
 *
 * Mengubah berkas ini mengubah Config Key setiap konfigurasi yang dibuat SESUDAHNYA;
 * konfigurasi yang sudah tersimpan tidak berubah, karena yang disimpan adalah hasilnya.
 */
final class Setelan
{
    /**
     * Proses yang dihentikan paksa. Nama asli (originalName) disamakan dengan nama berkas:
     * untuk butir yang juga ada di daftar bawaan SEB 3.10.2 itu memang nilainya; untuk yang
     * lain belum diperiksa pada Windows sungguhan (PROGRESS.md, tahap 10a).
     * Tidak ada proses sistem di daftar ini.
     */
    private const TERLARANG = [
        'Peramban lain' => ['chrome.exe', 'msedge.exe', 'firefox.exe', 'opera.exe', 'brave.exe', 'vivaldi.exe', 'iexplore.exe'],
        'Kendali jarak jauh' => [
            'AA_v3.exe', 'AeroAdmin.exe', 'AnyDesk.exe', 'beamyourscreen-host.exe', 'chromoting.exe', 'join.me.exe', 'join.me.sentinel.exe',
            'Mikogo-host.exe', 'msra.exe', 'mstsc.exe', 'parsecd.exe', 'PCMonitorSrv.exe', 'pcmontask.exe', 'ptoneclk.exe', 'QuickAssist.exe',
            'RemotePCDesktop.exe', 'remoting_host.exe', 'RPCService.exe', 'RPCSuite.exe', 'rustdesk.exe', 'SRServer.exe', 'strwinclt.exe',
            'TeamViewer.exe', 'ToDesk.exe', 'UltraViewer_Desktop.exe', 'vncserver.exe', 'vncserverui.exe', 'vncviewer.exe',
        ],
        'Perekam layar' => [
            'bdcam.exe', 'CamPlay.exe', 'CamRecorder.exe', 'Camtasia.exe', 'Camtasia_Studio.exe', 'CamtasiaStudio.exe', 'CamtasiaUtl.exe',
            'obs32.exe', 'obs64.exe', 'ShareX.exe', 'SnippingTool.exe',
        ],
        'Berbagi layar dan percakapan' => [
            'CiscoCollabHost.exe', 'CiscoWebExStart.exe', 'Discord.exe', 'DiscordCanary.exe', 'DiscordPTB.exe', 'Element.exe', 'g2mcomm.exe',
            'g2mlauncher.exe', 'g2mstart.exe', 'GotoMeetingWinStore.exe', 'Guilded.exe', 'LINE.exe', 'Messenger.exe', 'MS-teams.exe',
            'Signal.exe', 'Skype.exe', 'SkypeApp.exe', 'SkypeHost.exe', 'slack.exe', 'Teams.exe', 'Telegram.exe', 'webexmta.exe',
            'WeChat.exe', 'WhatsApp.exe', 'Zoom.exe',
        ],
    ];

    // Nama asli yang berbeda dari nama berkasnya di daftar bawaan SEB 3.10.2.
    private const NAMA_ASLI = ['MS-teams.exe' => 'MS-Teams.exe'];

    /**
     * @param string       $urlKeluar  alamat yang menutup SEB saat dibuka (quitURL)
     * @param list<string> $asal      asal (origin) yang boleh dibuka, mis. https://lokakode.lokandita.com
     * @param string       $hashKeluar hex SHA-256 kata sandi keluar
     * @param string       $garam      32 byte acak (examKeySalt)
     *
     * @return array<string, mixed>
     */
    public static function bangun(string $urlMulai, string $urlKeluar, array $asal, string $hashKeluar, string $garam): array
    {
        $aturan = [];
        foreach ($asal as $a) {
            $aturan[] = ['action' => 1, 'active' => true, 'expression' => self::ekspresi($a), 'regex' => false];
        }
        $terlarang = [];
        foreach (self::TERLARANG as $jenis => $daftar) {
            foreach ($daftar as $exe) {
                $terlarang[] = [
                    'active' => true, 'description' => $jenis, 'executable' => $exe,
                    'originalName' => self::NAMA_ASLI[$exe] ?? $exe, 'os' => 1, 'strongKill' => true,
                ];
            }
        }

        return [
            // Ujian: mulai dari LokaKode, keluar dengan kata sandi.
            'sebConfigPurpose' => 0,
            'sebMode'          => 0,
            'startURL'         => $urlMulai,
            'allowQuit'        => true,
            'hashedQuitPassword' => $hashKeluar,
            // Tautan "Keluar dari SEB": membuka alamat ini menutup SEB tanpa kata sandi, sesudah
            // konfirmasi. Tanpa ini mahasiswa yang berlatih di rumah tidak bisa menutup SEB.
            'quitURL'          => $urlKeluar,
            'quitURLConfirm'   => true,
            // Browser Exam Key dan Config Key dikirim di header (satu kunci untuk keduanya).
            'sendBrowserExamKey' => true,
            'examKeySalt'        => Plist::data($garam),
            // Mode kios "Create new desktop".
            'createNewDesktop'  => true,
            'killExplorerShell' => false,
            // Proses: dipantau, tidak ada aplikasi lain yang diizinkan, daftar di atas dihentikan paksa.
            'monitorProcesses'    => true,
            'permittedProcesses'  => [],
            'prohibitedProcesses' => $terlarang,
            // Mesin virtual, tangkapan layar, sesi jarak jauh, layar kedua.
            'allowVirtualMachine'          => false,
            'enablePrintScreen'            => false,
            'allowScreenCapture'           => false,
            'allowScreenSharing'           => false,
            'allowedDisplaysMaxNumber'     => 1,
            'allowedDisplaysIgnoreFailure' => false,
            'allowedDisplayBuiltinEnforce' => false,
            // Peramban: hanya asal di bawah ini; tanpa konsol pengembang, unduh, unggah.
            'URLFilterEnable'              => true,
            'URLFilterEnableContentFilter' => false,
            'URLFilterRules'               => $aturan,
            'allowDeveloperConsole'        => false,
            'allowDownloads'               => false,
            'allowUploads'                 => false,
            'allowSpellCheck'              => false,
            'allowPDFReaderToolbar'        => false,
            'allowApplicationLog'          => false,
            // Papan klip terisolasi: salin-tempel hanya di dalam SEB (2 = isolated).
            'clipboardPolicy' => 2,
            // Komputer lab dipakai bergantian.
            'examSessionClearCookiesOnStart' => true,
            'examSessionClearCookiesOnEnd'   => true,
        ];
    }

    /**
     * Ekspresi filter URL untuk satu asal: skema + host PERSIS (titik di depan host berarti
     * "tanpa subdomain" bagi SEB). `https://lokandita.com` tanpa titik akan ikut mengizinkan
     * setiap subdomainnya.
     */
    public static function ekspresi(string $asal): string
    {
        $skema = (string) parse_url($asal, PHP_URL_SCHEME);
        $host  = (string) parse_url($asal, PHP_URL_HOST);
        if ($skema !== 'https' || preg_match('/^[a-z0-9.-]+$/', $host) !== 1) {
            throw new \InvalidArgumentException('asal SEB harus https dengan nama host biasa');
        }

        return 'https://.' . $host;
    }
}
